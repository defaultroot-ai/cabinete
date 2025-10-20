<?php
/**
 * Database Management Class
 * 
 * @package MedicalBookingSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MBS_Database {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Database version
     */
    private $db_version = '1.1.0';
    
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
        add_action('init', array($this, 'check_database_version'));
    }
    
    /**
     * Check database version and update if needed
     */
    public function check_database_version() {
        $installed_version = get_option('mbs_db_version', '0');
        
        if ($installed_version !== $this->db_version) {
            $this->create_tables();
            update_option('mbs_db_version', $this->db_version);
        }
    }
    
    /**
     * Create database tables
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table: Services
        $table_services = $wpdb->prefix . 'mbs_services';
        $sql_services = "CREATE TABLE $table_services (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            duration int(11) NOT NULL DEFAULT 30,
            price decimal(10,2) DEFAULT 0.00,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Table: Doctors
        $table_doctors = $wpdb->prefix . 'mbs_doctors';
        $sql_doctors = "CREATE TABLE $table_doctors (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            specialty varchar(255),
            phone varchar(20),
            email varchar(255),
            bio text,
            profile_image varchar(255),
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        // Table: Doctor Services (Many-to-Many relationship)
        $table_doctor_services = $wpdb->prefix . 'mbs_doctor_services';
        $sql_doctor_services = "CREATE TABLE $table_doctor_services (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            service_id int(11) NOT NULL,
            price decimal(10,2) DEFAULT 0.00,
            duration int(11) DEFAULT 30,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY doctor_service (doctor_id, service_id),
            KEY doctor_id (doctor_id),
            KEY service_id (service_id)
        ) $charset_collate;";
        
        // Table: Doctor Schedules
        $table_doctor_schedules = $wpdb->prefix . 'mbs_doctor_schedules';
        $sql_doctor_schedules = "CREATE TABLE $table_doctor_schedules (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            day_of_week tinyint(1) NOT NULL COMMENT '0=Sunday, 1=Monday, etc.',
            start_time time NOT NULL,
            end_time time NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doctor_id (doctor_id),
            KEY day_of_week (day_of_week)
        ) $charset_collate;";
        
        // Table: Doctor Breaks/Holidays
        $table_doctor_breaks = $wpdb->prefix . 'mbs_doctor_breaks';
        $sql_doctor_breaks = "CREATE TABLE $table_doctor_breaks (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            break_date date NOT NULL,
            start_time time,
            end_time time,
            reason varchar(255),
            is_all_day tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doctor_id (doctor_id),
            KEY break_date (break_date)
        ) $charset_collate;";
        
        // Table: Patients
        $table_patients = $wpdb->prefix . 'mbs_patients';
        $sql_patients = "CREATE TABLE $table_patients (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11),
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            phone varchar(20),
            email varchar(255),
            cnp varchar(13),
            birth_date date,
            address text,
            emergency_contact_name varchar(255),
            emergency_contact_phone varchar(20),
            medical_notes text,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY cnp (cnp),
            KEY phone (phone)
        ) $charset_collate;";
        
        // Table: Appointments
        $table_appointments = $wpdb->prefix . 'mbs_appointments';
        $sql_appointments = "CREATE TABLE $table_appointments (
            id int(11) NOT NULL AUTO_INCREMENT,
            appointment_code varchar(20) NOT NULL,
            doctor_id int(11) NOT NULL,
            patient_id int(11) NOT NULL,
            service_id int(11) NOT NULL,
            appointment_date date NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            status enum('pending','confirmed','completed','cancelled','no_show') DEFAULT 'pending',
            notes text,
            patient_notes text,
            price decimal(10,2) DEFAULT 0.00,
            payment_status enum('unpaid','paid','partial','refunded') DEFAULT 'unpaid',
            created_by int(11),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY appointment_code (appointment_code),
            KEY doctor_id (doctor_id),
            KEY patient_id (patient_id),
            KEY service_id (service_id),
            KEY appointment_date (appointment_date),
            KEY status (status)
        ) $charset_collate;";
        
        // Table: Appointment History (for audit trail)
        $table_appointment_history = $wpdb->prefix . 'mbs_appointment_history';
        $sql_appointment_history = "CREATE TABLE $table_appointment_history (
            id int(11) NOT NULL AUTO_INCREMENT,
            appointment_id int(11) NOT NULL,
            action varchar(50) NOT NULL,
            old_value text,
            new_value text,
            changed_by int(11),
            changed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY changed_by (changed_by)
        ) $charset_collate;";
        
        // Table: Notifications
        $table_notifications = $wpdb->prefix . 'mbs_notifications';
        $sql_notifications = "CREATE TABLE $table_notifications (
            id int(11) NOT NULL AUTO_INCREMENT,
            appointment_id int(11) NOT NULL,
            type enum('confirmation','reminder','cancellation','reschedule') NOT NULL,
            method enum('email','sms','both') DEFAULT 'email',
            recipient_email varchar(255),
            recipient_phone varchar(20),
            subject varchar(255),
            message text,
            sent_at datetime,
            status enum('pending','sent','failed') DEFAULT 'pending',
            error_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY appointment_id (appointment_id),
            KEY type (type),
            KEY status (status)
        ) $charset_collate;";
        
        // Table: Settings
        $table_settings = $wpdb->prefix . 'mbs_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value longtext,
            setting_type varchar(20) DEFAULT 'string',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        // Table: User Phones (for multi-phone authentication)
        $table_user_phones = $wpdb->prefix . 'mbs_user_phones';
        $sql_user_phones = "CREATE TABLE $table_user_phones (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            phone varchar(20) NOT NULL,
            is_primary tinyint(1) DEFAULT 0,
            is_verified tinyint(1) DEFAULT 0,
            verification_code varchar(10),
            verification_expires datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_phone (user_id, phone),
            KEY phone (phone),
            KEY user_id (user_id),
            KEY is_verified (is_verified)
        ) $charset_collate;";
        
        // Execute table creation
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_services);
        dbDelta($sql_doctors);
        dbDelta($sql_doctor_services);
        dbDelta($sql_doctor_schedules);
        dbDelta($sql_doctor_breaks);
        dbDelta($sql_patients);
        dbDelta($sql_appointments);
        dbDelta($sql_appointment_history);
        dbDelta($sql_notifications);
        dbDelta($sql_settings);
        dbDelta($sql_user_phones);
        
        // Create user roles
        $this->create_user_roles();
        
        // Insert default data
        $this->insert_default_data();
        
        error_log('Medical Booking System: Database tables created successfully');
    }
    
    /**
     * Create custom user roles
     */
    private function create_user_roles() {
        // Patient Role
        add_role('mbs_patient', __('Patient', 'medical-booking-system'), array(
            'read' => true,
            'mbs_view_own_appointments' => true,
            'mbs_cancel_own_appointments' => true,
        ));
        
        // Receptionist Role
        add_role('mbs_receptionist', __('Receptionist', 'medical-booking-system'), array(
            'read' => true,
            'mbs_view_all_appointments' => true,
            'mbs_create_appointments' => true,
            'mbs_edit_appointments' => true,
            'mbs_cancel_appointments' => true,
            'mbs_view_patients' => true,
            'mbs_create_patients' => true,
            'mbs_edit_patients' => true,
            'mbs_view_doctors' => true,
            'mbs_view_services' => true,
        ));
        
        // Assistant Role
        add_role('mbs_assistant', __('Medical Assistant', 'medical-booking-system'), array(
            'read' => true,
            // Appointments
            'mbs_view_all_appointments' => true,
            'mbs_view_own_appointments' => true,
            'mbs_create_appointments' => true,
            'mbs_edit_appointments' => true,
            'mbs_cancel_appointments' => true,
            // Patients
            'mbs_view_patients' => true,
            'mbs_create_patients' => true,
            'mbs_edit_patients' => true,
            // Catalog
            'mbs_view_doctors' => true,
            'mbs_view_services' => true,
        ));
        
        // Doctor Role
        add_role('mbs_doctor', __('Doctor', 'medical-booking-system'), array(
            'read' => true,
            'mbs_view_own_appointments' => true,
            'mbs_view_all_appointments' => true,
            'mbs_edit_own_appointments' => true,
            'mbs_view_patients' => true,
            'mbs_edit_patients' => true,
            'mbs_manage_own_schedule' => true,
            'mbs_view_services' => true,
        ));
        
        // Manager Role
        add_role('mbs_manager', __('Manager', 'medical-booking-system'), array(
            'read' => true,
            'mbs_view_all_appointments' => true,
            'mbs_create_appointments' => true,
            'mbs_edit_appointments' => true,
            'mbs_cancel_appointments' => true,
            'mbs_view_patients' => true,
            'mbs_create_patients' => true,
            'mbs_edit_patients' => true,
            'mbs_delete_patients' => true,
            'mbs_view_doctors' => true,
            'mbs_create_doctors' => true,
            'mbs_edit_doctors' => true,
            'mbs_delete_doctors' => true,
            'mbs_view_services' => true,
            'mbs_create_services' => true,
            'mbs_edit_services' => true,
            'mbs_delete_services' => true,
            'mbs_manage_settings' => true,
            'mbs_view_reports' => true,
        ));
        
        // Add capabilities to Administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_capabilities = array(
                'mbs_view_all_appointments',
                'mbs_create_appointments',
                'mbs_edit_appointments',
                'mbs_cancel_appointments',
                'mbs_view_patients',
                'mbs_create_patients',
                'mbs_edit_patients',
                'mbs_delete_patients',
                'mbs_view_doctors',
                'mbs_create_doctors',
                'mbs_edit_doctors',
                'mbs_delete_doctors',
                'mbs_view_services',
                'mbs_create_services',
                'mbs_edit_services',
                'mbs_delete_services',
                'mbs_manage_settings',
                'mbs_view_reports',
            );
            
            foreach ($admin_capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
    }
    
    /**
     * Insert default data
     */
    private function insert_default_data() {
        global $wpdb;
        
        // Insert default services
        $table_services = $wpdb->prefix . 'mbs_services';
        $default_services = array(
            array(
                'name' => __('General Consultation', 'medical-booking-system'),
                'description' => __('Standard medical consultation', 'medical-booking-system'),
                'duration' => 30,
                'price' => 0.00
            ),
            array(
                'name' => __('Cardiology Consultation', 'medical-booking-system'),
                'description' => __('Complete cardiology evaluation', 'medical-booking-system'),
                'duration' => 45,
                'price' => 0.00
            ),
            array(
                'name' => __('Medical Tests', 'medical-booking-system'),
                'description' => __('Sample collection for medical tests', 'medical-booking-system'),
                'duration' => 15,
                'price' => 0.00
            ),
            array(
                'name' => __('Periodic Check-up', 'medical-booking-system'),
                'description' => __('General health status verification', 'medical-booking-system'),
                'duration' => 20,
                'price' => 0.00
            )
        );
        
        foreach ($default_services as $service) {
            $wpdb->insert($table_services, $service);
        }
        
        // Insert default settings
        $table_settings = $wpdb->prefix . 'mbs_settings';
        $default_settings = array(
            array('setting_key' => 'clinic_name', 'setting_value' => __('Medical Clinic', 'medical-booking-system')),
            array('setting_key' => 'clinic_address', 'setting_value' => ''),
            array('setting_key' => 'clinic_phone', 'setting_value' => ''),
            array('setting_key' => 'clinic_email', 'setting_value' => get_option('admin_email')),
            array('setting_key' => 'slot_duration', 'setting_value' => '30'),
            array('setting_key' => 'advance_booking_days', 'setting_value' => '30'),
            array('setting_key' => 'cancellation_hours', 'setting_value' => '24'),
            array('setting_key' => 'enable_notifications', 'setting_value' => '1'),
            array('setting_key' => 'theme_color', 'setting_value' => '#2563eb'),
        );
        
        foreach ($default_settings as $setting) {
            // Use REPLACE to avoid duplicate key warnings on re-activations
            $wpdb->replace($table_settings, $setting);
        }
    }
    
    /**
     * Drop all tables (for uninstall)
     */
    public function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'mbs_notifications',
            $wpdb->prefix . 'mbs_appointment_history',
            $wpdb->prefix . 'mbs_appointments',
            $wpdb->prefix . 'mbs_patients',
            $wpdb->prefix . 'mbs_doctor_breaks',
            $wpdb->prefix . 'mbs_doctor_schedules',
            $wpdb->prefix . 'mbs_doctor_services',
            $wpdb->prefix . 'mbs_doctors',
            $wpdb->prefix . 'mbs_services',
            $wpdb->prefix . 'mbs_settings',
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        // Remove user roles
        remove_role('mbs_patient');
        remove_role('mbs_receptionist');
        remove_role('mbs_assistant');
        remove_role('mbs_doctor');
        remove_role('mbs_manager');
        
        // Remove capabilities from Administrator
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_capabilities = array(
                'mbs_view_all_appointments',
                'mbs_create_appointments',
                'mbs_edit_appointments',
                'mbs_cancel_appointments',
                'mbs_view_patients',
                'mbs_create_patients',
                'mbs_edit_patients',
                'mbs_delete_patients',
                'mbs_view_doctors',
                'mbs_create_doctors',
                'mbs_edit_doctors',
                'mbs_delete_doctors',
                'mbs_view_services',
                'mbs_create_services',
                'mbs_edit_services',
                'mbs_delete_services',
                'mbs_manage_settings',
                'mbs_view_reports',
            );
            
            foreach ($admin_capabilities as $cap) {
                $admin_role->remove_cap($cap);
            }
        }
        
        // Remove options
        delete_option('mbs_db_version');
    }
}
