<?php
/**
 * Třída pro správu kalendáře událostí
 */
class WP_Event_Calendar {

    /**
     * Konstruktor
     */
    public function __construct() {
        // Načtení CSS a JS pro frontend
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Registrace AJAX endpointů pro kalendář
        $this->register_rest_routes();
    }

    /**
     * Načtení skriptů a stylů pro frontend
     */
    public function enqueue_scripts() {
        // Zajistíme, že jQuery je načteno
        wp_enqueue_script('jquery');

        // Debugování cest
        error_log('Plugin URL: ' . WP_EVENT_CALENDAR_PLUGIN_URL);

        // Načtení CSS pro kalendář s verzí pro cache busting
        wp_enqueue_style(
            'jsm-wp-event-calendar',
            WP_EVENT_CALENDAR_PLUGIN_URL . 'assets/css/event-calendar.css',
            array(),
            WP_EVENT_CALENDAR_VERSION . '.' . time()
        );

        // Responzivní styly
        wp_enqueue_style(
            'jsm-wp-event-calendar-mobile',
            WP_EVENT_CALENDAR_PLUGIN_URL . 'assets/css/event-calendar-mobile.css',
            array('jsm-wp-event-calendar'),
            WP_EVENT_CALENDAR_VERSION . '.' . time(),
            'only screen and (max-width: 768px)'
        );

        // Načtení JS pro kalendář
        wp_enqueue_script(
            'jsm-wp-event-calendar',
            WP_EVENT_CALENDAR_PLUGIN_URL . 'assets/js/event-calendar.js',
            array('jquery'),
            WP_EVENT_CALENDAR_VERSION . '.' . time(),
            true
        );

        // Lokalizace proměnných pro JavaScript
        wp_localize_script('jsm-wp-event-calendar', 'jsmEventCalendar', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'action' => 'get_events_for_calendar',
            'nonce' => wp_create_nonce('jsm_event_calendar_nonce'),
            'i18n' => array(
                'loadingText' => __('Načítání událostí...', 'jsm-wp-event-calendar'),
                'noEventsText' => __('Žádné události k zobrazení', 'jsm-wp-event-calendar'),
                'eventsListTitle' => __('Seznam událostí', 'jsm-wp-event-calendar'),
                'months' => array(
                    __('Leden', 'jsm-wp-event-calendar'),
                    __('Únor', 'jsm-wp-event-calendar'),
                    __('Březen', 'jsm-wp-event-calendar'),
                    __('Duben', 'jsm-wp-event-calendar'),
                    __('Květen', 'jsm-wp-event-calendar'),
                    __('Červen', 'jsm-wp-event-calendar'),
                    __('Červenec', 'jsm-wp-event-calendar'),
                    __('Srpen', 'jsm-wp-event-calendar'),
                    __('Září', 'jsm-wp-event-calendar'),
                    __('Říjen', 'jsm-wp-event-calendar'),
                    __('Listopad', 'jsm-wp-event-calendar'),
                    __('Prosinec', 'jsm-wp-event-calendar')
                ),
                'weekdays' => array(

                    __('Pondělí', 'jsm-wp-event-calendar'),
                    __('Úterý', 'jsm-wp-event-calendar'),
                    __('Středa', 'jsm-wp-event-calendar'),
                    __('Čtvrtek', 'jsm-wp-event-calendar'),
                    __('Pátek', 'jsm-wp-event-calendar'),
                    __('Sobota', 'jsm-wp-event-calendar'),
                     __('Neděle', 'jsm-wp-event-calendar'),
                ),
                'weekdaysShort' => array(

                    __('Po', 'jsm-wp-event-calendar'),
                    __('Út', 'jsm-wp-event-calendar'),
                    __('St', 'jsm-wp-event-calendar'),
                    __('Čt', 'jsm-wp-event-calendar'),
                    __('Pá', 'jsm-wp-event-calendar'),
                    __('So', 'jsm-wp-event-calendar'),
                       __('Ne', 'jsm-wp-event-calendar'),
                )
            )
        ));
    }

    /**
     * Registrace REST API routes
     */
    public function register_rest_routes() {
        // AJAX pro kalendář událostí
        add_action('wp_ajax_get_events_for_calendar', array($this, 'get_events_for_calendar'));
        add_action('wp_ajax_nopriv_get_events_for_calendar', array($this, 'get_events_for_calendar'));

        // AJAX pro detail události - nový endpoint
        add_action('wp_ajax_get_event_detail', array($this, 'get_event_detail_ajax'));
        add_action('wp_ajax_nopriv_get_event_detail', array($this, 'get_event_detail_ajax'));
    }

    /**
     * AJAX handler pro získání detailu události
     */
    public function get_event_detail_ajax() {
        // Kontrola nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'jsm_event_calendar_nonce')) {
            wp_send_json_error('Neplatný bezpečnostní token');
        }

        // Kontrola platného ID
        $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
        if (!$event_id) {
            wp_send_json_error('Neplatné ID události');
        }

        // Získání detailu události
        $event_html = $this->render_event_detail($event_id);

        if (empty($event_html)) {
            wp_send_json_error('Událost nebyla nalezena');
        }

        wp_send_json_success($event_html);
    }

    /**
     * Získání událostí pro kalendář
     */
    public function get_events_for_calendar() {
        // Kontrola nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'jsm_event_calendar_nonce')) {
            wp_send_json_error('Neplatný bezpečnostní token');
        }

        $month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        // Počáteční a koncové datum pro dotaz
        $start_date = $year . '-' . $month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        // Aktuální datum pro filtraci minulých událostí
        $today = date('Y-m-d');

        // Dotaz pro události v daném měsíci - pouze budoucí nebo aktuální
        $args = array(
            'post_type' => 'wp_event',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                'relation' => 'AND',
                // Filtr na budoucí události
                array(
                    'relation' => 'OR',
                    // Události s datem začátku dnes nebo později
                    array(
                        'key' => '_event_start_date',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATE'
                    ),
                    // Události s datem konce dnes nebo později (pokud mají konec)
                    array(
                        'key' => '_event_end_date',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATE'
                    )
                ),
                // Filtrace na daný měsíc
                array(
                    'relation' => 'OR',
                    // Události začínající v daném měsíci
                    array(
                        'key' => '_event_start_date',
                        'value' => array($start_date, $end_date),
                        'compare' => 'BETWEEN',
                        'type' => 'DATE'
                    ),
                    // Události končící v daném měsíci
                    array(
                        'key' => '_event_end_date',
                        'value' => array($start_date, $end_date),
                        'compare' => 'BETWEEN',
                        'type' => 'DATE'
                    ),
                    // Události, které začínají před začátkem měsíce a končí po konci měsíce
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => '_event_start_date',
                            'value' => $start_date,
                            'compare' => '<',
                            'type' => 'DATE'
                        ),
                        array(
                            'key' => '_event_end_date',
                            'value' => $end_date,
                            'compare' => '>',
                            'type' => 'DATE'
                        )
                    )
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_event_start_date',
            'order' => 'ASC'
        );

        $query = new WP_Query($args);
        $events = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $start_date = get_post_meta($post_id, '_event_start_date', true);
                $end_date = get_post_meta($post_id, '_event_end_date', true);
                $start_time = get_post_meta($post_id, '_event_start_time', true);
                $end_time = get_post_meta($post_id, '_event_end_time', true);
                $all_day = get_post_meta($post_id, '_event_all_day', true);
                $url = get_post_meta($post_id, '_event_url', true);
                $button_text = get_post_meta($post_id, '_event_button_text', true);

                // Pokud není zadáno datum konce, použijeme datum začátku
                if (empty($end_date)) {
                    $end_date = $start_date;
                }

                // Formátování času pro zobrazení
                $time_display = '';
                if ('1' !== $all_day) {
                    if (!empty($start_time)) {
                        $time_display = date_i18n(get_option('time_format'), strtotime($start_time));

                        if (!empty($end_time)) {
                            $time_display .= ' - ' . date_i18n(get_option('time_format'), strtotime($end_time));
                        }
                    }
                } else {
                    $time_display = __('Celý den', 'jsm-wp-event-calendar');
                }

                // Formátování data pro zobrazení
                $date_display = date_i18n(get_option('date_format'), strtotime($start_date));
                if ($end_date !== $start_date) {
                    $date_display .= ' - ' . date_i18n(get_option('date_format'), strtotime($end_date));
                }

                // Sestavení události
                $event = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'startDate' => $start_date,
                    'endDate' => $end_date,
                    'dateDisplay' => $date_display,
                    'timeDisplay' => $time_display,
                    'allDay' => ('1' === $all_day),
                    'url' => get_permalink($post_id),
                    'excerpt' => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20),
                    'customUrl' => $url,
                    'buttonText' => !empty($button_text) ? $button_text : __('Více informací', 'jsm-wp-event-calendar')
                );

                // Přidání události do pole
                $events[] = $event;
            }

            wp_reset_postdata();
        }

        wp_send_json_success($events);
    }

    /**
     * Získání událostí pro zobrazení v seznamu
     *
     * @param array $args Argumenty pro WP_Query
     * @return array Pole událostí
     */
    public function get_events($args = array()) {
        $default_args = array(
            'post_type' => 'wp_event',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'meta_key' => '_event_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_event_start_date',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        );

        $args = wp_parse_args($args, $default_args);
        $query = new WP_Query($args);
        $events = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $start_date = get_post_meta($post_id, '_event_start_date', true);
                $end_date = get_post_meta($post_id, '_event_end_date', true);
                $start_time = get_post_meta($post_id, '_event_start_time', true);
                $end_time = get_post_meta($post_id, '_event_end_time', true);
                $all_day = get_post_meta($post_id, '_event_all_day', true);
                $url = get_post_meta($post_id, '_event_url', true);
                $button_text = get_post_meta($post_id, '_event_button_text', true);

                // Sestavení události
                $event = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20),
                    'permalink' => get_permalink($post_id),
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'all_day' => ('1' === $all_day),
                    'custom_url' => $url,
                    'button_text' => !empty($button_text) ? $button_text : __('Více informací', 'jsm-wp-event-calendar'),
                    'thumbnail' => has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, 'medium') : ''
                );

                $events[] = $event;
            }

            wp_reset_postdata();
        }

        return $events;
    }

    /**
     * Vykreslení kalendáře
     *
     * @param array $atts Atributy shortcodu
     * @return string HTML kalendáře
     */
    public function render_calendar($atts = array()) {
        $atts = shortcode_atts(array(
            'month' => date('m'),
            'year' => date('Y'),
            'show_list' => 'yes',
            'category' => ''
        ), $atts, 'event_calendar');

        // Načtení šablony
        ob_start();
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'templates/calendar.php';
        return ob_get_clean();
    }

    /**
     * Vykreslení seznamu událostí
     *
     * @param array $atts Atributy shortcodu
     * @return string HTML seznamu událostí
     */
    public function render_event_list($atts = array()) {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'category' => '',
            'past' => 'no',
            'layout' => 'list'
        ), $atts, 'event_list');

        $args = array(
            'posts_per_page' => intval($atts['limit'])
        );

        // Přidání kategorie
        if (!empty($atts['category'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => explode(',', $atts['category'])
                )
            );
        }

        // Minulé nebo budoucí události
        if ('yes' === $atts['past']) {
            $args['meta_query'] = array(
                array(
                    'key' => '_event_start_date',
                    'value' => date('Y-m-d'),
                    'compare' => '<',
                    'type' => 'DATE'
                )
            );
            $args['order'] = 'DESC';
        } else {
            $args['meta_query'] = array(
                array(
                    'key' => '_event_start_date',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            );
        }

        $events = $this->get_events($args);

        // Načtení šablony
        ob_start();
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'templates/event-list.php';
        return ob_get_clean();
    }

    /**
     * Vykreslení detailu události
     *
     * @param int $post_id ID události
     * @return string HTML detailu události
     */
    public function render_event_detail($post_id) {
        $post = get_post($post_id);

        if (!$post || 'wp_event' !== $post->post_type) {
            return '';
        }

        $start_date = get_post_meta($post_id, '_event_start_date', true);
        $end_date = get_post_meta($post_id, '_event_end_date', true);
        $start_time = get_post_meta($post_id, '_event_start_time', true);
        $end_time = get_post_meta($post_id, '_event_end_time', true);
        $all_day = get_post_meta($post_id, '_event_all_day', true);
        $url = get_post_meta($post_id, '_event_url', true);
        $button_text = get_post_meta($post_id, '_event_button_text', true);

        // Sestavení události
        $event = array(
            'id' => $post_id,
            'title' => get_the_title($post_id),
            'content' => apply_filters('the_content', $post->post_content),
            'excerpt' => has_excerpt($post_id) ? get_the_excerpt($post_id) : wp_trim_words($post->post_content, 20),
            'permalink' => get_permalink($post_id),
            'start_date' => $start_date,
            'end_date' => $end_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'all_day' => ('1' === $all_day),
            'custom_url' => $url,
            'button_text' => !empty($button_text) ? $button_text : __('Více informací', 'jsm-wp-event-calendar'),
            'thumbnail' => has_post_thumbnail($post_id) ? get_the_post_thumbnail_url($post_id, 'large') : ''
        );

        // Načtení šablony
        ob_start();
        include WP_EVENT_CALENDAR_PLUGIN_DIR . 'templates/event-detail.php';
        return ob_get_clean();
    }
}