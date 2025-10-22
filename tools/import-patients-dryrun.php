<?php
/**
 * Patient Import - DRY RUN (Preview Only)
 * 
 * This script previews the import without inserting into database.
 * Generates detailed log with all fixes and validations.
 * 
 * Usage: wp-cli or direct PHP execution
 * 
 * @package MedicalBookingSystem
 * @version 1.0
 */

// Prevent direct access if running in WordPress
if (!defined('ABSPATH') && php_sapi_name() !== 'cli') {
    die('Direct access not permitted');
}

// If running via CLI outside WordPress, load WordPress
if (php_sapi_name() === 'cli' && !defined('ABSPATH')) {
    // Adjust path to your wp-load.php
    require_once __DIR__ . '/../../../../wp-load.php';
}

class MBS_Patient_Import_DryRun {
    
    private $csv_file;
    private $log = [];
    private $stats = [
        'total' => 0,
        'valid' => 0,
        'errors' => 0,
        'warnings' => 0,
        'phone_fixed' => 0,
        'email_generated' => 0,
        'duplicates' => 0
    ];
    
    /**
     * Constructor
     */
    public function __construct($csv_file) {
        $this->csv_file = $csv_file;
    }
    
    /**
     * Run dry-run import
     */
    public function run() {
        $this->log("========================================");
        $this->log("MEDICAL BOOKING SYSTEM - PATIENT IMPORT");
        $this->log("DRY RUN MODE - NO DATABASE CHANGES");
        $this->log("========================================");
        $this->log("");
        $this->log("Start Time: " . date('Y-m-d H:i:s'));
        $this->log("CSV File: " . basename($this->csv_file));
        $this->log("");
        
        // Check if file exists
        if (!file_exists($this->csv_file)) {
            $this->log("ERROR: CSV file not found!", 'error');
            return false;
        }
        
        // Read CSV
        $patients = $this->read_csv();
        if (empty($patients)) {
            $this->log("ERROR: No patients found in CSV!", 'error');
            return false;
        }
        
        $this->stats['total'] = count($patients);
        $this->log("Total patients in CSV: {$this->stats['total']}");
        $this->log("");
        
        // Process each patient
        foreach ($patients as $index => $patient) {
            $this->process_patient($patient, $index + 1);
        }
        
        // Generate summary
        $this->generate_summary();
        
        // Save log to file
        $this->save_log();
        
        return true;
    }
    
