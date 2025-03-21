<?php
/**
 * Template for displaying the event calendar
 *
 * @var array $atts Shortcode attributes
 */

// Generate unique ID for the calendar
$calendar_id = 'jsm-event-calendar-' . uniqid();

// Current month and year - ensure we never display past months
$current_month = date('m');
$current_year = date('Y');

$month = absint($atts['month']);
$year = absint($atts['year']);

// Check for valid month and year
if ($month < 1 || $month > 12) {
    $month = $current_month;
}
if ($year < 1970 || $year > 2100) {
    $year = $current_year;
}

// Ensure we don't go into the past
if ($year < $current_year || ($year == $current_year && $month < $current_month)) {
    $month = $current_month;
    $year = $current_year;
}

// Get month names and weekdays
$month_name = date_i18n('F', strtotime("$year-$month-01"));
$weekdays = array(
    __('Mo', 'jsm-wp-event-calendar'),
    __('Tu', 'jsm-wp-event-calendar'),
    __('We', 'jsm-wp-event-calendar'),
    __('Th', 'jsm-wp-event-calendar'),
    __('Fr', 'jsm-wp-event-calendar'),
    __('Sa', 'jsm-wp-event-calendar'),
    __('Su', 'jsm-wp-event-calendar'),
);

// Number of days in month
$days_in_month = date('t', strtotime("$year-$month-01"));

// FIXED: Get first day of month (0 = Monday, 6 = Sunday - European format)
$first_day_timestamp = strtotime("$year-$month-01");
$first_day_of_week = date('N', $first_day_timestamp); // 1 (Monday) to 7 (Sunday)
$first_day_of_month = $first_day_of_week - 1; // Convert to 0-6, where 0 is Monday

// Today
$today = date('Y-m-d');
$today_day = date('j');
$today_month = date('m');
$today_year = date('Y');

// Category for filtering
$category = !empty($atts['category']) ? $atts['category'] : '';

// Show event list below calendar
$show_list = ($atts['show_list'] === 'yes');
?>

<div id="<?php echo esc_attr($calendar_id); ?>" class="jsm-event-calendar-wrapper" data-month="<?php echo esc_attr($month); ?>" data-year="<?php echo esc_attr($year); ?>" data-show-list="<?php echo esc_attr($atts['show_list']); ?>" data-category="<?php echo esc_attr($category); ?>">
    <!-- Calendar navigation -->
    <div class="jsm-event-calendar-nav">
        <h2 id="<?php echo esc_attr($calendar_id); ?>-title" class="jsm-event-calendar-title"><?php echo esc_html($month_name . ' ' . $year); ?></h2>

        <div class="jsm-event-calendar-nav-buttons">
            <?php
            // Show Previous button only if we're not in the current month
            $show_prev = ($month > $current_month || $year > $current_year);
            if ($show_prev) :
            ?>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-prev" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Previous', 'jsm-wp-event-calendar'); ?>
            </button>
            <?php endif; ?>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-today" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Today', 'jsm-wp-event-calendar'); ?>
            </button>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-next" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Next', 'jsm-wp-event-calendar'); ?>
            </button>
        </div>
    </div>

    <!-- Calendar -->
    <div id="<?php echo esc_attr($calendar_id); ?>-table" class="jsm-event-calendar-table-wrapper">
        <table class="jsm-event-calendar-table">
            <thead>
                <tr>
                    <?php foreach ($weekdays as $weekday) : ?>
                        <th><?php echo esc_html($weekday); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    // Empty cells before first day of month
                    for ($i = 0; $i < $first_day_of_month; $i++) {
                        echo '<td class="jsm-event-calendar-day empty other-month"></td>';
                    }

                    // Days in month
                    $day_count = $first_day_of_month;
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        // New row after 7 days
                        if ($day_count % 7 === 0 && $day_count > 0) {
                            echo '</tr><tr>';
                        }

                        // Date for this day
                        $date = sprintf('%s-%s-%s', $year, str_pad($month, 2, '0', STR_PAD_LEFT), str_pad($day, 2, '0', STR_PAD_LEFT));

                        // Class for today
                        $today_class = '';
                        if ($day == $today_day && $month == $today_month && $year == $today_year) {
                            $today_class = ' today';
                        }

                        echo '<td class="jsm-event-calendar-day' . esc_attr($today_class) . '" data-date="' . esc_attr($date) . '">';
                        echo '<span class="jsm-event-calendar-day-number">' . esc_html($day) . '</span>';

                        // Events would go here, but they're loaded dynamically by JavaScript

                        echo '</td>';

                        $day_count++;
                    }

                    // Empty cells after last day of month
                    while ($day_count % 7 !== 0) {
                        echo '<td class="jsm-event-calendar-day empty other-month"></td>';
                        $day_count++;
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Event list -->
    <?php if ($show_list) : ?>
        <div id="<?php echo esc_attr($calendar_id); ?>-list" class="jsm-event-list-wrapper">
            <!-- Event list will be populated dynamically by JavaScript -->
        </div>
    <?php endif; ?>

</div>

<!-- Modal for event details -->
<div id="jsm-event-modal" class="jsm-event-modal">
    <div id="jsm-event-modal-content" class="jsm-event-modal-content">
        <!-- Modal content will be populated dynamically -->
    </div>
</div>