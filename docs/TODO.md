# Medical Booking System - TODO List

## ✅ Completat

- [x] Database tables created (doctors, services, appointments, schedules, patients)
- [x] REST API endpoints created (GET services, doctors, slots + POST appointments)
- [x] React booking UI integrated in WordPress (full-width, direct render)
- [x] Admin menu and basic pages created (Dashboard, Appointments, Doctors, Services, Settings)
- [x] User roles created (pacient, receptionist, asistent, medic, manager)
- [x] **Settings System Complete (v1.2.0)** 
  - 5 tabs: General, Booking, Email, Display, Security
  - Date format: WordPress default + dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy, mm/dd/yyyy, yyyy-mm-dd
  - Time format: WordPress default + 24h, 12h
  - Booking policies: advance booking, cancellation, auto-confirm
  - Email templates: confirmation, reminder, cancellation (with placeholders)
  - Display settings: color picker, doctor photos, calendar view
  - Security settings: 2FA enforcement, session timeout, CNP validation
  - Helper functions: MBS_Settings::get(), format_date(), format_time()

---

## 🔥 URGENT - Autentificare & Date Test

### ✅ Pas 0: Sistem Autentificare (2-3 ore) **COMPLET**
- [x] **Database schema pentru autentificare**
  - Username = CNP (13 caractere, unic)
  - Adaugă field `cnp` în wp_users (via user_meta)
  - Tabel wp_mbs_user_phones (user_id, phone, is_primary, verified)
  - Index pe CNP și phone pentru căutare rapidă
- [x] **WordPress login customization**
  - Hook în `authenticate` filter
  - Accept login cu: CNP sau Email sau Telefon
  - Verificare CNP valid (13 cifre, algoritm control)
  - Match telefon din wp_mbs_user_phones
- [x] **REST API pentru autentificare**
  - POST /auth/register (CNP, email, password, phone, nume)
  - POST /auth/login (identifier + password) - identifier = CNP/email/phone
  - GET /auth/me (current user info)
  - GET /auth/phones (list user phones)
  - POST /auth/phones (add new phone)
  - DELETE /auth/phones/{id} (delete phone)
  - PUT /auth/phones/{id}/primary (set primary phone)
- [x] **Frontend React - Login/Register**
  - Form register: CNP (username), Email, Password, Telefon, Nume complet
  - Validare CNP românesc (13 cifre + algoritm)
  - Form login: un singur field "CNP, Email sau Telefon" + Password
  - Detectare automată tip identificator (CNP/email/phone)
  - Remember me checkbox
  - Shortcode [mbs_auth] pentru afișare în WordPress
- [x] **User profile management**
  - Add/edit/delete multiple phones (API ready)
  - Mark phone as primary (API ready)
  - Update CNP (doar admin poate modifica - în user profile)
- [x] **Security pentru CNP**
  - CNP masking în frontend (display doar ultimele 4 cifre)
  - Rate limiting (20 requests/min)
  - Nonce verification pentru toate API calls
  - GDPR compliance pentru date personale
- [x] **Documentație**
  - README-AUTH.md complet cu toate detaliile

### Pas 1: Date de Test (5-10 min)
- [x] Adaugă 2-3 medici via Admin → Medical Booking → Doctors
  - Exemplu: Dr. Maria Popescu (Medicină Generală)
  - Exemplu: Dr. Ion Ionescu (Cardiolog)
  - Exemplu: Dr. Ana Gheorghe (Medicină Generală)
- [x] Configurează program medici (orar personalizat setat)
- [x] Asociază medici cu servicii (wp_mbs_doctor_services)

### Pas 2: Fix Patient Handling (10 min)
- [x] Fix `patient_id: 0` în POST /appointments (mapare automată user→patient)
- [x] Auto-create patient din user curent (dacă este logat)
- [ ] Permite guest booking cu date manual introduse (nu se folosește în MVP actual)
- [x] Update frontend: eliminat trimitere `patient_id`; backend face maparea

### Pas 3: Test Complet (5 min)
- [ ] Test flux: serviciu → medic → dată → oră → confirmare
- [ ] Verifică programarea salvată în wp_mbs_appointments
- [ ] Test conflict detection (același doctor, aceeași oră)
- [x] Test sloturi disponibile (exclude weekend, past dates) – generare sloturi corectă; ajustat `slot_interval` la 15 min pentru serviciu 29