    /**
     * Read CSV file
     */
    private function read_csv() {
        $patients = [];
        
        if (($handle = fopen($this->csv_file, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $patient = [];
                foreach ($headers as $i => $header) {
                    $patient[$header] = isset($data[$i]) ? trim($data[$i]) : '';
                }
                $patients[] = $patient;
            }
            fclose($handle);
        }
        
        return $patients;
    }
    
    /**
     * Process single patient
     */
    private function process_patient($patient, $row_number) {
        $this->log("----------------------------------------");
        $this->log("ROW #$row_number: {$patient['NUME']} {$patient['PRENUME']}");
        $this->log("----------------------------------------");
        
        $errors = [];
        $warnings = [];
        $fixes = [];
        
        // 1. Validate CNP
        $cnp = trim($patient['CNP']);
        if (!$this->validate_cnp($cnp)) {
            $errors[] = "CNP invalid: $cnp";
        } else {
            $this->log("✓ CNP: $cnp (valid)");
            
            // Check for duplicate in WordPress
            if ($this->check_duplicate_cnp($cnp)) {
                $warnings[] = "CNP deja exista in WordPress";
                $this->stats['duplicates']++;
            }
        }
        
        // 2. Process email
        $email = trim($patient['EMAIL']);
        if (empty($email)) {
            $email = $this->generate_email($cnp);
            $fixes[] = "Email generat: $email";
            $this->stats['email_generated']++;
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $warnings[] = "Email invalid: $email";
                $email = $this->generate_email($cnp);
                $fixes[] = "Email inlocuit cu: $email";
                $this->stats['email_generated']++;
            } else {
                $this->log("✓ Email: $email");
            }
        }
        
        // 3. Process phones
        $phones_raw = trim($patient['TELEFON']);
        $phones = $this->process_phones($phones_raw);
        
        if (empty($phones)) {
            $warnings[] = "Niciun telefon valid";
        } else {
            foreach ($phones as $i => $phone) {
                $this->log("✓ Telefon " . ($i + 1) . ": $phone");
            }
            
            if ($phones_raw !== implode(';', $phones) . ';') {
                $fixes[] = "Telefoane fixate: " . count($phones) . " numere valide";
                $this->stats['phone_fixed']++;
            }
        }
        
        // 4. Validate names
        $nume = trim($patient['NUME']);
        $prenume = trim($patient['PRENUME']);
        
        if (empty($nume) || empty($prenume)) {
            $errors[] = "Nume sau prenume lipsa";
        } else {
            $this->log("✓ Nume complet: $nume $prenume");
        }
        
        // 5. Display fixes
        if (!empty($fixes)) {
            $this->log("");
            $this->log("FIXES APPLIED:", 'info');
            foreach ($fixes as $fix) {
                $this->log("  → $fix", 'info');
            }
        }
        
        // 6. Display warnings
        if (!empty($warnings)) {
            $this->log("");
            $this->log("WARNINGS:", 'warning');
            foreach ($warnings as $warning) {
                $this->log("  ⚠ $warning", 'warning');
                $this->stats['warnings']++;
            }
        }
        
        // 7. Display errors
        if (!empty($errors)) {
            $this->log("");
            $this->log("ERRORS:", 'error');
            foreach ($errors as $error) {
                $this->log("  ✗ $error", 'error');
                $this->stats['errors']++;
            }
        } else {
            $this->stats['valid']++;
        }
        
        // 8. Show what would be created
        if (empty($errors)) {
            $this->log("");
            $this->log("WOULD CREATE:", 'success');
            $this->log("  • WordPress User:", 'success');
            $this->log("    - Username: $cnp", 'success');
            $this->log("    - Email: $email", 'success');
            $this->log("    - Display Name: $nume $prenume", 'success');
            $this->log("    - First Name: $prenume", 'success');
            $this->log("    - Last Name: $nume", 'success');
            $this->log("    - Role: mbs_patient", 'success');
            $this->log("  • User Meta:", 'success');
            $this->log("    - mbs_cnp: $cnp", 'success');
            if (!empty($phones)) {
                $this->log("  • Phones Table ($" . "wpdb->prefix . 'mbs_user_phones'):", 'success');
                foreach ($phones as $i => $phone) {
                    $is_primary = ($i === 0) ? 'YES' : 'NO';
                    $this->log("    - Phone $phone (Primary: $is_primary)", 'success');
                }
            }
            $this->log("  • Patient Record ($" . "wpdb->prefix . 'mbs_patients'):", 'success');
            $this->log("    - Link to WordPress user", 'success');
        }
        
        $this->log("");
    }
    
    /**
     * Validate CNP
     */
    private function validate_cnp($cnp) {
        // Basic validation: 13 digits
        if (!preg_match('/^[1-9]\d{12}$/', $cnp)) {
            return false;
        }
        
        // Algorithm validation
        $control_key = '279146358279';
        $sum = 0;
        
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$cnp[$i] * (int)$control_key[$i];
        }
        
        $control = $sum % 11;
        if ($control == 10) {
            $control = 1;
        }
        
