<?php
if (!defined('ABSPATH')) { exit; }

class MBS_Admin {
	private static $instance = null;
	public static function get_instance() {
		if (null === self::$instance) { self::$instance = new self(); }
		return self::$instance;
	}
	private function __construct() {
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('wp_ajax_mbs_check_cnp_duplicate', array($this, 'ajax_check_cnp_duplicate'));
		add_action('wp_ajax_mbs_search_patients', array($this, 'ajax_search_patients'));
	}
	
	public function add_admin_menu() {
		add_menu_page(
			__('Medical Booking', 'medical-booking-system'),
			__('Medical Booking', 'medical-booking-system'),
			'manage_options',
			'medical-booking',
			array($this, 'render_dashboard'),
			'dashicons-calendar-alt',
			30
		);
		
		add_submenu_page(
			'medical-booking',
			__('Dashboard', 'medical-booking-system'),
			__('Dashboard', 'medical-booking-system'),
			'manage_options',
			' medical-booking',
			array($this, 'render_dashboard')
		);
		
		add_submenu_page(
			'medical-booking',
			__('Appointments', 'medical-booking-system'),
			__('Appointments', 'medical-booking-system'),
			'mbs_view_all_appointments',
			'medical-booking-appointments',
			array($this, 'render_appointments')
		);
		
		add_submenu_page(
			'medical-booking',
			__('Doctors', 'medical-booking-system'),
			__('Doctors', 'medical-booking-system'),
			'mbs_view_doctors',
			'medical-booking-doctors',
			array($this, 'render_doctors')
		);
		
		add_submenu_page(
			'medical-booking',
			__('Services', 'medical-booking-system'),
			__('Services', 'medical-booking-system'),
			'mbs_view_services',
			'medical-booking-services',
			array($this, 'render_services')
		);

		add_submenu_page(
			'medical-booking',
			__('Patients', 'medical-booking-system'),
			__('Patients', 'medical-booking-system'),
			'mbs_view_patients',
			'medical-booking-patients',
			array($this, 'render_patients')
		);
		
		add_submenu_page(
			'medical-booking',
			__('Families', 'medical-booking-system'),
			__('Families', 'medical-booking-system'),
			'mbs_view_patients',
			'medical-booking-families',
			array($this, 'render_families')
		);
		
		// Settings submenu is registered by MBS_Settings to avoid duplicates
	}
	
	public function render_dashboard() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Medical Booking Dashboard', 'medical-booking-system') . '</h1>';
		echo '<p>' . esc_html__('Welcome to Medical Booking System.', 'medical-booking-system') . '</p>';
		
		global $wpdb;
		$total_appointments = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mbs_appointments");
		$total_doctors = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mbs_doctors WHERE is_active=1");
		$total_services = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mbs_services WHERE is_active=1");
		
