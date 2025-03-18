<?php
/**
 * Plugin Name: JSM WP Event Calendar
 * Description: Plugin pro správu nastavení kalendáře událostí.
 * Version: 1.0
 * Author: JSM
 */

// Zabránění přímému přístupu
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Třída pro správu nastavení kalendáře událostí
 */
class WP_Event_Settings {

    /**
     * Výchozí hodnoty nastavení
     *
     * @var array
     */
    private $defaults = array(
        'primary_color' => '#2563eb',
        'primary_hover' => '#1d4ed8',
        'secondary_color' => '#4f46e5',
        'secondary_hover' => '#4338ca',
        'button_text' => '#ffffff',
        'background_color' => '#ffffff',
        'surface_color' => '#f8fafc',
        'surface_hover' => '#f1f5f9',
        'border_color' => '#e2e8f0',
        'text_primary' => '#1e293b',
        'text_secondary' => '#64748b',
        'shadow_sm' => '0 1px 2px rgba(0, 0, 0, 0.05)',
        'shadow_md' => '0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.05)',
        'shadow_lg' => '0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)',
        'border_radius_sm' => '0.375rem',
        'border_radius_md' => '0.75rem',
        'border_radius_lg' => '1rem',
        'button_radius' => '0.5rem',
        'calendar_spacing' => '0.5rem',
    );

    /**
     * Konstruktor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_head', array($this, 'output_custom_css'));
    }

    /**
     * Inicializace pluginu
     */
    public static function init() {
        new self();
    }

    /**
     * Přidání stránky nastavení do menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=wp_event',
            __('Nastavení kalendáře', 'jsm-wp-event-calendar'),
            __('Nastavení', 'jsm-wp-event-calendar'),
            'manage_options',
            'wp_event_settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Registrace nastavení
     */
    public function register_settings() {
        register_setting(
            'wp_event_settings',
            'wp_event_calendar_settings',
            array($this, 'sanitize_settings')
        );
    }

    /**
     * Výstup vlastních CSS stylů
     */
    public function output_custom_css() {
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, $this->defaults);
        ?>
        <style>
            :root {
                --primary-color: <?php echo esc_attr($options['primary_color']); ?>;
                --primary-hover: <?php echo esc_attr($options['primary_hover']); ?>;
                --secondary-color: <?php echo esc_attr($options['secondary_color']); ?>;
                --secondary-hover: <?php echo esc_attr($options['secondary_hover']); ?>;
                --button-text: <?php echo esc_attr($options['button_text']); ?>;
            }
        </style>
        <?php
    }

    /**
     * Sanitace nastavení
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        foreach ($this->defaults as $key => $default_value) {
            $sanitized_input[$key] = isset($input[$key]) ? sanitize_text_field($input[$key]) : $default_value;
        }
        return $sanitized_input;
    }
}
