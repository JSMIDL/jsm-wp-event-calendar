<?php
/**
 * Class for managing plugin menu and pages
 */
class WP_Event_Menu {

    /**
     * Initialize menu and pages
     */
    public static function init() {
        $instance = new self();
        add_action('admin_menu', array($instance, 'add_menu_pages'));
    }

    /**
     * Add pages to menu
     */
    public function add_menu_pages() {
        // Register main documentation page
        add_submenu_page(
            'edit.php?post_type=jsm_wp_event',
            __('Calendar Documentation', 'jsm-wp-event-calendar'),
            __('Documentation', 'jsm-wp-event-calendar'),
            'manage_options',
            'wp_event_docs',
            array($this, 'render_docs_page')
        );

        // Register settings page
        add_submenu_page(
            'edit.php?post_type=jsm_wp_event',
            __('Calendar Settings', 'jsm-wp-event-calendar'),
            __('Settings', 'jsm-wp-event-calendar'),
            'manage_options',
            'wp_event_settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Render documentation page
     */
    public function render_docs_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // Include documentation template
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/admin-documentation.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        // Include settings template
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/admin-settings.php';
    }

    /**
     * Reset settings to default values
     */
    public static function reset_settings() {
        // Check permissions and nonce
        if (!current_user_can('manage_options') ||
            !check_admin_referer('jsm_reset_settings_nonce')) {
            wp_die(__('Insufficient permissions', 'jsm-wp-event-calendar'));
        }

        // Delete current settings
        delete_option('wp_event_calendar_settings');

        // Redirect back to settings page with success message
        wp_redirect(add_query_arg(
            array(
                'page' => 'wp_event_settings',
                'reset' => 'true'
            ),
            admin_url('edit.php?post_type=jsm_wp_event')
        ));
        exit;
    }
}