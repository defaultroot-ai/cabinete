# 📋 TODO: Implementare Sistem de Familii - Medical Booking System

## **🎯 Obiectiv:**
Adaptarea sistemului de familii din pluginul `patient-form` pentru Medical Booking System.

## **📁 Sursa:**
- **Plugin**: `patient-form` (din `C:\xampp8.2.12\htdocs\split`)
- **Clasa principală**: `Patient_Form_Family_Manager`
- **Fișier**: `includes/class-family-manager.php`

## **🗄️ Structura Bazei de Date de Implementat:**

### **1. Tabela principală: `wp_mbs_family_members`**
```sql
CREATE TABLE wp_mbs_family_members (
    id int(11) NOT NULL AUTO_INCREMENT,
    main_patient_id int(11) NOT NULL,        -- ID-ul pacientului principal
    nume varchar(100) NOT NULL,              -- Numele membrului
    prenume varchar(100) NOT NULL,            -- Prenumele membrului
    cnp varchar(13) NOT NULL,                -- CNP-ul membrului
    relatia varchar(50) NOT NULL,             -- Relația (soț, copil, etc.)
    telefon varchar(20) DEFAULT NULL,        -- Telefon
    email varchar(100) DEFAULT NULL,          -- Email
    doctor_selected varchar(50) NOT NULL,     -- Medicul ales
    status varchar(20) DEFAULT 'active',      -- Status (active/deleted)
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY main_patient_id (main_patient_id),
    KEY cnp (cnp),
    KEY doctor_selected (doctor_selected),
    KEY status (status)
);
```

### **2. Tabela pentru cereri noi: `wp_mbs_new_requests`**
```sql
CREATE TABLE wp_mbs_new_requests (
    id int(11) NOT NULL AUTO_INCREMENT,
    main_patient_id int(11) NOT NULL,
    main_patient_cnp varchar(13) DEFAULT NULL,
    main_patient_name varchar(200) DEFAULT NULL,
    cnp varchar(13) NOT NULL,
    nume varchar(100) NOT NULL,
    prenume varchar(100) NOT NULL,
    relatia varchar(50) NOT NULL,
    telefon varchar(20) DEFAULT NULL,
    email varchar(100) DEFAULT NULL,
    doctor_selected varchar(50) NOT NULL,
    status varchar(20) DEFAULT 'new',         -- new/processed
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    processed_at datetime DEFAULT NULL,
    PRIMARY KEY (id)
);
```

## **🔧 Funcționalități de Implementat:**

### **✅ 1. Gestionare Membri de Familie:**
- [ ] Adăugare membri la familie existentă
- [ ] Validare CNP cu algoritm românesc
- [ ] Verificare duplicat CNP în sistem
- [ ] Verificare relații (nu poți fi membru în mai multe familii)
- [ ] Ștergere membri (soft delete cu status = 'deleted')

### **✅ 2. Validări Avansate:**
- [ ] CNP validation cu `MBS_Auth_Validator`
- [ ] Email format validation
- [ ] Phone normalization
- [ ] Duplicate prevention (CNP în altă familie)
- [ ] Main patient conversion (pacient principal → membru familie)

### **✅ 3. Raportare și Statistici:**
- [ ] Family stats (numărul de familii, membri)
- [ ] Distribution by doctor (pacienți pe medici)
- [ ] Paginated lists cu search și filtrare
- [ ] Export functionality pentru telefoane

### **✅ 4. Integrare cu Sistemul Principal:**
- [ ] Audit logging cu `MBS_Audit_Logger`
- [ ] User meta integration pentru date complete
- [ ] Doctor selection pentru fiecare membru
- [ ] Status management (active/deleted)

## **🎯 Conceptul de "Pacient Principal":**

### **Structura:**
- **Pacient Principal** = capul familiei (în `wp_mbs_patients`)
- **Membri Familie** = dependenții (în `wp_mbs_family_members`)
- **Relația** = tipul de legătură (soț, copil, părinte, etc.)

### **Exemplu:**
```
Familia Popescu:
├── Ion Popescu (pacient principal, CNP: 1800404080170)
├── Maria Popescu (soția, CNP: 2780621080059)
├── Ana Popescu (fiica, CNP: 5001234567890)
└── Mihai Popescu (fiul, CNP: 5001234567891)
```

## **🔒 Securitate și Validări:**

