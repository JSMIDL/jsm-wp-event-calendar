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
                updateLivePreview();
            }
        });

        // Inicializace textových polí
        $('.regular-text').on('input', function() {
            // Aktualizace živého náhledu při změně hodnoty
            updateLivePreview();
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
            var unsavedChanges = false;

            // Kontrola inputů, zde potřebujete logiku pro detekci změn
            // Příklad: porovnání aktuální hodnoty s původní hodnotou

            if (unsavedChanges) {
                if (!confirm('Máte neuložené změny. Opravdu chcete opustit stránku?')) {
                    e.preventDefault();
                }
            }
        });

        /**
         * Aktualizace živého náhledu
         */
        function updateLivePreview() {
            // Získání všech hodnot
            var values = {};
            $('.jsm-color-picker, .regular-text').each(function() {
                var id = $(this).attr('id');
                values[id] = $(this).val();
            });

            // Aktualizace náhledů barev
            $('.jsm-color-preview').each(function() {
                var id = $(this).data('color-id');
                if (values[id]) {
                    $(this).css('background-color', values[id]);
                    $(this).text(values[id]);
                }
            });

            // Aktualizace náhledu tlačítka
            $('.jsm-preview-button').css({
                'background-color': values.primary_color || jsmEventSettings.defaults.primary_color,
                'color': values.button_text || jsmEventSettings.defaults.button_text,
                'border-radius': values.button_radius || jsmEventSettings.defaults.button_radius,
                'box-shadow': values.shadow_sm || jsmEventSettings.defaults.shadow_sm
            });

            // Při najetí na tlačítko ukažte hover stav
            $('.jsm-preview-button').hover(
                function() {
                    $(this).css('background-color', values.primary_hover || jsmEventSettings.defaults.primary_hover);
                },
                function() {
                    $(this).css('background-color', values.primary_color || jsmEventSettings.defaults.primary_color);
                }
            );
        }

        // Inicializace náhledu při načtení stránky
        updateLivePreview();
    });
})(jQuery);