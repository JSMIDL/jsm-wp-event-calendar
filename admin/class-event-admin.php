<?php
/**
 * Class for managing plugin administration
 */
class WP_Event_Admin {

    /**
     * Initialize administration
     */
    public function init() {
        // Load CSS and JS
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Add column to events list
        add_filter('manage_jsm_wp_event_posts_columns', array($this, 'add_event_columns'));
        add_action('manage_jsm_wp_event_posts_custom_column', array($this, 'render_event_columns'), 10, 2);

        // Column sorting
        add_filter('manage_edit-jsm_wp_event_sortable_columns', array($this, 'sortable_event_columns'));
        add_action('pre_get_posts', array($this, 'sort_events_by_date'));
    }

    /**
     * Load scripts and styles for admin
     */
    public function enqueue_admin_scripts($hook) {
        $screen = get_current_screen();

        // Load on event edit/add page
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            if ('jsm_wp_event' === $screen->post_type) {
                wp_enqueue_style('wp-event-admin', plugin_dir_url(dirname(__FILE__)) . 'admin/css/event-admin.css', array(), defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0');
                wp_enqueue_script('wp-event-admin', plugin_dir_url(dirname(__FILE__)) . 'admin/js/event-admin.js', array('jquery', 'jquery-ui-datepicker'), defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0', true);
            }
        }

        // Load on documentation and settings pages
        if (strpos($hook, 'wp_event_docs') !== false || strpos($hook, 'wp_event_settings') !== false) {
            wp_enqueue_style('wp-event-admin-pages', plugin_dir_url(dirname(__FILE__)) . 'admin/css/event-admin-pages.css', array(), defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0');
        }
    }

    /**
     * Add columns to events list
     */
    public function add_event_columns($columns) {
        $new_columns = array();

        // Insert new columns after title
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ('title' === $key) {
                $new_columns['event_date'] = __('Event Date', 'jsm-wp-event-calendar');
                $new_columns['event_time'] = __('Time', 'jsm-wp-event-calendar');
            }
        }

        return $new_columns;
    }

    /**
     * Render column content
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
                    echo esc_html__('All Day', 'jsm-wp-event-calendar');
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
     * Add sortable columns
     */
    public function sortable_event_columns($columns) {
        $columns['event_date'] = 'event_date';
        return $columns;
    }

    /**
     * Sort events by date
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