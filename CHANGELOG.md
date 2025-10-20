# Changelog

All notable changes to the Medical Booking System plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-10-20

### Added - Settings System
- **Complete Settings System** with 5 organized tabs:
  - **General Settings**: Business info, timezone, date/time formats
  - **Booking Settings**: Policies, limits, display options
  - **Email Settings**: Notification templates with placeholders
  - **Display Settings**: Theme colors, UI customization
  - **Security Settings**: 2FA configuration, session timeout, validation
  
- **Date Format Options**:
  - WordPress default (uses WP settings)
  - DD/MM/YYYY (`20/10/2025`)
  - DD-MM-YYYY (`20-10-2025`)
  - DD.MM.YYYY (`20.10.2025`)
  - MM/DD/YYYY (`10/20/2025`)
  - YYYY-MM-DD (`2025-10-20`)
  
- **Time Format Options**:
  - WordPress default (uses WP settings)
  - 24-hour format (`14:30`)
  - 12-hour format (`2:30 PM`)
  
- **Email Template System**:
  - Confirmation email template
  - Reminder email template (24h before)
  - Cancellation email template
  - Customizable subjects and bodies
  - Placeholder support: `{patient_name}`, `{appointment_date}`, `{appointment_time}`, `{doctor_name}`, `{service_name}`, `{business_name}`, `{cancellation_reason}`
  
- **Booking Policy Configuration**:
  - Advance booking days (1-365)
  - Max appointments per day (1-100)
  - Time slot interval (15/30/60 minutes)
  - Patient cancellation allowed (Yes/No)
  - Cancellation deadline (hours before appointment)
  - Auto-confirm appointments (Yes/No)
  - Show/hide service prices
  - Show/hide service durations
  - Show/hide available slots count
  
- **Security Configuration**:
  - 2FA enforcement (all users / optional)
  - 2FA recommendation banner
  - Session timeout (5-1440 minutes)
  - Failed login limit (3-10 attempts)
  - CNP strict validation toggle
  
- **Display Customization**:
  - Primary color picker (WordPress color picker)
  - Doctor photos display toggle
  - Calendar view type (Week/Month/Day)
  
- **Helper Functions**:
  - `MBS_Settings::get($key, $default)` - Get setting value
  - `MBS_Settings::format_date($date, $format)` - Format date based on settings
  - `MBS_Settings::format_time($time, $format)` - Format time based on settings
  - `MBS_Settings::get_defaults()` - Get all default settings
  
- **Admin UI Enhancements**:
  - WordPress color picker integration
  - Form validation (email, numbers, ranges)
  - Conditional field visibility
  - Email template placeholder helper
  - Character counter for textareas
  - Timezone search functionality
  - Auto-hide success messages

### Changed
- **Plugin Version**: 1.1.0 → 1.2.0
- **Admin Menu**: Settings page now functional with full configuration
- **Settings Storage**: Centralized in `mbs_settings` option
- **Date/Time Display**: Now uses configured formats throughout plugin

### Documentation
- Added `docs/SETTINGS.md` - Complete settings guide (40+ pages)
  - All settings tabs explained
  - PHP usage examples
  - JavaScript integration guide
  - Best practices
  - Troubleshooting
- Updated `README.md` - Added Settings link
- Updated `docs/README.md` - Added Settings section
- Updated `docs/TODO.md` - Marked Settings as completed

### Technical Details
- New file: `admin/class-settings.php` (788 lines)
- New file: `assets/js/admin-settings.js` (jQuery-based admin UI)
- Settings API integration with WordPress Settings API
- All settings sanitized and validated
- Settings exposed to frontend via `wp_localize_script`

---

## [1.1.0] - 2025-10-20

### Added - Authentication System
- **CNP Authentication**: Romanian Personal ID (CNP) as username
- **Multi-authentication**: Login with CNP, Email, or Phone number
- **Multi-phone support**: Users can add multiple phone numbers
  - Primary phone designation
  - Phone verification system (database ready)
  - Normalized phone format (07XXXXXXXX)
