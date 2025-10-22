// Medical Booking System - Patient Form JavaScript
jQuery(document).ready(function($) {
	
	// Phone management functions
	window.addPhone = function() {
		const list = document.getElementById("phone-list");
		const count = list.children.length;
		const item = document.createElement("div");
		item.className = "mbs-phone-item";
		item.innerHTML = `
			<input type="text" name="phones[]" class="phone-input" placeholder="07xxxxxxxx"/>
			<div class="mbs-phone-controls">
				<div class="mbs-phone-primary">
					<input type="checkbox" name="phone_primary[]" value="${count}"/>
					<label>Primary</label>
				</div>
				<div class="mbs-phone-remove" onclick="removePhone(this)">×</div>
			</div>
		`;
		list.appendChild(item);
		
		// Add event listener for primary checkbox
		const primaryDiv = item.querySelector('.mbs-phone-primary');
		const checkbox = item.querySelector('input[type="checkbox"]');
		
		primaryDiv.addEventListener('click', function(e) {
			if (e.target !== checkbox) {
				checkbox.checked = !checkbox.checked;
				updatePrimaryState(primaryDiv, checkbox.checked);
			}
		});
		
		checkbox.addEventListener('change', function() {
			updatePrimaryState(primaryDiv, this.checked);
		});
	};
	
	window.removePhone = function(el) {
		el.closest('.mbs-phone-item').remove();
	};
	
	// Update primary phone state
	function updatePrimaryState(primaryDiv, isChecked) {
		if (isChecked) {
			primaryDiv.classList.add('checked');
		} else {
			primaryDiv.classList.remove('checked');
		}
	}
	
	// Romanian Calendar
	class RomanianCalendar {
		constructor(inputId) {
			this.input = document.getElementById(inputId);
			this.calendar = null;
			this.currentDate = new Date();
			this.selectedDate = null;
			this.months = ["Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie", 
						  "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"];
			this.days = ["Lu", "Ma", "Mi", "Jo", "Vi", "Sa", "Du"];
			this.init();
		}
		
		init() {
			this.input.addEventListener('click', () => this.show());
			document.addEventListener('click', (e) => {
				if (!this.input.contains(e.target) && (!this.calendar || !this.calendar.contains(e.target))) {
					this.hide();
				}
			});
		}
		
		show() {
			this.hide();
			this.calendar = this.createCalendar();
			this.input.parentNode.style.position = 'relative';
			this.input.parentNode.appendChild(this.calendar);
		}
		
		hide() {
			if (this.calendar) {
				this.calendar.remove();
				this.calendar = null;
			}
		}
		
		createCalendar() {
			const calendar = document.createElement('div');
			calendar.className = 'mbs-calendar';
			
			// Header
			const header = document.createElement('div');
			header.className = 'mbs-calendar-header';
			
			const prevBtn = document.createElement('button');
			prevBtn.className = 'mbs-calendar-nav';
			prevBtn.innerHTML = '‹';
			prevBtn.addEventListener('click', () => this.navigateMonth(-1));
			
			const title = document.createElement('div');
			title.className = 'mbs-calendar-title';
			title.textContent = this.months[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
			
			const nextBtn = document.createElement('button');
			nextBtn.className = 'mbs-calendar-nav';
			nextBtn.innerHTML = '›';
			nextBtn.addEventListener('click', () => this.navigateMonth(1));
			
			header.appendChild(prevBtn);
			header.appendChild(title);
			header.appendChild(nextBtn);
			
			// Days header
			const daysHeader = document.createElement('div');
			daysHeader.className = 'mbs-calendar-grid';
			this.days.forEach(day => {
				const dayEl = document.createElement('div');
				dayEl.className = 'mbs-calendar-day-header';
				dayEl.textContent = day;
				daysHeader.appendChild(dayEl);
			});
			
			// Days grid
			const daysGrid = document.createElement('div');
			daysGrid.className = 'mbs-calendar-grid';
			this.renderDays(daysGrid);
			
			calendar.appendChild(header);
			calendar.appendChild(daysHeader);
			calendar.appendChild(daysGrid);
			
			return calendar;
		}
		
		navigateMonth(direction) {
			this.currentDate.setMonth(this.currentDate.getMonth() + direction);
			this.updateCalendar();
		}
		
		updateCalendar() {
			const title = this.calendar.querySelector('.mbs-calendar-title');
			title.textContent = this.months[this.currentDate.getMonth()] + ' ' + this.currentDate.getFullYear();
			
			const daysGrid = this.calendar.querySelector('.mbs-calendar-grid:last-child');
			daysGrid.innerHTML = '';
			this.renderDays(daysGrid);
		}
		
		renderDays(container) {
			const year = this.currentDate.getFullYear();
			const month = this.currentDate.getMonth();
			const today = new Date();
			const firstDay = new Date(year, month, 1);
			const lastDay = new Date(year, month + 1, 0);
			const startDate = new Date(firstDay);
			startDate.setDate(startDate.getDate() - firstDay.getDay() + 1); // Start from Monday
			
			for (let i = 0; i < 42; i++) {
				const date = new Date(startDate);
				date.setDate(startDate.getDate() + i);
				
				const dayEl = document.createElement('div');
				dayEl.className = 'mbs-calendar-day';
				dayEl.textContent = date.getDate();
				
				// Other month
				if (date.getMonth() !== month) {
					dayEl.classList.add('other-month');
				}
				
				// Today
				if (date.toDateString() === today.toDateString()) {
					dayEl.classList.add('today');
				}
				
				// Future dates
				if (date > today) {
					dayEl.classList.add('disabled');
				} else {
					dayEl.addEventListener('click', () => this.selectDate(date));
				}
				
				container.appendChild(dayEl);
			}
		}
		
		selectDate(date) {
			this.selectedDate = date;
			const romanianDate = this.formatRomanianDate(date);
			this.input.value = romanianDate;
			this.hide();
		}
		
		formatRomanianDate(date) {
			const day = String(date.getDate()).padStart(2, '0');
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const year = date.getFullYear();
			return `${day}.${month}.${year}`;
		}
	}
	
	// CNP validation and auto-fill functions - CNAS Compatible
	// Validates CNP following CNAS logic (accepts CNPs processed by official CNAS platform)
	function validateCNP(cnp) {
		// Basic format validation
		if (!/^\d{13}$/.test(cnp)) {
			return { valid: false, message: "CNP must be exactly 13 digits" };
		}
		
		// Romanian CNP algorithm validation (official algorithm)
		const weights = [2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9];
		let sum = 0;
		for (let i = 0; i < 12; i++) {
			sum += parseInt(cnp[i]) * weights[i];
		}
		const remainder = sum % 11;
		const checkDigit = remainder < 10 ? remainder : 1;
		
		if (parseInt(cnp[12]) !== checkDigit) {
			return { valid: false, message: "Invalid CNP (checksum failed)" };
		}
		
		// Note: This validation follows CNAS logic and does NOT validate county codes
		// CNAS may use updated county codes or special cases not in public documentation
		return { valid: true, message: "✓ Valid CNP" };
	}
	
	// Extract data from CNP
	function extractDataFromCNP(cnp) {
		if (cnp.length !== 13) return null;
		
		// Gender (first digit)
		const genderDigit = parseInt(cnp[0]);
		const gender = (genderDigit === 1 || genderDigit === 3 || genderDigit === 5 || genderDigit === 7) ? 'M' : 'F';
		
		// Birth date (digits 2-7: YYMMDD)
		const year = parseInt(cnp.substring(1, 3));
		const month = parseInt(cnp.substring(3, 5));
		const day = parseInt(cnp.substring(5, 7));
		
		// Determine century based on gender digit
		let fullYear;
		if (genderDigit === 1 || genderDigit === 2) {
			fullYear = 1900 + year;
		} else if (genderDigit === 3 || genderDigit === 4) {
			fullYear = 1800 + year;
		} else if (genderDigit === 5 || genderDigit === 6) {
			fullYear = 2000 + year;
		} else if (genderDigit === 7 || genderDigit === 8) {
			fullYear = 2000 + year;
		} else {
			fullYear = 2000 + year; // Default for 9
		}
		
		// Format date as DD.MM.YYYY
		const formattedDate = `${day.toString().padStart(2, '0')}.${month.toString().padStart(2, '0')}.${fullYear}`;
		
		// Calculate age from birth date
		const today = new Date();
		const birthDateObj = new Date(fullYear, month - 1, day);
		let calculatedAge = today.getFullYear() - birthDateObj.getFullYear();
		
		// Check if birthday hasn't occurred this year yet
		const monthDiff = today.getMonth() - birthDateObj.getMonth();
		const dayDiff = today.getDate() - birthDateObj.getDate();
		
		if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
			calculatedAge--;
		}
		
		// For children under 1 year, show age in months
		let ageDisplay = calculatedAge;
		if (calculatedAge === 0) {
			// Calculate total months
			const totalMonths = (today.getFullYear() - birthDateObj.getFullYear()) * 12 + (today.getMonth() - birthDateObj.getMonth());
			if (today.getDate() < birthDateObj.getDate()) {
				ageDisplay = `${totalMonths - 1} luni`;
			} else {
				ageDisplay = `${totalMonths} luni`;
			}
		} else {
			ageDisplay = `${calculatedAge} ani`;
		}
		
		// Ensure age is not negative
		if (calculatedAge < 0) {
			calculatedAge = 0;
			ageDisplay = '0 luni';
		}
		
		// Password from last 7 digits
		const password = cnp.substring(6, 13);
		
		// Generate email from CNP (same logic as PHP backend)
		const cnpHash = md5(cnp).substring(0, 8);
		const generatedEmail = `patient${cnpHash}@noemail.local`;
		
		return {
			gender: gender,
			birthDate: formattedDate,
			age: calculatedAge,
			ageDisplay: ageDisplay,
			password: password,
			email: generatedEmail
		};
	}
	
	// Simple MD5 implementation for JavaScript
	function md5(string) {
		function md5cycle(x, k) {
			var a = x[0], b = x[1], c = x[2], d = x[3];
			a = ff(a, b, c, d, k[0], 7, -680876936);
			d = ff(d, a, b, c, k[1], 12, -389564586);
			c = ff(c, d, a, b, k[2], 17, 606105819);
			b = ff(b, c, d, a, k[3], 22, -1044525330);
			a = ff(a, b, c, d, k[4], 7, -176418897);
			d = ff(d, a, b, c, k[5], 12, 1200080426);
			c = ff(c, d, a, b, k[6], 17, -1473231341);
			b = ff(b, c, d, a, k[7], 22, -45705983);
			a = ff(a, b, c, d, k[8], 7, 1770035416);
			d = ff(d, a, b, c, k[9], 12, -1958414417);
			c = ff(c, d, a, b, k[10], 17, -42063);
			b = ff(b, c, d, a, k[11], 22, -1990404162);
			a = ff(a, b, c, d, k[12], 7, 1804603682);
			d = ff(d, a, b, c, k[13], 12, -40341101);
			c = ff(c, d, a, b, k[14], 17, -1502002290);
			b = ff(b, c, d, a, k[15], 22, 1236535329);
			a = gg(a, b, c, d, k[1], 5, -165796510);
			d = gg(d, a, b, c, k[6], 9, -1069501632);
			c = gg(c, d, a, b, k[11], 14, 643717713);
			b = gg(b, c, d, a, k[0], 20, -373897302);
			a = gg(a, b, c, d, k[5], 5, -701558691);
			d = gg(d, a, b, c, k[10], 9, 38016083);
			c = gg(c, d, a, b, k[15], 14, -660478335);
			b = gg(b, c, d, a, k[4], 20, -405537848);
			a = gg(a, b, c, d, k[9], 5, 568446438);
			d = gg(d, a, b, c, k[14], 9, -1019803690);
			c = gg(c, d, a, b, k[3], 14, -187363961);
			b = gg(b, c, d, a, k[8], 20, 1163531501);
			a = gg(a, b, c, d, k[13], 5, -1444681467);
			d = gg(d, a, b, c, k[2], 9, -51403784);
			c = gg(c, d, a, b, k[7], 14, 1735328473);
			b = gg(b, c, d, a, k[12], 20, -1926607734);
			a = hh(a, b, c, d, k[5], 4, -378558);
			d = hh(d, a, b, c, k[8], 11, -2022574463);
			c = hh(c, d, a, b, k[11], 16, 1839030562);
			b = hh(b, c, d, a, k[14], 23, -35309556);
			a = hh(a, b, c, d, k[1], 4, -1530992060);
			d = hh(d, a, b, c, k[4], 11, 1272893353);
			c = hh(c, d, a, b, k[7], 16, -155497632);
			b = hh(b, c, d, a, k[10], 23, -1094730640);
			a = hh(a, b, c, d, k[13], 4, 681279174);
			d = hh(d, a, b, c, k[0], 11, -358537222);
			c = hh(c, d, a, b, k[3], 16, -722521979);
			b = hh(b, c, d, a, k[6], 23, 76029189);
			a = hh(a, b, c, d, k[9], 4, -640364487);
			d = hh(d, a, b, c, k[12], 11, -421815835);
			c = hh(c, d, a, b, k[15], 16, 530742520);
			b = hh(b, c, d, a, k[2], 23, -995338651);
			a = ii(a, b, c, d, k[0], 6, -198630844);
			d = ii(d, a, b, c, k[7], 10, 1126891415);
			c = ii(c, d, a, b, k[14], 15, -1416354905);
			b = ii(b, c, d, a, k[5], 21, -57434055);
			a = ii(a, b, c, d, k[12], 6, 1700485571);
			d = ii(d, a, b, c, k[3], 10, -1894986606);
			c = ii(c, d, a, b, k[10], 15, -1051523);
			b = ii(b, c, d, a, k[1], 21, -2054922799);
			a = ii(a, b, c, d, k[8], 6, 1873313359);
			d = ii(d, a, b, c, k[15], 10, -30611744);
			c = ii(c, d, a, b, k[6], 15, -1560198380);
			b = ii(b, c, d, a, k[13], 21, 1309151649);
			a = ii(a, b, c, d, k[4], 6, -145523070);
			d = ii(d, a, b, c, k[11], 10, -1120210379);
			c = ii(c, d, a, b, k[2], 15, 718787259);
			b = ii(b, c, d, a, k[9], 21, -343485551);
			x[0] = add32(a, x[0]);
			x[1] = add32(b, x[1]);
			x[2] = add32(c, x[2]);
			x[3] = add32(d, x[3]);
		}
		function cmn(q, a, b, x, s, t) {
			a = add32(add32(a, q), add32(x, t));
			return add32((a << s) | (a >>> (32 - s)), b);
		}
		function ff(a, b, c, d, x, s, t) {
			return cmn((b & c) | ((~b) & d), a, b, x, s, t);
		}
		function gg(a, b, c, d, x, s, t) {
			return cmn((b & d) | (c & (~d)), a, b, x, s, t);
		}
		function hh(a, b, c, d, x, s, t) {
			return cmn(b ^ c ^ d, a, b, x, s, t);
		}
		function ii(a, b, c, d, x, s, t) {
			return cmn(c ^ (b | (~d)), a, b, x, s, t);
		}
		function md51(s) {
			var n = s.length,
				state = [1732584193, -271733879, -1732584194, 271733878], i;
			for (i = 64; i <= s.length; i += 64) {
				md5cycle(state, md5blk(s.substring(i - 64, i)));
			}
			s = s.substring(i - 64);
			var tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
			for (i = 0; i < s.length; i++)
				tail[i >> 2] |= s.charCodeAt(i) << ((i % 4) << 3);
			tail[i >> 2] |= 0x80 << ((i % 4) << 3);
			if (i > 55) {
				md5cycle(state, tail);
				for (i = 0; i < 16; i++) tail[i] = 0;
			}
			tail[14] = n * 8;
			md5cycle(state, tail);
			return state;
		}
		function md5blk(s) {
			var md5blks = [], i;
			for (i = 0; i < 64; i += 4) {
				md5blks[i >> 2] = s.charCodeAt(i)
					+ (s.charCodeAt(i + 1) << 8)
					+ (s.charCodeAt(i + 2) << 16)
					+ (s.charCodeAt(i + 3) << 24);
			}
			return md5blks;
		}
		var hex_chr = '0123456789abcdef'.split('');
		function rhex(n) {
			var s = '', j = 0;
			for (; j < 4; j++)
				s += hex_chr[(n >> (j * 8 + 4)) & 0x0F]
					+ hex_chr[(n >> (j * 8)) & 0x0F];
			return s;
		}
		function hex(x) {
			for (var i = 0; i < x.length; i++)
				x[i] = rhex(x[i]);
			return x.join('');
		}
		function add32(a, b) {
			return (a + b) & 0xFFFFFFFF;
		}
		return hex(md51(string));
	}
	
	function showCNPValidation(input, result) {
		// Remove existing validation message
		const existing = input.parentNode.querySelector('.mbs-cnp-validation');
		if (existing) existing.remove();
		
		// Add new validation message
		const validation = document.createElement('div');
		validation.className = 'mbs-cnp-validation';
		validation.style.fontSize = '12px';
		validation.style.marginTop = '4px';
		validation.style.fontWeight = '500';
		
		if (result.valid) {
			validation.style.color = '#00a32a';
			validation.textContent = result.message;
			input.style.borderColor = '#00a32a';
		} else {
			validation.style.color = '#d63638';
			validation.textContent = result.message;
			input.style.borderColor = '#d63638';
		}
		
		input.parentNode.appendChild(validation);
	}
	
	// Normalize Romanian/Hungarian names (remove diacritics)
	function normalizeName(name) {
		// Romanian diacritics
		const romanian = {
			'ă': 'a', 'â': 'a', 'î': 'i', 'ș': 's', 'ț': 't',
			'Ă': 'A', 'Â': 'A', 'Î': 'I', 'Ș': 'S', 'Ț': 'T'
		};
		
		// Hungarian diacritics
		const hungarian = {
			'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u', 'ö': 'o', 'ő': 'o', 'ü': 'u', 'ű': 'u',
			'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U', 'Ö': 'O', 'Ő': 'O', 'Ü': 'U', 'Ű': 'U'
		};
		
		// Other common diacritics
		const other = {
			'à': 'a', 'è': 'e', 'ì': 'i', 'ò': 'o', 'ù': 'u',
			'À': 'A', 'È': 'E', 'Ì': 'I', 'Ò': 'O', 'Ù': 'U',
			'ä': 'a', 'ë': 'e', 'ï': 'i', 'ö': 'o', 'ü': 'u',
			'Ä': 'A', 'Ë': 'E', 'Ï': 'I', 'Ö': 'O', 'Ü': 'U',
			'ç': 'c', 'Ç': 'C', 'ñ': 'n', 'Ñ': 'N'
		};
		
		const allDiacritics = {...romanian, ...hungarian, ...other};
		
		return name.replace(/[ăâîșțĂÂÎȘȚáéíóúöőüűÁÉÍÓÚÖŐÜŰàèìòùÀÈÌÒÙäëïöüÄËÏÖÜçÇñÑ]/g, function(match) {
			return allDiacritics[match] || match;
		});
	}
	
	// Initialize calendar and CNP validation
	if (document.getElementById('birth_date')) {
		new RomanianCalendar('birth_date');
	}
	
	// Initialize primary phone functionality for existing phones
	document.querySelectorAll('.mbs-phone-primary').forEach(function(primaryDiv) {
		const checkbox = primaryDiv.querySelector('input[type="checkbox"]');
		
		primaryDiv.addEventListener('click', function(e) {
			if (e.target !== checkbox) {
				checkbox.checked = !checkbox.checked;
				updatePrimaryState(primaryDiv, checkbox.checked);
			}
		});
		
		checkbox.addEventListener('change', function() {
			updatePrimaryState(primaryDiv, this.checked);
		});
		
		// Set initial state
		updatePrimaryState(primaryDiv, checkbox.checked);
	});
	
	// Block/unblock form fields when CNP is duplicate
	function blockFormFields(block) {
		const fieldsToBlock = [
			'password', 'last_name', 'first_name', 'birth_date', 'age', 'gender',
			'email', 'address', 'notes', 'phone_0', 'emergency_contact_name', 'emergency_contact_phone'
		];
		
		fieldsToBlock.forEach(fieldId => {
			const field = document.getElementById(fieldId);
			if (field) {
				if (block) {
					field.disabled = true;
					field.style.backgroundColor = '#f0f0f1';
					field.style.color = '#666';
					field.style.cursor = 'not-allowed';
					field.setAttribute('data-blocked-by-duplicate', 'true');
				} else {
					field.disabled = false;
					field.style.backgroundColor = '#fff';
					field.style.color = '#000';
					field.style.cursor = 'text';
					field.removeAttribute('data-blocked-by-duplicate');
				}
			}
		});
		
		// Also block phone add button and submit button
		const addPhoneBtn = document.querySelector('.mbs-add-phone');
		const submitBtn = document.querySelector('button[type="submit"]');
		
		if (addPhoneBtn) {
			if (block) {
				addPhoneBtn.style.pointerEvents = 'none';
				addPhoneBtn.style.opacity = '0.5';
				addPhoneBtn.style.cursor = 'not-allowed';
			} else {
				addPhoneBtn.style.pointerEvents = 'auto';
				addPhoneBtn.style.opacity = '1';
				addPhoneBtn.style.cursor = 'pointer';
			}
		}
		
		if (submitBtn) {
			if (block) {
				submitBtn.disabled = true;
				submitBtn.style.backgroundColor = '#f0f0f1';
				submitBtn.style.color = '#666';
				submitBtn.style.cursor = 'not-allowed';
				submitBtn.textContent = 'CNP duplicat - Formular blocat';
			} else {
				submitBtn.disabled = false;
				submitBtn.style.backgroundColor = '#2271b1';
				submitBtn.style.color = '#fff';
				submitBtn.style.cursor = 'pointer';
				submitBtn.textContent = 'Create Patient + WP User';
			}
		}
	}

	// Check if CNP already exists (AJAX call)
	function checkCNPDuplicate(cnp) {
		if (cnp.length !== 13) return Promise.resolve({ exists: false });
		
		return fetch(mbs_ajax.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: new URLSearchParams({
				action: 'mbs_check_cnp_duplicate',
				cnp: cnp,
				nonce: mbs_ajax.nonce
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				return data.data;
			}
			return { exists: false };
		})
		.catch(error => {
			console.error('Error checking CNP duplicate:', error);
			return { exists: false };
		});
	}

	// CNP validation and auto-fill
	const cnpInput = document.getElementById('cnp');
	if (cnpInput) {
		function handleCNPChange() {
			const cnp = cnpInput.value.trim();
			if (cnp) {
				const result = validateCNP(cnp);
				showCNPValidation(cnpInput, result);
				
				// Check for duplicates if CNP is valid
				if (result.valid && cnp.length === 13) {
					checkCNPDuplicate(cnp).then(duplicateResult => {
						if (duplicateResult.exists) {
							const duplicateMessage = document.createElement('div');
							duplicateMessage.className = 'mbs-cnp-duplicate';
							duplicateMessage.style.fontSize = '12px';
							duplicateMessage.style.marginTop = '4px';
							duplicateMessage.style.color = '#d63638';
							duplicateMessage.style.fontWeight = '500';
							duplicateMessage.textContent = `⚠️ CNP ${cnp} already exists! ${duplicateResult.patient_name} (ID: ${duplicateResult.patient_id})`;
							
							// Remove existing duplicate message
							const existing = cnpInput.parentNode.querySelector('.mbs-cnp-duplicate');
							if (existing) existing.remove();
							
							cnpInput.parentNode.appendChild(duplicateMessage);
							cnpInput.style.borderColor = '#d63638';
							
							// Block all other form fields
							blockFormFields(true);
						} else {
							// CNP is available, unblock fields
							blockFormFields(false);
						}
					});
				} else {
					// CNP is invalid or empty, unblock fields
					blockFormFields(false);
				}
				
				// Auto-fill fields if CNP is valid
				if (result.valid) {
					const data = extractDataFromCNP(cnp);
					if (data) {
						// Always update password field (user can change it)
						const passwordInput = document.getElementById('password');
						if (passwordInput) {
							passwordInput.value = data.password;
							passwordInput.style.background = '#fff';
							passwordInput.style.color = '#000';
						}
						
						// Always update email field (user can change it)
						const emailInput = document.getElementById('email');
						if (emailInput) {
							emailInput.value = data.email;
							emailInput.style.background = '#fff';
							emailInput.style.color = '#000';
						}
						
						// Always update birth date field (user can change it)
						const birthDateInput = document.getElementById('birth_date');
						if (birthDateInput) {
							birthDateInput.value = data.birthDate;
							birthDateInput.style.background = '#fff';
							birthDateInput.style.color = '#000';
						}
						
						// Always update age field (user can change it)
						const ageInput = document.getElementById('age');
						if (ageInput) {
							ageInput.value = data.ageDisplay;
							ageInput.style.background = '#fff';
							ageInput.style.color = '#000';
						}
						
						// Always update gender field (user can change it)
						const genderSelect = document.getElementById('gender');
						if (genderSelect) {
							genderSelect.value = data.gender;
							genderSelect.style.background = '#fff';
							genderSelect.style.color = '#000';
						}
					}
				}
			} else {
				// Clear validation if empty
				const existing = cnpInput.parentNode.querySelector('.mbs-cnp-validation');
				if (existing) existing.remove();
				const existingDuplicate = cnpInput.parentNode.querySelector('.mbs-cnp-duplicate');
				if (existingDuplicate) existingDuplicate.remove();
				cnpInput.style.borderColor = '#dcdcde';
				
				// Unblock all fields when CNP is empty
				blockFormFields(false);
				
				// Clear auto-filled fields
				const passwordInput = document.getElementById('password');
				if (passwordInput) {
					passwordInput.value = '';
					passwordInput.style.background = '#f0f0f1';
					passwordInput.style.color = '#666';
				}
				
				const emailInput = document.getElementById('email');
				if (emailInput) {
					emailInput.value = '';
					emailInput.style.background = '#f0f0f1';
					emailInput.style.color = '#666';
				}
				
				const birthDateInput = document.getElementById('birth_date');
				if (birthDateInput) {
					birthDateInput.value = '';
					birthDateInput.style.background = '#f0f0f1';
					birthDateInput.style.color = '#666';
				}
				
				const ageInput = document.getElementById('age');
				if (ageInput) {
					ageInput.value = '';
					ageInput.style.background = '#f0f0f1';
					ageInput.style.color = '#666';
				}
				
				const genderSelect = document.getElementById('gender');
				if (genderSelect) {
					genderSelect.value = '';
					genderSelect.style.background = '#f0f0f1';
					genderSelect.style.color = '#666';
				}
			}
		}
		
		// Add event listeners for both input and blur
		cnpInput.addEventListener('input', function() {
			// Only allow digits
			this.value = this.value.replace(/\D/g, '');
			// Limit to 13 digits
			if (this.value.length > 13) {
				this.value = this.value.substring(0, 13);
			}
			// Call the main handler
			handleCNPChange();
		});
		cnpInput.addEventListener('blur', handleCNPChange);
	}
	
	// Name normalization (real-time)
	const firstNameInput = document.getElementById('first_name');
	if (firstNameInput) {
		firstNameInput.addEventListener('input', function() {
			const original = this.value;
			const normalized = normalizeName(original);
			if (original !== normalized) {
				// Update the field with normalized value
				this.value = normalized;
				// Show a subtle hint that normalization happened
				this.style.borderColor = '#00a32a';
				setTimeout(() => {
					this.style.borderColor = '#dcdcde';
				}, 1000);
			}
		});
		
		// Also handle paste events
		firstNameInput.addEventListener('paste', function() {
			setTimeout(() => {
				const original = this.value;
				const normalized = normalizeName(original);
				if (original !== normalized) {
					this.value = normalized;
					this.style.borderColor = '#00a32a';
					setTimeout(() => {
						this.style.borderColor = '#dcdcde';
					}, 1000);
				}
			}, 10);
		});
	}
	
	const lastNameInput = document.getElementById('last_name');
	if (lastNameInput) {
		lastNameInput.addEventListener('input', function() {
			const original = this.value;
			const normalized = normalizeName(original);
			if (original !== normalized) {
				// Update the field with normalized value
				this.value = normalized;
				// Show a subtle hint that normalization happened
				this.style.borderColor = '#00a32a';
				setTimeout(() => {
					this.style.borderColor = '#dcdcde';
				}, 1000);
			}
		});
		
		// Also handle paste events
		lastNameInput.addEventListener('paste', function() {
			setTimeout(() => {
				const original = this.value;
				const normalized = normalizeName(original);
				if (original !== normalized) {
					this.value = normalized;
					this.style.borderColor = '#00a32a';
					setTimeout(() => {
						this.style.borderColor = '#dcdcde';
					}, 1000);
				}
			}, 10);
		});
	}
	
});
