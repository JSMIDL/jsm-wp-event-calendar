<?php
/**
 * Třída pro správu menu a stránek pluginu
 */
class WP_Event_Menu {

    /**
     * Inicializace menu a stránek
     */
    public static function init() {
        $instance = new self();
        add_action('admin_menu', array($instance, 'add_menu_pages'));
    }

    /**
     * Přidání stránek do menu
     */
    public function add_menu_pages() {
        // Registrace hlavní stránky dokumentace
        add_submenu_page(
            'edit.php?post_type=jsm_wp_event',
            __('Dokumentace kalendáře', 'jsm-wp-event-calendar'),
            __('Dokumentace', 'jsm-wp-event-calendar'),
            'manage_options',
            'wp_event_docs',
            array($this, 'render_docs_page')
        );

        // Registrace stránky nastavení
        add_submenu_page(
            'edit.php?post_type=jsm_wp_event',
            __('Nastavení kalendáře', 'jsm-wp-event-calendar'),
            __('Nastavení', 'jsm-wp-event-calendar'),
            'manage_options',
            'wp_event_settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Vykreslení stránky dokumentace
     */
    public function render_docs_page() {
        // Kontrola oprávnění
        if (!current_user_can('manage_options')) {
            return;
        }

        // Vložení šablony dokumentace
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/admin-documentation.php';
    }

    /**
     * Vykreslení stránky nastavení
     */
    public function render_settings_page() {
        // Kontrola oprávnění
        if (!current_user_can('manage_options')) {
            return;
        }

        // Vložení šablony nastavení
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/admin-settings.php';
    }

    /**
     * Reset nastavení do výchozích hodnot
     */
    public static function reset_settings() {
        // Kontrola oprávnění a nonce
        if (!current_user_can('manage_options') ||
            !check_admin_referer('jsm_reset_settings_nonce')) {
            wp_die('Nedostatečná oprávnění');
        }

        // Smazání aktuálních nastavení
        delete_option('wp_event_calendar_settings');

        // Přesměrování zpět na stránku nastavení s úspěšnou zprávou
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