<?php
/**
 * Template for displaying the weekly event calendar view
 *
 * @var array $atts Shortcode attributes
 */

// Generate unique ID for the calendar
$calendar_id = 'jsm-event-calendar-' . uniqid();

// Current date information
$current_day = date('d');
$current_month = date('m');
$current_year = date('Y');

$day = absint($atts['day']);
$month = absint($atts['month']);
$year = absint($atts['year']);

// Check for valid date
if ($day < 1 || $day > 31) {
    $day = $current_day;
}
if ($month < 1 || $month > 12) {
    $month = $current_month;
}
if ($year < 1970 || $year > 2100) {
    $year = $current_year;
}

// Create date string
$date_string = sprintf('%04d-%02d-%02d', $year, $month, $day);

// Ensure date is valid
$timestamp = strtotime($date_string);
if ($timestamp === false) {
    $day = $current_day;
    $month = $current_month;
    $year = $current_year;
    $date_string = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $timestamp = strtotime($date_string);
}

// Get the first day of the week (Monday)
$day_of_week = date('N', $timestamp); // 1 (Monday) through 7 (Sunday)
$week_start = strtotime("-" . ($day_of_week - 1) . " days", $timestamp); // Monday
$week_end = strtotime("+" . (7 - $day_of_week) . " days", $timestamp); // Sunday

$week_start_date = date_i18n(get_option('date_format'), $week_start);
$week_end_date = date_i18n(get_option('date_format'), $week_end);

// Category for filtering
$category = !empty($atts['category']) ? $atts['category'] : '';

// Show event list below calendar
$show_list = ($atts['show_list'] === 'yes');

// Load calendar view settings
$options = get_option('wp_event_calendar_settings', array());
$enable_monthly = isset($options['enable_monthly_view']) ? $options['enable_monthly_view'] === 'yes' : true;
$enable_weekly = isset($options['enable_weekly_view']) ? $options['enable_weekly_view'] === 'yes' : true;
$enable_daily = isset($options['enable_daily_view']) ? $options['enable_daily_view'] === 'yes' : true;
$allow_past_navigation = isset($options['allow_past_navigation']) ? $options['allow_past_navigation'] === 'yes' : true;
?>

<div id="<?php echo esc_attr($calendar_id); ?>" class="jsm-event-calendar-wrapper jsm-weekly-view" data-month="<?php echo esc_attr($month); ?>" data-year="<?php echo esc_attr($year); ?>" data-day="<?php echo esc_attr($day); ?>" data-view="weekly" data-show-list="<?php echo esc_attr($atts['show_list']); ?>" data-category="<?php echo esc_attr($category); ?>">
    <!-- Calendar navigation -->
    <div class="jsm-event-calendar-nav jsm-nav-vertical-layout">
        <h2 id="<?php echo esc_attr($calendar_id); ?>-title" class="jsm-event-calendar-title"><?php echo esc_html($week_start_date . ' - ' . $week_end_date); ?></h2>

        <div class="jsm-event-calendar-nav-buttons jsm-nav-buttons-vertical">
            <?php
            // Show Previous button based on allow_past_navigation setting or if we're not in the current month
            $show_prev = $allow_past_navigation;
            if ($show_prev) :
            ?>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-prev" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Previous Week', 'jsm-wp-event-calendar'); ?>
            </button>
            <?php endif; ?>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-today" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('This Week', 'jsm-wp-event-calendar'); ?>
            </button>
            <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-next" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php _e('Next Week', 'jsm-wp-event-calendar'); ?>
            </button>

            <!-- View switching buttons -->
            <div class="jsm-event-calendar-view-switcher">
                <?php if ($enable_daily): ?>
                <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-view-button" data-view="daily" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                    <?php _e('Day', 'jsm-wp-event-calendar'); ?>
                </button>
                <?php endif; ?>

                <?php if ($enable_weekly): ?>
                <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-view-button active" data-view="weekly" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                    <?php _e('Week', 'jsm-wp-event-calendar'); ?>
                </button>
                <?php endif; ?>

                <?php if ($enable_monthly): ?>
                <button type="button" class="jsm-event-calendar-nav-button jsm-event-calendar-view-button" data-view="monthly" data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                    <?php _e('Month', 'jsm-wp-event-calendar'); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Weekly calendar -->
    <div id="<?php echo esc_attr($calendar_id); ?>-table" class="jsm-event-calendar-table-wrapper jsm-weekly-calendar">
        <div class="jsm-weekly-grid">
            <div class="jsm-weekly-header">
                <div class="jsm-weekly-time-column">
                    &nbsp;
                </div>
                <?php
                // Generate weekday headers
                $current_week_day = $week_start;
                for ($i = 0; $i < 7; $i++) {
                    $day_name = date_i18n('D', $current_week_day);
                    $day_number = date('j', $current_week_day);
                    $month_number = date('n', $current_week_day);
                    $year_number = date('Y', $current_week_day);

                    $header_class = 'jsm-weekly-day-header';
                    if (date('Y-m-d', $current_week_day) === date('Y-m-d')) {
                        $header_class .= ' today';
                    }

                    echo '<div class="' . esc_attr($header_class) . '" data-date="' . esc_attr(date('Y-m-d', $current_week_day)) . '">';
                    echo '<div class="jsm-weekly-day-name">' . esc_html($day_name) . '</div>';
                    echo '<div class="jsm-weekly-day-number">' . esc_html($day_number) . '</div>';
                    echo '</div>';

                    $current_week_day = strtotime('+1 day', $current_week_day);
                }
                ?>
            </div>
            <div class="jsm-weekly-body">
                <div class="jsm-weekly-time-column">
                    <?php
                    // Generate time slots - from 6 AM to 9 PM
                    $start_hour = 6;
                    $end_hour = 21;

                    for ($hour = $start_hour; $hour <= $end_hour; $hour++) {
                        $time_display = date_i18n(get_option('time_format'), strtotime("$hour:00"));
                        echo '<div class="jsm-weekly-time-label">' . esc_html($time_display) . '</div>';
                    }
                    ?>
                </div>
                <?php
                // Generate day columns with hour cells
                $current_week_day = $week_start;
                for ($day_idx = 0; $day_idx < 7; $day_idx++) {
                    $day_date = date('Y-m-d', $current_week_day);
                    $day_class = 'jsm-weekly-day-column';

                    if ($day_date === date('Y-m-d')) {
                        $day_class .= ' today';
                    }

                    echo '<div class="' . esc_attr($day_class) . '" data-date="' . esc_attr($day_date) . '">';

                    // Hours in the day
                    for ($hour = $start_hour; $hour <= $end_hour; $hour++) {
                        echo '<div class="jsm-weekly-hour-cell" data-hour="' . esc_attr($hour) . '"></div>';
                    }

                    echo '</div>';
                    $current_week_day = strtotime('+1 day', $current_week_day);
                }
                ?>
            </div>
        </div>
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
