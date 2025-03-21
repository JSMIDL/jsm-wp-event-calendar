<?php
/**
 * Třída pro správu událostí jako vlastního post typu
 */
class WP_Event_Post_Type {
    
    /**
     * Registrace post typu a metaboxů
     */
    public function register() {
        // Registrace post typu
        add_action('init', array($this, 'register_post_type'));
        
        // Registrace metaboxů
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        
        // Uložení metadat
        add_action('save_post', array($this, 'save_event_metadata'), 10, 2);
    }
    
    /**
     * Registrace post typu Event
     */
    public function register_post_type() {
        $labels = array(
            'name'               => _x('Události', 'post type general name', 'jsm-wp-event-calendar'),
            'singular_name'      => _x('Událost', 'post type singular name', 'jsm-wp-event-calendar'),
            'menu_name'          => _x('Události', 'admin menu', 'jsm-wp-event-calendar'),
            'name_admin_bar'     => _x('Událost', 'add new on admin bar', 'jsm-wp-event-calendar'),
            'add_new'            => _x('Přidat novou', 'event', 'jsm-wp-event-calendar'),
            'add_new_item'       => __('Přidat novou událost', 'jsm-wp-event-calendar'),
            'new_item'           => __('Nová událost', 'jsm-wp-event-calendar'),
            'edit_item'          => __('Upravit událost', 'jsm-wp-event-calendar'),
            'view_item'          => __('Zobrazit událost', 'jsm-wp-event-calendar'),
            'all_items'          => __('Všechny události', 'jsm-wp-event-calendar'),
            'search_items'       => __('Hledat události', 'jsm-wp-event-calendar'),
            'parent_item_colon'  => __('Nadřazené události:', 'jsm-wp-event-calendar'),
            'not_found'          => __('Žádné události nenalezeny.', 'jsm-wp-event-calendar'),
            'not_found_in_trash' => __('Žádné události v koši.', 'jsm-wp-event-calendar')
        );

        $args = array(
            'labels'             => $labels,
            'description'        => __('Události pro kalendář', 'jsm-wp-event-calendar'),
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

        register_post_type('jsm_wp_event_calendar', $args);
    }

    /**
     * Registrace metaboxů pro události
     */
    public function register_meta_boxes() {
        add_meta_box(
            'wp_event_details',
            __('Detaily události', 'jsm-wp-event-calendar'),
            array($this, 'render_event_metabox'),
            'jsm_wp_event_calendar',
            'normal',
            'high'
        );
    }

    /**
     * Vykreslení metaboxu pro detaily události
     */
    public function render_event_metabox($post) {
        // Bezpečnostní nonce pole
        wp_nonce_field('wp_event_details_nonce', 'wp_event_details_nonce');

        // Načtení uložených hodnot
        $start_date = get_post_meta($post->ID, '_event_start_date', true);
        $start_time = get_post_meta($post->ID, '_event_start_time', true);
        $end_date = get_post_meta($post->ID, '_event_end_date', true);
        $end_time = get_post_meta($post->ID, '_event_end_time', true);
        $all_day = get_post_meta($post->ID, '_event_all_day', true);
        $url = get_post_meta($post->ID, '_event_url', true);
        $button_text = get_post_meta($post->ID, '_event_button_text', true);

        // Obsahuje html soubor s formulářem
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/event-metabox.php';
    }

    /**
     * Uložení metadat události
     */
    public function save_event_metadata($post_id, $post) {
        // Kontrola nonce pole
        if (!isset($_POST['wp_event_details_nonce']) ||
            !wp_verify_nonce($_POST['wp_event_details_nonce'], 'wp_event_details_nonce')) {
            return;
        }

        // Automatické uložení
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Kontrola oprávnění
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Kontrola správného post typu
        if ('jsm_wp_event_calendar' !== $post->post_type) {
            return;
        }
        
        // Uložení metadat
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