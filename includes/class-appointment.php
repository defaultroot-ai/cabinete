<?php
/**
 * Appointment Service
 *
 * Minimal end-to-end appointment flow helpers used by REST API.
 */

if (!defined('ABSPATH')) {
	exit;
}

class MBS_Appointment_Service {
	private static $instance = null;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function current_user_can_role_caps(string $action, array $context = array()): bool {
		// Map high-level actions to caps defined in class-database roles
		$action_to_cap = array(
			'appointment_create' => 'mbs_create_appointments',
			'appointment_edit'   => 'mbs_edit_appointments',
			'appointment_cancel' => 'mbs_cancel_appointments',
			'appointment_view_all' => 'mbs_view_all_appointments',
			'appointment_view_own' => 'mbs_view_own_appointments',
		);

		$cap = $action_to_cap[$action] ?? '';
		if ($cap && current_user_can($cap)) {
			return true;
		}

		// Fallbacks for patient viewing own
		if ($action === 'appointment_view_own' && is_user_logged_in()) {
			return true;
		}
		return false;
	}

	public function list_appointments(array $args = array()) {
		global $wpdb;
		$table = $wpdb->prefix . 'mbs_appointments';

		$defaults = array(
			'limit' => 50,
			'offset' => 0,
			'user_scope' => 'auto', // auto, own, all
		);
		$params = wp_parse_args($args, $defaults);

		$scope_all = $this->current_user_can_role_caps('appointment_view_all');
		$scope_own = $this->current_user_can_role_caps('appointment_view_own');

		$where = array();
		$values = array();

		if ($params['user_scope'] === 'all' || ($params['user_scope'] === 'auto' && $scope_all)) {
			// no user constraint
		} else {
			// own appointments by patient user or doctor user
			$user_id = get_current_user_id();
			$where[] = '(created_by = %d OR patient_id IN (SELECT id FROM ' . $wpdb->prefix . 'mbs_patients WHERE user_id = %d))';
			$values[] = $user_id;
			$values[] = $user_id;
		}

		$sql = "SELECT * FROM {$table}";
		if (!empty($where)) {
			$sql .= ' WHERE ' . implode(' AND ', $where);
		}
		$sql .= ' ORDER BY appointment_date DESC, start_time DESC';
		$sql .= $wpdb->prepare(' LIMIT %d OFFSET %d', (int)$params['limit'], (int)$params['offset']);

		if (!empty($values)) {
			$sql = $wpdb->prepare($sql, $values);
		}

		$rows = $wpdb->get_results($sql, ARRAY_A);
		return $rows ?: array();
	}

	public function create_appointment(array $data) {
		if (!$this->current_user_can_role_caps('appointment_create')) {
			return new WP_Error('forbidden', __('You do not have permission to create appointments.', 'medical-booking-system'), array('status' => 403));
		}

		$required = array('doctor_id','patient_id','service_id','appointment_date','start_time','end_time');
		foreach ($required as $key) {
			if (empty($data[$key])) {
				return new WP_Error('invalid_params', sprintf(__('Missing field: %s', 'medical-booking-system'), $key), array('status' => 400));
			}
		}

		global $wpdb;
		$table = $wpdb->prefix . 'mbs_appointments';

		$code = 'APT-' . date('Ymd') . '-' . wp_generate_password(4, false, false);
		$row = array(
			'appointment_code' => $code,
			'doctor_id' => (int)$data['doctor_id'],
			'patient_id' => (int)$data['patient_id'],
			'service_id' => (int)$data['service_id'],
			'appointment_date' => sanitize_text_field($data['appointment_date']),
			'start_time' => sanitize_text_field($data['start_time']),
			'end_time' => sanitize_text_field($data['end_time']),
			'notes' => isset($data['notes']) ? wp_kses_post($data['notes']) : '',
			'patient_notes' => isset($data['patient_notes']) ? wp_kses_post($data['patient_notes']) : '',
			'price' => isset($data['price']) ? floatval($data['price']) : 0.0,
			'created_by' => get_current_user_id(),
			'status' => 'confirmed',
		);

		// Basic conflict check: overlap on same doctor/date/time
		$overlap = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(1) FROM {$table} WHERE doctor_id=%d AND appointment_date=%s AND NOT( end_time<=%s OR start_time>=%s ) AND status IN ('pending','confirmed')",
			$row['doctor_id'], $row['appointment_date'], $row['start_time'], $row['end_time']
		));
		if ($overlap) {
			return new WP_Error('conflict', __('Selected time overlaps an existing appointment.', 'medical-booking-system'), array('status' => 409));
		}

		$ok = $wpdb->insert($table, $row);
		if (!$ok) {
			return new WP_Error('db_error', __('Could not create appointment.', 'medical-booking-system'), array('status' => 500));
		}
		$row['id'] = (int)$wpdb->insert_id;
		return $row;
	}

	public function get_slots(int $doctor_id, string $date, int $duration_minutes) {
		global $wpdb;
		$schedules = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_doctor_schedules WHERE doctor_id=%d AND day_of_week=%d AND is_active=1",
			$doctor_id, (int) date('w', strtotime($date))
		), ARRAY_A);

		$breaks = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_doctor_breaks WHERE doctor_id=%d AND break_date=%s",
			$doctor_id, $date
		), ARRAY_A);

		$appointments = $wpdb->get_results($wpdb->prepare(
			"SELECT start_time,end_time FROM {$wpdb->prefix}mbs_appointments WHERE doctor_id=%d AND appointment_date=%s AND status IN ('pending','confirmed')",
			$doctor_id, $date
		), ARRAY_A);

		$to_minutes = function($hhmm) { list($h,$m) = array_map('intval', explode(':', $hhmm)); return $h*60+$m; };
		$to_hhmm = function($minutes) { $h = floor($minutes/60); $m = $minutes%60; return sprintf('%02d:%02d', $h, $m); };

		$blocked_ranges = array();
		foreach ($appointments as $a) {
			$blocked_ranges[] = array($to_minutes($a['start_time']), $to_minutes($a['end_time']));
		}
		foreach ($breaks as $b) {
			if (!empty($b['is_all_day']) && $b['is_all_day']) {
				// whole day blocked
				return array();
			}
			if ($b['start_time'] && $b['end_time']) {
				$blocked_ranges[] = array($to_minutes($b['start_time']), $to_minutes($b['end_time']));
			}
		}

		$slots = array();
		foreach ($schedules as $s) {
			$start = $to_minutes($s['start_time']);
			$end   = $to_minutes($s['end_time']);
			for ($t = $start; $t + $duration_minutes <= $end; $t += $duration_minutes) {
				$slot_start = $t;
				$slot_end   = $t + $duration_minutes;

				$overlaps = false;
				foreach ($blocked_ranges as $br) {
					if (!($slot_end <= $br[0] || $slot_start >= $br[1])) { $overlaps = true; break; }
				}
				if (!$overlaps) {
					$slots[] = array(
						'time' => $to_hhmm($slot_start) . '-' . $to_hhmm($slot_end),
						'status' => 'available'
					);
				}
			}
		}
		return $slots;
	}
}
