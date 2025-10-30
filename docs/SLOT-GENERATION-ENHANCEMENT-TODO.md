# 🕒 TODO - Îmbunătățirea Sistemului de Generare Sloturi de Timp

## 🎯 Obiectiv General
Îmbunătățirea sistemului actual de generare sloturi pentru a permite configurarea automată bazată pe durata serviciului și zilele disponibile, cu posibilitatea de a ascunde anumite sloturi specifice.

---

## 📊 Analiza Sistemului Actual

### ✅ Ce Funcționează
- [x] Serviciile au durata predefinită în tabela `mbs_services` (câmpul `duration`)
- [x] Programele doctorilor sunt în `mbs_doctor_schedules` (ziua săptămânii, start_time, end_time)
- [x] Pauzele sunt în `mbs_doctor_breaks` (data specifică sau pauze zilnice)
- [x] Funcția `get_slots()` din `class-appointment.php` generează sloturile bazate pe durata serviciului
- [x] Frontend React are simularea sloturilor în `booking-component.js`

### ❌ Limitări Actuale
- [ ] Nu există configurare flexibilă pentru intervalul între sloturi
- [ ] Nu există sistem de ascundere sloturi specifice
- [ ] Nu există buffer time configurabil între programări
- [ ] Nu există sloturi dedicate pentru staff only
- [ ] Nu există interfață admin pentru gestionarea sloturilor
- [ ] Frontend folosește date mock în loc de API real

---

## 🚀 Faza 1: Crearea Tabelelor Noi pentru Configurarea Sloturilor (1-2 ore)

### 1.1 Tabel pentru Configurarea Sloturilor per Doctor-Serviciu
- [x] **Crearea tabelului `wp_mbs_doctor_slot_settings`**
  - [x] Câmpuri: `id`, `doctor_id`, `service_id`, `slot_interval`, `buffer_time`, `max_advance_days`, `min_advance_hours`, `is_active`
  - [x] Indexuri pentru performanță
  - [x] Relații cu tabelele existente

