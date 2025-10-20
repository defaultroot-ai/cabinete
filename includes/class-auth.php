<?php
/**
 * Authentication Class
 * Handles CNP-based authentication and multi-phone support
 * 
 * @package MedicalBookingSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MBS_Auth {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Hook into WordPress authentication
        add_filter('authenticate', array($this, 'custom_authenticate'), 30, 3);
        
        // Add CNP field to user profile
        add_action('show_user_profile', array($this, 'add_cnp_field'));
        add_action('edit_user_profile', array($this, 'add_cnp_field'));
        add_action('personal_options_update', array($this, 'save_cnp_field'));
        add_action('edit_user_profile_update', array($this, 'save_cnp_field'));
    }
    
    /**
     * Custom authentication handler
     * Allows login with: CNP, Email, or Phone
     */
    public function custom_authenticate($user, $username, $password) {
        // Skip if already authenticated or empty credentials
        if ($user instanceof WP_User || empty($username) || empty($password)) {
            return $user;
        }
        
        $user_to_authenticate = null;
        
        // Detect identifier type and find user
        if ($this->is_cnp($username)) {
            // Login with CNP
            $user_to_authenticate = $this->get_user_by_cnp($username);
        } elseif (is_email($username)) {
            // Login with Email (WordPress default)
            $user_to_authenticate = get_user_by('email', $username);
        } elseif ($this->is_phone($username)) {
            // Login with Phone
            $user_to_authenticate = $this->get_user_by_phone($username);
        } else {
            // Try WordPress default username
            $user_to_authenticate = get_user_by('login', $username);
        }
        
        // If user found, verify password
        if ($user_to_authenticate && wp_check_password($password, $user_to_authenticate->data->user_pass, $user_to_authenticate->ID)) {
            return $user_to_authenticate;
        }
        
        return $user;
    }
    
    /**
     * Check if string is a valid Romanian CNP
     * 
     * @param string $cnp CNP to validate
     * @return bool True if valid CNP format
     */
    public function is_cnp($cnp) {
        // Remove any spaces or dashes
        $cnp = preg_replace('/[\s\-]/', '', $cnp);
        
        // Must be exactly 13 digits
        if (!preg_match('/^\d{13}$/', $cnp)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate Romanian CNP with control algorithm
     * 
     * @param string $cnp CNP to validate
     * @return bool True if valid CNP
     */
    public function validate_cnp($cnp) {
        // Basic format check
        if (!$this->is_cnp($cnp)) {
            return false;
        }
        
        // Control algorithm
        $control_string = '279146358279';
        $sum = 0;
        
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnp[$i] * (int)$control_string[$i];
        }
        
        $control_digit = $sum % 11;
        if ($control_digit == 10) {
            $control_digit = 1;
        }
        
        return (int)$cnp[12] === $control_digit;
    }
    
    /**
     * Check if string is a valid phone number
     * 
     * @param string $phone Phone to validate
     * @return bool True if valid phone format
     */
    public function is_phone($phone) {
        // Remove any spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Romanian phone: 10 digits starting with 07, or international format
        return preg_match('/^(07\d{8}|(\+4|04)07\d{8})$/', $phone);
    }
    
    /**
     * Normalize phone number to standard format
     * 
     * @param string $phone Phone to normalize
     * @return string Normalized phone (07XXXXXXXX)
     */
    public function normalize_phone($phone) {
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
        $phone = preg_replace('/^\+40/', '0', $phone);
        $phone = preg_replace('/^40/', '0', $phone);
        return $phone;
    }
    
    /**
     * Get user by CNP
     * 
     * @param string $cnp CNP to search
     * @return WP_User|false User object or false
     */
    public function get_user_by_cnp($cnp) {
        $users = get_users(array(
            'meta_key' => 'mbs_cnp',
            'meta_value' => $cnp,
            'number' => 1,
        ));
        
        return !empty($users) ? $users[0] : false;
    }
    
    /**
     * Get user by phone number
     * 
     * @param string $phone Phone to search
     * @return WP_User|false User object or false
     */
    public function get_user_by_phone($phone) {
        global $wpdb;
        
        $phone = $this->normalize_phone($phone);
        $table = $wpdb->prefix . 'mbs_user_phones';
        
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $table WHERE phone = %s LIMIT 1",
            $phone
        ));
        
        return $user_id ? get_user_by('id', $user_id) : false;
    }
    
    /**
     * Add phone to user account
     * 
     * @param int $user_id User ID
     * @param string $phone Phone number
     * @param bool $is_primary Set as primary phone
     * @return int|false Insert ID or false on failure
     */
    public function add_user_phone($user_id, $phone, $is_primary = false) {
        global $wpdb;
        
        $phone = $this->normalize_phone($phone);
        
        if (!$this->is_phone($phone)) {
            return false;
        }
        
        $table = $wpdb->prefix . 'mbs_user_phones';
        
        // If setting as primary, unset other primary phones
        if ($is_primary) {
            $wpdb->update(
                $table,
                array('is_primary' => 0),
                array('user_id' => $user_id)
            );
        }
        
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'phone' => $phone,
                'is_primary' => $is_primary ? 1 : 0,
                'is_verified' => 0,
            ),
            array('%d', '%s', '%d', '%d')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Get all phones for user
     * 
     * @param int $user_id User ID
     * @return array Array of phone records
     */
    public function get_user_phones($user_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mbs_user_phones';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY is_primary DESC, created_at ASC",
            $user_id
        ));
    }
    
    /**
     * Delete user phone
     * 
     * @param int $user_id User ID
     * @param int $phone_id Phone record ID
     * @return bool True on success
     */
    public function delete_user_phone($user_id, $phone_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mbs_user_phones';
        
        return $wpdb->delete(
            $table,
            array('id' => $phone_id, 'user_id' => $user_id),
            array('%d', '%d')
        );
    }
    
    /**
     * Set phone as primary
     * 
     * @param int $user_id User ID
     * @param int $phone_id Phone record ID
     * @return bool True on success
     */
    public function set_primary_phone($user_id, $phone_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mbs_user_phones';
        
        // Unset all primary phones for user
        $wpdb->update(
            $table,
            array('is_primary' => 0),
            array('user_id' => $user_id)
        );
        
        // Set selected phone as primary
        return $wpdb->update(
            $table,
            array('is_primary' => 1),
            array('id' => $phone_id, 'user_id' => $user_id),
            array('%d'),
            array('%d', '%d')
        );
    }
    
    /**
     * Register new user with CNP
     * 
     * @param array $data User registration data
     * @return int|WP_Error User ID or error
     */
    public function register_user($data) {
        // Validate required fields
        $required = array('cnp', 'email', 'password', 'first_name', 'last_name');
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return new WP_Error('missing_field', sprintf(__('Câmpul %s este obligatoriu', 'medical-booking-system'), $field));
            }
        }
        
        // Validate CNP
        if (!$this->validate_cnp($data['cnp'])) {
            return new WP_Error('invalid_cnp', __('CNP invalid', 'medical-booking-system'));
        }
        
        // Check if CNP already exists
        if ($this->get_user_by_cnp($data['cnp'])) {
            return new WP_Error('cnp_exists', __('CNP deja înregistrat', 'medical-booking-system'));
        }
        
        // Check if email already exists
        if (email_exists($data['email'])) {
            return new WP_Error('email_exists', __('Email deja înregistrat', 'medical-booking-system'));
        }
        
        // Create user with CNP as username
        $user_id = wp_create_user($data['cnp'], $data['password'], $data['email']);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Update user data
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => sanitize_text_field($data['first_name']),
            'last_name' => sanitize_text_field($data['last_name']),
            'display_name' => sanitize_text_field($data['first_name'] . ' ' . $data['last_name']),
        ));
        
        // Save CNP to user meta
        update_user_meta($user_id, 'mbs_cnp', $data['cnp']);
        
        // Add phone if provided
        if (!empty($data['phone'])) {
            $this->add_user_phone($user_id, $data['phone'], true);
        }
        
        // Assign patient role
        $user = new WP_User($user_id);
        $user->set_role('mbs_patient');
        
        return $user_id;
    }
    
    /**
     * Add CNP field to user profile
     */
    public function add_cnp_field($user) {
        $cnp = get_user_meta($user->ID, 'mbs_cnp', true);
        ?>
        <h3><?php _e('Date Medicale', 'medical-booking-system'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="mbs_cnp"><?php _e('CNP', 'medical-booking-system'); ?></label></th>
                <td>
                    <input type="text" name="mbs_cnp" id="mbs_cnp" value="<?php echo esc_attr($cnp); ?>" 
                           class="regular-text" maxlength="13" pattern="\d{13}" 
                           <?php echo (!current_user_can('manage_options') && !empty($cnp)) ? 'readonly' : ''; ?> />
                    <p class="description"><?php _e('CNP românesc (13 cifre). Doar administratorii pot modifica CNP-ul după creare.', 'medical-booking-system'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Save CNP field from user profile
     */
    public function save_cnp_field($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        if (isset($_POST['mbs_cnp'])) {
            $cnp = sanitize_text_field($_POST['mbs_cnp']);
            
            // Only allow admin to change CNP if already set
            $existing_cnp = get_user_meta($user_id, 'mbs_cnp', true);
            if (!empty($existing_cnp) && !current_user_can('manage_options')) {
                return false;
            }
            
            // Validate CNP
            if (!empty($cnp) && !$this->validate_cnp($cnp)) {
                add_action('user_profile_update_errors', function($errors) {
                    $errors->add('invalid_cnp', __('CNP invalid', 'medical-booking-system'));
                });
                return false;
            }
            
            // Check for duplicate CNP
            $existing_user = $this->get_user_by_cnp($cnp);
            if ($existing_user && $existing_user->ID != $user_id) {
                add_action('user_profile_update_errors', function($errors) {
                    $errors->add('cnp_exists', __('CNP deja utilizat de alt utilizator', 'medical-booking-system'));
                });
                return false;
            }
            
            update_user_meta($user_id, 'mbs_cnp', $cnp);
        }
    }
    
    /**
     * Mask CNP for display (show only last 4 digits)
     * 
     * @param string $cnp CNP to mask
     * @return string Masked CNP (e.g., *********1234)
     */
    public function mask_cnp($cnp) {
        if (strlen($cnp) !== 13) {
            return $cnp;
        }
        return str_repeat('*', 9) . substr($cnp, -4);
    }
}

