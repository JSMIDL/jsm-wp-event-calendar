<?php
/**
 * Class for managing event calendar settings
 */
class WP_Event_Settings {

    /**
     * Prevent multiple initialization
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * Default settings values
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
     * Get default settings values
     */
    public static function get_defaults() {
        return self::$defaults;
    }

    /**
     * Initialize plugin settings
     */
    public static function init() {
        // Protection against multiple initialization
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        $instance = new self();

        add_action('admin_init', array($instance, 'register_settings'));
        add_action('admin_enqueue_scripts', array($instance, 'enqueue_admin_scripts'));
        add_action('wp_enqueue_scripts', array($instance, 'enqueue_custom_styles'), 99);
        add_action('wp_enqueue_scripts', array($instance, 'enqueue_frontend_styles'));
        add_action('admin_notices', array($instance, 'admin_notices'));
        add_action('admin_init', array($instance, 'maybe_reset_settings'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wp_event_settings') !== false || strpos($hook, 'wp_event_docs') !== false) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery');

            wp_enqueue_script(
                'jsm-event-settings',
                plugin_dir_url(dirname(__FILE__)) . 'admin/js/event-settings.js',
                array('jquery', 'wp-color-picker'),
                defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0',
                true
            );

            wp_localize_script('jsm-event-settings', 'jsmEventSettings', array(
                'defaults' => self::$defaults,
                'resetUrl' => wp_nonce_url(add_query_arg('reset-settings', 'true'), 'jsm_reset_settings_nonce'),
                'resetConfirmText' => __('Do you really want to reset to default settings? This action cannot be undone.', 'jsm-wp-event-calendar'),
                'resetNonce' => wp_create_nonce('jsm_reset_settings_nonce')
            ));
        }
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_frontend_styles() {
        wp_enqueue_style(
            'jsm-wp-event-calendar',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/event-calendar.css',
            array(),
            defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0'
        );

        wp_enqueue_style(
            'jsm-wp-event-calendar-mobile',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/event-calendar-mobile.css',
            array('jsm-wp-event-calendar'),
            defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0',
            'only screen and (max-width: 768px)'
        );
    }

    /**
     * Enqueue custom styles from settings
     */
    public function enqueue_custom_styles() {
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        $custom_css = $this->generate_custom_css($options);
        wp_add_inline_style('jsm-wp-event-calendar', $custom_css);
    }

    /**
     * Generate custom CSS from settings
     *
     * @param array $options Settings
     * @return string Custom CSS
     */
    private function generate_custom_css($options) {
        return "
        :root {
            --primary-color: {$options['primary_color']} !important;
            --primary-hover: {$options['primary_hover']} !important;
            --secondary-color: {$options['secondary_color']} !important;
            --secondary-hover: {$options['secondary_hover']} !important;
            --button-text: {$options['button_text']} !important;
            --background-color: {$options['background_color']} !important;
            --surface-color: {$options['surface_color']} !important;
            --surface-hover: {$options['surface_hover']} !important;
            --border-color: {$options['border_color']} !important;
            --text-primary: {$options['text_primary']} !important;
            --text-secondary: {$options['text_secondary']} !important;
            --shadow-sm: {$options['shadow_sm']} !important;
            --shadow-md: {$options['shadow_md']} !important;
            --shadow-lg: {$options['shadow_lg']} !important;
            --border-radius-sm: {$options['border_radius_sm']} !important;
            --border-radius-md: {$options['border_radius_md']} !important;
            --border-radius-lg: {$options['border_radius_lg']} !important;
            --button-radius: {$options['button_radius']} !important;
            --calendar-spacing: {$options['calendar_spacing']} !important;
        }
        ";
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'wp_event_settings',
            'wp_event_calendar_settings',
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'colors_section',
            __('Color Scheme', 'jsm-wp-event-calendar'),
            array($this, 'colors_section_callback'),
            'wp_event_settings'
        );

        add_settings_section(
            'dimensions_section',
            __('Dimensions and Effects', 'jsm-wp-event-calendar'),
            array($this, 'dimensions_section_callback'),
            'wp_event_settings'
        );

        $this->add_color_fields();
        $this->add_dimension_fields();
    }