        return $control == (int)$cnp[12];
    }
    
    /**
     * Check if CNP already exists in WordPress
     */
    private function check_duplicate_cnp($cnp) {
        global $wpdb;
        
        // Check in wp_users (username)
        $user = get_user_by('login', $cnp);
        if ($user) {
            return true;
        }
        
        // Check in user_meta
        $users = get_users([
            'meta_key' => 'mbs_cnp',
            'meta_value' => $cnp,
            'number' => 1
        ]);
        
        return !empty($users);
    }
    
    /**
     * Generate GDPR-compliant email
     */
    private function generate_email($cnp) {
        // Generate hash from CNP (first 8 chars of MD5)
        $hash = substr(md5($cnp), 0, 8);
        return "patient{$hash}@temp.clinic.ro";
    }
    
    /**
     * Process phone numbers
     */
    private function process_phones($phones_raw) {
        if (empty($phones_raw)) {
            return [];
        }
        
        // Fix common issues
        $phones_raw = str_replace(' sau ', ';', $phones_raw);
        $phones_raw = str_replace('\\', ';', $phones_raw);
        $phones_raw = str_replace('+40', '0', $phones_raw);
        $phones_raw = preg_replace('/\s+/', '', $phones_raw);
        
        // Split by semicolon
        $phones = explode(';', $phones_raw);
        
        $valid_phones = [];
        foreach ($phones as $phone) {
            $phone = trim($phone);
            if (empty($phone)) {
                continue;
            }
            
            // Remove non-digits
            $clean = preg_replace('/[^0-9]/', '', $phone);
            
            // Validate length (Romanian: 10 digits starting with 07)
            if (strlen($clean) === 10 && substr($clean, 0, 2) === '07') {
                $valid_phones[] = $clean;
            } elseif (strlen($clean) === 9 && substr($clean, 0, 1) === '7') {
                // Add leading 0
                $valid_phones[] = '0' . $clean;
            }
        }
        
        // Remove duplicates
        return array_unique($valid_phones);
    }
    
    /**
     * Generate summary
     */
    private function generate_summary() {
        $this->log("");
        $this->log("========================================");
        $this->log("IMPORT SUMMARY");
        $this->log("========================================");
        $this->log("");
        $this->log("Total Rows: {$this->stats['total']}");
        $this->log("Valid for Import: {$this->stats['valid']} (" . round(($this->stats['valid'] / $this->stats['total']) * 100, 2) . "%)");
        $this->log("Errors: {$this->stats['errors']}");
        $this->log("Warnings: {$this->stats['warnings']}");
        $this->log("");
        $this->log("Fixes Applied:");
        $this->log("  - Phone numbers fixed: {$this->stats['phone_fixed']}");
        $this->log("  - Emails generated: {$this->stats['email_generated']}");
        $this->log("");
        $this->log("Duplicates Found: {$this->stats['duplicates']}");
        $this->log("");
        
        if ($this->stats['errors'] > 0) {
            $this->log("⚠ {$this->stats['errors']} patients have ERRORS and will be SKIPPED", 'warning');
        }
        
        if ($this->stats['duplicates'] > 0) {
            $this->log("⚠ {$this->stats['duplicates']} patients already exist (will be SKIPPED)", 'warning');
        }
        
        $can_import = $this->stats['valid'] - $this->stats['duplicates'];
        $this->log("");
        $this->log("READY TO IMPORT: $can_import patients", 'success');
        $this->log("");
        $this->log("========================================");
        $this->log("DRY RUN COMPLETE - NO DATA INSERTED");
        $this->log("========================================");
        $this->log("");
        $this->log("End Time: " . date('Y-m-d H:i:s'));
    }
    
    /**
     * Log message
     */
    private function log($message, $type = 'info') {
        $prefix = '';
        switch ($type) {
            case 'error':
                $prefix = '[ERROR] ';
                break;
            case 'warning':
                $prefix = '[WARNING] ';
                break;
            case 'success':
                $prefix = '[SUCCESS] ';
                break;
        }
        
        $log_line = $prefix . $message;
        $this->log[] = $log_line;
        
        // Output to console if CLI
        if (php_sapi_name() === 'cli') {
            echo $log_line . PHP_EOL;
        }
    }
    
    /**
     * Save log to file
     */
    private function save_log() {
        $log_dir = __DIR__ . '/logs';
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_file = $log_dir . '/import-dryrun-' . date('Y-m-d-His') . '.log';
        file_put_contents($log_file, implode(PHP_EOL, $this->log));
        
        $this->log("Log saved to: " . basename($log_file));
        echo "Log saved to: $log_file" . PHP_EOL;
    }
}

// Run if called directly via CLI
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $csv_file = 'E:/Downloads/ulieru-pacienti-temp.csv';
    
    if (!file_exists($csv_file)) {
        echo "ERROR: CSV file not found: $csv_file\n";
        exit(1);
    }
    
    $importer = new MBS_Patient_Import_DryRun($csv_file);
    $importer->run();
}