---

## 🎯 PRIORITAR - După Test

### 2FA - Autentificare în 2 Pași (6-8 ore) **OPȚIONAL PENTRU PACIENT**
- [ ] **Backend - TOTP Implementation**
  - Instalare library PHPGangsta/GoogleAuthenticator (gratuit)
  - Generare SECRET KEY pentru fiecare user
  - API endpoint: POST /auth/2fa/enable (generează QR code)
  - API endpoint: POST /auth/2fa/confirm (verifică primul cod)
  - API endpoint: POST /auth/2fa/verify (la login)
  - API endpoint: POST /auth/2fa/disable (dezactivare)
  - API endpoint: POST /auth/2fa/backup-codes/regenerate
  - Salvare în wp_usermeta: mbs_2fa_enabled, mbs_2fa_secret, mbs_2fa_backup_codes
- [ ] **Frontend - User Settings Page**
  - Pagină "Securitate" în profil pacient
  - Toggle ON/OFF pentru 2FA (implicit OFF)
  - Modal cu QR Code pentru scanat cu Google Authenticator
  - Display manual entry key (pentru input manual)
  - Input 6 cifre pentru confirmare activare
  - Lista backup codes (10 coduri x 8 cifre)
  - Buton "Regenerează backup codes"
  - Warning: "Salvează backup codes în loc sigur"
- [ ] **Login Flow cu 2FA**
  - Modificare login: verifică dacă user are 2FA activat
  - Dacă DA: Modal "Introdu codul din aplicație"
  - OTP Input component (6 cifre)
  - Link "Folosește backup code" (dacă pierde telefonul)
  - Opțiune "Amintește acest dispozitiv 30 zile" (skip 2FA)
- [ ] **Backup Codes System**
  - Generare 10 coduri unice la activare 2FA
  - Display o singură dată (user trebuie să salveze)
  - Marcare "used" după utilizare
  - Un backup code poate fi folosit o singură dată
  - Regenerare coduri noi (invalidează cele vechi)
- [ ] **User Experience**
  - Instrucțiuni clare: "Descarcă Google Authenticator"
  - Link-uri către Google Auth / Microsoft Auth / Authy
  - Preview cum arată codul în app (ex: 123456)
  - Success message după activare
  - Email notification când 2FA este activat/dezactivat
- [ ] **Admin Settings (opțional)**
  - Opțiune: "Forțează 2FA pentru toți utilizatorii"
  - Opțiune: "Recomandă 2FA la login" (banner)
  - Statistici: câți users au 2FA activat
- [ ] **Recovery Flow**
  - Dacă user pierde telefonul: folosește backup code
  - Dacă pierde și backup codes: contact admin
  - Admin poate dezactiva 2FA pentru un user specific
- [ ] **Security Features**
  - Rate limiting: max 5 încercări greșite
  - Lockout temporar după 5 fail (5 minute)
  - Log toate încercările de login cu 2FA
  - Email alert la 5 încercări failed

### Admin UI - Management (1-2 ore)
- [ ] Doctor Schedules: Add/edit program lucru
  - UI pentru configurare zile și ore
  - Salvare în wp_mbs_doctor_schedules
- [ ] Doctor Breaks: Concedii, pauze
  - Calendar view pentru breaks
  - All-day și partial breaks
- [ ] Service Management: Add/edit/delete servicii
  - CRUD complet pentru servicii
  - Set duration și price
- [ ] Appointment Management: Edit/cancel programări
  - Buton edit în lista appointments
  - Change status (confirmed, completed, cancelled, no_show)
  - Audit trail în wp_mbs_appointment_history

### Notificări Email (1 oră)
- [ ] Email confirmation după programare
  - Template HTML pentru email
  - Include appointment details, doctor, service
  - Link pentru cancel (dacă permis)
- [ ] Reminder email 24h înainte
  - Cron job pentru verificare
  - Trimite doar pentru status=confirmed
- [ ] Cancellation notice
  - Notify patient și doctor
  - Include motiv anulare

### Patient Dashboard (1 oră)
- [ ] Frontend: Programările mele
  - List view cu appointments
  - Filter by status (upcoming, past, cancelled)
- [ ] Cancel appointment feature
  - Buton cancel (cu verificare cancellation_hours)
  - Confirmation dialog