- **Authentication API**: Complete REST API for auth
  - `POST /auth/register` - User registration with CNP
  - `POST /auth/login` - Flexible login (CNP/Email/Phone)
  - `GET /auth/me` - Current user information
  - `GET /auth/phones` - List user phones
  - `POST /auth/phones` - Add new phone
  - `DELETE /auth/phones/{id}` - Remove phone
  - `PUT /auth/phones/{id}/primary` - Set primary phone
- **React Authentication Components**:
  - Login form with auto-detection (CNP/Email/Phone)
  - Register form with CNP validation
  - Romanian CNP validation algorithm
  - Phone number validation (Romanian format)
  - Remember me functionality
- **Security Features**:
  - Rate limiting (20 requests/minute per IP/user)
  - Nonce verification for all API calls
  - CNP masking in frontend (*********1234)
  - Password hashing (WordPress native)
  - SQL injection protection (prepared statements)
- **User Profile**:
  - CNP field in WordPress user profile
  - Admin-only CNP editing after creation
  - Phone management UI (backend ready)

### Changed
- **Database Schema**: Updated to v1.1.0
  - New table: `wp_mbs_user_phones`
  - New user_meta: `mbs_cnp`
  - Indexes added for performance
- **Plugin Version**: 1.0.0 → 1.1.0
- **Plugin Description**: Added CNP authentication mention

### Database Changes
```sql
-- New table
CREATE TABLE wp_mbs_user_phones (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) unsigned NOT NULL,
    phone varchar(20) NOT NULL,
    is_primary tinyint(1) DEFAULT 0,
    is_verified tinyint(1) DEFAULT 0,
    verification_code varchar(10),
    verification_expires datetime,
    PRIMARY KEY (id),
    UNIQUE KEY user_phone (user_id, phone),
    KEY phone (phone)
);

-- New user_meta
mbs_cnp - Romanian CNP (13 digits)
```

### Documentation
- Added `docs/AUTHENTICATION.md` - Complete authentication guide (10+ pages)
- Added `docs/QUICK-START.md` - 5-minute quick start guide
- Added `docs/2FA-IMPLEMENTATION.md` - 2FA implementation guide (20+ pages)
- Added `docs/PROJECT-PLAN.md` - Detailed project architecture
- Added `docs/TODO.md` - Development roadmap
- Added `README.md` - Main plugin documentation
- Added `CHANGELOG.md` - This file

---

## [1.0.0] - 2025-10-15

### Added - Initial Release
- **Database Structure**:
  - `wp_mbs_services` - Medical services management
  - `wp_mbs_doctors` - Doctor profiles
  - `wp_mbs_doctor_services` - Doctor-service associations
  - `wp_mbs_doctor_schedules` - Working hours
  - `wp_mbs_doctor_breaks` - Breaks and holidays
  - `wp_mbs_patients` - Patient records
  - `wp_mbs_appointments` - Appointments
  - `wp_mbs_appointment_history` - Audit trail
  - `wp_mbs_notifications` - Email/SMS queue
  - `wp_mbs_settings` - Plugin settings
  
- **User Roles**:
  - `mbs_patient` - Can book appointments, view own appointments
  - `mbs_receptionist` - Can manage all appointments and patients
  - `mbs_assistant` - Can view appointments and manage patients
  - `mbs_doctor` - Can view own appointments and manage schedule
  - `mbs_manager` - Full access with analytics
  
- **Basic REST API**:
  - `GET /mbs/v1/services` - List active services
  - `GET /mbs/v1/doctors` - List active doctors
  - `GET /mbs/v1/slots` - Available time slots
  - `POST /mbs/v1/appointments` - Create appointment
  - `GET /mbs/v1/appointments` - List appointments
  
