# 🔐 Implementare 2FA (Two-Factor Authentication)

## 📋 Overview

**Feature:** Autentificare în 2 Pași cu TOTP (Time-based One-Time Password)  
**Cost:** $0 (100% GRATUIT)  
**Timp estimat:** 6-8 ore  
**Prioritate:** MEDIE (după booking flow funcțional)  
**User Choice:** ✅ OPȚIONAL - Pacientul decide dacă activează sau nu

---

## 🎯 Ce va putea face pacientul?

### Scenario 1: Pacient fără 2FA (DEFAULT)
```
Login → CNP/Email/Telefon + Parolă → ✅ Acces direct
```

### Scenario 2: Pacient cu 2FA ACTIVAT
```
Login → CNP/Email/Telefon + Parolă → Cere cod din Google Authenticator → Cod valid → ✅ Acces
```

### Scenario 3: Pacient cu 2FA care pierde telefonul
```
Login → CNP/Email/Telefon + Parolă → "Nu am acces la app" → Folosește backup code → ✅ Acces
```

---

## 🏗️ Arhitectură Implementare

### 1. Backend (PHP)

#### **Fișiere noi:**

**`includes/class-totp.php`** - TOTP Logic
```php
<?php
class MBS_TOTP {
    private $ga; // GoogleAuthenticator instance
    
    public function __construct() {
        require_once MBS_PLUGIN_DIR . 'vendor/GoogleAuthenticator.php';
        $this->ga = new PHPGangsta_GoogleAuthenticator();
    }
    
    // Generare secret pentru user
    public function generate_secret() {
        return $this->ga->createSecret();
    }
    
    // Generare QR code URL
    public function get_qr_code_url($user_email, $secret) {
        return $this->ga->getQRCodeGoogleUrl(
            'Medical Booking System',
            $secret,
            $user_email
        );
    }
    
    // Verificare cod
    public function verify_code($secret, $code) {
        return $this->ga->verifyCode($secret, $code, 2); // ±60s tolerance
    }
    
    // Generare backup codes
    public function generate_backup_codes($count = 10) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = [
                'code' => sprintf('%08d', mt_rand(10000000, 99999999)),
                'used' => false,
                'used_at' => null
            ];
        }
        return $codes;
    }
}
```

**`vendor/GoogleAuthenticator.php`** - Library (descărcat)
- Download: https://github.com/PHPGangsta/GoogleAuthenticator
- 1 fișier, ~10KB, fără dependencies

#### **Modificări în fișiere existente:**

**`includes/class-api.php`** - Adăugare endpoint-uri:
```php
// Enable 2FA
register_rest_route($ns, '/auth/2fa/enable', [
    'methods' => 'POST',
    'callback' => [$this, 'enable_2fa'],
    'permission_callback' => 'is_user_logged_in',
]);

// Confirm 2FA (verify first code)
register_rest_route($ns, '/auth/2fa/confirm', [
    'methods' => 'POST',
    'callback' => [$this, 'confirm_2fa'],
    'permission_callback' => 'is_user_logged_in',
]);

// Verify 2FA code at login
register_rest_route($ns, '/auth/2fa/verify', [
    'methods' => 'POST',
    'callback' => [$this, 'verify_2fa'],
    'permission_callback' => '__return_true',
]);

// Disable 2FA
register_rest_route($ns, '/auth/2fa/disable', [
    'methods' => 'POST',
    'callback' => [$this, 'disable_2fa'],
    'permission_callback' => 'is_user_logged_in',
]);

// Regenerate backup codes
register_rest_route($ns, '/auth/2fa/backup-codes/regenerate', [
    'methods' => 'POST',
    'callback' => [$this, 'regenerate_backup_codes'],
    'permission_callback' => 'is_user_logged_in',
]);

// Verify backup code
register_rest_route($ns, '/auth/2fa/backup-code/verify', [
    'methods' => 'POST',
    'callback' => [$this, 'verify_backup_code'],
    'permission_callback' => '__return_true',
]);
```

