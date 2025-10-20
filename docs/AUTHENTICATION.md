# 🔐 Sistem Autentificare - Medical Booking System

## Prezentare Generală

Sistemul de autentificare permite utilizatorilor să se înregistreze și să se autentifice folosind:
- **CNP** (Cod Numeric Personal românesc)
- **Email**
- **Număr de telefon** (unul sau mai multe)

## 🚀 Instalare și Activare

### 1. Activare Plugin

```
WordPress Admin → Plugins → Medical Booking System → Activate
```

### 2. Tabele Database Create Automat

La activare, sunt create următoarele tabele:
- `wp_mbs_user_phones` - Telefoane utilizatori (multi-phone support)
- User meta: `mbs_cnp` - CNP-ul utilizatorului

### 3. Roluri Create

- **Patient (mbs_patient)** - Utilizator standard, poate face programări
- **Receptionist (mbs_receptionist)** - Poate gestiona toate programările
- **Assistant (mbs_assistant)** - Asistent medical
- **Doctor (mbs_doctor)** - Medic
- **Manager (mbs_manager)** - Manager cu acces complet

## 📄 Utilizare Shortcode

### Shortcode pentru Login/Register

Adaugă următorul shortcode în orice pagină WordPress:

```
[mbs_auth]
```

**Exemplu de utilizare:**
1. Creează o pagină nouă: **"Autentificare"**
2. Adaugă shortcode-ul `[mbs_auth]`
3. Publică pagina
4. Vizitatorii vor vedea formularul de login/register

## 🎯 Funcționalități

### Înregistrare (Register)

**Câmpuri obligatorii:**
- **CNP** - Cod Numeric Personal (13 cifre, validat cu algoritm)
- **Prenume**
- **Nume**
- **Email** - Adresă email validă
- **Parolă** - Minim 8 caractere
- **Confirmare Parolă**

**Câmpuri opționale:**
- **Telefon** - Format românesc (07XXXXXXXX)

**Validări:**
- CNP românesc valid (13 cifre + algoritm de control)
- Email unic (nu poate fi folosit de alt utilizator)
- CNP unic (nu poate fi folosit de alt utilizator)
- Telefon format valid (dacă este completat)

**După înregistrare:**
- Utilizatorul este automat autentificat
- I se atribuie rolul `mbs_patient`
- CNP devine `username` în WordPress
- Redirect automat la pagină

### Autentificare (Login)

**Login flexibil - un singur câmp "Identificator":**
Utilizatorul poate introduce:
- **CNP**: `1234567890123`
- **Email**: `email@example.com`
- **Telefon**: `0712345678` sau `+40712345678`

**Opțiuni:**
- **Ține-mă minte** - Salvează sesiunea pentru 14 zile

**Detectare automată:**
Sistemul detectează automat tipul de identificator:
- 13 cifre → CNP
- Conține @ → Email
- Începe cu 07 → Telefon

### După Autentificare

Utilizatorul autentificat vede:
- **Numele complet**
- **Email**
- **CNP mascat** (ex: *********1234)
- **Telefon principal**
- **Buton Deconectare**

## 🔧 API Endpoints

### POST `/wp-json/mbs/v1/auth/register`

Înregistrare utilizator nou.

**Request:**
```json
{
  "cnp": "1234567890123",
  "email": "ion.popescu@example.com",
  "password": "parola123",
  "first_name": "Ion",
  "last_name": "Popescu",
  "phone": "0712345678"
}
```

**Response (Success):**
```json
{
  "success": true,
  "user_id": 42,
  "message": "Înregistrare reușită",
  "user": {
    "id": 42,
    "cnp": "1234567890123",
    "email": "ion.popescu@example.com",
    "first_name": "Ion",
    "last_name": "Popescu",
    "display_name": "Ion Popescu",
    "roles": ["mbs_patient"]
  }
}
```

**Response (Error):**
```json
{
  "code": "cnp_exists",
  "message": "CNP deja înregistrat",
  "data": { "status": 400 }
}
```

### POST `/wp-json/mbs/v1/auth/login`

Autentificare utilizator.

**Request:**
```json
{
  "identifier": "1234567890123",
  "password": "parola123",
  "remember": true
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Autentificare reușită",
  "user": {
    "id": 42,
    "cnp": "1234567890123",
    "email": "ion.popescu@example.com",
    "first_name": "Ion",
    "last_name": "Popescu",
    "display_name": "Ion Popescu",
    "roles": ["mbs_patient"],
    "phones": [
      {
        "id": 1,
        "phone": "0712345678",
        "is_primary": 1,
        "is_verified": 0
      }
    ]
  }
}
```

### GET `/wp-json/mbs/v1/auth/me`

Informații utilizator curent (necesită autentificare).

**Headers:**
```
X-WP-Nonce: {nonce}
```

**Response:**
```json
{
  "id": 42,
  "cnp": "1234567890123",
  "cnp_masked": "*********0123",
  "email": "ion.popescu@example.com",
  "first_name": "Ion",
  "last_name": "Popescu",
  "display_name": "Ion Popescu",
  "roles": ["mbs_patient"],
  "phones": [...]
}
```

