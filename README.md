# Medical Booking System

![Version](https://img.shields.io/badge/version-1.2.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)

Complete WordPress plugin for medical appointment booking with CNP authentication, multi-phone support, and comprehensive patient management.

## ✨ Features

### 🔐 Authentication & Security
- ✅ **CNP-based authentication** (Romanian Personal ID)
- ✅ **Multi-authentication methods**: Login with CNP, Email, or Phone
- ✅ **Multi-phone support**: Users can add multiple phone numbers
- ✅ **2FA Ready**: TOTP (Google Authenticator) implementation ready
- ✅ **Rate limiting**: Protection against brute force attacks
- ✅ **CNP masking**: Privacy-compliant display (*********1234)
- ✅ **GDPR compliant**: Secure data handling

### 📅 Booking System
- ✅ **Appointment booking**: Intuitive React-based booking flow
- ✅ **Doctor management**: Complete doctor profiles with specialties
- ✅ **Service management**: Configurable medical services with duration and pricing
- ✅ **Schedule management**: Doctor working hours and breaks
- ✅ **Calendar integration**: Visual appointment calendar
- ✅ **Conflict detection**: Automatic double-booking prevention

### 👥 Role-Based Access
- ✅ **Patient**: Book appointments, view own appointments
- ✅ **Receptionist**: Manage all appointments, create patients
- ✅ **Medical Assistant**: View appointments, manage patients
- ✅ **Doctor**: View own appointments, manage schedule
- ✅ **Manager**: Full access with analytics and reports

### 🌍 Internationalization
- ✅ **Romanian translations**: Complete ro_RO translation
- ✅ **Translation ready**: .pot file for additional languages
- ✅ **RTL support ready**: Structure prepared for RTL languages

### 🔌 REST API
- ✅ **Authentication endpoints**: Register, login, 2FA
- ✅ **Booking endpoints**: Services, doctors, slots, appointments
- ✅ **Phone management**: Add, remove, set primary phone
- ✅ **User management**: Profile, settings, preferences

## 🚀 Quick Start

### Installation

1. **Upload Plugin**
   ```bash
   # Upload to your WordPress installation
   /wp-content/plugins/medical-booking-system/
   ```

2. **Activate Plugin**
   - Go to **WordPress Admin → Plugins**
   - Find "Medical Booking System"
   - Click **Activate**

3. **Initial Setup**
   - Database tables are created automatically
   - User roles are configured automatically
   - Default services are added

4. **Configure Settings**
   - Go to **Medical Booking → Settings**
   - Configure basic settings
   - Add doctors and services

### Basic Usage

**For Booking Form:**
```php
[mbs_booking]
```

**For Login/Register:**
```php
[mbs_auth]
```

**For Patient Dashboard:**
```php
[mbs_patient_dashboard]
```

## 📚 Documentation

Comprehensive documentation is available in the [`docs/`](docs/) folder:

| Document | Description |
|----------|-------------|
| [TODO.md](docs/TODO.md) | Development roadmap and task tracking |
| [AUTHENTICATION.md](docs/AUTHENTICATION.md) | Complete authentication system guide |
| [SETTINGS.md](docs/SETTINGS.md) | Settings system and configuration guide |
| [QUICK-START.md](docs/QUICK-START.md) | 5-minute quick start guide |
| [2FA-IMPLEMENTATION.md](docs/2FA-IMPLEMENTATION.md) | Two-factor authentication guide |
| [PROJECT-PLAN.md](docs/PROJECT-PLAN.md) | Detailed project architecture |

### Quick Links

- 🔐 [Authentication Setup](docs/AUTHENTICATION.md)
- ⚙️ [Settings Guide](docs/SETTINGS.md)
- 🚀 [Quick Start Guide](docs/QUICK-START.md)
- 📋 [Development TODO](docs/TODO.md)
- 🔒 [2FA Implementation](docs/2FA-IMPLEMENTATION.md)

## 🛠️ Technical Stack

- **Backend**: PHP 7.4+, WordPress 5.0+
- **Frontend**: React 18, Tailwind CSS
- **Database**: MySQL 5.6+ / MariaDB 10.0+
- **Libraries**: 
  - PHPGangsta/GoogleAuthenticator (2FA)
  - Babel Standalone (JSX transformation)

## 📦 System Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher / MariaDB 10.0+
- **PHP Extensions**: mysqli, json, mbstring
- **Apache Modules**: mod_rewrite (for permalinks)

## 🗄️ Database Schema

The plugin creates 10 custom tables:

| Table | Purpose |
|-------|---------|
| `wp_mbs_services` | Medical services |
| `wp_mbs_doctors` | Doctor profiles |
| `wp_mbs_doctor_services` | Doctor-service associations |
| `wp_mbs_doctor_schedules` | Working hours |
| `wp_mbs_doctor_breaks` | Breaks and holidays |
| `wp_mbs_patients` | Patient records |
| `wp_mbs_appointments` | Appointments |
| `wp_mbs_appointment_history` | Audit trail |
| `wp_mbs_notifications` | Email/SMS queue |
| `wp_mbs_settings` | Plugin settings |
| `wp_mbs_user_phones` | Multi-phone support |

Plus user metadata for CNP and 2FA.

## 🔌 REST API Endpoints

### Authentication
```
POST   /wp-json/mbs/v1/auth/register
POST   /wp-json/mbs/v1/auth/login
GET    /wp-json/mbs/v1/auth/me
GET    /wp-json/mbs/v1/auth/phones
POST   /wp-json/mbs/v1/auth/phones
DELETE /wp-json/mbs/v1/auth/phones/{id}
PUT    /wp-json/mbs/v1/auth/phones/{id}/primary
```

### Booking
```
GET    /wp-json/mbs/v1/services
GET    /wp-json/mbs/v1/doctors?serviceId={id}
GET    /wp-json/mbs/v1/slots?doctorId={id}&date={date}&duration={min}
POST   /wp-json/mbs/v1/appointments
GET    /wp-json/mbs/v1/appointments
```

See [AUTHENTICATION.md](docs/AUTHENTICATION.md) for detailed API documentation.

## 🎨 Shortcodes

| Shortcode | Description | Example |
|-----------|-------------|---------|
| `[mbs_booking]` | Appointment booking form | `[mbs_booking]` |
| `[mbs_auth]` | Login/Register form | `[mbs_auth]` |
| `[mbs_patient_dashboard]` | Patient's appointments | `[mbs_patient_dashboard]` |

## 🔒 Security Features

- ✅ **Nonce verification**: All AJAX/REST requests protected
- ✅ **SQL injection protection**: Prepared statements only
- ✅ **XSS prevention**: All output escaped
- ✅ **CSRF protection**: WordPress nonce system
- ✅ **Rate limiting**: 20 requests/minute per user/IP
- ✅ **CNP validation**: Romanian CNP algorithm verification
- ✅ **Password hashing**: WordPress native bcrypt
- ✅ **Capability checks**: Role-based access control

## 📊 Current Status

**Version**: 1.1.0  
**Status**: ✅ **PRODUCTION READY** for authentication system  
**Next Features**: Booking flow finalization, admin UI, email notifications

### What's Working
- ✅ Complete authentication system
- ✅ Multi-authentication (CNP/Email/Phone)
- ✅ Database structure
- ✅ REST API foundation
- ✅ React booking UI (basic)
- ✅ Admin menu structure
- ✅ User roles and capabilities

### In Development
- 🔨 Doctor schedule management
- 🔨 Admin UI for doctors/services
- 🔨 Email notifications
- 🔨 Patient dashboard
- 🔨 2FA implementation
- 🔨 Reporting and analytics

See [docs/TODO.md](docs/TODO.md) for detailed roadmap.

## 🤝 Contributing

This is a private/commercial plugin. For issues and feature requests, contact the development team.

## 📄 License

This plugin is licensed under the GNU General Public License v2 or later.

```
Copyright (C) 2025 Medical Booking System

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 👨‍💻 Development Team

For support and customization requests, contact your development team.

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

**Made with ❤️ for Romanian medical practices**

*Last updated: October 20, 2025*

