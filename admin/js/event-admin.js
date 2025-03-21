/**
 * JavaScript for event administration interface
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize datepicker
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        }

        // Toggle for all-day events
        $('#event_all_day').on('change', function() {
            if ($(this).is(':checked')) {
                $('#event_start_time, #event_end_time').val('').prop('disabled', true);
            } else {
                $('#event_start_time, #event_end_time').prop('disabled', false);
            }
        });

        // Date validation
        $('#event_start_date, #event_end_date').on('change', function() {
            var startDate = $('#event_start_date').val();
            var endDate = $('#event_end_date').val();

            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                alert('End date cannot be earlier than start date.');
                $('#event_end_date').val(startDate);
            }
        });
    });
})(jQuery);