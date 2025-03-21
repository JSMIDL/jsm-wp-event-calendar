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
    private static $defaults = array(
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
     * Získání výchozích hodnot nastavení
     *
     * @return array Výchozí hodnoty nastavení
     */
    public static function get_defaults() {
        return self::$defaults;
    }

    /**
     * Inicializace pluginu
     */
    public static function init() {
        $instance = new self();
        add_action('admin_menu', array($instance, 'add_settings_pages'));
        add_action('admin_init', array($instance, 'register_settings'));
        add_action('admin_enqueue_scripts', array($instance, 'enqueue_admin_scripts'));

        // Přidáme filter pro načtení CSS a dynamických hodnot
        add_action('wp_head', array($instance, 'output_custom_css'), 999); // Vysoká priorita, aby přepsala statické hodnoty

        // Přidáme akci pro načtení základního CSS
        add_action('wp_enqueue_scripts', array($instance, 'enqueue_frontend_styles'));
    }

    /**
     * Načtení skriptů a stylů pro admin
     */
    public function enqueue_admin_scripts($hook) {
        // Načtení pouze na stránkách našeho pluginu
        if (strpos($hook, 'wp_event_settings') !== false || strpos($hook, 'wp_event_docs') !== false) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery');

            // Vlastní skript pro nastavení
            wp_enqueue_script(
                'jsm-event-settings',
                plugin_dir_url(dirname(__FILE__)) . 'admin/js/event-settings.js',
                array('jquery', 'wp-color-picker'),
                defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0',
                true
            );

            // Lokalizace pro JS
            wp_localize_script('jsm-event-settings', 'jsmEventSettings', array(
                'defaults' => self::$defaults,
                'resetUrl' => wp_nonce_url(add_query_arg('reset-settings', 'true'), 'jsm_reset_settings_nonce'),
                'resetConfirmText' => __('Opravdu chcete obnovit výchozí nastavení? Tato akce nelze vrátit zpět.', 'jsm-wp-event-calendar')
            ));
        }
    }

    /**
     * Načtení stylů pro frontend
     */
    public function enqueue_frontend_styles() {
        // Načteme základní CSS soubor
        wp_enqueue_style(
            'jsm-wp-event-calendar',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/event-calendar.css',
            array(),
            defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0'
        );

        // Responzivní styly
        wp_enqueue_style(
            'jsm-wp-event-calendar-mobile',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/event-calendar-mobile.css',
            array('jsm-wp-event-calendar'),
            defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0',
            'only screen and (max-width: 768px)'
        );
    }

    /**
     * Přidání stránek do menu
     */
    public function add_settings_pages() {
        // Přidání stránky dokumentace
        add_submenu_page(
            'edit.php?post_type=jsm_wp_event',
            __('Dokumentace kalendáře', 'jsm-wp-event-calendar'),
            __('Dokumentace', 'jsm-wp-event-calendar'),
            'manage_options',
            'wp_event_docs',
            array($this, 'render_docs_page')
        );

        // Přidání stránky nastavení
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
        include plugin_dir_path(dirname(__FILE__)) . 'admin/views/admin-documentation.php';
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
        include plugin_dir_path(dirname(__FILE__)) . 'admin/views/admin-settings.php';
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

        // Přidání sekcí a polí pro nastavení
        add_settings_section(
            'colors_section',
            __('Barevné schéma', 'jsm-wp-event-calendar'),
            array($this, 'colors_section_callback'),
            'wp_event_settings'
        );

        add_settings_section(
            'dimensions_section',
            __('Rozměry a efekty', 'jsm-wp-event-calendar'),
            array($this, 'dimensions_section_callback'),
            'wp_event_settings'
        );

        // Přidání polí pro barvy
        $this->add_color_fields();

        // Přidání polí pro rozměry
        $this->add_dimension_fields();
    }

    /**
     * Callback pro sekci barev
     */
    public function colors_section_callback() {
        echo '<p>' . __('Nastavte barevné schéma kalendáře událostí.', 'jsm-wp-event-calendar') . '</p>';
    }

    /**
     * Callback pro sekci rozměrů
     */
    public function dimensions_section_callback() {
        echo '<p>' . __('Nastavte rozměry, stíny a zaoblení prvků kalendáře.', 'jsm-wp-event-calendar') . '</p>';
    }

    /**
     * Přidání polí pro barvy
     */
    private function add_color_fields() {
        $color_fields = array(
            'primary_color' => __('Primární barva', 'jsm-wp-event-calendar'),
            'primary_hover' => __('Primární barva (hover)', 'jsm-wp-event-calendar'),
            'secondary_color' => __('Sekundární barva', 'jsm-wp-event-calendar'),
            'secondary_hover' => __('Sekundární barva (hover)', 'jsm-wp-event-calendar'),
            'button_text' => __('Barva textu tlačítek', 'jsm-wp-event-calendar'),
            'background_color' => __('Barva pozadí', 'jsm-wp-event-calendar'),
            'surface_color' => __('Barva povrchu', 'jsm-wp-event-calendar'),
            'surface_hover' => __('Barva povrchu (hover)', 'jsm-wp-event-calendar'),
            'border_color' => __('Barva ohraničení', 'jsm-wp-event-calendar'),
            'text_primary' => __('Primární barva textu', 'jsm-wp-event-calendar'),
            'text_secondary' => __('Sekundární barva textu', 'jsm-wp-event-calendar'),
        );

        foreach ($color_fields as $id => $label) {
            add_settings_field(
                $id,
                $label,
                array($this, 'color_field_callback'),
                'wp_event_settings',
                'colors_section',
                array('id' => $id, 'label' => $label)
            );
        }
    }

    /**
     * Přidání polí pro rozměry
     */
    private function add_dimension_fields() {
        $dimension_fields = array(
            'shadow_sm' => __('Malý stín', 'jsm-wp-event-calendar'),
            'shadow_md' => __('Střední stín', 'jsm-wp-event-calendar'),
            'shadow_lg' => __('Velký stín', 'jsm-wp-event-calendar'),
            'border_radius_sm' => __('Malý rádius', 'jsm-wp-event-calendar'),
            'border_radius_md' => __('Střední rádius', 'jsm-wp-event-calendar'),
            'border_radius_lg' => __('Velký rádius', 'jsm-wp-event-calendar'),
            'button_radius' => __('Rádius tlačítek', 'jsm-wp-event-calendar'),
            'calendar_spacing' => __('Mezery v kalendáři', 'jsm-wp-event-calendar'),
        );

        foreach ($dimension_fields as $id => $label) {
            add_settings_field(
                $id,
                $label,
                array($this, 'text_field_callback'),
                'wp_event_settings',
                'dimensions_section',
                array('id' => $id, 'label' => $label)
            );
        }
    }

    /**
     * Callback pro barevné pole
     */
    public function color_field_callback($args) {
        $id = $args['id'];
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        echo '<input type="text" id="' . esc_attr($id) . '" name="wp_event_calendar_settings[' . esc_attr($id) . ']" value="' . esc_attr($options[$id]) . '" class="jsm-color-picker" data-default-color="' . esc_attr(self::$defaults[$id]) . '" />';

        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Callback pro textové pole
     */
    public function text_field_callback($args) {
        $id = $args['id'];
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        echo '<input type="text" id="' . esc_attr($id) . '" name="wp_event_calendar_settings[' . esc_attr($id) . ']" value="' . esc_attr($options[$id]) . '" class="regular-text" data-default="' . esc_attr(self::$defaults[$id]) . '" />';

        if (isset($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    /**
     * Sanitace nastavení
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        foreach (self::$defaults as $key => $default_value) {
            $sanitized_input[$key] = isset($input[$key]) ? sanitize_text_field($input[$key]) : $default_value;
        }
        return $sanitized_input;
    }

    /**
     * Výstup vlastních CSS stylů
     *
     * Používáme vysokou prioritu (999), aby naše vlastní CSS přepsalo
     * výchozí hodnoty ze statického CSS souboru
     */
    public function output_custom_css() {
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);
        ?>
        <style id="jsm-event-calendar-custom-css">
            :root {
                --primary-color: <?php echo esc_attr($options['primary_color']); ?> !important;
                --primary-hover: <?php echo esc_attr($options['primary_hover']); ?> !important;
                --secondary-color: <?php echo esc_attr($options['secondary_color']); ?> !important;
                --secondary-hover: <?php echo esc_attr($options['secondary_hover']); ?> !important;
                --button-text: <?php echo esc_attr($options['button_text']); ?> !important;
                --background-color: <?php echo esc_attr($options['background_color']); ?> !important;
                --surface-color: <?php echo esc_attr($options['surface_color']); ?> !important;
                --surface-hover: <?php echo esc_attr($options['surface_hover']); ?> !important;
                --border-color: <?php echo esc_attr($options['border_color']); ?> !important;
                --text-primary: <?php echo esc_attr($options['text_primary']); ?> !important;
                --text-secondary: <?php echo esc_attr($options['text_secondary']); ?> !important;
                --shadow-sm: <?php echo esc_attr($options['shadow_sm']); ?> !important;
                --shadow-md: <?php echo esc_attr($options['shadow_md']); ?> !important;
                --shadow-lg: <?php echo esc_attr($options['shadow_lg']); ?> !important;
                --border-radius-sm: <?php echo esc_attr($options['border_radius_sm']); ?> !important;
                --border-radius-md: <?php echo esc_attr($options['border_radius_md']); ?> !important;
                --border-radius-lg: <?php echo esc_attr($options['border_radius_lg']); ?> !important;
                --button-radius: <?php echo esc_attr($options['button_radius']); ?> !important;
                --calendar-spacing: <?php echo esc_attr($options['calendar_spacing']); ?> !important;
                --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            /* Opravená specifická barva pro kalendář - počítá s alfa kanálem */
            .jsm-event-list-item-date {
                background-color: <?php echo esc_attr($this->hex_to_rgba($options['primary_color'], 0.1)); ?> !important;
                color: <?php echo esc_attr($options['primary_color']); ?> !important;
            }

            .jsm-event-calendar-day.past-day .jsm-event-calendar-event {
                background-color: <?php echo esc_attr($this->hex_to_rgba($options['primary_color'], 0.7)); ?> !important;
            }

            /* Speciální vzhled pro dnešní den */
            .jsm-event-calendar-day.today {
                background-color: <?php echo esc_attr($this->hex_to_rgba($options['primary_color'], 0.05)); ?> !important;
                border-color: <?php echo esc_attr($options['primary_color']); ?> !important;
            }

            /* Barva pro tlačítka a prvky s interakcí */
            .jsm-event-button,
            .jsm-event-calendar-event,
            .jsm-event-calendar-day.today .jsm-event-calendar-day-number {
                background-color: <?php echo esc_attr($options['primary_color']); ?> !important;
                color: <?php echo esc_attr($options['button_text']); ?> !important;
            }

            .jsm-event-button:hover,
            .jsm-event-calendar-event:hover {
                background-color: <?php echo esc_attr($options['primary_hover']); ?> !important;
            }

            /* Barva pro navigační panel kalendáře */
            .jsm-event-calendar-nav {
                background: linear-gradient(135deg, <?php echo esc_attr($options['primary_color']); ?>, <?php echo esc_attr($options['secondary_color']); ?>) !important;
            }

            /* Barva pro záhlaví seznamu událostí */
            .jsm-event-list-title:after {
                background-color: <?php echo esc_attr($options['primary_color']); ?> !important;
            }
        </style>
        <?php
    }

    /**
     * Pomocná funkce pro převod HEX barvy na RGBA
     */
    private function hex_to_rgba($hex, $alpha = 1) {
        // Odstranění # pokud existuje
        $hex = str_replace('#', '', $hex);

        // Zpracování 3 nebo 6 znakové hex barvy
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        // Vrácení RGBA hodnoty
        return "rgba($r, $g, $b, $alpha)";
    }
}