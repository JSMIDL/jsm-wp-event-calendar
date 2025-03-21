/**
 * JavaScript for settings page
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize color picker
        $('.jsm-color-picker').wpColorPicker({
            change: function(event, ui) {
                // Update live preview when color changes
                var id = $(this).attr('id');
                var color = ui.color.toString();
                updateColorPreview(id, color);
                updateCalendarPreview();
                updateButtonPreview();
            }
        });

        // Initialize text fields
        $('.regular-text').on('input', function() {
            // Update live preview when value changes
            updateCalendarPreview();
            updateButtonPreview();
        });

       // Reset settings button
       $('#reset-settings').on('click', function(e) {
           e.preventDefault();
           if (confirm(jsmEventSettings.resetConfirmText)) {
               // Construct proper URL for reset
               var resetUrl = 'edit.php?post_type=jsm_wp_event&page=wp_event_settings&reset-settings=true&_wpnonce=' + jsmEventSettings.resetNonce;
               window.location.href = resetUrl;
           }
       });

        // Tab switching
        $('.nav-tab').on('click', function(e) {
            var tab = $(this).attr('href').split('tab=')[1];

            // If already on this tab, do nothing
            if (!tab || window.location.href.indexOf('tab=' + tab) > -1) {
                return;
            }

            // Check for unsaved data
            var unsavedChanges = checkUnsavedChanges();

            if (unsavedChanges) {
                if (!confirm('You have unsaved changes. Do you really want to leave this page?')) {
                    e.preventDefault();
                }
            }
        });

        /**
         * Check for unsaved changes
         */
        function checkUnsavedChanges() {
            var hasChanges = false;

            // Check color pickers
            $('.jsm-color-picker').each(function() {
                var id = $(this).attr('id');
                var currentValue = $(this).val();
                var originalValue = $(this).data('default-color');

                if (currentValue !== originalValue) {
                    hasChanges = true;
                    return false; // exit loop
                }
            });

            // Check text fields
            if (!hasChanges) {
                $('.regular-text').each(function() {
                    var id = $(this).attr('id');
                    var currentValue = $(this).val();
                    var originalValue = $(this).data('default');

                    if (currentValue !== originalValue) {
                        hasChanges = true;
                        return false; // exit loop
                    }
                });
            }

            return hasChanges;
        }

        /**
         * Update color preview
         */
        function updateColorPreview(id, color) {
            $('.jsm-color-preview[data-color-id="' + id + '"]').css('background-color', color).text(color);
        }

        /**
         * Update calendar preview
         */
        function updateCalendarPreview() {
            // Update calendar header
            var primaryColor = $('#primary_color').val();
            var secondaryColor = $('#secondary_color').val();
            var buttonText = $('#button_text').val();
            var borderRadius = $('#border_radius_sm').val();
            var shadowMd = $('#shadow_md').val();
            var borderColor = $('#border_color').val();
            var backgroundColor = $('#background_color').val();
            var surfaceColor = $('#surface_color').val();
            var textPrimary = $('#text_primary').val();
            var calendarSpacing = $('#calendar_spacing').val();

            // Update elements in preview
            $('.jsm-preview-calendar-header').css({
                'background': 'linear-gradient(135deg, ' + primaryColor + ', ' + secondaryColor + ')'
            });

            $('.jsm-preview-calendar-wrapper').css({
                'background-color': backgroundColor,
                'color': textPrimary,
                'border-radius': $('#border_radius_lg').val(),
                'box-shadow': shadowMd,
                'border-color': borderColor
            });

            $('.jsm-preview-calendar-days').css({
                'grid-gap': calendarSpacing
            });

            $('.jsm-preview-calendar-day').css({
                'background-color': surfaceColor,
                'border-color': borderColor,
                'border-radius': borderRadius
            });

            // Update today
            $('.jsm-preview-calendar-day:nth-child(2)').css({
                'border-color': primaryColor
            });

            $('.jsm-preview-calendar-day:nth-child(2) > div:first-child').css({
                'background-color': primaryColor,
                'color': buttonText
            });

            $('.jsm-preview-calendar-day:nth-child(2) > div:last-child').css({
                'background-color': primaryColor,
                'color': buttonText,
                'border-radius': borderRadius
            });
        }

        /**
         * Update button preview
         */
        function updateButtonPreview() {
            var primaryColor = $('#primary_color').val();
            var buttonText = $('#button_text').val();
            var buttonRadius = $('#button_radius').val();
            var shadowSm = $('#shadow_sm').val();
            var primaryHover = $('#primary_hover').val();

            $('.jsm-preview-button').css({
                'background-color': primaryColor,
                'color': buttonText,
                'border-radius': buttonRadius,
                'box-shadow': shadowSm
            });

            // Set hover effect for button
            $('.jsm-preview-button').hover(
                function() {
                    $(this).css('background-color', primaryHover);
                },
                function() {
                    $(this).css('background-color', primaryColor);
                }
            );
        }

        // Initialize preview on page load
        updateCalendarPreview();
        updateButtonPreview();
    });
})(jQuery);