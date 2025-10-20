# Medical Booking System - Documentation

Complete documentation for the Medical Booking System WordPress plugin.

## 📚 Documentation Index

### Getting Started

| Document | Description | Time to Read |
|----------|-------------|--------------|
| [QUICK-START.md](QUICK-START.md) | 5-minute quick start guide for testing | 5 min |
| [../README.md](../README.md) | Main plugin overview and features | 10 min |
| [../CHANGELOG.md](../CHANGELOG.md) | Version history and changes | 5 min |

### Authentication System

| Document | Description | Time to Read |
|----------|-------------|--------------|
| [AUTHENTICATION.md](AUTHENTICATION.md) | Complete authentication system guide | 20 min |
| [2FA-IMPLEMENTATION.md](2FA-IMPLEMENTATION.md) | Two-factor authentication implementation | 30 min |

### Development

| Document | Description | Time to Read |
|----------|-------------|--------------|
| [TODO.md](TODO.md) | Development roadmap and task tracking | 15 min |
| [PROJECT-PLAN.md](PROJECT-PLAN.md) | Detailed project architecture and planning | 30 min |

---

## 🔍 Quick Navigation

### By Topic

#### 🔐 Authentication & Security
- [Authentication System Overview](AUTHENTICATION.md#overview)
- [CNP Validation](AUTHENTICATION.md#cnp-validation)
- [Multi-phone Support](AUTHENTICATION.md#phone-management)
- [2FA Implementation](2FA-IMPLEMENTATION.md)
- [Security Features](AUTHENTICATION.md#security)

#### 📅 Booking System
- [Booking Flow](PROJECT-PLAN.md#booking-flow)
- [Doctor Management](PROJECT-PLAN.md#doctor-management)
- [Schedule Management](PROJECT-PLAN.md#schedule-management)

#### 🔌 API Reference
- [Authentication Endpoints](AUTHENTICATION.md#api-endpoints)
- [Booking Endpoints](PROJECT-PLAN.md#api-endpoints)
- [Error Handling](AUTHENTICATION.md#error-handling)

#### 👥 User Roles
- [Role Definitions](PROJECT-PLAN.md#user-roles)
- [Capabilities](PROJECT-PLAN.md#capabilities)
- [Workflow Examples](TODO.md#role-based-features)

#### 🗄️ Database
- [Schema Overview](PROJECT-PLAN.md#database-schema)
- [Tables Description](AUTHENTICATION.md#database-schema)
- [Migrations](../CHANGELOG.md#database-changes)

---

## 🚀 Common Tasks

### For Administrators

**Initial Setup:**
1. [Quick Start Guide](QUICK-START.md) - Get started in 5 minutes
2. [Configure Authentication](AUTHENTICATION.md#wordpress-admin-setup)
3. [Add Doctors and Services](PROJECT-PLAN.md#admin-ui)

**User Management:**
- [Understanding User Roles](PROJECT-PLAN.md#user-roles)
- [Managing CNP Authentication](AUTHENTICATION.md#user-profile)

### For Developers

**Getting Started:**
1. [Project Architecture](PROJECT-PLAN.md)
2. [Database Schema](PROJECT-PLAN.md#database-schema)
3. [API Documentation](AUTHENTICATION.md#api-endpoints)

**Implementation:**
- [2FA Implementation Guide](2FA-IMPLEMENTATION.md)
- [Custom Hooks](PROJECT-PLAN.md#hooks-and-filters)
- [REST API Extension](AUTHENTICATION.md#api-usage)

**Contributing:**
- [Development Roadmap](TODO.md)
- [Current Status](TODO.md#status)

### For End Users

**Getting Started:**
1. [Register Account](QUICK-START.md#test-registration)
2. [Login Options](QUICK-START.md#test-login)
3. [Book Appointment](PROJECT-PLAN.md#booking-flow)

**Account Security:**
- [Enable 2FA](2FA-IMPLEMENTATION.md#user-journey)
- [Manage Phones](AUTHENTICATION.md#phone-management)
- [Backup Codes](2FA-IMPLEMENTATION.md#backup-codes)

---

## 📖 Document Summaries

### QUICK-START.md
**What:** 5-minute testing guide  
**For:** Quick evaluation and testing  
**Contains:**
- Installation steps
- Test scenarios (register, login, booking)
- CNP test values
- Troubleshooting

### AUTHENTICATION.md
**What:** Complete authentication documentation  
**For:** Understanding and implementing auth system  
**Contains:**
- CNP authentication overview
- Multi-phone support
- API endpoints reference
- Security features
- Code examples
- Testing procedures

### 2FA-IMPLEMENTATION.md
**What:** Two-factor authentication guide  
**For:** Implementing TOTP 2FA  
**Contains:**
- TOTP overview (vs SMS)
- Complete implementation plan
- Backend PHP code
- Frontend React components
- User journey mockups
- Security considerations

### TODO.md
**What:** Development roadmap  
**For:** Tracking progress and planning  
**Contains:**
- Completed features ✅
- Urgent tasks 🔥
- Priority features
- Future enhancements
- Time estimates

### PROJECT-PLAN.md
**What:** Comprehensive project architecture  
**For:** Understanding complete system design  
**Contains:**
- Requirements and scope
- Database design
- User roles and workflows
- UI/UX specifications
- Security architecture
- Performance considerations
- Integration plans

---

## 🔗 External Resources

### WordPress Development
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

### React Development
- [React Documentation](https://react.dev/)
- [React Hooks](https://react.dev/reference/react)

### Security & Authentication
- [TOTP RFC 6238](https://tools.ietf.org/html/rfc6238)
- [Google Authenticator](https://github.com/google/google-authenticator)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

### Romanian Standards
- [CNP Structure](https://ro.wikipedia.org/wiki/Cod_numeric_personal)
- [GDPR Romania](https://www.dataprotection.ro/)

---

## 📝 Document Status

| Document | Version | Last Updated | Status |
|----------|---------|--------------|--------|
| README.md (main) | 1.1.0 | 2025-10-20 | ✅ Complete |
| CHANGELOG.md | 1.1.0 | 2025-10-20 | ✅ Complete |
| QUICK-START.md | 1.0 | 2025-10-20 | ✅ Complete |
| AUTHENTICATION.md | 1.0 | 2025-10-20 | ✅ Complete |
| 2FA-IMPLEMENTATION.md | 1.0 | 2025-10-20 | ✅ Complete |
| TODO.md | Current | 2025-10-20 | 🔄 Active |
| PROJECT-PLAN.md | 1.0 | 2025-10-15 | ✅ Complete |

---

## 💡 Tips for Reading

1. **Start with** [QUICK-START.md](QUICK-START.md) if you want to test immediately
2. **Read** [AUTHENTICATION.md](AUTHENTICATION.md) for complete auth understanding
3. **Check** [TODO.md](TODO.md) for current development status
4. **Review** [2FA-IMPLEMENTATION.md](2FA-IMPLEMENTATION.md) when ready to implement 2FA
5. **Refer to** [PROJECT-PLAN.md](PROJECT-PLAN.md) for architecture details

---

## 🆘 Need Help?

**For specific topics:**
- Authentication issues → [AUTHENTICATION.md](AUTHENTICATION.md#troubleshooting)
- Quick testing → [QUICK-START.md](QUICK-START.md)
- Feature requests → [TODO.md](TODO.md)
- Architecture questions → [PROJECT-PLAN.md](PROJECT-PLAN.md)

**For general support:**
Contact your development team.

---

*Last updated: October 20, 2025*  
*Documentation version: 1.1.0*

