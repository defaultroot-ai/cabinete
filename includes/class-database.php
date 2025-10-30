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
    private $db_version = '1.7.0';
    
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
            age int(3) DEFAULT NULL,
            gender enum('M','F') DEFAULT NULL,
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

        // Table: Families
        $table_families = $wpdb->prefix . 'mbs_families';
        $sql_families = "CREATE TABLE $table_families (
            id int(11) NOT NULL AUTO_INCREMENT,
            family_name varchar(255) NOT NULL,
            head_patient_id int(11) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY head_patient_id (head_patient_id),
            KEY family_name (family_name)
        ) $charset_collate;";

        // Table: Family Members
        $table_family_members = $wpdb->prefix . 'mbs_family_members';
        $sql_family_members = "CREATE TABLE $table_family_members (
            id int(11) NOT NULL AUTO_INCREMENT,
            family_id int(11) NOT NULL,
            patient_id int(11) NOT NULL,
            relationship_type enum('head','spouse','child','parent','sibling','other') NOT NULL,
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY family_id (family_id),
            KEY patient_id (patient_id),
            KEY relationship_type (relationship_type)
        ) $charset_collate;";
        
        // Table: Doctor Slot Settings (NEW - for enhanced slot configuration)
        $table_doctor_slot_settings = $wpdb->prefix . 'mbs_doctor_slot_settings';
        $sql_doctor_slot_settings = "CREATE TABLE $table_doctor_slot_settings (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            service_id int(11) NOT NULL,
            slot_interval int(11) DEFAULT 30 COMMENT 'Intervalul între sloturi în minute',
            buffer_time int(11) DEFAULT 0 COMMENT 'Timp buffer între programări în minute',
            max_advance_days int(11) DEFAULT 30 COMMENT 'Câte zile în avans se pot face programări',
            min_advance_hours int(11) DEFAULT 2 COMMENT 'Minim câte ore în avans',
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY doctor_service (doctor_id, service_id),
            KEY doctor_id (doctor_id),
            KEY service_id (service_id)
        ) $charset_collate;";
        
        // Table: Hidden Slots and Staff Only Slots (NEW - for slot management)
        $table_hidden_slots = $wpdb->prefix . 'mbs_hidden_slots';
        $sql_hidden_slots = "CREATE TABLE $table_hidden_slots (
            id int(11) NOT NULL AUTO_INCREMENT,
            doctor_id int(11) NOT NULL,
            slot_time time NOT NULL COMMENT 'Ora slotului (ex: 12:00)',
            day_of_week tinyint(1) NULL COMMENT 'Ziua săptămânii (0=Duminică, 1=Luni, etc.)',
            specific_date date NULL COMMENT 'Data specifică pentru ascundere',
            reason varchar(255) DEFAULT 'Slot ascuns' COMMENT 'Motivul ascunderii',
            is_recurring tinyint(1) DEFAULT 0 COMMENT 'Dacă se repetă săptămânal',
            slot_type enum('hidden', 'staff_only') DEFAULT 'hidden' COMMENT 'Tipul slotului',
            staff_notes text NULL COMMENT 'Note pentru staff (doar pentru staff_only)',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY doctor_id (doctor_id),
            KEY day_of_week (day_of_week),
            KEY specific_date (specific_date),
            KEY slot_type (slot_type)
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
        dbDelta($sql_families);
        dbDelta($sql_family_members);
        
        // Execute new slot management tables
        $result_slot_settings = dbDelta($sql_doctor_slot_settings);
        $result_hidden_slots = dbDelta($sql_hidden_slots);
        
        // Debug logging for new tables
        error_log('Medical Booking System: Slot Settings table creation result: ' . print_r($result_slot_settings, true));
        error_log('Medical Booking System: Hidden Slots table creation result: ' . print_r($result_hidden_slots, true));
        
        // Add gender column to existing patients table if it doesn't exist
        $this->add_gender_column_to_patients();
        
        // Create user roles
        $this->create_user_roles();
        
        // Insert default data
        $this->insert_default_data();
        
        error_log('Medical Booking System: Database tables created successfully');
    }
    
    /**
     * Add gender and age columns to existing patients table
     */
    private function add_gender_column_to_patients() {
        global $wpdb;
        
        $table_patients = $wpdb->prefix . 'mbs_patients';
        
        // Check if gender column exists
        $gender_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_patients LIKE 'gender'");
        if (empty($gender_exists)) {
            $wpdb->query("ALTER TABLE $table_patients ADD COLUMN gender enum('M','F') DEFAULT NULL AFTER birth_date");
            error_log('Medical Booking System: Added gender column to patients table');
        }
        
        // Check if age column exists
        $age_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_patients LIKE 'age'");
        if (empty($age_exists)) {
            $wpdb->query("ALTER TABLE $table_patients ADD COLUMN age int(3) DEFAULT NULL AFTER birth_date");
            error_log('Medical Booking System: Added age column to patients table');
        }
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
        
        // Insert default services - DISABLED
        // Serviciile trebuie adăugate manual prin admin panel
        // pentru a evita instalarea automată de servicii nedorite
        /*
        $table_services = $wpdb->prefix . 'mbs_services';
        $default_services = array(
            array(
                'name' => 'Consultație Generală',
                'description' => 'Consultație medicală standard',
                'duration' => 30,
                'price' => 0.00
            ),
            array(
                'name' => 'Consultație Cardiologie',
                'description' => 'Evaluare completă cardiologică',
                'duration' => 45,
                'price' => 0.00
            ),
            array(
                'name' => 'Analize Medicale',
                'description' => 'Recoltare probe pentru analize',
                'duration' => 15,
                'price' => 0.00
            ),
            array(
                'name' => 'Control Periodic',
                'description' => 'Verificare stare generală de sănătate',
                'duration' => 20,
                'price' => 0.00
            )
        );
        
        foreach ($default_services as $service) {
            $wpdb->insert($table_services, $service);
        }
        */
        
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
        
        // Insert default slot settings for existing doctors and services
        $this->insert_default_slot_settings();
    }
    
    /**
     * Insert default slot settings for existing doctors and services
     */
    private function insert_default_slot_settings() {
        global $wpdb;
        
        $table_slot_settings = $wpdb->prefix . 'mbs_doctor_slot_settings';
        $table_doctors = $wpdb->prefix . 'mbs_doctors';
        $table_services = $wpdb->prefix . 'mbs_services';
        
        // Check if table exists before inserting
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_slot_settings'");
        if (!$table_exists) {
            error_log('Medical Booking System: Slot settings table does not exist, skipping default data insertion');
            return;
        }
        
        // Get all doctors and services
        $doctors = $wpdb->get_results("SELECT id FROM $table_doctors WHERE is_active = 1", ARRAY_A);
        $services = $wpdb->get_results("SELECT id FROM $table_services WHERE is_active = 1", ARRAY_A);
        
        $inserted_count = 0;
        
        // Create default slot settings for each doctor-service combination
        foreach ($doctors as $doctor) {
            foreach ($services as $service) {
                $default_settings = array(
                    'doctor_id' => $doctor['id'],
                    'service_id' => $service['id'],
                    'slot_interval' => 30, // Default 30 minutes
                    'buffer_time' => 0,    // No buffer by default
                    'max_advance_days' => 30, // 30 days in advance
                    'min_advance_hours' => 2,  // 2 hours minimum
                    'is_active' => 1
                );
                
                // Use REPLACE to avoid duplicate key warnings
                $result = $wpdb->replace($table_slot_settings, $default_settings);
                if ($result !== false) {
                    $inserted_count++;
                }
            }
        }
        
        error_log("Medical Booking System: Inserted $inserted_count default slot settings");
    }
    
    /**
     * Drop all tables (for uninstall)
     */
    public function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'mbs_hidden_slots',           // NEW - Hidden slots table
            $wpdb->prefix . 'mbs_doctor_slot_settings',  // NEW - Slot settings table
            $wpdb->prefix . 'mbs_family_members',        // Family members
            $wpdb->prefix . 'mbs_families',              // Families
            $wpdb->prefix . 'mbs_user_phones',           // User phones
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
