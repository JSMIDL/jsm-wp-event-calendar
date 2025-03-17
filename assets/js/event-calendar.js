/**
 * JavaScript pro frontend kalendáře událostí - modernizováno pro rok 2025
 */
(function($) {
    'use strict';

    // Globální objekt pro naše funkce
    window.JSMEventCalendar = {
        init: function() {
            console.log('Initializing JSM Event Calendar 2025');

            // Nastavení navigace a modálů
            this.setupCalendarNavigation();
            this.setupEventModals();
            this.detectMobileView();

            // Okamžité načtení dat při prvním zobrazení
            $('.jsm-event-calendar-wrapper').each(function() {
                const calendarId = $(this).attr('id');
                const month = parseInt($(this).data('month'));
                const year = parseInt($(this).data('year'));

                if (calendarId && month && year) {
                    console.log('Loading initial calendar data:', calendarId, month, year);
                    JSMEventCalendar.updateCalendar(calendarId, month, year);
                }
            });

            // Sledování změny velikosti okna pro responzivní vzhled
            $(window).on('resize', this.detectMobileView);
        },

        /**
         * Nastavení navigace kalendáře
         */
        setupCalendarNavigation: function() {
            // Tlačítka pro přechod na předchozí/další měsíc
            $(document).on('click', '.jsm-event-calendar-prev', function() {
                JSMEventCalendar.changeMonth($(this).data('calendar-id'), -1);
            });

            $(document).on('click', '.jsm-event-calendar-next', function() {
                JSMEventCalendar.changeMonth($(this).data('calendar-id'), 1);
            });

            // Tlačítko pro aktuální měsíc
            $(document).on('click', '.jsm-event-calendar-today', function() {
                JSMEventCalendar.goToToday($(this).data('calendar-id'));
            });
        },

        /**
         * Změna měsíce v kalendáři - pouze dopředu
         */
        changeMonth: function(calendarId, direction) {
            const $calendar = $('#' + calendarId);
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
                    (currentYear == currentRealYear && currentMonth <= currentRealMonth)) {
                    console.log("Nelze navigovat do minulosti");
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
         * Aktualizace kalendáře pomocí AJAX
         */
        updateCalendar: function(calendarId, month, year) {
            const $calendar = $('#' + calendarId);
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
                    nonce: jsmEventCalendar.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const events = response.data;

                        // Aktualizace měsíce a roku v datových atributech
                        $calendar.data('month', month);
                        $calendar.data('year', year);

                        // Aktualizace nadpisu kalendáře
                        const monthName = jsmEventCalendar.i18n.months[month - 1];
                        $calendarTitle.text(monthName + ' ' + year);

                        // Vykreslení kalendáře
                        JSMEventCalendar.renderCalendar($calendarTable, month, year, events);

                        // Aktualizace seznamu událostí, pokud je zobrazen
                        if (showList === 'yes') {
                            JSMEventCalendar.renderEventList($('#' + calendarId + '-list'), events);
                        }

                        // Znovu nastavení event handlers pro modální okna událostí
                        JSMEventCalendar.setupEventModals();
                    } else {
                        $calendarTable.html('<div class="jsm-event-no-events">Chyba při načítání kalendáře: ' + response.data + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $calendarTable.html('<div class="jsm-event-no-events">Chyba při načítání kalendáře. Zkuste to prosím znovu.</div>');
                }
            });
        },

        /**
         * Vykreslení kalendáře
         */
        renderCalendar: function($calendarTable, month, year, events) {
            const daysInMonth = new Date(year, month, 0).getDate();
            const firstDay = new Date(year, month - 1, 1).getDay();
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

            // Názvy dnů v týdnu
            for (let i = 0; i < 7; i++) {
                html += '<th>' + jsmEventCalendar.i18n.weekdaysShort[i] + '</th>';
            }

            html += '</tr></thead><tbody><tr>';

            // Prázdné buňky před prvním dnem měsíce
            let dayCount = 0;
            for (let i = 0; i < firstDay; i++) {
                html += '<td class="jsm-event-calendar-day empty other-month"></td>';
                dayCount++;
            }

            // Dny v měsíci
            for (let i = 1; i <= daysInMonth; i++) {
                // Nový řádek po 7 dnech
                if (dayCount % 7 === 0 && dayCount > 0) {
                    html += '</tr><tr>';
                }

                // Třída pro dnešní den
                let classToday = '';
                if (i === todayDate && month === todayMonth && year === todayYear) {
                    classToday = ' today';
                }

                html += '<td class="jsm-event-calendar-day' + classToday + '" data-date="' + year + '-' + this.pad(month) + '-' + this.pad(i) + '">';
                html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';

                // Události pro tento den
                const dayEvents = this.getEventsForDay(events, year, month, i);
                for (let j = 0; j < dayEvents.length; j++) {
                    html += this.renderEventInCell(dayEvents[j]);
                }

                html += '</td>';
                dayCount++;
            }

            // Prázdné buňky na konci měsíce
            while (dayCount % 7 !== 0) {
                html += '<td class="jsm-event-calendar-day empty other-month"></td>';
                dayCount++;
            }

            html += '</tr></tbody></table>';

            $calendarTable.html(html);
        },

        /**
         * Vykreslení kalendáře pro mobilní zařízení
         */
        renderMobileCalendar: function($calendarTable, month, year, daysInMonth, firstDay, events, todayDate, todayMonth, todayYear) {
            let html = '<div class="jsm-event-calendar-list-view">';

            // Procházení dnů v měsíci
            for (let i = 1; i <= daysInMonth; i++) {
                // Události pro tento den
                const dayEvents = this.getEventsForDay(events, year, month, i);

                // Přeskočíme dny bez událostí
                if (dayEvents.length === 0) {
                    continue;
                }

                // Třída pro dnešní den
                let classToday = '';
                if (i === todayDate && month === todayMonth && year === todayYear) {
                    classToday = ' today';
                }

                // Název dne v týdnu
                const dayDate = new Date(year, month - 1, i);
                const dayName = jsmEventCalendar.i18n.weekdays[dayDate.getDay()];

                html += '<div class="jsm-event-calendar-day' + classToday + '" data-date="' + year + '-' + this.pad(month) + '-' + this.pad(i) + '">';
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
            if (html === '<div class="jsm-event-calendar-list-view"></div>') {
                html = '<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>';
            }

            $calendarTable.html(html);
        },

        /**
         * Získání událostí pro daný den
         */
        getEventsForDay: function(events, year, month, day) {
            const dayEvents = [];
            const dateString = year + '-' + this.pad(month) + '-' + this.pad(day);

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
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
         * Vykreslení události v buňce kalendáře
         */
        renderEventInCell: function(event) {
            let html = '<div class="jsm-event-calendar-event" data-event-id="' + event.id + '">';
            html += '<div class="jsm-event-calendar-event-title">' + event.title + '</div>';

            if (event.timeDisplay && !event.allDay) {
                html += '<div class="jsm-event-calendar-event-time">' + event.timeDisplay + '</div>';
            }

            html += '</div>';

            return html;
        },

        /**
         * Vykreslení seznamu událostí
         */
        renderEventList: function($listContainer, events) {
            if (events.length === 0) {
                $listContainer.html('<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>');
                return;
            }

            let html = '<div class="jsm-event-list">';
            html += '<h3 class="jsm-event-list-title">' + jsmEventCalendar.i18n.eventsListTitle + '</h3>';

            for (let i = 0; i < events.length; i++) {
                const event = events[i];

                html += '<div class="jsm-event-list-item">';
                html += '<div class="jsm-event-list-item-header">';
                html += '<h4 class="jsm-event-list-item-title">' + event.title + '</h4>';
                html += '<div class="jsm-event-list-item-date">' + event.dateDisplay + '</div>';
                html += '</div>';

                html += '<div class="jsm-event-list-item-content">' + event.excerpt + '</div>';

                html += '<div class="jsm-event-list-item-footer">';

                if (event.timeDisplay) {
                    html += '<div class="jsm-event-list-item-time">' + event.timeDisplay + '</div>';
                }

                const buttonUrl = event.customUrl || event.url;
                html += '<a href="' + buttonUrl + '" class="jsm-event-button">' + event.buttonText + '</a>';

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
            $(document).off('click', '.jsm-event-calendar-event').on('click', '.jsm-event-calendar-event', function() {
                const eventId = $(this).data('event-id');
                JSMEventCalendar.openEventModal(eventId);
            });

            // Zavření modálního okna
            $(document).off('click', '.jsm-event-modal-close').on('click', '.jsm-event-modal-close', function() {
                JSMEventCalendar.closeEventModal();
            });

            // Zavření modálního okna po kliknutí mimo obsah
            $(document).off('click', '.jsm-event-modal').on('click', '.jsm-event-modal', function(e) {
                if ($(e.target).hasClass('jsm-event-modal')) {
                    JSMEventCalendar.closeEventModal();
                }
            });

            // Zavření modálního okna po stisknutí klávesy Escape
            $(document).off('keyup').on('keyup', function(e) {
                if (e.key === 'Escape' && $('.jsm-event-modal').is(':visible')) {
                    JSMEventCalendar.closeEventModal();
                }
            });
        },

        /**
         * Otevření modálního okna s detailem události
         */
        openEventModal: function(eventId) {
            // Implementace zobrazení modálního okna s detailem události

            const $modal = $('#jsm-event-modal');
            const $modalContent = $('#jsm-event-modal-content');

            // Načítací animace
            $modalContent.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

            // Zobrazení modálního okna
            $modal.fadeIn(300);

            // AJAX požadavek pro načtení detailu události - optimalizovaná verze pro 2025
            $.ajax({
                url: jsmEventCalendar.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_event_detail',
                    event_id: eventId,
                    nonce: jsmEventCalendar.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Vykreslení detailu události
                        $modalContent.html(response.data);
                    } else {
                        $modalContent.html('<div class="jsm-event-no-events">Událost nebyla nalezena.</div>');
                    }
                },
                error: function() {
                    $modalContent.html('<div class="jsm-event-no-events">Chyba při načítání události.</div>');
                }
            });
        },

        /**
         * Zavření modálního okna
         */
        closeEventModal: function() {
            const $modal = $('#jsm-event-modal');
            $modal.fadeOut(300);
        },

        /**
         * Detekce mobilního zobrazení a přepnutí na responzivní layout
         */
        detectMobileView: function() {
            if (window.innerWidth <= 768) {
                $('.jsm-event-calendar-wrapper').addClass('jsm-mobile-view');

                // Pokud už máme vykreslený kalendář, překreslíme ho pro mobilní zobrazení
                if ($('.jsm-event-calendar-table').length) {
                    const $calendar = $('.jsm-event-calendar-wrapper');
                    const month = parseInt($calendar.data('month'));
                    const year = parseInt($calendar.data('year'));

                    if (month && year) {
                        JSMEventCalendar.updateCalendar($calendar.attr('id'), month, year);
                    }
                }
            } else {
                $('.jsm-event-calendar-wrapper').removeClass('jsm-mobile-view');

                // Pokud máme mobilní zobrazení, překreslíme ho pro desktop
                if ($('.jsm-event-calendar-list-view').length) {
                    const $calendar = $('.jsm-event-calendar-wrapper');
                    const month = parseInt($calendar.data('month'));
                    const year = parseInt($calendar.data('year'));

                    if (month && year) {
                        JSMEventCalendar.updateCalendar($calendar.attr('id'), month, year);
                    }
                }
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