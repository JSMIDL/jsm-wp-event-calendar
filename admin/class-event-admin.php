<?php
/**
 * Třída pro správu administrace pluginu
 */
class WP_Event_Admin {

    /**
     * Inicializace administrace
     */
    public function init() {
        // Načtení CSS a JS
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Přidání sloupce do seznamu událostí
        add_filter('manage_jsm_wp_event_calendar_posts_columns', array($this, 'add_event_columns'));
        add_action('manage_jsm_wp_event_calendar_posts_custom_column', array($this, 'render_event_columns'), 10, 2);

        // Řazení sloupců
        add_filter('manage_edit-jsm_wp_event_calendar_sortable_columns', array($this, 'sortable_event_columns'));
        add_action('pre_get_posts', array($this, 'sort_events_by_date'));
    }

    /**
     * Načtení skriptů a stylů pro administraci
     */
    public function enqueue_admin_scripts($hook) {
        $screen = get_current_screen();

        if ('post.php' === $hook || 'post-new.php' === $hook) {
            if ('jsm_wp_event_calendar' === $screen->post_type) {
                wp_enqueue_style('wp-event-admin', WP_EVENT_CALENDAR_PLUGIN_URL . 'admin/css/event-admin.css', array(), WP_EVENT_CALENDAR_VERSION);
                wp_enqueue_script('wp-event-admin', WP_EVENT_CALENDAR_PLUGIN_URL . 'admin/js/event-admin.js', array('jquery', 'jquery-ui-datepicker'), WP_EVENT_CALENDAR_VERSION, true);
            }
        }
    }

    /**
     * Přidání sloupců do seznamu událostí
     */
    public function add_event_columns($columns) {
        $new_columns = array();

        // Vložení nových sloupců po titulku
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ('title' === $key) {
                $new_columns['event_date'] = __('Datum události', 'wp-event-calendar');
                $new_columns['event_time'] = __('Čas', 'wp-event-calendar');
            }
        }

        return $new_columns;
    }

    /**
     * Vykreslení obsahu sloupců
     */
    public function render_event_columns($column, $post_id) {
        switch ($column) {
            case 'event_date':
                $start_date = get_post_meta($post_id, '_event_start_date', true);
                $end_date = get_post_meta($post_id, '_event_end_date', true);

                if (!empty($start_date)) {
                    echo esc_html(date_i18n(get_option('date_format'), strtotime($start_date)));

                    if (!empty($end_date) && $end_date !== $start_date) {
                        echo ' - ' . esc_html(date_i18n(get_option('date_format'), strtotime($end_date)));
                    }
                } else {
                    echo '—';
                }
                break;

            case 'event_time':
                $all_day = get_post_meta($post_id, '_event_all_day', true);

                if ('1' === $all_day) {
                    echo esc_html__('Celý den', 'wp-event-calendar');
                } else {
                    $start_time = get_post_meta($post_id, '_event_start_time', true);
                    $end_time = get_post_meta($post_id, '_event_end_time', true);

                    if (!empty($start_time)) {
                        echo esc_html(date_i18n(get_option('time_format'), strtotime($start_time)));

                        if (!empty($end_time)) {
                            echo ' - ' . esc_html(date_i18n(get_option('time_format'), strtotime($end_time)));
                        }
                    } else {
                        echo '—';
                    }
                }
                break;
        }
    }

    /**
     * Přidání možnosti řazení sloupců
     */
    public function sortable_event_columns($columns) {
        $columns['event_date'] = 'event_date';
        return $columns;
    }

    /**
     * Řazení událostí podle data
     */
    public function sort_events_by_date($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->get('orderby');

        if ('event_date' === $orderby) {
            $query->set('meta_key', '_event_start_date');
            $query->set('orderby', 'meta_value');
        }
    }
}