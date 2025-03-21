<?php
/**
 * Class for managing events as a custom post type
 */
class WP_Event_Post_Type {

    /**
     * Register post type and metaboxes
     */
    public function register() {
        // Register post type
        add_action('init', array($this, 'register_post_type'));

        // Register metaboxes
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));

        // Save metadata
        add_action('save_post', array($this, 'save_event_metadata'), 10, 2);
    }

    /**
     * Register Event post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Events', 'post type general name', 'jsm-wp-event-calendar'),
            'singular_name'      => _x('Event', 'post type singular name', 'jsm-wp-event-calendar'),
            'menu_name'          => _x('Events', 'admin menu', 'jsm-wp-event-calendar'),
            'name_admin_bar'     => _x('Event', 'add new on admin bar', 'jsm-wp-event-calendar'),
            'add_new'            => _x('Add New', 'event', 'jsm-wp-event-calendar'),
            'add_new_item'       => __('Add New Event', 'jsm-wp-event-calendar'),
            'new_item'           => __('New Event', 'jsm-wp-event-calendar'),
            'edit_item'          => __('Edit Event', 'jsm-wp-event-calendar'),
            'view_item'          => __('View Event', 'jsm-wp-event-calendar'),
            'all_items'          => __('All Events', 'jsm-wp-event-calendar'),
            'search_items'       => __('Search Events', 'jsm-wp-event-calendar'),
            'parent_item_colon'  => __('Parent Events:', 'jsm-wp-event-calendar'),
            'not_found'          => __('No events found.', 'jsm-wp-event-calendar'),
            'not_found_in_trash' => __('No events in trash.', 'jsm-wp-event-calendar')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Events for calendar', 'jsm-wp-event-calendar'),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'events'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest'       => true,
        );

        register_post_type('jsm_wp_event', $args);
    }

    /**
     * Register metaboxes for events
     */
    public function register_meta_boxes() {
        add_meta_box(
            'wp_event_details',
            __('Event Details', 'jsm-wp-event-calendar'),
            array($this, 'render_event_metabox'),
            'jsm_wp_event',
            'normal',
            'high'
        );
    }

    /**
     * Render metabox for event details
     */
    public function render_event_metabox($post) {
        // Security nonce field
        wp_nonce_field('wp_event_details_nonce', 'wp_event_details_nonce');

        // Load saved values
        $start_date = get_post_meta($post->ID, '_event_start_date', true);
        $start_time = get_post_meta($post->ID, '_event_start_time', true);
        $end_date = get_post_meta($post->ID, '_event_end_date', true);
        $end_time = get_post_meta($post->ID, '_event_end_time', true);
        $all_day = get_post_meta($post->ID, '_event_all_day', true);
        $url = get_post_meta($post->ID, '_event_url', true);
        $button_text = get_post_meta($post->ID, '_event_button_text', true);

        // Include html file with the form
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/event-metabox.php';
    }

    /**
     * Save event metadata
     */
    public function save_event_metadata($post_id, $post) {
        // Check nonce field
        if (!isset($_POST['wp_event_details_nonce']) ||
            !wp_verify_nonce($_POST['wp_event_details_nonce'], 'wp_event_details_nonce')) {
            return;
        }

        // Autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check correct post type
        if ('jsm_wp_event' !== $post->post_type) {
            return;
        }

        // Save metadata
        if (isset($_POST['_event_start_date'])) {
            update_post_meta($post_id, '_event_start_date', sanitize_text_field($_POST['_event_start_date']));
        }
        
        if (isset($_POST['_event_start_time'])) {
            update_post_meta($post_id, '_event_start_time', sanitize_text_field($_POST['_event_start_time']));
        }
        
        if (isset($_POST['_event_end_date'])) {
            update_post_meta($post_id, '_event_end_date', sanitize_text_field($_POST['_event_end_date']));
        }
        
        if (isset($_POST['_event_end_time'])) {
            update_post_meta($post_id, '_event_end_time', sanitize_text_field($_POST['_event_end_time']));
        }
        
        $all_day = isset($_POST['_event_all_day']) ? '1' : '0';
        update_post_meta($post_id, '_event_all_day', $all_day);
        
        if (isset($_POST['_event_url'])) {
            update_post_meta($post_id, '_event_url', esc_url_raw($_POST['_event_url']));
        }
        
        if (isset($_POST['_event_button_text'])) {
            update_post_meta($post_id, '_event_button_text', sanitize_text_field($_POST['_event_button_text']));
        }
    }
}