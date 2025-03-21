/**
 * JavaScript for frontend event calendar - Minimalist design 2025
 */
(function($) {
    'use strict';

    // Global object for our functions
    window.JSMEventCalendar = {
        init: function() {
            console.log('Initializing JSM Event Calendar 2025 - Minimalist Edition');

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
                    console.log('Loading initial calendar data:', calendarId, month, year);
                    // Add slight delay for proper DOM rendering
                    setTimeout(function() {
                        JSMEventCalendar.updateCalendar(calendarId, month, year);
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
                JSMEventCalendar.detectMobileView();
                // Equalize heights after resize
                JSMEventCalendar.equalizeCalendarCellHeights();
            }, 250);
        },

        /**
         * Set up calendar navigation
         */
        setupCalendarNavigation: function() {
            // Event delegation for better performance and compatibility with dynamically created elements
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
         * Change month in calendar - forward only
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

            // If going back, check if we're not going into the past
            if (direction < 0) {
                // If we're in the current month or trying to go to the past, stop
                if ((currentYear < currentRealYear) ||
                    (currentYear === currentRealYear && currentMonth <= currentRealMonth)) {
                    console.log("Cannot navigate to the past");
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
         * Go to current month
         */
        goToToday: function(calendarId) {
            const today = new Date();
            const month = today.getMonth() + 1; // JavaScript counts months from 0
            const year = today.getFullYear();

            this.updateCalendar(calendarId, month, year);
        },

        /**
         * Update calendar via AJAX - optimized for speed
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

            // Show loading animation
            $calendarTable.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

            // AJAX request to backend - optimized for faster loading
            $.ajax({
                url: jsmEventCalendar.ajaxurl,
                type: 'GET',
                data: {
                    action: jsmEventCalendar.action,
                    month: month,
                    year: year,
                    category: category,
                    nonce: jsmEventCalendar.nonce,
                    cache: false // Force bypass browser cache
                },
                success: function(response) {
                    if (response && response.success) {
                        const events = response.data;

                        // Update month and year in data attributes
                        $calendar.data('month', month);
                        $calendar.data('year', year);

                        // Update calendar title
                        const monthName = jsmEventCalendar.i18n.months[month - 1];
                        $calendarTitle.text(monthName + ' ' + year);

                        // Show/Hide Previous month button based on current month
                        const today = new Date();
                        const currentRealMonth = today.getMonth() + 1;
                        const currentRealYear = today.getFullYear();
                        const $prevButton = $('.jsm-event-calendar-prev[data-calendar-id="' + calendarId + '"]');

                        if (month === currentRealMonth && year === currentRealYear) {
                            $prevButton.css('visibility', 'hidden');
                        } else {
                            $prevButton.css('visibility', 'visible');
                        }

                        // Render calendar
                        JSMEventCalendar.renderCalendar($calendarTable, month, year, events);

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
            if (window.innerWidth <= 768) {
                this.renderMobileCalendar($calendarTable, month, year, daysInMonth, firstDay, events, todayDate, todayMonth, todayYear);
                return;
            }

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

                // Events for this day
                const dayEvents = this.getEventsForDay(events, year, month, i);
                for (let j = 0; j < dayEvents.length; j++) {
                    html += this.renderEventInCell(dayEvents[j]);
                }

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

            console.log('Calendar cell heights equalized');
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

            console.log('All calendar cell heights equalized to ' + maxHeight + 'px');
        },


/**
 * Render calendar for mobile devices - only days with events
 * Modified to ensure consistent event data handling with desktop view
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
        const formattedDate = dayDate.toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'long' });

        html += '<div class="' + dayClasses + '" data-date="' + year + '-' + this.pad(month) + '-' + this.pad(i) + '">';
        html += '<div class="jsm-event-calendar-day-header">';
        html += '<span class="jsm-event-calendar-day-number">' + i + '</span>';
        html += '<span class="jsm-event-calendar-day-name">' + dayName + '</span>';
        html += '</div>';

        // Events for this day - use the same rendering function for consistency
        for (let j = 0; j < dayEvents.length; j++) {
            html += this.renderEventInCell(dayEvents[j]);
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

        /**
         * Render event in calendar cell - minimalist design with data attributes
         * Modified to ensure all necessary data is available for both desktop and mobile
         */
        renderEventInCell: function(event) {
            if (!event || !event.id || !event.title) {
                return '';
            }

            // Track custom events consistently
            const isCustomEvent = event.custom === true || (typeof event.id === 'string' && event.id.startsWith('Courses-'));

            // Store essential event data as data attributes
            let html = '<div class="jsm-event-calendar-event" ' +
                       'data-event-id="' + event.id + '" ' +
                       (isCustomEvent ? 'data-custom="true"' : '') + ' ' +
                       'data-title="' + this.escapeAttr(event.title) + '" ' +
                       'data-date="' + this.escapeAttr(event.dateDisplay || event.startDate) + '" ';

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
            html += '<div class="jsm-event-calendar-event-title">' + event.title + '</div>';

            if (event.timeDisplay && !event.allDay) {
                html += '<div class="jsm-event-calendar-event-time">' + event.timeDisplay + '</div>';
            }

            html += '</div>';

            return html;
        },

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

            let html = '<div class="jsm-event-list">';
            html += '<h3 class="jsm-event-list-title">' + jsmEventCalendar.i18n.eventsListTitle + '</h3>';

            for (let i = 0; i < events.length; i++) {
                const event = events[i];
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

                console.log('Event clicked:', eventId, 'Custom:', isCustomEvent);

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
                            console.log('Excerpt is not Base64 encoded or is invalid');
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

           console.log('Rendering event in modal:', event);

           // Render modal HTML
           let modalHtml = `
               <div class="jsm-event-detail">
                   <div class="jsm-event-detail-header">
                       <h1 class="jsm-event-detail-title">${event.title || ''}</h1>
                       <div class="jsm-event-detail-meta">
                           <div class="jsm-event-detail-date">${event.dateDisplay || event.startDate}</div>
                           <div class="jsm-event-detail-time">${event.timeDisplay || (event.allDay ? 'Celý den' : '')}</div>
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
                           ${event.buttonText || 'Více informací'}
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

           console.log('Opening modal for event ID:', eventId);

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

               console.log('Rendering event:', event);

               // Prepare date and time display
               let dateDisplay = event.dateDisplay || event.startDate;
               let timeDisplay = event.timeDisplay || (event.allDay ? 'Celý den' : '');

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
                               ${event.buttonText || 'Více informací'}
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
           console.log('Rendering external event', event);

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
                               ${event.timeDisplay || (event.allDay ? 'Celý den' : '')}
                           </div>
                       </div>
                   </div>

                   <div class="jsm-event-detail-content">
                       ${event.excerpt || ''}
                   </div>

                   ${event.customUrl ? `
                   <div class="jsm-event-detail-footer">
                       <a href="${event.customUrl}" class="jsm-event-button" target="_blank">
                           ${event.buttonText || 'Více informací'}
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
           console.log('Modal closed'); // Debugging
       },

        /**
         * Detect mobile view and switch to responsive layout
         */
        detectMobileView: function() {
            const isMobile = window.innerWidth <= 768;

            $('.jsm-event-calendar-wrapper').toggleClass('jsm-mobile-view', isMobile);

            // Redraw only if needed
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
         * Helper function to pad single-digit numbers with leading zero
         */
        pad: function(num) {
            return (num < 10 ? '0' : '') + num;
        }
    };

    // Initialize after document loads
    $(document).ready(function() {
        JSMEventCalendar.init();
    });

})(jQuery);