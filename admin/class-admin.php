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
			'medical-booking',
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
			__('Settings', 'medical-booking-system'),
			__('Settings', 'medical-booking-system'),
			'mbs_manage_settings',
			'medical-booking-settings',
			array($this, 'render_settings')
		);
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
	
	public function render_settings() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Settings', 'medical-booking-system') . '</h1>';
		echo '<p>Settings page - coming soon.</p>';
		echo '</div>';
	}
}
