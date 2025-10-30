# 📋 TODO Extins - Integrarea Frontend-Backend pentru Logica de Programare

## 🎯 Obiectiv General
Integrarea completă a logicii de programare din frontend (React) cu backend-ul (PHP/WordPress) pentru a crea un sistem funcțional de programări medicale.

---

## 📊 Analiza Situației Actuale - STATUS ACTUALIZAT

### ✅ Ce Funcționează DEJA
- [x] Backend API complet implementat (`class-api.php`)
- [x] Serviciu de programări funcțional (`class-appointment.php`)
- [x] Baza de date cu 10 tabele custom
- [x] Frontend React cu flow complet de programare
- [x] Autentificare și gestionare utilizatori
- [x] Rate limiting și securitate API
- [x] **Servicii și medici** - API calls reale implementate
- [x] **Sloturi de timp** - API enhanced slots implementat
- [x] **Validarea conflictelor** - funcționează în backend
- [x] **Autentificare completă** - login/register cu API

### ❌ Ce Mai Trebuie Integrat
- [ ] **Crearea programărilor** - folosește simulare în loc de API real
- [ ] **Membrii familie** - folosește date mock în loc de API
- [ ] **Gestionarea pacienților** - nu este conectată la sistemul de membri familie

---

## 🚀 Faza 1: Integrarea Creării Programărilor (1-2 ore) - PRIORITATE CRITICĂ

### 1.1 Înlocuirea Simulării cu API Real
- [ ] **Modificare în `booking-component.js`**
  - [ ] Înlocuirea simulării din `handleNext()` cu API call real
  - [ ] Implementarea funcției `createAppointment()`
  - [ ] Adăugarea gestionării erorilor pentru crearea programărilor
  - [ ] Implementarea feedback-ului de succes

```javascript
// Înlocuirea acestei secțiuni din handleNext():
if (step === 6) {
  // Simulare procesare programare
  setLoading(true);
  setTimeout(() => {
    setLoading(false);
    setStep(7);
  }, 2000);
}

// Cu această implementare:
if (step === 6) {
  await createAppointment();
}

const createAppointment = async () => {
  try {
    setLoading(true);
    setError(null);
    
    const appointmentData = {
      doctor_id: bookingData.doctor.id,
      patient_id: bookingData.familyMember?.id || getCurrentUserId(),
      service_id: bookingData.service.id,
      appointment_date: bookingData.date,
      start_time: bookingData.timeSlot.split('-')[0],
      end_time: bookingData.timeSlot.split('-')[1],
      notes: bookingData.notes,
      patient_notes: bookingData.familyMember?.notes || ''
    };
    
    const response = await fetch('/wp-json/mbs/v1/appointments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': mbs_ajax.rest_nonce,
      },
      credentials: 'include',
      body: JSON.stringify(appointmentData)
    });
    
    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Eroare la crearea programării');
    }
    
    const result = await response.json();
    setStep(7); // Trece la confirmare
  } catch (err) {
    setError(err.message);
  } finally {
    setLoading(false);
  }
};
```

### 1.2 Gestionarea Pacienților pentru Programări
- [ ] **Modificare în `booking-component.js`**
  - [ ] Implementarea funcției `getCurrentUserId()` pentru utilizatorul curent
  - [ ] Gestionarea cazurilor când utilizatorul nu este logat
  - [ ] Adăugarea validării pentru pacientul selectat
  - [ ] Implementarea fallback-ului pentru pacienți

---

## 👥 Faza 2: Integrarea Membrilor Familie (2-3 ore) - PRIORITATE MEDIE

### 2.1 Conectarea la API-ul pentru Telefoane
- [ ] **Modificare în `booking-component.js`**
  - [ ] Înlocuirea array-ului static `familyMembers` cu API call
  - [ ] Implementarea funcției `fetchFamilyMembers()`
  - [ ] Adăugarea loading state pentru membri familie
  - [ ] Gestionarea erorilor de încărcare

