# Changelog

All notable changes to the Medical Booking System plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

### Planned for 1.2.0
- [ ] 2FA (TOTP with Google Authenticator)
- [ ] Complete admin UI for doctor schedule management
- [ ] Email notifications system
- [ ] Patient dashboard with appointment history
- [ ] Appointment cancellation feature
- [ ] Doctor availability calendar view

### Planned for 1.3.0
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
| 1.1.0 | 2025-10-20 | ✅ Released | Authentication system |
| 1.0.0 | 2025-10-15 | ✅ Released | Initial release |

---

## Upgrade Notes

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
- [Authentication Guide](docs/AUTHENTICATION.md)
- [Quick Start](docs/QUICK-START.md)
- [2FA Guide](docs/2FA-IMPLEMENTATION.md)

---

*Format based on [Keep a Changelog](https://keepachangelog.com/)*  
*Last updated: October 20, 2025*

