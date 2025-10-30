<?php
/**
 * REST API for Medical Booking System
 */
if (!defined('ABSPATH')) { exit; }

class MBS_API {
	private static $instance = null;
	private $rate_limit_key = 'mbs_api_rate_';

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action('rest_api_init', array($this, 'register_routes'));
	}

	public function register_routes() {
		$ns = 'mbs/v1';

		// Authentication endpoints
		register_rest_route($ns, '/auth/register', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array($this, 'register_user'),
			'permission_callback' => '__return_true',
		));

		register_rest_route($ns, '/auth/register-staff', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array($this, 'register_medical_staff'),
			'permission_callback' => array($this, 'can_register_staff'),
		));

		register_rest_route($ns, '/auth/login', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array($this, 'login_user'),
			'permission_callback' => '__return_true',
		));

		register_rest_route($ns, '/auth/me', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_current_user'),
			'permission_callback' => 'is_user_logged_in',
		));

		register_rest_route($ns, '/auth/phones', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_user_phones'),
			'permission_callback' => 'is_user_logged_in',
		));

		register_rest_route($ns, '/auth/phones', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array($this, 'add_user_phone'),
			'permission_callback' => 'is_user_logged_in',
		));

		register_rest_route($ns, '/auth/phones/(?P<id>\d+)', array(
			'methods'  => WP_REST_Server::DELETABLE,
			'callback' => array($this, 'delete_user_phone'),
			'permission_callback' => 'is_user_logged_in',
		));

		register_rest_route($ns, '/auth/phones/(?P<id>\d+)/primary', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array($this, 'set_primary_phone'),
			'permission_callback' => 'is_user_logged_in',
		));

		// Booking endpoints
		register_rest_route($ns, '/services', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_services'),
			'permission_callback' => '__return_true',
		));

		register_rest_route($ns, '/doctors', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_doctors'),
			'permission_callback' => '__return_true',
		));

		register_rest_route($ns, '/slots', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_slots'),
			'permission_callback' => '__return_true',
		));

		register_rest_route($ns, '/slots/enhanced', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_enhanced_slots'),
			'permission_callback' => '__return_true',
		));

		register_rest_route($ns, '/appointments', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array($this, 'create_appointment'),
			'permission_callback' => array($this, 'can_create_appointment'),
		));

		register_rest_route($ns, '/appointments', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'list_appointments'),
			'permission_callback' => array($this, 'can_list_appointments'),
		));

		// Family members (basic) - returns current patient as main + optional members
		register_rest_route($ns, '/family-members', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_family_members_current_user'),
			'permission_callback' => 'is_user_logged_in',
		));
	}

	private function check_rate_limit(WP_REST_Request $request) {
		$user = get_current_user_id();
		$key = $this->rate_limit_key . ($user ?: $request->get_header('X-Forwarded-For') ?: $_SERVER['REMOTE_ADDR']);
		$hits = (int) get_transient($key);
		if ($hits >= 20) { // 20 requests per minute
			return new WP_Error('rate_limited', __('Too many requests. Please slow down.', 'medical-booking-system'), array('status' => 429));
		}
		set_transient($key, $hits + 1, MINUTE_IN_SECONDS);
		return true;
	}

	public function can_create_appointment() {
		return current_user_can('mbs_create_appointments') || current_user_can('mbs_view_own_appointments');
	}

	public function can_list_appointments() {
		return is_user_logged_in();
	}

	public function can_register_staff() {
		return current_user_can('mbs_create_doctors') || current_user_can('manage_options');
	}

	public function get_services(WP_REST_Request $request) {
		global $wpdb;
		$rows = $wpdb->get_results("SELECT id,name,description,duration,price FROM {$wpdb->prefix}mbs_services WHERE is_active=1 ORDER BY name ASC", ARRAY_A);
		return rest_ensure_response($rows ?: array());
	}

	public function get_doctors(WP_REST_Request $request) {
		global $wpdb;
		$service_id = (int) $request->get_param('serviceId');
		if ($service_id) {
			$sql = $wpdb->prepare(
				"SELECT d.id,d.first_name,d.last_name,d.phone,d.email FROM {$wpdb->prefix}mbs_doctors d INNER JOIN {$wpdb->prefix}mbs_doctor_services ds ON d.id=ds.doctor_id WHERE ds.service_id=%d AND d.is_active=1",
				$service_id
			);
		} else {
			$sql = "SELECT id,first_name,last_name,phone,email FROM {$wpdb->prefix}mbs_doctors WHERE is_active=1";
		}
		$rows = $wpdb->get_results($sql, ARRAY_A);
		return rest_ensure_response($rows ?: array());
	}

	public function get_slots(WP_REST_Request $request) {
		$doctor_id = (int) $request->get_param('doctorId');
		$date = sanitize_text_field($request->get_param('date'));
		$duration = (int) $request->get_param('duration');
		if (!$doctor_id || !$date || !$duration) {
			return new WP_Error('invalid_params', __('Missing required parameters.', 'medical-booking-system'), array('status' => 400));
		}
		$svc = MBS_Appointment_Service::get_instance();
		$slots = $svc->get_slots($doctor_id, $date, $duration);
		return rest_ensure_response($slots);
	}

	public function get_enhanced_slots(WP_REST_Request $request) {
		$doctor_id = (int) $request->get_param('doctorId');
		$date = sanitize_text_field($request->get_param('date'));
		$service_id = (int) $request->get_param('serviceId');
		$user_type = sanitize_text_field($request->get_param('userType')) ?: 'patient';
		
		if (!$doctor_id || !$date || !$service_id) {
			return new WP_Error('invalid_params', __('Missing required parameters: doctorId, date, serviceId.', 'medical-booking-system'), array('status' => 400));
		}
		
		// Validate user_type
		if (!in_array($user_type, array('patient', 'staff'))) {
			return new WP_Error('invalid_user_type', __('Invalid userType. Must be "patient" or "staff".', 'medical-booking-system'), array('status' => 400));
		}
		
		$svc = MBS_Appointment_Service::get_instance();
		$slots = $svc->get_enhanced_slots($doctor_id, $date, $service_id, $user_type);
		
		return rest_ensure_response($slots);
	}

	public function create_appointment(WP_REST_Request $request) {
		$nonce = $request->get_header('X-WP-Nonce');
		if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
			return new WP_Error('invalid_nonce', __('Security check failed.', 'medical-booking-system'), array('status' => 403));
		}
		$rl = $this->check_rate_limit($request);
		if (is_wp_error($rl)) { return $rl; }

		$payload = $request->get_json_params();
		$svc = MBS_Appointment_Service::get_instance();
		$res = $svc->create_appointment(is_array($payload) ? $payload : array());
		if (is_wp_error($res)) { return $res; }
		return rest_ensure_response($res);
	}

	public function list_appointments(WP_REST_Request $request) {
		$svc = MBS_Appointment_Service::get_instance();
		$scope = current_user_can('mbs_view_all_appointments') ? 'all' : 'own';
		$rows = $svc->list_appointments(array(
			'user_scope' => $scope,
			'limit' => (int) $request->get_param('limit') ?: 50,
			'offset' => (int) $request->get_param('offset') ?: 0,
		));
		return rest_ensure_response($rows);
	}

	/**
	 * Return current user's patient as main and optional family members if table exists
	 */
	public function get_family_members_current_user(WP_REST_Request $request) {
		$user_id = get_current_user_id();
		if (!$user_id) {
			return new WP_Error('not_logged_in', __('Nu sunteți autentificat', 'medical-booking-system'), array('status' => 401));
		}

		global $wpdb;
		$auth = MBS_Auth::get_instance();
		$user = get_user_by('id', $user_id);
		$phones = $auth->get_user_phones($user_id);

		$main_patient = $wpdb->get_row($wpdb->prepare(
			"SELECT id, first_name, last_name, cnp, email, phone FROM {$wpdb->prefix}mbs_patients WHERE user_id=%d LIMIT 1",
			$user_id
		), ARRAY_A);

		// Extract primary phone (objects from get_results)
		$primary_phone = '';
		if (is_array($phones) && !empty($phones)) {
			$first = $phones[0];
			$primary_phone = is_object($first) ? ($first->phone ?? '') : ($first['phone'] ?? '');
		}

		$computed_name = trim(($user->first_name ?: ($main_patient['first_name'] ?? '')) . ' ' . ($user->last_name ?: ($main_patient['last_name'] ?? '')));
		if ($computed_name === '') { $computed_name = $user->display_name ?: 'Pacient'; }

		$main = array(
			'id' => $user_id,
			'name' => $computed_name,
			'cnp' => get_user_meta($user_id, 'mbs_cnp', true) ?: ($main_patient['cnp'] ?? ''),
			'cnp_masked' => $auth->mask_cnp(get_user_meta($user_id, 'mbs_cnp', true) ?: ($main_patient['cnp'] ?? '')),
			'email' => $user->user_email ?: ($main_patient['email'] ?? ''),
			'phone' => ($primary_phone !== '' ? $primary_phone : ($main_patient['phone'] ?? '')),
			'isDefault' => true,
		);

		$members = array();
		// Try to read family members if table exists (handle both schemas)
		$family_table = $wpdb->prefix . 'mbs_family_members';
		$table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $family_table));
		if ($table_exists === $family_table) {
			// Suppress notices if SHOW COLUMNS fails unexpectedly
			$wpdb->hide_errors();
			$cols = $wpdb->get_col("SHOW COLUMNS FROM {$family_table}", 0);
			$wpdb->show_errors();
			$cols = is_array($cols) ? $cols : array();
			if (in_array('main_patient_id', $cols, true)) {
				$main_id = $main_patient ? (int)$main_patient['id'] : 0;
				if ($main_id) {
					$members = $wpdb->get_results($wpdb->prepare(
						"SELECT id, nume as last_name, prenume as first_name, cnp, relatia as relationship, telefon as phone, email FROM {$family_table} WHERE main_patient_id=%d AND (status IS NULL OR status<>'deleted')",
						$main_id
					), ARRAY_A) ?: array();
				}
			} elseif (in_array('family_id', $cols, true) && in_array('patient_id', $cols, true)) {
				// Join variant with patients table
				$main_id = $main_patient ? (int)$main_patient['id'] : 0;
				if ($main_id) {
					$members = $wpdb->get_results($wpdb->prepare(
						"SELECT fm.id, p.first_name, p.last_name, p.cnp, fm.relationship_type as relationship, p.phone, p.email
						 FROM {$family_table} fm JOIN {$wpdb->prefix}mbs_patients p ON p.id=fm.patient_id WHERE fm.family_id=%d",
						$main_id
					), ARRAY_A) ?: array();
				}
			}
		}

		// Normalize members
		$members = array_map(function($m) use ($auth) {
			return array(
				'id' => (int) ($m['id'] ?? 0),
				'name' => trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')),
				'cnp' => $m['cnp'] ?? '',
				'cnp_masked' => $auth->mask_cnp($m['cnp'] ?? ''),
				'phone' => $m['phone'] ?? '',
				'email' => $m['email'] ?? '',
				'relationship' => $m['relationship'] ?? ($m['relatia'] ?? ''),
				'isDefault' => false,
			);
		}, $members);

		return rest_ensure_response(array(
			'main' => $main,
			'members' => $members,
		));
	}

	// Authentication methods

	public function register_user(WP_REST_Request $request) {
		$rl = $this->check_rate_limit($request);
		if (is_wp_error($rl)) { return $rl; }

		$data = $request->get_json_params();
		
		if (!is_array($data)) {
			return new WP_Error('invalid_data', __('Date invalide', 'medical-booking-system'), array('status' => 400));
		}

		$auth = MBS_Auth::get_instance();
		$user_id = $auth->register_user($data);

		if (is_wp_error($user_id)) {
			return $user_id;
		}

		// Auto-login after registration
		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true);

		$user = get_user_by('id', $user_id);
		
		return rest_ensure_response(array(
			'success' => true,
			'user_id' => $user_id,
			'message' => __('Înregistrare reușită', 'medical-booking-system'),
			'user' => array(
				'id' => $user->ID,
				'cnp' => get_user_meta($user->ID, 'mbs_cnp', true),
				'email' => $user->user_email,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'display_name' => $user->display_name,
				'roles' => $user->roles,
			),
		));
	}

	public function register_medical_staff(WP_REST_Request $request) {
		$rl = $this->check_rate_limit($request);
		if (is_wp_error($rl)) { return $rl; }

		$data = $request->get_json_params();
		
		if (!is_array($data)) {
			return new WP_Error('invalid_data', __('Date invalide', 'medical-booking-system'), array('status' => 400));
		}

		$auth = MBS_Auth::get_instance();
		$user_id = $auth->register_medical_staff($data);

		if (is_wp_error($user_id)) {
			return $user_id;
		}

		$user = get_user_by('id', $user_id);
		
		return rest_ensure_response(array(
			'success' => true,
			'user_id' => $user_id,
			'message' => __('Personal medical înregistrat cu succes', 'medical-booking-system'),
			'user' => array(
				'id' => $user->ID,
				'cnp' => get_user_meta($user->ID, 'mbs_cnp', true),
				'email' => $user->user_email,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'display_name' => $user->display_name,
				'roles' => $user->roles,
			),
		));
	}

	public function login_user(WP_REST_Request $request) {
		$rl = $this->check_rate_limit($request);
		if (is_wp_error($rl)) { return $rl; }

		$data = $request->get_json_params();
		
		if (empty($data['identifier']) || empty($data['password'])) {
			return new WP_Error('missing_credentials', __('Identificator și parolă sunt obligatorii', 'medical-booking-system'), array('status' => 400));
		}

		$identifier = sanitize_text_field($data['identifier']);
		$password = $data['password'];
		$remember = !empty($data['remember']);

		// Attempt authentication
		$user = wp_authenticate($identifier, $password);

		if (is_wp_error($user)) {
			return new WP_Error('login_failed', __('Identificator sau parolă incorectă', 'medical-booking-system'), array('status' => 401));
		}

		// Set authentication cookies
		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID, $remember);

		$auth = MBS_Auth::get_instance();
		$phones = $auth->get_user_phones($user->ID);

		return rest_ensure_response(array(
			'success' => true,
			'message' => __('Autentificare reușită', 'medical-booking-system'),
			'user' => array(
				'id' => $user->ID,
				'cnp' => get_user_meta($user->ID, 'mbs_cnp', true),
				'email' => $user->user_email,
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'display_name' => $user->display_name,
				'roles' => $user->roles,
				'phones' => $phones,
			),
		));
	}

	public function get_current_user(WP_REST_Request $request) {
		$user_id = get_current_user_id();
		
		if (!$user_id) {
			return new WP_Error('not_logged_in', __('Nu sunteți autentificat', 'medical-booking-system'), array('status' => 401));
		}

		$user = get_user_by('id', $user_id);
		$auth = MBS_Auth::get_instance();
		$phones = $auth->get_user_phones($user_id);

		return rest_ensure_response(array(
			'id' => $user->ID,
			'cnp' => get_user_meta($user->ID, 'mbs_cnp', true),
			'cnp_masked' => $auth->mask_cnp(get_user_meta($user->ID, 'mbs_cnp', true)),
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'display_name' => $user->display_name,
			'roles' => $user->roles,
			'phones' => $phones,
		));
	}

	public function get_user_phones(WP_REST_Request $request) {
		$user_id = get_current_user_id();
		$auth = MBS_Auth::get_instance();
		$phones = $auth->get_user_phones($user_id);

		return rest_ensure_response($phones);
	}

	public function add_user_phone(WP_REST_Request $request) {
		$user_id = get_current_user_id();
		$data = $request->get_json_params();
		
		if (empty($data['phone'])) {
			return new WP_Error('missing_phone', __('Număr telefon lipsă', 'medical-booking-system'), array('status' => 400));
		}

		$auth = MBS_Auth::get_instance();
		
		if (!$auth->is_phone($data['phone'])) {
			return new WP_Error('invalid_phone', __('Format telefon invalid', 'medical-booking-system'), array('status' => 400));
		}

		$is_primary = !empty($data['is_primary']);
		$phone_id = $auth->add_user_phone($user_id, $data['phone'], $is_primary);

		if (!$phone_id) {
			return new WP_Error('phone_add_failed', __('Eroare la adăugarea telefonului', 'medical-booking-system'), array('status' => 500));
		}

		return rest_ensure_response(array(
			'success' => true,
			'phone_id' => $phone_id,
			'message' => __('Telefon adăugat cu succes', 'medical-booking-system'),
		));
	}

	public function delete_user_phone(WP_REST_Request $request) {
		$user_id = get_current_user_id();
		$phone_id = (int) $request->get_param('id');
		
		$auth = MBS_Auth::get_instance();
		$result = $auth->delete_user_phone($user_id, $phone_id);

		if (!$result) {
			return new WP_Error('phone_delete_failed', __('Eroare la ștergerea telefonului', 'medical-booking-system'), array('status' => 500));
		}

		return rest_ensure_response(array(
			'success' => true,
			'message' => __('Telefon șters cu succes', 'medical-booking-system'),
		));
	}

	public function set_primary_phone(WP_REST_Request $request) {
		$user_id = get_current_user_id();
		$phone_id = (int) $request->get_param('id');
		
		$auth = MBS_Auth::get_instance();
		$result = $auth->set_primary_phone($user_id, $phone_id);

		if (!$result) {
			return new WP_Error('phone_primary_failed', __('Eroare la setarea telefonului principal', 'medical-booking-system'), array('status' => 500));
		}

		return rest_ensure_response(array(
			'success' => true,
			'message' => __('Telefon principal actualizat', 'medical-booking-system'),
		));
	}
}
