<?php
/**
 * Upravená verze souboru
 * admin/class-event-settings.php
 */

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
    public static $defaults = array(
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
     * Inicializace pluginu
     */
    public static function init() {
        $instance = new self();
        add_action('admin_menu', array($instance, 'add_settings_page'));
        add_action('admin_init', array($instance, 'register_settings'));
        add_action('wp_head', array($instance, 'output_custom_css'));
    }

    /**
     * Přidání stránky nastavení do menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=jsm_wp_event_calendar',
            __('Nastavení kalendáře', 'jsm-wp-event-calendar'),
            __('Nastavení', 'jsm-wp-event-calendar'),
            'manage_options',
            'jsm_wp_event_settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Vykreslení stránky nastavení
     */
    public function render_settings_page() {
        // Kontrola oprávnění
        if (!current_user_can('manage_options')) {
            return;
        }

        // Získání aktuálních hodnot
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        // Vložení šablony nastavení
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'admin/views/admin-page.php';
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

        echo '<input type="color" id="' . esc_attr($id) . '" name="wp_event_calendar_settings[' . esc_attr($id) . ']" value="' . esc_attr($options[$id]) . '" class="jsm-color-picker" />';
    }

    /**
     * Callback pro textové pole
     */
    public function text_field_callback($args) {
        $id = $args['id'];
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        echo '<input type="text" id="' . esc_attr($id) . '" name="wp_event_calendar_settings[' . esc_attr($id) . ']" value="' . esc_attr($options[$id]) . '" class="regular-text" />';
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
     */
    public function output_custom_css() {
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);
        ?>
        <style>
            :root {
                --primary-color: <?php echo esc_attr($options['primary_color']); ?>;
                --primary-hover: <?php echo esc_attr($options['primary_hover']); ?>;
                --secondary-color: <?php echo esc_attr($options['secondary_color']); ?>;
                --secondary-hover: <?php echo esc_attr($options['secondary_hover']); ?>;
                --button-text: <?php echo esc_attr($options['button_text']); ?>;
                --background-color: <?php echo esc_attr($options['background_color']); ?>;
                --surface-color: <?php echo esc_attr($options['surface_color']); ?>;
                --surface-hover: <?php echo esc_attr($options['surface_hover']); ?>;
                --border-color: <?php echo esc_attr($options['border_color']); ?>;
                --text-primary: <?php echo esc_attr($options['text_primary']); ?>;
                --text-secondary: <?php echo esc_attr($options['text_secondary']); ?>;
                --shadow-sm: <?php echo esc_attr($options['shadow_sm']); ?>;
                --shadow-md: <?php echo esc_attr($options['shadow_md']); ?>;
                --shadow-lg: <?php echo esc_attr($options['shadow_lg']); ?>;
                --border-radius-sm: <?php echo esc_attr($options['border_radius_sm']); ?>;
                --border-radius-md: <?php echo esc_attr($options['border_radius_md']); ?>;
                --border-radius-lg: <?php echo esc_attr($options['border_radius_lg']); ?>;
                --button-radius: <?php echo esc_attr($options['button_radius']); ?>;
                --calendar-spacing: <?php echo esc_attr($options['calendar_spacing']); ?>;
            }
        </style>
        <?php
    }
}