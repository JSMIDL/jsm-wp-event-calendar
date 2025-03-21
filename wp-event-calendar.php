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

// Načtení požadovaných souborů - vždy načíst všechny potřebné soubory na začátku
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-post-type.php';
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-calendar.php';
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-shortcodes.php';

// Admin soubory - načteme je rovnou na začátku
if (is_admin()) {
    require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-admin.php';
    require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-settings.php';
}

/**
 * Inicializace pluginu
 */
function wp_event_calendar_init() {
    // Vytvoření instance kalendáře pro načtení CSS a JS
    $calendar = new WP_Event_Calendar();

    // Inicializace shortcodů
    $shortcodes = new WP_Event_Shortcodes();
    $shortcodes->register();

    // Načtení textové domény
    load_plugin_textdomain('jsm-wp-event-calendar', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wp_event_calendar_init');

/**
 * Načtení a registrace post typu - nízká priorita pro urychlení
 */
function wp_event_calendar_register_post_type() {
    $post_type = new WP_Event_Post_Type();
    $post_type->register();
}
add_action('init', 'wp_event_calendar_register_post_type', 0); // Nižší číslo = vyšší priorita

/**
 * Inicializace admin části
 */
function wp_event_calendar_admin_init() {
    if (is_admin()) {
        $admin = new WP_Event_Admin();
        $admin->init();
    }
}
add_action('admin_init', 'wp_event_calendar_admin_init');

/**
 * Přidání admin menu - nižší priorita, aby se načetlo dřív
 */
function wp_event_calendar_admin_menu() {
    if (class_exists('WP_Event_Settings')) {
        WP_Event_Settings::init();
    }
}
add_action('admin_menu', 'wp_event_calendar_admin_menu', 0); // Nižší číslo = vyšší priorita

/**
 * Registrace aktivačního hooku
 */
function wp_event_calendar_activate() {
    // Registrace post typu
    $post_type = new WP_Event_Post_Type();
    $post_type->register();

    // Propláchnutí permalinků
    flush_rewrite_rules();

    // Uložení verze pluginu pro případné budoucí aktualizace
    update_option('wp_event_calendar_version', WP_EVENT_CALENDAR_VERSION);
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