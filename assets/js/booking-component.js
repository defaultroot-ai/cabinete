        const { useState, useMemo, useCallback } = React;
        const { createRoot } = ReactDOM;

        // Simple icon components to replace Lucide icons
        const ChevronRight = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "m9 18 6-6-6-6" })
            );
        
        const ChevronLeft = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "m15 18-6-6 6-6" })
            );
        
        const Check = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "M20 6 9 17l-5-5" })
            );
        
        const Calendar = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('rect', { width: "18", height: "18", x: "3", y: "4", rx: "2", ry: "2" }),
                React.createElement('line', { x1: "16", x2: "16", y1: "2", y2: "6" }),
                React.createElement('line', { x1: "8", x2: "8", y1: "2", y2: "6" }),
                React.createElement('line', { x1: "3", x2: "21", y1: "10", y2: "10" })
            );
        
        const Clock = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('circle', { cx: "12", cy: "12", r: "10" }),
                React.createElement('polyline', { points: "12,6 12,12 16,14" })
            );
        
        const User = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }),
                React.createElement('circle', { cx: "12", cy: "7", r: "4" })
            );
        
        const Users = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" }),
                React.createElement('circle', { cx: "9", cy: "7", r: "4" }),
                React.createElement('path', { d: "m22 21-2-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }),
                React.createElement('circle', { cx: "17", cy: "7", r: "4" })
            );
        
        const FileText = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" }),
                React.createElement('polyline', { points: "14,2 14,8 20,8" }),
                React.createElement('line', { x1: "16", x2: "8", y1: "13", y2: "13" }),
                React.createElement('line', { x1: "16", x2: "8", y1: "17", y2: "17" }),
                React.createElement('polyline', { points: "10,9 9,9 8,9" })
            );
        
        const CheckCircle = ({ size = 24, className = "" }) => 
            React.createElement('svg', { width: size, height: size, className, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: "2" },
                React.createElement('path', { d: "M22 11.08V12a10 10 0 1 1-5.93-9.14" }),
                React.createElement('polyline', { points: "22,4 12,14.01 9,11.01" })
            );

        const BookingFlow = () => {
          const [step, setStep] = useState(1);
          const [timeFilter, setTimeFilter] = useState('all');
          const [loading, setLoading] = useState(false);
          const [error, setError] = useState(null);
          const [bookingData, setBookingData] = useState({
            service: null,
            doctor: null,
            date: null,
            timeSlot: null,
            familyMember: null,
            notes: ''
          });

          // Date mock pentru demo
          const services = [
            { id: 1, name: 'Consultație Generală', duration: 30, description: 'Consultație medicală standard' },
            { id: 2, name: 'Consultație Cardiologie', duration: 45, description: 'Evaluare completă cardiologică' },
            { id: 3, name: 'Analize Medicale', duration: 15, description: 'Recoltare probe pentru analize' },
            { id: 4, name: 'Control Periodic', duration: 20, description: 'Verificare stare generală de sănătate' }
          ];

          const doctors = [
            { id: 1, name: 'Dr. Maria Popescu', specialty: 'Medicină Generală', serviceId: 1, image: '👩‍⚕️' },
            { id: 2, name: 'Dr. Ion Ionescu', specialty: 'Cardiolog', serviceId: 2, image: '👨‍⚕️' },
            { id: 3, name: 'Dr. Ana Gheorghe', specialty: 'Medicină Generală', serviceId: 1, image: '👩‍⚕️' },
            { id: 4, name: 'Dr. Mihai Dumitrescu', specialty: 'Cardiolog', serviceId: 2, image: '👨‍⚕️' }
          ];

          const [currentMonth, setCurrentMonth] = useState(new Date(2025, 9, 17));

          // Generare calendar pentru luna curentă (weekend-ul disabled) - memoized pentru performanță
          const generateCalendar = useCallback(() => {
            const year = currentMonth.getFullYear();
            const month = currentMonth.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const today = new Date(2025, 9, 17);
            
            const calendar = [];
            
            // Padding pentru prima săptămână
            const firstDayOfWeek = firstDay.getDay();
            const paddingDays = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
            
            for (let i = 0; i < paddingDays; i++) {
              calendar.push(null);
            }
            
            // Zilele din lună
            for (let day = 1; day <= lastDay.getDate(); day++) {
              const date = new Date(year, month, day);
              const isWeekend = date.getDay() === 0 || date.getDay() === 6;
              const isPast = date < today;
              
              calendar.push({
                date: date.toISOString().split('T')[0],
                dayNum: day,
                dayOfWeek: date.getDay(),
                available: !isWeekend && !isPast,
                isToday: date.toDateString() === today.toDateString(),
                isPast
              });
            }
            
            return calendar;
          }, [currentMonth]);

          const calendarDates = useMemo(() => generateCalendar(), [generateCalendar]);

          // Generare sloturi în funcție de durata serviciului - memoized pentru performanță
          const generateTimeSlots = useCallback(() => {
            if (!bookingData.service) return [];
            
            const duration = bookingData.service.duration;
            const slots = [];
            const startHour = 9;
            const endHour = 17;
            const startMinutes = startHour * 60;
            const endMinutes = endHour * 60;
            
            // Mesaje posibile pentru sloturi blocate
            const blockReasons = ['Pauză masă', 'Concediu', 'Închis', 'Eveniment special', 'Întâlnire internă'];
            
            for (let minutes = startMinutes; minutes < endMinutes; minutes += duration) {
              const startH = Math.floor(minutes / 60);
              const startM = minutes % 60;
              const endMinTotal = minutes + duration;
              const endH = Math.floor(endMinTotal / 60);
              const endM = endMinTotal % 60;
              
              const startTime = `${String(startH).padStart(2, '0')}:${String(startM).padStart(2, '0')}`;
              const endTime = `${String(endH).padStart(2, '0')}:${String(endM).padStart(2, '0')}`;
              
              // Simulare statusuri (pentru demo)
              const random = Math.random();
              let status = 'available';
              let blockReason = null;
              
              if (random < 0.2) {
                status = 'booked';
              } else if (random < 0.3) {
                status = 'blocked';
                blockReason = blockReasons[Math.floor(Math.random() * blockReasons.length)];
              }
              
              slots.push({
                time: `${startTime}-${endTime}`,
                status,
                blockReason
              });
            }
            
            return slots;
          }, [bookingData.service]);

          const timeSlots = useMemo(() => generateTimeSlots(), [generateTimeSlots]);

          // Filtrare sloturi după perioada zilei - memoized pentru performanță
          const filteredTimeSlots = useMemo(() => timeSlots.filter(slot => {
            if (timeFilter === 'all') return true;
            
            const startTime = slot.time.split('-')[0];
            const hour = parseInt(startTime.split(':')[0]);
            
            if (timeFilter === 'morning') return hour < 13;
            if (timeFilter === 'afternoon') return hour >= 13;
            
            return true;
          }), [timeSlots, timeFilter]);

          // Calculare statistici sloturi - memoized pentru performanță
          const slotStats = useMemo(() => ({
            available: filteredTimeSlots.filter(s => s.status === 'available').length,
            booked: filteredTimeSlots.filter(s => s.status === 'booked').length,
            blocked: filteredTimeSlots.filter(s => s.status === 'blocked').length
          }), [filteredTimeSlots]);

          const { available: availableSlots, booked: bookedSlots, blocked: blockedSlots } = slotStats;

          const familyMembers = [
            { id: 0, name: 'Eu', phone: '0722123456', cnp: '1850101123456', isDefault: true },
            { id: 1, name: 'Soția mea - Elena Popescu', phone: '0722123457', cnp: '2860202234567', isDefault: false },
            { id: 2, name: 'Fiul meu - Andrei Popescu', phone: '0722123458', cnp: '5100303345678', isDefault: false }
          ];

          const steps = [
            { num: 1, name: 'Serviciu', icon: FileText },
            { num: 2, name: 'Medic', icon: User },
            { num: 3, name: 'Data', icon: Calendar },
            { num: 4, name: 'Ora', icon: Clock },
            { num: 5, name: 'Pacient', icon: Users },
            { num: 6, name: 'Rezumat', icon: FileText },
            { num: 7, name: 'Confirmare', icon: CheckCircle }
          ];

          const handleNext = useCallback(() => {
            if (step < 7) {
              if (step === 6) {
                // Simulare procesare programare
                setLoading(true);
                setTimeout(() => {
                  setLoading(false);
                  setStep(7);
                }, 2000);
              } else {
                setStep(step + 1);
              }
            }
          }, [step]);

          const handleBack = useCallback(() => {
            if (step > 1) setStep(step - 1);
          }, [step]);

          const goToPreviousMonth = useCallback(() => {
            const newDate = new Date(currentMonth);
            newDate.setMonth(currentMonth.getMonth() - 1);
            setCurrentMonth(newDate);
          }, [currentMonth]);

          const goToNextMonth = useCallback(() => {
            const newDate = new Date(currentMonth);
            newDate.setMonth(currentMonth.getMonth() + 1);
            setCurrentMonth(newDate);
          }, [currentMonth]);

          const selectService = useCallback((service) => {
            try {
              setError(null);
              setBookingData(prev => ({ ...prev, service }));
              setTimeout(() => setStep(2), 300);
            } catch (err) {
              setError('Eroare la selectarea serviciului');
              console.error('Error selecting service:', err);
            }
          }, []);

          const selectDoctor = useCallback((doctor) => {
            try {
              setError(null);
              setBookingData(prev => ({ ...prev, doctor }));
              setTimeout(() => setStep(3), 300);
            } catch (err) {
              setError('Eroare la selectarea medicului');
              console.error('Error selecting doctor:', err);
            }
          }, []);

          const selectDate = useCallback((date) => {
            try {
              setError(null);
              setBookingData(prev => ({ ...prev, date }));
              setTimeout(() => setStep(4), 300);
            } catch (err) {
              setError('Eroare la selectarea datei');
              console.error('Error selecting date:', err);
            }
          }, []);

          const selectTimeSlot = useCallback((time) => {
            try {
              setError(null);
              setBookingData(prev => ({ ...prev, timeSlot: time }));
              setTimeout(() => setStep(5), 300);
            } catch (err) {
              setError('Eroare la selectarea orei');
              console.error('Error selecting time slot:', err);
            }
          }, []);

          const selectFamilyMember = useCallback((member) => {
            try {
              setError(null);
              setBookingData(prev => ({ ...prev, familyMember: member }));
              setTimeout(() => setStep(6), 300);
            } catch (err) {
              setError('Eroare la selectarea pacientului');
              console.error('Error selecting family member:', err);
            }
          }, []);

          // Filtrare medici după serviciu - memoized pentru performanță
          const filteredDoctors = useMemo(() => 
            bookingData.service 
              ? doctors.filter(d => d.serviceId === bookingData.service.id)
              : doctors,
            [bookingData.service]
          );

          return React.createElement('div', { className: "min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 p-4" },
            React.createElement('div', { className: "max-w-4xl mx-auto" },
              // Header
              React.createElement('div', { className: "bg-white rounded-lg shadow-sm p-6 mb-6" },
                React.createElement('h1', { className: "text-3xl font-bold text-gray-800 mb-2" }, "Programare Consultație"),
                React.createElement('p', { className: "text-gray-600" }, "Completează pașii pentru a realiza o programare")
              ),

              // Progress Steps
              React.createElement('div', { className: "bg-white rounded-lg shadow-sm p-6 mb-6" },
                React.createElement('div', { className: "flex items-center justify-between" },
                  steps.map((s, idx) => {
                    const Icon = s.icon;
                    return React.createElement(React.Fragment, { key: s.num },
                      React.createElement('div', { className: "flex flex-col items-center" },
                        React.createElement('div', { 
                          className: `w-12 h-12 rounded-full flex items-center justify-center mb-2 transition-all ${
                            step === s.num 
                              ? 'bg-blue-600 text-white scale-110' 
                              : step > s.num 
                              ? 'bg-green-500 text-white' 
                              : 'bg-gray-200 text-gray-500'
                          }`
                        },
                          step > s.num ? React.createElement(Check, { size: 20 }) : React.createElement(Icon, { size: 20 })
                        ),
                        React.createElement('span', { 
                          className: `text-xs font-medium ${step === s.num ? 'text-blue-600' : 'text-gray-600'}`
                        }, s.name)
                      ),
                      idx < steps.length - 1 && React.createElement('div', { 
                        className: `flex-1 h-1 mx-2 rounded ${step > s.num ? 'bg-green-500' : 'bg-gray-200'}`
                      })
                    );
                  })
                )
              ),

              // Error Display
              error && React.createElement('div', { className: "bg-red-50 border border-red-200 rounded-lg p-4 mb-6" },
                React.createElement('div', { className: "flex items-center" },
                  React.createElement('div', { className: "text-red-600 mr-3" }, "⚠️"),
                  React.createElement('div', null,
                    React.createElement('h3', { className: "text-red-800 font-medium" }, "Eroare"),
                    React.createElement('p', { className: "text-red-700 text-sm" }, error)
                  ),
                  React.createElement('button', { 
                    onClick: () => setError(null),
                    className: "ml-auto text-red-600 hover:text-red-800"
                  }, "✕")
                )
              ),

              // Main Content
              React.createElement('div', { className: "bg-white rounded-lg shadow-lg p-8 min-h-96" },
                // Step 1: Serviciu
                step === 1 && React.createElement('div', { className: "space-y-4" },
                  React.createElement('h2', { className: "text-2xl font-bold text-gray-800 mb-6" }, "Selectează Serviciul"),
                  React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 gap-4" },
                    services.map(service => 
                      React.createElement('div', {
                        key: service.id,
                        onClick: () => selectService(service),
                        className: `border-2 rounded-lg p-6 cursor-pointer transition-all hover:shadow-md ${
                          bookingData.service?.id === service.id 
                            ? 'border-blue-600 bg-blue-50' 
                            : 'border-gray-200 hover:border-blue-300'
                        }`
                      },
                        React.createElement('h3', { className: "font-bold text-lg text-gray-800 mb-2" }, service.name),
                        React.createElement('p', { className: "text-sm text-gray-600 mb-3" }, service.description),
                        React.createElement('div', { className: "flex justify-between items-center" },
                          React.createElement('span', { className: "text-sm text-gray-500" }, `⏱️ ${service.duration} min`)
                        )
                      )
                    )
                  )
                ),

                // Step 2: Medic
                step === 2 && React.createElement('div', { className: "space-y-4" },
                  React.createElement('h2', { className: "text-2xl font-bold text-gray-800 mb-6" }, "Selectează Medicul"),
                  React.createElement('div', { className: "grid grid-cols-1 md:grid-cols-2 gap-4" },
                    filteredDoctors.map(doctor => 
                      React.createElement('div', {
                        key: doctor.id,
                        onClick: () => selectDoctor(doctor),
                        className: `border-2 rounded-lg p-6 cursor-pointer transition-all hover:shadow-md ${
                          bookingData.doctor?.id === doctor.id 
                            ? 'border-blue-600 bg-blue-50' 
                            : 'border-gray-200 hover:border-blue-300'
                        }`
                      },
                        React.createElement('div', { className: "flex items-center mb-3" },
                          React.createElement('span', { className: "text-4xl mr-4" }, doctor.image),
                          React.createElement('div', null,
                            React.createElement('h3', { className: "font-bold text-lg text-gray-800" }, doctor.name),
                            React.createElement('p', { className: "text-sm text-gray-600" }, doctor.specialty)
                          )
                        )
                      )
                    )
                  )
                ),

                // Step 3: Data - Calendar View
                step === 3 && React.createElement('div', { className: "space-y-4" },
                  React.createElement('h2', { className: "text-2xl font-bold text-gray-800 mb-4" }, "Selectează Data"),
                  React.createElement('p', { className: "text-sm text-gray-600 mb-6" }, "Programările sunt disponibile doar în zilele lucrătoare (Luni-Vineri)"),
                  
                  // Month/Year Selector
                  React.createElement('div', { className: "flex items-center justify-between mb-6 bg-gray-50 rounded-lg p-4" },
                    React.createElement('button', { 
                      onClick: goToPreviousMonth,
                      className: "p-2 hover:bg-gray-200 rounded-lg transition-all"
                    }, React.createElement(ChevronLeft, { size: 24, className: "text-gray-600" })),
                    React.createElement('div', { className: "text-center" },
                      React.createElement('h3', { className: "text-xl font-bold text-gray-800" },
                        currentMonth.toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' })
                      )
                    ),
                    React.createElement('button', { 
                      onClick: goToNextMonth,
                      className: "p-2 hover:bg-gray-200 rounded-lg transition-all"
                    }, React.createElement(ChevronRight, { size: 24, className: "text-gray-600" }))
                  ),
                  
                  React.createElement('div', { className: "grid grid-cols-7 gap-2 mb-4" },
                    ['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm', 'Dum'].map((day, idx) => 
                      React.createElement('div', { key: idx, className: "text-center text-xs font-semibold text-gray-600 py-2" }, day)
                    )
                  ),
                  
                  React.createElement('div', { className: "grid grid-cols-7 gap-2" },
                    calendarDates.map((dateObj, idx) => {
                      if (!dateObj) {
                        return React.createElement('div', { key: `empty-${idx}`, className: "aspect-square" });
                      }
                      
                      const isWeekend = dateObj.dayOfWeek === 0 || dateObj.dayOfWeek === 6;
                      
                      return React.createElement('div', {
                        key: `${dateObj.date}-${idx}`,
                        onClick: () => dateObj.available && selectDate(dateObj.date),
                        className: `aspect-square border-2 rounded-lg p-2 transition-all flex flex-col justify-center items-center ${
                          dateObj.isPast
                            ? 'border-gray-100 bg-gray-50 text-gray-300 cursor-not-allowed'
                            : isWeekend
                            ? 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed'
                            : bookingData.date === dateObj.date 
                            ? 'border-blue-600 bg-blue-600 text-white cursor-pointer hover:shadow-md' 
                            : dateObj.isToday
                            ? 'border-green-500 bg-green-50 text-green-700 cursor-pointer hover:shadow-md'
                            : 'border-gray-200 hover:border-blue-300 text-gray-800 cursor-pointer hover:shadow-md'
                        }`
                      },
                        React.createElement('div', { className: "text-lg font-bold" }, dateObj.dayNum),
                        dateObj.isToday && !isWeekend && !dateObj.isPast && React.createElement('div', { className: "text-xs font-medium mt-1" }, "Azi")
                      );
                    })
                  )
                ),

                // Step 4: Ora cu interval
                step === 4 && React.createElement('div', { className: "space-y-4" },
                  React.createElement('div', { className: "flex justify-between items-start mb-4" },
                    React.createElement('div', null,
                      React.createElement('h2', { className: "text-2xl font-bold text-gray-800" }, "Selectează Ora"),
                      React.createElement('p', { className: "text-sm text-gray-600 mt-2" },
                        "Durata consultației: ", React.createElement('span', { className: "font-semibold text-blue-600" }, `${bookingData.service?.duration} minute`)
                      )
                    ),
                    React.createElement('div', { className: "bg-blue-50 rounded-lg p-3 text-right" },
                      React.createElement('div', { className: "text-2xl font-bold text-blue-600" }, availableSlots),
                      React.createElement('div', { className: "text-xs text-gray-600" }, "sloturi disponibile")
                    )
                  ),

                  // Time Filter Buttons
                  React.createElement('div', { className: "flex gap-2 mb-4" },
                    React.createElement('button', {
                      onClick: () => setTimeFilter('all'),
                      className: `flex-1 py-2 px-4 rounded-lg font-medium transition-all ${
                        timeFilter === 'all'
                          ? 'bg-blue-600 text-white'
                          : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                      }`
                    }, "Toată ziua"),
                    React.createElement('button', {
                      onClick: () => setTimeFilter('morning'),
                      className: `flex-1 py-2 px-4 rounded-lg font-medium transition-all ${
                        timeFilter === 'morning'
                          ? 'bg-blue-600 text-white'
                          : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                      }`
                    }, "🌅 Dimineața (09:00-13:00)"),
                    React.createElement('button', {
                      onClick: () => setTimeFilter('afternoon'),
                      className: `flex-1 py-2 px-4 rounded-lg font-medium transition-all ${
                        timeFilter === 'afternoon'
                          ? 'bg-blue-600 text-white'
                          : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                      }`
                    }, "🌆 După-amiaza (13:00-17:00)")
                  ),
                  
                  // Legend
                  React.createElement('div', { className: "flex flex-wrap gap-4 mb-6 p-4 bg-gray-50 rounded-lg" },
                    React.createElement('div', { className: "flex items-center gap-2" },
                      React.createElement('div', { className: "w-4 h-4 bg-white border-2 border-gray-200 rounded" }),
                      React.createElement('span', { className: "text-sm text-gray-600" }, `Disponibil (${availableSlots})`)
                    ),
                    React.createElement('div', { className: "flex items-center gap-2" },
                      React.createElement('div', { className: "w-4 h-4 bg-red-100 border-2 border-red-300 rounded" }),
                      React.createElement('span', { className: "text-sm text-gray-600" }, `Ocupat (${bookedSlots})`)
                    ),
                    React.createElement('div', { className: "flex items-center gap-2" },
                      React.createElement('div', { className: "w-4 h-4 bg-orange-100 border-2 border-orange-300 rounded" }),
                      React.createElement('span', { className: "text-sm text-gray-600" }, `Blocat (${blockedSlots})`)
                    )
                  ),
                  
                  filteredTimeSlots.length === 0 ? 
                    React.createElement('div', { className: "text-center py-12 text-gray-500" }, "Nu sunt sloturi disponibile în această perioadă") :
                    React.createElement('div', { className: "grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" },
                      filteredTimeSlots.map(slot => 
                        React.createElement('div', { key: slot.time, className: "relative group" },
                          React.createElement('button', {
                            onClick: () => slot.status === 'available' && selectTimeSlot(slot.time),
                            disabled: slot.status !== 'available',
                            className: `w-full py-3 px-3 rounded-lg font-medium transition-all text-sm ${
                              bookingData.timeSlot === slot.time
                                ? 'bg-blue-600 text-white shadow-md'
                                : slot.status === 'available'
                                ? 'bg-white border-2 border-gray-200 text-gray-800 hover:border-blue-300 hover:shadow-md'
                                : slot.status === 'booked'
                                ? 'bg-red-100 text-red-700 border-2 border-red-300 cursor-not-allowed'
                                : 'bg-orange-100 text-orange-700 border-2 border-orange-300 cursor-not-allowed'
                            }`
                          },
                            React.createElement('div', null, slot.time),
                            slot.status === 'booked' && React.createElement('div', { className: "text-xs mt-1 font-normal" }, "Ocupat"),
                            slot.status === 'blocked' && React.createElement('div', { className: "text-xs mt-1 font-normal" }, slot.blockReason)
                          ),
                          
                          // Tooltip
                          slot.status !== 'available' && React.createElement('div', { 
                            className: "absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 shadow-lg"
                          },
                            slot.status === 'booked' ? 
                              React.createElement(React.Fragment, null,
                                React.createElement('div', { className: "font-semibold" }, "Programare existentă"),
                                React.createElement('div', { className: "text-gray-300" }, "Acest slot este deja rezervat")
                              ) :
                              React.createElement(React.Fragment, null,
                                React.createElement('div', { className: "font-semibold" }, slot.blockReason),
                                React.createElement('div', { className: "text-gray-300" }, "Intervalul nu este disponibil")
                              ),
                            React.createElement('div', { className: "absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900" })
                          )
                        )
                      )
                    )
                ),

                // Step 5: Membru Familie
                step === 5 && React.createElement('div', { className: "space-y-4" },
                  React.createElement('h2', { className: "text-2xl font-bold text-gray-800 mb-6" }, "Pentru Cine Este Programarea?"),
                  React.createElement('div', { className: "space-y-3" },
                    familyMembers.map(member => 
                      React.createElement('div', {
                        key: member.id,
                        onClick: () => selectFamilyMember(member),
                        className: `border-2 rounded-lg p-5 cursor-pointer transition-all hover:shadow-md ${
                          bookingData.familyMember?.id === member.id 
                            ? 'border-blue-600 bg-blue-50' 
                            : 'border-gray-200 hover:border-blue-300'
                        }`
                      },
                        React.createElement('div', { className: "flex items-center justify-between" },
                          React.createElement('div', { className: "flex items-center" },
                            React.createElement('div', { className: "w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4" },
                              React.createElement(User, { className: "text-blue-600", size: 24 })
                            ),
                            React.createElement('div', null,
                              React.createElement('h3', { className: "font-bold text-gray-800" }, member.name),
                              React.createElement('p', { className: "text-sm text-gray-600" }, `📞 ${member.phone}`),
                              React.createElement('p', { className: "text-sm text-gray-600" }, `🆔 ${member.cnp}`)
                            )
                          ),
                          member.isDefault && React.createElement('span', { 
                            className: "bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium"
                          }, "Pacient Principal")
                        )
                      )
                    ),
                    React.createElement('button', { 
                      className: "w-full border-2 border-dashed border-gray-300 rounded-lg p-5 text-gray-600 hover:border-blue-400 hover:text-blue-600 transition-all"
                    }, "+ Adaugă Membru Nou")
                  )
                ),

                // Step 6: Rezumat
                step === 6 && React.createElement('div', { className: "space-y-6" },
                  React.createElement('h2', { className: "text-2xl font-bold text-gray-800 mb-6" }, "Rezumat Programare"),
                  
                  React.createElement('div', { className: "bg-blue-50 rounded-lg p-6 space-y-4" },
                    React.createElement('div', { className: "flex justify-between items-start border-b border-blue-100 pb-3" },
                      React.createElement('span', { className: "text-gray-600 font-medium" }, "Serviciu:"),
                      React.createElement('div', { className: "text-right" },
                        React.createElement('p', { className: "font-bold text-gray-800" }, bookingData.service?.name),
                        React.createElement('p', { className: "text-sm text-gray-600" }, `${bookingData.service?.duration} minute`)
                      )
                    ),

                    React.createElement('div', { className: "flex justify-between items-start border-b border-blue-100 pb-3" },
                      React.createElement('span', { className: "text-gray-600 font-medium" }, "Medic:"),
                      React.createElement('div', { className: "text-right" },
                        React.createElement('p', { className: "font-bold text-gray-800" }, bookingData.doctor?.name),
                        React.createElement('p', { className: "text-sm text-gray-600" }, bookingData.doctor?.specialty)
                      )
                    ),

                    React.createElement('div', { className: "flex justify-between items-start border-b border-blue-100 pb-3" },
                      React.createElement('span', { className: "text-gray-600 font-medium" }, "Data și Ora:"),
                      React.createElement('div', { className: "text-right" },
                        React.createElement('p', { className: "font-bold text-gray-800" },
                          bookingData.date && new Date(bookingData.date).toLocaleDateString('ro-RO', { 
                            weekday: 'long', 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                          })
                        ),
                        React.createElement('p', { className: "text-sm text-gray-600" }, bookingData.timeSlot)
                      )
                    ),

                    React.createElement('div', { className: "flex justify-between items-start border-b border-blue-100 pb-3" },
                      React.createElement('span', { className: "text-gray-600 font-medium" }, "Pacient:"),
                      React.createElement('div', { className: "text-right" },
                        React.createElement('p', { className: "font-bold text-gray-800" }, bookingData.familyMember?.name),
                        React.createElement('p', { className: "text-sm text-gray-600" }, bookingData.familyMember?.phone)
                      )
                    )
                  ),

                  React.createElement('div', { className: "mt-6" },
                    React.createElement('label', { className: "block text-gray-700 font-medium mb-2" }, "Observații (opțional)"),
                    React.createElement('textarea', {
                      value: bookingData.notes,
                      onChange: (e) => {
                        const value = e.target.value;
                        if (value.length <= 500) {
                          setBookingData(prev => ({ ...prev, notes: value }));
                        }
                      },
                      className: "w-full border-2 border-gray-200 rounded-lg p-3 focus:border-blue-400 focus:outline-none",
                      rows: "3",
                      placeholder: "Adaugă orice informații suplimentare...",
                      maxLength: 500
                    }),
                    React.createElement('div', { className: "text-right text-sm text-gray-500 mt-1" },
                      `${bookingData.notes.length}/500 caractere`
                    )
                  )
                ),

                // Step 7: Confirmare
                step === 7 && React.createElement('div', { className: "text-center py-8" },
                  React.createElement('div', { className: "w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6" },
                    React.createElement(CheckCircle, { className: "text-green-600", size: 48 })
                  ),
                  React.createElement('h2', { className: "text-3xl font-bold text-gray-800 mb-4" }, "Programare Confirmată!"),
                  React.createElement('p', { className: "text-gray-600 mb-8" },
                    "Ai primit un email de confirmare la adresa ta de email.", React.createElement('br'),
                    "Vei primi un reminder cu 24 de ore înainte de consultație."
                  ),
                  
                  React.createElement('div', { className: "bg-gray-50 rounded-lg p-6 max-w-md mx-auto mb-8" },
                    React.createElement('p', { className: "text-sm text-gray-600 mb-2" }, "Cod programare:"),
                    React.createElement('p', { className: "text-2xl font-bold text-blue-600 mb-4" }, "#APT-2025-1234"),
                    React.createElement('p', { className: "text-xs text-gray-500" }, "Salvează acest cod pentru referințe ulterioare")
                  ),

                  React.createElement('div', { className: "flex gap-4 justify-center" },
                    React.createElement('button', { 
                      className: "bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition-all"
                    }, "Vezi Programările Mele"),
                    React.createElement('button', { 
                      onClick: () => {
                        setStep(1);
                        setBookingData({
                          service: null,
                          doctor: null,
                          date: null,
                          timeSlot: null,
                          familyMember: null,
                          notes: ''
                        });
                      },
                      className: "border-2 border-gray-300 text-gray-700 px-8 py-3 rounded-lg font-medium hover:border-blue-400 hover:text-blue-600 transition-all"
                    }, "Programare Nouă")
                  )
                )
              ),

              // Navigation Buttons
              step < 7 && React.createElement('div', { className: "flex justify-between mt-6" },
                React.createElement('button', {
                  onClick: handleBack,
                  disabled: step === 1,
                  className: `flex items-center px-6 py-3 rounded-lg font-medium transition-all ${
                    step === 1
                      ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                      : 'bg-white text-gray-700 hover:bg-gray-50 shadow-sm'
                  }`
                },
                  React.createElement(ChevronLeft, { size: 20, className: "mr-2" }),
                  "Înapoi"
                ),

                step === 6 && React.createElement('button', {
                  onClick: handleNext,
                  disabled: loading,
                  className: `flex items-center px-8 py-3 rounded-lg font-medium shadow-md transition-all ${
                    loading 
                      ? 'bg-gray-400 cursor-not-allowed' 
                      : 'bg-green-600 hover:bg-green-700'
                  } text-white`
                },
                  loading ? 
                    React.createElement(React.Fragment, null,
                      React.createElement('div', { className: "animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2" }),
                      "Se procesează..."
                    ) :
                    React.createElement(React.Fragment, null,
                      "Confirmă Programarea",
                      React.createElement(Check, { size: 20, className: "ml-2" })
                    )
                )
              )
            )
          );
        };

        // Render the component using React 18 createRoot
        const root = createRoot(document.getElementById('medical-booking-root'));
        root.render(React.createElement(BookingFlow));
