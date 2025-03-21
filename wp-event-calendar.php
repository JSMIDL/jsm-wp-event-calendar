<?php
/**
 * Plugin Name: JŠM WP Event Calendar With WooCommerce
 * Plugin URI: https://jansmidl.cz
 * Description: Plugin pro zobrazení kalendáře událostí s responzivním designem s napojením na produkty woocommerce.
 * Version: 1.0.0
 * Author: Jan Šmídl
 * Author URI: https://jansmidl.cz
 * Text Domain: jsm-wp-event-calendar
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * License: Proprietary
 */

// Zabránění přímému přístupu k souboru
if (!defined('ABSPATH')) {
    exit;
}

// Definice konstant
define('WP_EVENT_CALENDAR_VERSION', '1.0.0');
define('WP_EVENT_CALENDAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_EVENT_CALENDAR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_EVENT_CALENDAR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Načtení požadovaných souborů
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-post-type.php';
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-calendar.php';
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-shortcodes.php';

/**
 * Inicializace pluginu
 */
function wp_event_calendar_init() {
    // Kontrola a načtení hlavních tříd pluginu
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-calendar.php')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-calendar.php';
    }

    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-shortcodes.php')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-shortcodes.php';
    }

    // Vytvoření instance kalendáře pro načtení CSS a JS
    $calendar = new WP_Event_Calendar();

    // Inicializace shortcodů
    $shortcodes = new WP_Event_Shortcodes();
    $shortcodes->register();
}
add_action('plugins_loaded', 'wp_event_calendar_init');

/**
 * Načtení a registrace post typu
 */
function wp_event_calendar_register_post_type() {
    if (class_exists('WP_Event_Post_Type')) {
        $post_type = new WP_Event_Post_Type();
        $post_type->register();
    }
}
add_action('init', 'wp_event_calendar_register_post_type', 5); // Priorita 5 zajistí, že proběhne dříve

/**
 * Inicializace admin části
 */
function wp_event_calendar_admin_init() {
    // Načtení administrativních tříd
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-admin.php')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-admin.php';
        $admin = new WP_Event_Admin();
        $admin->init();
    }

    // Načtení nastavení událostí
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-settings.php')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-settings.php';
        WP_Event_Settings::init();
    }
}
add_action('admin_init', 'wp_event_calendar_admin_init');

/**
 * Načtení admin menu
 */
function wp_event_calendar_admin_menu() {
    // Zkontrolovat zda byly načteny administrační třídy
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-settings.php') && !function_exists('WP_Event_Settings::init')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-settings.php';
        WP_Event_Settings::init();
    }
}
add_action('admin_menu', 'wp_event_calendar_admin_menu', 5);

/**
 * Registrace aktivačního hooku
 */
function wp_event_calendar_activate() {
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-post-type.php')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-post-type.php';

        // Registrace post typu
        $post_type = new WP_Event_Post_Type();
        $post_type->register();
    }

    // Propláchnutí permalinků
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wp_event_calendar_activate');

/**
 * Registrace deaktivačního hooku
 */
function wp_event_calendar_deactivate() {
    // Propláchnutí permalinků
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wp_event_calendar_deactivate');

/**
 * Přidání migrace databáze při aktualizaci pluginu
 */
function wp_event_calendar_update_db_check() {
    $installed_ver = get_option('wp_event_calendar_version');

    // Pokud verze není nastavena nebo je nižší než aktuální, proveďte migraci
    if (!$installed_ver || version_compare($installed_ver, WP_EVENT_CALENDAR_VERSION, '<')) {
        global $wpdb;

        // Zjištění všech wp_event postů
        $old_posts = $wpdb->get_results("
            SELECT ID
            FROM {$wpdb->posts}
            WHERE post_type = 'wp_event'
        ");

        if ($old_posts) {
            foreach ($old_posts as $post) {
                // Aktualizace post_type na jsm_wp_event
                $wpdb->update(
                    $wpdb->posts,
                    array('post_type' => 'jsm_wp_event'),
                    array('ID' => $post->ID)
                );
            }
        }

        // Aktualizace uložené verze
        update_option('wp_event_calendar_version', WP_EVENT_CALENDAR_VERSION);

        // Propláchnutí permalinků
        flush_rewrite_rules();
    }
}
add_action('plugins_loaded', 'wp_event_calendar_update_db_check');