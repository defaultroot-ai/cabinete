<?php
/**
 * Settings Management Class
 * 
 * @package MedicalBookingSystem
 * @since 1.2.0
 */

if (!defined('ABSPATH')) { exit; }

class MBS_Settings {
	private static $instance = null;
	
	private $option_name = 'mbs_settings';
	private $current_tab = 'general';
	
	public static function get_instance() {
		if (null === self::$instance) { 
			self::$instance = new self(); 
		}
		return self::$instance;
	}
	
	private function __construct() {
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_menu', array($this, 'add_settings_page'), 20);
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}
	
	/**
	 * Add settings page to admin menu
	 */
	public function add_settings_page() {
		add_submenu_page(
			'medical-booking',
			__('Settings', 'medical-booking-system'),
			__('Settings', 'medical-booking-system'),
			'manage_options',
			'mbs-settings',
			array($this, 'render_settings_page')
		);
	}
	
	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting('mbs_settings_group', $this->option_name, array($this, 'sanitize_settings'));
	}
	
	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_scripts($hook) {
		if ('medical-booking_page_mbs-settings' !== $hook) {
			return;
		}
		
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		
		wp_enqueue_script(
			'mbs-settings-script',
			MBS_PLUGIN_URL . 'assets/js/admin-settings.js',
			array('jquery', 'wp-color-picker'),
			MBS_VERSION,
			true
		);
	}
	
	/**
	 * Get default settings
	 */
	public static function get_defaults() {
		return array(
			// General Settings
			'business_name' => get_bloginfo('name'),
			'timezone' => wp_timezone_string(),
			'date_format' => 'wordpress', // wordpress, dd/mm/yyyy, mm/dd/yyyy, yyyy-mm-dd, dd-mm-yyyy, dd.mm.yyyy
			'time_format' => 'wordpress', // wordpress, 24h, 12h
			'phone' => '',
			'email' => get_option('admin_email'),
			'address' => '',
			
			// Booking Settings
			'advance_booking_days' => 30,
			'max_appointments_per_day' => 20,
			'min_time_between_appointments' => 15,
			'allow_patient_cancellation' => 'yes',
			'cancellation_deadline_hours' => 24,
			'cancellation_reason_required' => 'no',
			'auto_confirm_appointments' => 'yes',
			'patient_notes_required' => 'optional',
			'show_available_slots_count' => 'yes',
			'show_service_price' => 'yes',
			'show_service_duration' => 'yes',
			'time_slot_interval' => 30,
			
			// Email Settings
			'enable_email_notifications' => 'yes',
			'email_from_name' => get_bloginfo('name'),
			'email_from_address' => get_option('admin_email'),
			'admin_notification_email' => get_option('admin_email'),
			'email_logo' => '',
			
			// Email Templates
			'email_appointment_confirmation_subject' => __('Confirmare programare', 'medical-booking-system'),
			'email_appointment_confirmation_body' => self::get_default_email_template('confirmation'),
			'email_appointment_reminder_subject' => __('Reminder: Programare mâine', 'medical-booking-system'),
			'email_appointment_reminder_body' => self::get_default_email_template('reminder'),
			'email_appointment_cancellation_subject' => __('Programare anulată', 'medical-booking-system'),
			'email_appointment_cancellation_body' => self::get_default_email_template('cancellation'),
			
			// Display Settings
			'primary_color' => '#3b82f6',
			'show_doctor_photos' => 'yes',
			'calendar_view_type' => 'week',
			
			// Security Settings
			'enforce_2fa' => 'no',
			'recommend_2fa' => 'yes',
			'session_timeout_minutes' => 60,
			'failed_login_limit' => 5,
			'cnp_strict_validation' => 'yes',
		);
	}
	
	/**
	 * Get setting value
	 */
	public static function get($key, $default = null) {
		$settings = get_option('mbs_settings', self::get_defaults());
		
		if (isset($settings[$key])) {
			return $settings[$key];
		}
		
		if ($default !== null) {
			return $default;
		}
		
		$defaults = self::get_defaults();
		return isset($defaults[$key]) ? $defaults[$key] : '';
	}
	
	/**
	 * Get formatted date based on settings
	 */
	public static function format_date($date, $format = null) {
		if ($format === null) {
			$format = self::get('date_format');
		}
		
		$timestamp = is_numeric($date) ? $date : strtotime($date);
		
		switch ($format) {
			case 'wordpress':
				return date_i18n(get_option('date_format'), $timestamp);
			case 'dd/mm/yyyy':
				return date('d/m/Y', $timestamp);
			case 'mm/dd/yyyy':
				return date('m/d/Y', $timestamp);
			case 'yyyy-mm-dd':
				return date('Y-m-d', $timestamp);
			case 'dd-mm-yyyy':
				return date('d-m-Y', $timestamp);
			case 'dd.mm.yyyy':
				return date('d.m.Y', $timestamp);
			default:
				return date_i18n(get_option('date_format'), $timestamp);
		}
	}
	
	/**
	 * Get formatted time based on settings
	 */
	public static function format_time($time, $format = null) {
		if ($format === null) {
			$format = self::get('time_format');
		}
		
		$timestamp = is_numeric($time) ? $time : strtotime($time);
		
		switch ($format) {
			case 'wordpress':
				return date_i18n(get_option('time_format'), $timestamp);
			case '24h':
				return date('H:i', $timestamp);
			case '12h':
				return date('g:i A', $timestamp);
			default:
				return date_i18n(get_option('time_format'), $timestamp);
		}
	}
	
	/**
	 * Sanitize settings
	 */
	public function sanitize_settings($input) {
		$sanitized = array();
		$defaults = self::get_defaults();
		
		foreach ($defaults as $key => $default) {
			if (isset($input[$key])) {
				// Sanitize based on type
				if (in_array($key, array('business_name', 'email_from_name'))) {
					$sanitized[$key] = sanitize_text_field($input[$key]);
				} elseif (in_array($key, array('email', 'email_from_address', 'admin_notification_email'))) {
					$sanitized[$key] = sanitize_email($input[$key]);
				} elseif (in_array($key, array('phone', 'address'))) {
					$sanitized[$key] = sanitize_text_field($input[$key]);
				} elseif (strpos($key, '_body') !== false) {
					$sanitized[$key] = wp_kses_post($input[$key]);
				} elseif (strpos($key, '_subject') !== false) {
					$sanitized[$key] = sanitize_text_field($input[$key]);
				} elseif (in_array($key, array('advance_booking_days', 'max_appointments_per_day', 'min_time_between_appointments', 'cancellation_deadline_hours', 'session_timeout_minutes', 'failed_login_limit', 'time_slot_interval'))) {
					$sanitized[$key] = absint($input[$key]);
				} elseif ($key === 'primary_color') {
					$sanitized[$key] = sanitize_hex_color($input[$key]);
				} else {
					$sanitized[$key] = sanitize_text_field($input[$key]);
				}
			} else {
				$sanitized[$key] = $default;
			}
		}
		
		return $sanitized;
	}
	
	/**
	 * Get default email template
	 */
	private static function get_default_email_template($type) {
		$templates = array(
			'confirmation' => "Bună ziua {patient_name},\n\nProgramarea dumneavoastră a fost confirmată:\n\nData: {appointment_date}\nOra: {appointment_time}\nDoctor: {doctor_name}\nServiciu: {service_name}\n\nVă rugăm să ajungeți cu 10 minute înainte.\n\nMulțumim!",
			'reminder' => "Bună ziua {patient_name},\n\nVă reamintim de programarea dumneavoastră de mâine:\n\nData: {appointment_date}\nOra: {appointment_time}\nDoctor: {doctor_name}\n\nVă rugăm să confirmați prezența.\n\nMulțumim!",
			'cancellation' => "Bună ziua {patient_name},\n\nProgramarea dumneavoastră a fost anulată:\n\nData: {appointment_date}\nOra: {appointment_time}\nDoctor: {doctor_name}\n\nDacă doriți o nouă programare, vă rugăm să accesați sistemul de booking.\n\nMulțumim!"
		);
		
		return isset($templates[$type]) ? $templates[$type] : '';
	}
	
	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		$this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
		$settings = get_option($this->option_name, self::get_defaults());
		
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			
			<?php settings_errors(); ?>
			
			<h2 class="nav-tab-wrapper">
				<a href="?page=mbs-settings&tab=general" class="nav-tab <?php echo $this->current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
					<?php _e('General', 'medical-booking-system'); ?>
				</a>
				<a href="?page=mbs-settings&tab=booking" class="nav-tab <?php echo $this->current_tab === 'booking' ? 'nav-tab-active' : ''; ?>">
					<?php _e('Booking', 'medical-booking-system'); ?>
				</a>
				<a href="?page=mbs-settings&tab=email" class="nav-tab <?php echo $this->current_tab === 'email' ? 'nav-tab-active' : ''; ?>">
					<?php _e('Email', 'medical-booking-system'); ?>
				</a>
				<a href="?page=mbs-settings&tab=display" class="nav-tab <?php echo $this->current_tab === 'display' ? 'nav-tab-active' : ''; ?>">
					<?php _e('Display', 'medical-booking-system'); ?>
				</a>
				<a href="?page=mbs-settings&tab=security" class="nav-tab <?php echo $this->current_tab === 'security' ? 'nav-tab-active' : ''; ?>">
					<?php _e('Security', 'medical-booking-system'); ?>
				</a>
			</h2>
			
			<form method="post" action="options.php">
				<?php
				settings_fields('mbs_settings_group');
				
				switch ($this->current_tab) {
					case 'general':
						$this->render_general_tab($settings);
						break;
					case 'booking':
						$this->render_booking_tab($settings);
						break;
					case 'email':
						$this->render_email_tab($settings);
						break;
					case 'display':
						$this->render_display_tab($settings);
						break;
					case 'security':
						$this->render_security_tab($settings);
						break;
				}
				
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Render General Settings Tab
	 */
	private function render_general_tab($settings) {
		$wp_date_format = get_option('date_format');
		$wp_time_format = get_option('time_format');
		$example_date = current_time('timestamp');
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="business_name"><?php _e('Business Name', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="business_name" name="mbs_settings[business_name]" value="<?php echo esc_attr($settings['business_name']); ?>" class="regular-text" />
						<p class="description"><?php _e('Numele clinicii/cabinetului medical', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="timezone"><?php _e('Timezone', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<select id="timezone" name="mbs_settings[timezone]">
							<?php
							$timezones = timezone_identifiers_list();
							foreach ($timezones as $tz) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr($tz),
									selected($settings['timezone'], $tz, false),
									esc_html($tz)
								);
							}
							?>
						</select>
						<p class="description"><?php _e('Fusul orar folosit pentru programări', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Date Format', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mbs_settings[date_format]" value="wordpress" <?php checked($settings['date_format'], 'wordpress'); ?> />
								<?php printf(__('WordPress Default (%s)', 'medical-booking-system'), '<code>' . date_i18n($wp_date_format, $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[date_format]" value="dd/mm/yyyy" <?php checked($settings['date_format'], 'dd/mm/yyyy'); ?> />
								<?php printf(__('DD/MM/YYYY (%s)', 'medical-booking-system'), '<code>' . date('d/m/Y', $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[date_format]" value="dd-mm-yyyy" <?php checked($settings['date_format'], 'dd-mm-yyyy'); ?> />
								<?php printf(__('DD-MM-YYYY (%s)', 'medical-booking-system'), '<code>' . date('d-m-Y', $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[date_format]" value="dd.mm.yyyy" <?php checked($settings['date_format'], 'dd.mm.yyyy'); ?> />
								<?php printf(__('DD.MM.YYYY (%s)', 'medical-booking-system'), '<code>' . date('d.m.Y', $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[date_format]" value="mm/dd/yyyy" <?php checked($settings['date_format'], 'mm/dd/yyyy'); ?> />
								<?php printf(__('MM/DD/YYYY (%s)', 'medical-booking-system'), '<code>' . date('m/d/Y', $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[date_format]" value="yyyy-mm-dd" <?php checked($settings['date_format'], 'yyyy-mm-dd'); ?> />
								<?php printf(__('YYYY-MM-DD (%s)', 'medical-booking-system'), '<code>' . date('Y-m-d', $example_date) . '</code>'); ?>
							</label>
						</fieldset>
						<p class="description"><?php _e('Formatul în care se vor afișa datele', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Time Format', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mbs_settings[time_format]" value="wordpress" <?php checked($settings['time_format'], 'wordpress'); ?> />
								<?php printf(__('WordPress Default (%s)', 'medical-booking-system'), '<code>' . date_i18n($wp_time_format, $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[time_format]" value="24h" <?php checked($settings['time_format'], '24h'); ?> />
								<?php printf(__('24 Hour (%s)', 'medical-booking-system'), '<code>' . date('H:i', $example_date) . '</code>'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[time_format]" value="12h" <?php checked($settings['time_format'], '12h'); ?> />
								<?php printf(__('12 Hour (%s)', 'medical-booking-system'), '<code>' . date('g:i A', $example_date) . '</code>'); ?>
							</label>
						</fieldset>
						<p class="description"><?php _e('Formatul în care se va afișa ora', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="phone"><?php _e('Phone', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="phone" name="mbs_settings[phone]" value="<?php echo esc_attr($settings['phone']); ?>" class="regular-text" />
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="email"><?php _e('Email', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="email" id="email" name="mbs_settings[email]" value="<?php echo esc_attr($settings['email']); ?>" class="regular-text" />
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="address"><?php _e('Address', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<textarea id="address" name="mbs_settings[address]" rows="3" class="large-text"><?php echo esc_textarea($settings['address']); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	
	/**
	 * Render Booking Settings Tab
	 */
	private function render_booking_tab($settings) {
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="advance_booking_days"><?php _e('Advance Booking (Days)', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="number" id="advance_booking_days" name="mbs_settings[advance_booking_days]" value="<?php echo esc_attr($settings['advance_booking_days']); ?>" min="1" max="365" class="small-text" />
						<p class="description"><?php _e('Câte zile înainte se pot face programări', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="max_appointments_per_day"><?php _e('Max Appointments Per Day', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="number" id="max_appointments_per_day" name="mbs_settings[max_appointments_per_day]" value="<?php echo esc_attr($settings['max_appointments_per_day']); ?>" min="1" max="100" class="small-text" />
						<p class="description"><?php _e('Număr maxim de programări pe zi', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="time_slot_interval"><?php _e('Time Slot Interval (Minutes)', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<select id="time_slot_interval" name="mbs_settings[time_slot_interval]">
							<option value="15" <?php selected($settings['time_slot_interval'], 15); ?>>15 minute</option>
							<option value="30" <?php selected($settings['time_slot_interval'], 30); ?>>30 minute</option>
							<option value="60" <?php selected($settings['time_slot_interval'], 60); ?>>1 oră</option>
						</select>
						<p class="description"><?php _e('Intervalul de timp între sloturi', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Patient Cancellation', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mbs_settings[allow_patient_cancellation]" value="yes" <?php checked($settings['allow_patient_cancellation'], 'yes'); ?> />
								<?php _e('Allow', 'medical-booking-system'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[allow_patient_cancellation]" value="no" <?php checked($settings['allow_patient_cancellation'], 'no'); ?> />
								<?php _e('Do not allow', 'medical-booking-system'); ?>
							</label>
						</fieldset>
						<p class="description"><?php _e('Permit pacienților să anuleze programările', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="cancellation_deadline_hours"><?php _e('Cancellation Deadline (Hours)', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="number" id="cancellation_deadline_hours" name="mbs_settings[cancellation_deadline_hours]" value="<?php echo esc_attr($settings['cancellation_deadline_hours']); ?>" min="1" max="168" class="small-text" />
						<p class="description"><?php _e('Cu câte ore înainte se poate anula programarea', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Auto-Confirm Appointments', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mbs_settings[auto_confirm_appointments]" value="yes" <?php checked($settings['auto_confirm_appointments'], 'yes'); ?> />
								<?php _e('Yes', 'medical-booking-system'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[auto_confirm_appointments]" value="no" <?php checked($settings['auto_confirm_appointments'], 'no'); ?> />
								<?php _e('No (require manual confirmation)', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Show Service Price', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[show_service_price]" value="yes" <?php checked($settings['show_service_price'], 'yes'); ?> />
								<?php _e('Show prices in booking form', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Show Service Duration', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[show_service_duration]" value="yes" <?php checked($settings['show_service_duration'], 'yes'); ?> />
								<?php _e('Show duration in booking form', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Show Available Slots Count', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[show_available_slots_count]" value="yes" <?php checked($settings['show_available_slots_count'], 'yes'); ?> />
								<?php _e('Show number of available slots', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	
	/**
	 * Render Email Settings Tab
	 */
	private function render_email_tab($settings) {
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label><?php _e('Enable Email Notifications', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[enable_email_notifications]" value="yes" <?php checked($settings['enable_email_notifications'], 'yes'); ?> />
								<?php _e('Send email notifications', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="email_from_name"><?php _e('From Name', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="email_from_name" name="mbs_settings[email_from_name]" value="<?php echo esc_attr($settings['email_from_name']); ?>" class="regular-text" />
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="email_from_address"><?php _e('From Email', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="email" id="email_from_address" name="mbs_settings[email_from_address]" value="<?php echo esc_attr($settings['email_from_address']); ?>" class="regular-text" />
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="admin_notification_email"><?php _e('Admin Notification Email', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="email" id="admin_notification_email" name="mbs_settings[admin_notification_email]" value="<?php echo esc_attr($settings['admin_notification_email']); ?>" class="regular-text" />
						<p class="description"><?php _e('Email pentru notificări admin (programări noi, anulări)', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th colspan="2">
						<h3><?php _e('Email Templates', 'medical-booking-system'); ?></h3>
						<p class="description">
							<?php _e('Placeholders disponibile:', 'medical-booking-system'); ?>
							<code>{patient_name}</code>, <code>{appointment_date}</code>, <code>{appointment_time}</code>, 
							<code>{doctor_name}</code>, <code>{service_name}</code>, <code>{business_name}</code>
						</p>
					</th>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="email_appointment_confirmation_subject"><?php _e('Confirmation Email', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="email_appointment_confirmation_subject" name="mbs_settings[email_appointment_confirmation_subject]" value="<?php echo esc_attr($settings['email_appointment_confirmation_subject']); ?>" class="large-text" placeholder="<?php _e('Subject', 'medical-booking-system'); ?>" /><br><br>
						<textarea name="mbs_settings[email_appointment_confirmation_body]" rows="6" class="large-text code"><?php echo esc_textarea($settings['email_appointment_confirmation_body']); ?></textarea>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="email_appointment_reminder_subject"><?php _e('Reminder Email', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="email_appointment_reminder_subject" name="mbs_settings[email_appointment_reminder_subject]" value="<?php echo esc_attr($settings['email_appointment_reminder_subject']); ?>" class="large-text" placeholder="<?php _e('Subject', 'medical-booking-system'); ?>" /><br><br>
						<textarea name="mbs_settings[email_appointment_reminder_body]" rows="6" class="large-text code"><?php echo esc_textarea($settings['email_appointment_reminder_body']); ?></textarea>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="email_appointment_cancellation_subject"><?php _e('Cancellation Email', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="email_appointment_cancellation_subject" name="mbs_settings[email_appointment_cancellation_subject]" value="<?php echo esc_attr($settings['email_appointment_cancellation_subject']); ?>" class="large-text" placeholder="<?php _e('Subject', 'medical-booking-system'); ?>" /><br><br>
						<textarea name="mbs_settings[email_appointment_cancellation_body]" rows="6" class="large-text code"><?php echo esc_textarea($settings['email_appointment_cancellation_body']); ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	
	/**
	 * Render Display Settings Tab
	 */
	private function render_display_tab($settings) {
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="primary_color"><?php _e('Primary Color', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="text" id="primary_color" name="mbs_settings[primary_color]" value="<?php echo esc_attr($settings['primary_color']); ?>" class="mbs-color-picker" />
						<p class="description"><?php _e('Culoarea principală folosită în interfață', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Show Doctor Photos', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[show_doctor_photos]" value="yes" <?php checked($settings['show_doctor_photos'], 'yes'); ?> />
								<?php _e('Display doctor photos in booking form', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="calendar_view_type"><?php _e('Calendar View Type', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<select id="calendar_view_type" name="mbs_settings[calendar_view_type]">
							<option value="week" <?php selected($settings['calendar_view_type'], 'week'); ?>><?php _e('Week View', 'medical-booking-system'); ?></option>
							<option value="month" <?php selected($settings['calendar_view_type'], 'month'); ?>><?php _e('Month View', 'medical-booking-system'); ?></option>
							<option value="day" <?php selected($settings['calendar_view_type'], 'day'); ?>><?php _e('Day View', 'medical-booking-system'); ?></option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	
	/**
	 * Render Security Settings Tab
	 */
	private function render_security_tab($settings) {
		?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label><?php _e('Two-Factor Authentication', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="mbs_settings[enforce_2fa]" value="yes" <?php checked($settings['enforce_2fa'], 'yes'); ?> />
								<?php _e('Enforce for all users', 'medical-booking-system'); ?>
							</label><br>
							<label>
								<input type="radio" name="mbs_settings[enforce_2fa]" value="no" <?php checked($settings['enforce_2fa'], 'no'); ?> />
								<?php _e('Optional (user choice)', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('Recommend 2FA', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[recommend_2fa]" value="yes" <?php checked($settings['recommend_2fa'], 'yes'); ?> />
								<?php _e('Show banner recommending 2FA', 'medical-booking-system'); ?>
							</label>
						</fieldset>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="session_timeout_minutes"><?php _e('Session Timeout (Minutes)', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="number" id="session_timeout_minutes" name="mbs_settings[session_timeout_minutes]" value="<?php echo esc_attr($settings['session_timeout_minutes']); ?>" min="5" max="1440" class="small-text" />
						<p class="description"><?php _e('Timpul după care utilizatorul este deconectat automat', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label for="failed_login_limit"><?php _e('Failed Login Limit', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<input type="number" id="failed_login_limit" name="mbs_settings[failed_login_limit]" value="<?php echo esc_attr($settings['failed_login_limit']); ?>" min="3" max="10" class="small-text" />
						<p class="description"><?php _e('Număr de încercări greșite după care contul este blocat temporar', 'medical-booking-system'); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row">
						<label><?php _e('CNP Validation', 'medical-booking-system'); ?></label>
					</th>
					<td>
						<fieldset>
							<label>
								<input type="checkbox" name="mbs_settings[cnp_strict_validation]" value="yes" <?php checked($settings['cnp_strict_validation'], 'yes'); ?> />
								<?php _e('Use strict CNP validation (algorithm check)', 'medical-booking-system'); ?>
							</label>
						</fieldset>
						<p class="description"><?php _e('Validează CNP-ul folosind algoritmul de control', 'medical-booking-system'); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
