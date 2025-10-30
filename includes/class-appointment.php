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
        // Enforce authentication: only logged-in users can create appointments
        if (!is_user_logged_in()) {
            return new WP_Error('not_logged_in', __('Nu sunteți autentificat', 'medical-booking-system'), array('status' => 401));
        }

        // Permissions: allow staff with create capability OR patients creating their own
        $is_staff_can_create = $this->current_user_can_role_caps('appointment_create');
        $is_logged_patient = current_user_can('mbs_view_own_appointments') || in_array('mbs_patient', (array) wp_get_current_user()->roles, true);
        if (!$is_staff_can_create && !$is_logged_patient) {
            return new WP_Error('forbidden', __('You do not have permission to create appointments.', 'medical-booking-system'), array('status' => 403));
        }

        // Resolve patient_id for logged-in patient users (no guests allowed)
        $current_user_id = get_current_user_id();
        if ($is_logged_patient) {
            $data['patient_id'] = $this->ensure_patient_for_user($current_user_id, $data);
            if (is_wp_error($data['patient_id'])) { return $data['patient_id']; }
        } else {
            // Staff flow: validate provided patient_id exists
            if (empty($data['patient_id'])) {
                return new WP_Error('invalid_params', __('Missing field: patient_id', 'medical-booking-system'), array('status' => 400));
            }
            if (!$this->patient_exists((int)$data['patient_id'])) {
                return new WP_Error('invalid_patient', __('Patient not found', 'medical-booking-system'), array('status' => 400));
            }
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

    private function patient_exists(int $patient_id): bool {
        global $wpdb;
        $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}mbs_patients WHERE id=%d AND is_active=1", $patient_id));
        return !empty($id);
    }

    /**
     * Ensure there is a patient row linked to a WP user and return its ID.
     * If missing, create a minimal patient from current user's profile/meta.
     */
    private function ensure_patient_for_user(int $user_id, array $data) {
        global $wpdb;

        // If client passed a non-zero patient_id, force it to the logged user's patient to avoid spoofing
        $existing_patient_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}mbs_patients WHERE user_id=%d LIMIT 1",
            $user_id
        ));
        if ($existing_patient_id) {
            return $existing_patient_id;
        }

        // Create minimal patient from user data
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('invalid_user', __('Utilizator invalid', 'medical-booking-system'), array('status' => 400));
        }
        $first_name = $user->first_name ?: sanitize_text_field($data['first_name'] ?? '');
        $last_name = $user->last_name ?: sanitize_text_field($data['last_name'] ?? '');
        $email = $user->user_email ?: sanitize_email($data['email'] ?? '');
        $cnp = get_user_meta($user_id, 'mbs_cnp', true);

        $wpdb->insert($wpdb->prefix . 'mbs_patients', array(
            'user_id' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'cnp' => $cnp ?: null,
            'is_active' => 1,
        ));
        if (!$wpdb->insert_id) {
            return new WP_Error('db_error', __('Could not create patient for current user.', 'medical-booking-system'), array('status' => 500));
        }
        return (int) $wpdb->insert_id;
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

	/**
	 * Get enhanced slots with new configuration system
	 * 
	 * @param int $doctor_id Doctor ID
	 * @param string $date Date in Y-m-d format
	 * @param int $service_id Service ID
	 * @param string $user_type 'patient' or 'staff' (for filtering staff_only slots)
	 * @return array Enhanced slots with configuration
	 */
	public function get_enhanced_slots(int $doctor_id, string $date, int $service_id, string $user_type = 'patient') {
		// Debug logging
		error_log("MBS: Getting enhanced slots for doctor_id=$doctor_id, date=$date, service_id=$service_id, user_type=$user_type");
		
		// 1. Get service duration
		$service_duration = $this->get_service_duration($service_id);
		if (!$service_duration) {
			error_log("MBS: Service not found for service_id=$service_id");
			return array();
		}
		
		// 2. Get slot settings for this doctor-service combination
		$slot_settings = $this->get_slot_settings($doctor_id, $service_id);
		if (!$slot_settings) {
			error_log("MBS: No slot settings found for doctor_id=$doctor_id, service_id=$service_id");
			return array();
		}
		
		// 3. Get doctor schedule for this date
		$schedule = $this->get_doctor_schedule($doctor_id, $date);
		if (empty($schedule)) {
			error_log("MBS: No schedule found for doctor_id=$doctor_id on date=$date");
			return array();
		}
		
		// 4. Get hidden slots and staff only slots
		$hidden_slots = $this->get_hidden_slots($doctor_id, $date);
		$staff_slots = $this->get_staff_only_slots($doctor_id, $date);
		
		// 5. Get existing appointments
		$existing_appointments = $this->get_existing_appointments($doctor_id, $date);
		
		// 6. Generate available slots
		$available_slots = $this->generate_available_slots(
			$schedule, 
			$service_duration, 
			$slot_settings,
			$hidden_slots, 
			$staff_slots,
			$existing_appointments,
			$user_type
		);
		
		error_log("MBS: Generated " . count($available_slots) . " enhanced slots");
		return $available_slots;
	}

	/**
	 * Get service duration
	 */
	private function get_service_duration(int $service_id): ?int {
		global $wpdb;
		
		$duration = $wpdb->get_var($wpdb->prepare(
			"SELECT duration FROM {$wpdb->prefix}mbs_services WHERE id=%d AND is_active=1",
			$service_id
		));
		
		return $duration ? (int)$duration : null;
	}

	/**
	 * Get slot settings for doctor-service combination
	 */
	private function get_slot_settings(int $doctor_id, int $service_id): ?array {
		global $wpdb;
		
		$settings = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_doctor_slot_settings 
			 WHERE doctor_id=%d AND service_id=%d AND is_active=1",
			$doctor_id, $service_id
		), ARRAY_A);
		
		return $settings ?: null;
	}

	/**
	 * Get doctor schedule for specific date
	 */
	private function get_doctor_schedule(int $doctor_id, string $date): array {
		global $wpdb;
		
		$day_of_week = (int) date('w', strtotime($date));
		
		$schedules = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_doctor_schedules 
			 WHERE doctor_id=%d AND day_of_week=%d AND is_active=1",
			$doctor_id, $day_of_week
		), ARRAY_A);
		
		return $schedules;
	}

	/**
	 * Get hidden slots for doctor and date
	 */
	private function get_hidden_slots(int $doctor_id, string $date): array {
		global $wpdb;
		
		$day_of_week = (int) date('w', strtotime($date));
		
		$hidden_slots = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_hidden_slots 
			 WHERE doctor_id=%d AND slot_type='hidden' 
			 AND (day_of_week=%d OR specific_date=%s)",
			$doctor_id, $day_of_week, $date
		), ARRAY_A);
		
		return $hidden_slots;
	}

	/**
	 * Get staff only slots for doctor and date
	 */
	private function get_staff_only_slots(int $doctor_id, string $date): array {
		global $wpdb;
		
		$day_of_week = (int) date('w', strtotime($date));
		
		$staff_slots = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}mbs_hidden_slots 
			 WHERE doctor_id=%d AND slot_type='staff_only' 
			 AND (day_of_week=%d OR specific_date=%s)",
			$doctor_id, $day_of_week, $date
		), ARRAY_A);
		
		return $staff_slots;
	}

	/**
	 * Get existing appointments for doctor and date
	 */
	private function get_existing_appointments(int $doctor_id, string $date): array {
		global $wpdb;
		
		$appointments = $wpdb->get_results($wpdb->prepare(
			"SELECT start_time, end_time FROM {$wpdb->prefix}mbs_appointments 
			 WHERE doctor_id=%d AND appointment_date=%s AND status IN ('pending','confirmed')",
			$doctor_id, $date
		), ARRAY_A);
		
		return $appointments;
	}

	/**
	 * Generate available slots with enhanced logic
	 */
	private function generate_available_slots(
		array $schedule, 
		int $service_duration, 
		array $slot_settings,
		array $hidden_slots, 
		array $staff_slots,
		array $existing_appointments,
		string $user_type
	): array {
		
		// Helper functions
		$to_minutes = function($hhmm) { 
			list($h,$m) = array_map('intval', explode(':', $hhmm)); 
			return $h*60+$m; 
		};
		$to_hhmm = function($minutes) { 
			$h = floor($minutes/60); 
			$m = $minutes%60; 
			return sprintf('%02d:%02d', $h, $m); 
		};
		
		// Get slot interval (use service duration or configured interval, whichever is larger)
		$slot_interval = max($service_duration, $slot_settings['slot_interval']);
		$buffer_time = $slot_settings['buffer_time'];
		
		// Convert hidden slots to blocked ranges
		$blocked_ranges = array();
		foreach ($hidden_slots as $slot) {
			$start_minutes = $to_minutes($slot['slot_time']);
			$end_minutes = $start_minutes + $service_duration;
			$blocked_ranges[] = array($start_minutes, $end_minutes, 'hidden', $slot['reason']);
		}
		
		// Convert staff slots to blocked ranges (only for patients)
		// For staff users, we'll handle staff slots differently
		if ($user_type === 'patient') {
			foreach ($staff_slots as $slot) {
				$start_minutes = $to_minutes($slot['slot_time']);
				$end_minutes = $start_minutes + $service_duration;
				$blocked_ranges[] = array($start_minutes, $end_minutes, 'staff_only', $slot['reason']);
			}
		}
		
		// Convert existing appointments to blocked ranges
		foreach ($existing_appointments as $appointment) {
			$start_minutes = $to_minutes($appointment['start_time']);
			$end_minutes = $to_minutes($appointment['end_time']);
			$blocked_ranges[] = array($start_minutes, $end_minutes, 'booked', 'Programare existentă');
		}
		
		$available_slots = array();
		
		foreach ($schedule as $schedule_item) {
			$schedule_start = $to_minutes($schedule_item['start_time']);
			$schedule_end = $to_minutes($schedule_item['end_time']);
			
			// Generate slots with configured interval
			for ($t = $schedule_start; $t + $service_duration <= $schedule_end; $t += $slot_interval) {
				$slot_start = $t;
				$slot_end = $t + $service_duration;
				
				// Check if this slot is a staff slot for staff users (before checking blocked ranges)
				$is_staff_slot = false;
				$staff_notes = null;
				if ($user_type === 'staff') {
					foreach ($staff_slots as $staff_slot) {
						$staff_start = $to_minutes($staff_slot['slot_time']);
						if ($staff_start === $slot_start) {
							$is_staff_slot = true;
							$staff_notes = $staff_slot['staff_notes'] ?? null;
							break;
						}
					}
				}
				
				// Check if slot overlaps with blocked ranges
				$is_blocked = false;
				$block_reason = '';
				$slot_type = 'available';
				
				// Skip blocking check for staff slots when user is staff
				if (!$is_staff_slot) {
					foreach ($blocked_ranges as $blocked_range) {
						$block_start = $blocked_range[0];
						$block_end = $blocked_range[1];
						$block_type = $blocked_range[2];
						$reason = $blocked_range[3];
						
						// Check for overlap
						if (!($slot_end <= $block_start || $slot_start >= $block_end)) {
							$is_blocked = true;
							$block_reason = $reason;
							$slot_type = $block_type;
							break;
						}
					}
				}
				
				// Add buffer time if configured
				if (!$is_blocked && $buffer_time > 0) {
					$slot_end_with_buffer = $slot_end + $buffer_time;
					foreach ($blocked_ranges as $blocked_range) {
						$block_start = $blocked_range[0];
						$block_end = $blocked_range[1];
						
						// Check if buffer time overlaps
						if (!($slot_end_with_buffer <= $block_start || $slot_end >= $block_end)) {
							$is_blocked = true;
							$block_reason = 'Buffer time conflict';
							$slot_type = 'buffer_conflict';
							break;
						}
					}
				}
				
				// Add slot to results
				$slot_data = array(
					'time' => $to_hhmm($slot_start) . '-' . $to_hhmm($slot_end),
					'status' => $is_staff_slot ? 'staff_available' : ($is_blocked ? $slot_type : 'available'),
					'start_time' => $to_hhmm($slot_start),
					'end_time' => $to_hhmm($slot_end),
					'duration' => $service_duration,
					'interval' => $slot_interval,
					'buffer_time' => $buffer_time
				);
				
				if ($is_blocked && !$is_staff_slot) {
					$slot_data['block_reason'] = $block_reason;
				}
				
				if ($is_staff_slot) {
					$slot_data['staff_notes'] = $staff_notes;
				}
				
				$available_slots[] = $slot_data;
			}
		}
		
		return $available_slots;
	}

	/**
	 * Get staff notes for a specific slot
	 */
	private function get_staff_notes_for_slot(array $staff_slots, int $slot_start_minutes): ?string {
		$to_minutes = function($hhmm) { 
			list($h,$m) = array_map('intval', explode(':', $hhmm)); 
			return $h*60+$m; 
		};
		
		foreach ($staff_slots as $staff_slot) {
			$staff_start = $to_minutes($staff_slot['slot_time']);
			if ($staff_start === $slot_start_minutes) {
				return $staff_slot['staff_notes'] ?? null;
			}
		}
		
		return null;
	}

	/**
	 * Filter slots by user type
	 */
	public function filter_slots_by_user_type(array $slots, string $user_type): array {
		if ($user_type === 'patient') {
			// Patients can't see staff_only slots
			return array_filter($slots, function($slot) {
				return $slot['status'] !== 'staff_only';
			});
		}
		
		// Staff can see all slots
		return $slots;
	}
}
