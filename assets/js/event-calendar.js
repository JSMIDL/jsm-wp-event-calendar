/**
 * JavaScript pro frontend kalendáře událostí - Minimalistický design 2025
 */
(function($) {
    'use strict';

    // Globální objekt pro naše funkce
    window.JSMEventCalendar = {
        init: function() {
            console.log('Initializing JSM Event Calendar 2025 - Minimalist Edition');

            // Nastavení navigace a modálů
            this.setupCalendarNavigation();
            this.setupEventModals();
            this.detectMobileView();

            // Okamžité načtení dat při prvním zobrazení - důležité pro správné první načtení
            this.initializeCalendars();

            // Sledování změny velikosti okna pro responzivní vzhled
            $(window).on('resize', this.handleResize.bind(this));
        },

        /**
         * Inicializace všech kalendářů na stránce
         */
        initializeCalendars: function() {
            $('.jsm-event-calendar-wrapper').each(function() {
                const calendarId = $(this).attr('id');
                const month = parseInt($(this).data('month'));
                const year = parseInt($(this).data('year'));

                if (calendarId && month && year) {
                    console.log('Loading initial calendar data:', calendarId, month, year);
                    // Přidání mírného zpoždění pro správné vykreslení DOM
                    setTimeout(function() {
                        JSMEventCalendar.updateCalendar(calendarId, month, year);
                    }, 50);
                }
            });
        },

        /**
         * Zpracování změny velikosti okna s omezením počtu volání (debounce)
         */
        handleResize: function() {
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(function() {
                JSMEventCalendar.detectMobileView();
                // Sjednocení výšek po změně velikosti okna
                JSMEventCalendar.equalizeCalendarCellHeights();
            }, 250);
        },

        /**
         * Nastavení navigace kalendáře
         */
        setupCalendarNavigation: function() {
            // Delegace událostí pro lepší výkon a kompatibilitu s dynamicky vytvořenými prvky
            $(document).off('click', '.jsm-event-calendar-prev').on('click', '.jsm-event-calendar-prev', function() {
                JSMEventCalendar.changeMonth($(this).data('calendar-id'), -1);
            });

            $(document).off('click', '.jsm-event-calendar-next').on('click', '.jsm-event-calendar-next', function() {
                JSMEventCalendar.changeMonth($(this).data('calendar-id'), 1);
            });

            $(document).off('click', '.jsm-event-calendar-today').on('click', '.jsm-event-calendar-today', function() {
                JSMEventCalendar.goToToday($(this).data('calendar-id'));
            });
        },

        /**
         * Změna měsíce v kalendáři - pouze dopředu
         */
        changeMonth: function(calendarId, direction) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found:', calendarId);
                return;
            }

            const currentMonth = parseInt($calendar.data('month'));
            const currentYear = parseInt($calendar.data('year'));

            // Získání aktuálního data pro omezení
            const today = new Date();
            const currentRealMonth = today.getMonth() + 1; // +1 protože getMonth() vrací 0-11
            const currentRealYear = today.getFullYear();

            // Pokud jde o navigaci zpět, kontrolujeme, zda nejdeme do minulosti
            if (direction < 0) {
                // Pokud jsme v aktuálním měsíci nebo chceme jít do minulosti, zastavíme
                if ((currentYear < currentRealYear) ||
                    (currentYear === currentRealYear && currentMonth <= currentRealMonth)) {
                    console.log("Nelze navigovat do minulosti");
                    // Přidání efektu "zakázaného" tlačítka
                    const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');
                    $prevButton.addClass('disabled').delay(300).queue(function(next) {
                        $(this).removeClass('disabled');
                        next();
                    });
                    return; // Nedovolíme navigaci do minulosti
                }
            }

            let newMonth = currentMonth + direction;
            let newYear = currentYear;

            // Ošetření přechodů mezi roky
            if (newMonth > 12) {
                newMonth = 1;
                newYear++;
            } else if (newMonth < 1) {
                newMonth = 12;
                newYear--;
            }

            // Aktualizace kalendáře
            this.updateCalendar(calendarId, newMonth, newYear);
        },

        /**
         * Přechod na aktuální měsíc
         */
        goToToday: function(calendarId) {
            const today = new Date();
            const month = today.getMonth() + 1; // JavaScript počítá měsíce od 0
            const year = today.getFullYear();

            this.updateCalendar(calendarId, month, year);
        },

        /**
         * Aktualizace kalendáře pomocí AJAX - optimalizováno pro rychlost
         */
        updateCalendar: function(calendarId, month, year) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found for update:', calendarId);
                return;
            }

            const $calendarTable = $('#' + calendarId + '-table');
            const $calendarTitle = $('#' + calendarId + '-title');
            const showList = $calendar.data('show-list');
            const category = $calendar.data('category');

            // Zobrazení načítací animace
            $calendarTable.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

            // AJAX požadavek na backend - optimalizace pro rychlejší načítání
            $.ajax({
                url: jsmEventCalendar.ajaxurl,
                type: 'GET',
                data: {
                    action: jsmEventCalendar.action,
                    month: month,
                    year: year,
                    category: category,
                    nonce: jsmEventCalendar.nonce,
                    cache: false // Vynucení obejití vyrovnávací paměti prohlížeče
                },
                success: function(response) {
                    if (response && response.success) {
                        const events = response.data;

                        // Aktualizace měsíce a roku v datových atributech
                        $calendar.data('month', month);
                        $calendar.data('year', year);

                        // Aktualizace nadpisu kalendáře
                        const monthName = jsmEventCalendar.i18n.months[month - 1];
                        $calendarTitle.text(monthName + ' ' + year);

                        // Zobrazit/Skrýt tlačítko Předchozí měsíc podle aktuálního měsíce
                        const today = new Date();
                        const currentRealMonth = today.getMonth() + 1;
                        const currentRealYear = today.getFullYear();
                        const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');

                        if (month === currentRealMonth && year === currentRealYear) {
                            $prevButton.css('visibility', 'hidden');
                        } else {
                            $prevButton.css('visibility', 'visible');
                        }

                        // Vykreslení kalendáře
                        JSMEventCalendar.renderCalendar($calendarTable, month, year, events);

                        // Přidat timeout pro zajištění, že všechny obrázky a obsah se stihnou načíst
                        setTimeout(function() {
                            JSMEventCalendar.equalizeCalendarCellHeights();
                        }, 100);

                        // Aktualizace seznamu událostí, pokud je zobrazen
                        if (showList === 'yes') {
                            JSMEventCalendar.renderEventList($('#' + calendarId + '-list'), events);
                        }

                        // Znovu nastavení event handlers pro modální okna událostí
                        JSMEventCalendar.setupEventModals();
                    } else {
                        $calendarTable.html('<div class="jsm-event-no-events">Chyba při načítání kalendáře: ' + (response ? response.data : 'Neplatná odpověď') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $calendarTable.html('<div class="jsm-event-no-events">Chyba při načítání kalendáře. Zkuste to prosím znovu.</div>');
                }
            });
        },

        /**
         * Vykreslení kalendáře - evropský formát (pondělí jako první den)
         */
        renderCalendar: function($calendarTable, month, year, events) {
            const daysInMonth = new Date(year, month, 0).getDate();

            // Výpočet prvního dne v měsíci pro evropský formát (pondělí=0, neděle=6)
            const firstDayDate = new Date(year, month - 1, 1);
            let firstDay = firstDayDate.getDay(); // 0=neděle, 1=pondělí, ..., 6=sobota
            firstDay = firstDay === 0 ? 6 : firstDay - 1; // Konverze na 0=pondělí, ..., 6=neděle

            const today = new Date();
            const todayDate = today.getDate();
            const todayMonth = today.getMonth() + 1;
            const todayYear = today.getFullYear();

            // Pokud jsme na mobilním zařízení, zobrazíme seznam dnů místo tabulky
            if (window.innerWidth <= 768) {
                this.renderMobileCalendar($calendarTable, month, year, daysInMonth, firstDay, events, todayDate, todayMonth, todayYear);
                return;
            }

            let html = '<table class="jsm-event-calendar-table">';
            html += '<thead><tr>';

            // Názvy dnů v týdnu - začínáme pondělkem (evropský formát)
            const weekDaysOrder = [0, 1, 2, 3, 4, 5, 6]; // 0=pondělí, 6=neděle
            for (let i = 0; i < 7; i++) {
                html += '<th>' + jsmEventCalendar.i18n.weekdaysShort[weekDaysOrder[i]] + '</th>';
            }

            html += '</tr></thead><tbody><tr>';

            // Prázdné buňky před prvním dnem měsíce
            let dayCount = 0;
            for (let i = 0; i < firstDay; i++) {
                html += '<td><div class="jsm-event-calendar-day empty other-month"></div></td>';
                dayCount++;
            }

            // Dny v měsíci
            for (let i = 1; i <= daysInMonth; i++) {
                // Nový řádek po 7 dnech
                if (dayCount % 7 === 0 && dayCount > 0) {
                    html += '</tr><tr>';
                }

                // Třídy pro den
                let dayClasses = 'jsm-event-calendar-day';

                // Kontrola, zda je den dnešní
                if (i === todayDate && month === todayMonth && year === todayYear) {
                    dayClasses += ' today';
                }

                // Kontrola, zda den již proběhl (je to minulý den v aktuálním měsíci)
                if ((year === todayYear && month === todayMonth && i < todayDate) ||
                    (year === todayYear && month < todayMonth) ||
                    (year < todayYear)) {
                    dayClasses += ' past-day';
                }

                // Datum pro tento den
                const dateStr = year + '-' + this.pad(month) + '-' + this.pad(i);

                html += '<td>';
                html += '<div class="' + dayClasses + '" data-date="' + dateStr + '">';
                html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';

                // Události pro tento den
                const dayEvents = this.getEventsForDay(events, year, month, i);
                for (let j = 0; j < dayEvents.length; j++) {
                    html += this.renderEventInCell(dayEvents[j]);
                }

                html += '</div>';
                html += '</td>';

                dayCount++;
            }

            // Prázdné buňky na konci měsíce
            while (dayCount % 7 !== 0) {
                html += '<td><div class="jsm-event-calendar-day empty other-month"></div></td>';
                dayCount++;
            }

            html += '</tr></tbody></table>';

            $calendarTable.html(html);

            // Volání funkce pro sjednocení výšky buněk po vykreslení
            this.equalizeCalendarCellHeights();
        },

        /**
         * Funkce pro sjednocení výšky buněk v kalendáři
         */
        equalizeCalendarCellHeights: function() {
            // Pokud jsme na mobilním zařízení, nebudeme výšku sjednocovat
            if (window.innerWidth <= 768) {
                return;
            }

            // Sjednocení výšky po řádcích
            $('.jsm-event-calendar-table tbody tr').each(function() {
                const $cells = $(this).find('.jsm-event-calendar-day');
                if ($cells.length === 0) return;

                // Reset výšky pro správné měření
                $cells.css('height', 'auto');

                // Najít největší výšku v řádku
                let maxHeight = 0;
                $cells.each(function() {
                    const height = $(this).outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                });

                // Aplikovat stejnou výšku na všechny buňky v řádku
                if (maxHeight > 0) {
                    $cells.css('height', maxHeight + 'px');
                }
            });

            console.log('Calendar cell heights equalized');
        },

        /**
         * Alternativní přístup - sjednocení výšky všech buněk v kalendáři
         */
        equalizeAllCalendarCellHeights: function() {
            // Pokud jsme na mobilním zařízení, nebudeme výšku sjednocovat
            if (window.innerWidth <= 768) {
                return;
            }

            const $cells = $('.jsm-event-calendar-day:not(.empty)');
            if ($cells.length === 0) return;

            // Reset výšky pro správné měření
            $cells.css('height', 'auto');

            // Najít největší výšku ve všech buňkách
            let maxHeight = 0;
            $cells.each(function() {
                const height = $(this).outerHeight();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            // Aplikovat stejnou výšku na všechny buňky
            if (maxHeight > 0) {
                $cells.css('height', maxHeight + 'px');
            }

            console.log('All calendar cell heights equalized to ' + maxHeight + 'px');
        },


/**
 * Vykreslení kalendáře pro mobilní zařízení - pouze dny s událostmi
 */
renderMobileCalendar: function($calendarTable, month, year, daysInMonth, firstDay, events, todayDate, todayMonth, todayYear) {
    let html = '<div class="jsm-event-calendar-list-view">';
    let hasEvents = false;

    // Procházení dnů v měsíci
    for (let i = 1; i <= daysInMonth; i++) {
        // Události pro tento den
        const dayEvents = this.getEventsForDay(events, year, month, i);

        // Přeskočíme dny bez událostí
        if (dayEvents.length === 0) {
            continue;
        }

        hasEvents = true;

        // Třídy pro den
        let dayClasses = 'jsm-event-calendar-day';

        // Kontrola, zda je den dnešní
        if (i === todayDate && month === todayMonth && year === todayYear) {
            dayClasses += ' today';
        }

        // Kontrola, zda den již proběhl (je to minulý den v aktuálním měsíci)
        if ((year === todayYear && month === todayMonth && i < todayDate) ||
            (year === todayYear && month < todayMonth) ||
            (year < todayYear)) {
            dayClasses += ' past-day';
        }

        // Výpočet dne v týdnu pro evropský formát
        const dayDate = new Date(year, month - 1, i);
        let dayOfWeek = dayDate.getDay(); // 0=neděle, 1=pondělí, ..., 6=sobota
        dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Konverze na 0=pondělí, ..., 6=neděle

        const dayName = jsmEventCalendar.i18n.weekdays[dayOfWeek];
        const formattedDate = dayDate.toLocaleDateString('cs-CZ', { weekday: 'long', day: 'numeric', month: 'long' });

        html += '<div class="' + dayClasses + '" data-date="' + year + '-' + this.pad(month) + '-' + this.pad(i) + '">';
        html += '<div class="jsm-event-calendar-day-header">';
        html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';
        html += '<span class="jsm-event-calendar-day-name">' + dayName + '</span>';
        html += '</div>';

        // Události pro tento den
        for (let j = 0; j < dayEvents.length; j++) {
            html += this.renderEventInCell(dayEvents[j]);
        }

        html += '</div>';
    }

    html += '</div>';

    // Pokud nejsou žádné události, zobrazíme oznámení
    if (!hasEvents) {
        html = '<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>';
    }

    $calendarTable.html(html);
},

        /**
         * Získání událostí pro daný den
         */
        getEventsForDay: function(events, year, month, day) {
            if (!events || !Array.isArray(events)) {
                return [];
            }

            const dayEvents = [];
            const dateString = year + '-' + this.pad(month) + '-' + this.pad(day);

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
                if (!event || !event.startDate) continue;

                const startDate = event.startDate;
                const endDate = event.endDate || event.startDate;

                // Kontrola, zda událost patří do daného dne
                if (dateString >= startDate && dateString <= endDate) {
                    dayEvents.push(event);
                }
            }

            return dayEvents;
        },

        /**
         * Vykreslení události v buňce kalendáře - minimalistický design
         */
        renderEventInCell: function(event) {
            if (!event || !event.id || !event.title) {
                return '';
            }

            let html = '<div class="jsm-event-calendar-event" data-event-id="' + event.id + '">';
            html += '<div class="jsm-event-calendar-event-title">' + event.title + '</div>';

            if (event.timeDisplay && !event.allDay) {
                html += '<div class="jsm-event-calendar-event-time">' + event.timeDisplay + '</div>';
            }

            html += '</div>';

            return html;
        },

        /**
         * Vykreslení seznamu událostí - moderní design
         */
        renderEventList: function($listContainer, events) {
            if (!events || !Array.isArray(events) || events.length === 0) {
                $listContainer.html('<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>');
                return;
            }

            let html = '<div class="jsm-event-list">';
            html += '<h3 class="jsm-event-list-title">' + jsmEventCalendar.i18n.eventsListTitle + '</h3>';

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
                if (!event || !event.id) continue;

                html += '<div class="jsm-event-list-item">';

                // Náhledový obrázek, pokud existuje
                if (event.thumbnail) {
                    html += '<div class="jsm-event-list-item-thumbnail">';
                    html += '<img src="' + event.thumbnail + '" alt="' + event.title + '">';
                    html += '</div>';
                }

                html += '<div class="jsm-event-list-item-header">';
                html += '<div class="jsm-event-list-item-date">' + event.dateDisplay + '</div>';
                html += '<h4 class="jsm-event-list-item-title">' + event.title + '</h4>';
                html += '</div>';

                html += '<div class="jsm-event-list-item-content">' + event.excerpt + '</div>';

                html += '<div class="jsm-event-list-item-footer">';

                if (event.timeDisplay) {
                    html += '<div class="jsm-event-list-item-time">' + event.timeDisplay + '</div>';
                }

                if (event.customUrl && event.buttonText) {
                    html += '<a href="' + event.customUrl + '" class="jsm-event-button">' + event.buttonText + '</a>';
                }

                html += '</div>';
                html += '</div>';
            }

            html += '</div>';

            $listContainer.html(html);
        },

       /**
        * Nastavení modálních oken pro události
        */
       setupEventModals: function() {
           // Použijeme delegaci událostí pro lepší výkon
           $(document).off('click', '.jsm-event-calendar-event').on('click', '.jsm-event-calendar-event', function(e) {
               e.preventDefault();
               e.stopPropagation();
               const eventId = $(this).data('event-id');
               console.log('Event clicked, ID:', eventId); // Debugging
               if (eventId) {
                   JSMEventCalendar.openEventModal(eventId);
               }
           });

           // Zavření modálního okna - delegace událostí pro dynamicky vytvořené prvky
           $(document).off('click', '.jsm-event-modal-close').on('click', '.jsm-event-modal-close', function(e) {
               e.preventDefault();
               e.stopPropagation(); // Zabránit propagaci události do rodičů
               JSMEventCalendar.closeEventModal();
           });

           // Zavření modálního okna po kliknutí mimo obsah
           $(document).off('click', '.jsm-event-modal').on('click', '.jsm-event-modal', function(e) {
               if ($(e.target).hasClass('jsm-event-modal')) {
                   JSMEventCalendar.closeEventModal();
               }
           });

           // Zavření modálního okna po stisknutí klávesy Escape
           $(document).off('keyup.modal').on('keyup.modal', function(e) {
               if (e.key === 'Escape' && $('.jsm-event-modal.active').length) {
                   JSMEventCalendar.closeEventModal();
               }
           });
       },

       /**
        * Otevření modálního okna s detailem události - upravená implementace
        */
       openEventModal: function(eventId) {
           if (!eventId) {
               console.error('No event ID provided for modal');
               return;
           }

           const $modal = $('#jsm-event-modal');
           const $modalContent = $('#jsm-event-modal-content');

           // Použijeme CSS třídu pro aktivaci modálu
           $modal.addClass('active');

           console.log('Opening modal for event ID:', eventId); // Debugging

           // Přidání třídy pro omezení scrollování na stránce pod modálem
           $('body').addClass('modal-open');

           // Načítací animace
           $modalContent.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

           // AJAX požadavek pro načtení detailu události - s cachingem pro lepší výkon
           const cacheKey = 'event_' + eventId;

           // Zkusíme načíst z cache
           if (this.eventCache && this.eventCache[cacheKey]) {
               $modalContent.html(this.eventCache[cacheKey]);
               return;
           }

          // Pokud není v cache, načteme ze serveru
          $.ajax({
              url: jsmEventCalendar.ajaxurl,
              type: 'GET',
              data: {
                  action: 'get_event_detail',
                  event_id: eventId,
                  nonce: jsmEventCalendar.nonce
              },
              success: function(response) {
                  if (response && response.success) {
                      // Vykreslení detailu události s křížkem
                      $modalContent.html(response.data + '<span class="jsm-event-modal-close">&times;</span>');

                      // Uložení do cache (včetně křížku)
                      if (!JSMEventCalendar.eventCache) JSMEventCalendar.eventCache = {};
                      JSMEventCalendar.eventCache[cacheKey] = response.data + '<span class="jsm-event-modal-close">&times;</span>';
                  } else {
                      $modalContent.html('<div class="jsm-event-no-events">Událost nebyla nalezena.</div><span class="jsm-event-modal-close">&times;</span>');
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Error loading event:', error); // Debugging
                  $modalContent.html('<div class="jsm-event-no-events">Chyba při načítání události.</div><span class="jsm-event-modal-close">&times;</span>');
              }
          });
       },

       /**
        * Zavření modálního okna - upravená implementace
        */
       closeEventModal: function() {
           const $modal = $('#jsm-event-modal');
           // Odstraníme třídu active
           $modal.removeClass('active');
           $('body').removeClass('modal-open');
           console.log('Modal closed'); // Debugging
       },

        /**
         * Detekce mobilního zobrazení a přepnutí na responzivní layout
         */
        detectMobileView: function() {
            const isMobile = window.innerWidth <= 768;

            $('.jsm-event-calendar-wrapper').toggleClass('jsm-mobile-view', isMobile);

            // Překreslení jen pokud je potřeba
            if (isMobile && $('.jsm-event-calendar-table').is(':visible')) {
                $('.jsm-event-calendar-wrapper').each(function() {
                    const calendarId = $(this).attr('id');
                    const month = parseInt($(this).data('month'));
                    const year = parseInt($(this).data('year'));

                    if (calendarId && month && year) {
                        JSMEventCalendar.updateCalendar(calendarId, month, year);
                    }
                });
            } else if (!isMobile && $('.jsm-event-calendar-list-view').is(':visible')) {
                $('.jsm-event-calendar-wrapper').each(function() {
                    const calendarId = $(this).attr('id');
                    const month = parseInt($(this).data('month'));
                    const year = parseInt($(this).data('year'));

                    if (calendarId && month && year) {
                        JSMEventCalendar.updateCalendar(calendarId, month, year);
                    }
                });
            }
        },

        /**
         * Pomocná funkce pro doplnění nuly před jednomístné číslo
         */
        pad: function(num) {
            return (num < 10 ? '0' : '') + num;
        }
    };

    // Inicializace po načtení dokumentu
    $(document).ready(function() {
        JSMEventCalendar.init();
    });

})(jQuery);