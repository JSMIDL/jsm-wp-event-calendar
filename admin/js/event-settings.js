/**
 * JavaScript pro stránku nastavení
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Inicializace color pickeru
        $('.jsm-color-picker').wpColorPicker({
            change: function(event, ui) {
                // Aktualizace živého náhledu při změně barvy
                var id = $(this).attr('id');
                var color = ui.color.toString();
                updateColorPreview(id, color);
                updateCalendarPreview();
                updateButtonPreview();
            }
        });

        // Inicializace textových polí
        $('.regular-text').on('input', function() {
            // Aktualizace živého náhledu při změně hodnoty
            updateCalendarPreview();
            updateButtonPreview();
        });

       // Reset settings button
       $('#reset-settings').on('click', function(e) {
           e.preventDefault();
           if (confirm(jsmEventSettings.resetConfirmText)) {
               // Konstruování správného URL pro reset
               var resetUrl = 'edit.php?post_type=jsm_wp_event&page=wp_event_settings&reset-settings=true&_wpnonce=' + jsmEventSettings.resetNonce;
               window.location.href = resetUrl;
           }
       });

        // Přepínání tabů
        $('.nav-tab').on('click', function(e) {
            var tab = $(this).attr('href').split('tab=')[1];

            // Pokud už jsme na tomto tabu, neděláme nic
            if (!tab || window.location.href.indexOf('tab=' + tab) > -1) {
                return;
            }

            // Ověření, zda jsou neuložená data
            var unsavedChanges = checkUnsavedChanges();

            if (unsavedChanges) {
                if (!confirm('Máte neuložené změny. Opravdu chcete opustit stránku?')) {
                    e.preventDefault();
                }
            }
        });

        /**
         * Kontrola neuložených změn
         */
        function checkUnsavedChanges() {
            var hasChanges = false;

            // Kontrola color pickerů
            $('.jsm-color-picker').each(function() {
                var id = $(this).attr('id');
                var currentValue = $(this).val();
                var originalValue = $(this).data('default-color');

                if (currentValue !== originalValue) {
                    hasChanges = true;
                    return false; // ukončit smyčku
                }
            });

            // Kontrola textových polí
            if (!hasChanges) {
                $('.regular-text').each(function() {
                    var id = $(this).attr('id');
                    var currentValue = $(this).val();
                    var originalValue = $(this).data('default');

                    if (currentValue !== originalValue) {
                        hasChanges = true;
                        return false; // ukončit smyčku
                    }
                });
            }

            return hasChanges;
        }

        /**
         * Aktualizace náhledu barvy
         */
        function updateColorPreview(id, color) {
            $('.jsm-color-preview[data-color-id="' + id + '"]').css('background-color', color).text(color);
        }

        /**
         * Aktualizace náhledu kalendáře
         */
        function updateCalendarPreview() {
            // Aktualizace hlavičky kalendáře
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

            // Aktualizace prvků v náhledu
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

            // Aktualizace dnešního dne
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
         * Aktualizace náhledu tlačítka
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

            // Nastavení hover efektu pro tlačítko
            $('.jsm-preview-button').hover(
                function() {
                    $(this).css('background-color', primaryHover);
                },
                function() {
                    $(this).css('background-color', primaryColor);
                }
            );
        }

        // Inicializace náhledu při načtení stránky
        updateCalendarPreview();
        updateButtonPreview();
    });
})(jQuery);