### **1. Validări CNP:**
- [ ] Format validation (13 cifre)
- [ ] Algorithm validation cu validator românesc
- [ ] Duplicate check în sistem
- [ ] Cross-family prevention (nu poți fi în mai multe familii)

### **2. Business Logic:**
- [ ] Main patient conversion (dacă adaugi un pacient principal ca membru, îl convertești)
- [ ] Soft delete (nu șterge fizic, doar marchează ca deleted)
- [ ] Status management (active/deleted/new/processed)

### **3. Audit Trail:**
- [ ] Action logging cu `MBS_Audit_Logger`
- [ ] Change tracking pentru toate operațiunile
- [ ] Security events logging

## **📊 Funcții de Implementat:**

### **1. CRUD Operations:**
```php
// Adăugare membru familie
$family_manager->save_family_members($main_patient_id, $family_members, $selected_doctor);

// Obținere membri familie
$family_manager->get_family_members($main_patient_id);

// Ștergere membru
$family_manager->delete_single_family_member($member_id);

// Statistici
$family_manager->get_family_stats();
```

### **2. Validări:**
```php
// Validare membru familie
$validation = $family_manager->validate_family_member($member_data);

// Verificare CNP duplicat
$exists = $family_manager->cnp_exists_in_other_family($cnp, $exclude_main_patient_id);

// Verificare pacient principal
$is_main = $family_manager->is_cnp_main_patient_in_other_family($cnp, $exclude_main_patient_id);
```

### **3. Raportare:**
```php
// Toți pacienții cu paginare
$patients = $family_manager->get_all_patients_paginated($page, $per_page, $search_name, $search_cnp, $filter_doctor, $total);

// Pacienți filtrați
$patients = $family_manager->get_main_patients_filtered($has_doctor, $doctor_id, $page, $per_page, $search, $orderby, $order, $total);
```

## **🚀 Plan de Implementare:**

### **Faza 1: Structura Bazei de Date**
- [ ] Creează tabelele `wp_mbs_family_members` și `wp_mbs_new_requests`
- [ ] Adaptează numele tabelelor din `patient_auth` în `mbs_patients`
- [ ] Testează structura bazei de date

### **Faza 2: Clasa Family Manager**
- [ ] Creează `class-family-manager.php` în `includes/`
- [ ] Adaptează metodele din `Patient_Form_Family_Manager`
- [ ] Integrează cu `MBS_Auth` și `MBS_Patient`
- [ ] Testează funcționalitățile de bază

### **Faza 3: Admin Interface**
- [ ] Adaugă tab "Familii" în admin panel
- [ ] Creează formularul de adăugare membri familie
- [ ] Implementează lista de familii cu paginare
- [ ] Adaugă funcționalități de editare/ștergere

### **Faza 4: Integrare cu Sistemul Actual**
- [ ] Integrează cu sistemul de pacienți existent
- [ ] Adaugă selector de familie în formularul de adăugare pacient
- [ ] Implementează vizualizarea familiilor în lista de pacienți
- [ ] Adaugă rapoarte și statistici pentru familii

### **Faza 5: Îmbunătățiri și Testare**
- [ ] Testează toate funcționalitățile
- [ ] Optimizează performanța
- [ ] Adaugă funcționalități avansate (dashboard, templates)
- [ ] Documentează utilizarea

## **📝 Note Importante:**

**Sistemul existent este foarte complet și bine implementat!** 

**Avantaje:**
- ✅ Logica de business este solidă
- ✅ Validările sunt comprehensive
- ✅ Securitatea este bine gândită
- ✅ Integrarea cu audit logging
- ✅ Soft delete și status management

**Ce putem face:**
1. **Adapta** structura pentru Medical Booking System
2. **Integra** cu sistemul actual de pacienți
3. **Îmbunătăți** UI/UX cu visualizări moderne
4. **Adăuga** funcționalități avansate (dashboard, templates)

**Recomandarea**: Să folosim această implementare ca bază și să o adaptăm pentru nevoile noastre! 🎯

## **🔗 Fișiere de Referință:**
- `wp-content/plugins/patient-form/includes/class-family-manager.php`
- `wp-content/plugins/medical-booking-system/docs/FAMILY-SYSTEM-ANALYSIS.md`

## **📅 Status:**
- **Creat**: 2025-10-22
- **Status**: Planificare completă
- **Prioritate**: Medie
- **Estimare**: 2-3 săptămâni pentru implementare completă
