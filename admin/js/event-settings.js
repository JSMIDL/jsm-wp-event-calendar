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
                updateButtonPreview();
            }
        });

        // Inicializace textových polí
        $('.regular-text').on('input', function() {
            // Aktualizace živého náhledu při změně hodnoty
            updateButtonPreview();
        });

        // Reset settings button
        $('#reset-settings').on('click', function(e) {
            e.preventDefault();
            if (confirm(jsmEventSettings.resetConfirmText)) {
                window.location.href = jsmEventSettings.resetUrl;
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
        updateButtonPreview();
    });
})(jQuery);