**Implementare metode:**
```php
public function enable_2fa(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $user = get_user_by('id', $user_id);
    
    $totp = MBS_TOTP::get_instance();
    $secret = $totp->generate_secret();
    
    // Save temporary secret (not confirmed yet)
    update_user_meta($user_id, 'mbs_2fa_secret_temp', $secret);
    
    $qr_url = $totp->get_qr_code_url($user->user_email, $secret);
    
    return rest_ensure_response([
        'success' => true,
        'qr_code_url' => $qr_url,
        'secret' => $secret,
        'manual_entry_key' => chunk_split($secret, 4, ' ') // Ex: "JBSW Y3DP EHPK 3PXP"
    ]);
}

public function confirm_2fa(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $data = $request->get_json_params();
    $code = sanitize_text_field($data['code']);
    
    $secret = get_user_meta($user_id, 'mbs_2fa_secret_temp', true);
    
    if (empty($secret)) {
        return new WP_Error('no_secret', 'No 2FA setup in progress');
    }
    
    $totp = MBS_TOTP::get_instance();
    
    if (!$totp->verify_code($secret, $code)) {
        return new WP_Error('invalid_code', 'Cod invalid');
    }
    
    // Cod valid, activăm 2FA
    update_user_meta($user_id, 'mbs_2fa_enabled', '1');
    update_user_meta($user_id, 'mbs_2fa_secret', $secret);
    delete_user_meta($user_id, 'mbs_2fa_secret_temp');
    
    // Generăm backup codes
    $backup_codes = $totp->generate_backup_codes(10);
    update_user_meta($user_id, 'mbs_2fa_backup_codes', json_encode($backup_codes));
    
    // TODO: Send email notification
    
    return rest_ensure_response([
        'success' => true,
        'message' => '2FA activat cu succes',
        'backup_codes' => array_column($backup_codes, 'code')
    ]);
}

public function verify_2fa(WP_REST_Request $request) {
    $data = $request->get_json_params();
    $user_id = (int) $data['user_id'];
    $code = sanitize_text_field($data['code']);
    
    $secret = get_user_meta($user_id, 'mbs_2fa_secret', true);
    
    if (empty($secret)) {
        return new WP_Error('no_2fa', '2FA not enabled');
    }
    
    $totp = MBS_TOTP::get_instance();
    
    if ($totp->verify_code($secret, $code)) {
        // Cod valid, setăm auth cookie
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
        
        return rest_ensure_response(['success' => true]);
    }
    
    return new WP_Error('invalid_code', 'Cod invalid');
}

public function disable_2fa(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $data = $request->get_json_params();
    $password = $data['password'];
    
    // Verificăm parola pentru securitate
    $user = get_user_by('id', $user_id);
    if (!wp_check_password($password, $user->data->user_pass, $user_id)) {
        return new WP_Error('invalid_password', 'Parolă incorectă');
    }
    
    // Dezactivăm 2FA
    delete_user_meta($user_id, 'mbs_2fa_enabled');
    delete_user_meta($user_id, 'mbs_2fa_secret');
    delete_user_meta($user_id, 'mbs_2fa_backup_codes');
    
    // TODO: Send email notification
    
    return rest_ensure_response([
        'success' => true,
        'message' => '2FA dezactivat'
    ]);
}
```

#### **Database Storage (wp_usermeta):**
```
mbs_2fa_enabled: "1" sau "0"
mbs_2fa_secret: "JBSWY3DPEHPK3PXP"
mbs_2fa_secret_temp: "..." (temporar, până la confirmare)
mbs_2fa_backup_codes: JSON array
mbs_2fa_failed_attempts: counter
mbs_2fa_lockout_until: timestamp
```

---

### 2. Frontend (React)

#### **Fișiere noi:**

**`assets/js/components/TwoFactorSetup.js`**
```jsx
const TwoFactorSetup = () => {
  const [step, setStep] = useState(1); // 1=intro, 2=qr, 3=confirm, 4=backup
  const [qrCodeUrl, setQrCodeUrl] = useState('');
  const [secret, setSecret] = useState('');
  const [verifyCode, setVerifyCode] = useState('');
  const [backupCodes, setBackupCodes] = useState([]);
  const [loading, setLoading] = useState(false);

  const startSetup = async () => {
    const response = await fetch(mbs_ajax.rest_base + '/auth/2fa/enable', {
      method: 'POST',
      headers: { 'X-WP-Nonce': mbs_ajax.rest_nonce }
    });
    const data = await response.json();
    setQrCodeUrl(data.qr_code_url);
    setSecret(data.manual_entry_key);
    setStep(2);
  };

  const confirmSetup = async () => {
    const response = await fetch(mbs_ajax.rest_base + '/auth/2fa/confirm', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-WP-Nonce': mbs_ajax.rest_nonce 
      },
      body: JSON.stringify({ code: verifyCode })
    });
    
    if (response.ok) {
      const data = await response.json();
      setBackupCodes(data.backup_codes);
      setStep(4);
    }
  };

  return (
    <div>
      {step === 1 && <IntroStep onStart={startSetup} />}
      {step === 2 && <QRStep qrUrl={qrCodeUrl} secret={secret} onNext={() => setStep(3)} />}
      {step === 3 && <VerifyStep code={verifyCode} onChange={setVerifyCode} onConfirm={confirmSetup} />}
      {step === 4 && <BackupCodesStep codes={backupCodes} />}
    </div>
  );
};
```

