/**
 * JavaScript pro administrační rozhraní událostí
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Inicializace datepickeru
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        }

        // Toggle pro celodenní události
        $('#event_all_day').on('change', function() {
            if ($(this).is(':checked')) {
                $('#event_start_time, #event_end_time').val('').prop('disabled', true);
            } else {
                $('#event_start_time, #event_end_time').prop('disabled', false);
            }
        });

        // Kontrola dat
        $('#event_start_date, #event_end_date').on('change', function() {
            var startDate = $('#event_start_date').val();
            var endDate = $('#event_end_date').val();

            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                alert('Datum konce nemůže být dříve než datum začátku.');
                $('#event_end_date').val(startDate);
            }
        });
    });
})(jQuery);