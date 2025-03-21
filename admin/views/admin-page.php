<?php
/**
 * Šablona pro stránku nastavení pluginu v administraci
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

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
</div>

<style>
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

    @media screen and (max-width: 782px) {
        .jsm-admin-content {
            flex-direction: column;
        }
    }
</style>