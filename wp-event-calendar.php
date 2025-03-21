<?php
/**
 * Plugin Name: JSM WP Event Calendar
 * Plugin URI: https://wordpress.org/plugins/jsm-wp-event-calendar
 * Description: Responsive event calendar with list view, category filtering, add-on support and customizable appearance.
 * Version: 1.0.0
 * Author: Jan Smidl
 * Author URI: https://jansmidl.cz
 * Text Domain: jsm-wp-event-calendar
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.2
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
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
require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'includes/class-event-taxonomy.php';

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
 * Načtení a registrace post typu a taxonomie
 */
function wp_event_calendar_register_post_type() {
    if (class_exists('WP_Event_Post_Type')) {
        $post_type = new WP_Event_Post_Type();
        $post_type->register();
    }

    if (class_exists('WP_Event_Taxonomy')) {
        $taxonomy = new WP_Event_Taxonomy();
        $taxonomy->register();
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

    // Načtení menu
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-menu.php')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-menu.php';
        WP_Event_Menu::init();
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

    // Kontrola a načtení menu
    if (file_exists(WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-menu.php') && !method_exists('WP_Event_Menu', 'init')) {
        require_once WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/class-event-menu.php';
        WP_Event_Menu::init();
    }
}
add_action('admin_menu', 'wp_event_calendar_admin_menu', 5);

// Zbytek kódu zůstává nezměněn (aktivační hook, deaktivační hook, update DB check)