- [ ] Shortcode [my_appointments]

---

## 📊 IMPORTANT - Features Avansate

### Roluri și Permisiuni (2 ore)
- [ ] Receptionist workflow
  - Programează pentru orice pacient
  - Search pacient by phone/CNP
  - Quick add new patient
- [ ] Doctor view
  - Calendar propriu cu appointments
  - Manage own schedule (breaks, holidays)
  - Mark appointments as completed
- [ ] Manager reports
  - Statistics dashboard
  - Appointments by doctor/service
  - Revenue reports (dacă price tracking)
  - Export to PDF/Excel

### Patient Management (1 oră)
- [ ] Patient CRUD în Admin
- [ ] Patient history (toate programările)
- [ ] Medical notes (restricted to doctors)
- [ ] Family members support
- [ ] Emergency contact info

### Calendar Advanced (2 ore)
- [ ] FullCalendar integration în Admin
- [ ] Drag & drop appointments
- [ ] Multi-doctor view
- [ ] Color coding by service/status
- [ ] Week/Month/Day views

---

## 🔒 Security & Performance

### Security (1 oră)
- [ ] Security audit complet
  - Verify all nonce checks
  - SQL injection protection (prepared statements)
  - XSS prevention (esc_html, esc_attr)
- [ ] Rate limiting per user/IP
- [ ] GDPR compliance
  - Privacy policy integration
  - Data export feature
  - Data deletion on request

### Performance (1 oră)
- [ ] Caching pentru services/doctors
  - Transient API cache (15 min)
  - Clear cache on update
- [ ] Database query optimization
  - Add missing indexes
  - Optimize appointment queries
- [ ] Asset minification
  - Minify CSS/JS
  - Use production React build
- [ ] Lazy loading pentru calendar

---

## 🎨 UX & Polish

### UI/UX (2 ore)
- [x] Mobile responsive (prima versiune):
  - Stepper ascuns pe mobil; butoane Înapoi/Înainte sticky
  - Carduri full-width; paddings reduse pe mobil; touch-friendly
  - Seed nume pacient la primul render pentru a elimina flicker
  - Test pe toate breakpoints
  - Touch-friendly controls
- [ ] Accessibility (a11y)
  - ARIA labels
  - Keyboard navigation
  - Screen reader support
- [ ] Loading states
  - Skeleton loaders
  - Progress indicators
- [ ] Error messages
  - User-friendly error text
  - Validation feedback

### Traduceri (1 oră)
- [ ] Complete Romanian translations
  - All admin strings
  - All frontend strings
  - Email templates
- [ ] Generate .pot file
- [ ] Create .mo compiled files

---

## 📚 Documentație & Testing

### Documentation (2 ore)
- [ ] User documentation
  - How to book appointment
  - How to cancel
  - FAQ section
- [ ] Admin documentation
  - Setup guide
  - Add doctors/services
  - Manage appointments
  - Role management
- [ ] Developer documentation
  - API endpoints
  - Hooks and filters
  - Database schema
  - Customization guide

### Testing (2 ore)
- [ ] Unit tests (PHPUnit)
  - Database class tests
  - API endpoint tests
- [ ] Integration tests
  - Complete booking flow
  - Role permissions
- [ ] Manual testing checklist
  - All user scenarios
  - Edge cases
  - Cross-browser testing

---

## 🚀 Future Enhancements (Backlog)

### Advanced Features
- [ ] Online payments integration (Stripe, PayPal)
- [ ] SMS notifications (Twilio)
- [ ] Video consultations (Zoom API)
- [ ] Lab results integration
- [ ] Prescription management
- [ ] Multi-location support
- [ ] Queue management system
- [ ] Waiting list automation
- [ ] Recurring appointments
- [ ] Group appointments
- [ ] Custom fields for services
- [ ] Invoice generation
- [ ] Rating & review system
- [ ] Patient portal (login area)
- [ ] Medical history upload

### Integrations
- [ ] Google Calendar sync
- [ ] Outlook Calendar sync
- [ ] WooCommerce integration
- [ ] Contact Form 7 integration
- [ ] Elementor widgets
- [ ] REST API for mobile apps

---

## ✅ Checklist de control MVP — Riscuri & Criterii de acceptanță