		echo '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:20px;">';
		echo '<div style="background:#fff;padding:20px;border:1px solid #ccc;border-radius:5px;"><h2>' . intval($total_appointments) . '</h2><p>Total Appointments</p></div>';
		echo '<div style="background:#fff;padding:20px;border:1px solid #ccc;border-radius:5px;"><h2>' . intval($total_doctors) . '</h2><p>Active Doctors</p></div>';
		echo '<div style="background:#fff;padding:20px;border:1px solid #ccc;border-radius:5px;"><h2>' . intval($total_services) . '</h2><p>Active Services</p></div>';
		echo '</div>';
		echo '</div>';
	}
	
	public function render_appointments() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Appointments', 'medical-booking-system') . '</h1>';
		
		global $wpdb;
		$appointments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mbs_appointments ORDER BY appointment_date DESC, start_time DESC LIMIT 50", ARRAY_A);
		
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>Code</th><th>Doctor</th><th>Patient</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody>';
		foreach ($appointments as $a) {
			echo '<tr>';
			echo '<td>' . esc_html($a['appointment_code']) . '</td>';
			echo '<td>Doctor ID ' . intval($a['doctor_id']) . '</td>';
			echo '<td>Patient ID ' . intval($a['patient_id']) . '</td>';
			echo '<td>Service ID ' . intval($a['service_id']) . '</td>';
			echo '<td>' . esc_html($a['appointment_date']) . '</td>';
			echo '<td>' . esc_html($a['start_time']) . ' - ' . esc_html($a['end_time']) . '</td>';
			echo '<td>' . esc_html($a['status']) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
	}
	
	public function render_doctors() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Doctors', 'medical-booking-system') . '</h1>';
		
		if (isset($_POST['add_doctor_nonce']) && wp_verify_nonce($_POST['add_doctor_nonce'], 'add_doctor_action')) {
			global $wpdb;
			$wpdb->insert($wpdb->prefix . 'mbs_doctors', array(
				'user_id' => 0,
				'first_name' => sanitize_text_field($_POST['first_name']),
				'last_name' => sanitize_text_field($_POST['last_name']),
				'specialty' => sanitize_text_field($_POST['specialty']),
				'phone' => sanitize_text_field($_POST['phone']),
				'email' => sanitize_email($_POST['email']),
				'is_active' => 1
			));
			echo '<div class="notice notice-success"><p>Doctor added successfully!</p></div>';
		}
		
		echo '<h2>Add New Doctor</h2>';
		echo '<form method="post" style="background:#fff;padding:20px;border:1px solid #ccc;max-width:600px;">';
		wp_nonce_field('add_doctor_action', 'add_doctor_nonce');
		echo '<p><label>First Name: <input type="text" name="first_name" required style="width:100%;"></label></p>';
		echo '<p><label>Last Name: <input type="text" name="last_name" required style="width:100%;"></label></p>';
		echo '<p><label>Specialty: <input type="text" name="specialty" style="width:100%;"></label></p>';
		echo '<p><label>Phone: <input type="text" name="phone" style="width:100%;"></label></p>';
		echo '<p><label>Email: <input type="email" name="email" style="width:100%;"></label></p>';
		echo '<p><button type="submit" class="button button-primary">Add Doctor</button></p>';
		echo '</form>';
		
		global $wpdb;
		$doctors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mbs_doctors ORDER BY id DESC", ARRAY_A);
		echo '<h2 style="margin-top:30px;">Existing Doctors</h2>';
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>ID</th><th>Name</th><th>Specialty</th><th>Phone</th><th>Email</th><th>Active</th></tr></thead><tbody>';
		foreach ($doctors as $d) {
			echo '<tr>';
			echo '<td>' . intval($d['id']) . '</td>';
			echo '<td>' . esc_html($d['first_name'] . ' ' . $d['last_name']) . '</td>';
			echo '<td>' . esc_html($d['specialty']) . '</td>';
			echo '<td>' . esc_html($d['phone']) . '</td>';
			echo '<td>' . esc_html($d['email']) . '</td>';
			echo '<td>' . ($d['is_active'] ? 'Yes' : 'No') . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
	}
	
	public function render_services() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Services', 'medical-booking-system') . '</h1>';
		
		global $wpdb;
		$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mbs_services ORDER BY id DESC", ARRAY_A);
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>ID</th><th>Name</th><th>Duration (min)</th><th>Price</th><th>Active</th></tr></thead><tbody>';
		foreach ($services as $s) {
			echo '<tr>';
			echo '<td>' . intval($s['id']) . '</td>';
			echo '<td>' . esc_html($s['name']) . '</td>';
			echo '<td>' . intval($s['duration']) . '</td>';
			echo '<td>' . esc_html($s['price']) . '</td>';
			echo '<td>' . ($s['is_active'] ? 'Yes' : 'No') . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '</div>';
	}

	public function render_patients() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Patients', 'medical-booking-system') . '</h1>';
		echo '<style>
		.mbs-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:24px;margin:20px 0;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
		.mbs-toolbar{display:flex;gap:12px;align-items:center;margin:12px 0}
		.mbs-badge{display:inline-block;padding:4px 12px;border-radius:20px;background:#f0f0f1;color:#1d2327;font-size:12px;font-weight:500}
		.mbs-badge--primary{background:#2271b1;color:#fff}
		.mbs-muted{color:#50575e;font-size:14px}
		.mbs-table thead th{font-weight:600;background:#f8f9fa;border-bottom:2px solid #e1e5e9}
		.mbs-table td,.mbs-table th{padding:12px 16px;border-bottom:1px solid #f0f0f1}
		.mbs-actions a{margin-right:8px}
		.mbs-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:20px}
		.mbs-form-section{background:#f8f9fa;padding:20px;border-radius:6px;border-left:4px solid #2271b1}
		.mbs-form-section h3{margin:0 0 16px 0;color:#1d2327;font-size:16px;font-weight:600}
		.mbs-form-row{display:flex;gap:16px;margin-bottom:16px}
		.mbs-form-field{flex:1}
		.mbs-form-field label{display:block;margin-bottom:6px;font-weight:500;color:#1d2327}
		.mbs-form-field input,.mbs-form-field select,.mbs-form-field textarea{width:100%;padding:10px 12px;border:1px solid #dcdcde;border-radius:4px;font-size:14px;transition:border-color 0.2s}
		.mbs-form-field input:focus,.mbs-form-field select:focus,.mbs-form-field textarea:focus{outline:none;border-color:#2271b1;box-shadow:0 0 0 2px rgba(34,113,177,0.1)}
		.mbs-form-field .description{font-size:12px;color:#666;margin-top:4px}
		.mbs-form-actions{grid-column:1/-1;margin-top:24px;padding-top:20px;border-top:1px solid #e1e5e9;text-align:right}
		.mbs-phone-list{background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:16px;margin-top:8px}
		.mbs-phone-item{display:flex;gap:12px;align-items:center;margin-bottom:12px;padding:12px;background:#f8f9fa;border:1px solid #e1e5e9;border-radius:6px;transition:all 0.2s ease}
		.mbs-phone-item:hover{background:#f0f0f1;border-color:#dcdcde}
		.mbs-phone-item:last-child{margin-bottom:0}
		.mbs-phone-item .phone-input{flex:1;padding:10px 12px;border:1px solid #dcdcde;border-radius:4px;font-size:14px;transition:border-color 0.2s}
		.mbs-phone-item .phone-input:focus{outline:none;border-color:#2271b1;box-shadow:0 0 0 2px rgba(34,113,177,0.1)}
		.mbs-phone-controls{display:flex;align-items:center;gap:8px}
		.mbs-phone-primary{display:flex;align-items:center;gap:6px;padding:8px 12px;background:#fff;border:1px solid #dcdcde;border-radius:4px;cursor:pointer;transition:all 0.2s}
		.mbs-phone-primary:hover{background:#f8f9fa;border-color:#2271b1}
		.mbs-phone-primary input[type="checkbox"]{margin:0;width:16px;height:16px;cursor:pointer}
		.mbs-phone-primary label{margin:0;font-size:13px;font-weight:500;color:#1d2327;cursor:pointer}
		.mbs-phone-primary.checked{background:#e7f3ff;border-color:#2271b1;color:#2271b1}
		.mbs-phone-primary.checked label{color:#2271b1}
		.mbs-phone-remove{display:flex;align-items:center;justify-content:center;width:32px;height:32px;background:#fff;border:1px solid #dcdcde;border-radius:4px;color:#d63638;cursor:pointer;transition:all 0.2s;font-size:16px;font-weight:bold}
		.mbs-phone-remove:hover{background:#d63638;color:#fff;border-color:#d63638}
		.mbs-add-phone{display:inline-flex;align-items:center;gap:6px;color:#2271b1;cursor:pointer;font-size:13px;font-weight:500;margin-top:12px;padding:8px 12px;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:4px;transition:all 0.2s}
		.mbs-add-phone:hover{background:#e7f3ff;border-color:#2271b1}
		.mbs-add-phone::before{content:"+";font-size:16px;font-weight:bold}
		.mbs-calendar{position:absolute;background:#fff;border:1px solid #dcdcde;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:1000;padding:16px;min-width:280px}
		.mbs-calendar-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
		.mbs-calendar-nav{background:none;border:none;font-size:18px;cursor:pointer;padding:4px 8px;border-radius:4px;color:#2271b1}
		.mbs-calendar-nav:hover{background:#f0f0f1}
		.mbs-calendar-title{font-weight:600;font-size:16px}
		.mbs-calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:2px}
		.mbs-calendar-day-header{text-align:center;font-weight:600;font-size:12px;color:#666;padding:8px 4px}
		.mbs-calendar-day{text-align:center;padding:8px 4px;cursor:pointer;border-radius:4px;font-size:14px}
		.mbs-calendar-day:hover{background:#f0f0f1}
		.mbs-calendar-day.other-month{color:#ccc}
		.mbs-calendar-day.today{background:#2271b1;color:#fff;font-weight:600}
		.mbs-calendar-day.selected{background:#00a32a;color:#fff}
		.mbs-calendar-day.disabled{color:#ccc;cursor:not-allowed}
		.mbs-calendar-day.disabled:hover{background:none}
		</style>';

		// Tabs
		$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';
		$base_url = add_query_arg(array('page' => 'medical-booking-patients'), admin_url('admin.php'));
		echo '<h2 class="nav-tab-wrapper" style="margin-top:15px;">';
		echo '<a class="nav-tab ' . ($current_tab === 'list' ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'list', $base_url)) . '">' . esc_html__('List', 'medical-booking-system') . '</a>';
		echo '<a class="nav-tab ' . ($current_tab === 'add' ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'add', $base_url)) . '">' . esc_html__('Add New', 'medical-booking-system') . '</a>';
		echo '</h2>';

		// Normalize Romanian/Hungarian names (remove diacritics)
		function normalize_name($name) {
			// Romanian diacritics
			$romanian = array(
				'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ț' => 't',
				'Ă' => 'A', 'Â' => 'A', 'Î' => 'I', 'Ș' => 'S', 'Ț' => 'T'
			);
			
			// Hungarian diacritics
			$hungarian = array(
				'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ö' => 'o', 'ő' => 'o', 'ü' => 'u', 'ű' => 'u',
				'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ö' => 'O', 'Ő' => 'O', 'Ü' => 'U', 'Ű' => 'U'
			);
			
			// Other common diacritics
			$other = array(
				'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
				'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
				'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
				'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
				'ç' => 'c', 'Ç' => 'C', 'ñ' => 'n', 'Ñ' => 'N'
			);
			
			$all_diacritics = array_merge($romanian, $hungarian, $other);
			return strtr($name, $all_diacritics);
		}

		// Handle Create Patient + WP User sync
		if (isset($_POST['mbs_add_patient_nonce']) && wp_verify_nonce($_POST['mbs_add_patient_nonce'], 'mbs_add_patient') && current_user_can('mbs_create_patients')) {
			$first_name_raw = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
			$last_name_raw = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
			$first_name = normalize_name($first_name_raw);
			$last_name = normalize_name($last_name_raw);
			$cnp = isset($_POST['cnp']) ? trim($_POST['cnp']) : '';
			$password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
			$phones = array_map('sanitize_text_field', $_POST['phones'] ?? array());
			$phone_primary = array_map('intval', $_POST['phone_primary'] ?? array());
			$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
			$birth_date = isset($_POST['birth_date']) ? sanitize_text_field($_POST['birth_date']) : '';
			$age_input = isset($_POST['age']) ? sanitize_text_field($_POST['age']) : '';
			$gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
			$address = isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '';
			$notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
			
			// Process age input - extract numeric value if it contains text
			$age = null;
			if (!empty($age_input)) {
				// Extract number from age input (e.g., "8 luni" -> 8, "5 ani" -> 5)
				if (preg_match('/(\d+)/', $age_input, $matches)) {
					$age = intval($matches[1]);
					// If it says "luni", convert to 0 (under 1 year)
					if (strpos($age_input, 'luni') !== false) {
						$age = 0;
					}
				}
			}

			$errors = array();
			if ($first_name === '' || $last_name === '') { $errors[] = __('First name and last name are required.', 'medical-booking-system'); }
			if (!preg_match('/^\d{13}$/', $cnp)) { $errors[] = __('CNP must be exactly 13 digits.', 'medical-booking-system'); }
			if ($email !== '' && !is_email($email)) { $errors[] = __('Invalid email address.', 'medical-booking-system'); }
			
			// Check if CNP already exists in database
			if (empty($errors) && !empty($cnp)) {
				global $wpdb;
				
				// Check if patient with this CNP already exists
				$existing_patient = $wpdb->get_row($wpdb->prepare(
					"SELECT id, first_name, last_name FROM {$wpdb->prefix}mbs_patients WHERE cnp = %s LIMIT 1", 
					$cnp
				));
				if ($existing_patient) {
					$errors[] = sprintf(
						__('CNP %s already exists! Patient: %s %s (ID: %d)', 'medical-booking-system'), 
						$cnp, 
						$existing_patient->first_name, 
						$existing_patient->last_name, 
						$existing_patient->id
					);
				}
				
				// Also check if WordPress user with this CNP already exists
				$existing_user = get_user_by('login', $cnp);
				if ($existing_user) {
					$errors[] = sprintf(
						__('WordPress user with CNP %s already exists! Username: %s (ID: %d)', 'medical-booking-system'), 
						$cnp, 
						$existing_user->user_login, 
						$existing_user->ID
					);
				}
			}
			
			// Validate Romanian date format and convert to ISO
			$birth_date_iso = null;
			$calculated_age = null;
			if (!empty($birth_date)) {
				$date_obj = DateTime::createFromFormat('d.m.Y', $birth_date);
				if ($date_obj === false) {
					$errors[] = __('Invalid birth date format. Use DD.MM.YYYY', 'medical-booking-system');
				} else {
					$birth_date_iso = $date_obj->format('Y-m-d');
					// Calculate age
					$today = new DateTime();
					$age_diff = $today->diff($date_obj);
					$calculated_age = $age_diff->y;
					
					// Additional validation: reasonable age
					if ($calculated_age > 120) {
						$errors[] = __('Please enter a valid birth date', 'medical-booking-system');
					}
				}
			}
			
			// Use calculated age if no age provided or if age is empty
			if (empty($age) && $calculated_age !== null) {
				$age = $calculated_age;
			}
			
			// Validate phones
			$valid_phones = array();
			foreach ($phones as $index => $phone) {
				$phone = trim($phone);
				if (!empty($phone)) {
					// Normalize Romanian phone numbers
					$normalized = preg_replace('/^\+40/', '0', $phone);
					$normalized = preg_replace('/[^\d]/', '', $normalized);
					if (preg_match('/^0[0-9]{9}$/', $normalized)) {
						$valid_phones[] = array(
							'phone' => $normalized,
							'is_primary' => in_array($index, $phone_primary) ? 1 : 0
						);
					} else {
						$errors[] = sprintf(__('Invalid phone number: %s', 'medical-booking-system'), $phone);
					}
				}
			}
			
			if (empty($valid_phones)) {
				$errors[] = __('At least one phone number is required', 'medical-booking-system');
			}

			if (empty($errors)) {
				// Ensure WP user exists with username = CNP
				$user = get_user_by('login', $cnp);
				if (!$user) {
					$temp_email = $email !== '' ? $email : ('patient' . substr(md5($cnp), 0, 8) . '@noemail.local');
					// Use provided password or generate from CNP (last 7 digits)
					$user_password = !empty($password) ? $password : substr($cnp, 6, 7);
					$user_id = wp_insert_user(array(
						'user_login' => $cnp,
						'user_pass' => $user_password,
						'user_email' => $temp_email,
						'first_name' => $first_name,
						'last_name' => $last_name,
						'display_name' => trim($first_name . ' ' . $last_name),
						'role' => 'mbs_patient',
					));
					if (is_wp_error($user_id)) {
						$errors[] = __('Failed to create WordPress user.', 'medical-booking-system') . ' ' . esc_html($user_id->get_error_message());
					} else {
						update_user_meta($user_id, 'mbs_cnp', $cnp);
						update_user_meta($user_id, 'default_password_nag', true);
						$user = get_user_by('id', $user_id);
					}
				}

				if (empty($errors) && $user) {
					global $wpdb;
					
					// Debug logging
					error_log("MBS Patient Creation - Age processing:");
					error_log("Age input: " . $age_input);
					error_log("Processed age: " . $age);
					error_log("Birth date ISO: " . $birth_date_iso);
					
					$wpdb->insert($wpdb->prefix . 'mbs_patients', array(
						'user_id' => $user->ID,
						'first_name' => $first_name,
						'last_name' => $last_name,
						'phone' => $valid_phones[0]['phone'],
						'email' => ($email !== '' ? $email : $user->user_email),
						'cnp' => $cnp,
						'birth_date' => $birth_date_iso,
						'age' => $age,
						'gender' => $gender,
						'address' => $address,
						'medical_notes' => $notes,
						'is_active' => 1,
					));
					
					if ($wpdb->last_error) {
						error_log("MBS Database Error: " . $wpdb->last_error);
						$errors[] = __('Database error occurred while saving patient.', 'medical-booking-system');
					} else {
						error_log("MBS Patient saved successfully with ID: " . $wpdb->insert_id);
					}
					
					// Add phones to user_phones table
					foreach ($valid_phones as $phone_data) {
						$wpdb->insert($wpdb->prefix . 'mbs_user_phones', array(
							'user_id' => $user->ID,
							'phone' => $phone_data['phone'],
							'is_primary' => $phone_data['is_primary'],
							'is_verified' => 0,
						));
					}
					
					echo '<div class="notice notice-success"><p>' . esc_html__('Patient created and linked to WP user.', 'medical-booking-system') . '</p></div>';
				}
			} 
			if (!empty($errors)) {
				echo '<div class="notice notice-error"><p>' . implode('<br>', array_map('esc_html', $errors)) . '</p></div>';
			}
		}

		// Add Patient Form (tab: add)
		if ($current_tab === 'add' && current_user_can('mbs_create_patients')) {
			echo '<div class="mbs-card">';
			echo '<h2 style="margin-top:0;">' . esc_html__('Add New Patient', 'medical-booking-system') . ' <span class="mbs-badge mbs-badge--primary">' . esc_html__('Sync with WP User', 'medical-booking-system') . '</span></h2>';
			echo '<p class="mbs-muted" style="margin-top:-8px;">' . esc_html__('This will also create a WordPress user with username = CNP.', 'medical-booking-system') . '</p>';
			
			echo '<form method="post" class="mbs-form-grid">';
			wp_nonce_field('mbs_add_patient', 'mbs_add_patient_nonce');
			
        // Left Column - Personal Information
        echo '<div class="mbs-form-section">';
        echo '<h3>' . esc_html__('Personal Information', 'medical-booking-system') . '</h3>';
        
        // CNP and Password (same row)
        echo '<div class="mbs-form-row">';
        echo '<div class="mbs-form-field">';
        echo '<label for="cnp">' . esc_html__('CNP (Personal ID)', 'medical-booking-system') . ' <span style="color:#d63638;">*</span></label>';
        echo '<input type="text" id="cnp" name="cnp" pattern="\d{13}" title="13 digits" required placeholder="1234567890123" maxlength="13"/>';
        echo '<div class="description">' . esc_html__('13-digit Romanian Personal Identification Number (will be used as username)', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '<div class="mbs-form-field">';
        echo '<label for="password">' . esc_html__('Password', 'medical-booking-system') . '</label>';
        echo '<input type="text" id="password" name="password" placeholder="Auto-generated from CNP" style="background:#fff;color:#000;"/>';
        echo '<div class="description">' . esc_html__('Auto-generated from last 7 digits of CNP (can be changed)', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Name fields
        echo '<div class="mbs-form-row">';
        echo '<div class="mbs-form-field">';
        echo '<label for="last_name">' . esc_html__('Last Name', 'medical-booking-system') . ' <span style="color:#d63638;">*</span></label>';
        echo '<input type="text" id="last_name" name="last_name" required placeholder="' . esc_attr__('Enter last name', 'medical-booking-system') . '"/>';
        echo '<div class="description">' . esc_html__('Diacritics will be automatically normalized (ă→a, ș→s, etc.)', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '<div class="mbs-form-field">';
        echo '<label for="first_name">' . esc_html__('First Name', 'medical-booking-system') . ' <span style="color:#d63638;">*</span></label>';
        echo '<input type="text" id="first_name" name="first_name" required placeholder="' . esc_attr__('Enter first name', 'medical-booking-system') . '"/>';
        echo '<div class="description">' . esc_html__('Diacritics will be automatically normalized (ă→a, ș→s, etc.)', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '</div>';
        
        // Birth date, age and gender (auto-filled from CNP)
        echo '<div class="mbs-form-row">';
        echo '<div class="mbs-form-field">';
        echo '<label for="birth_date">' . esc_html__('Birth Date', 'medical-booking-system') . '</label>';
        echo '<input type="text" id="birth_date" name="birth_date" placeholder="DD.MM.YYYY" readonly style="background:#f0f0f1;color:#666;cursor:pointer;"/>';
        echo '<div class="description">' . esc_html__('Auto-filled from CNP (click to override)', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '<div class="mbs-form-field">';
        echo '<label for="age">' . esc_html__('Age', 'medical-booking-system') . '</label>';
        echo '<input type="text" id="age" name="age" readonly placeholder="Auto-calculated" style="background:#f0f0f1;color:#666;"/>';
        echo '<div class="description">' . esc_html__('Auto-calculated from birth date', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="mbs-form-row">';
        echo '<div class="mbs-form-field">';
        echo '<label for="gender">' . esc_html__('Gender', 'medical-booking-system') . '</label>';
        echo '<select id="gender" name="gender" style="background:#f0f0f1;color:#666;">';
        echo '<option value="">' . esc_html__('Auto-filled from CNP', 'medical-booking-system') . '</option>';
        echo '<option value="M">' . esc_html__('Male', 'medical-booking-system') . '</option>';
        echo '<option value="F">' . esc_html__('Female', 'medical-booking-system') . '</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="mbs-form-field">';
        echo '<label>&nbsp;</label>';
        echo '<div style="height:42px;display:flex;align-items:center;color:#666;font-size:12px;">' . esc_html__('Additional fields can be added here', 'medical-booking-system') . '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>'; // End Personal Information
			
			// Right Column - Contact Information
			echo '<div class="mbs-form-section">';
			echo '<h3>' . esc_html__('Contact Information', 'medical-booking-system') . '</h3>';
			
        echo '<div class="mbs-form-field">';
        echo '<label for="email">' . esc_html__('Email Address', 'medical-booking-system') . '</label>';
        echo '<input type="email" id="email" name="email" placeholder="patient@example.com"/>';
        echo '<div class="description">' . esc_html__('Optional. If empty, a temporary email will be generated: patient[CNP_hash]@noemail.local', 'medical-booking-system') . '</div>';
        echo '</div>';
			
        echo '<div class="mbs-form-field">';
        echo '<label>' . esc_html__('Phone Numbers', 'medical-booking-system') . '</label>';
        echo '<div class="mbs-phone-list" id="phone-list">';
        echo '<div class="mbs-phone-item">';
        echo '<input type="text" name="phones[]" class="phone-input" placeholder="07xxxxxxxx"/>';
        echo '<div class="mbs-phone-controls">';
        echo '<div class="mbs-phone-primary checked">';
        echo '<input type="checkbox" name="phone_primary[]" value="0" checked/>';
        echo '<label>Primary</label>';
        echo '</div>';
        echo '<div class="mbs-phone-remove" onclick="removePhone(this)">×</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="mbs-add-phone" onclick="addPhone()">' . esc_html__('Add another phone', 'medical-booking-system') . '</div>';
        echo '</div>';
			
			echo '<div class="mbs-form-field">';
			echo '<label for="address">' . esc_html__('Address', 'medical-booking-system') . '</label>';
			echo '<textarea id="address" name="address" rows="3" placeholder="' . esc_attr__('Enter full address', 'medical-booking-system') . '"></textarea>';
			echo '</div>';
			
			echo '<div class="mbs-form-field">';
			echo '<label for="notes">' . esc_html__('Notes', 'medical-booking-system') . '</label>';
			echo '<textarea id="notes" name="notes" rows="2" placeholder="' . esc_attr__('Additional notes about the patient', 'medical-booking-system') . '"></textarea>';
			echo '</div>';
			
			echo '</div>'; // End Contact Information
			
			// Form Actions
			echo '<div class="mbs-form-actions">';
			echo '<button type="submit" class="button button-primary button-large">' . esc_html__('Create Patient + WP User', 'medical-booking-system') . '</button>';
			echo '<a href="' . esc_url($base_url . '&tab=list') . '" class="button button-secondary" style="margin-left:12px;">' . esc_html__('Cancel', 'medical-booking-system') . '</a>';
			echo '</div>';
			
			echo '</form>';
			echo '</div>';
			
			// Enqueue JavaScript for patient form
			wp_enqueue_script('mbs-patient-form', plugin_dir_url(__FILE__) . '../assets/js/patient-form.js', array('jquery'), '1.0.0', true);
			
			// Localize script for AJAX
			wp_localize_script('mbs-patient-form', 'mbs_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('mbs_admin_nonce')
			));
		}

		// Backfill user links (username = CNP) (available from any tab)
		if (isset($_GET['mbs_action']) && $_GET['mbs_action'] === 'backfill_user_links' && current_user_can('mbs_edit_patients')) {
			global $wpdb;
			$updated = 0;
			$skipped_no_user = 0;
			$mismatches = array();
			// Link patients without user_id to existing users with user_login = CNP
			$rows = $wpdb->get_results("SELECT p.id, p.cnp FROM {$wpdb->prefix}mbs_patients p WHERE (p.user_id IS NULL OR p.user_id=0) AND p.cnp IS NOT NULL AND p.cnp<>''", ARRAY_A);
			foreach ($rows as $row) {
				$user = get_user_by('login', $row['cnp']);
				if ($user) {
					$wpdb->update($wpdb->prefix . 'mbs_patients', array('user_id' => $user->ID), array('id' => $row['id']));
					$updated++;
				} else {
					$skipped_no_user++;
				}
			}
			// Find mismatches where linked user_login != patient CNP
			$mm = $wpdb->get_results("SELECT p.id, p.cnp, p.user_id FROM {$wpdb->prefix}mbs_patients p WHERE p.user_id IS NOT NULL AND p.user_id<>0", ARRAY_A);
			foreach ($mm as $row) {
				$user = get_user_by('id', intval($row['user_id']));
				if ($user && $row['cnp'] && $user->user_login !== $row['cnp']) {
					$mismatches[] = array('patient_id' => $row['id'], 'cnp' => $row['cnp'], 'user_login' => $user->user_login, 'user_id' => $user->ID);
				}
			}
			// Notice
			echo '<div class="notice notice-success"><p>' . sprintf(esc_html__('%d patients linked. %d without matching WP user. Mismatches found: %d.', 'medical-booking-system'), intval($updated), intval($skipped_no_user), count($mismatches)) . '</p></div>';
			if (!empty($mismatches)) {
				echo '<div class="notice notice-warning"><p>' . esc_html__('Username (CNP) mismatches detected (user_login != patient CNP):', 'medical-booking-system') . '</p><ul>';
				foreach (array_slice($mismatches, 0, 20) as $m) {
					echo '<li>' . sprintf('Patient #%d: CNP %s ↔ user_login %s (user #%d)', intval($m['patient_id']), esc_html($m['cnp']), esc_html($m['user_login']), intval($m['user_id'])) . '</li>';
				}
				echo '</ul></div>';
			}
		}

		// List tab
		if ($current_tab === 'list') {
			$q = isset($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
			$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
			$per_page = 20;
			$offset = ($page - 1) * $per_page;

			$backfill_url = add_query_arg(array('page' => 'medical-booking-patients', 'mbs_action' => 'backfill_user_links'), admin_url('admin.php'));
			echo '<div class="mbs-card">';
			echo '<div class="mbs-toolbar">';
			echo '<a href="' . esc_url($backfill_url) . '" class="button button-secondary">' . esc_html__('Backfill links (username = CNP)', 'medical-booking-system') . '</a>';
			echo '<form method="get" class="mbs-toolbar" style="margin-left:auto;">';
			echo '<input type="hidden" name="page" value="medical-booking-patients" />';
			echo '<input type="hidden" name="tab" value="list" />';
			echo '<input type="search" name="q" value="' . esc_attr($q) . '" placeholder="' . esc_attr__('Search name, CNP, phone, email', 'medical-booking-system') . '" style="width:320px;" /> ';
			echo '<button class="button">' . esc_html__('Search', 'medical-booking-system') . '</button>';
			echo '</form>';
			echo '</div>';

			global $wpdb;
			$where = 'WHERE 1=1';
			$params = array();
			if ($q !== '') {
				$like = '%' . $wpdb->esc_like($q) . '%';
				$where .= " AND (p.first_name LIKE %s OR p.last_name LIKE %s OR p.cnp LIKE %s OR p.email LIKE %s OR p.phone LIKE %s OR up.phone LIKE %s)";
				array_push($params, $like, $like, $like, $like, $like, $like);
			}

			$total_sql = "SELECT COUNT(DISTINCT p.id) FROM {$wpdb->prefix}mbs_patients p LEFT JOIN {$wpdb->prefix}mbs_user_phones up ON up.user_id = p.user_id $where";
			$total = $params ? $wpdb->get_var($wpdb->prepare($total_sql, $params)) : $wpdb->get_var($total_sql);

			$sql = "SELECT p.*,
			COALESCE(NULLIF(GROUP_CONCAT(DISTINCT up.phone ORDER BY up.is_primary DESC SEPARATOR '; '), ''), p.phone) AS phones
			FROM {$wpdb->prefix}mbs_patients p
			LEFT JOIN {$wpdb->prefix}mbs_user_phones up ON up.user_id = p.user_id
			$where
			GROUP BY p.id
			ORDER BY p.created_at DESC
			LIMIT %d OFFSET %d";
			$query_params = $params;
			array_push($query_params, $per_page, $offset);
			$rows = $params ? $wpdb->get_results($wpdb->prepare($sql, $query_params), ARRAY_A) : $wpdb->get_results($wpdb->prepare($sql, $per_page, $offset), ARRAY_A);

			echo '<table class="wp-list-table widefat fixed striped mbs-table">';
			echo '<thead><tr><th>ID</th><th>Name</th><th>CNP</th><th>Birth Date</th><th>Age</th><th>Gender</th><th>Phones</th><th>Email</th><th>Status</th><th>Created</th><th style="width:120px;">Actions</th></tr></thead><tbody>';
			foreach ($rows as $r) {
				$cnp = isset($r['cnp']) ? $r['cnp'] : '';
				$birth_date = isset($r['birth_date']) ? $r['birth_date'] : '';
				$age = isset($r['age']) ? $r['age'] : '';
				$gender = isset($r['gender']) ? $r['gender'] : '';
				
				// Format birth date to Romanian format (DD.MM.YYYY)
				$formatted_birth_date = '';
				if ($birth_date && $birth_date !== '0000-00-00') {
					$date_obj = DateTime::createFromFormat('Y-m-d', $birth_date);
					if ($date_obj) {
						$formatted_birth_date = $date_obj->format('d.m.Y');
					}
				}
				
				// Format age display
				$age_display = '';
				if ($age !== '' && $age !== null) {
					if ($age == 0) {
						$age_display = '< 1 an';
					} else {
						$age_display = $age . ' ani';
					}
				}
				
				// Gender styling
				$gender_display = '';
				if ($gender) {
					$gender_color = ($gender === 'M') ? '#2271b1' : '#d63638'; // Blue for Male, Red for Female
					$gender_display = '<span style="color: ' . $gender_color . '; font-weight: bold;">' . esc_html($gender) . '</span>';
				}
				
				echo '<tr>';
				echo '<td>' . intval($r['id']) . '</td>';
				echo '<td>' . esc_html(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''))) . '</td>';
				echo '<td>' . esc_html($cnp) . '</td>';
				echo '<td>' . esc_html($formatted_birth_date) . '</td>';
				echo '<td>' . $age_display . '</td>';
				echo '<td>' . $gender_display . '</td>';
				echo '<td>' . esc_html($r['phones'] ?? '') . '</td>';
				echo '<td>' . esc_html($r['email'] ?? '') . '</td>';
				echo '<td><span class="mbs-badge">' . esc_html($r['status'] ?? 'active') . '</span></td>';
				echo '<td>' . esc_html($r['created_at'] ?? '') . '</td>';
				echo '<td class="mbs-actions"><a href="#" class="button button-small" disabled>' . esc_html__('Edit', 'medical-booking-system') . '</a> <a href="#" class="button button-small" disabled>' . esc_html__('Delete', 'medical-booking-system') . '</a></td>';
				echo '</tr>';
			}
			echo '</tbody></table>';

			// Pagination
			$total_pages = max(1, ceil($total / $per_page));
			if ($total_pages > 1) {
				echo '<div class="tablenav"><div class="tablenav-pages">';
				for ($p = 1; $p <= $total_pages; $p++) {
					$link = add_query_arg(array('page' => 'medical-booking-patients', 'tab' => 'list', 'q' => $q, 'paged' => $p), admin_url('admin.php'));
					$class = $p === $page ? 'class="page-numbers current"' : 'class="page-numbers"';
					echo '<a ' . $class . ' href="' . esc_url($link) . '">' . intval($p) . '</a> ';
				}
				echo '</div></div>';
			}
			echo '</div>';
		}

		echo '</div>';
	}
	
	// Settings rendering handled by MBS_Settings
	
	// AJAX handler for CNP duplicate check
	public function ajax_check_cnp_duplicate() {
		// Verify nonce
		if (!wp_verify_nonce($_POST['nonce'], 'mbs_admin_nonce')) {
			wp_die('Security check failed');
		}
		
		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}
		
		$cnp = sanitize_text_field($_POST['cnp']);
		
		if (!preg_match('/^\d{13}$/', $cnp)) {
			wp_send_json_error('Invalid CNP format');
		}
		
		global $wpdb;
		
		// Check if patient with this CNP already exists
		$existing_patient = $wpdb->get_row($wpdb->prepare(
			"SELECT id, first_name, last_name FROM {$wpdb->prefix}mbs_patients WHERE cnp = %s LIMIT 1", 
			$cnp
		));
		
		if ($existing_patient) {
			wp_send_json_success(array(
				'exists' => true,
				'patient_id' => $existing_patient->id,
				'patient_name' => trim($existing_patient->first_name . ' ' . $existing_patient->last_name),
				'message' => sprintf(
					'CNP %s already exists! Patient: %s %s (ID: %d)', 
					$cnp, 
					$existing_patient->first_name, 
					$existing_patient->last_name, 
					$existing_patient->id
				)
			));
		}
		
		// Also check if WordPress user with this CNP already exists
		$existing_user = get_user_by('login', $cnp);
		if ($existing_user) {
			wp_send_json_success(array(
				'exists' => true,
				'user_id' => $existing_user->ID,
				'user_login' => $existing_user->user_login,
				'message' => sprintf(
					'WordPress user with CNP %s already exists! Username: %s (ID: %d)', 
					$cnp, 
					$existing_user->user_login, 
					$existing_user->ID
				)
			));
		}
		
		wp_send_json_success(array(
			'exists' => false,
			'message' => 'CNP is available'
		));
	}
	
	/**
	 * AJAX handler for patient search
	 */
	public function ajax_search_patients() {
		// Verify nonce
		if (!wp_verify_nonce($_POST['nonce'], 'mbs_admin_nonce')) {
			wp_die('Security check failed');
		}
		
		// Check user capabilities
		if (!current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}
		
		$query = sanitize_text_field($_POST['query']);
		
		if (strlen($query) < 2) {
			wp_send_json_success(array());
		}
		
		global $wpdb;
		
		// Search patients by name or CNP
		$search_term = '%' . $wpdb->esc_like($query) . '%';
		$patients = $wpdb->get_results($wpdb->prepare(
			"SELECT 
				p.id,
				p.first_name,
				p.last_name,
				p.cnp,
				p.birth_date,
				p.age,
				p.gender
			FROM {$wpdb->prefix}mbs_patients p
			WHERE p.is_active = 1 
			AND (
				p.first_name LIKE %s 
				OR p.last_name LIKE %s 
				OR CONCAT(p.first_name, ' ', p.last_name) LIKE %s
				OR p.cnp LIKE %s
			)
			ORDER BY 
				CASE 
					WHEN p.first_name LIKE %s THEN 1
					WHEN p.last_name LIKE %s THEN 2
					WHEN CONCAT(p.first_name, ' ', p.last_name) LIKE %s THEN 3
					WHEN p.cnp LIKE %s THEN 4
					ELSE 5
				END,
				p.first_name, p.last_name
			LIMIT 10",
			$search_term, $search_term, $search_term, $search_term,
			$search_term, $search_term, $search_term, $search_term
		));
		
		$results = array();
		foreach ($patients as $patient) {
			$results[] = array(
				'id' => $patient->id,
				'name' => trim($patient->first_name . ' ' . $patient->last_name),
				'cnp' => $patient->cnp,
				'birth_date' => $patient->birth_date,
				'age' => $patient->age,
				'gender' => $patient->gender
			);
		}
		
		wp_send_json_success($results);
	}
	
	/**
	 * Render families page
	 */
	public function render_families() {
		if (!current_user_can('mbs_view_patients')) {
			wp_die(__('You do not have sufficient permissions to access this page.', 'medical-booking-system'));
		}
		
		// Handle form submissions
		if (isset($_POST['action']) && wp_verify_nonce($_POST['_wpnonce'], 'mbs_family_action')) {
			$this->handle_family_action();
		}
		
		// Get current tab
		$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';
		$base_url = add_query_arg(array('page' => 'medical-booking-families'), admin_url('admin.php'));
		
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Family Management', 'medical-booking-system') . '</h1>';
		
		// Tabs
		echo '<h2 class="nav-tab-wrapper" style="margin-top:15px;">';
		echo '<a class="nav-tab ' . ($current_tab === 'list' ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'list', $base_url)) . '">' . esc_html__('Families List', 'medical-booking-system') . '</a>';
		echo '<a class="nav-tab ' . ($current_tab === 'create' ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'create', $base_url)) . '">' . esc_html__('Create Family', 'medical-booking-system') . '</a>';
		echo '<a class="nav-tab ' . ($current_tab === 'members' ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'members', $base_url)) . '">' . esc_html__('Manage Members', 'medical-booking-system') . '</a>';
		echo '<a class="nav-tab ' . ($current_tab === 'stats' ? 'nav-tab-active' : '') . '" href="' . esc_url(add_query_arg('tab', 'stats', $base_url)) . '">' . esc_html__('Statistics', 'medical-booking-system') . '</a>';
		echo '</h2>';
		
		// Render tab content
		switch ($current_tab) {
			case 'create':
				$this->render_create_family_tab();
				break;
			case 'members':
				$this->render_family_members_tab();
				break;
			case 'stats':
				$this->render_family_stats_tab();
				break;
			default:
				$this->render_families_list_tab();
				break;
		}
		
		echo '</div>';
	}
	
	/**
	 * Handle family actions
	 */
	private function handle_family_action() {
		$action = sanitize_text_field($_POST['action']);
		
		switch ($action) {
			case 'create_family':
				$family_name = sanitize_text_field($_POST['family_name']);
				$head_patient_id = intval($_POST['head_patient_id']);
				
				$result = $this->create_family($family_name, $head_patient_id);
				
				if ($result['success']) {
					echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
				}
				break;
				
			case 'add_member':
				$family_id = intval($_POST['family_id']);
				$patient_id = intval($_POST['patient_id']);
				$relationship_type = sanitize_text_field($_POST['relationship_type']);
				$notes = sanitize_textarea_field($_POST['notes']);
				
				$result = $this->add_family_member($family_id, $patient_id, $relationship_type, $notes);
				
				if ($result['success']) {
					echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
				}
				break;
				
			case 'remove_member':
				$family_id = intval($_POST['family_id']);
				$patient_id = intval($_POST['patient_id']);
				
				$result = $this->remove_family_member($family_id, $patient_id);
				
				if ($result['success']) {
					echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
				}
				break;
				
			case 'delete_family':
				$family_id = intval($_POST['family_id']);
				
				$result = $this->delete_family($family_id);
				
				if ($result['success']) {
					echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
				}
				break;
		}
	}
	
	/**
	 * Render families list tab
	 */
	private function render_families_list_tab() {
		$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
		$per_page = 20;
		$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
		
		$families = $this->get_all_families($page, $per_page, $search);
		
		echo '<div class="mbs-card">';
		echo '<div class="mbs-toolbar">';
		echo '<form method="get" class="mbs-toolbar" style="margin-left:auto;">';
		echo '<input type="hidden" name="page" value="medical-booking-families" />';
		echo '<input type="hidden" name="tab" value="list" />';
		echo '<input type="search" name="search" value="' . esc_attr($search) . '" placeholder="' . esc_attr__('Search families...', 'medical-booking-system') . '" style="width:320px;" /> ';
		echo '<button class="button">' . esc_html__('Search', 'medical-booking-system') . '</button>';
		echo '</form>';
		echo '</div>';
		
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__('Family Name', 'medical-booking-system') . '</th>';
		echo '<th>' . esc_html__('Head of Family', 'medical-booking-system') . '</th>';
		echo '<th>' . esc_html__('Members', 'medical-booking-system') . '</th>';
		echo '<th>' . esc_html__('Created', 'medical-booking-system') . '</th>';
		echo '<th style="width:120px;">' . esc_html__('Actions', 'medical-booking-system') . '</th>';
		echo '</tr></thead><tbody>';
		
		foreach ($families as $family) {
			echo '<tr>';
			echo '<td><strong>' . esc_html($family->family_name) . '</strong></td>';
			echo '<td>' . esc_html($family->head_first_name . ' ' . $family->head_last_name) . '<br><small>' . esc_html($family->head_cnp) . '</small></td>';
			echo '<td>' . intval($family->member_count) . '</td>';
			echo '<td>' . esc_html($family->created_at) . '</td>';
			echo '<td>';
			echo '<a href="' . esc_url(add_query_arg(array('tab' => 'view', 'family_id' => $family->id), admin_url('admin.php?page=medical-booking-families'))) . '" class="button button-small">' . esc_html__('View', 'medical-booking-system') . '</a>';
			echo '</td>';
			echo '</tr>';
		}
		
		echo '</tbody></table>';
		echo '</div>';
	}
	
	/**
	 * Render create family tab
	 */
	private function render_create_family_tab() {
		global $wpdb;
		
		// Get all patients for dropdown
		$patients = $wpdb->get_results(
			"SELECT p.*, u.user_login 
			 FROM {$wpdb->prefix}mbs_patients p 
			 LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID 
			 WHERE p.is_active = 1 
			 ORDER BY p.first_name, p.last_name"
		);
		
		echo '<div class="mbs-card">';
		echo '<h3>' . esc_html__('Create New Family', 'medical-booking-system') . '</h3>';
		
		echo '<form method="post" action="">';
		wp_nonce_field('mbs_family_action');
		echo '<input type="hidden" name="action" value="create_family" />';
		
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th scope="row"><label for="family_name">' . esc_html__('Family Name', 'medical-booking-system') . '</label></th>';
		echo '<td><input type="text" id="family_name" name="family_name" class="regular-text" required /></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<th scope="row"><label for="head_patient_search">' . esc_html__('Head of Family', 'medical-booking-system') . '</label></th>';
		echo '<td>';
		echo '<div class="mbs-patient-search-container" style="position:relative;">';
		echo '<input type="text" id="head_patient_search" name="head_patient_search" class="regular-text" placeholder="' . esc_attr__('Search patient by name or CNP...', 'medical-booking-system') . '" autocomplete="off" required />';
		echo '<input type="hidden" id="head_patient_id" name="head_patient_id" value="" />';
		echo '<div id="patient_search_results" class="mbs-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #dcdcde;border-top:none;max-height:200px;overflow-y:auto;z-index:1000;"></div>';
		echo '</div>';
		echo '<div id="selected_patient_info" class="mbs-selected-patient" style="margin-top:8px;padding:8px;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:4px;display:none;"></div>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		
		echo '<p class="submit">';
		echo '<input type="submit" class="button button-primary" value="' . esc_attr__('Create Family', 'medical-booking-system') . '" />';
		echo '</p>';
		
		echo '</form>';
		echo '</div>';
		
		// Add CSS and JavaScript for patient search
		echo '<style>
		.mbs-search-results {
			box-shadow: 0 2px 8px rgba(0,0,0,0.1);
		}
		.mbs-search-result-item {
			padding: 8px 12px;
			cursor: pointer;
			border-bottom: 1px solid #f0f0f1;
			transition: background-color 0.2s;
		}
		.mbs-search-result-item:hover {
			background-color: #f0f6fc;
		}
		.mbs-search-result-item:last-child {
			border-bottom: none;
		}
		.mbs-search-result-name {
			font-weight: 500;
			color: #1d2327;
		}
		.mbs-search-result-cnp {
			font-size: 12px;
			color: #666;
			margin-top: 2px;
		}
		.mbs-selected-patient {
			font-size: 14px;
		}
		.mbs-selected-patient-name {
			font-weight: 500;
			color: #1d2327;
		}
		.mbs-selected-patient-cnp {
			font-size: 12px;
			color: #666;
			margin-top: 2px;
		}
		.mbs-clear-selection {
			float: right;
			color: #d63638;
			cursor: pointer;
			font-size: 12px;
		}
		</style>';
		
		echo '<script>
		jQuery(document).ready(function($) {
			let searchTimeout;
			const searchInput = $("#head_patient_search");
			const hiddenInput = $("#head_patient_id");
			const resultsDiv = $("#patient_search_results");
			const selectedDiv = $("#selected_patient_info");
			
			// Search patients
			searchInput.on("input", function() {
				const query = $(this).val().trim();
				
				// Clear previous timeout
				if (searchTimeout) {
					clearTimeout(searchTimeout);
				}
				
				// Hide results if query is too short
				if (query.length < 2) {
					resultsDiv.hide();
					return;
				}
				
				// Debounce search
				searchTimeout = setTimeout(function() {
					searchPatients(query);
				}, 300);
			});
			
			// Hide results when clicking outside
			$(document).on("click", function(e) {
				if (!$(e.target).closest(".mbs-patient-search-container").length) {
					resultsDiv.hide();
				}
			});
			
			// Search patients function
			function searchPatients(query) {
				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
						action: "mbs_search_patients",
						query: query,
						nonce: "' . wp_create_nonce('mbs_admin_nonce') . '"
					},
					success: function(response) {
						if (response.success && response.data.length > 0) {
							displaySearchResults(response.data);
						} else {
							resultsDiv.html("<div class=\"mbs-search-result-item\">' . esc_js(__('No patients found', 'medical-booking-system')) . '</div>").show();
						}
					},
					error: function() {
						resultsDiv.html("<div class=\"mbs-search-result-item\">' . esc_js(__('Search error', 'medical-booking-system')) . '</div>").show();
					}
				});
			}
			
			// Display search results
			function displaySearchResults(patients) {
				let html = "";
				patients.forEach(function(patient) {
					html += "<div class=\"mbs-search-result-item\" data-patient-id=\"" + patient.id + "\" data-patient-name=\"" + patient.name + "\" data-patient-cnp=\"" + patient.cnp + "\">";
					html += "<div class=\"mbs-search-result-name\">" + patient.name + "</div>";
					html += "<div class=\"mbs-search-result-cnp\">CNP: " + patient.cnp + "</div>";
					html += "</div>";
				});
				resultsDiv.html(html).show();
			}
			
			// Handle result selection
			resultsDiv.on("click", ".mbs-search-result-item", function() {
				const patientId = $(this).data("patient-id");
				const patientName = $(this).data("patient-name");
				const patientCnp = $(this).data("patient-cnp");
				
				// Set hidden input
				hiddenInput.val(patientId);
				
				// Update search input
				searchInput.val(patientName);
				
				// Show selected patient info
				selectedDiv.html(
					"<div class=\"mbs-selected-patient-name\">" + patientName + "</div>" +
					"<div class=\"mbs-selected-patient-cnp\">CNP: " + patientCnp + "</div>" +
					"<span class=\"mbs-clear-selection\">' . esc_js(__('Clear', 'medical-booking-system')) . '</span>"
				).show();
				
				// Hide results
				resultsDiv.hide();
			});
			
			// Clear selection
			selectedDiv.on("click", ".mbs-clear-selection", function() {
				hiddenInput.val("");
				searchInput.val("");
				selectedDiv.hide();
			});
		});
		</script>';
	}
	
	/**
	 * Render family members management tab
	 */
	private function render_family_members_tab() {
		
		// Handle form submissions
		if (isset($_POST['action'])) {
			$this->handle_family_member_action();
		}
		
		// Get current family ID from URL or form
		$current_family_id = isset($_GET['family_id']) ? intval($_GET['family_id']) : (isset($_POST['family_id']) ? intval($_POST['family_id']) : 0);
		
		if ($current_family_id > 0) {
			$this->render_family_members_for_family($current_family_id);
		} else {
			$this->render_family_selector();
		}
	}
	
	/**
	 * Render family selector
	 */
	private function render_family_selector() {
		echo '<div class="mbs-family-selector">';
		echo '<h2>' . esc_html__('Select Family', 'medical-booking-system') . '</h2>';
		
		$families = $this->get_all_families();
		
		if (empty($families)) {
			echo '<p>' . esc_html__('No families found. Please create a family first.', 'medical-booking-system') . '</p>';
			echo '<p><a href="' . esc_url(add_query_arg('tab', 'create', admin_url('admin.php?page=medical-booking-families'))) . '" class="button button-primary">' . esc_html__('Create Family', 'medical-booking-system') . '</a></p>';
			return;
		}
		
		echo '<form method="get" action="">';
		echo '<input type="hidden" name="page" value="medical-booking-families" />';
		echo '<input type="hidden" name="tab" value="members" />';
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th scope="row"><label for="family_id">' . esc_html__('Select Family', 'medical-booking-system') . '</label></th>';
		echo '<td>';
		echo '<select id="family_id" name="family_id" required>';
		echo '<option value="">' . esc_html__('Choose a family...', 'medical-booking-system') . '</option>';
		
		foreach ($families as $family) {
			$head_patient = $this->get_patient_details($family->head_patient_id);
			$head_name = $head_patient ? trim($head_patient->first_name . ' ' . $head_patient->last_name) : 'Unknown';
			echo '<option value="' . intval($family->id) . '">';
			echo esc_html($family->family_name . ' (' . $head_name . ')');
			echo '</option>';
		}
		
		echo '</select>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '<p class="submit">';
		echo '<input type="submit" class="button button-primary" value="' . esc_attr__('Manage Members', 'medical-booking-system') . '" />';
		echo '</p>';
		echo '</form>';
		echo '</div>';
	}
	
	/**
	 * Render family members for specific family
	 */
	private function render_family_members_for_family($family_id) {
		$family = $this->get_family_details($family_id);
		$members = $this->get_family_members($family_id);
		
		if (!$family) {
			echo '<p>' . esc_html__('Family not found.', 'medical-booking-system') . '</p>';
			return;
		}
		
		echo '<div class="mbs-family-members">';
		echo '<h2>' . esc_html__('Family:', 'medical-booking-system') . ' ' . esc_html($family->family_name) . '</h2>';
		
		// Back button
		echo '<p><a href="' . esc_url(add_query_arg('tab', 'members', admin_url('admin.php?page=medical-booking-families'))) . '" class="button">' . esc_html__('← Back to Family Selection', 'medical-booking-system') . '</a></p>';
		
		// Add new member form
		echo '<div class="mbs-add-member-form" style="background:#f9f9f9;padding:20px;margin:20px 0;border:1px solid #ddd;">';
		echo '<h3>' . esc_html__('Add New Member', 'medical-booking-system') . '</h3>';
		
		echo '<form method="post" action="">';
		echo '<input type="hidden" name="action" value="add_family_member" />';
		echo '<input type="hidden" name="family_id" value="' . intval($family_id) . '" />';
		echo '<input type="hidden" name="nonce" value="' . wp_create_nonce('mbs_family_action') . '" />';
		
		echo '<table class="form-table">';
		echo '<tr>';
		echo '<th scope="row"><label for="member_patient_search">' . esc_html__('Patient', 'medical-booking-system') . '</label></th>';
		echo '<td>';
		echo '<div class="mbs-patient-search-container" style="position:relative;">';
		echo '<input type="text" id="member_patient_search" name="member_patient_search" class="regular-text" placeholder="' . esc_attr__('Search patient by name or CNP...', 'medical-booking-system') . '" autocomplete="off" required />';
		echo '<input type="hidden" id="member_patient_id" name="member_patient_id" value="" />';
		echo '<div id="member_search_results" class="mbs-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #dcdcde;border-top:none;max-height:200px;overflow-y:auto;z-index:1000;"></div>';
		echo '</div>';
		echo '<div id="selected_member_info" class="mbs-selected-patient" style="margin-top:8px;padding:8px;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:4px;display:none;"></div>';
		echo '</td>';
		echo '</tr>';
		
		echo '<tr>';
		echo '<th scope="row"><label for="relationship_type">' . esc_html__('Relationship', 'medical-booking-system') . '</label></th>';
		echo '<td>';
		echo '<select id="relationship_type" name="relationship_type" required>';
		echo '<option value="">' . esc_html__('Select relationship...', 'medical-booking-system') . '</option>';
		echo '<option value="spouse">' . esc_html__('Spouse', 'medical-booking-system') . '</option>';
		echo '<option value="child">' . esc_html__('Child', 'medical-booking-system') . '</option>';
		echo '<option value="parent">' . esc_html__('Parent', 'medical-booking-system') . '</option>';
		echo '<option value="sibling">' . esc_html__('Sibling', 'medical-booking-system') . '</option>';
		echo '<option value="other">' . esc_html__('Other', 'medical-booking-system') . '</option>';
		echo '</select>';
		echo '</td>';
		echo '</tr>';
		
		echo '<tr>';
		echo '<th scope="row"><label for="member_notes">' . esc_html__('Notes', 'medical-booking-system') . '</label></th>';
		echo '<td><textarea id="member_notes" name="member_notes" rows="3" cols="50" placeholder="' . esc_attr__('Optional notes about this family relationship...', 'medical-booking-system') . '"></textarea></td>';
		echo '</tr>';
		echo '</table>';
		
		echo '<p class="submit">';
		echo '<input type="submit" class="button button-primary" value="' . esc_attr__('Add Member', 'medical-booking-system') . '" />';
		echo '</p>';
		echo '</form>';
		echo '</div>';
		
		// Members list
		echo '<div class="mbs-members-list">';
		echo '<h3>' . esc_html__('Family Members', 'medical-booking-system') . '</h3>';
		
		if (empty($members)) {
			echo '<p>' . esc_html__('No members added yet.', 'medical-booking-system') . '</p>';
		} else {
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead>';
			echo '<tr>';
			echo '<th>' . esc_html__('Name', 'medical-booking-system') . '</th>';
			echo '<th>' . esc_html__('CNP', 'medical-booking-system') . '</th>';
			echo '<th>' . esc_html__('Relationship', 'medical-booking-system') . '</th>';
			echo '<th>' . esc_html__('Notes', 'medical-booking-system') . '</th>';
			echo '<th>' . esc_html__('Actions', 'medical-booking-system') . '</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			
			foreach ($members as $member) {
				$patient = $this->get_patient_details($member->patient_id);
				if (!$patient) continue;
				
				echo '<tr>';
				echo '<td>' . esc_html(trim($patient->first_name . ' ' . $patient->last_name)) . '</td>';
				echo '<td>' . esc_html($patient->cnp) . '</td>';
				echo '<td>' . esc_html($this->get_relationship_label($member->relationship_type)) . '</td>';
				echo '<td>' . esc_html($member->notes ?: '-') . '</td>';
				echo '<td>';
				echo '<form method="post" style="display:inline;" onsubmit="return confirm(\'' . esc_js(__('Are you sure you want to remove this member?', 'medical-booking-system')) . '\');">';
				echo '<input type="hidden" name="action" value="remove_family_member" />';
				echo '<input type="hidden" name="family_id" value="' . intval($family_id) . '" />';
				echo '<input type="hidden" name="member_id" value="' . intval($member->id) . '" />';
				echo '<input type="hidden" name="nonce" value="' . wp_create_nonce('mbs_family_action') . '" />';
				echo '<input type="submit" class="button button-small" value="' . esc_attr__('Remove', 'medical-booking-system') . '" />';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
			
			echo '</tbody>';
			echo '</table>';
		}
		
		echo '</div>';
		echo '</div>';
		
		// Add JavaScript for member search (similar to family head search)
		echo '<script>
		jQuery(document).ready(function($) {
			let searchTimeout;
			const searchInput = $("#member_patient_search");
			const hiddenInput = $("#member_patient_id");
			const resultsDiv = $("#member_search_results");
			const selectedDiv = $("#selected_member_info");
			
			// Search patients
			searchInput.on("input", function() {
				const query = $(this).val().trim();
				
				if (searchTimeout) {
					clearTimeout(searchTimeout);
				}
				
				if (query.length < 2) {
					resultsDiv.hide();
					return;
				}
				
				searchTimeout = setTimeout(function() {
					searchPatients(query);
				}, 300);
			});
			
			$(document).on("click", function(e) {
				if (!$(e.target).closest(".mbs-patient-search-container").length) {
					resultsDiv.hide();
				}
			});
			
			function searchPatients(query) {
				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
						action: "mbs_search_patients",
						query: query,
						nonce: "' . wp_create_nonce('mbs_admin_nonce') . '"
					},
					success: function(response) {
						if (response.success && response.data.length > 0) {
							displaySearchResults(response.data);
						} else {
							resultsDiv.html("<div class=\"mbs-search-result-item\">' . esc_js(__('No patients found', 'medical-booking-system')) . '</div>").show();
						}
					},
					error: function() {
						resultsDiv.html("<div class=\"mbs-search-result-item\">' . esc_js(__('Search error', 'medical-booking-system')) . '</div>").show();
					}
				});
			}
			
			function displaySearchResults(patients) {
				let html = "";
				patients.forEach(function(patient) {
					html += "<div class=\"mbs-search-result-item\" data-patient-id=\"" + patient.id + "\" data-patient-name=\"" + patient.name + "\" data-patient-cnp=\"" + patient.cnp + "\">";
					html += "<div class=\"mbs-search-result-name\">" + patient.name + "</div>";
					html += "<div class=\"mbs-search-result-cnp\">CNP: " + patient.cnp + "</div>";
					html += "</div>";
				});
				resultsDiv.html(html).show();
			}
			
			resultsDiv.on("click", ".mbs-search-result-item", function() {
				const patientId = $(this).data("patient-id");
				const patientName = $(this).data("patient-name");
				const patientCnp = $(this).data("patient-cnp");
				
				hiddenInput.val(patientId);
				searchInput.val(patientName);
				
				selectedDiv.html(
					"<div class=\"mbs-selected-patient-name\">" + patientName + "</div>" +
					"<div class=\"mbs-selected-patient-cnp\">CNP: " + patientCnp + "</div>" +
					"<span class=\"mbs-clear-selection\">' . esc_js(__('Clear', 'medical-booking-system')) . '</span>"
				).show();
				
				resultsDiv.hide();
			});
			
			selectedDiv.on("click", ".mbs-clear-selection", function() {
				hiddenInput.val("");
				searchInput.val("");
				selectedDiv.hide();
			});
		});
		</script>';
	}
	
	/**
	 * Handle family member actions
	 */
	private function handle_family_member_action() {
		if (!wp_verify_nonce($_POST['nonce'], 'mbs_family_action')) {
			wp_die('Security check failed');
		}
		
		if (!current_user_can('manage_options')) {
			wp_die('Insufficient permissions');
		}
		
		$action = sanitize_text_field($_POST['action']);
		$family_id = intval($_POST['family_id']);
		
		switch ($action) {
			case 'add_family_member':
				$patient_id = intval($_POST['member_patient_id']);
				$relationship_type = sanitize_text_field($_POST['relationship_type']);
				$notes = sanitize_textarea_field($_POST['member_notes']);
				
				if (empty($patient_id) || empty($relationship_type)) {
					echo '<div class="notice notice-error"><p>' . esc_html__('Please fill in all required fields.', 'medical-booking-system') . '</p></div>';
					return;
				}
				
				// Check if patient is already in this family
				$existing_member = $this->get_family_member_by_patient($family_id, $patient_id);
				if ($existing_member) {
					echo '<div class="notice notice-error"><p>' . esc_html__('This patient is already a member of this family.', 'medical-booking-system') . '</p></div>';
					return;
				}
				
				// Check if patient is head of another family
				if ($this->is_patient_head_of_family($patient_id)) {
					echo '<div class="notice notice-error"><p>' . esc_html__('This patient is already the head of another family.', 'medical-booking-system') . '</p></div>';
					return;
				}
				
				$result = $this->add_family_member($family_id, $patient_id, $relationship_type, $notes);
				
				if ($result) {
					echo '<div class="notice notice-success"><p>' . esc_html__('Family member added successfully!', 'medical-booking-system') . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html__('Failed to add family member.', 'medical-booking-system') . '</p></div>';
				}
				break;
				
			case 'remove_family_member':
				$member_id = intval($_POST['member_id']);
				
				$result = $this->remove_family_member_by_id($member_id);
				
				if ($result) {
					echo '<div class="notice notice-success"><p>' . esc_html__('Family member removed successfully!', 'medical-booking-system') . '</p></div>';
				} else {
					echo '<div class="notice notice-error"><p>' . esc_html__('Failed to remove family member.', 'medical-booking-system') . '</p></div>';
				}
				break;
		}
	}
	
	/**
	 * Render family statistics tab
	 */
	private function render_family_stats_tab() {
		$stats = $this->get_family_stats();
		
		echo '<div class="mbs-card">';
		echo '<h3>' . esc_html__('Family Statistics', 'medical-booking-system') . '</h3>';
		
		echo '<div class="mbs-stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin:20px 0;">';
		
		// Total families
		echo '<div class="mbs-stat-box" style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:20px;text-align:center;">';
		echo '<h4 style="margin:0 0 10px 0;color:#1d2327;">' . esc_html__('Total Families', 'medical-booking-system') . '</h4>';
		echo '<div style="font-size:32px;font-weight:bold;color:#2271b1;">' . intval($stats['total_families']) . '</div>';
		echo '</div>';
		
		// Total family members
		echo '<div class="mbs-stat-box" style="background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:20px;text-align:center;">';
		echo '<h4 style="margin:0 0 10px 0;color:#1d2327;">' . esc_html__('Total Family Members', 'medical-booking-system') . '</h4>';
		echo '<div style="font-size:32px;font-weight:bold;color:#00a32a;">' . intval($stats['total_family_members']) . '</div>';
		echo '</div>';
		
		echo '</div>';
		
		// Relationship distribution
		if (!empty($stats['by_relationship'])) {
			echo '<h4>' . esc_html__('Relationship Distribution', 'medical-booking-system') . '</h4>';
			echo '<table class="wp-list-table widefat fixed striped">';
			echo '<thead><tr>';
			echo '<th>' . esc_html__('Relationship Type', 'medical-booking-system') . '</th>';
			echo '<th>' . esc_html__('Count', 'medical-booking-system') . '</th>';
			echo '</tr></thead><tbody>';
			
			foreach ($stats['by_relationship'] as $rel) {
				echo '<tr>';
				echo '<td>' . esc_html($this->get_relationship_label($rel->relationship_type)) . '</td>';
				echo '<td>' . intval($rel->count) . '</td>';
				echo '</tr>';
			}
			
			echo '</tbody></table>';
		}
		
		echo '</div>';
	}
	
	// ===== FAMILY MANAGEMENT METHODS (moved from MBS_Family_Manager) =====
	
	/**
	 * Create a new family
	 */
	private function create_family($family_name, $head_patient_id) {
		global $wpdb;
		
		// Check if patient exists
		$patient = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_patients WHERE id = %d AND is_active = 1",
			$head_patient_id
		));
		
		if (!$patient) {
			return array(
				'success' => false,
				'message' => 'Patient not found or inactive'
			);
		}
		
		// Check if patient is already head of a family
		$existing_family = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_families WHERE head_patient_id = %d",
			$head_patient_id
		));
		
		if ($existing_family) {
			return array(
				'success' => false,
				'message' => 'Patient is already head of family "' . $existing_family->family_name . '"'
			);
		}
		
		// Create family
		$result = $wpdb->insert(
			$wpdb->prefix . 'mbs_families',
			array(
				'family_name' => sanitize_text_field($family_name),
				'head_patient_id' => intval($head_patient_id)
			),
			array('%s', '%d')
		);
		
		if ($result !== false) {
			$family_id = $wpdb->insert_id;
			
			// Add patient as member with relationship 'head'
			$this->add_family_member($family_id, $head_patient_id, 'head');
			
			return array(
				'success' => true,
				'message' => 'Family created successfully',
				'family_id' => $family_id
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Error creating family: ' . $wpdb->last_error
			);
		}
	}
	
	/**
	 * Add a family member
	 */
	private function add_family_member($family_id, $patient_id, $relationship_type, $notes = '') {
		global $wpdb;
		
		// Check if family exists
		$family = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_families WHERE id = %d",
			$family_id
		));
		
		if (!$family) {
			return array(
				'success' => false,
				'message' => 'Family not found'
			);
		}
		
		// Check if patient exists
		$patient = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_patients WHERE id = %d AND is_active = 1",
			$patient_id
		));
		
		if (!$patient) {
			return array(
				'success' => false,
				'message' => 'Patient not found or inactive'
			);
		}
		
		// Check if patient is already member of a family
		$existing_membership = $wpdb->get_row($wpdb->prepare(
			"SELECT fm.*, f.family_name FROM {$wpdb->prefix}mbs_family_members fm 
			 JOIN {$wpdb->prefix}mbs_families f ON fm.family_id = f.id 
			 WHERE fm.patient_id = %d",
			$patient_id
		));
		
		if ($existing_membership) {
			return array(
				'success' => false,
				'message' => sprintf(
					'Patient is already member of family "%s" with relationship "%s"',
					$existing_membership->family_name,
					$this->get_relationship_label($existing_membership->relationship_type)
				)
			);
		}
		
		// Check if patient is already head of a family
		$existing_head = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_families WHERE head_patient_id = %d",
			$patient_id
		));
		
		if ($existing_head) {
			return array(
				'success' => false,
				'message' => 'Patient is already head of family "' . $existing_head->family_name . '". Cannot be member in another family.'
			);
		}
		
		// Add member
		$result = $wpdb->insert(
			$wpdb->prefix . 'mbs_family_members',
			array(
				'family_id' => intval($family_id),
				'patient_id' => intval($patient_id),
				'relationship_type' => sanitize_text_field($relationship_type),
				'notes' => sanitize_textarea_field($notes)
			),
			array('%d', '%d', '%s', '%s')
		);
		
		if ($result !== false) {
			return array(
				'success' => true,
				'message' => 'Member added successfully to family'
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Error adding member: ' . $wpdb->last_error
			);
		}
	}
	
	/**
	 * Get family members
	 */
	private function get_family_members($family_id) {
		global $wpdb;
		
		$sql = $wpdb->prepare(
			"SELECT 
				fm.*,
				p.first_name,
				p.last_name,
				p.cnp,
				p.birth_date,
				p.age,
				p.gender,
				p.email,
				p.phone
			FROM {$wpdb->prefix}mbs_family_members fm
			JOIN {$wpdb->prefix}mbs_patients p ON fm.patient_id = p.id
			WHERE fm.family_id = %d
			ORDER BY fm.relationship_type, p.first_name, p.last_name",
			$family_id
		);
		
		return $wpdb->get_results($sql);
	}
	
	/**
	 * Get all families
	 */
	private function get_all_families($page = 1, $per_page = 20, $search = '') {
		global $wpdb;
		
		$where = "1=1";
		$params = array();
		
		if (!empty($search)) {
			$search_term = '%' . $wpdb->esc_like($search) . '%';
			$where .= " AND (f.family_name LIKE %s OR p.first_name LIKE %s OR p.last_name LIKE %s)";
			$params[] = $search_term;
			$params[] = $search_term;
			$params[] = $search_term;
		}
		
		// Pagination
		$page = max(1, intval($page));
		$per_page = max(1, intval($per_page));
		$offset = ($page - 1) * $per_page;
		
		$sql = "SELECT 
					f.*,
					p.first_name as head_first_name,
					p.last_name as head_last_name,
					p.cnp as head_cnp,
					COUNT(fm.id) as member_count
				FROM {$wpdb->prefix}mbs_families f
				JOIN {$wpdb->prefix}mbs_patients p ON f.head_patient_id = p.id
				LEFT JOIN {$wpdb->prefix}mbs_family_members fm ON f.id = fm.family_id
				WHERE {$where}
				GROUP BY f.id
				ORDER BY f.family_name
				LIMIT %d OFFSET %d";
		
		$params[] = $per_page;
		$params[] = $offset;
		
		return $wpdb->get_results($wpdb->prepare($sql, $params));
	}
	
	/**
	 * Remove family member
	 */
	private function remove_family_member($family_id, $patient_id) {
		global $wpdb;
		
		// Check if is head of family
		$family = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_families WHERE id = %d AND head_patient_id = %d",
			$family_id, $patient_id
		));
		
		if ($family) {
			return array(
				'success' => false,
				'message' => 'Cannot remove head of family. Delete entire family or transfer role.'
			);
		}
		
		$result = $wpdb->delete(
			$wpdb->prefix . 'mbs_family_members',
			array(
				'family_id' => intval($family_id),
				'patient_id' => intval($patient_id)
			),
			array('%d', '%d')
		);
		
		if ($result !== false) {
			return array(
				'success' => true,
				'message' => 'Member removed from family'
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Error removing member: ' . $wpdb->last_error
			);
		}
	}
	
	/**
	 * Delete entire family
	 */
	private function delete_family($family_id) {
		global $wpdb;
		
		// Delete family members
		$wpdb->delete(
			$wpdb->prefix . 'mbs_family_members',
			array('family_id' => intval($family_id)),
			array('%d')
		);
		
		// Delete family
		$result = $wpdb->delete(
			$wpdb->prefix . 'mbs_families',
			array('id' => intval($family_id)),
			array('%d')
		);
		
		if ($result !== false) {
			return array(
				'success' => true,
				'message' => 'Family deleted successfully'
			);
		} else {
			return array(
				'success' => false,
				'message' => 'Error deleting family: ' . $wpdb->last_error
			);
		}
	}
	
	/**
	 * Get family statistics
	 */
	private function get_family_stats() {
		global $wpdb;
		
		$stats = array();
		
		// Total families
		$stats['total_families'] = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}mbs_families"
		);
		
		// Total family members
		$stats['total_family_members'] = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}mbs_family_members"
		);
		
		// Distribution by relationship
		$stats['by_relationship'] = $wpdb->get_results(
			"SELECT relationship_type, COUNT(*) as count 
			 FROM {$wpdb->prefix}mbs_family_members 
			 GROUP BY relationship_type 
			 ORDER BY count DESC"
		);
		
		// Largest families
		$stats['largest_families'] = $wpdb->get_results(
			"SELECT f.family_name, COUNT(fm.id) as member_count
			 FROM {$wpdb->prefix}mbs_families f
			 LEFT JOIN {$wpdb->prefix}mbs_family_members fm ON f.id = fm.family_id
			 GROUP BY f.id
			 ORDER BY member_count DESC
			 LIMIT 10"
		);
		
		return $stats;
	}
	
	/**
	 * Get family details by ID
	 */
	private function get_family_details($family_id) {
		global $wpdb;
		
		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_families WHERE id = %d",
			$family_id
		));
	}
	
	/**
	 * Get family member by patient ID in specific family
	 */
	private function get_family_member_by_patient($family_id, $patient_id) {
		global $wpdb;
		
		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_family_members 
			WHERE family_id = %d AND patient_id = %d",
			$family_id, $patient_id
		));
	}
	
	/**
	 * Check if patient is head of any family
	 */
	private function is_patient_head_of_family($patient_id) {
		global $wpdb;
		
		$result = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}mbs_families WHERE head_patient_id = %d",
			$patient_id
		));
		
		return $result > 0;
	}
	
	/**
	 * Remove family member by member ID (for admin interface)
	 */
	private function remove_family_member_by_id($member_id) {
		global $wpdb;
		
		$result = $wpdb->delete(
			$wpdb->prefix . 'mbs_family_members',
			array('id' => intval($member_id)),
			array('%d')
		);
		
		return $result !== false;
	}
	
	/**
	 * Get patient details by ID
	 */
	private function get_patient_details($patient_id) {
		global $wpdb;
		
		return $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_patients WHERE id = %d AND is_active = 1",
			$patient_id
		));
	}
	
	/**
	 * Get relationship label for display
	 */
	private function get_relationship_label($relationship_type) {
		$labels = array(
			'head' => 'Head of Family',
			'spouse' => 'Spouse',
			'child' => 'Child',
			'parent' => 'Parent',
			'sibling' => 'Sibling',
			'other' => 'Other'
		);
		
		return isset($labels[$relationship_type]) ? $labels[$relationship_type] : $relationship_type;
	}
}
