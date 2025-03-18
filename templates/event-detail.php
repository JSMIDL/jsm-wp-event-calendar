<?php
/**
 * Šablona pro vykreslení detailu události
 *
 * @var array $event Detail události
 */

// Kontrola existence události
if (empty($event)) {
    echo '<div class="jsm-event-no-events">';
    echo esc_html__('Událost nebyla nalezena.', 'jsm-wp-event-calendar');
    echo '</div>';
    return;
}
?>

<div class="jsm-event-detail">
    <div class="jsm-event-detail-header">
        <h1 class="jsm-event-detail-title"><?php echo esc_html($event['title']); ?></h1>

        <div class="jsm-event-detail-meta">
            <div class="jsm-event-detail-date">
                <?php
                // Formátování data
                $start_date = date_i18n(get_option('date_format'), strtotime($event['start_date']));

                if (!empty($event['end_date']) && $event['end_date'] !== $event['start_date']) {
                    $end_date = date_i18n(get_option('date_format'), strtotime($event['end_date']));
                    echo esc_html($start_date . ' - ' . $end_date);
                } else {
                    echo esc_html($start_date);
                }
                ?>
            </div>

            <div class="jsm-event-detail-time">
                <?php if ($event['all_day']) : ?>
                    <?php echo esc_html__('Celý den', 'jsm-wp-event-calendar'); ?>
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
        </div>
    </div>

    <?php if (!empty($event['thumbnail'])) : ?>
        <div class="jsm-event-detail-thumbnail">
            <img src="<?php echo esc_url($event['thumbnail']); ?>" alt="<?php echo esc_attr($event['title']); ?>">
        </div>
    <?php endif; ?>

    <div class="jsm-event-detail-content">
        <?php echo $event['content']; ?>
    </div>

    <?php if (!empty($event['custom_url'])) : ?>
        <div class="jsm-event-detail-footer">
            <a href="<?php echo esc_url($event['custom_url']); ?>" class="jsm-event-button" target="_blank">
                <?php echo esc_html($event['button_text']); ?>
            </a>
        </div>
    <?php endif; ?>
</div>