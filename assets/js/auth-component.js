// Medical Booking System - Auth Component
const { useState, useEffect } = React;

// CNP Validation Function
const validateCNP = (cnp) => {
  // Remove spaces and dashes
  cnp = cnp.replace(/[\s\-]/g, '');
  
  // Must be 13 digits
  if (!/^\d{13}$/.test(cnp)) {
    return false;
  }
  
  // Control algorithm
  const controlString = '279146358279';
  let sum = 0;
  
  for (let i = 0; i < 12; i++) {
    sum += parseInt(cnp[i]) * parseInt(controlString[i]);
  }
  
  let controlDigit = sum % 11;
  if (controlDigit === 10) {
    controlDigit = 1;
  }
  
  return parseInt(cnp[12]) === controlDigit;
};

// Phone Validation Function
const validatePhone = (phone) => {
  phone = phone.replace(/[\s\-\(\)]/g, '');
  return /^(07\d{8}|(\+4|04)07\d{8})$/.test(phone);
};

// Icons
const UserIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
  </svg>
);

const LockIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
  </svg>
);

const MailIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
  </svg>
);

const PhoneIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
  </svg>
);

const AlertIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
  </svg>
);

const CheckIcon = () => (
  <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
  </svg>
);

const AuthComponent = () => {
  const [mode, setMode] = useState('login'); // 'login' or 'register'
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);
  const [currentUser, setCurrentUser] = useState(null);

  // Login form
  const [loginData, setLoginData] = useState({
    identifier: '',
    password: '',
    remember: false,
  });

  // Register form
  const [registerData, setRegisterData] = useState({
    cnp: '',
    email: '',
    password: '',
    confirmPassword: '',
    first_name: '',
    last_name: '',
    phone: '',
  });

  // Form validation errors
  const [validationErrors, setValidationErrors] = useState({});

  // Check if user is already logged in
  useEffect(() => {
    checkCurrentUser();
  }, []);

  const checkCurrentUser = async () => {
    try {
      const response = await fetch(mbs_ajax.rest_base + '/auth/me', {
        method: 'GET',
        headers: {
          'X-WP-Nonce': mbs_ajax.rest_nonce,
        },
        credentials: 'include',
      });

      if (response.ok) {
        const data = await response.json();
        setCurrentUser(data);
      }
    } catch (err) {
      // User not logged in, which is fine
    }
  };

  const validateLoginForm = () => {
    const errors = {};

    if (!loginData.identifier) {
      errors.identifier = 'Vă rugăm introduceți CNP, email sau telefon';
    }

    if (!loginData.password) {
      errors.password = 'Vă rugăm introduceți parola';
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const validateRegisterForm = () => {
    const errors = {};

    // CNP validation
    if (!registerData.cnp) {
      errors.cnp = 'CNP este obligatoriu';
    } else if (!validateCNP(registerData.cnp)) {
      errors.cnp = 'CNP invalid (trebuie să fie 13 cifre valide)';
    }

    // Email validation
    if (!registerData.email) {
      errors.email = 'Email este obligatoriu';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(registerData.email)) {
      errors.email = 'Adresă email invalidă';
    }

    // Password validation
    if (!registerData.password) {
      errors.password = 'Parola este obligatorie';
    } else if (registerData.password.length < 8) {
      errors.password = 'Parola trebuie să aibă minim 8 caractere';
    }

    if (!registerData.confirmPassword) {
      errors.confirmPassword = 'Confirmați parola';
    } else if (registerData.password !== registerData.confirmPassword) {
      errors.confirmPassword = 'Parolele nu coincid';
    }

    // Name validation
    if (!registerData.first_name) {
      errors.first_name = 'Prenume este obligatoriu';
    }

    if (!registerData.last_name) {
      errors.last_name = 'Nume este obligatoriu';
    }

    // Phone validation (optional, but if provided must be valid)
    if (registerData.phone && !validatePhone(registerData.phone)) {
      errors.phone = 'Format telefon invalid (ex: 0712345678)';
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleLogin = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);

    if (!validateLoginForm()) {
      return;
    }

    setLoading(true);

    try {
      const response = await fetch(mbs_ajax.rest_base + '/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': mbs_ajax.rest_nonce,
        },
        credentials: 'include',
        body: JSON.stringify(loginData),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Eroare la autentificare');
      }

      setSuccess(data.message || 'Autentificare reușită!');
      setCurrentUser(data.user);
      
      // Redirect after 1 second
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleRegister = async (e) => {
    e.preventDefault();
    setError(null);
    setSuccess(null);

    if (!validateRegisterForm()) {
      return;
    }

    setLoading(true);

    try {
      const response = await fetch(mbs_ajax.rest_base + '/auth/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': mbs_ajax.rest_nonce,
        },
        credentials: 'include',
        body: JSON.stringify(registerData),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Eroare la înregistrare');
      }

      setSuccess(data.message || 'Înregistrare reușită!');
      setCurrentUser(data.user);
      
      // Redirect after 1 second
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    // WordPress logout - redirect to wp-login.php?action=logout
    window.location.href = mbs_ajax.ajax_url.replace('admin-ajax.php', '../wp-login.php?action=logout');
  };

  // If user is logged in, show user info
  if (currentUser) {
    return (
      <div className="max-w-md mx-auto p-6">
        <div className="bg-white rounded-lg shadow-lg p-6">
          <div className="flex items-center space-x-4 mb-6">
            <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
              <UserIcon />
            </div>
            <div>
              <h2 className="text-xl font-bold text-gray-900">{currentUser.display_name}</h2>
              <p className="text-sm text-gray-600">{currentUser.email}</p>
            </div>
          </div>

          <div className="space-y-3 mb-6">
            <div className="flex items-center text-sm">
              <span className="font-medium text-gray-700 w-20">CNP:</span>
              <span className="text-gray-900">{currentUser.cnp_masked || currentUser.cnp}</span>
            </div>
            {currentUser.phones && currentUser.phones.length > 0 && (
              <div className="flex items-center text-sm">
                <span className="font-medium text-gray-700 w-20">Telefon:</span>
                <span className="text-gray-900">{currentUser.phones[0].phone}</span>
              </div>
            )}
          </div>

          <button
            onClick={handleLogout}
            className="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition-colors"
          >
            Deconectare
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-md mx-auto p-6">
      <div className="bg-white rounded-lg shadow-lg p-8">
        {/* Header with mode toggle */}
        <div className="flex border-b border-gray-200 mb-6">
          <button
            className={`flex-1 py-3 text-center font-medium transition-colors ${
              mode === 'login'
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-500 hover:text-gray-700'
            }`}
            onClick={() => {
              setMode('login');
              setError(null);
              setSuccess(null);
              setValidationErrors({});
            }}
          >
            Autentificare
          </button>
          <button
            className={`flex-1 py-3 text-center font-medium transition-colors ${
              mode === 'register'
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-500 hover:text-gray-700'
            }`}
            onClick={() => {
              setMode('register');
              setError(null);
              setSuccess(null);
              setValidationErrors({});
            }}
          >
            Înregistrare
          </button>
        </div>

        {/* Error/Success messages */}
        {error && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-start space-x-2">
            <AlertIcon />
            <span className="text-red-800 text-sm">{error}</span>
          </div>
        )}

        {success && (
          <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-start space-x-2">
            <CheckIcon />
            <span className="text-green-800 text-sm">{success}</span>
          </div>
        )}

        {/* Login Form */}
        {mode === 'login' && (
          <form onSubmit={handleLogin} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                CNP, Email sau Telefon
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <UserIcon />
                </div>
                <input
                  type="text"
                  value={loginData.identifier}
                  onChange={(e) => setLoginData({ ...loginData, identifier: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.identifier ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="1234567890123 sau email@example.com"
                />
              </div>
              {validationErrors.identifier && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.identifier}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Parolă</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <LockIcon />
                </div>
                <input
                  type="password"
                  value={loginData.password}
                  onChange={(e) => setLoginData({ ...loginData, password: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.password ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="••••••••"
                />
              </div>
              {validationErrors.password && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.password}</p>
              )}
            </div>

            <div className="flex items-center">
              <input
                type="checkbox"
                id="remember"
                checked={loginData.remember}
                onChange={(e) => setLoginData({ ...loginData, remember: e.target.checked })}
                className="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label htmlFor="remember" className="ml-2 text-sm text-gray-700">
                Ține-mă minte
              </label>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {loading ? 'Se autentifică...' : 'Autentificare'}
            </button>
          </form>
        )}

        {/* Register Form */}
        {mode === 'register' && (
          <form onSubmit={handleRegister} className="space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Prenume *</label>
                <input
                  type="text"
                  value={registerData.first_name}
                  onChange={(e) => setRegisterData({ ...registerData, first_name: e.target.value })}
                  className={`w-full px-4 py-2 border ${
                    validationErrors.first_name ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="Ion"
                />
                {validationErrors.first_name && (
                  <p className="mt-1 text-xs text-red-600">{validationErrors.first_name}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Nume *</label>
                <input
                  type="text"
                  value={registerData.last_name}
                  onChange={(e) => setRegisterData({ ...registerData, last_name: e.target.value })}
                  className={`w-full px-4 py-2 border ${
                    validationErrors.last_name ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="Popescu"
                />
                {validationErrors.last_name && (
                  <p className="mt-1 text-xs text-red-600">{validationErrors.last_name}</p>
                )}
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">CNP *</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <UserIcon />
                </div>
                <input
                  type="text"
                  value={registerData.cnp}
                  onChange={(e) => setRegisterData({ ...registerData, cnp: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.cnp ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="1234567890123"
                  maxLength="13"
                />
              </div>
              {validationErrors.cnp && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.cnp}</p>
              )}
              <p className="mt-1 text-xs text-gray-500">CNP românesc (13 cifre)</p>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Email *</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <MailIcon />
                </div>
                <input
                  type="email"
                  value={registerData.email}
                  onChange={(e) => setRegisterData({ ...registerData, email: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.email ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="email@example.com"
                />
              </div>
              {validationErrors.email && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.email}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Telefon (opțional)</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <PhoneIcon />
                </div>
                <input
                  type="tel"
                  value={registerData.phone}
                  onChange={(e) => setRegisterData({ ...registerData, phone: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.phone ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="0712345678"
                />
              </div>
              {validationErrors.phone && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.phone}</p>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Parolă *</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <LockIcon />
                </div>
                <input
                  type="password"
                  value={registerData.password}
                  onChange={(e) => setRegisterData({ ...registerData, password: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.password ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="••••••••"
                />
              </div>
              {validationErrors.password && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.password}</p>
              )}
              <p className="mt-1 text-xs text-gray-500">Minim 8 caractere</p>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Confirmă Parola *</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                  <LockIcon />
                </div>
                <input
                  type="password"
                  value={registerData.confirmPassword}
                  onChange={(e) => setRegisterData({ ...registerData, confirmPassword: e.target.value })}
                  className={`w-full pl-10 pr-4 py-2 border ${
                    validationErrors.confirmPassword ? 'border-red-500' : 'border-gray-300'
                  } rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent`}
                  placeholder="••••••••"
                />
              </div>
              {validationErrors.confirmPassword && (
                <p className="mt-1 text-xs text-red-600">{validationErrors.confirmPassword}</p>
              )}
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {loading ? 'Se înregistrează...' : 'Înregistrare'}
            </button>
          </form>
        )}
      </div>
    </div>
  );
};

// Render the component
const authRoot = document.getElementById('medical-auth-root');
if (authRoot) {
  ReactDOM.createRoot(authRoot).render(<AuthComponent />);
}