- [ ] PII masking pentru CNP și date sensibile în UI și loguri
  - Responsabil: Dev Frontend, Dev Backend
  - Criterii de acceptanță: toate aparițiile CNP sunt mascate (ultimele 4 cifre); logurile aplicației și serverului nu conțin CNP complet sau telefoane ne-redactate; review de securitate trecut.

- [ ] Nonce și rate limiting aplicate pe toate endpoint-urile REST
  - Responsabil: Dev Backend
  - Criterii de acceptanță: fiecare endpoint critic validează nonce; throttling per IP/user configurat; teste manuale arată 429 după depășirea limitei.

- [ ] Mapare corectă user→patient și `patient_id` setat în programări
  - Responsabil: Dev Backend
  - Criterii de acceptanță: creare programare logged-in asociază `patient_id` existent sau auto-creat; flux guest creează pacient temporar și normalizează datele; zero rânduri cu `patient_id=0` după test.

- [ ] Prevenire double-booking (lock/verificare la commit)
  - Responsabil: Dev Backend
  - Criterii de acceptanță: două cereri concurente pentru același doctor/interval nu pot crea programări duplicate; return 409 Conflict la a doua cerere; index compus existent pe (doctor_id, start_time).

- [ ] Normalizare timp și timezone (UTC în DB, conversie în UI)
  - Responsabil: Dev Backend, Dev Frontend
  - Criterii de acceptanță: orele afișate coincid cu cele stocate pentru trei timezone-uri de test; caz DST acoperit în teste manuale.

- [ ] Email confirmation funcțional + cron healthcheck
  - Responsabil: Dev Backend
  - Criterii de acceptanță: e-mail de confirmare se trimite pentru status=confirmed; cron rulează și loghează execuția; link de anulare corect și validat.

- [ ] Indexuri DB pentru interogări critice de programări
  - Responsabil: Dev Backend
  - Criterii de acceptanță: EXPLAIN arată utilizarea indexurilor pe (doctor_id, start_time); timpi < 100ms pe set de date de test mărit.

- [ ] Cache și invalidare pentru doctors/services
  - Responsabil: Dev Backend
  - Criterii de acceptanță: răspunsurile sunt cache-uit pe 15 min; la CRUD pe doctors/services cache-ul se invalidează; nu se servesc date vechi după update.

- [ ] Permisiuni/roluri verificate pe Admin și API
  - Responsabil: Dev Backend, QA
  - Criterii de acceptanță: utilizatorii fără capabilități nu pot accesa endpoint-urile admin; test negativ pentru escaladare privilegii trece.

- [ ] Mesaje de eroare prietenoase și traduse (fără stack traces)
  - Responsabil: Dev Frontend
  - Criterii de acceptanță: erorile afișate conțin text prietenos în română; coduri interne în log numai; acoperire i18n.

- [ ] Teste pentru `POST /appointments` (happy path + conflict + guest)
  - Responsabil: Dev Backend
  - Criterii de acceptanță: cel puțin 3 teste trec: creare reușită, conflict 409, creare ca guest cu date minime.

- [ ] Versionare migrații DB cu posibilitate minimă de rollback
  - Responsabil: Dev Backend
  - Criterii de acceptanță: versiuni incremental numerotate; script de rollback pentru ultimul pas validat pe mediu de test.

- [ ] Audit trail pentru schimbări de status la programări
  - Responsabil: Dev Backend
  - Criterii de acceptanță: tabel `wp_mbs_appointment_history` înregistrează cine/când/ce status vechi/ nou; vizibil în Admin.

- [ ] A11y minim: focus, tastatură, ARIA pe fluxul de rezervare
  - Responsabil: Dev Frontend
  - Criterii de acceptanță: navigare completă cu tastatura; focus vizibil; roluri/aria-labels setate pe componentele interactive.

## 📝 Notes

**Current Status:**
- Plugin structure: ✅ Complete
- Database: ✅ Tables created
- API: ✅ Basic endpoints working
- Frontend: ✅ React UI integrated
- Admin: ✅ Basic management pages

**Next Immediate Steps:**
1. Test complet cap‑coadă + verificare salvare în `wp_mbs_appointments`
2. Conflict detection și mesaje UX la suprapuneri
3. Email confirmation (minim)
4. Patient Dashboard (listă programări)

**Estimated Time to MVP:** 2-3 hours
**Estimated Time to Production Ready:** 10-15 hours

---

*Last Updated: October 30, 2025*

