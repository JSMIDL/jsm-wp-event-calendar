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

        // Events for this day
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
         * Render event in calendar cell - minimalist design
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
        * Set up modal windows for events
        */
       setupEventModals: function() {
           // Use event delegation for better performance
           $(document).off('click', '.jsm-event-calendar-event').on('click', '.jsm-event-calendar-event', function(e) {
               e.preventDefault();
               e.stopPropagation();
               const eventId = $(this).data('event-id');
               console.log('Event clicked, ID:', eventId); // Debugging
               if (eventId) {
                   JSMEventCalendar.openEventModal(eventId);
               }
           });

           // Close modal - event delegation for dynamically created elements
           $(document).off('click', '.jsm-event-modal-close').on('click', '.jsm-event-modal-close', function(e) {
               e.preventDefault();
               e.stopPropagation(); // Prevent event propagation to parents
               JSMEventCalendar.closeEventModal();
           });

           // Close modal when clicking outside content
           $(document).off('click', '.jsm-event-modal').on('click', '.jsm-event-modal', function(e) {
               if ($(e.target).hasClass('jsm-event-modal')) {
                   JSMEventCalendar.closeEventModal();
               }
           });

           // Close modal when pressing Escape key
           $(document).off('keyup.modal').on('keyup.modal', function(e) {
               if (e.key === 'Escape' && $('.jsm-event-modal.active').length) {
                   JSMEventCalendar.closeEventModal();
               }
           });
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

           console.log('Opening modal for event ID:', eventId); // Debugging

           // Add class to restrict scrolling on page under modal
           $('body').addClass('modal-open');

           // Loading animation
           $modalContent.html('<div class="jsm-event-loading"><div class="jsm-event-loading-spinner"></div><p>' + jsmEventCalendar.i18n.loadingText + '</p></div>');

           // AJAX request to load event details - with caching for better performance
           const cacheKey = 'event_' + eventId;

           // Try to load from cache
           if (this.eventCache && this.eventCache[cacheKey]) {
               $modalContent.html(this.eventCache[cacheKey]);
               return;
           }

          // If not in cache, load from server
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
                      // Render event detail with close button
                      $modalContent.html(response.data + '<span class="jsm-event-modal-close">&times;</span>');

                      // Save to cache (including close button)
                      if (!JSMEventCalendar.eventCache) JSMEventCalendar.eventCache = {};
                      JSMEventCalendar.eventCache[cacheKey] = response.data + '<span class="jsm-event-modal-close">&times;</span>';
                  } else {
                      $modalContent.html('<div class="jsm-event-no-events">Event not found.</div><span class="jsm-event-modal-close">&times;</span>');
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Error loading event:', error); // Debugging
                  $modalContent.html('<div class="jsm-event-no-events">Error loading event.</div><span class="jsm-event-modal-close">&times;</span>');
              }
          });
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