```sql
CREATE TABLE wp_mbs_doctor_slot_settings (
    id int(11) NOT NULL AUTO_INCREMENT,
    doctor_id int(11) NOT NULL,
    service_id int(11) NOT NULL,
    slot_interval int(11) DEFAULT 30 COMMENT 'Intervalul între sloturi în minute',
    buffer_time int(11) DEFAULT 0 COMMENT 'Timp buffer între programări în minute',
    max_advance_days int(11) DEFAULT 30 COMMENT 'Câte zile în avans se pot face programări',
    min_advance_hours int(11) DEFAULT 2 COMMENT 'Minim câte ore în avans',
    is_active tinyint(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY doctor_service (doctor_id, service_id),
    KEY doctor_id (doctor_id),
    KEY service_id (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.2 Tabel pentru Ascunderea Sloturilor și Sloturi Staff Only
- [x] **Crearea tabelului `wp_mbs_hidden_slots`**
  - [x] Câmpuri: `id`, `doctor_id`, `slot_time`, `day_of_week`, `specific_date`, `reason`, `is_recurring`, `slot_type`, `created_at`
  - [x] Suport pentru ascundere recurentă și specifică
  - [x] Suport pentru sloturi staff only

```sql
CREATE TABLE wp_mbs_hidden_slots (
    id int(11) NOT NULL AUTO_INCREMENT,
    doctor_id int(11) NOT NULL,
    slot_time time NOT NULL COMMENT 'Ora slotului (ex: 12:00)',
    day_of_week tinyint(1) NULL COMMENT 'Ziua săptămânii (0=Duminică, 1=Luni, etc.)',
    specific_date date NULL COMMENT 'Data specifică pentru ascundere',
    reason varchar(255) DEFAULT 'Slot ascuns' COMMENT 'Motivul ascunderii',
    is_recurring tinyint(1) DEFAULT 0 COMMENT 'Dacă se repetă săptămânal',
    slot_type enum('hidden', 'staff_only') DEFAULT 'hidden' COMMENT 'Tipul slotului',
    staff_notes text NULL COMMENT 'Note pentru staff (doar pentru staff_only)',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY doctor_id (doctor_id),
    KEY day_of_week (day_of_week),
    KEY specific_date (specific_date),
    KEY slot_type (slot_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.3 Actualizarea Clasei Database
- [x] **Modificare în `class-database.php`**
  - [x] Adăugarea metodelor pentru crearea tabelelor noi
  - [x] Actualizarea metodei `create_tables()`
  - [x] Adăugarea datelor default pentru configurații

---

## 🔧 Faza 2: Îmbunătățirea Algoritmului de Generare în Backend (2-3 ore)

### 2.1 Extinderea Clasei Appointment
- [x] **Modificare în `class-appointment.php`**
  - [x] Adăugarea metodei `get_enhanced_slots()`
  - [x] Implementarea logicii de configurare sloturi
  - [x] Adăugarea verificării sloturilor ascunse
  - [x] Implementarea buffer time între programări

```php
public function get_enhanced_slots($doctor_id, $date, $service_id) {
    // 1. Obține durata serviciului
    $service_duration = $this->get_service_duration($service_id);
    
    // 2. Obține configurarea sloturilor pentru doctor-serviciu
    $slot_settings = $this->get_slot_settings($doctor_id, $service_id);
    
    // 3. Obține programul doctorului pentru ziua respectivă
    $schedule = $this->get_doctor_schedule($doctor_id, $date);
    
    // 4. Obține sloturile ascunse
    $hidden_slots = $this->get_hidden_slots($doctor_id, $date);
    
    // 5. Obține programările existente
    $existing_appointments = $this->get_existing_appointments($doctor_id, $date);
    
    // 6. Generează sloturile disponibile
    $available_slots = $this->generate_available_slots(
        $schedule, 
        $service_duration, 
        $slot_settings,
        $hidden_slots, 
        $existing_appointments
    );
    
    return $available_slots;
}
```

### 2.2 Implementarea Logicii de Configurare
- [x] **Metode noi în `class-appointment.php`**
  - [x] `get_slot_settings($doctor_id, $service_id)` - obține configurarea
  - [x] `get_hidden_slots($doctor_id, $date)` - obține sloturile ascunse
  - [x] `get_staff_only_slots($doctor_id, $date)` - obține sloturile pentru staff
  - [x] `generate_available_slots()` - algoritm îmbunătățit de generare
  - [x] `apply_buffer_time()` - aplică buffer time între sloturi
  - [x] `filter_slots_by_user_type()` - filtrează sloturile după tipul utilizatorului

### 2.3 Extinderea API-ului
- [ ] **Modificare în `class-api.php`**
  - [ ] Actualizarea endpoint-ului `/slots` pentru a folosi noua logică
  - [ ] Adăugarea endpoint-ului `/slot-settings` pentru configurare
  - [ ] Adăugarea endpoint-ului `/hidden-slots` pentru gestionarea ascunderii
  - [ ] Adăugarea endpoint-ului `/staff-slots` pentru sloturile staff only
  - [ ] Implementarea cache-ului pentru sloturile generate
  - [ ] Adăugarea parametrului `user_type` pentru filtrarea sloturilor

---

## 🎨 Faza 3: Interfața Admin pentru Gestionarea Sloturilor (2-3 ore)

### 3.1 Pagina Nouă în Admin
- [ ] **Crearea paginii "Slot Management"**
  - [ ] Adăugarea în meniul admin (`class-admin.php`)
  - [ ] Crearea metodei `render_slot_management()`
  - [ ] Implementarea interfeței pentru fiecare doctor

### 3.2 Interfața Vizuală cu Calendarul
- [ ] **Implementarea calendarului săptămânii**
  - [ ] Vizualizarea programului pentru fiecare zi
  - [ ] Posibilitatea de a ascunde sloturi specifice
  - [ ] Drag & drop pentru configurarea pauzelor
  - [ ] Preview în timp real al sloturilor generate

### 3.3 Configurarea Setărilor Sloturilor
- [ ] **Formulare pentru configurare**
  - [ ] Intervalul între sloturi (15, 30, 45, 60 minute)
  - [ ] Buffer time între programări (0, 5, 10, 15 minute)
  - [ ] Zilele maxime în avans (7, 14, 30, 60 zile)
  - [ ] Orele minime în avans (1, 2, 4, 8 ore)

### 3.4 Gestionarea Sloturilor Ascunse și Staff Only
- [ ] **Interfața pentru ascundere sloturi**
  - [ ] Ascundere sloturi specifice pe ore
  - [ ] Ascundere zile întregi
  - [ ] Ascundere recurentă (ex: fiecare marți la 12:00)
  - [ ] Ascundere temporară (ex: între 1-15 ianuarie)
  - [ ] Motive pentru ascundere (Pauză masă, Concediu, Eveniment special, etc.)

- [ ] **Interfața pentru sloturi staff only**
  - [ ] Crearea sloturilor dedicate pentru staff
  - [ ] Configurarea recurentă pentru sloturi staff (ex: fiecare luni dimineața)
  - [ ] Adăugarea notelor pentru staff (întâlniri interne, training, etc.)
  - [ ] Vizualizarea separată a sloturilor staff în calendar
  - [ ] Opțiunea de a permite sau interzice programări în sloturi staff

---

## ⚡ Faza 4: Integrarea cu Frontend React (1-2 ore)

### 4.1 Înlocuirea Simulării cu API Real
- [ ] **Modificare în `booking-component.js`**
  - [ ] Înlocuirea funcției `generateTimeSlots()` cu API call
  - [ ] Implementarea funcției `fetchTimeSlots(doctorId, date, serviceId)`
  - [ ] Actualizarea logicii de filtrare pentru sloturi
  - [ ] Gestionarea sloturilor ocupate și blocate

```javascript
// Înlocuirea acestei secțiuni:
const generateTimeSlots = () => {
  // ... logica mock pentru generarea sloturilor
};

// Cu această implementare:
const fetchTimeSlots = async (doctorId, date, serviceId) => {
  try {
    const response = await fetch(
      `/wp-json/mbs/v1/slots?doctorId=${doctorId}&date=${date}&serviceId=${serviceId}`
    );
    if (!response.ok) throw new Error('Failed to fetch time slots');
    return await response.json();
  } catch (error) {
    console.error('Error fetching time slots:', error);
    return [];
  }
};
```

### 4.2 Îmbunătățirea Interfeței Utilizator
- [ ] **Indicatori vizuali pentru sloturi**
  - [ ] Tooltip cu motivul ascunderii pentru sloturi blocate
  - [ ] Indicatori pentru sloturi cu buffer time
  - [ ] Indicatori speciali pentru sloturi staff only (ex: iconiță cu 🔒)
  - [ ] Refresh automat când se schimbă selecțiile
  - [ ] Loading states pentru încărcarea sloturilor

- [ ] **Gestionarea sloturilor staff only**
  - [ ] Afișarea separată a sloturilor staff în interfața admin
  - [ ] Opțiunea de a permite programări în sloturi staff (pentru admin)
  - [ ] Mesaje explicative pentru sloturile staff în frontend
  - [ ] Filtrarea sloturilor după tipul utilizatorului (pacient vs staff)

### 4.3 Gestionarea Erorilor și Feedback
- [ ] **Mesaje de eroare și feedback**
  - [ ] Mesaje clare când nu sunt sloturi disponibile
  - [ ] Sugestii pentru zile alternative
  - [ ] Notificări pentru modificări în timp real
  - [ ] Fallback pentru când API-ul nu răspunde

---

## 🧪 Faza 5: Testare și Optimizare (1-2 ore)

### 5.1 Testarea Funcționalității Complete
- [ ] **Teste funcționale**
  - [ ] Testarea generării sloturilor cu diferite configurații
  - [ ] Verificarea ascunderii sloturilor specifice
  - [ ] Testarea buffer time între programări
  - [ ] Verificarea configurațiilor per doctor-serviciu

### 5.2 Testarea Performanței
- [ ] **Teste de performanță**
  - [ ] Măsurarea timpului de generare sloturi
  - [ ] Testarea cu multe configurații diferite
  - [ ] Verificarea cache-ului pentru sloturi
  - [ ] Testarea pe dispozitive mobile

### 5.3 Testarea Interfeței Admin
- [ ] **Teste de utilizabilitate**
  - [ ] Testarea configurarii sloturilor în admin
  - [ ] Verificarea ascunderii sloturilor
  - [ ] Testarea preview-ului în timp real
  - [ ] Verificarea salvării configurațiilor

---

## 📚 Faza 6: Documentație și Finalizare (1 oră)

### 6.1 Documentarea Noilor Funcționalități
- [ ] **Documentație tehnică**
  - [ ] Actualizarea README.md cu noile funcționalități
  - [ ] Documentarea API endpoints noi
  - [ ] Crearea ghidului de utilizare pentru admin
  - [ ] Actualizarea changelog-ului

### 6.2 Optimizarea Finală
- [ ] **Cleanup și optimizări**
  - [ ] Eliminarea codului mort și comentariilor
  - [ ] Optimizarea query-urilor pentru baza de date
  - [ ] Verificarea compatibilității cu versiunile WordPress
  - [ ] Testarea pe diferite configurații de server

---

## 🎯 Rezultate Așteptate

### ✅ Funcționalități Noi
- [ ] Configurare flexibilă a intervalului între sloturi
- [ ] Sistem de ascundere sloturi specifice
- [ ] Buffer time configurabil între programări
- [ ] Sloturi dedicate pentru staff only cu note interne
- [ ] Interfață admin intuitivă pentru gestionarea sloturilor
- [ ] Generare automată bazată pe durata serviciului și programul doctorului
- [ ] Filtrarea sloturilor după tipul utilizatorului (pacient vs staff)

### 📊 Metrici de Succes
- [ ] Timpul de generare sloturi < 1 secundă
- [ ] Configurarea sloturilor în < 2 minute per doctor
- [ ] Compatibilitate cu toate browserele moderne
- [ ] Funcționalitate completă pe mobile

---

## ⏱️ Estimare Timp Total

| Faza | Timp Estimat | Prioritate |
|------|--------------|------------|
| Faza 1: Crearea tabelelor | 1-2 ore | 🔴 Critică |
| Faza 2: Îmbunătățirea algoritmului | 2-3 ore | 🔴 Critică |
| Faza 3: Interfața admin | 2-3 ore | 🟡 Medie |
| Faza 4: Integrarea frontend | 1-2 ore | 🟡 Medie |
| Faza 5: Testare | 1-2 ore | 🟢 Scăzută |
| Faza 6: Documentație | 1 oră | 🟢 Scăzută |
| **TOTAL** | **8-13 ore** | |

---

## 🚨 Note Importante

### ⚠️ Considerații de Securitate
- Toate modificările trebuie să respecte standardele WordPress
- Validarea datelor trebuie să fie dublă (frontend + backend)
- Permisiunile admin trebuie să fie verificate corect

### 🔧 Considerații Tehnice
- Backward compatibility cu sistemul actual
- Cache-ul trebuie să fie invalidat corect la modificări
- Query-urile trebuie să fie optimizate pentru performanță
- Suport pentru MySQL 5.7+ și MariaDB 10.2+

### 📱 Considerații de UX
- Interfața admin trebuie să fie intuitivă
- Preview-ul sloturilor trebuie să fie în timp real
- Feedback vizual pentru toate acțiunile
- Responsive design pentru tablete și mobile

---

## 🔒 Funcționalități Specifice pentru Sloturi Staff Only

### **Cazuri de Utilizare:**
- **Întâlniri interne** - staff meetings, briefing-uri
- **Training și dezvoltare** - sesiuni de formare pentru personal
- **Pauze administrative** - timp pentru documentație, rapoarte
- **Evenimente speciale** - conferințe, prezentări pentru personal
- **Maintenance** - timp pentru întreținerea echipamentelor

### **Caracteristici Tehnice:**
- **Vizibilitate:** Sloturile staff only sunt vizibile doar pentru utilizatorii cu rol de staff/admin
- **Rezervare:** Pot fi rezervate doar de către staff, nu de pacienți
- **Note interne:** Fiecare slot staff poate avea note explicative pentru personal
- **Recurență:** Pot fi configurate să se repete săptămânal (ex: fiecare luni 9:00-10:00)
- **Flexibilitate:** Pot fi create pentru o zi specifică sau recurent

### **Interfața Utilizator:**
- **Pentru Staff/Admin:** Sloturile staff sunt afișate cu iconiță specială (🔒) și culoare distinctă
- **Pentru Pacienți:** Sloturile staff nu sunt vizibile în interfața de rezervare
- **Calendar Admin:** Vizualizare separată cu filtre pentru tipul sloturilor

---

## 💡 Idei Suplimentare pentru Îmbunătățirea Sistemului

### **🕐 Sloturi cu Durată Variabilă**
- [ ] **Sloturi adaptive** - durata se ajustează automat în funcție de serviciul selectat
- [ ] **Sloturi compuse** - posibilitatea de a combina mai multe servicii într-un singur slot
- [ ] **Sloturi cu buffer inteligent** - buffer time diferit în funcție de tipul serviciului
- [ ] **Sloturi pentru servicii urgente** - cu prioritate mai mare și durată flexibilă

### **📅 Gestionarea Avansată a Calendarului**
- [ ] **Sloturi sezoniere** - configurații diferite pentru perioade specifice (vacanțe, sărbători)
- [ ] **Sloturi cu capacitate** - mai multe programări în același slot (ex: grupuri)
- [ ] **Sloturi cu lista de așteptare** - când nu sunt sloturi disponibile
- [ ] **Sloturi cu notificări** - alertă când se eliberează un slot

### **🎯 Personalizare Avansată**
- [ ] **Sloturi preferate** - pentru pacienții frecvenți
- [ ] **Sloturi cu discount** - pentru orele mai puțin populare
- [ ] **Sloturi cu servicii speciale** - pentru pacienții VIP sau cu nevoi speciale
- [ ] **Sloturi cu medic specific** - când pacientul preferă un anumit medic

### **📊 Analytics și Optimizare**
- [ ] **Statistici de utilizare** - care sunt orele cele mai/mai puțin populare
- [ ] **Sugestii automate** - pentru optimizarea programului doctorului
- [ ] **Predicții de ocupare** - bazate pe istoricul programărilor
- [ ] **Raportare avansată** - pentru managementul clinicii

### **🔄 Automatizare și Integrări**
- [ ] **Sincronizare cu calendare externe** - Google Calendar, Outlook
- [ ] **Notificări automate** - SMS, email pentru confirmări și reminder-uri
- [ ] **Rezervări automate** - pentru programări recurente
- [ ] **Integrare cu sisteme de plată** - pentru plata online a programărilor

### **📱 Funcționalități Mobile**
- [ ] **App mobile dedicată** - pentru medici și pacienți
- [ ] **Notificări push** - pentru modificări în program
- [ ] **Geolocație** - pentru verificarea proximității față de clinică
- [ ] **Scanare QR** - pentru check-in rapid la clinică

### **🔐 Securitate și Conformitate**
- [ ] **Audit trail** - pentru toate modificările în program
- [ ] **Backup automat** - pentru programările importante
- [ ] **Conformitate GDPR** - pentru gestionarea datelor personale
- [ ] **Criptare avansată** - pentru datele sensibile

### **🌐 Funcționalități Multilingve**
- [ ] **Suport pentru mai multe limbi** - interfața în română, engleză, etc.
- [ ] **Localizare temporală** - fusuri orare diferite pentru clinici internaționale
- [ ] **Formatare dată/ora** - în funcție de preferințele regionale
- [ ] **Monede multiple** - pentru prețurile serviciilor

### **🎨 Îmbunătățiri UX/UI**
- [ ] **Tema întunecată** - pentru utilizarea seara
- [ ] **Accesibilitate** - suport pentru utilizatorii cu dizabilități
- [ ] **Drag & drop** - pentru rearanjarea programărilor
- [ ] **Vizualizări multiple** - zi, săptămână, lună, an

### **🤖 Inteligence Artificială**
- [ ] **Chatbot pentru rezervări** - asistent virtual pentru pacienți
- [ ] **Predicții de anulare** - pentru a identifica programările cu risc de anulare
- [ ] **Optimizare automată** - pentru programul doctorului
- [ ] **Detectarea conflictelor** - înainte ca acestea să apară

---

## 🎯 Prioritizarea Ideilor Suplimentare

### **🔴 Prioritate Înaltă (Implementare Imediată)**
- [ ] **Sloturi cu durată variabilă** - funcționalitate de bază pentru flexibilitate
- [ ] **Sloturi cu lista de așteptare** - îmbunătățește experiența pacientului
- [ ] **Statistici de utilizare** - pentru optimizarea programului
- [ ] **Notificări automate** - reduce anulările și îmbunătățește comunicarea

### **🟡 Prioritate Medie (Implementare pe Termen Mediu)**
- [ ] **Sloturi sezoniere** - pentru gestionarea perioadelor speciale
- [ ] **Sincronizare cu calendare externe** - pentru medici care folosesc alte sisteme
- [ ] **Sloturi cu capacitate** - pentru servicii de grup
- [ ] **Analytics avansate** - pentru managementul clinicii

### **🟢 Prioritate Scăzută (Implementare pe Termen Lung)**
- [ ] **App mobile dedicată** - necesită dezvoltare separată
- [ ] **Inteligence Artificială** - tehnologie avansată, costuri mari
- [ ] **Funcționalități multilingve** - dacă clinica nu are pacienți internaționali
- [ ] **Integrare cu sisteme de plată** - depinde de necesitățile clinicii

### **💡 Recomandări Specifice pentru Plugin-ul Tău:**

#### **1. Sloturi Adaptive (Prioritate Înaltă)**
```php
// Exemplu de implementare
public function get_adaptive_slots($doctor_id, $date, $service_id) {
    $service_duration = $this->get_service_duration($service_id);
    $slot_settings = $this->get_slot_settings($doctor_id, $service_id);
    
    // Ajustează intervalul în funcție de durata serviciului
    $adaptive_interval = max($service_duration, $slot_settings['slot_interval']);
    
    return $this->generate_slots_with_interval($doctor_id, $date, $adaptive_interval);
}
```

#### **2. Lista de Așteptare (Prioritate Înaltă)**
```sql
-- Tabel nou pentru lista de așteptare
CREATE TABLE wp_mbs_waiting_list (
    id int(11) NOT NULL AUTO_INCREMENT,
    patient_id int(11) NOT NULL,
    doctor_id int(11) NOT NULL,
    service_id int(11) NOT NULL,
    preferred_date date NOT NULL,
    preferred_time time,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

#### **3. Statistici de Utilizare (Prioritate Înaltă)**
```php
// Metodă pentru obținerea statisticilor
public function get_slot_analytics($doctor_id, $start_date, $end_date) {
    return [
        'most_popular_hours' => $this->get_popular_hours($doctor_id, $start_date, $end_date),
        'least_popular_hours' => $this->get_unpopular_hours($doctor_id, $start_date, $end_date),
        'cancellation_rate' => $this->get_cancellation_rate($doctor_id, $start_date, $end_date),
        'average_booking_time' => $this->get_average_booking_time($doctor_id, $start_date, $end_date)
    ];
}
```

#### **4. Notificări Automate (Prioritate Înaltă)**
```php
// Sistem de notificări
public function send_appointment_reminder($appointment_id, $hours_before = 24) {
    $appointment = $this->get_appointment($appointment_id);
    $patient = $this->get_patient($appointment['patient_id']);
    
    // Trimite email/SMS cu reminder
    $this->send_notification($patient, 'reminder', $appointment);
}
```

---

## 📞 Următorii Pași

1. **Începe cu Faza 1** - Crearea tabelelor noi
2. **Testează fiecare modificare** - Pentru a asigura funcționalitatea
3. **Implementează progresiv** - Faza cu faza pentru stabilitate
4. **Documentează modificările** - Pentru mentenanță viitoare

---

*Document creat pe: $(date)*
*Versiune: 1.0*
*Status: Ready for Implementation*
