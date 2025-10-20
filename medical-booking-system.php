<?php
/**
 * Plugin Name: Medical Booking System
 * Plugin URI: https://example.com/medical-booking-system
 * Description: Complete medical appointment booking system with CNP authentication, multilingual support, calendar management, and patient management.
 * Version: 1.1.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: medical-booking-system
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('MBS_PLUGIN_FILE', __FILE__);
define('MBS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MBS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MBS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MBS_VERSION', '1.1.0');

/**
 * Main Medical Booking System Class
 */
class MedicalBookingSystem {
    
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
        $this->init_hooks();
        $this->load_dependencies();
        $this->init();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once MBS_PLUGIN_DIR . 'includes/class-database.php';
        require_once MBS_PLUGIN_DIR . 'includes/class-auth.php';
        require_once MBS_PLUGIN_DIR . 'includes/class-appointment.php';
        require_once MBS_PLUGIN_DIR . 'includes/class-doctor.php';
        require_once MBS_PLUGIN_DIR . 'includes/class-patient.php';
        require_once MBS_PLUGIN_DIR . 'includes/class-service.php';
        require_once MBS_PLUGIN_DIR . 'includes/class-api.php';
        
        // Admin classes
        if (is_admin()) {
            require_once MBS_PLUGIN_DIR . 'admin/class-admin.php';
            require_once MBS_PLUGIN_DIR . 'admin/class-settings.php';
            require_once MBS_PLUGIN_DIR . 'admin/class-doctors-manager.php';
            require_once MBS_PLUGIN_DIR . 'admin/class-appointments-manager.php';
        }
        
        // Public classes
        require_once MBS_PLUGIN_DIR . 'public/class-booking-form.php';
        require_once MBS_PLUGIN_DIR . 'public/class-auth-form.php';
        require_once MBS_PLUGIN_DIR . 'public/class-patient-dashboard.php';
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize database
        MBS_Database::get_instance();
        
        // Initialize authentication
        MBS_Auth::get_instance();
        
        // Initialize API
        MBS_API::get_instance();
        
        // Initialize admin
        if (is_admin()) {
            MBS_Admin::get_instance();
        }
        
        // Initialize public
        MBS_Booking_Form::get_instance();
        MBS_Auth_Form::get_instance();
        MBS_Patient_Dashboard::get_instance();
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'medical-booking-system',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Enqueue public scripts and styles
     */
    public function enqueue_public_scripts() {
        wp_enqueue_style(
            'mbs-public-style',
            MBS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            MBS_VERSION
        );
        
        wp_enqueue_script(
            'mbs-public-script',
            MBS_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            MBS_VERSION,
            true
        );
        
        // Localize script for translations
        wp_localize_script('mbs-public-script', 'mbs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mbs_nonce'),
            'rest_base' => rest_url('mbs/v1'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'strings' => array(
                'loading' => __('Loading...', 'medical-booking-system'),
                'error' => __('An error occurred. Please try again.', 'medical-booking-system'),
                'success' => __('Success!', 'medical-booking-system'),
                'confirm_booking' => __('Confirm Booking', 'medical-booking-system'),
                'cancel_booking' => __('Cancel Booking', 'medical-booking-system'),
                'select_service' => __('Select Service', 'medical-booking-system'),
                'select_doctor' => __('Select Doctor', 'medical-booking-system'),
                'select_date' => __('Select Date', 'medical-booking-system'),
                'select_time' => __('Select Time', 'medical-booking-system'),
                'select_patient' => __('Select Patient', 'medical-booking-system'),
                'booking_summary' => __('Booking Summary', 'medical-booking-system'),
                'confirmation' => __('Confirmation', 'medical-booking-system'),
            )
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'medical-booking') === false) {
            return;
        }
        
        wp_enqueue_style(
            'mbs-admin-style',
            MBS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            MBS_VERSION
        );
        
        wp_enqueue_script(
            'mbs-admin-script',
            MBS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker'),
            MBS_VERSION,
            true
        );
        
        // Localize admin script
        wp_localize_script('mbs-admin-script', 'mbs_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mbs_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'medical-booking-system'),
                'saving' => __('Saving...', 'medical-booking-system'),
                'saved' => __('Saved successfully!', 'medical-booking-system'),
                'error_saving' => __('Error saving. Please try again.', 'medical-booking-system'),
            )
        ));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        MBS_Database::get_instance()->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log activation
        error_log('Medical Booking System activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Log deactivation
        error_log('Medical Booking System deactivated');
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'mbs_general_settings' => array(
                'clinic_name' => __('Medical Clinic', 'medical-booking-system'),
                'clinic_address' => '',
                'clinic_phone' => '',
                'clinic_email' => get_option('admin_email'),
                'timezone' => get_option('timezone_string'),
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'working_hours' => array(
                    'monday' => array('start' => '09:00', 'end' => '17:00'),
                    'tuesday' => array('start' => '09:00', 'end' => '17:00'),
                    'wednesday' => array('start' => '09:00', 'end' => '17:00'),
                    'thursday' => array('start' => '09:00', 'end' => '17:00'),
                    'friday' => array('start' => '09:00', 'end' => '17:00'),
                    'saturday' => array('start' => '09:00', 'end' => '13:00'),
                    'sunday' => array('start' => '', 'end' => ''),
                ),
                'slot_duration' => 30,
                'advance_booking_days' => 30,
                'cancellation_hours' => 24,
            ),
            'mbs_email_settings' => array(
                'enable_notifications' => true,
                'admin_email' => get_option('admin_email'),
                'from_name' => get_bloginfo('name'),
                'from_email' => get_option('admin_email'),
                'confirmation_subject' => __('Appointment Confirmation', 'medical-booking-system'),
                'reminder_subject' => __('Appointment Reminder', 'medical-booking-system'),
                'cancellation_subject' => __('Appointment Cancelled', 'medical-booking-system'),
            ),
            'mbs_display_settings' => array(
                'theme_color' => '#2563eb',
                'show_patient_dashboard' => true,
                'allow_online_cancellation' => true,
                'require_patient_registration' => false,
                'show_doctor_schedule' => true,
            )
        );
        
        foreach ($default_options as $option_name => $option_value) {
            if (!get_option($option_name)) {
                add_option($option_name, $option_value);
            }
        }
    }
}

/**
 * Initialize the plugin
 */
function medical_booking_system() {
    return MedicalBookingSystem::get_instance();
}

// Start the plugin
medical_booking_system();

/**
 * Helper function to get plugin option
 */
function mbs_get_option($option_name, $default = false) {
    return get_option($option_name, $default);
}

/**
 * Helper function to update plugin option
 */
function mbs_update_option($option_name, $value) {
    return update_option($option_name, $value);
}

/**
 * Helper function for translations
 */
function mbs__($text, $domain = 'medical-booking-system') {
    return __($text, $domain);
}

/**
 * Helper function for echo translations
 */
function mbs_e($text, $domain = 'medical-booking-system') {
    _e($text, $domain);
}

/**
 * Helper function for sprintf translations
 */
function mbs_sprintf($format, ...$args) {
    return sprintf($format, ...$args);
}