    /**
     * Colors section description
     */
    public function colors_section_callback() {
        echo '<p>' . __('Set the color scheme for the event calendar.', 'jsm-wp-event-calendar') . '</p>';
    }

    /**
     * Dimensions section description
     */
    public function dimensions_section_callback() {
        echo '<p>' . __('Set dimensions, shadows, and rounded corners for calendar elements.', 'jsm-wp-event-calendar') . '</p>';
    }

    /**
     * Add color setting fields
     */
    private function add_color_fields() {
        $color_fields = array(
            'primary_color' => __('Primary Color', 'jsm-wp-event-calendar'),
            'primary_hover' => __('Primary Color (hover)', 'jsm-wp-event-calendar'),
            'secondary_color' => __('Secondary Color', 'jsm-wp-event-calendar'),
            'secondary_hover' => __('Secondary Color (hover)', 'jsm-wp-event-calendar'),
            'button_text' => __('Button Text Color', 'jsm-wp-event-calendar'),
            'background_color' => __('Background Color', 'jsm-wp-event-calendar'),
            'surface_color' => __('Surface Color', 'jsm-wp-event-calendar'),
            'surface_hover' => __('Surface Color (hover)', 'jsm-wp-event-calendar'),
            'border_color' => __('Border Color', 'jsm-wp-event-calendar'),
            'text_primary' => __('Primary Text Color', 'jsm-wp-event-calendar'),
            'text_secondary' => __('Secondary Text Color', 'jsm-wp-event-calendar'),
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
     * Add dimension setting fields
     */
    private function add_dimension_fields() {
        $dimension_fields = array(
            'shadow_sm' => __('Small Shadow', 'jsm-wp-event-calendar'),
            'shadow_md' => __('Medium Shadow', 'jsm-wp-event-calendar'),
            'shadow_lg' => __('Large Shadow', 'jsm-wp-event-calendar'),
            'border_radius_sm' => __('Small Radius', 'jsm-wp-event-calendar'),
            'border_radius_md' => __('Medium Radius', 'jsm-wp-event-calendar'),
            'border_radius_lg' => __('Large Radius', 'jsm-wp-event-calendar'),
            'button_radius' => __('Button Radius', 'jsm-wp-event-calendar'),
            'calendar_spacing' => __('Calendar Spacing', 'jsm-wp-event-calendar'),
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
     * Color field callback
     */
    public function color_field_callback($args) {
        $id = $args['id'];
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        echo '<input type="text" id="' . esc_attr($id) . '" name="wp_event_calendar_settings[' . esc_attr($id) . ']" value="' . esc_attr($options[$id]) . '" class="jsm-color-picker" data-default-color="' . esc_attr(self::$defaults[$id]) . '" />';
    }

    /**
     * Text field callback
     */
    public function text_field_callback($args) {
        $id = $args['id'];
        $options = get_option('wp_event_calendar_settings', array());
        $options = wp_parse_args($options, self::$defaults);

        echo '<input type="text" id="' . esc_attr($id) . '" name="wp_event_calendar_settings[' . esc_attr($id) . ']" value="' . esc_attr($options[$id]) . '" class="regular-text" data-default="' . esc_attr(self::$defaults[$id]) . '" />';
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();
        foreach (self::$defaults as $key => $default_value) {
            $sanitized_input[$key] = isset($input[$key]) ? sanitize_text_field($input[$key]) : $default_value;
        }
        return $sanitized_input;
    }

    /**
     * Admin notices
     */
    public function admin_notices() {
        if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Calendar settings have been reset to defaults.', 'jsm-wp-event-calendar') . '</p></div>';
        }
    }

    /**
     * Reset settings handler
     */
    public function maybe_reset_settings() {
        if (
            isset($_GET['reset-settings']) &&
            $_GET['reset-settings'] === 'true' &&
            isset($_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'jsm_reset_settings_nonce')
        ) {
            update_option('wp_event_calendar_settings', self::get_defaults());
            wp_redirect(admin_url('edit.php?post_type=jsm_wp_event&page=wp_event_settings&reset=success'));
            exit;
        }
    }
}