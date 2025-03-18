<?php
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
        // Přidání stránky nastavení do menu
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Registrace nastavení
        add_action('admin_init', array($this, 'register_settings'));

        // Přidání dynamických CSS do hlavičky
        add_action('wp_head', array($this, 'output_custom_css'));

        // Načtení skriptů pro admin
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
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
     * Vykreslení stránky nastavení
     */
    public function render_settings_page() {
        // Kontrola oprávnění
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form method="post" action="options.php">
                <?php
                // Výstup skrytých polí nastavení
                settings_fields('wp_event_settings');

                // Výstup sekcí nastavení
                do_settings_sections('wp_event_settings');

                // Tlačítko pro uložení
                submit_button(__('Uložit nastavení', 'jsm-wp-event-calendar'));

                // Tlačítko pro reset nastavení
                ?>
                <p>
                    <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('reset-settings', 'true'), 'reset_event_settings', 'event_settings_nonce')); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Opravdu chcete obnovit výchozí nastavení? Tato akce nelze vrátit zpět.', 'jsm-wp-event-calendar')); ?>');">
                        <?php _e('Obnovit výchozí nastavení', 'jsm-wp-event-calendar'); ?>
                    </a>
                </p>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Inicializace color pickerů
            $('.color-picker').wpColorPicker();

            // Skupiny nastavení
            $('.settings-group-header').click(function() {
                $(this).next('.settings-group-content').slideToggle();
                $(this).toggleClass('closed');
            });

            // Na začátku otevřeme pouze první skupinu
            $('.settings-group-header:not(:first)').addClass('closed');
            $('.settings-group-content:not(:first)').hide();
        });
        </script>

        <style>
        .settings-group {
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
        }
        .settings-group-header {
            padding: 15px;
            border-bottom: 1px solid #ccd0d4;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
        }
        .settings-group-header:hover {
            background-color: #f8f9fa;
        }
        .settings-group-header:after {
            content: "\f142";
            font-family: dashicons;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
        }
        .settings-group-header.closed:after {
            content: "\f140";
        }
        .settings-group-content {
            padding: 15px;
        }
        .form-table th {
            padding: 15px 10px 15px 0;
        }
        .preview-box {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            border: 1px solid #ddd;
            margin-top: 20px;
            border-radius: 4px;
        }
        .color-sample {
            display: inline-block;
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border: 1px solid #ddd;
            vertical-align: middle;
            border-radius: 3px;
        }
        </style>
        <?php
    }

    /**
     * Registrace nastavení
     */
    public function register_settings() {
        // Registrace nastavení
        register_setting(
            'wp_event_settings',
            'wp_event_calendar_settings',
            array($this, 'sanitize_settings')
        );

        // Sekce barev
        add_settings_section(
            'wp_event_colors_section',
            '', // Záhlaví sekce přidáme ručně pomocí HTML
            array($this, 'colors_section_callback'),
            'wp_event_settings'
        );

        // Primární barvy
        $this->add_color_field('primary_color', __('Primární barva', 'jsm-wp-event-calendar'), __('Hlavní barva pluginu používaná pro tlačítka, kalendář, atd.', 'jsm-wp-event-calendar'));
        $this->add_color_field('primary_hover', __('Primární barva (hover)', 'jsm-wp-event-calendar'), __('Barva při najetí myší na prvky s primární barvou.', 'jsm-wp-event-calendar'));
        $this->add_color_field('secondary_color', __('Sekundární barva', 'jsm-wp-event-calendar'), __('Doplňková barva pro zvýraznění.', 'jsm-wp-event-calendar'));
        $this->add_color_field('secondary_hover', __('Sekundární barva (hover)', 'jsm-wp-event-calendar'), __('Barva při najetí myší na prvky se sekundární barvou.', 'jsm-wp-event-calendar'));
        $this->add_color_field('button_text', __('Barva textu tlačítek', 'jsm-wp-event-calendar'), __('Barva textu na tlačítkách.', 'jsm-wp-event-calendar'));

        // Barvy pozadí
        $this->add_color_field('background_color', __('Barva pozadí', 'jsm-wp-event-calendar'), __('Základní barva pozadí kalendáře.', 'jsm-wp-event-calendar'));
        $this->add_color_field('surface_color', __('Barva povrchu', 'jsm-wp-event-calendar'), __('Barva povrchu prvků (buňky kalendáře, karty).', 'jsm-wp-event-calendar'));
        $this->add_color_field('surface_hover', __('Barva povrchu (hover)', 'jsm-wp-event-calendar'), __('Barva povrchu prvků při najetí myší.', 'jsm-wp-event-calendar'));
        $this->add_color_field('border_color', __('Barva ohraničení', 'jsm-wp-event-calendar'), __('Barva okrajů a ohraničení.', 'jsm-wp-event-calendar'));

        // Barvy textu
        $this->add_color_field('text_primary', __('Primární barva textu', 'jsm-wp-event-calendar'), __('Hlavní barva textu.', 'jsm-wp-event-calendar'));
        $this->add_color_field('text_secondary', __('Sekundární barva textu', 'jsm-wp-event-calendar'), __('Barva méně důležitého textu.', 'jsm-wp-event-calendar'));

        // Sekce rozměrů
        add_settings_section(
            'wp_event_dimensions_section',
            '', // Záhlaví sekce přidáme ručně pomocí HTML
            array($this, 'dimensions_section_callback'),
            'wp_event_settings'
        );

        // Stíny
        $this->add_text_field('shadow_sm', __('Malý stín', 'jsm-wp-event-calendar'), __('CSS hodnota pro malý stín.', 'jsm-wp-event-calendar'));
        $this->add_text_field('shadow_md', __('Střední stín', 'jsm-wp-event-calendar'), __('CSS hodnota pro střední stín.', 'jsm-wp-event-calendar'));
        $this->add_text_field('shadow_lg', __('Velký stín', 'jsm-wp-event-calendar'), __('CSS hodnota pro velký stín.', 'jsm-wp-event-calendar'));

        // Rádius
        $this->add_text_field('border_radius_sm', __('Malý rádius', 'jsm-wp-event-calendar'), __('Hodnota pro malé zaoblení rohů.', 'jsm-wp-event-calendar'));
        $this->add_text_field('border_radius_md', __('Střední rádius', 'jsm-wp-event-calendar'), __('Hodnota pro střední zaoblení rohů.', 'jsm-wp-event-calendar'));
        $this->add_text_field('border_radius_lg', __('Velký rádius', 'jsm-wp-event-calendar'), __('Hodnota pro velké zaoblení rohů.', 'jsm-wp-event-calendar'));
        $this->add_text_field('button_radius', __('Rádius tlačítek', 'jsm-wp-event-calendar'), __('Hodnota zaoblení rohů pro tlačítka.', 'jsm-wp-event-calendar'));

        // Ostatní
        $this->add_text_field('calendar_spacing', __('Mezery v kalendáři', 'jsm-wp-event-calendar'), __('Velikost mezer mezi buňkami kalendáře.', 'jsm-wp-event-calendar'));

        // Sekce náhledu
        add_settings_section(
            'wp_event_preview_section',
            '', // Záhlaví sekce přidáme ručně pomocí HTML
            array($this, 'preview_section_callback'),
            'wp_event_settings'
        );

        // Zpracování resetu nastavení
        $this->maybe_reset_settings();
    }

    /**
     * Callback pro sekci barev
     */
    public function colors_section_callback() {
        echo '<div class="settings-group">';
        echo '<div class="settings-group-header">' . __('Barevné schéma', 'jsm-wp-event-calendar') . '</div>';
        echo '<div class="settings-group-content">';
        echo '<p>' . __('Nastavte barevné schéma kalendáře událostí.', 'jsm-wp-event-calendar') . '</p>';
    }

    /**
     * Callback pro sekci rozměrů
     */
    public function dimensions_section_callback() {
        echo '</div>'; // Uzavření předchozí skupiny obsahu
        echo '</div>'; // Uzavření předchozí skupiny

        echo '<div class="settings-group">';
        echo '<div class="settings-group-header">' . __('Rozměry a efekty', 'jsm-wp-event-calendar') . '</div>';
        echo '<div class="settings-group-content">';
        echo '<p>' . __('Nastavte rozměry, stíny a zaoblení prvků kalendáře.', 'jsm-wp-event-calendar') . '</p>';
    }

    /**
     * Callback pro sekci náhledu
     */
    public function preview_section_callback() {
        echo '</div>'; // Uzavření předchozí skupiny obsahu
        echo '</div>'; // Uzavření předchozí skupiny

        echo '<div class="settings-group">';
        echo '<div class="settings-group-header">' . __('Náhled nastavení', 'jsm-wp-event-calendar') . '</div>';
        echo '<div class="settings-group-content">';
        echo '<p>' . __('Náhled aktuálního nastavení barev.', 'jsm-wp-event-calendar') . '</p>';

        // Získání aktuálních nastavení
        $options = $this->get_settings();

        // Náhled barev
        echo '<div class="preview-box">';
        echo '<h3>' . __('Primární barvy', 'jsm-wp-event-calendar') . '</h3>';
        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['primary_color']) . '"></span>';
        echo '<span>' . __('Primární barva', 'jsm-wp-event-calendar') . ': ' . esc_html($options['primary_color']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['primary_hover']) . '"></span>';
        echo '<span>' . __('Primární barva (hover)', 'jsm-wp-event-calendar') . ': ' . esc_html($options['primary_hover']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['secondary_color']) . '"></span>';
        echo '<span>' . __('Sekundární barva', 'jsm-wp-event-calendar') . ': ' . esc_html($options['secondary_color']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['secondary_hover']) . '"></span>';
        echo '<span>' . __('Sekundární barva (hover)', 'jsm-wp-event-calendar') . ': ' . esc_html($options['secondary_hover']) . '</span>';
        echo '</div>';

        echo '<h3>' . __('Barvy pozadí', 'jsm-wp-event-calendar') . '</h3>';
        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['background_color']) . '"></span>';
        echo '<span>' . __('Barva pozadí', 'jsm-wp-event-calendar') . ': ' . esc_html($options['background_color']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['surface_color']) . '"></span>';
        echo '<span>' . __('Barva povrchu', 'jsm-wp-event-calendar') . ': ' . esc_html($options['surface_color']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['surface_hover']) . '"></span>';
        echo '<span>' . __('Barva povrchu (hover)', 'jsm-wp-event-calendar') . ': ' . esc_html($options['surface_hover']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['border_color']) . '"></span>';
        echo '<span>' . __('Barva ohraničení', 'jsm-wp-event-calendar') . ': ' . esc_html($options['border_color']) . '</span>';
        echo '</div>';

        echo '<h3>' . __('Barvy textu', 'jsm-wp-event-calendar') . '</h3>';
        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['text_primary']) . '"></span>';
        echo '<span>' . __('Primární barva textu', 'jsm-wp-event-calendar') . ': ' . esc_html($options['text_primary']) . '</span>';
        echo '</div>';

        echo '<div>';
        echo '<span class="color-sample" style="background-color:' . esc_attr($options['text_secondary']) . '"></span>';
        echo '<span>' . __('Sekundární barva textu', 'jsm-wp-event-calendar') . ': ' . esc_html($options['text_secondary']) . '</span>';
        echo '</div>';

        echo '<h3>' . __('Ukázka tlačítka', 'jsm-wp-event-calendar') . '</h3>';
        echo '<div style="margin-top: 10px; margin-bottom: 20px;">';
        echo '<button style="background-color: ' . esc_attr($options['primary_color']) . '; color: ' . esc_attr($options['button_text']) . '; border: none; padding: 10px 20px; border-radius: ' . esc_attr($options['button_radius']) . ';">';
        echo __('Ukázkové tlačítko', 'jsm-wp-event-calendar');
        echo '</button>';
        echo '</div>';

        echo '</div>'; // Konec preview-box

        echo '</div>'; // Uzavření skupiny obsahu
        echo '</div>'; // Uzavření skupiny
    }

    /**
     * Přidání pole pro výběr barvy
     */
    private function add_color_field($id, $title, $description = '') {
        add_settings_field(
            'wp_event_' . $id,
            $title,
            array($this, 'render_color_field'),
            'wp_event_settings',
            'wp_event_colors_section',
            array(
                'id' => $id,
                'description' => $description
            )
        );
    }

    /**
     * Přidání textového pole
     */
    private function add_text_field($id, $title, $description = '') {
        add_settings_field(
            'wp_event_' . $id,
            $title,
            array($this, 'render_text_field'),
            'wp_event_settings',
            'wp_event_dimensions_section',
            array(
                'id' => $id,
                'description' => $description
            )
        );
    }

    /**
     * Vykreslení pole pro výběr barvy
     */
    public function render_color_field($args) {
        $id = $args['id'];
        $options = $this->get_settings();
        $value = isset($options[$id]) ? $options[$id] : $this->defaults[$id];
        ?>
        <input type="text" class="color-picker" id="wp_event_<?php echo esc_attr($id); ?>" name="wp_event_calendar_settings[<?php echo esc_attr($id); ?>]" value="<?php echo esc_attr($value); ?>" />
        <?php if (!empty($args['description'])) : ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Vykreslení textového pole
     */
    public function render_text_field($args) {
        $id = $args['id'];
        $options = $this->get_settings();
        $value = isset($options[$id]) ? $options[$id] : $this->defaults[$id];
        ?>
        <input type="text" id="wp_event_<?php echo esc_attr($id); ?>" name="wp_event_calendar_settings[<?php echo esc_attr($id); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php if (!empty($args['description'])) : ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Sanitace nastavení
     */
    public function sanitize_settings($input) {
        $sanitized_input = array();

        // Procházení všech nastavení
        foreach ($this->defaults as $key => $default_value) {
            if (isset($input[$key])) {
                if (strpos($key, 'color') !== false) {
                    // Sanitace barvy
                    $sanitized_input[$key] = sanitize_hex_color($input[$key]);
                } else {
                    // Sanitace ostatních hodnot
                    $sanitized_input[$key] = sanitize_text_field($input[$key]);
                }
            } else {
                $sanitized_input[$key] = $default_value;
            }
        }

        return $sanitized_input;
    }

    /**
     * Kontrola a případné resetování nastavení
     */
    private function maybe_reset_settings() {
        if (isset($_GET['reset-settings']) && $_GET['reset-settings'] === 'true') {
            // Ověření nonce
            if (isset($_GET['event_settings_nonce']) && wp_verify_nonce($_GET['event_settings_nonce'], 'reset_event_settings')) {
                // Reset nastavení
                update_option('wp_event_calendar_settings', $this->defaults);

                // Přesměrování zpět na stránku nastavení
                wp_redirect(add_query_arg(array('page' => 'wp_event_settings', 'settings-updated' => 'true', 'reset' => 'true'), admin_url('edit.php?post_type=wp_event')));
                exit;
            }
        }
    }

    /**
     * Získání nastavení s výchozími hodnotami
     */
    public function get_settings() {
        $options = get_option('wp_event_calendar_settings', array());
        return wp_parse_args($options, $this->defaults);
    }

    /**
     * Výstup vlastních CSS stylů
     */
    public function output_custom_css() {
        $options = $this->get_settings();

        // Generování CSS proměnných na základě nastavení
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
                --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                --calendar-spacing: <?php echo esc_attr($options['calendar_spacing']); ?>;
            }
        </style>
        <?php
    }
}

// Inicializace třídy nastavení
$wp_event_settings = new WP_Event_Settings();