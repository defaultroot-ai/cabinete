# 📋 Idei pentru Import Lista de Pacienți

## **🔹 Format de fișier:**
1. **Excel (.xlsx)** - cel mai comun
2. **CSV** - simplu și universal
3. **JSON** - pentru dezvoltatori
4. **XML** - pentru sisteme enterprise

## **🔹 Structura recomandată Excel/CSV:**
```
CNP | Last Name | First Name | Middle Name | Birth Date | Gender | Email | Phone | Address | Notes
```

## **🔹 Funcționalități:**

### **1. Upload și Preview:**
- **Drag & drop** fișier
- **Preview** primelor 10 rânduri
- **Validare** format și câmpuri obligatorii
- **Mapping** coloane (CNP → CNP, Nume → Last Name, etc.)

### **2. Validare și procesare:**
- **CNP validation** (algoritm + duplicat)
- **Email format** validation
- **Phone normalization** (RO format)
- **Date format** conversion (DD.MM.YYYY → ISO)
- **Name normalization** (diacritice)

### **3. Opțiuni de import:**
- **Skip duplicates** (CNP existent)
- **Update existing** (modifică pacientul existent)
- **Create new only** (doar pacienți noi)
- **Dry run** (preview rezultatelor fără salvare)

### **4. Raport de import:**
- **Succes**: X pacienți importați
- **Erori**: Y rânduri cu probleme
- **Sărite**: Z duplicat
- **Detalii** pentru fiecare eroare

### **5. Template și exemple:**
- **Download template** Excel cu coloanele corecte
- **Sample data** pentru testare
- **Instrucțiuni** de completare

## **🔹 UI/UX:**
- **Wizard** în 3 pași: Upload → Preview → Import
- **Progress bar** pentru importuri mari
- **Cancel** opțiune în timpul importului
- **Log** detaliat cu erorile

## **🔹 Securitate:**
- **File type validation** (doar Excel/CSV)
- **Size limits** (max 10MB)
- **Row limits** (max 1000 pacienți)
- **Nonce verification**
- **Capability checks**

## **🔹 Implementare tehnică:**

### **Backend (PHP):**
- `class-patient-import.php` - logica de import
- `wp_ajax_mbs_import_patients` - endpoint AJAX
- `wp_ajax_mbs_validate_import` - validare preview
- `wp_ajax_mbs_download_template` - download template

### **Frontend (JavaScript):**
- `patient-import.js` - UI și logica frontend
- Drag & drop handler
- Progress tracking
- Error display

### **Database:**
- `wp_mbs_import_logs` - tabel pentru loguri
- `wp_mbs_import_sessions` - sesiuni de import

## **🔹 Workflow:**

1. **Upload** → Validare fișier
2. **Preview** → Mapping coloane + validare date
3. **Import** → Procesare în batch + progres
4. **Raport** → Rezultate + erori

## **🔹 Exemple de erori comune:**
- CNP invalid (algoritm)
- CNP duplicat
- Email format greșit
- Data nașterii invalidă
- Câmpuri obligatorii lipsă
- Telefon format greșit

## **🔹 Template Excel:**
```
| CNP           | Last Name | First Name | Middle Name | Birth Date | Gender | Email              | Phone       | Address                    | Notes        |
|---------------|-----------|------------|-------------|------------|--------|--------------------|-------------|----------------------------|--------------|
| 1800404080170 | Test      | Beta       |             | 04.04.1980| M      | test@example.com   | 0769222973  | Str. Neptun nr. 8          | Patient note |
| 2780621080059 | Popescu   | Maria      | Elena       | 21.06.1978| F      | maria@example.com  | 0721234567  | Str. Mihai Viteazu nr. 15  |              |
```

## **🔹 Status:**
- [ ] Planificare completă
- [ ] Implementare backend
- [ ] Implementare frontend
- [ ] Testare și optimizare
- [ ] Documentație utilizator