**`assets/js/components/TwoFactorLogin.js`**
```jsx
const TwoFactorLogin = ({ userId, onSuccess }) => {
  const [code, setCode] = useState('');
  const [useBackupCode, setUseBackupCode] = useState(false);
  const [error, setError] = useState('');

  const verifyCode = async () => {
    const endpoint = useBackupCode ? '/auth/2fa/backup-code/verify' : '/auth/2fa/verify';
    
    const response = await fetch(mbs_ajax.rest_base + endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId, code })
    });

    if (response.ok) {
      onSuccess();
    } else {
      setError('Cod invalid');
    }
  };

  return (
    <div className="modal">
      <h3>🔐 Autentificare în 2 Pași</h3>
      
      {!useBackupCode ? (
        <>
          <p>Introdu codul din Google Authenticator:</p>
          <OTPInput length={6} value={code} onChange={setCode} />
          <button onClick={verifyCode}>Verifică</button>
          <a onClick={() => setUseBackupCode(true)}>Nu ai acces? Folosește backup code</a>
        </>
      ) : (
        <>
          <p>Introdu backup code (8 cifre):</p>
          <OTPInput length={8} value={code} onChange={setCode} />
          <button onClick={verifyCode}>Verifică</button>
          <a onClick={() => setUseBackupCode(false)}>Înapoi la cod normal</a>
        </>
      )}
      
      {error && <p className="error">{error}</p>}
    </div>
  );
};
```

**`assets/js/components/OTPInput.js`** - Reusable component
```jsx
const OTPInput = ({ length, value, onChange }) => {
  const inputs = useRef([]);

  const handleChange = (index, digit) => {
    const newValue = value.split('');
    newValue[index] = digit;
    onChange(newValue.join(''));

    // Auto-focus next input
    if (digit && index < length - 1) {
      inputs.current[index + 1].focus();
    }
  };

  const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !value[index] && index > 0) {
      inputs.current[index - 1].focus();
    }
  };

  return (
    <div className="otp-input-container">
      {Array.from({ length }).map((_, index) => (
        <input
          key={index}
          ref={el => inputs.current[index] = el}
          type="text"
          maxLength="1"
          value={value[index] || ''}
          onChange={(e) => handleChange(index, e.target.value)}
          onKeyDown={(e) => handleKeyDown(index, e)}
          className="otp-digit"
        />
      ))}
    </div>
  );
};
```

#### **Modificări în auth-component.js:**

```javascript
// În handleLogin(), după verificare CNP + password:
const data = await response.json();

if (data.requires_2fa) {
  // Show 2FA modal
  setShow2FAModal(true);
  set2FAUserId(data.user_id);
} else {
  // Login success
  setCurrentUser(data.user);
}
```

**`assets/css/2fa.css`** - Styling
```css
.otp-input-container {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.otp-digit {
  width: 48px;
  height: 56px;
  text-align: center;
  font-size: 24px;
  font-weight: bold;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
}

.otp-digit:focus {
  border-color: #3b82f6;
  outline: none;
}

.backup-codes-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin: 20px 0;
}

.backup-code {
  padding: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-family: monospace;
  font-size: 16px;
  text-align: center;
}

.backup-code.used {
  opacity: 0.4;
  text-decoration: line-through;
}
```

---

### 3. User Settings Page

**Nou shortcode:** `[mbs_user_settings]`

**`public/class-user-settings.php`**
```php
class MBS_User_Settings {
    public function shortcode_settings() {
        if (!is_user_logged_in()) {
            return '<p>Trebuie să fii autentificat.</p>';
        }
        
        ob_start();
        ?>
        <div id="mbs-user-settings-root"></div>
        <?php
        
        // Enqueue settings component
        wp_enqueue_script('mbs-user-settings-component', ...);
        
        return ob_get_clean();
    }
}
```

