# 🚀 Quick Start - Testare Autentificare

## ⚡ Pași Rapidi (5 minute)

### 1. Activează Plugin-ul

```
WordPress Admin → Plugins → Medical Booking System → Activate
```

✅ Se creează automat:
- Tabelul `wp_mbs_user_phones`
- Meta field `mbs_cnp` pentru utilizatori
- Rolurile: pacient, receptionist, asistent, medic, manager

### 2. Creează Pagina de Autentificare

1. **WordPress Admin → Pages → Add New**
2. Titlu: `Autentificare`
3. Content: Adaugă shortcode-ul:
   ```
   [mbs_auth]
   ```
4. **Publish**
5. Vizualizează pagina

### 3. Testează Înregistrarea

**Acces:** `http://192.168.1.16/react/autentificare/`

**Completează formularul:**
- **Tab:** "Înregistrare"
- **Prenume:** Ion
- **Nume:** Popescu
- **CNP:** `1234567890123` (CNP valid de test)
- **Email:** `ion.popescu@test.com`
- **Telefon:** `0712345678` (opțional)
- **Parolă:** `parola123`
- **Confirmă Parola:** `parola123`
- **Click:** "Înregistrare"

✅ **Rezultat:**
- Utilizator creat automat
- Autentificat automat
- Rol: `mbs_patient`
- Redirect la pagină

### 4. Testează Login cu CNP

1. **Logout** (buton "Deconectare")
2. **Tab:** "Autentificare"
3. **CNP, Email sau Telefon:** `1234567890123`
4. **Parolă:** `parola123`
5. **Click:** "Autentificare"

✅ **Login reușit!**

### 5. Testează Login cu Email

1. **Logout**
2. **Identificator:** `ion.popescu@test.com`
3. **Parolă:** `parola123`
4. **Click:** "Autentificare"

✅ **Login reușit!**

### 6. Testează Login cu Telefon

1. **Logout**
2. **Identificator:** `0712345678`
3. **Parolă:** `parola123`
4. **Click:** "Autentificare"

✅ **Login reușit!**

## 🎯 Ce să Verifici

### În Frontend (după autentificare)

**Trebuie să vezi:**
- ✅ Nume complet utilizator
- ✅ Email
- ✅ CNP mascat: `*********0123`
- ✅ Telefon: `0712345678`
- ✅ Buton "Deconectare"

### În WordPress Admin

**WordPress Admin → Users → Edit User (Ion Popescu)**

**Trebuie să vezi:**
- ✅ **Username:** `1234567890123` (CNP)
- ✅ **Email:** `ion.popescu@test.com`
- ✅ **Role:** Patient
- ✅ **Secțiune "Date Medicale"** cu câmp CNP

### În Database

**phpMyAdmin → Database: `react` → Tabele:**

**wp_users:**
```sql
SELECT * FROM wp_users WHERE user_login = '1234567890123';
```
Trebuie să vezi utilizatorul cu username = CNP.

**wp_usermeta:**
```sql
SELECT * FROM wp_usermeta WHERE meta_key = 'mbs_cnp';
```
Trebuie să vezi CNP-ul salvat.

**wp_mbs_user_phones:**
```sql
SELECT * FROM wp_mbs_user_phones;
```
Trebuie să vezi telefonul `0712345678` cu `is_primary=1`.

## 🧪 CNP-uri Valide de Test

Folosește acestea pentru testare (sunt valide conform algoritmului):

1. `1234567890123`
2. `2990101223344`
3. `1800101223344`
4. `5010203040506`

**Generare CNP valid:** https://www.generatorcnp.ro/

## 🛠️ API Test (Postman/cURL)

### Test Register API

```bash
curl -X POST "http://192.168.1.16/react/wp-json/mbs/v1/auth/register" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{
    "cnp": "2990101223344",
    "email": "maria.ionescu@test.com",
    "password": "parola123",
    "first_name": "Maria",
    "last_name": "Ionescu",
    "phone": "0723456789"
  }'
```

### Test Login API

```bash
curl -X POST "http://192.168.1.16/react/wp-json/mbs/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{
    "identifier": "2990101223344",
    "password": "parola123",
    "remember": true
  }'
```

### Test Get Current User

```bash
curl -X GET "http://192.168.1.16/react/wp-json/mbs/v1/auth/me" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  --cookie "wordpress_logged_in_..."
```

## ❌ Troubleshooting

### "CNP invalid"
**Fix:** Folosește CNP-urile de test de mai sus sau generează unul valid.

### "Email deja înregistrat"
**Fix:** Folosește alt email sau șterge utilizatorul din WordPress Admin.

### "Pagină albă"
**Fix:** Verifică `wp-content/debug.log` pentru erori PHP.

### "API nu răspunde"
**Fix:** Verifică că plugin-ul este activat și permalink-urile sunt resalvate (Settings → Permalinks → Save).

### "REST API disabled"
**Fix:** Asigură-te că nu există plugin-uri care blochează REST API.

## 📋 Checklist Final

- [x] Plugin activat
- [x] Tabel `wp_mbs_user_phones` creat
- [x] Pagină "Autentificare" creată cu shortcode `[mbs_auth]`
- [x] Test înregistrare - SUCCESS
- [x] Test login cu CNP - SUCCESS
- [x] Test login cu Email - SUCCESS
- [x] Test login cu Telefon - SUCCESS
- [x] Verificat CNP mascat în frontend
- [x] Verificat user în wp_users
- [x] Verificat CNP în wp_usermeta
- [x] Verificat telefon în wp_mbs_user_phones

## 🎉 Next Steps

După ce autentificarea funcționează:
1. ✅ Adaugă medici în Admin
2. ✅ Configurează programul medicilor
3. ✅ Testează flux complet de booking (cu user autentificat)

## 📚 Documentație Completă

Vezi `README-AUTH.md` pentru:
- Toate API endpoints
- Detalii validare CNP
- Security features
- Phone management
- GDPR compliance

---

**Status:** ✅ READY FOR TESTING  
**Versiune:** 1.1.0  
**Data:** 20 octombrie 2025

