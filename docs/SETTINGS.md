# Settings System - Medical Booking System

## 📖 Overview

The Settings System provides a comprehensive admin interface for configuring all aspects of the Medical Booking System. Available at **Medical Booking → Settings** in WordPress admin.

## 🎨 Settings Tabs

### 1. General Settings

Business and timezone configuration.

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Business Name | Text | Site name | Clinic/Practice name |
| Timezone | Dropdown | WordPress timezone | Timezone for appointments |
| Date Format | Radio | WordPress default | Date display format |
| Time Format | Radio | WordPress default | Time display format |
| Phone | Text | - | Business phone number |
| Email | Email | Admin email | Business email address |
| Address | Textarea | - | Business physical address |

#### Date Format Options

- **WordPress Default**: Uses WordPress Settings → General → Date Format
- **DD/MM/YYYY**: `20/10/2025`
- **DD-MM-YYYY**: `20-10-2025`
- **DD.MM.YYYY**: `20.10.2025`
- **MM/DD/YYYY**: `10/20/2025`
- **YYYY-MM-DD**: `2025-10-20`

#### Time Format Options

- **WordPress Default**: Uses WordPress Settings → General → Time Format
- **24 Hour**: `14:30`
- **12 Hour**: `2:30 PM`

---

### 2. Booking Settings

Controls booking behavior and patient interactions.

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Advance Booking (Days) | Number | 30 | How many days in advance patients can book |
| Max Appointments Per Day | Number | 20 | Maximum appointments per day |
| Time Slot Interval | Dropdown | 30 | Minutes between time slots (15/30/60) |
| Patient Cancellation | Radio | Yes | Allow patients to cancel appointments |
| Cancellation Deadline (Hours) | Number | 24 | Hours before appointment when cancellation is allowed |
| Auto-Confirm Appointments | Radio | Yes | Automatically confirm or require manual approval |
| Show Service Price | Checkbox | Yes | Display prices in booking form |
| Show Service Duration | Checkbox | Yes | Display duration in booking form |
| Show Available Slots Count | Checkbox | Yes | Display number of available slots |

---

### 3. Email Settings

Email notification configuration and templates.

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Enable Email Notifications | Checkbox | Yes | Send email notifications |
| From Name | Text | Site name | Email sender name |
| From Email | Email | Admin email | Email sender address |
| Admin Notification Email | Email | Admin email | Email for admin notifications |

#### Email Templates

Three customizable email templates:

1. **Confirmation Email**: Sent when appointment is confirmed
2. **Reminder Email**: Sent 24 hours before appointment
3. **Cancellation Email**: Sent when appointment is cancelled

#### Available Placeholders

Use these placeholders in email templates:

- `{patient_name}` - Patient's full name
- `{appointment_date}` - Appointment date (formatted according to settings)
- `{appointment_time}` - Appointment time (formatted according to settings)
- `{doctor_name}` - Doctor's full name
- `{service_name}` - Service name
- `{business_name}` - Business name from settings
- `{cancellation_reason}` - Cancellation reason (only for cancellation email)

#### Default Templates

**Confirmation:**
```
Bună ziua {patient_name},

Programarea dumneavoastră a fost confirmată:

Data: {appointment_date}
Ora: {appointment_time}
Doctor: {doctor_name}
Serviciu: {service_name}

Vă rugăm să ajungeți cu 10 minute înainte.

Mulțumim!
```

**Reminder:**
```
Bună ziua {patient_name},

Vă reamintim de programarea dumneavoastră de mâine:

Data: {appointment_date}
Ora: {appointment_time}
Doctor: {doctor_name}

Vă rugăm să confirmați prezența.

Mulțumim!
```

**Cancellation:**
```
Bună ziua {patient_name},

Programarea dumneavoastră a fost anulată:

Data: {appointment_date}
Ora: {appointment_time}
Doctor: {doctor_name}

Dacă doriți o nouă programare, vă rugăm să accesați sistemul de booking.

Mulțumim!
```

---

### 4. Display Settings

Visual appearance customization.

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Primary Color | Color Picker | #3b82f6 | Main theme color for booking interface |
| Show Doctor Photos | Checkbox | Yes | Display doctor photos in booking form |
| Calendar View Type | Dropdown | Week | Default calendar view (Week/Month/Day) |

---

### 5. Security Settings

Security and authentication configuration.

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Two-Factor Authentication | Radio | Optional | Enforce 2FA for all users or make it optional |
| Recommend 2FA | Checkbox | Yes | Show banner recommending 2FA to users |
| Session Timeout (Minutes) | Number | 60 | Auto-logout after inactivity |
| Failed Login Limit | Number | 5 | Failed attempts before temporary lockout |
| CNP Validation | Checkbox | Yes | Use strict CNP validation algorithm |