- **React Booking UI**:
  - Service selection
  - Doctor selection by service
  - Calendar date picker
  - Time slot selection (with filtering)
  - Patient information form
  - Booking confirmation
  - Integrated with Tailwind CSS
  
- **Admin Interface**:
  - Dashboard widget
  - Appointments list
  - Doctors management (basic)
  - Services management (basic)
  - Settings page
  
- **Shortcodes**:
  - `[mbs_booking]` - Booking form
  - `[mbs_patient_dashboard]` - Patient dashboard
  
- **Internationalization**:
  - Romanian (ro_RO) translations
  - Translation-ready .pot file
  - Text domain: `medical-booking-system`
  
- **Default Data**:
  - 5 default medical services
  - Basic plugin settings
  - Sample booking hours

### Technical Details
- WordPress 5.0+ compatibility
- PHP 7.4+ requirement
- MySQL 5.6+ / MariaDB 10.0+
- React 18 for frontend
- Babel Standalone for JSX transformation
- Tailwind CSS for styling

---

## [Unreleased] - Future Versions

### Planned for 1.3.0
- [ ] 2FA (TOTP with Google Authenticator)
- [ ] Email notifications implementation (templates ready)
- [ ] Complete admin UI for doctor schedule management
- [ ] Patient dashboard with appointment history
- [ ] Appointment cancellation feature
- [ ] Doctor availability calendar view

### Planned for 1.4.0
- [ ] SMS notifications (Twilio integration)
- [ ] Payment integration (Stripe/PayPal)
- [ ] Recurring appointments
- [ ] Waiting list management
- [ ] Advanced reporting and analytics
- [ ] Export to PDF/Excel

### Planned for 2.0.0
- [ ] Multi-location support
- [ ] Video consultations (Zoom/Google Meet)
- [ ] Lab results integration
- [ ] Prescription management
- [ ] Medical history tracking
- [ ] Mobile app API
- [ ] Advanced calendar features (drag & drop)
- [ ] Queue management system

---

## Version History

| Version | Date | Status | Notes |
|---------|------|--------|-------|
| 1.2.0 | 2025-10-20 | ✅ Released | Settings system |
| 1.1.0 | 2025-10-20 | ✅ Released | Authentication system |
| 1.0.0 | 2025-10-15 | ✅ Released | Initial release |

---

## Upgrade Notes

### Upgrading to 1.2.0 from 1.1.0

1. **New Settings System**: Automatic
   - Settings page now fully functional
   - Default settings applied automatically
   - Visit Medical Booking → Settings to customize
   
2. **Date/Time Formats**: 
   - Defaults to WordPress settings
   - Customize in Settings → General tab
   - Existing dates will use new formats
   
3. **Email Templates**: 
   - Ready to use (not yet sending)
   - Configure in Settings → Email tab
   - Placeholders available for customization
   
4. **Breaking Changes**: None
   - Fully backward compatible with 1.1.0
   - All existing functionality preserved
   
5. **New Features**:
   - Complete settings configuration
   - Date/time format customization
   - Email template preparation
   - Booking policy management

### Upgrading to 1.1.0 from 1.0.0

1. **Database Migration**: Automatic
   - New table `wp_mbs_user_phones` will be created
   - Existing data is preserved
   
2. **New Shortcode**: 
   - `[mbs_auth]` available for login/register
   
3. **Breaking Changes**: None
   - Fully backward compatible
   
4. **New Features**:
   - Users can now register/login with CNP
   - Existing users can add CNP in profile
   - Multi-phone support available

---

## Support & Issues

For bug reports and feature requests, contact your development team.

## Links

- [Documentation](docs/)
- [Settings Guide](docs/SETTINGS.md)
- [Authentication Guide](docs/AUTHENTICATION.md)
- [Quick Start](docs/QUICK-START.md)
- [2FA Guide](docs/2FA-IMPLEMENTATION.md)

---

*Format based on [Keep a Changelog](https://keepachangelog.com/)*  
*Last updated: October 20, 2025*

