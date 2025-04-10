/**
 * JavaScript for frontend event calendar - Minimalist design 2025
 */
(function($) {
    'use strict';

    // Global object for our functions
    window.JSMEventCalendar = {
        // Track window width to avoid unnecessary reloads
        lastWindowWidth: 0,
        
        init: function() {
            //console.log('Initializing JSM Event Calendar 2025 - Minimalist Edition');

            // Flag for view switching
            this.viewSwitchInProgress = false;
            
            // Store mobile state immediately
            this.isMobile = window.innerWidth <= 768;

            // Set up navigation and modals
            this.setupCalendarNavigation();
            this.setupEventModals();
            this.detectMobileView();

            // Immediate data loading on first display - important for correct initial load
            this.initializeCalendars();

            // Watch for window resize for responsive layout
            $(window).on('resize', this.handleResize.bind(this));
        },

        /**
         * Initialize all calendars on the page
         */
        initializeCalendars: function() {
            $('.jsm-event-calendar-wrapper').each(function() {
                const calendarId = $(this).attr('id');
                const month = parseInt($(this).data('month'));
                const year = parseInt($(this).data('year'));

                if (calendarId && month && year) {
                    // Force monthly view on mobile devices before any rendering
                    if (JSMEventCalendar.isMobile) {
                        $(this).data('view', 'monthly');
                        $(this).attr('data-view', 'monthly');
                        $(this).removeClass('jsm-weekly-view jsm-daily-view').addClass('jsm-monthly-view jsm-mobile-view');
                    }
                    
                    // Add slight delay for proper DOM rendering
                    setTimeout(function() {
                        // Force monthly view on mobile devices regardless of default setting
                        if (JSMEventCalendar.isMobile) {
                            JSMEventCalendar.updateCalendar(calendarId, month, year, 1, 'monthly');
                        } else {
                            JSMEventCalendar.updateCalendar(calendarId, month, year);
                        }
                    }, 50);
                }
            });
        },

        /**
         * Handle window resize with debounce to limit the number of calls
         */
        handleResize: function() {
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(function() {
                // Check if mobile state changed
                const wasMobile = JSMEventCalendar.isMobile;
                const isMobileNow = window.innerWidth <= 768;
                
                // Only detect mobile view if window width actually changed
                if (JSMEventCalendar.lastWindowWidth !== window.innerWidth) {
                    JSMEventCalendar.lastWindowWidth = window.innerWidth;
                    
                    // If mobile state changed, update it
                    if (wasMobile !== isMobileNow) {
                        JSMEventCalendar.detectMobileView();
                    }
                }
                
                // Equalize heights after resize
                JSMEventCalendar.equalizeCalendarCellHeights();
                
                // Also equalize all-day cell heights - with a slight delay to ensure content is fully rendered
                setTimeout(() => {
                    JSMEventCalendar.equalizeAllDayCellHeights();
                }, 50);
            }, 250);
        },

        /**
         * Set up calendar navigation
         */
        setupCalendarNavigation: function() {
            // Event delegation for better performance and compatibility with dynamically created elements
            $(document).off('click', '.jsm-event-calendar-prev').on('click', '.jsm-event-calendar-prev', function() {
                const $calendar = $('#' + $(this).data('calendar-id'));
                const view = $calendar.data('view') || 'monthly';

                // Remove focus after click
                $(this).blur();

                switch(view) {
                    case 'daily':
                        JSMEventCalendar.changeDay($(this).data('calendar-id'), -1);
                        break;
                    case 'weekly':
                        JSMEventCalendar.changeWeek($(this).data('calendar-id'), -1);
                        break;
                    default: // monthly
                        JSMEventCalendar.changeMonth($(this).data('calendar-id'), -1);
                        break;
                }
            });

            $(document).off('click', '.jsm-event-calendar-next').on('click', '.jsm-event-calendar-next', function() {
                const $calendar = $('#' + $(this).data('calendar-id'));
                const view = $calendar.data('view') || 'monthly';

                // Remove focus after click
                $(this).blur();

                switch(view) {
                    case 'daily':
                        JSMEventCalendar.changeDay($(this).data('calendar-id'), 1);
                        break;
                    case 'weekly':
                        JSMEventCalendar.changeWeek($(this).data('calendar-id'), 1);
                        break;
                    default: // monthly
                        JSMEventCalendar.changeMonth($(this).data('calendar-id'), 1);
                        break;
                }
            });

            $(document).off('click', '.jsm-event-calendar-today').on('click', '.jsm-event-calendar-today', function() {
                const $calendar = $('#' + $(this).data('calendar-id'));
                const view = $calendar.data('view') || 'monthly';

                // Remove focus after click
                $(this).blur();

                switch(view) {
                    case 'daily':
                        JSMEventCalendar.goToToday($(this).data('calendar-id'), 'daily');
                        break;
                    case 'weekly':
                        JSMEventCalendar.goToToday($(this).data('calendar-id'), 'weekly');
                        break;
                    default: // monthly
                        JSMEventCalendar.goToToday($(this).data('calendar-id'), 'monthly');
                        break;
                }
            });

            // View switching buttons
            $(document).off('click', '.jsm-event-calendar-view-button').on('click', '.jsm-event-calendar-view-button', function() {
                const calendarId = $(this).data('calendar-id');
                const view = $(this).data('view');

                // Remove focus after click
                $(this).blur();

                // Update active button
                $(this).siblings('.jsm-event-calendar-view-button').removeClass('active');
                $(this).addClass('active');

                JSMEventCalendar.switchCalendarView(calendarId, view);
            });
        },
        /**
         * Change day in calendar
         */
        changeDay: function(calendarId, direction) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found:', calendarId);
                return;
            }

            const currentDay = parseInt($calendar.data('day'));
            const currentMonth = parseInt($calendar.data('month'));
            const currentYear = parseInt($calendar.data('year'));

            // Create date object and add/subtract days
            const currentDate = new Date(currentYear, currentMonth - 1, currentDay);

            // Check if past navigation is allowed
            const allowPastNavigation = jsmEventCalendar.allowPastNavigation === 'yes';

            // Check if we're trying to navigate to the past
            if (direction < 0 && !allowPastNavigation) {
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time part for proper comparison

                const targetDate = new Date(currentDate);
                targetDate.setDate(targetDate.getDate() + direction);
                targetDate.setHours(0, 0, 0, 0); // Reset time part

                if (targetDate < today) {
                    // Add "disabled" button effect
                    const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');
                    $prevButton.addClass('disabled').delay(300).queue(function(next) {
                        $(this).removeClass('disabled');
                        next();
                    });
                    return; // Don't allow navigation to the past
                }
            }

            // Proceed with navigation
            currentDate.setDate(currentDate.getDate() + direction);

            // Update calendar with new date
            this.updateCalendar(
                calendarId,
                currentDate.getMonth() + 1,
                currentDate.getFullYear(),
                currentDate.getDate(),
                'daily'
            );
        },

        /**
         * Change week in calendar
         */
        changeWeek: function(calendarId, direction) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found:', calendarId);
                return;
            }

            const currentDay = parseInt($calendar.data('day'));
            const currentMonth = parseInt($calendar.data('month'));
            const currentYear = parseInt($calendar.data('year'));

            // Create date object and calculate week start
            const currentDate = new Date(currentYear, currentMonth - 1, currentDay);

            // Check if past navigation is allowed
            const allowPastNavigation = jsmEventCalendar.allowPastNavigation === 'yes';

            // Check if we're trying to navigate to the past
            if (direction < 0 && !allowPastNavigation) {
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time part for proper comparison

                const targetDate = new Date(currentDate);
                targetDate.setDate(targetDate.getDate() + (direction * 7));
                targetDate.setHours(0, 0, 0, 0); // Reset time part

                if (targetDate < today) {
                    // Add "disabled" button effect
                    const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');
                    $prevButton.addClass('disabled').delay(300).queue(function(next) {
                        $(this).removeClass('disabled');
                        next();
                    });
                    return; // Don't allow navigation to the past
                }
            }

            // Proceed with navigation
            currentDate.setDate(currentDate.getDate() + (direction * 7));

            // Update calendar with new date
            this.updateCalendar(
                calendarId,
                currentDate.getMonth() + 1,
                currentDate.getFullYear(),
                currentDate.getDate(),
                'weekly'
            );
        },

        /**
         * Change month in calendar
         */
        changeMonth: function(calendarId, direction) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found:', calendarId);
                return;
            }

            const currentMonth = parseInt($calendar.data('month'));
            const currentYear = parseInt($calendar.data('year'));

            // Get current date for limitation
            const today = new Date();
            const currentRealMonth = today.getMonth() + 1; // +1 because getMonth() returns 0-11
            const currentRealYear = today.getFullYear();

            // Check if past navigation is allowed
            const allowPastNavigation = jsmEventCalendar.allowPastNavigation === 'yes';

            // If going back, check if we're not going into the past
            if (direction < 0 && !allowPastNavigation) {
                // If we're in the current month or trying to go to the past, stop
                if ((currentYear < currentRealYear) ||
                    (currentYear === currentRealYear && currentMonth <= currentRealMonth)) {
                    //console.log("Cannot navigate to the past");
                    // Add "disabled" button effect
                    const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');
                    $prevButton.addClass('disabled').delay(300).queue(function(next) {
                        $(this).removeClass('disabled');
                        next();
                    });
                    return; // Don't allow navigation to the past
                }
            }

            let newMonth = currentMonth + direction;
            let newYear = currentYear;

            // Handle year transitions
            if (newMonth > 12) {
                newMonth = 1;
                newYear++;
            } else if (newMonth < 1) {
                newMonth = 12;
                newYear--;
            }

            // Update calendar
            this.updateCalendar(calendarId, newMonth, newYear);
        },

        /**
         * Go to current date
         */
        goToToday: function(calendarId, view = 'monthly') {
            const today = new Date();
            const month = today.getMonth() + 1; // JavaScript counts months from 0
            const year = today.getFullYear();
            const day = today.getDate();

            this.updateCalendar(calendarId, month, year, day, view);
        },
        /**
         * Switch calendar view (monthly, weekly, daily)
         */
        switchCalendarView: function(calendarId, view) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found for view switch:', calendarId);
                return;
            }

            // Check if we're on mobile - if so, force monthly view
            if (this.isMobile) {
                view = 'monthly';
            }

            const currentMonth = parseInt($calendar.data('month'));
            const currentYear = parseInt($calendar.data('year'));

            // Použijeme dnešní datum pokud přecházíme na denní nebo týdenní pohled
            let currentDay;
            if ((view === 'daily' || view === 'weekly') && $calendar.data('view') === 'monthly') {
                // Při přechodu z měsíčního pohledu na denní nebo týdenní použijeme dnešní datum
                currentDay = new Date().getDate();
            } else {
                // Jinak použijeme aktuální den z kalendáře nebo dnešní den
                currentDay = parseInt($calendar.data('day') || new Date().getDate());
            }

            const currentView = $calendar.data('view');
            const showList = $calendar.data('show-list');
            const category = $calendar.data('category');

            // If the view hasn't changed, don't do anything
            if (currentView === view) {
                return;
            }

            // Use a flag to prevent multiple concurrent AJAX requests
            if (this.viewSwitchInProgress) {
                return;
            }
            this.viewSwitchInProgress = true;

            // Místo kompletního překreslení HTML přes AJAX, zachováme původní kontejner a upravíme obsah
            // 1. Skryjeme existující obsah a zobrazíme loading
            const $calendarTable = $('#' + calendarId + '-table');

            // Vytvoříme overlay pro loading, který zakryje celý kalendář bez změny velikosti
            const $loadingOverlay = $('<div class="jsm-loading-overlay"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');
            $calendar.append($loadingOverlay);

            // Aktualizace atributů a tříd
            $calendar.data('view', view);
            $calendar.attr('data-view', view);
            $calendar.removeClass('jsm-monthly-view jsm-weekly-view jsm-daily-view');
            $calendar.addClass('jsm-' + view + '-view');

            // Update navigation button texts based on view
            const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');
            const $todayButton = $('.jsm-event-calendar-today[data-calendar-id="' + calendarId + '"]');
            const $nextButton = $('.jsm-event-calendar-next[data-calendar-id="' + calendarId + '"]');

            switch(view) {
                case 'daily':
                    $prevButton.text(jsmEventCalendar.i18n.previousDay || 'Previous Day');
                    $todayButton.text(jsmEventCalendar.i18n.today || 'Today');
                    $nextButton.text(jsmEventCalendar.i18n.nextDay || 'Next Day');
                    break;
                case 'weekly':
                    $prevButton.text(jsmEventCalendar.i18n.previousWeek || 'Previous Week');
                    $todayButton.text(jsmEventCalendar.i18n.thisWeek || 'This Week');
                    $nextButton.text(jsmEventCalendar.i18n.nextWeek || 'Next Week');
                    break;
                default: // monthly
                    $prevButton.text(jsmEventCalendar.i18n.previous || 'Previous');
                    $todayButton.text(jsmEventCalendar.i18n.today || 'Today');
                    $nextButton.text(jsmEventCalendar.i18n.nextMonth || 'Next Month');
                    break;
            }

            // Připravíme data pro AJAX podle typu pohledu
            let ajaxData = {
                action: 'get_events_for_calendar',
                month: currentMonth,
                year: currentYear,
                day: currentDay,
                view: view,
                category: category,
                nonce: jsmEventCalendar.nonce,
                cache: false
            };

            // Výpočet rozsahu dat pro jednotlivé pohledy
            if (view === 'weekly') {
                // Dostaneme datum prvního dne týdne (pondělí)
                const weekStartDate = new Date(currentYear, currentMonth - 1, currentDay);
                const dayOfWeek = weekStartDate.getDay() || 7; // Convert 0 (Sunday) to 7
                const mondayOffset = 1 - dayOfWeek; // Calculate days to Monday (1 - day of week)

                const weekStart = new Date(weekStartDate);
                weekStart.setDate(weekStartDate.getDate() + mondayOffset);

                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);

                // Přidáme do AJAX dat informace o rozsahu týdne
                ajaxData.start_date = this.formatDate(weekStart);
                ajaxData.end_date = this.formatDate(weekEnd);
            }
            // Pro denní pohled přidáme jen konkrétní datum
            else if (view === 'daily') {
                const selectedDate = new Date(currentYear, currentMonth - 1, currentDay);
                ajaxData.start_date = this.formatDate(selectedDate);
                ajaxData.end_date = this.formatDate(selectedDate);
            }
            // Pro měsíční pohled přidáme rozsah celého měsíce
            else if (view === 'monthly') {
                // První den v měsíci
                const firstDay = new Date(currentYear, currentMonth - 1, 1);

                // Poslední den v měsíci - přejdeme na další měsíc a vrátíme se o jeden den zpět
                const lastDay = new Date(currentYear, currentMonth, 0);

                ajaxData.start_date = this.formatDate(firstDay);
                ajaxData.end_date = this.formatDate(lastDay);
            }

            // 2. Načítáme data bez kompletní změny HTML struktury
            $.ajax({
                url: jsmEventCalendar.ajaxurl,
                type: 'GET',
                data: ajaxData,
                success: function(response) {
                    // Reset the flag
                    JSMEventCalendar.viewSwitchInProgress = false;

                    if (response && response.success) {
                        const events = response.data;

                        // Update calendar title based on view
                        let titleText = '';

                        switch(view) {
                            case 'daily':
                                const dayDate = new Date(currentYear, currentMonth - 1, currentDay);
                                const weekdayName = jsmEventCalendar.i18n.weekdays[dayDate.getDay()];
                                const formattedDate = dayDate.toLocaleDateString();
                                titleText = weekdayName + ', ' + formattedDate;
                                break;
                            case 'weekly':
                                const weekStartDate = new Date(currentYear, currentMonth - 1, currentDay);
                                const dayOfWeek = weekStartDate.getDay() || 7; // Convert 0 (Sunday) to 7
                                const mondayOffset = 1 - dayOfWeek; // Calculate days to Monday (1 - day of week)

                                const weekStart = new Date(weekStartDate);
                                weekStart.setDate(weekStartDate.getDate() + mondayOffset);

                                const weekEnd = new Date(weekStart);
                                weekEnd.setDate(weekStart.getDate() + 6);

                                const weekStartFormatted = weekStart.toLocaleDateString();
                                const weekEndFormatted = weekEnd.toLocaleDateString();

                                titleText = weekStartFormatted + ' - ' + weekEndFormatted;
                                break;
                            default: // monthly
                                const monthName = jsmEventCalendar.i18n.months[currentMonth - 1];
                                titleText = monthName + ' ' + currentYear;
                                break;
                        }

                        // Update calendar title
                        $('#' + calendarId + '-title').text(titleText);

                        // Render appropriate calendar view without loading the complete HTML template
                        switch(view) {
                            case 'daily':
                                JSMEventCalendar.renderDailyCalendarContent($calendarTable, currentYear, currentMonth, currentDay, events);
                                break;
                            case 'weekly':
                                JSMEventCalendar.renderWeeklyCalendarContent($calendarTable, currentYear, currentMonth, currentDay, events);
                                break;
                            default: // monthly
                                JSMEventCalendar.renderCalendar($calendarTable, currentMonth, currentYear, events);
                                break;
                        }

                        // Update event list if displayed
                        if (showList === 'yes') {
                            JSMEventCalendar.renderEventList($('#' + calendarId + '-list'), events);
                        }

                        // Update buttons to reflect active view
                        $('.jsm-event-calendar-view-button').removeClass('active');
                        $('.jsm-event-calendar-view-button[data-view="' + view + '"]').addClass('active');

                        // Re-setup event handlers
                        JSMEventCalendar.setupEventModals();

                        // Equalize all-day cell heights if in weekly view
                        if (view === 'weekly') {
                            setTimeout(() => {
                                JSMEventCalendar.equalizeAllDayCellHeights();
                            }, 200);
                        }
                    } else {
                        $calendarTable.html('<div class="jsm-event-no-events">Error loading calendar: ' + (response ? response.data : 'Invalid response') + '</div>');
                    }

                    // Remove loading overlay
                    $loadingOverlay.remove();
                },
                error: function(xhr, status, error) {
                    // Reset the flag
                    JSMEventCalendar.viewSwitchInProgress = false;

                    console.error('AJAX error:', error);
                    $calendarTable.html('<div class="jsm-event-no-events">Error loading calendar. Please try again.</div>');

                    // Remove loading overlay
                    $loadingOverlay.remove();
                }
            });
        },
        /**
         * Render daily calendar content - zcela přepracováno
         */
        renderDailyCalendarContent: function($calendarTable, year, month, day, events) {
            // Check if the structure already exists or create it
            if (!$calendarTable.find('.jsm-daily-timeline').length) {
                $calendarTable.html('<div class="jsm-daily-timeline"></div>');
            }

            const $timeline = $calendarTable.find('.jsm-daily-timeline');
            $timeline.empty(); // Clear existing content

            // Create date object for current day - FIX: Correctly create date object for day display
            const currentDate = new Date(year, month - 1, day);
            // Get correct day name using the i18n weekdays array
            // JavaScript getDay() returns 0-6 (Sunday-Saturday)
            const dayIndex = currentDate.getDay();
            const weekdayName = jsmEventCalendar.i18n.weekdays[dayIndex === 0 ? 6 : dayIndex - 1]; // Adjust to European format
            const formattedDate = currentDate.toLocaleDateString();

            // Update calendar title if it exists
            const $calendarTitle = $('#' + $calendarTable.closest('.jsm-event-calendar-wrapper').attr('id') + '-title');
            if ($calendarTitle.length) {
                $calendarTitle.text(weekdayName + ', ' + formattedDate);
            }

            // First, add all-day events section
            const allDayEvents = this.getAllDayEvents(events, year, month, day);
            if (allDayEvents.length > 0) {
                const $allDaySlot = $('<div class="jsm-daily-time-slot jsm-all-day-slot"></div>');
                $allDaySlot.append('<div class="jsm-daily-time-label jsm-all-day-label">' + (jsmEventCalendar.i18n.allDay || 'All Day') + '</div>');

                const $allDayContainer = $('<div class="jsm-daily-events-container" data-hour="all-day"></div>');

                // Sort all-day events
                const sortedAllDayEvents = this.sortEventsByTime(allDayEvents);

                // If only one event, use full width
                if (sortedAllDayEvents.length === 1) {
                    $allDayContainer.append(
                        this.renderEventInDailyCell(
                            sortedAllDayEvents[0],
                            true,
                            {
                                left: '0%',
                                width: '98%', // 2% for gap
                                position: 'absolute',
                                zIndex: 5
                            }
                        )
                    );
                } else {
                    // Multiple events - calculate width based on number of events (with min width)
                    const totalEvents = sortedAllDayEvents.length;
                    const eventWidth = Math.max(90 / totalEvents, 40); // Min 40% width for readability

                    for (let i = 0; i < sortedAllDayEvents.length; i++) {
                        // Position events side by side
                        const leftOffset = (i * (90 / totalEvents)) + '%';
                        const widthValue = eventWidth - 2 + '%'; // 2% for gap

                        $allDayContainer.append(
                            this.renderEventInDailyCell(
                                sortedAllDayEvents[i],
                                true,
                                {
                                    left: leftOffset,
                                    width: widthValue,
                                    position: 'absolute',
                                    zIndex: 5 + i
                                }
                            )
                        );
                    }
                }

                $allDaySlot.append($allDayContainer);
                $timeline.append($allDaySlot);
            } else {
                // Add empty all-day slot with minimum height
                const $allDaySlot = $('<div class="jsm-daily-time-slot jsm-all-day-slot" style="min-height: 60px;"></div>');
                $allDaySlot.append('<div class="jsm-daily-time-label jsm-all-day-label">' + (jsmEventCalendar.i18n.allDay || 'All Day') + '</div>');
                $allDaySlot.append('<div class="jsm-daily-events-container" data-hour="all-day"></div>');
                $timeline.append($allDaySlot);
            }

            // Připravit časové sloty - 24 hodin po 15 minutách = 96 slotů
            const timeSlots = [];
            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 15) {
                    const slotIndex = (hour * 4) + (minute / 15);
                    const timeDisplay = this.formatTime(hour, minute);

                    // Vytvořit časový slot
                    const $timeSlot = $('<div class="jsm-daily-time-slot jsm-time-slot-15min" data-hour="' + hour + '" data-minute="' + minute + '" data-slot="' + slotIndex + '"></div>');
                    $timeSlot.append('<div class="jsm-daily-time-label">' + timeDisplay + '</div>');
                    $timeSlot.append('<div class="jsm-daily-events-container" data-slot="' + slotIndex + '"></div>');

                    timeSlots.push($timeSlot);
                    $timeline.append($timeSlot);
                }
            }

            // Připravit události pro tento den
            const timeEvents = this.prepareEventsForTimeView(events, year, month, day);

            // Rozmístit události do příslušných časových slotů
            if (timeEvents.length > 0) {
                // Najít překrývající se události a určit sloupce
                const eventColumns = this.calculateEventColumns(timeEvents);

                // Vykreslit každou událost do časového slotu, kde začíná
                for (let i = 0; i < timeEvents.length; i++) {
                    const event = timeEvents[i];
                    const columnInfo = eventColumns[i];
                        
                    if (!columnInfo) continue;
                        
                    const column = columnInfo.column;
                    const group = columnInfo.group;
                        
                    // Zjistit počet sloupců v této skupině
                    const totalColumnsInGroup = (eventColumns.groupMaxColumns[group] || 0) + 1;
                        
                    // Pokud je událost sama ve skupině, použít plnou šířku
                    const isAlone = eventColumns.groups[group].length === 1;
                        
                    // Pro každou událost vypočítat pozici
                    let leftOffset, widthValue;
                        
                    if (isAlone) {
                        // Událost je sama, použít plnou šířku
                        leftOffset = '0%';
                        widthValue = '98%'; // 2% mezera
                    } else {
                        // Událost se překrývá s jinými, rozdělit šířku
                        leftOffset = (column * (100 / totalColumnsInGroup)) + '%';
                        widthValue = (100 / totalColumnsInGroup) - 2 + '%'; // 2% mezera
                    }

                    // Získat správný slot pro začátek události
                    const startSlotIndex = event._startTimeSlot;
                    if (startSlotIndex >= 0 && startSlotIndex < timeSlots.length) {
                        const $container = timeSlots[startSlotIndex].find('.jsm-daily-events-container');

                        // Výška události - v minutách krát poměr výšky (např. 22px / 15min)
                        const heightPixels = event._durationMinutes * (22 / 15);

                        // Vykreslit událost s vypočtenými parametry
                        $container.append(
                            this.renderEventInDailyCell(
                                event,
                                false,
                                {
                                    left: leftOffset,
                                    width: widthValue,
                                    height: heightPixels + 'px',
                                    position: 'absolute',
                                    zIndex: 5 + column
                                }
                            )
                        );
                    }
                }
            }

            // Add tooltip functionality for events
            setTimeout(() => {
                $('.jsm-daily-event').each(function() {
                    const $event = $(this);
                    const title = $event.data('title');
                    const time = $event.data('time') || '';
                    const date = $event.data('date') || '';

                    // Create tooltip content
                    const tooltipContent = `${title} - ${date} ${time}`;

                    // Add title attribute for native tooltip
                    $event.attr('title', tooltipContent);
                });
            }, 200);
        },
        /**
         * Vypočítá sloupce pro události, aby se nepřekrývaly
         * Vrací pole index sloupce pro každou událost a informace o překrývajících se skupinách
         */
        calculateEventColumns: function(events) {
            if (!events || events.length === 0) {
                return [];
            }

            // Příprava pole výsledků
            const columns = [];
            
            // Vytvoříme skupiny překrývajících se událostí
            const overlapGroups = [];
            
            // Pro každou událost
            for (let i = 0; i < events.length; i++) {
                const currentEvent = events[i];
                
                // Najít všechny události, které se překrývají s aktuální
                const overlapping = [];
                
                for (let j = 0; j < i; j++) {
                    const previousEvent = events[j];
                    
                    // Kontrola překrytí
                    if (
                        (currentEvent._startTimeSlot <= previousEvent._endTimeSlot) &&
                        (currentEvent._endTimeSlot >= previousEvent._startTimeSlot)
                    ) {
                        overlapping.push(j);
                    }
                }
                
                // Pokud nemá žádné překrytí, vytvoříme novou skupinu
                if (overlapping.length === 0) {
                    overlapGroups.push([i]);
                    columns[i] = { column: 0, group: overlapGroups.length - 1 };
                    continue;
                }
                
                // Najít skupinu, do které patří
                let foundGroup = false;
                for (let g = 0; g < overlapGroups.length; g++) {
                    const group = overlapGroups[g];
                    
                    // Kontrola, zda se překrývá s nějakou událostí v této skupině
                    const hasOverlap = overlapping.some(idx => group.includes(idx));
                    
                    if (hasOverlap) {
                        // Přidat do existující skupiny
                        group.push(i);
                        foundGroup = true;
                        
                        // Najít první volný sloupec v této skupině
                        let column = 0;
                        let columnTaken;
                        
                        do {
                            columnTaken = false;
                            for (let j = 0; j < overlapping.length; j++) {
                                if (group.includes(overlapping[j]) && 
                                    columns[overlapping[j]] && 
                                    columns[overlapping[j]].column === column) {
                                    columnTaken = true;
                                    column++;
                                    break;
                                }
                            }
                        } while (columnTaken);
                        
                        columns[i] = { column: column, group: g };
                        break;
                    }
                }
                
                // Pokud nenalezena žádná skupina, vytvoříme novou
                if (!foundGroup) {
                    overlapGroups.push([i]);
                    columns[i] = { column: 0, group: overlapGroups.length - 1 };
                }
            }
            
            // Vypočítat maximální počet sloupců pro každou skupinu
            const groupMaxColumns = {};
            for (let i = 0; i < events.length; i++) {
                if (columns[i]) {
                    const group = columns[i].group;
                    const column = columns[i].column;
                    
                    if (!groupMaxColumns[group] || column > groupMaxColumns[group]) {
                        groupMaxColumns[group] = column;
                    }
                }
            }
            
            // Přidat informace o skupinách a maximálních sloupcích
            columns.groups = overlapGroups;
            columns.groupMaxColumns = groupMaxColumns;
            
            return columns;
        },
        /**
         * Get all-day events for a specific day
         */
        getAllDayEvents: function(events, year, month, day) {
            if (!events || !Array.isArray(events)) {
                return [];
            }

            const dateString = this.pad(year) + '-' + this.pad(month) + '-' + this.pad(day);
            const allDayEvents = [];

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
                if (!event || !event.startDate) continue;

                const startDate = event.startDate;
                const endDate = event.endDate || event.startDate;

                // Check if event belongs to this day and is all-day
                if (dateString >= startDate && dateString <= endDate && event.allDay) {
                    allDayEvents.push(event);
                }
            }

            return allDayEvents;
        },
        /**
         * Příprava událostí pro denní a týdenní pohled - zcela nová funkce
         * Vrací události seřazené podle začátku, každá událost se objeví jen jednou
         */
        prepareEventsForTimeView: function(events, year, month, day) {
            if (!events || !Array.isArray(events)) {
                return [];
            }

            const dateString = this.pad(year) + '-' + this.pad(month) + '-' + this.pad(day);
            const dayEvents = [];

            // Získáme všechny události pro tento den (kromě celodenních)
            for (let i = 0; i < events.length; i++) {
                const event = events[i];
                if (!event || !event.startDate) continue;

                const startDate = event.startDate;
                const endDate = event.endDate || event.startDate;

                // Kontrola, zda událost patří do tohoto dne
                if (dateString >= startDate && dateString <= endDate) {
                    // Přeskočit celodenní události
                    if (event.allDay) {
                        continue;
                    }

                    // Zpracování začátku a konce události
                    if (event.timeDisplay) {
                        let eventStartMinutes = -1;
                        let eventEndMinutes = -1;

                        // Extrahovat začátek a konec události
                        const timeMatch = event.timeDisplay.match(/(\d{1,2}):(\d{2})(?:\s*-\s*(\d{1,2}):(\d{2}))?/);
                        if (timeMatch) {
                            // Začátek události
                            const startHour = parseInt(timeMatch[1]);
                            const startMinute = parseInt(timeMatch[2]);
                            eventStartMinutes = startHour * 60 + startMinute;

                            // Konec události - pokud existuje, jinak předpokládáme +1 hodinu
                            if (timeMatch[3] && timeMatch[4]) {
                                const endHour = parseInt(timeMatch[3]);
                                const endMinute = parseInt(timeMatch[4]);
                                eventEndMinutes = endHour * 60 + endMinute;
                            } else {
                                // Default trvání 1 hodina, pokud není konec specifikován
                                eventEndMinutes = eventStartMinutes + 60;
                            }

                            // Vytvoříme kopii události s přidanými údaji o čase
                            const eventCopy = { ...event };
                            eventCopy._startMinutes = eventStartMinutes;
                            eventCopy._startTimeSlot = Math.floor(eventStartMinutes / 15); // Slot pro daný čas (0-95)
                            eventCopy._endMinutes = eventEndMinutes;
                            eventCopy._endTimeSlot = Math.floor(eventEndMinutes / 15);
                            eventCopy._durationMinutes = eventEndMinutes - eventStartMinutes;

                            dayEvents.push(eventCopy);
                        }
                    }
                }
            }

            // Seřadit události podle začátku
            return dayEvents.sort((a, b) => a._startMinutes - b._startMinutes);
        },
        /**
         * Get events for a specific 15-minute time slot - zcela přepracováno
         */
        getEventsForTimeSlot: function(events, year, month, day, hour, minute) {
            if (!events || !Array.isArray(events)) {
                return [];
            }

            const dateString = this.pad(year) + '-' + this.pad(month) + '-' + this.pad(day);
            const slotEvents = [];

            // Aktuální slot začíná v X:00, X:15, X:30 nebo X:45
            const slotStartMinutes = hour * 60 + minute;

            // Aktuální slot končí o 15 minut později
            const slotEndMinutes = slotStartMinutes + 15;

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
                if (!event || !event.startDate) continue;

                const startDate = event.startDate;
                const endDate = event.endDate || event.startDate;

                // Kontrola, zda událost patří do tohoto dne
                if (dateString >= startDate && dateString <= endDate) {
                    // Přeskočit celodenní události
                    if (event.allDay) {
                        continue;
                    }

                    // Kontrola, zda událost zasahuje do tohoto časového slotu
                    if (event.timeDisplay) {
                        let eventStartMinutes = -1;
                        let eventEndMinutes = -1;

                        // Extrahovat začátek a konec události
                        const timeMatch = event.timeDisplay.match(/(\d{1,2}):(\d{2})(?:\s*-\s*(\d{1,2}):(\d{2}))?/);
                        if (timeMatch) {
                            // Začátek události
                            const startHour = parseInt(timeMatch[1]);
                            const startMinute = parseInt(timeMatch[2]);
                            eventStartMinutes = startHour * 60 + startMinute;

                            // Konec události - pokud existuje, jinak předpokládáme +1 hodinu
                            if (timeMatch[3] && timeMatch[4]) {
                                const endHour = parseInt(timeMatch[3]);
                                const endMinute = parseInt(timeMatch[4]);
                                eventEndMinutes = endHour * 60 + endMinute;
                            } else {
                                // Default trvání 1 hodina, pokud není konec specifikován
                                eventEndMinutes = eventStartMinutes + 60;
                            }

                            // Podmínky pro zařazení události do slotu:
                            // 1. Událost začíná přesně v tomto slotu, NEBO
                            // 2. Událost začala dříve, ale ještě neskončila (pokračuje přes tento slot)
                            if (
                                // Začátek události padá přesně do tohoto slotu
                                (eventStartMinutes >= slotStartMinutes && eventStartMinutes < slotEndMinutes) ||
                                // Událost už běží a zasahuje do tohoto slotu
                                (eventStartMinutes < slotStartMinutes && eventEndMinutes > slotStartMinutes)
                            ) {
                                // Zkopírujeme událost a přidáme informace o trvání
                                const eventCopy = { ...event };
                                eventCopy._startMinutes = eventStartMinutes;
                                eventCopy._endMinutes = eventEndMinutes;
                                eventCopy._duration = eventEndMinutes - eventStartMinutes;
                                slotEvents.push(eventCopy);
                            }
                        }
                    }
                }
            }

            return slotEvents;
        },

        /**
         * Improved function to equalize all-day cell heights
         */
        equalizeAllDayCellHeights: function() {
            // Skip on mobile devices
            if (window.innerWidth <= 768) {
                return;
            }

            const $allDayCells = $('.jsm-weekly-all-day-cell');
            const $allDayLabels = $('.jsm-weekly-time-label.jsm-all-day-label');

            if ($allDayCells.length === 0) return;

            // Reset height for accurate measurement
            $allDayCells.css('height', 'auto');
            $allDayLabels.css('height', 'auto');

            // Find maximum height
            let maxHeight = 0;
            $allDayCells.each(function() {
                const height = $(this).outerHeight();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            // Also check the label height
            $allDayLabels.each(function() {
                const height = $(this).outerHeight();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            // Ensure minimum height
            maxHeight = Math.max(maxHeight, 80);

            // Apply same height to all cells and labels
            if (maxHeight > 0) {
                $allDayCells.css('height', maxHeight + 'px');
                $allDayLabels.css('height', maxHeight + 'px');
            }
        },

        /**
         * Render weekly calendar content - zcela přepracováno pro správné zobrazení událostí
         */
        renderWeeklyCalendarContent: function($calendarTable, year, month, day, events) {
            // Create a date object for the reference day
            const refDate = new Date(year, month - 1, day);

            // Get the day of the week (0 = Sunday, 6 = Saturday)
            let dayOfWeek = refDate.getDay();

            // Adjust to make Monday the first day (0 = Monday, 6 = Sunday)
            dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1;

            // Calculate the Monday of the week
            const mondayDate = new Date(refDate);
            mondayDate.setDate(refDate.getDate() - dayOfWeek);

            // Get today's date for comparison
            const today = new Date();
            const todayStr = this.formatDate(today);

            // Check if the structure already exists or create it
            if (!$calendarTable.find('.jsm-weekly-grid').length) {
                $calendarTable.html('<div class="jsm-weekly-grid"><div class="jsm-weekly-header"></div><div class="jsm-weekly-body"></div></div>');
            }

            const $grid = $calendarTable.find('.jsm-weekly-grid');
            const $header = $grid.find('.jsm-weekly-header');
            const $body = $grid.find('.jsm-weekly-body');

            // Clear existing content
            $header.empty();
            $body.empty();

            // Add time column header
            $header.append('<div class="jsm-weekly-time-column">&nbsp;</div>');

            // Generate weekday headers
            const weekDays = [];
            for (let i = 0; i < 7; i++) {
                const dayDate = new Date(mondayDate);
                dayDate.setDate(mondayDate.getDate() + i);

                const dayStr = this.formatDate(dayDate);
                const isToday = dayStr === todayStr;

                const dayName = jsmEventCalendar.i18n.weekdaysShort[i];
                const dayNum = dayDate.getDate();

                let headerClass = 'jsm-weekly-day-header';
                if (isToday) {
                    headerClass += ' today';
                }

                const $dayHeader = $('<div class="' + headerClass + '" data-date="' + dayStr + '"></div>');
                $dayHeader.append('<div class="jsm-weekly-day-name">' + dayName + '</div>');
                $dayHeader.append('<div class="jsm-weekly-day-number">' + dayNum + '</div>');
                $header.append($dayHeader);

                weekDays.push({
                    date: dayDate,
                    dateStr: dayStr,
                    isToday: isToday
                });
            }

            // Time column
            const $timeColumn = $('<div class="jsm-weekly-time-column"></div>');

            // Add all-day row label with fixed minimum height
            $timeColumn.append('<div class="jsm-weekly-time-label jsm-all-day-label" style="min-height: 80px;">' +
                              (jsmEventCalendar.i18n.allDay || 'All Day') + '</div>');

            // Generate time labels - 96 15-minutových časových slotů
            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 15) {
                    const slotIndex = (hour * 4) + (minute / 15);
                    const timeDisplay = this.formatTime(hour, minute);
                    $timeColumn.append('<div class="jsm-weekly-time-label" data-hour="' + hour + '" data-minute="' + minute + '" data-slot="' + slotIndex + '">' + timeDisplay + '</div>');
                }
            }

            $body.append($timeColumn);

            // Day columns
            for (let dayIdx = 0; dayIdx < 7; dayIdx++) {
                const dayInfo = weekDays[dayIdx];
                const dayYear = dayInfo.date.getFullYear();
                const dayMonth = dayInfo.date.getMonth() + 1;
                const dayDate = dayInfo.date.getDate();

                let dayClass = 'jsm-weekly-day-column';
                if (dayInfo.isToday) {
                    dayClass += ' today';
                }

                const $dayColumn = $('<div class="' + dayClass + '" data-date="' + dayInfo.dateStr + '"></div>');

                // All-day events cell
                const $allDayCell = $('<div class="jsm-weekly-all-day-cell" data-hour="all-day" style="min-height: 80px;"></div>');

                // Get all-day events for this day
                const allDayEvents = this.getAllDayEvents(
                    events,
                    dayYear,
                    dayMonth,
                    dayDate
                );

                // Sort all-day events
                const sortedAllDayEvents = this.sortEventsByTime(allDayEvents);

                if (sortedAllDayEvents.length > 0) {
                    // Adjust height based on number of events
                    if (sortedAllDayEvents.length > 1) {
                        $allDayCell.css('min-height', (80 + (sortedAllDayEvents.length - 1) * 25) + 'px');
                    }

                    // If only one event, use full width
                    if (sortedAllDayEvents.length === 1) {
                        $allDayCell.append(
                            this.renderEventInDailyCell(
                                sortedAllDayEvents[0],
                                true,
                                {
                                    left: '0%',
                                    width: '98%', // 2% for gap
                                    position: 'absolute',
                                    zIndex: 5
                                }
                            )
                        );
                    } else {
                        // Multiple events - calculate width based on number of events
                        const eventWidth = Math.max(90 / sortedAllDayEvents.length, 40); // Min 40% width

                        for (let i = 0; i < sortedAllDayEvents.length; i++) {
                            const leftOffset = (i * (90 / sortedAllDayEvents.length)) + '%';
                            const widthValue = eventWidth - 2 + '%'; // 2% gap

                            $allDayCell.append(
                                this.renderEventInDailyCell(
                                    sortedAllDayEvents[i],
                                    true,
                                    {
                                        left: leftOffset,
                                        width: widthValue,
                                        position: 'absolute',
                                        zIndex: 5 + i
                                    }
                                )
                            );
                        }
                    }
                }

                $dayColumn.append($allDayCell);

                // Create time slots - 96 slots
                const $timeSlots = [];
                for (let hour = 0; hour < 24; hour++) {
                    for (let minute = 0; minute < 60; minute += 15) {
                        const slotIndex = (hour * 4) + (minute / 15);

                        let slotClass = 'jsm-weekly-hour-cell jsm-time-slot-15min';
                        if (minute === 0) {
                            slotClass += ' jsm-hour-start';
                        }

                        const $slot = $('<div class="' + slotClass + '" data-hour="' + hour + '" data-minute="' + minute + '" data-slot="' + slotIndex + '"></div>');
                        $timeSlots.push($slot);
                        $dayColumn.append($slot);
                    }
                }

                // Připravit události pro tento den
                const timeEvents = this.prepareEventsForTimeView(events, dayYear, dayMonth, dayDate);

                // Rozmístit události
                if (timeEvents.length > 0) {
                    // Najít překrývající se události a určit sloupce
                    const eventColumns = this.calculateEventColumns(timeEvents);

                    // Vykreslit každou událost
                    for (let i = 0; i < timeEvents.length; i++) {
                        const event = timeEvents[i];
                        const columnInfo = eventColumns[i];
                        
                        if (!columnInfo) continue;
                        
                        const column = columnInfo.column;
                        const group = columnInfo.group;
                        
                        // Zjistit počet sloupců v této skupině
                        const totalColumnsInGroup = (eventColumns.groupMaxColumns[group] || 0) + 1;
                        
                        // Pokud je událost sama ve skupině, použít plnou šířku
                        const isAlone = eventColumns.groups[group].length === 1;
                        
                        // Pro každou událost vypočítat pozici
                        let leftOffset, widthValue;
                        
                        if (isAlone) {
                            // Událost je sama, použít plnou šířku
                            leftOffset = '0%';
                            widthValue = '98%'; // 2% mezera
                        } else {
                            // Událost se překrývá s jinými, rozdělit šířku
                            leftOffset = (column * (100 / totalColumnsInGroup)) + '%';
                            widthValue = (100 / totalColumnsInGroup) - 2 + '%'; // 2% mezera
                        }

                        // Získat správný slot pro začátek události
                        const startSlotIndex = event._startTimeSlot;
                        if (startSlotIndex >= 0 && startSlotIndex < $timeSlots.length) {
                            const $slot = $timeSlots[startSlotIndex];

                            // Výška události - v minutách krát poměr výšky (např. 22px / 15min)
                            const heightPixels = event._durationMinutes * (22 / 15);

                            // Vykreslit událost s vypočtenými parametry
                            $slot.append(
                                this.renderEventInDailyCell(
                                    event,
                                    false,
                                    {
                                        left: leftOffset,
                                        width: widthValue,
                                        height: heightPixels + 'px',
                                        position: 'absolute',
                                        zIndex: 5 + column
                                    }
                                )
                            );
                        }
                    }
                }

                $body.append($dayColumn);
            }

            // Equalize heights of all-day cells after rendering
            setTimeout(() => {
                this.equalizeAllDayCellHeights();
                
                // Force a second equalization after a short delay to handle any dynamic content
                setTimeout(() => {
                    this.equalizeAllDayCellHeights();
                }, 100);

                // Add tooltips to all events
                $('.jsm-daily-event, .jsm-event-calendar-event').each(function() {
                    const $event = $(this);
                    const title = $event.data('title');
                    const time = $event.data('time') || '';
                    const date = $event.data('date') || '';

                    // Create tooltip content
                    const tooltipContent = `${title} - ${date} ${time}`;

                    // Add title attribute for native tooltip
                    $event.attr('title', tooltipContent);
                });
            }, 200);
        },

        /**
         * Update calendar via AJAX - optimized for speed
         */
        updateCalendar: function(calendarId, month, year, day = new Date().getDate(), view = null) {
            const $calendar = $('#' + calendarId);
            if (!$calendar.length) {
                console.error('Calendar not found for update:', calendarId);
                return;
            }

            const $calendarTable = $('#' + calendarId + '-table');
            const $calendarTitle = $('#' + calendarId + '-title');
            const showList = $calendar.data('show-list');
            const category = $calendar.data('category');

            // If view is not specified, use the current view from the calendar
            if (view === null) {
                view = $calendar.data('view') || 'monthly';
            }
            
            // Always force monthly view on mobile devices
            if (this.isMobile) {
                view = 'monthly';
            }

            // Show loading animation
            $calendarTable.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

            // Připravíme data pro AJAX podle typu pohledu
            let ajaxData = {
                action: 'get_events_for_calendar',
                month: month,
                year: year,
                day: day,
                view: view,
                category: category,
                nonce: jsmEventCalendar.nonce,
                cache: false // Force bypass browser cache
            };

            // Výpočet rozsahu dat pro jednotlivé pohledy
            if (view === 'weekly') {
                // Dostaneme datum prvního dne týdne (pondělí)
                const weekStartDate = new Date(year, month - 1, day);
                const dayOfWeek = weekStartDate.getDay() || 7; // Convert 0 (Sunday) to 7
                const mondayOffset = 1 - dayOfWeek; // Calculate days to Monday (1 - day of week)

                const weekStart = new Date(weekStartDate);
                weekStart.setDate(weekStartDate.getDate() + mondayOffset);

                const weekEnd = new Date(weekStart);
                weekEnd.setDate(weekStart.getDate() + 6);

                // Přidáme do AJAX dat informace o rozsahu týdne
                ajaxData.start_date = this.formatDate(weekStart);
                ajaxData.end_date = this.formatDate(weekEnd);
            }
            // Pro denní pohled přidáme jen konkrétní datum
            else if (view === 'daily') {
                const selectedDate = new Date(year, month - 1, day);
                ajaxData.start_date = this.formatDate(selectedDate);
                ajaxData.end_date = this.formatDate(selectedDate);
            }
            // Pro měsíční pohled přidáme rozsah celého měsíce
            else if (view === 'monthly') {
                // První den v měsíci
                const firstDay = new Date(year, month - 1, 1);

                // Poslední den v měsíci - přejdeme na další měsíc a vrátíme se o jeden den zpět
                const lastDay = new Date(year, month, 0);

                ajaxData.start_date = this.formatDate(firstDay);
                ajaxData.end_date = this.formatDate(lastDay);
            }

            // AJAX request to backend - optimized for faster loading
            $.ajax({
                url: jsmEventCalendar.ajaxurl,
                type: 'GET',
                data: ajaxData,
                success: function(response) {
                    if (response && response.success) {
                        const events = response.data;

                        // Update month, year, and day in data attributes
                        $calendar.data('month', month);
                        $calendar.data('year', year);
                        $calendar.data('day', day);
                        $calendar.data('view', view);

                        // Update calendar title based on view
                        let titleText = '';

                        switch(view) {
                            case 'daily':
                                const dayDate = new Date(year, month - 1, day);
                                const weekdayName = jsmEventCalendar.i18n.weekdays[dayDate.getDay()];
                                const formattedDate = dayDate.toLocaleDateString();
                                titleText = weekdayName + ', ' + formattedDate;
                                break;
                            case 'weekly':
                                const weekStartDate = new Date(year, month - 1, day);
                                const dayOfWeek = weekStartDate.getDay() || 7; // Convert 0 (Sunday) to 7
                                const mondayOffset = 1 - dayOfWeek; // Calculate days to Monday (1 - day of week)

                                const weekStart = new Date(weekStartDate);
                                weekStart.setDate(weekStartDate.getDate() + mondayOffset);

                                const weekEnd = new Date(weekStart);
                                weekEnd.setDate(weekStart.getDate() + 6);

                                const weekStartFormatted = weekStart.toLocaleDateString();
                                const weekEndFormatted = weekEnd.toLocaleDateString();

                                titleText = weekStartFormatted + ' - ' + weekEndFormatted;
                                break;
                            default: // monthly
                                const monthName = jsmEventCalendar.i18n.months[month - 1];
                                titleText = monthName + ' ' + year;
                                break;
                        }

                        $calendarTitle.text(titleText);

                        // Get the current date for comparison
                        const today = new Date();
                        const currentRealDay = today.getDate();
                        const currentRealMonth = today.getMonth() + 1;
                        const currentRealYear = today.getFullYear();

                        // Check if past navigation is allowed
                        const allowPastNavigation = jsmEventCalendar.allowPastNavigation === 'yes';
                        const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');

                        if (!allowPastNavigation) {
                            // Skrýt tlačítko pro navigaci do minulosti podle aktuálního data
                            if (
                                (view === 'monthly' && month === currentRealMonth && year === currentRealYear) ||
                                (view === 'weekly' && new Date(year, month - 1, day) <= today) ||
                                (view === 'daily' && new Date(year, month - 1, day) < new Date(currentRealYear, currentRealMonth - 1, currentRealDay))
                            ) {
                                $prevButton.css('visibility', 'hidden');
                            } else {
                                $prevButton.css('visibility', 'visible');
                            }
                        } else {
                            // Vždy zobrazit tlačítko, pokud je povolena navigace do minulosti
                            $prevButton.css('visibility', 'visible');
                        }

                        // Update navigation button texts based on view
                        const $todayButton = $('.jsm-event-calendar-today[data-calendar-id="' + calendarId + '"]');
                        const $nextButton = $('.jsm-event-calendar-next[data-calendar-id="' + calendarId + '"]');

                        switch(view) {
                            case 'daily':
                                $prevButton.text(jsmEventCalendar.i18n.previousDay || 'Previous Day');
                                $todayButton.text(jsmEventCalendar.i18n.today || 'Today');
                                $nextButton.text(jsmEventCalendar.i18n.nextDay || 'Next Day');
                                break;
                            case 'weekly':
                                $prevButton.text(jsmEventCalendar.i18n.previousWeek || 'Previous Week');
                                $todayButton.text(jsmEventCalendar.i18n.thisWeek || 'This Week');
                                $nextButton.text(jsmEventCalendar.i18n.nextWeek || 'Next Week');
                                break;
                            default: // monthly
                                $prevButton.text(jsmEventCalendar.i18n.previous || 'Previous');
                                $todayButton.text(jsmEventCalendar.i18n.today || 'Today');
                                $nextButton.text(jsmEventCalendar.i18n.nextMonth || 'Next Month');
                                break;
                        }

                        // Render calendar based on the view
                        // Check if we're on mobile - if so, force monthly view with list display
                        if (JSMEventCalendar.isMobile) {
                            JSMEventCalendar.renderMobileCalendarView($calendarTable, month, year, events);
                        } else {
                            switch(view) {
                                case 'daily':
                                    JSMEventCalendar.renderDailyCalendarContent($calendarTable, year, month, day, events);
                                    break;
                                case 'weekly':
                                    JSMEventCalendar.renderWeeklyCalendarContent($calendarTable, year, month, day, events);
                                    break;
                                default: // monthly
                                    JSMEventCalendar.renderCalendar($calendarTable, month, year, events);
                                    break;
                            }
                        }

                        // Add timeout to ensure all images and content have loaded
                        setTimeout(function() {
                            JSMEventCalendar.equalizeCalendarCellHeights();
                        }, 100);

                        // Update event list if displayed
                        if (showList === 'yes') {
                            JSMEventCalendar.renderEventList($('#' + calendarId + '-list'), events);
                        }

                        // Re-setup event handlers for event modals
                        JSMEventCalendar.setupEventModals();

                        // Přidáme třídu navigační liště pro změnu rozložení
                        const $nav = $calendar.find('.jsm-event-calendar-nav');
                        const $navButtons = $calendar.find('.jsm-event-calendar-nav-buttons');

                        // Přidáme třídu pro vertikální rozložení
                        $nav.addClass('jsm-nav-vertical-layout');
                        $navButtons.addClass('jsm-nav-buttons-vertical');
                    } else {
                        $calendarTable.html('<div class="jsm-event-no-events">Error loading calendar: ' + (response ? response.data : 'Invalid response') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $calendarTable.html('<div class="jsm-event-no-events">Error loading calendar. Please try again.</div>');
                }
            });
        },

        /**
         * Extract start time from timeDisplay string
         * Returns time in minutes since midnight for easy comparison
         */
        extractStartTime: function(timeDisplay) {
            if (!timeDisplay) return null;

            // Try to extract the first time in format HH:MM from the string
            const timeMatch = timeDisplay.match(/(\d{1,2}):(\d{2})/);
            if (timeMatch) {
                const hours = parseInt(timeMatch[1]);
                const minutes = parseInt(timeMatch[2]);
                return hours * 60 + minutes; // Convert to minutes for easier comparison
            }
            return null;
        },

        /**
         * Sort events - all-day events first, then by start time
         */
        sortEventsByTime: function(events) {
            if (!events || !Array.isArray(events)) return [];

            return events.sort((a, b) => {
                // All-day events always come first
                if (a.allDay && !b.allDay) return -1;
                if (!a.allDay && b.allDay) return 1;

                // If both are all-day or both are not all-day, sort by time
                const aTime = this.extractStartTime(a.timeDisplay);
                const bTime = this.extractStartTime(b.timeDisplay);

                // If we have valid times for both, compare them
                if (aTime !== null && bTime !== null) {
                    return aTime - bTime;
                }

                // If only one has a valid time, the one without time comes first
                if (aTime === null && bTime !== null) return -1;
                if (aTime !== null && bTime === null) return 1;

                // If neither has a valid time, maintain original order
                return 0;
            });
        },

        /**
         * Render calendar - European format (Monday as first day)
         */
        renderCalendar: function($calendarTable, month, year, events) {
            const daysInMonth = new Date(year, month, 0).getDate();

            // Calculate first day of month for European format (Monday=0, Sunday=6)
            const firstDayDate = new Date(year, month - 1, 1);
            let firstDay = firstDayDate.getDay(); // 0=Sunday, 1=Monday, ..., 6=Saturday
            firstDay = firstDay === 0 ? 6 : firstDay - 1; // Convert to 0=Monday, ..., 6=Sunday

            const today = new Date();
            const todayDate = today.getDate();
            const todayMonth = today.getMonth() + 1;
            const todayYear = today.getFullYear();

            // If on mobile device, display day list instead of table
            if (this.isMobile) {
                // Use special mobile view that only shows days with events
                this.renderMobileCalendarView($calendarTable, month, year, events);
                return;
            }

            // Pokračovat se standardním zobrazením pro desktop
            let html = '<table class="jsm-event-calendar-table">';
            html += '<thead><tr>';

            // Weekday names - start with Monday (European format)
            const weekDaysOrder = [0, 1, 2, 3, 4, 5, 6]; // 0=Monday, 6=Sunday
            for (let i = 0; i < 7; i++) {
                html += '<th>' + jsmEventCalendar.i18n.weekdaysShort[weekDaysOrder[i]] + '</th>';
            }

            html += '</tr></thead><tbody><tr>';

            // Empty cells before first day of month
            let dayCount = 0;
            for (let i = 0; i < firstDay; i++) {
                html += '<td><div class="jsm-event-calendar-day empty other-month"></div></td>';
                dayCount++;
            }

            // Days in month
            for (let i = 1; i <= daysInMonth; i++) {
                // New row after 7 days
                if (dayCount % 7 === 0 && dayCount > 0) {
                    html += '</tr><tr>';
                }

                // Classes for day
                let dayClasses = 'jsm-event-calendar-day';

                // Check if day is today
                if (i === todayDate && month === todayMonth && year === todayYear) {
                    dayClasses += ' today';
                }

                // Check if day is past (already occurred in current month)
                if ((year === todayYear && month === todayMonth && i < todayDate) ||
                    (year === todayYear && month < todayMonth) ||
                    (year < todayYear)) {
                    dayClasses += ' past-day';
                }

                // Date for this day
                const dateStr = year + '-' + this.pad(month) + '-' + this.pad(i);

                html += '<td>';
                html += '<div class="' + dayClasses + '" data-date="' + dateStr + '">';
                html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';
                html += '<div class="jsm-event-calendar-events-container">'; // Kontejner pro události

                // Get and sort events for this day
                const dayEvents = this.getEventsForDay(events, year, month, i);
                // Sort events - all-day events first, then by start time
                const sortedDayEvents = this.sortEventsByTime(dayEvents);

                // Render events
                for (let j = 0; j < sortedDayEvents.length; j++) {
                    html += this.renderEventInMonthCell(sortedDayEvents[j], j);
                }

                html += '</div>'; // Uzavření kontejneru pro události
                html += '</div>';
                html += '</td>';

                dayCount++;
            }

            // Empty cells at end of month
            while (dayCount % 7 !== 0) {
                html += '<td><div class="jsm-event-calendar-day empty other-month"></div></td>';
                dayCount++;
            }

            html += '</tr></tbody></table>';

            $calendarTable.html(html);

            // Call function to equalize cell heights after rendering
            this.equalizeCalendarCellHeights();
        },
        /**
         * Render event in monthly calendar cell
         * @param {Object} event - The event to render
         * @param {number} index - Index of the event in the cell (for stacking)
         */
renderEventInMonthCell: function(event, index) {
            if (!event || !event.id || !event.title) {
                return '';
            }

            // Tooltip text - create a descriptive title for hovering
            const tooltipText = `${event.title} - ${event.dateDisplay || event.startDate} ${event.timeDisplay || ''}`;

            // Add class based on event type (all-day vs. timed)
            const eventClass = event.allDay ? 'jsm-event-calendar-event jsm-all-day-event' : 'jsm-event-calendar-event';

            // Create event element
            let html = '<div class="' + eventClass + '" ' +
                'data-event-id="' + event.id + '" ' +
                'data-title="' + this.escapeAttr(event.title) + '" ' +
                'data-date="' + this.escapeAttr(event.dateDisplay || event.startDate) + '" ' +
                'title="' + this.escapeAttr(tooltipText) + '" ';

            // Add additional data attributes
            if (event.timeDisplay) {
                html += 'data-time="' + this.escapeAttr(event.timeDisplay) + '" ';
            }
            if (event.allDay) {
                html += 'data-all-day="true" ';
            }
            if (event.excerpt) {
                // Base64 encode the HTML excerpt to preserve HTML formatting
                const encodedExcerpt = btoa(encodeURIComponent(event.excerpt));
                html += 'data-excerpt="' + encodedExcerpt + '" ';
            }
            if (event.customUrl || event.url) {
                html += 'data-url="' + this.escapeAttr(event.customUrl || event.url) + '" ';
            }
            if (event.buttonText) {
                html += 'data-button-text="' + this.escapeAttr(event.buttonText) + '" ';
            }

            // Close opening tag
            html += '>';

            // Event content - show time + title
            if (event.timeDisplay && !event.allDay) {
                html += '<span class="jsm-event-calendar-event-time">' + event.timeDisplay + '</span> ';
            }

            html += '<span class="jsm-event-calendar-event-title">' + event.title + '</span>';
            html += '</div>';

            return html;
        },
        // This function is no longer needed as it's replaced by renderDailyCalendarContent

        /**
         * Render event in daily cell - zcela přepracováno pro lepší trvání událostí
         */
        renderEventInDailyCell: function(event, isAllDay = false, positioning = null) {
            if (!event || !event.id || !event.title) {
                return '';
            }

            // Výpočet trvání a pozice
            let durationMinutes = 60; // Výchozí 1 hodina
            let topOffset = 0;

            // Pokud máme informace o začátku a konci, použijeme je
            if (event._startMinutes !== undefined && event._endMinutes !== undefined) {
                durationMinutes = event._endMinutes - event._startMinutes;

                // Pokud jsme ve slotu, který je uprostřed události, posuneme začátek nahoru
                if (positioning && positioning.slotMinutes !== undefined &&
                    event._startMinutes < positioning.slotMinutes) {
                    // Vypočítat relativní pozici uvnitř buňky (pro události, které začínají dříve)
                    topOffset = 0;
                }
            }

            // Minimální výška pro čitelnost
            const heightInPixels = Math.max(durationMinutes * (22/15), 22); // 22px na 15 minut

            // Set styles based on parameters
            let styles = [];

            // Height for regular events - based on duration
            if (!isAllDay && !event.allDay) {
                styles.push(`height: ${heightInPixels}px`);

                // Top offset pokud událost nezačíná přesně na začátku slotu
                if (topOffset !== 0) {
                    styles.push(`top: ${topOffset}px`);
                }
            }

            // Apply custom positioning if provided
            if (positioning) {
                if (positioning.left) styles.push(`left: ${positioning.left}`);
                if (positioning.width) styles.push(`width: ${positioning.width}`);
                if (positioning.zIndex) styles.push(`z-index: ${positioning.zIndex}`);
                if (positioning.position) styles.push(`position: ${positioning.position}`);
            }

            // Join all styles
            const styleAttr = styles.length > 0 ? `style="${styles.join('; ')}"` : '';

            // Tooltip text - create a descriptive title for hovering
            const tooltipText = `${event.title} - ${event.dateDisplay || event.startDate} ${event.timeDisplay || ''}`;

            // Create event element with title attribute for tooltip
            let html = '<div class="jsm-daily-event jsm-event-calendar-event' + (isAllDay || event.allDay ? ' jsm-all-day-event' : '') + '" ' +
                'data-event-id="' + event.id + '" ' +
                'data-title="' + this.escapeAttr(event.title) + '" ' +
                'data-date="' + this.escapeAttr(event.dateDisplay || event.startDate) + '" ' +
                'title="' + this.escapeAttr(tooltipText) + '" ' +
                styleAttr + ' ';

            // Add optional data attributes only if they exist
            if (event.timeDisplay) {
                html += 'data-time="' + this.escapeAttr(event.timeDisplay) + '" ';
            }
            if (event.allDay) {
                html += 'data-all-day="true" ';
            }
            if (event.excerpt) {
                // Base64 encode the HTML excerpt to preserve HTML formatting
                const encodedExcerpt = btoa(encodeURIComponent(event.excerpt));
                html += 'data-excerpt="' + encodedExcerpt + '" ';
            }
            if (event.customUrl || event.url) {
                html += 'data-url="' + this.escapeAttr(event.customUrl || event.url) + '" ';
            }
            if (event.buttonText) {
                html += 'data-button-text="' + this.escapeAttr(event.buttonText) + '" ';
            }

            // Close opening tag
            html += '>';

            // Event content
            // Nejdřív zobrazíme kompletní čas
            if (event.timeDisplay && !event.allDay) {
                html += '<div class="jsm-event-calendar-event-time">' + event.timeDisplay + '</div>';
            }

            // Pak zobrazíme název s useknutím textu
            html += '<div class="jsm-event-calendar-event-title">' + event.title + '</div>';

            html += '</div>';

            return html;
        },

        /**
         * Get events for a specific hour
         * @param {boolean} includeAllDay - Whether to include all-day events
         */
        getEventsForHour: function(events, year, month, day, hour, includeAllDay = true) {
            if (!events || !Array.isArray(events)) {
                return [];
            }

            const dateString = this.pad(year) + '-' + this.pad(month) + '-' + this.pad(day);
            const hourEvents = [];

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
                if (!event || !event.startDate) continue;

                const startDate = event.startDate;
                const endDate = event.endDate || event.startDate;

                // Check if event belongs to this day
                if (dateString >= startDate && dateString <= endDate) {
                    // Handle all-day events
                    if (event.allDay) {
                        // Only include all-day events if requested
                        if (includeAllDay && hour === 0) {
                            hourEvents.push(event);
                        }
                        continue;
                    }

                    // Check if event happens in this hour
                    if (event.timeDisplay) {
                        const eventStartHour = this.extractHourFromTimeDisplay(event.timeDisplay);
                        const eventEndHour = this.extractEndHourFromTimeDisplay(event.timeDisplay);

                        // Only add the event to its start hour
                        if (eventStartHour === hour) {
                            hourEvents.push(event);
                        }
                    }
                }
            }

            return hourEvents;
        },
        
        /**
         * Extract end hour from time display string
         */
        extractEndHourFromTimeDisplay: function(timeDisplay) {
            if (!timeDisplay) return -1;
            
            // Try to extract the end time in format HH:MM from the string
            const timeMatch = timeDisplay.match(/(\d{1,2})[:\.]\d{2}\s*-\s*(\d{1,2})[:\.]/);
            if (timeMatch && timeMatch[2]) {
                return parseInt(timeMatch[2]);
            }
            return -1;
        },

        /**
         * Extract hour from time display string
         */
        extractHourFromTimeDisplay: function(timeDisplay) {
            if (!timeDisplay) return -1;

            // Try to extract the first time in format HH:MM from the string
            const timeMatch = timeDisplay.match(/(\d{1,2})[:\.]/);
            if (timeMatch) {
                return parseInt(timeMatch[1]);
            }
            return -1;
        },

        /**
         * Format date as YYYY-MM-DD
         */
        formatDate: function(date) {
            return date.getFullYear() + '-' +
                   this.pad(date.getMonth() + 1) + '-' +
                   this.pad(date.getDate());
        },

        /**
         * Format time as HH:MM or h:MM AM/PM based on settings
         */
        formatTime: function(hours, minutes) {
            // Check if time format is set in settings
            const timeFormat = jsmEventCalendar.timeFormat || '24';

            // Format minutes with leading zero if needed
            const formattedMinutes = (minutes < 10 ? '0' : '') + minutes;

            if (timeFormat === '12') {
                // 12-hour format
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const h = hours % 12 || 12;
                return h + ':' + formattedMinutes + ' ' + ampm;
            } else {
                // 24-hour format
                return hours + ':' + formattedMinutes;
            }
        },

        /**
         * Function to equalize calendar cell heights
         */
        equalizeCalendarCellHeights: function() {
            // Skip on mobile devices
            if (window.innerWidth <= 768) {
                return;
            }

            // Equalize heights by row
            $('.jsm-event-calendar-table tbody tr').each(function() {
                const $cells = $(this).find('.jsm-event-calendar-day');
                if ($cells.length === 0) return;

                // Reset height for accurate measurement
                $cells.css('height', 'auto');

                // Find maximum height in row
                let maxHeight = 0;
                $cells.each(function() {
                    const height = $(this).outerHeight();
                    if (height > maxHeight) {
                        maxHeight = height;
                    }
                });

                // Apply same height to all cells in row
                if (maxHeight > 0) {
                    $cells.css('height', maxHeight + 'px');
                }
            });

            //console.log('Calendar cell heights equalized');
        },

        /**
         * Alternative approach - equalize heights of all cells in calendar
         */
        equalizeAllCalendarCellHeights: function() {
            // Skip on mobile devices
            if (window.innerWidth <= 768) {
                return;
            }

            const $cells = $('.jsm-event-calendar-day:not(.empty)');
            if ($cells.length === 0) return;

            // Reset height for accurate measurement
            $cells.css('height', 'auto');

            // Find maximum height across all cells
            let maxHeight = 0;
            $cells.each(function() {
                const height = $(this).outerHeight();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            // Apply same height to all cells
            if (maxHeight > 0) {
                $cells.css('height', maxHeight + 'px');
            }

            //console.log('All calendar cell heights equalized to ' + maxHeight + 'px');
        },
        /**
         * Kompletně přepracovaná funkce pro mobilní zobrazení
         * Zobrazuje POUZE dny, které obsahují události
         */
        renderMobileCalendarView: function($calendarTable, month, year, events) {
            // Aktuální datum pro porovnání
            const today = new Date();
            const todayDate = today.getDate();
            const todayMonth = today.getMonth() + 1;
            const todayYear = today.getFullYear();

            // Počet dní v měsíci
            const daysInMonth = new Date(year, month, 0).getDate();

            let html = '<div class="jsm-event-calendar-list-view">';
            let hasEvents = false;

            // Projít všechny dny v měsíci
            for (let i = 1; i <= daysInMonth; i++) {
                // Získat události pro tento den - filtrujeme pouze pro aktuální měsíc a rok
                const dayEvents = this.getEventsForDay(events, year, month, i);

                // Přeskočit dny bez událostí
                if (dayEvents.length === 0) {
                    continue;
                }

                hasEvents = true;

                // Třídy pro den
                let dayClasses = 'jsm-event-calendar-day';

                // Zkontrolovat, zda je den dnešní
                if (i === todayDate && month === todayMonth && year === todayYear) {
                    dayClasses += ' today';
                }

                // Zkontrolovat, zda je den v minulosti
                if ((year === todayYear && month === todayMonth && i < todayDate) ||
                    (year === todayYear && month < todayMonth) ||
                    (year < todayYear)) {
                    dayClasses += ' past-day';
                }

                // Vypočítat den v týdnu v evropském formátu
                const dayDate = new Date(year, month - 1, i);
                let dayOfWeek = dayDate.getDay(); // 0=Neděle, 1=Pondělí, ..., 6=Sobota
                dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Převod na 0=Pondělí, ..., 6=Neděle

                const dayName = jsmEventCalendar.i18n.weekdays[dayOfWeek];
                const dateString = this.pad(year) + '-' + this.pad(month) + '-' + this.pad(i);

                // Vytvořit buňku dne
                html += '<div class="' + dayClasses + '" data-date="' + dateString + '">';
                html += '<div class="jsm-event-calendar-day-header">';
                html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';
                html += '<span class="jsm-event-calendar-day-name">' + dayName + '</span>';
                html += '</div>';

                // Seřadit události
                const sortedDayEvents = this.sortEventsByTime(dayEvents);

                // Vykreslit všechny události pro tento den
                for (let j = 0; j < sortedDayEvents.length; j++) {
                    const event = sortedDayEvents[j];

                    if (!event || !event.id || !event.title) {
                        continue;
                    }

                    // Třídy pro událost
                    const eventClass = event.allDay ? 'jsm-event-calendar-event jsm-all-day-event' : 'jsm-event-calendar-event';

                    // Tooltip text
                    const tooltipText = `${event.title} - ${event.dateDisplay || event.startDate} ${event.timeDisplay || ''}`;

                    // Vykreslit událost
                    html += '<div class="' + eventClass + '" ' +
                        'data-event-id="' + event.id + '" ' +
                        'data-title="' + this.escapeAttr(event.title) + '" ' +
                        'data-date="' + this.escapeAttr(event.dateDisplay || event.startDate) + '" ' +
                        'title="' + this.escapeAttr(tooltipText) + '" ';

                    // Přidat volitelné atributy
                    if (event.timeDisplay) {
                        html += 'data-time="' + this.escapeAttr(event.timeDisplay) + '" ';
                    }
                    if (event.allDay) {
                        html += 'data-all-day="true" ';
                    }
                    if (event.excerpt) {
                        // Base64 kódování HTML výpisu pro zachování formátování
                        const encodedExcerpt = btoa(encodeURIComponent(event.excerpt));
                        html += 'data-excerpt="' + encodedExcerpt + '" ';
                    }
                    if (event.customUrl || event.url) {
                        html += 'data-url="' + this.escapeAttr(event.customUrl || event.url) + '" ';
                    }
                    if (event.buttonText) {
                        html += 'data-button-text="' + this.escapeAttr(event.buttonText) + '" ';
                    }

                    // Uzavřít otevírací tag
                    html += '>';

                    // Obsah události - zobrazit čas + název
                    if (event.timeDisplay && !event.allDay) {
                        html += '<span class="jsm-event-calendar-event-time">' + event.timeDisplay + '</span> ';
                    }

                    html += '<span class="jsm-event-calendar-event-title">' + event.title + '</span>';
                    html += '</div>';
                }

                html += '</div>';
            }

            html += '</div>';

            // Pokud nejsou žádné události, zobrazit zprávu
            if (!hasEvents) {
                html = '<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>';
            }

            // Vložit HTML do tabulky
            $calendarTable.html(html);
        },

        /**
         * Render calendar for mobile devices - only days with events
         * Kompletně přepracované mobilní zobrazení - seznam dnů s událostmi
         */
        renderMobileCalendar: function($calendarTable, month, year, daysInMonth, firstDay, events, todayDate, todayMonth, todayYear) {
            let html = '<div class="jsm-event-calendar-list-view">';
            let hasEvents = false;

            // Loop through days in month
            for (let i = 1; i <= daysInMonth; i++) {
                // Events for this day
                const dayEvents = this.getEventsForDay(events, year, month, i);

                // Skip days without events
                if (dayEvents.length === 0) {
                    continue;
                }

                hasEvents = true;

                // Classes for day
                let dayClasses = 'jsm-event-calendar-day';

                // Check if day is today
                if (i === todayDate && month === todayMonth && year === todayYear) {
                    dayClasses += ' today';
                }

                // Check if day is past (already occurred in current month)
                if ((year === todayYear && month === todayMonth && i < todayDate) ||
                    (year === todayYear && month < todayMonth) ||
                    (year < todayYear)) {
                    dayClasses += ' past-day';
                }

                // Calculate day of week for European format
                const dayDate = new Date(year, month - 1, i);
                let dayOfWeek = dayDate.getDay(); // 0=Sunday, 1=Monday, ..., 6=Saturday
                dayOfWeek = dayOfWeek === 0 ? 6 : dayOfWeek - 1; // Convert to 0=Monday, ..., 6=Sunday

                const dayName = jsmEventCalendar.i18n.weekdays[dayOfWeek];
                const formattedDate = i + '. ' + jsmEventCalendar.i18n.months[month - 1];

                html += '<div class="' + dayClasses + '" data-date="' + year + '-' + this.pad(month) + '-' + this.pad(i) + '">';
                html += '<div class="jsm-event-calendar-day-header">';
                html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';
                html += '<span class="jsm-event-calendar-day-name">' + dayName + '</span>';
                html += '</div>';

                // Sort events before rendering
                const sortedDayEvents = this.sortEventsByTime(dayEvents);

                // Events for this day - custom formatting for mobile
                for (let j = 0; j < sortedDayEvents.length; j++) {
                    const event = sortedDayEvents[j];
                    if (!event || !event.id || !event.title) {
                        continue;
                    }

                    // Event class - all-day vs. timed events
                    const eventClass = event.allDay ? 'jsm-event-calendar-event jsm-all-day-event' : 'jsm-event-calendar-event';

                    // Create event HTML
                    html += '<div class="' + eventClass + '" ' +
                           'data-event-id="' + event.id + '" ' +
                           'data-title="' + this.escapeAttr(event.title) + '" ' +
                           'data-date="' + this.escapeAttr(event.dateDisplay || event.startDate) + '" ';

                    // Add additional data attributes
                    if (event.timeDisplay) {
                        html += 'data-time="' + this.escapeAttr(event.timeDisplay) + '" ';
                    }
                    if (event.allDay) {
                        html += 'data-all-day="true" ';
                    }
                    if (event.excerpt) {
                        // Base64 encode the HTML excerpt to preserve HTML formatting
                        const encodedExcerpt = btoa(encodeURIComponent(event.excerpt));
                        html += 'data-excerpt="' + encodedExcerpt + '" ';
                    }
                    if (event.customUrl || event.url) {
                        html += 'data-url="' + this.escapeAttr(event.customUrl || event.url) + '" ';
                    }
                    if (event.buttonText) {
                        html += 'data-button-text="' + this.escapeAttr(event.buttonText) + '" ';
                    }

                    // Close opening tag
                    html += '>';

                    // Event content - show time + title
                    if (event.timeDisplay && !event.allDay) {
                        html += '<span class="jsm-event-calendar-event-time">' + event.timeDisplay + '</span> ';
                    }

                    html += '<span class="jsm-event-calendar-event-title">' + event.title + '</span>';
                    html += '</div>';
                }

                html += '</div>';
            }

            html += '</div>';

            // If no events, show message
            if (!hasEvents) {
                html = '<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>';
            }

            $calendarTable.html(html);

            // Make sure modal is closed after rendering calendar
            this.closeEventModal();
        },

        /**
         * Get events for given day
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

                // Check if event belongs to this day
                if (dateString >= startDate && dateString <= endDate) {
                    dayEvents.push(event);
                }
            }

            return dayEvents;
        },

        // This function is no longer needed as it's replaced by renderEventInMonthCell

        /**
         * Helper function to escape attributes for HTML
         */
        escapeAttr: function(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        },

        /**
         * Render event list - modern design
         */
        renderEventList: function($listContainer, events) {
            if (!events || !Array.isArray(events) || events.length === 0) {
                $listContainer.html('<div class="jsm-event-no-events">' + jsmEventCalendar.i18n.noEventsText + '</div>');
                return;
            }

            // Sort events by all-day status and start time
            const sortedEvents = this.sortEventsByTime([...events]);

            let html = '<div class="jsm-event-list">';
            html += '<h3 class="jsm-event-list-title">' + jsmEventCalendar.i18n.eventsListTitle + '</h3>';

            for (let i = 0; i < sortedEvents.length; i++) {
                const event = sortedEvents[i];
                if (!event || !event.id) continue;

                html += '<div class="jsm-event-list-item">';

                // Thumbnail if exists
                if (event.thumbnail) {
                    html += '<div class="jsm-event-list-item-thumbnail">';
                    html += '<img src="' + event.thumbnail + '" alt="' + event.title + '">';
                    html += '</div>';
                }

                html += '<div class="jsm-event-list-item-header">';
                html += '<div class="jsm-event-list-item-date">' + event.dateDisplay + '</div>';
                html += '<h4 class="jsm-event-list-item-title">' + event.title + '</h4>';
                html += '</div>';

                // Handle excerpt - check if it's Base64 encoded
                let excerptContent = '';
                if (event.excerpt) {
                    try {
                        // Try to decode as Base64 first
                        excerptContent = decodeURIComponent(atob(event.excerpt));
                    } catch (e) {
                        // If decoding fails, use the excerpt as is (may be plain text or already HTML)
                        excerptContent = event.excerpt;
                    }
                }

                html += '<div class="jsm-event-list-item-content">' + excerptContent + '</div>';

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
         * Set up modal windows for events - improved to use data attributes
         * Modified for better mobile support and consistent event handling
         */
        setupEventModals: function() {
            // Use event delegation for better performance
            $(document).off('click', '.jsm-event-calendar-event').on('click', '.jsm-event-calendar-event', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Get all data from the clicked element
                const $eventElement = $(this);
                const eventId = $eventElement.data('event-id');
                const isCustomEvent = $eventElement.data('custom') === true;

                //console.log('Event clicked:', eventId, 'Custom:', isCustomEvent);

                // Show modal and loading indicator immediately
                const $modal = $('#jsm-event-modal');
                const $modalContent = $('#jsm-event-modal-content');

                // Activate modal and show loading
                $modal.addClass('active');
                $('body').addClass('modal-open');
                $modalContent.html(`
                    <div class="jsm-event-loading">
                        <div class="jsm-event-loading-spinner"></div>
                        <p>${jsmEventCalendar.i18n.loadingText}</p>
                    </div>
                `);

                // Check if we have embedded data
                if ($eventElement.data('title')) {
                    // We have the data embedded in element - use it directly
                    let excerptContent = $eventElement.data('excerpt');

                    // Check if excerpt is Base64 encoded and decode it
                    if (excerptContent) {
                        try {
                            // Try to decode as Base64
                            excerptContent = decodeURIComponent(atob(excerptContent));
                        } catch (e) {
                            // If decoding fails, use as is
                            //console.log('Excerpt is not Base64 encoded or is invalid');
                        }
                    }

                    const eventData = {
                        id: eventId,
                        custom: isCustomEvent,
                        title: $eventElement.data('title'),
                        dateDisplay: $eventElement.data('date'),
                        timeDisplay: $eventElement.data('time'),
                        allDay: $eventElement.data('all-day'),
                        excerpt: excerptContent,
                        customUrl: $eventElement.data('url'),
                        buttonText: $eventElement.data('button-text')
                    };

                    // Render event modal with the data
                    JSMEventCalendar.renderEventDetailModal(eventData);
                } else {
                    // Fallback to AJAX request if data isn't embedded
                    if (isCustomEvent) {
                        // Custom event - check global events store first
                        if (jsmEventCalendar.allEvents && jsmEventCalendar.allEvents.length > 0) {
                            const foundEvent = jsmEventCalendar.allEvents.find(event =>
                                event.id === eventId ||
                                event.id.toString() === eventId.toString() ||
                                (typeof event.id === 'string' && typeof eventId === 'string' &&
                                    event.id.startsWith('Courses-') && eventId.includes(event.id.split('-')[1]))
                            );

                            if (foundEvent) {
                                JSMEventCalendar.renderEventDetailModal(foundEvent);
                                return;
                            }
                        }

                        // If event not found, fetch events from server
                        $.ajax({
                            url: jsmEventCalendar.ajaxurl,
                            type: 'GET',
                            data: {
                                action: jsmEventCalendar.action,
                                month: new Date().getMonth() + 1,
                                year: new Date().getFullYear(),
                                nonce: jsmEventCalendar.nonce,
                                cache: false // Force bypass browser cache
                            },
                            success: function(response) {
                                if (response && response.success) {
                                    // Store events globally
                                    jsmEventCalendar.allEvents = response.data;

                                    // Find the event - with improved matching for custom events
                                    const foundEvent = response.data.find(event => {
                                        if (typeof event.id === 'string' && typeof eventId === 'string') {
                                            // For string IDs, check for special Courses- format
                                            if (event.id.startsWith('Courses-') && eventId.includes(event.id.split('-')[1])) {
                                                return true;
                                            }
                                        }
                                        return event.id === eventId || event.id.toString() === eventId.toString();
                                    });

                                    if (foundEvent) {
                                        JSMEventCalendar.renderEventDetailModal(foundEvent);
                                    } else {
                                        $modalContent.html('<div class="jsm-event-no-events">Událost nebyla nalezena.</div>');
                                    }
                                } else {
                                    $modalContent.html('<div class="jsm-event-no-events">Chyba při načítání událostí.</div>');
                                }
                            },
                            error: function() {
                                $modalContent.html('<div class="jsm-event-no-events">Chyba připojení.</div>');
                            }
                        });
                    } else {
                        // Standard event - fetch via AJAX
                        $.ajax({
                            url: jsmEventCalendar.ajaxurl,
                            type: 'GET',
                            data: {
                                action: 'get_event_detail',
                                event_id: eventId,
                                nonce: jsmEventCalendar.nonce,
                                cache: false // Force bypass browser cache
                            },
                            success: function(response) {
                                if (response && response.success) {
                                    // Create event object from response
                                    const eventData = {
                                        title: $(response.data).find('.jsm-event-detail-title').text(),
                                        dateDisplay: $(response.data).find('.jsm-event-detail-date').text(),
                                        timeDisplay: $(response.data).find('.jsm-event-detail-time').text(),
                                        excerpt: $(response.data).find('.jsm-event-detail-content').html(),
                                        url: $(response.data).find('.jsm-event-button').attr('href'),
                                        buttonText: $(response.data).find('.jsm-event-button').text()
                                    };

                                    JSMEventCalendar.renderEventDetailModal(eventData);
                                } else {
                                    $modalContent.html('<div class="jsm-event-no-events">Událost nebyla nalezena.</div>');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX error:', error);
                                $modalContent.html('<div class="jsm-event-no-events">Chyba při načítání události.</div>');
                            }
                        });
                    }
                }
            });

            // Existing modal close handlers remain the same
            $(document).off('click', '.jsm-event-modal-close').on('click', '.jsm-event-modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                JSMEventCalendar.closeEventModal();
            });

            $(document).off('click', '.jsm-event-modal').on('click', '.jsm-event-modal', function(e) {
                if ($(e.target).hasClass('jsm-event-modal')) {
                    JSMEventCalendar.closeEventModal();
                }
            });

            $(document).off('keyup.modal').on('keyup.modal', function(e) {
                if (e.key === 'Escape' && $('.jsm-event-modal.active').length) {
                    JSMEventCalendar.closeEventModal();
                }
            });
        },

        /**
         * Render event detail modal - unified method for all events
         */
        renderEventDetailModal: function(event) {
            const $modal = $('#jsm-event-modal');
            const $modalContent = $('#jsm-event-modal-content');

            // Use CSS class to activate modal
            $modal.addClass('active');

            // Add class to restrict scrolling on page under modal
            $('body').addClass('modal-open');

            // Render modal HTML
            let modalHtml = `
                <div class="jsm-event-detail">
                    <div class="jsm-event-detail-header">
                        <h1 class="jsm-event-detail-title">${event.title || ''}</h1>
                        <div class="jsm-event-detail-meta">
                            <div class="jsm-event-detail-date">${event.dateDisplay || event.startDate}</div>
                            <div class="jsm-event-detail-time">${event.timeDisplay || (event.allDay ? jsmEventCalendar.i18n.allDay : '')}</div>
                        </div>
                    </div>
            `;

            // Add excerpt if available
            if (event.excerpt) {
                modalHtml += `
                    <div class="jsm-event-detail-content">
                        ${event.excerpt}
                    </div>
                `;
            }

            // Add custom URL button
            if (event.customUrl || event.url) {
                modalHtml += `
                    <div class="jsm-event-detail-footer">
                        <a href="${event.customUrl || event.url}" class="jsm-event-button" target="_blank">
                            ${event.buttonText || jsmEventCalendar.i18n.moreInformation}
                        </a>
                    </div>
                `;
            }

            modalHtml += '</div>';

            $modalContent.html(modalHtml + '<span class="jsm-event-modal-close">&times;</span>');
        },

        /**
         * Open modal with event details - improved implementation
         */
        openEventModal: function(eventId) {
            if (!eventId) {
                console.error('No event ID provided for modal');
                return;
            }

            const $modal = $('#jsm-event-modal');
            const $modalContent = $('#jsm-event-modal-content');

            // Use CSS class to activate modal
            $modal.addClass('active');

            //console.log('Opening modal for event ID:', eventId);

            // Add class to restrict scrolling on page under modal
            $('body').addClass('modal-open');

            // Loading animation
            $modalContent.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

            // Function to render modal content
            const renderEventModal = (event) => {
                if (!event) {
                    $modalContent.html('<div class="jsm-event-no-events">Událost nebyla nalezena.</div>');
                    return;
                }

                // Prepare date and time display
                let dateDisplay = event.dateDisplay || event.startDate;
                let timeDisplay = event.timeDisplay || (event.allDay ? jsmEventCalendar.i18n.allDay : '');

                // Prepare HTML for modal
                let modalHtml = `
                    <div class="jsm-event-detail">
                        <div class="jsm-event-detail-header">
                            <h1 class="jsm-event-detail-title">${event.title || ''}</h1>
                            <div class="jsm-event-detail-meta">
                                <div class="jsm-event-detail-date">${dateDisplay}</div>
                                <div class="jsm-event-detail-time">${timeDisplay}</div>
                            </div>
                        </div>
                `;

                // Add excerpt if available
                if (event.excerpt) {
                    modalHtml += `
                        <div class="jsm-event-detail-content">
                            ${event.excerpt}
                        </div>
                    `;
                }

                // Add custom URL button
                if (event.customUrl || event.url) {
                    modalHtml += `
                        <div class="jsm-event-detail-footer">
                            <a href="${event.customUrl || event.url}" class="jsm-event-button" target="_blank">
                                ${event.buttonText || jsmEventCalendar.i18n.moreInformation}
                            </a>
                        </div>
                    `;
                }

                modalHtml += '</div>';

                $modalContent.html(modalHtml + '<span class="jsm-event-modal-close">&times;</span>');
            };

            // First, try to find in current allEvents
            const addonEvent = jsmEventCalendar.allEvents &&
                jsmEventCalendar.allEvents.find(event =>
                    event.custom === true &&
                    (event.id === eventId ||
                        event.id === eventId.toString() ||
                        (event.id.startsWith('Courses-') && eventId.toString().includes(event.id.split('-')[1])))
                );

            if (addonEvent) {
                renderEventModal(addonEvent);
                return;
            }

            // If not found in current events, fetch via AJAX
            $.ajax({
                url: jsmEventCalendar.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_events_for_calendar',
                    month: new Date().getMonth() + 1, // Current month
                    year: new Date().getFullYear(),
                    nonce: jsmEventCalendar.nonce
                },
                success: function(response) {
                    if (response && response.success) {
                        // Update allEvents
                        jsmEventCalendar.allEvents = response.data;

                        // Try to find the event again
                        const foundEvent = response.data.find(event =>
                            event.custom === true &&
                            (event.id === eventId ||
                                event.id === eventId.toString() ||
                                (event.id.startsWith('Courses-') && eventId.toString().includes(event.id.split('-')[1])))
                        );

                        if (foundEvent) {
                            renderEventModal(foundEvent);
                        } else {
                            $modalContent.html('<div class="jsm-event-no-events">Událost nebyla nalezena.</div><span class="jsm-event-modal-close">&times;</span>');
                        }
                    } else {
                        $modalContent.html('<div class="jsm-event-no-events">Chyba při načítání událostí.</div><span class="jsm-event-modal-close">&times;</span>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading events:', error);
                    $modalContent.html('<div class="jsm-event-no-events">Chyba při načítání události.</div><span class="jsm-event-modal-close">&times;</span>');
                }
            });
        },

        /**
         * Find external event from loaded events
         */
        findExternalEvent: function(eventId) {
            // Check if current events have been loaded
            if (!window.jsmEventCalendar || !jsmEventCalendar.currentEvents) {
                return null;
            }

            // Find event with matching ID
            return jsmEventCalendar.currentEvents.find(event => event.id === eventId);
        },

        /**
         * Render external event modal content
         */
        renderExternalEventModal: function(event) {
            // Prepare start date
            const startDate = event.startDate ?
                new Date(event.startDate).toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '';

            // Prepare end date (if different)
            const endDate = event.endDate && event.endDate !== event.startDate ?
                new Date(event.endDate).toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : '';

            // Combine dates
            const dateDisplay = endDate ? `${startDate} - ${endDate}` : startDate;

            // HTML for modal
            return `
                <div class="jsm-event-detail">
                    <div class="jsm-event-detail-header">
                        <h1 class="jsm-event-detail-title">${event.title}</h1>

                        <div class="jsm-event-detail-meta">
                            <div class="jsm-event-detail-date">${dateDisplay}</div>
                            <div class="jsm-event-detail-time">
                                ${event.timeDisplay || (event.allDay ? jsmEventCalendar.i18n.allDay : '')}
                            </div>
                        </div>
                    </div>

                    <div class="jsm-event-detail-content">
                        ${event.excerpt || ''}
                    </div>

                    ${event.customUrl ? `
                    <div class="jsm-event-detail-footer">
                        <a href="${event.customUrl}" class="jsm-event-button" target="_blank">
                            ${event.buttonText || jsmEventCalendar.i18n.moreInformation}
                        </a>
                    </div>
                    ` : ''}
                </div>
            `;
        },

        /**
         * Close modal window - improved implementation
         */
        closeEventModal: function() {
            const $modal = $('#jsm-event-modal');
            // Remove active class
            $modal.removeClass('active');
            $('body').removeClass('modal-open');
            //console.log('Modal closed'); // Debugging
        },

        /**
         * Detect mobile view and switch to responsive layout
         */
        detectMobileView: function() {
            // Update global mobile state
            this.isMobile = window.innerWidth <= 768;

            $('.jsm-event-calendar-wrapper').toggleClass('jsm-mobile-view', this.isMobile);

            // On mobile, hide view switcher and force monthly view
            if (this.isMobile) {
                $('.jsm-event-calendar-view-switcher').hide();

                // Force monthly view for all calendars
                $('.jsm-event-calendar-wrapper').each(function() {
                    const calendarId = $(this).attr('id');
                    const month = parseInt($(this).data('month'));
                    const year = parseInt($(this).data('year'));
                    
                    // Always force monthly view on mobile, regardless of current view
                    if (calendarId && month && year) {
                        $(this).data('view', 'monthly');
                        $(this).attr('data-view', 'monthly');
                        $(this).removeClass('jsm-weekly-view jsm-daily-view').addClass('jsm-monthly-view');
                        JSMEventCalendar.updateCalendar(calendarId, month, year, 1, 'monthly');
                    }
                });
            } else {
                // On desktop, show view switcher
                $('.jsm-event-calendar-view-switcher').show();

                // If we're coming from mobile to desktop and showing list view, update to proper view
                if ($('.jsm-event-calendar-list-view').is(':visible')) {
                    $('.jsm-event-calendar-wrapper').each(function() {
                        const calendarId = $(this).attr('id');
                        const month = parseInt($(this).data('month'));
                        const year = parseInt($(this).data('year'));
                        const view = $(this).data('view');

                        if (calendarId && month && year) {
                            JSMEventCalendar.updateCalendar(calendarId, month, year, 1, view);
                        }
                    });
                }
            }
        },

        /**
         * Helper function to pad single-digit numbers with leading zero
         */
        pad: function(num) {
            return (num < 10 ? '0' : '') + num;
        }
    };

    // Initialize after document loads
    $(document).ready(function() {
        // Store initial window width to avoid unnecessary reloads
        JSMEventCalendar.lastWindowWidth = window.innerWidth;
        JSMEventCalendar.isMobile = window.innerWidth <= 768;
        
        // Apply mobile classes immediately if needed
        if (JSMEventCalendar.isMobile) {
            $('.jsm-event-calendar-wrapper').addClass('jsm-mobile-view jsm-monthly-view');
            $('.jsm-event-calendar-wrapper').data('view', 'monthly');
            $('.jsm-event-calendar-wrapper').attr('data-view', 'monthly');
            $('.jsm-event-calendar-view-switcher').hide();
        }
        
        JSMEventCalendar.init();
        
        // Also run on orientation change for mobile devices
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                JSMEventCalendar.lastWindowWidth = window.innerWidth;
                JSMEventCalendar.detectMobileView();
            }, 200);
        });
    });

})(jQuery);
