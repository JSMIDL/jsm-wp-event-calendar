<?php
/**
 * Template for displaying event list
 *
 * @var array $atts Shortcode attributes
 * @var array $events Array of events
 */

// List layout (list or grid)
$layout = $atts['layout'];

// No events
if (empty($events)) {
    echo '<div class="jsm-event-no-events">';
    echo esc_html__('No events to display.', 'jsm-wp-event-calendar');
    echo '</div>';
    return;
}

// Class for layout
$list_class = 'list' === $layout ? 'jsm-event-list' : 'jsm-event-grid';

// Section title
$section_title = 'yes' === $atts['past']
    ? esc_html__('Past Events', 'jsm-wp-event-calendar')
    : esc_html__('Upcoming Events', 'jsm-wp-event-calendar');
?>

<div class="jsm-event-wrapper">
    <h2 class="jsm-event-section-title"><?php echo $section_title; ?></h2>

    <div class="<?php echo esc_attr($list_class); ?>">
        <?php foreach ($events as $event) : ?>
            <?php if ('list' === $layout) : ?>
                <!-- List layout -->
                <div class="jsm-event-list-item">
                    <div class="jsm-event-list-item-header">
                        <h3 class="jsm-event-list-item-title">
                            <span class="jsm-event-title-link">
                                <?php echo esc_html($event['title']); ?>
                            </span>
                        </h3>

                        <div class="jsm-event-list-item-date">
                            <?php
                            // Format date
                            $start_date = date_i18n(get_option('date_format'), strtotime($event['start_date']));

                            if (!empty($event['end_date']) && $event['end_date'] !== $event['start_date']) {
                                $end_date = date_i18n(get_option('date_format'), strtotime($event['end_date']));
                                echo esc_html($start_date . ' - ' . $end_date);
                            } else {
                                echo esc_html($start_date);
                            }
                            ?>
                        </div>
                    </div>

                    <?php if (!empty($event['thumbnail'])) : ?>
                        <div class="jsm-event-list-item-thumbnail">
                            <img src="<?php echo esc_url($event['thumbnail']); ?>" alt="<?php echo esc_attr($event['title']); ?>">
                        </div>
                    <?php endif; ?>

                    <div class="jsm-event-list-item-content">
                        <?php echo wpautop($event['excerpt']); ?>
                    </div>

                    <div class="jsm-event-list-item-footer">
                        <div class="jsm-event-list-item-time">
                            <?php if ($event['all_day']) : ?>
                                <?php echo esc_html__('All Day', 'jsm-wp-event-calendar'); ?>
                            <?php else : ?>
                                <?php
                                if (!empty($event['start_time'])) {
                                    echo esc_html(date_i18n(get_option('time_format'), strtotime($event['start_time'])));

                                    if (!empty($event['end_time'])) {
                                        echo ' - ' . esc_html(date_i18n(get_option('time_format'), strtotime($event['end_time'])));
                                    }
                                }
                                ?>
                            <?php endif; ?>
                        </div>

                        <a href="<?php echo esc_url(!empty($event['custom_url']) ? $event['custom_url'] : $event['permalink']); ?>" class="jsm-event-button">
                            <?php echo esc_html($event['button_text']); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <!-- Grid layout -->
                <div class="jsm-event-grid-item">
                    <?php if (!empty($event['thumbnail'])) : ?>
                        <div class="jsm-event-grid-item-thumbnail">
                            <a href="<?php echo esc_url($event['permalink']); ?>">
                                <img src="<?php echo esc_url($event['thumbnail']); ?>" alt="<?php echo esc_attr($event['title']); ?>">
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="jsm-event-grid-item-thumbnail jsm-event-no-thumbnail">
                            <a href="<?php echo esc_url($event['permalink']); ?>">
                                <div class="jsm-event-grid-placeholder"></div>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="jsm-event-grid-item-content">
                        <h3 class="jsm-event-grid-item-title">
                            <a href="<?php echo esc_url($event['permalink']); ?>">
                                <?php echo esc_html($event['title']); ?>
                            </a>
                        </h3>

                        <div class="jsm-event-grid-item-date">
                            <?php
                            // Format date
                            $start_date = date_i18n(get_option('date_format'), strtotime($event['start_date']));

                            if (!empty($event['end_date']) && $event['end_date'] !== $event['start_date']) {
                                $end_date = date_i18n(get_option('date_format'), strtotime($event['end_date']));
                                echo esc_html($start_date . ' - ' . $end_date);
                            } else {
                                echo esc_html($start_date);
                            }

                            // Show time
                            if (!$event['all_day'] && !empty($event['start_time'])) {
                                echo ', ' . esc_html(date_i18n(get_option('time_format'), strtotime($event['start_time'])));

                                if (!empty($event['end_time'])) {
                                    echo ' - ' . esc_html(date_i18n(get_option('time_format'), strtotime($event['end_time'])));
                                }
                            } elseif ($event['all_day']) {
                                echo ', ' . esc_html__('All Day', 'jsm-wp-event-calendar');
                            }
                            ?>
                        </div>

                        <div class="jsm-event-grid-item-excerpt">
                            <?php echo wpautop(wp_trim_words($event['excerpt'], 15)); ?>
                        </div>

                        <?php if (!empty($event['custom_url']) && !empty($event['button_text'])): ?>
                            <a href="<?php echo esc_url($event['custom_url']); ?>" class="jsm-event-button">
                                <?php echo esc_html($event['button_text']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>