```javascript
// Înlocuirea acestei secțiuni:
const familyMembers = [
  { id: 0, name: 'Eu', phone: '0722123456', cnp: '1850101123456', isDefault: true },
  // ... alți membri mock
];

// Cu această implementare:
const [familyMembers, setFamilyMembers] = useState([]);
const [loadingFamilyMembers, setLoadingFamilyMembers] = useState(false);

const fetchFamilyMembers = async () => {
  try {
    setLoadingFamilyMembers(true);
    const response = await fetch('/wp-json/mbs/v1/auth/phones', {
      headers: {
        'X-WP-Nonce': mbs_ajax.rest_nonce,
      },
      credentials: 'include',
    });
    
    if (!response.ok) {
      throw new Error('Eroare la încărcarea membrilor familie');
    }
    
    const phones = await response.json();
    // Transformăm telefoanele în membri familie
    const members = phones.map(phone => ({
      id: phone.id,
      name: phone.is_primary ? 'Eu' : `Membru ${phone.id}`,
      phone: phone.phone_number,
      cnp: phone.cnp || '',
      isDefault: phone.is_primary
    }));
    
    setFamilyMembers(members);
  } catch (error) {
    console.error('Error fetching family members:', error);
    setFamilyMembers([]);
  } finally {
    setLoadingFamilyMembers(false);
  }
};
```

### 2.2 Extinderea API-ului pentru Membri Familie Complet
- [ ] **Modificare în `class-api.php`**
  - [ ] Adăugarea endpoint-ului `/family-members` pentru membri completi
  - [ ] Implementarea funcției `get_family_members()`
  - [ ] Adăugarea endpoint-ului pentru crearea membrilor noi
  - [ ] Implementarea funcției `create_family_member()`

```php
// Adăugarea endpoint-urilor:
register_rest_route($ns, '/family-members', array(
    'methods' => WP_REST_Server::READABLE,
    'callback' => array($this, 'get_family_members'),
    'permission_callback' => 'is_user_logged_in',
));

register_rest_route($ns, '/family-members', array(
    'methods' => WP_REST_Server::CREATABLE,
    'callback' => array($this, 'create_family_member'),
    'permission_callback' => 'is_user_logged_in',
));
```

### 2.3 Gestionarea Pacienților în Programări
- [ ] **Modificare în `class-appointment.php`**
  - [ ] Extinderea funcționalității pentru membri familie
  - [ ] Implementarea relațiilor între utilizatori și pacienți
  - [ ] Adăugarea validării CNP pentru membri familie
  - [ ] Implementarea gestionării telefoanelor multiple

---

## ⚡ Faza 3: Optimizări și Performanță (1-2 ore) - PRIORITATE SCĂZUTĂ

### 3.1 Implementarea Cache-ului pentru Sloturi
- [ ] **Modificare în `class-appointment.php`**
  - [ ] Adăugarea cache-ului temporar pentru sloturi (15 minute)
  - [ ] Implementarea invalidării cache la modificări
  - [ ] Adăugarea cache-ului pentru servicii și medici
  - [ ] Implementarea cache-ului pentru programările utilizatorului

```php
// Implementarea cache-ului:
public function get_enhanced_slots_cached($doctor_id, $date, $service_id, $user_type) {
    $cache_key = "mbs_enhanced_slots_{$doctor_id}_{$date}_{$service_id}_{$user_type}";
    $cached_slots = get_transient($cache_key);
    
    if ($cached_slots !== false) {
        return $cached_slots;
    }
    
    $slots = $this->get_enhanced_slots($doctor_id, $date, $service_id, $user_type);
    set_transient($cache_key, $slots, 15 * MINUTE_IN_SECONDS);
    
    return $slots;
}
```

### 3.2 Optimizarea Cererilor API
- [ ] **Modificare în `booking-component.js`**
  - [ ] Implementarea lazy loading pentru sloturi
  - [ ] Adăugarea debouncing pentru căutări
  - [ ] Implementarea paginării pentru liste mari
  - [ ] Adăugarea preloading pentru date frecvente

### 3.3 Gestionarea Stărilor de Loading
- [ ] **Modificare în `booking-component.js`**
  - [ ] Adăugarea loading states pentru toate operațiunile
  - [ ] Implementarea skeleton loaders
  - [ ] Adăugarea progress indicators
  - [ ] Gestionarea timeout-urilor pentru cereri

---

## 🧪 Faza 4: Testare și Validare (1-2 ore) - PRIORITATE SCĂZUTĂ

