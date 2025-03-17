<?php
/**
 * Šablona pro metabox detailů události
 */
?>
<div class="event-meta-box-wrapper">
    <style>
        .event-meta-field {
            margin-bottom: 15px;
        }
        .event-meta-field label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .event-meta-field input[type="text"],
        .event-meta-field input[type="url"],
        .event-meta-field input[type="date"],
        .event-meta-field input[type="time"] {
            width: 100%;
        }
        .event-meta-field.inline label {
            display: inline-block;
            margin-right: 10px;
        }
        .event-time-fields {
            display: flex;
            gap: 15px;
        }
        .event-time-fields .event-meta-field {
            flex: 1;
        }
    </style>

    <div class="event-meta-field">
        <label for="event_start_date"><?php _e('Datum začátku', 'jsm-wp-event-calendar'); ?> <span class="required">*</span></label>
        <input type="date" id="event_start_date" name="_event_start_date" value="<?php echo esc_attr($start_date); ?>" required>
    </div>

    <div class="event-time-fields">
        <div class="event-meta-field">
            <label for="event_start_time"><?php _e('Čas začátku', 'jsm-wp-event-calendar'); ?></label>
            <input type="time" id="event_start_time" name="_event_start_time" value="<?php echo esc_attr($start_time); ?>">
        </div>

        <div class="event-meta-field inline">
            <label for="event_all_day">
                <input type="checkbox" id="event_all_day" name="_event_all_day" value="1" <?php checked($all_day, '1'); ?>>
                <?php _e('Celodenní událost', 'jsm-wp-event-calendar'); ?>
            </label>
        </div>
    </div>

    <div class="event-meta-field">
        <label for="event_end_date"><?php _e('Datum konce', 'jsm-wp-event-calendar'); ?></label>
        <input type="date" id="event_end_date" name="_event_end_date" value="<?php echo esc_attr($end_date); ?>">
        <p class="description"><?php _e('Volitelné. Ponechte prázdné, pokud událost končí ve stejný den jako začíná.', 'jsm-wp-event-calendar'); ?></p>
    </div>

    <div class="event-meta-field">
        <label for="event_end_time"><?php _e('Čas konce', 'jsm-wp-event-calendar'); ?></label>
        <input type="time" id="event_end_time" name="_event_end_time" value="<?php echo esc_attr($end_time); ?>">
        <p class="description"><?php _e('Volitelné.', 'jsm-wp-event-calendar'); ?></p>
    </div>

    <div class="event-meta-field">
        <label for="event_url"><?php _e('URL adresa', 'jsm-wp-event-calendar'); ?></label>
        <input type="url" id="event_url" name="_event_url" value="<?php echo esc_url($url); ?>" placeholder="https://">
    </div>

    <div class="event-meta-field">
        <label for="event_button_text"><?php _e('Text tlačítka', 'jsm-wp-event-calendar'); ?></label>
        <input type="text" id="event_button_text" name="_event_button_text" value="<?php echo esc_attr($button_text); ?>" placeholder="<?php _e('Více informací', 'jsm-wp-event-calendar'); ?>">
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Skrýt/zobrazit pole času na základě celodenní události
        $('#event_all_day').change(function() {
            if ($(this).is(':checked')) {
                $('#event_start_time, #event_end_time').val('').attr('disabled', true).closest('.event-meta-field').css('opacity', 0.5);
            } else {
                $('#event_start_time, #event_end_time').attr('disabled', false).closest('.event-meta-field').css('opacity', 1);
            }
        }).trigger('change');
    });
</script>