**`assets/js/user-settings-component.js`**
```jsx
const UserSettings = () => {
  const [activeTab, setActiveTab] = useState('security');
  
  return (
    <div className="mbs-settings">
      <nav className="tabs">
        <button onClick={() => setActiveTab('profile')}>Profil</button>
        <button onClick={() => setActiveTab('security')}>Securitate</button>
        <button onClick={() => setActiveTab('phones')}>Telefoane</button>
      </nav>
      
      {activeTab === 'security' && <SecurityTab />}
      {activeTab === 'profile' && <ProfileTab />}
      {activeTab === 'phones' && <PhonesTab />}
    </div>
  );
};

const SecurityTab = () => {
  const [has2FA, setHas2FA] = useState(false);
  const [showSetup, setShowSetup] = useState(false);
  
  useEffect(() => {
    // Check if user has 2FA enabled
    fetch(mbs_ajax.rest_base + '/auth/me', {
      headers: { 'X-WP-Nonce': mbs_ajax.rest_nonce }
    }).then(r => r.json()).then(data => {
      setHas2FA(data.has_2fa);
    });
  }, []);
  
  return (
    <div className="security-settings">
      <h2>🔒 Securitate</h2>
      
      <div className="setting-box">
        <div className="setting-header">
          <div>
            <h3>Autentificare în 2 Pași</h3>
            <p>Protejează-ți contul cu un cod suplimentar</p>
          </div>
          <label className="toggle">
            <input 
              type="checkbox" 
              checked={has2FA}
              onChange={() => setShowSetup(true)}
            />
            <span className="slider"></span>
          </label>
        </div>
        
        {has2FA && (
          <div className="2fa-active">
            <p>✅ 2FA Activat</p>
            <button onClick={handleDisable2FA}>Dezactivează</button>
            <button onClick={viewBackupCodes}>Vezi Backup Codes</button>
          </div>
        )}
      </div>
      
      {showSetup && <TwoFactorSetup onClose={() => setShowSetup(false)} />}
    </div>
  );
};
```

---

## 📱 User Journey

### Activare 2FA

**Pas 1: Intro**
```
┌────────────────────────────────────┐
│  🔐 Activează Autentificarea       │
│      în 2 Pași                     │
├────────────────────────────────────┤
│                                    │
│  Ce este 2FA?                      │
│  • Securitate extra pentru cont   │
│  • Cod unic la fiecare login       │
│  • Protecție împotriva hackerilor  │
│                                    │
│  Ai nevoie de:                     │
│  📱 Google Authenticator (gratuit) │
│                                    │
│  [Începe Activarea]  [Renunță]     │
│                                    │
└────────────────────────────────────┘
```

**Pas 2: Scanare QR**
```
┌────────────────────────────────────┐
│  Scanează QR Code                  │
├────────────────────────────────────┤
│                                    │
│  1. Descarcă aplicația:            │
│     [Google Auth] [MS Auth] [Authy]│
│                                    │
│  2. Deschide app → Scanează        │
│                                    │
│     ┌──────────────┐               │
│     │  ████  ████  │ QR Code       │
│     │  ████  ████  │               │
│     └──────────────┘               │
│                                    │
│  Sau introdu manual:               │
│  JBSW Y3DP EHPK 3PXP              │
│  [📋 Copiază]                      │
│                                    │
│  [Înapoi]  [Am scanat →]           │
│                                    │
└────────────────────────────────────┘
```

**Pas 3: Verificare**
```
┌────────────────────────────────────┐
│  Introdu codul din aplicație       │
├────────────────────────────────────┤
│                                    │
│  App-ul afișează un cod de 6 cifre│
│  care se schimbă la 30 secunde.    │
│                                    │
│  Introdu codul aici:               │
│  ┌───┬───┬───┬───┬───┬───┐        │
│  │ 1 │ 2 │ 3 │ 4 │ 5 │ 6 │        │
│  └───┴───┴───┴───┴───┴───┘        │
│                                    │
│  [Înapoi]  [Verifică]              │
│                                    │
└────────────────────────────────────┘
```

**Pas 4: Backup Codes**
```
┌────────────────────────────────────┐
│  ✅ 2FA Activat cu Succes!         │
├────────────────────────────────────┤
│                                    │
│  ⚠️ IMPORTANT: Salvează aceste     │
│  coduri de siguranță!              │
│                                    │
│  Folosește-le dacă pierzi accesul  │
│  la aplicație. Fiecare cod         │
│  funcționează o singură dată.      │
│                                    │
│  12345678    87654321              │
│  11223344    44332211              │
│  55667788    88776655              │
│  99001122    22110099              │
│  33445566    66554433              │
│                                    │
│  [📋 Copiază Tot]  [📥 Descarcă]   │
│  [🖨️ Printează]    [Am salvat ✓]  │
│                                    │
└────────────────────────────────────┘
```

### Login cu 2FA