---

## 💻 PHP Usage

### Getting Settings

```php
// Get a single setting
$business_name = MBS_Settings::get('business_name');
$date_format = MBS_Settings::get('date_format');

// Get with custom default
$custom_value = MBS_Settings::get('some_key', 'default_value');

// Get all settings
$all_settings = get_option('mbs_settings', MBS_Settings::get_defaults());
```

### Formatting Dates and Times

```php
// Format date using plugin settings
$formatted_date = MBS_Settings::format_date('2025-10-20');
// Output: Depends on settings (e.g., "20/10/2025" or "October 20, 2025")

// Format with specific format (override settings)
$formatted_date = MBS_Settings::format_date('2025-10-20', 'dd.mm.yyyy');
// Output: "20.10.2025"

// Format time using plugin settings
$formatted_time = MBS_Settings::format_time('14:30:00');
// Output: Depends on settings (e.g., "14:30" or "2:30 PM")

// Format with specific format
$formatted_time = MBS_Settings::format_time('14:30:00', '12h');
// Output: "2:30 PM"

// Combined example for appointment display
$date = MBS_Settings::format_date($appointment->appointment_date);
$time = MBS_Settings::format_time($appointment->appointment_time);
echo "Your appointment: $date at $time";
```

### Checking Booking Policies

```php
// Check if patients can cancel
$can_cancel = MBS_Settings::get('allow_patient_cancellation') === 'yes';

// Get cancellation deadline
$deadline_hours = (int) MBS_Settings::get('cancellation_deadline_hours');

// Check if appointment can be cancelled
$appointment_time = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time);
$now = current_time('timestamp');
$hours_until = ($appointment_time - $now) / 3600;

if ($can_cancel && $hours_until >= $deadline_hours) {
    // Show cancel button
}

// Check advance booking limit
$advance_days = (int) MBS_Settings::get('advance_booking_days');
$max_date = strtotime("+{$advance_days} days");
```

### Email Settings

```php
// Check if emails are enabled
if (MBS_Settings::get('enable_email_notifications') === 'yes') {
    // Send email
    $from_name = MBS_Settings::get('email_from_name');
    $from_email = MBS_Settings::get('email_from_address');
    
    $headers = [
        "From: {$from_name} <{$from_email}>",
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    // Get template
    $subject = MBS_Settings::get('email_appointment_confirmation_subject');
    $body = MBS_Settings::get('email_appointment_confirmation_body');
    
    // Replace placeholders
    $body = str_replace(
        ['{patient_name}', '{appointment_date}', '{appointment_time}', '{doctor_name}', '{service_name}', '{business_name}'],
        [$patient_name, $date, $time, $doctor_name, $service_name, $business_name],
        $body
    );
    
    wp_mail($patient_email, $subject, $body, $headers);
}
```

---

## 🎯 JavaScript Integration

Settings are automatically available in the frontend via `wp_localize_script`:

```javascript
// In booking component
const dateFormat = mbs_ajax.settings.date_format;
const showPrices = mbs_ajax.settings.show_service_price === 'yes';
const primaryColor = mbs_ajax.settings.primary_color;

// Apply primary color
document.documentElement.style.setProperty('--mbs-primary-color', primaryColor);

// Conditional rendering
{showPrices && (
    <div className="price">{service.price} lei</div>
)}
```

---

## 🔧 Admin UI Features

### Form Validation

- Email validation for all email fields
- Number validation with min/max enforcement
- Character counter for textareas
- Real-time validation feedback

### User Experience

- **Timezone Search**: Search box for finding timezones quickly
- **Color Picker**: WordPress color picker integration
- **Conditional Fields**: Fields show/hide based on related settings
- **Email Template Helper**: Placeholder reference displayed next to templates
- **Auto-save Success**: Success messages auto-hide after 3 seconds

### Security

- All settings are sanitized before saving:
  - Text fields: `sanitize_text_field()`
  - Emails: `sanitize_email()`
  - HTML content: `wp_kses_post()`
  - Numbers: `absint()`
  - Colors: `sanitize_hex_color()`

---

## 🚀 Adding Custom Settings

To add your own settings to the system:

### 1. Add to Defaults

```php
// In MBS_Settings::get_defaults()
'my_custom_setting' => 'default_value',
```

### 2. Add to Render Method

```php
// In render_general_tab() or create new tab
<tr>
    <th scope="row">
        <label for="my_custom_setting"><?php _e('My Setting', 'medical-booking-system'); ?></label>
    </th>
    <td>
        <input type="text" id="my_custom_setting" name="mbs_settings[my_custom_setting]" value="<?php echo esc_attr($settings['my_custom_setting']); ?>" class="regular-text" />
        <p class="description"><?php _e('Description of my setting', 'medical-booking-system'); ?></p>
    </td>
</tr>
```