### 4.1 Testarea Integrării Complete
- [ ] **Teste funcționale**
  - [ ] Testarea fluxului complet de programare cu API real
  - [ ] Verificarea creării programărilor în baza de date
  - [ ] Testarea gestionării membrilor familie
  - [ ] Verificarea gestionării erorilor

### 4.2 Testarea Performanței
- [ ] **Teste de performanță**
  - [ ] Măsurarea timpului de încărcare
  - [ ] Testarea cu date mari (multe programări)
  - [ ] Verificarea cache-ului și invalidării
  - [ ] Testarea pe dispozitive mobile

### 4.3 Testarea Securității
- [ ] **Teste de securitate**
  - [ ] Verificarea validării nonce
  - [ ] Testarea rate limiting
  - [ ] Verificarea permisiunilor utilizatorilor
  - [ ] Testarea protecției împotriva XSS

---

## 📚 Faza 5: Documentație și Finalizare (1 oră) - PRIORITATE SCĂZUTĂ

### 5.1 Documentarea Modificărilor
- [ ] **Documentație tehnică**
  - [ ] Actualizarea README.md cu noile funcționalități
  - [ ] Documentarea API endpoints noi
  - [ ] Crearea ghidului de utilizare pentru dezvoltatori
  - [ ] Actualizarea changelog-ului

### 5.2 Optimizarea Finală
- [ ] **Cleanup și optimizări**
  - [ ] Eliminarea codului mort și comentariilor
  - [ ] Optimizarea bundle-ului JavaScript
  - [ ] Verificarea compatibilității cu browsere
  - [ ] Testarea pe diferite versiuni WordPress

---

## 🎯 Rezultate Așteptate

### ✅ Funcționalități Complete
- [ ] Sistem de programare complet funcțional cu API real
- [ ] Crearea programărilor în baza de date
- [ ] Gestionarea membrilor familie din API
- [ ] Cache și optimizări de performanță
- [ ] Gestionarea erorilor robustă

### 📊 Metrici de Succes
- [ ] Timpul de încărcare < 2 secunde
- [ ] Rate de eroare < 1%
- [ ] Compatibilitate cu toate browserele moderne
- [ ] Funcționalitate completă pe mobile

---

## ⏱️ Estimare Timp Total - ACTUALIZAT

| Faza | Timp Estimat | Prioritate | Status |
|------|--------------|------------|--------|
| Faza 1: Crearea programărilor | 1-2 ore | 🔴 Critică | ❌ De făcut |
| Faza 2: Membri familie | 2-3 ore | 🟡 Medie | ❌ De făcut |
| Faza 3: Optimizări | 1-2 ore | 🟢 Scăzută | ❌ De făcut |
| Faza 4: Testare | 1-2 ore | 🟢 Scăzută | ❌ De făcut |
| Faza 5: Documentație | 1 oră | 🟢 Scăzută | ❌ De făcut |
| **TOTAL** | **6-10 ore** | | |

---

## 🚨 Note Importante - ACTUALIZATE

### ⚠️ Considerații de Securitate
- Toate API calls trebuie să folosească nonce verification
- Rate limiting este deja implementat (20 requests/minut)
- Validarea datelor trebuie să fie dublă (frontend + backend)

### 🔧 Considerații Tehnice
- Frontend-ul folosește React 18 cu Babel Standalone
- Backend-ul folosește WordPress REST API
- Baza de date folosește MySQL cu tabele custom
- Cache-ul folosește WordPress Transients API
- **Servicii, medici și sloturi sunt deja integrate**

### 📱 Considerații de UX
- Loading states pentru toate operațiunile
- Mesaje de eroare clare și utile
- Feedback vizual pentru toate acțiunile
- Responsive design pentru mobile

---

## 📞 Următorii Pași - ACTUALIZAȚI

1. **Începe cu Faza 1** - Integrarea creării programărilor (CRITICĂ)
2. **Continuă cu Faza 2** - Membrii familie (MEDIE)
3. **Finalizează cu Fazele 3-5** - Optimizări și testare (SCĂZUTĂ)

---

*Document actualizat pe: $(date)*
*Versiune: 2.0*
*Status: Ready for Implementation - Updated based on current integration status*