```
User → Login (CNP + Password) → Valid
    ↓
Backend verifică: has 2FA?
    ↓
[DA] → Modal: "Introdu codul din Google Authenticator"
    ↓
┌────────────────────────────────────┐
│  🔐 Cod de Autentificare           │
├────────────────────────────────────┤
│  Introdu codul din aplicația ta:   │
│  ┌───┬───┬───┬───┬───┬───┐        │
│  │ _ │ _ │ _ │ _ │ _ │ _ │        │
│  └───┴───┴───┴───┴───┴───┘        │
│                                    │
│  Nu ai acces la aplicație?         │
│  → Folosește backup code           │
│                                    │
│  ☐ Amintește dispozitiv (30 zile)  │
│                                    │
│  [Verifică]                        │
└────────────────────────────────────┘
    ↓
Cod valid → ✅ Login Success
```

---

## 📊 Tracking & Analytics

**Admin Dashboard Widget:**
```php
// admin/dashboard-widget.php
function mbs_2fa_stats_widget() {
    global $wpdb;
    
    $total_users = count(get_users(['role' => 'mbs_patient']));
    $users_with_2fa = count(get_users([
        'role' => 'mbs_patient',
        'meta_key' => 'mbs_2fa_enabled',
        'meta_value' => '1'
    ]));
    
    $percentage = ($total_users > 0) ? round(($users_with_2fa / $total_users) * 100, 1) : 0;
    
    echo "<div class='mbs-2fa-stats'>";
    echo "<h3>🔐 Securitate 2FA</h3>";
    echo "<p><strong>{$users_with_2fa}</strong> / {$total_users} utilizatori ({$percentage}%)</p>";
    echo "<div class='progress-bar'>";
    echo "<div class='progress-fill' style='width: {$percentage}%'></div>";
    echo "</div>";
    echo "</div>";
}
```

---

## ✅ Checklist Implementare

### Faza 1: Setup Inițial (1h)
- [ ] Download library GoogleAuthenticator.php
- [ ] Creare `class-totp.php`
- [ ] Include în `medical-booking-system.php`
- [ ] Test: generare secret, QR code

### Faza 2: Backend API (2h)
- [ ] Endpoint `/auth/2fa/enable`
- [ ] Endpoint `/auth/2fa/confirm`
- [ ] Endpoint `/auth/2fa/verify`
- [ ] Endpoint `/auth/2fa/disable`
- [ ] Endpoint backup codes
- [ ] Test cu Postman/cURL

### Faza 3: Frontend Setup (2h)
- [ ] Component `TwoFactorSetup.js`
- [ ] Component `OTPInput.js`
- [ ] QR Code display
- [ ] Backup codes display
- [ ] CSS styling
- [ ] Test flow complet

### Faza 4: Login Flow (1h)
- [ ] Component `TwoFactorLogin.js`
- [ ] Modificare `auth-component.js`
- [ ] Modal 2FA
- [ ] Backup code input
- [ ] Test login cu 2FA

### Faza 5: User Settings (1h)
- [ ] Shortcode `[mbs_user_settings]`
- [ ] Security tab
- [ ] Toggle 2FA ON/OFF
- [ ] View backup codes
- [ ] Regenerate backup codes

### Faza 6: Polish & Security (1h)
- [ ] Rate limiting (max 5 failed attempts)
- [ ] Lockout temporar (5 min)
- [ ] Email notifications
- [ ] Logging
- [ ] Testing final

---

## 🎓 Training Materials

**Pentru utilizatori - Text explicativ:**
```
🔐 Ce este Autentificarea în 2 Pași?

Este un strat suplimentar de securitate pentru contul tău.
Când te conectezi, pe lângă parolă, vei introduce și un
cod unic de 6 cifre generat de o aplicație specială pe
telefonul tău.

✅ Avantaje:
• Contul tău este mult mai sigur
• Chiar dacă cineva află parola ta, nu poate intra
• Codul se schimbă la fiecare 30 secunde

📱 Ai nevoie de:
• Google Authenticator (gratuit, iOS/Android)
• SAU Microsoft Authenticator
• SAU Authy

⏱️ Activarea durează 2 minute.

🆘 Ce fac dacă pierd telefonul?
Vei primi 10 "backup codes" pe care trebuie să le salvezi
într-un loc sigur (pe hârtie sau într-un fișier securizat).
```

---

## 📝 Notes

1. **Cost:** $0 - 100% gratuit
2. **Dependency:** 1 fișier PHP (~10KB)
3. **User apps:** Google Auth, MS Auth, Authy (toate gratuite)
4. **Security:** Mai sigur decât SMS OTP
5. **Offline:** Funcționează fără internet
6. **Standard:** Același sistem folosit de Google, GitHub, Facebook

---

**Status:** 📋 READY FOR IMPLEMENTATION  
**Priority:** MEDIE (după booking flow funcțional)  
**Timp estimat:** 6-8 ore  

*Ultima actualizare: 20 octombrie 2025*