### GET `/wp-json/mbs/v1/auth/phones`

Lista telefoanelor utilizatorului (necesită autentificare).

### POST `/wp-json/mbs/v1/auth/phones`

Adaugă telefon nou (necesită autentificare).

**Request:**
```json
{
  "phone": "0723456789",
  "is_primary": false
}
```

### DELETE `/wp-json/mbs/v1/auth/phones/{id}`

Șterge telefon (necesită autentificare).

### PUT `/wp-json/mbs/v1/auth/phones/{id}/primary`

Setează telefon ca principal (necesită autentificare).

## 🔒 Securitate

### Validare CNP

Algoritmul de validare CNP românesc:
1. CNP trebuie să aibă exact 13 cifre
2. Se aplică algoritmul de control cu string-ul `279146358279`
3. Suma produselor primelor 12 cifre cu cifrele din string de control
4. Control digit = suma % 11 (dacă 10, devine 1)
5. Cifra 13 trebuie să fie egală cu control digit

### Protecție Date

- **CNP masking**: În frontend, CNP-ul este afișat mascat (*********1234)
- **Rate limiting**: 20 requests/minut per IP/user
- **Nonce verification**: Toate request-urile au verificare nonce
- **Password hashing**: WordPress native password hashing
- **SQL injection protection**: Prepared statements în toate query-urile

### GDPR Compliance

- Utilizatorii pot solicita ștergerea datelor
- CNP-ul nu este afișat complet în interfață
- Telefoanele pot fi șterse de utilizator

## 📱 Phone Management

### Telefoane Multiple

Un utilizator poate avea **mai multe telefoane**:
- Unul poate fi marcat ca **principal**
- Fiecare telefon poate fi **verificat** (opțional, pentru SMS OTP în viitor)
- Telefoanele sunt normalizate automat (ex: +40712345678 → 0712345678)

### Format Telefon

**Acceptate:**
- `0712345678` (format standard)
- `+40712345678` (format internațional)
- `0407 12345678` (cu prefix alternativ)

**Normalizare automată:**
Toate telefoanele sunt convertite la formatul `07XXXXXXXX`.

## 👤 User Profile în WordPress

### Câmp CNP în Profile

În **WordPress Admin → Users → Edit User**, există un câmp **CNP** în secțiunea "Date Medicale".

**Restricții:**
- Doar **administratorii** pot modifica CNP-ul după creare
- CNP-ul trebuie să fie unic
- Validare automată la salvare

## 🧪 Testare

### Test Register

1. Accesează pagina cu shortcode `[mbs_auth]`
2. Click pe tab "Înregistrare"
3. Completează:
   - CNP: `1234567890123` (CNP valid de test)
   - Email: `test@example.com`
   - Prenume: `Ion`
   - Nume: `Popescu`
   - Telefon: `0712345678`
   - Parolă: `parola123`
4. Click "Înregistrare"
5. Verifică că ești autentificat automat

### Test Login cu CNP

1. Logout
2. În câmpul "CNP, Email sau Telefon", introdu: `1234567890123`
3. Parolă: `parola123`
4. Click "Autentificare"

### Test Login cu Email

1. În câmpul identificator, introdu: `test@example.com`
2. Parolă: `parola123`
3. Click "Autentificare"

### Test Login cu Telefon

1. În câmpul identificator, introdu: `0712345678`
2. Parolă: `parola123`
3. Click "Autentificare"

## 🐛 Troubleshooting

### "CNP invalid"

**Cauză:** CNP-ul nu trece validarea algoritmului de control.

**Soluție:** Folosește un CNP valid de test:
- `1234567890123`
- `2990101223344`

### "Email deja înregistrat"

**Cauză:** Email-ul este folosit de alt utilizator WordPress.

**Soluție:** Folosește alt email sau șterge utilizatorul existent.

### "Format telefon invalid"

**Cauză:** Telefonul nu este în format românesc valid.

**Soluție:** Folosește format: `0712345678` (10 cifre, începe cu 07).

### "Identificator sau parolă incorectă"

**Cauză:** Credențialele nu se potrivesc cu niciun utilizator.

**Soluție:**
- Verifică CNP/Email/Telefon
- Verifică parola (case-sensitive)
- Asigură-te că utilizatorul există în WordPress

## 📞 Suport

Pentru probleme sau întrebări, verifică:
1. **debug.log** în `wp-content/debug.log`
2. **Console browser** (F12 → Console)
3. **Network tab** (F12 → Network) pentru API errors

## 🎉 Status Implementare

✅ **COMPLET:**
- Database schema (wp_mbs_user_phones, CNP în user_meta)
- Validare CNP românesc
- WordPress authenticate hook (login cu CNP/email/telefon)
- REST API endpoints (register, login, me, phones)
- React Login/Register form
- Shortcode [mbs_auth]
- Rate limiting și security
- CNP masking
- Multi-phone support

🔄 **ÎN DEZVOLTARE:**
- Phone verification via SMS OTP
- User profile page (manage phones)
- Password reset flow

---

*Ultima actualizare: 20 octombrie 2025*

