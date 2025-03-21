<?php
/**
 * Šablona pro stránku nastavení pluginu v administraci
 */

// Získání aktuálního tabu
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'docs';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?post_type=jsm_wp_event&page=wp_event_settings&tab=docs" class="nav-tab <?php echo $active_tab == 'docs' ? 'nav-tab-active' : ''; ?>"><?php _e('Dokumentace', 'jsm-wp-event-calendar'); ?></a>
        <a href="?post_type=jsm_wp_event&page=wp_event_settings&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Nastavení pluginu', 'jsm-wp-event-calendar'); ?></a>
    </h2>

    <?php if ($active_tab == 'docs') : ?>
    <!-- Dokumentační tab -->
    <div class="jsm-admin-content">
        <div class="jsm-admin-main">
            <div class="jsm-admin-card">
                <h2><?php _e('Jak používat plugin', 'jsm-wp-event-calendar'); ?></h2>

                <div class="jsm-admin-section">
                    <h3><?php _e('Přidání nové události', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('Pro přidání nové události přejděte do sekce <strong>Události</strong> v menu administrace a klikněte na <strong>Přidat novou</strong>.', 'jsm-wp-event-calendar'); ?></p>
                    <p><?php _e('Vyplňte název, popis a nastavte datum a čas události. Můžete také nastavit vlastní URL adresu, na kterou bude odkazovat tlačítko.', 'jsm-wp-event-calendar'); ?></p>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Vložení kalendáře do stránky', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('Pro zobrazení kalendáře na stránce použijte shortcode:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[event_calendar]</code></pre>

                    <p><?php _e('Můžete použít následující atributy pro přizpůsobení kalendáře:', 'jsm-wp-event-calendar'); ?></p>
                    <ul>
                        <li><code>month</code> - <?php _e('Číslo měsíce (1-12)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>year</code> - <?php _e('Rok (např. 2023)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>show_list</code> - <?php _e('Zobrazit seznam událostí pod kalendářem (yes/no)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>category</code> - <?php _e('ID nebo slug kategorie pro filtrování událostí', 'jsm-wp-event-calendar'); ?></li>
                    </ul>

                    <p><?php _e('Příklad s parametry:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[event_calendar month="1" year="2023" show_list="yes" category="akce"]</code></pre>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Vložení seznamu událostí do stránky', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('Pro zobrazení seznamu událostí na stránce použijte shortcode:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[event_list]</code></pre>

                    <p><?php _e('Můžete použít následující atributy pro přizpůsobení seznamu:', 'jsm-wp-event-calendar'); ?></p>
                    <ul>
                        <li><code>limit</code> - <?php _e('Počet zobrazených událostí', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>category</code> - <?php _e('ID nebo slug kategorie pro filtrování událostí', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>past</code> - <?php _e('Zobrazit proběhlé události (yes/no)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>layout</code> - <?php _e('Způsob zobrazení (list/grid)', 'jsm-wp-event-calendar'); ?></li>
                    </ul>

                    <p><?php _e('Příklad s parametry:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[event_list limit="5" past="no" layout="grid" category="akce"]</code></pre>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Vložení detailu události do stránky', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('Pro zobrazení detailu konkrétní události na stránce použijte shortcode:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[event_detail id="123"]</code></pre>

                    <p><?php _e('Kde <code>id</code> je ID události, kterou chcete zobrazit.', 'jsm-wp-event-calendar'); ?></p>
                </div>
            </div>
        </div>

        <div class="jsm-admin-sidebar">
            <div class="jsm-admin-card">
                <h3><?php _e('O pluginu', 'jsm-wp-event-calendar'); ?></h3>
                <p><?php _e('JŠM WP Event Calendar je jednoduchý plugin pro správu a zobrazení kalendáře událostí na vašem webu.', 'jsm-wp-event-calendar'); ?></p>
                <p><?php _e('Verze:', 'jsm-wp-event-calendar'); ?> <?php echo WP_EVENT_CALENDAR_VERSION; ?></p>
            </div>

            <div class="jsm-admin-card">
                <h3><?php _e('Výchozí nastavení', 'jsm-wp-event-calendar'); ?></h3>
                <p><?php _e('Celý plugin je navržen tak, aby fungoval ihned po aktivaci.', 'jsm-wp-event-calendar'); ?></p>
                <ul>
                    <li><?php _e('Responzivní design', 'jsm-wp-event-calendar'); ?></li>
                    <li><?php _e('Podpora češtiny a angličtiny', 'jsm-wp-event-calendar'); ?></li>
                    <li><?php _e('Snadné použití shortcodů', 'jsm-wp-event-calendar'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <?php else : ?>
    <!-- Nastavení tab -->
    <div class="jsm-settings-content">
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_event_settings');
            do_settings_sections('wp_event_settings');
            ?>

            <div class="jsm-settings-preview">
                <h3><?php _e('Náhled nastavení', 'jsm-wp-event-calendar'); ?></h3>
                <div class="jsm-color-previews">
                    <div class="jsm-preview-section">
                        <h4><?php _e('Primární barvy', 'jsm-wp-event-calendar'); ?></h4>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['primary_color']); ?>">
                            <?php echo esc_html($options['primary_color']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['primary_hover']); ?>">
                            <?php echo esc_html($options['primary_hover']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['secondary_color']); ?>">
                            <?php echo esc_html($options['secondary_color']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['secondary_hover']); ?>">
                            <?php echo esc_html($options['secondary_hover']); ?>
                        </div>
                    </div>

                    <div class="jsm-preview-section">
                        <h4><?php _e('Barvy pozadí', 'jsm-wp-event-calendar'); ?></h4>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['background_color']); ?>; color: #333;">
                            <?php echo esc_html($options['background_color']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['surface_color']); ?>; color: #333;">
                            <?php echo esc_html($options['surface_color']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['surface_hover']); ?>; color: #333;">
                            <?php echo esc_html($options['surface_hover']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['border_color']); ?>; color: #333;">
                            <?php echo esc_html($options['border_color']); ?>
                        </div>
                    </div>

                    <div class="jsm-preview-section">
                        <h4><?php _e('Barvy textu', 'jsm-wp-event-calendar'); ?></h4>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['text_primary']); ?>">
                            <?php echo esc_html($options['text_primary']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['text_secondary']); ?>">
                            <?php echo esc_html($options['text_secondary']); ?>
                        </div>
                        <div class="jsm-color-preview" style="background-color: <?php echo esc_attr($options['button_text']); ?>">
                            <?php echo esc_html($options['button_text']); ?>
                        </div>
                    </div>
                </div>

                <div class="jsm-button-preview">
                    <h4><?php _e('Ukázka tlačítka', 'jsm-wp-event-calendar'); ?></h4>
                    <button class="jsm-preview-button" style="
                        background-color: <?php echo esc_attr($options['primary_color']); ?>;
                        color: <?php echo esc_attr($options['button_text']); ?>;
                        border-radius: <?php echo esc_attr($options['button_radius']); ?>;
                        box-shadow: <?php echo esc_attr($options['shadow_sm']); ?>;
                        border: none;
                        padding: 8px 16px;
                        cursor: pointer;
                        transition: all 0.2s;
                    "><?php _e('Ukázkové tlačítko', 'jsm-wp-event-calendar'); ?></button>
                </div>

                <div class="jsm-reset-settings">
                    <button type="button" id="reset-settings" class="button button-secondary"><?php _e('Obnovit výchozí nastavení', 'jsm-wp-event-calendar'); ?></button>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php endif; ?>
</div>

<style>
    /* Obecné styly */
    .jsm-admin-content {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }

    .jsm-admin-main {
        flex: 2;
    }

    .jsm-admin-sidebar {
        flex: 1;
        min-width: 250px;
    }

    .jsm-admin-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
        padding: 15px;
    }

    .jsm-admin-section {
        margin-bottom: 25px;
    }

    .jsm-admin-section h3 {
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    pre {
        background: #f6f7f7;
        padding: 10px;
        border: 1px solid #ddd;
        overflow: auto;
    }

    /* Styly pro nastavení */
    .jsm-settings-content {
        margin-top: 20px;
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    }

    .jsm-settings-preview {
        margin-top: 30px;
        padding: 20px;
        background-color: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 5px;
    }

    .jsm-color-previews {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .jsm-preview-section {
        flex: 1;
        min-width: 200px;
    }

    .jsm-color-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 40px;
        color: white;
        margin-bottom: 10px;
        border-radius: 4px;
        font-family: monospace;
    }

    .jsm-button-preview {
        margin-top: 20px;
        padding: 15px;
        background-color: #f0f0f0;
        border-radius: 4px;
        text-align: center;
    }

    .jsm-reset-settings {
        margin-top: 30px;
        text-align: right;
    }

    /* Responzivní styly */
    @media screen and (max-width: 782px) {
        .jsm-admin-content,
        .jsm-color-previews {
            flex-direction: column;
        }
    }
</style>

<script>
(function($) {
    $(document).ready(function() {
        // Reset settings button
        $('#reset-settings').on('click', function() {
            if (confirm('<?php _e('Opravdu chcete obnovit výchozí nastavení? Tato akce nelze vrátit zpět.', 'jsm-wp-event-calendar'); ?>')) {
                // Zde můžete přidat kód pro reset nastavení
                // Nebo přesměrovat na stránku s resetem
            }
        });

        // Náhled barev při změně
        $('.jsm-color-picker').on('change', function() {
            var id = $(this).attr('id');
            var color = $(this).val();

            // Update příslušného náhledu podle ID
            // Toto by vyžadovalo více logiky v závislosti na struktuře vašich náhledů
        });
    });
})(jQuery);
</script>