### 3. Add Sanitization (if needed)

```php
// In sanitize_settings() method
elseif ($key === 'my_custom_setting') {
    $sanitized[$key] = sanitize_custom_function($input[$key]);
}
```

### 4. Use in Code

```php
$value = MBS_Settings::get('my_custom_setting');
```

---

## 📝 Best Practices

1. **Always Use Helper Functions**: Use `MBS_Settings::format_date()` and `format_time()` instead of hardcoding formats
2. **Check Before Using**: Verify settings like email notifications are enabled before sending
3. **Provide Defaults**: Always provide sensible defaults when calling `get()`
4. **Sanitize Output**: Use `esc_html()`, `esc_attr()` when displaying settings
5. **Cache Settings**: Settings are stored in WordPress options and auto-cached
6. **Expose to Frontend**: Use `wp_localize_script` to make settings available in JavaScript
7. **Translate Strings**: All UI strings use `__()` for translation support

---

## 🔄 Migration from Hardcoded Values

If you have hardcoded values in your plugin, migrate them to settings:

**Before:**
```php
$date = date('d/m/Y', strtotime($appointment_date));
$max_days = 30;
```

**After:**
```php
$date = MBS_Settings::format_date($appointment_date);
$max_days = (int) MBS_Settings::get('advance_booking_days');
```

---

## 📊 Settings in Database

All settings are stored in a single option:

- **Option name**: `mbs_settings`
- **Format**: Serialized array
- **Autoload**: Yes (for performance)

To view all settings in database:
```sql
SELECT * FROM wp_options WHERE option_name = 'mbs_settings';
```

---

## 🎓 Examples

### Example 1: Display Appointment with User Settings

```php
function display_appointment($appointment) {
    $date = MBS_Settings::format_date($appointment->appointment_date);
    $time = MBS_Settings::format_time($appointment->appointment_time);
    $business_name = MBS_Settings::get('business_name');
    
    echo "<div class='appointment'>";
    echo "<h3>{$business_name}</h3>";
    echo "<p>Date: {$date}</p>";
    echo "<p>Time: {$time}</p>";
    echo "</div>";
}
```

### Example 2: Check Cancellation Policy

```php
function can_cancel_appointment($appointment) {
    if (MBS_Settings::get('allow_patient_cancellation') !== 'yes') {
        return false;
    }
    
    $deadline = (int) MBS_Settings::get('cancellation_deadline_hours');
    $appointment_timestamp = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time);
    $hours_until = ($appointment_timestamp - current_time('timestamp')) / 3600;
    
    return $hours_until >= $deadline;
}
```

### Example 3: Send Email Using Template

```php
function send_confirmation_email($appointment, $patient) {
    if (MBS_Settings::get('enable_email_notifications') !== 'yes') {
        return;
    }
    
    $subject = MBS_Settings::get('email_appointment_confirmation_subject');
    $body = MBS_Settings::get('email_appointment_confirmation_body');
    
    $replacements = [
        '{patient_name}' => $patient->name,
        '{appointment_date}' => MBS_Settings::format_date($appointment->appointment_date),
        '{appointment_time}' => MBS_Settings::format_time($appointment->appointment_time),
        '{doctor_name}' => $appointment->doctor_name,
        '{service_name}' => $appointment->service_name,
        '{business_name}' => MBS_Settings::get('business_name'),
    ];
    
    $body = str_replace(array_keys($replacements), array_values($replacements), $body);
    
    $from_name = MBS_Settings::get('email_from_name');
    $from_email = MBS_Settings::get('email_from_address');
    
    $headers = [
        "From: {$from_name} <{$from_email}>",
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    wp_mail($patient->email, $subject, nl2br($body), $headers);
}
```

---

## 🆘 Troubleshooting

### Settings Not Saving

1. Check user has `manage_options` capability
2. Verify nonce is valid
3. Check for PHP errors in debug.log
4. Ensure settings sanitization isn't rejecting values

### Settings Not Displaying

1. Clear WordPress object cache
2. Check option exists: `get_option('mbs_settings')`
3. Verify `MBS_Settings::get_instance()` is called in plugin init

### Date/Time Format Not Working

1. Verify setting is saved correctly
2. Check `format_date()` and `format_time()` are used instead of direct PHP `date()`
3. Ensure timezone is set correctly in General settings

---

## 📚 Related Documentation

- [Main README](../README.md)
- [Authentication System](AUTHENTICATION.md)
- [Database Schema](DATABASE.md)
- [API Documentation](API.md)

---

*Version: 1.2.0*  
*Last Updated: October 20, 2025*

