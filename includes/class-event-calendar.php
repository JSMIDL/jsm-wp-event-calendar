<?php
/**
 * Class for managing event calendar
 */
class WP_Event_Calendar
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Load CSS and JS for frontend
        add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);

        // Register AJAX endpoints for calendar
        $this->register_rest_routes();
    }

    /**
     * Load scripts and styles for frontend
     */
    public function enqueue_scripts()
    {
        // Ensure jQuery is loaded
        wp_enqueue_script("jquery");

        // Load CSS for calendar with version for cache busting
        wp_enqueue_style(
            "jsm-wp-event-calendar",
            WP_EVENT_CALENDAR_PLUGIN_URL . "assets/css/event-calendar.css",
            [],
            WP_EVENT_CALENDAR_VERSION . "." . time()
        );

        // Responsive styles
        wp_enqueue_style(
            "jsm-wp-event-calendar-mobile",
            WP_EVENT_CALENDAR_PLUGIN_URL .
                "assets/css/event-calendar-mobile.css",
            ["jsm-wp-event-calendar"],
            WP_EVENT_CALENDAR_VERSION . "." . time(),
            "only screen and (max-width: 768px)"
        );

        // Dynamic CSS from calendar settings
        $this->enqueue_dynamic_styles();

        // Load JS for calendar
        wp_enqueue_script(
            "jsm-wp-event-calendar",
            WP_EVENT_CALENDAR_PLUGIN_URL . "assets/js/event-calendar.js",
            ["jquery"],
            WP_EVENT_CALENDAR_VERSION . "." . time(),
            true
        );

        // Localize variables for JavaScript
        wp_localize_script("jsm-wp-event-calendar", "jsmEventCalendar", [
            "ajaxurl" => admin_url("admin-ajax.php"),
            "action" => "get_events_for_calendar",
            "nonce" => wp_create_nonce("jsm_event_calendar_nonce"),
            "i18n" => [
                "loadingText" => __(
                    "Loading events...",
                    "jsm-wp-event-calendar"
                ),
                "noEventsText" => __(
                    "No events to display",
                    "jsm-wp-event-calendar"
                ),
                "eventsListTitle" => __(
                    "Events List",
                    "jsm-wp-event-calendar"
                ),
                "months" => [
                    __("January", "jsm-wp-event-calendar"),
                    __("February", "jsm-wp-event-calendar"),
                    __("March", "jsm-wp-event-calendar"),
                    __("April", "jsm-wp-event-calendar"),
                    __("May", "jsm-wp-event-calendar"),
                    __("June", "jsm-wp-event-calendar"),
                    __("July", "jsm-wp-event-calendar"),
                    __("August", "jsm-wp-event-calendar"),
                    __("September", "jsm-wp-event-calendar"),
                    __("October", "jsm-wp-event-calendar"),
                    __("November", "jsm-wp-event-calendar"),
                    __("December", "jsm-wp-event-calendar"),
                ],
                "weekdays" => [
                    __("Monday", "jsm-wp-event-calendar"),
                    __("Tuesday", "jsm-wp-event-calendar"),
                    __("Wednesday", "jsm-wp-event-calendar"),
                    __("Thursday", "jsm-wp-event-calendar"),
                    __("Friday", "jsm-wp-event-calendar"),
                    __("Saturday", "jsm-wp-event-calendar"),
                    __("Sunday", "jsm-wp-event-calendar"),
                ],
                "weekdaysShort" => [
                    __("Mo", "jsm-wp-event-calendar"),
                    __("Tu", "jsm-wp-event-calendar"),
                    __("We", "jsm-wp-event-calendar"),
                    __("Th", "jsm-wp-event-calendar"),
                    __("Fr", "jsm-wp-event-calendar"),
                    __("Sa", "jsm-wp-event-calendar"),
                    __("Su", "jsm-wp-event-calendar"),
                ],
            ],
        ]);
    }

    /**
     * Dynamically load styles from admin settings
     */
    private function enqueue_dynamic_styles()
    {
        // Load saved settings
        $options = get_option("wp_event_calendar_settings", []);

        // Use defaults if not set
        $defaults = [
            "primary_color" => "#2563eb",
            "primary_hover" => "#1d4ed8",
            "secondary_color" => "#4f46e5",
            "secondary_hover" => "#4338ca",
            "button_text" => "#ffffff",
            "background_color" => "#ffffff",
            "surface_color" => "#f8fafc",
            "surface_hover" => "#f1f5f9",
            "border_color" => "#e2e8f0",
            "text_primary" => "#1e293b",
            "text_secondary" => "#64748b",
            "shadow_sm" => "0 1px 2px rgba(0, 0, 0, 0.05)",
            "shadow_md" =>
                "0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.05)",
            "shadow_lg" =>
                "0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04)",
            "border_radius_sm" => "0.375rem",
            "border_radius_md" => "0.75rem",
            "border_radius_lg" => "1rem",
            "button_radius" => "0.5rem",
            "calendar_spacing" => "0.5rem",
        ];

        // Merge settings with defaults
        $options = wp_parse_args($options, $defaults);

        // Generate dynamic CSS
        $dynamic_css = $this->generate_dynamic_css($options);

        // Add inline styles
        wp_add_inline_style("jsm-wp-event-calendar", $dynamic_css);
    }

    /**
     * Generate dynamic CSS
     *
     * @param array $options Color and style settings
     * @return string Generated CSS
     */
    private function generate_dynamic_css($options)
    {
        // Helper function to convert HEX to RGBA
        $hex_to_rgba = function ($hex, $alpha = 1) {
            // Remove # if exists
            $hex = str_replace("#", "", $hex);

            // Process 3 or 6 character hex color
            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
                $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
                $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
            } else {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
            }

            return "rgba($r, $g, $b, $alpha)";
        };

        // Prepare variables with transparency
        $primary_color_rgba = $hex_to_rgba($options["primary_color"], 0.1);
        $primary_past_rgba = $hex_to_rgba($options["primary_color"], 0.7);
        $primary_today_rgba = $hex_to_rgba($options["primary_color"], 0.05);

        // Generate CSS
        return "
        :root {
            --primary-color: {$options["primary_color"]} !important;
            --primary-hover: {$options["primary_hover"]} !important;
            --secondary-color: {$options["secondary_color"]} !important;
            --secondary-hover: {$options["secondary_hover"]} !important;
            --button-text: {$options["button_text"]} !important;
            --background-color: {$options["background_color"]} !important;
            --surface-color: {$options["surface_color"]} !important;
            --surface-hover: {$options["surface_hover"]} !important;
            --border-color: {$options["border_color"]} !important;
            --text-primary: {$options["text_primary"]} !important;
            --text-secondary: {$options["text_secondary"]} !important;
            --shadow-sm: {$options["shadow_sm"]} !important;
            --shadow-md: {$options["shadow_md"]} !important;
            --shadow-lg: {$options["shadow_lg"]} !important;
            --border-radius-sm: {$options["border_radius_sm"]} !important;
            --border-radius-md: {$options["border_radius_md"]} !important;
            --border-radius-lg: {$options["border_radius_lg"]} !important;
            --button-radius: {$options["button_radius"]} !important;
            --calendar-spacing: {$options["calendar_spacing"]} !important;
        }

        /* Specific selectors for dynamic styling */
        .jsm-event-list-item-date {
            background-color: {$primary_color_rgba} !important;
            color: {$options["primary_color"]} !important;
        }

        .jsm-event-calendar-day.past-day .jsm-event-calendar-event {
            background-color: {$primary_past_rgba} !important;
        }

        .jsm-event-calendar-day.today {
            background-color: {$primary_today_rgba} !important;
            border-color: {$options["primary_color"]} !important;
        }

        .jsm-event-button,
        .jsm-event-calendar-event,
        .jsm-event-calendar-day.today .jsm-event-calendar-day-number {
            background-color: {$options["primary_color"]} !important;
            color: {$options["button_text"]} !important;
        }

        .jsm-event-button:hover,
        .jsm-event-calendar-event:hover {
            background-color: {$options["primary_hover"]} !important;
        }

        .jsm-event-calendar-nav {
            background: linear-gradient(135deg, {$options["primary_color"]}, {$options["secondary_color"]}) !important;
        }

        .jsm-event-list-title:after {
            background-color: {$options["primary_color"]} !important;
        }
        ";
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes()
    {
        // AJAX for event calendar
        add_action("wp_ajax_get_events_for_calendar", [
            $this,
            "get_events_for_calendar",
        ]);
        add_action("wp_ajax_nopriv_get_events_for_calendar", [
            $this,
            "get_events_for_calendar",
        ]);

        // AJAX for event detail - new endpoint
        add_action("wp_ajax_get_event_detail", [
            $this,
            "get_event_detail_ajax",
        ]);
        add_action("wp_ajax_nopriv_get_event_detail", [
            $this,
            "get_event_detail_ajax",
        ]);
    }

    /**
     * AJAX handler for getting event details
     */
    public function get_event_detail_ajax()
    {
        // Check nonce
        if (
            !isset($_GET["nonce"]) ||
            !wp_verify_nonce($_GET["nonce"], "jsm_event_calendar_nonce")
        ) {
            wp_send_json_error("Invalid security token");
        }

        // Check valid ID
        $event_id = isset($_GET["event_id"]) ? intval($_GET["event_id"]) : 0;
        if (!$event_id) {
            wp_send_json_error("Invalid event ID");
        }

        // Get event details
        $event_html = $this->render_event_detail($event_id);

        if (empty($event_html)) {
            wp_send_json_error("Event not found");
        }

        wp_send_json_success($event_html);
    }

    /**
     * Get events for calendar
     */
    public function get_events_for_calendar()
    {
        if (
            !isset($_GET["nonce"]) ||
            !wp_verify_nonce($_GET["nonce"], "jsm_event_calendar_nonce")
        ) {
            wp_send_json_error("Invalid security token");
        }

        $month = isset($_GET["month"]) ? intval($_GET["month"]) : date("m");
        $year = isset($_GET["year"]) ? intval($_GET["year"]) : date("Y");
        $category = isset($_GET["category"]) ? sanitize_text_field($_GET["category"]) : '';

        $start_date = $year . "-" . $month . "-01";
        $end_date = date("Y-m-t", strtotime($start_date));
        $today = date("Y-m-d");

        $args = [
            "post_type" => "jsm_wp_event",
            "posts_per_page" => -1,
            "post_status" => "publish",
            "meta_query" => [
                "relation" => "AND",
                [
                    "relation" => "OR",
                    [
                        "key" => "_event_start_date",
                        "value" => $today,
                        "compare" => ">=",
                        "type" => "DATE",
                    ],
                    [
                        "key" => "_event_end_date",
                        "value" => $today,
                        "compare" => ">=",
                        "type" => "DATE",
                    ],
                ],
                [
                    "relation" => "OR",
                    [
                        "key" => "_event_start_date",
                        "value" => [$start_date, $end_date],
                        "compare" => "BETWEEN",
                        "type" => "DATE",
                    ],
                    [
                        "key" => "_event_end_date",
                        "value" => [$start_date, $end_date],
                        "compare" => "BETWEEN",
                        "type" => "DATE",
                    ],
                    [
                        "relation" => "AND",
                        [
                            "key" => "_event_start_date",
                            "value" => $start_date,
                            "compare" => "<",
                            "type" => "DATE",
                        ],
                        [
                            "key" => "_event_end_date",
                            "value" => $end_date,
                            "compare" => ">",
                            "type" => "DATE",
                        ],
                    ],
                ],
            ],
            "orderby" => "meta_value",
            "meta_key" => "_event_start_date",
            "order" => "ASC",
        ];

        // Add category filter if specified
        if (!empty($category)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'event_category',
                    'field'    => 'slug',
                    'terms'    => explode(',', $category),
                ]
            ];
        }

        $query = new WP_Query($args);
        $events = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $start_date = get_post_meta($post_id, "_event_start_date", true);
                $end_date = get_post_meta($post_id, "_event_end_date", true);
                $start_time = get_post_meta($post_id, "_event_start_time", true);
                $end_time = get_post_meta($post_id, "_event_end_time", true);
                $all_day = get_post_meta($post_id, "_event_all_day", true);
                $url = get_post_meta($post_id, "_event_url", true);
                $button_text = get_post_meta($post_id, "_event_button_text", true);

                if (empty($end_date)) {
                    $end_date = $start_date;
                }

                $time_display = "";
                if ("1" !== $all_day) {
                    if (!empty($start_time)) {
                        $time_display = date_i18n(get_option("time_format"), strtotime($start_time));
                        if (!empty($end_time)) {
                            $time_display .= " - " . date_i18n(get_option("time_format"), strtotime($end_time));
                        }
                    }
                } else {
                    $time_display = __("All Day", "jsm-wp-event-calendar");
                }

                $date_display = date_i18n(get_option("date_format"), strtotime($start_date));
                if ($end_date !== $start_date) {
                    $date_display .= " - " . date_i18n(get_option("date_format"), strtotime($end_date));
                }

                // Get event categories
                $event_categories = wp_get_post_terms($post_id, 'event_category', array('fields' => 'all'));
                $categories = array();

                foreach ($event_categories as $cat) {
                    $categories[] = array(
                        'id' => $cat->term_id,
                        'name' => $cat->name,
                        'slug' => $cat->slug
                    );
                }

                $events[] = [
                    "id" => $post_id,
                    "title" => get_the_title(),
                    "startDate" => $start_date,
                    "endDate" => $end_date,
                    "dateDisplay" => $date_display,
                    "timeDisplay" => $time_display,
                    "allDay" => "1" === $all_day,
                    "url" => get_permalink($post_id),
                    "excerpt" => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20),
                    "customUrl" => $url,
                    "buttonText" => !empty($button_text) ? $button_text : __("More Information", "jsm-wp-event-calendar"),
                    "categories" => $categories
                ];
            }
            wp_reset_postdata();
        }

        // 🔌 Addon events
        $external_events = apply_filters('jsm_event_calendar_external_events', []);
        if (is_array($external_events)) {
            foreach ($external_events as $external_event) {
                if (!isset($external_event['title']) || !isset($external_event['startDate'])) {
                    continue;
                }
                $events[] = $external_event;
            }
        }

        wp_send_json_success($events);
    }

    /**
     * Get events for display in list
     *
     * @param array $args Arguments for WP_Query
     * @return array Array of events
     */
    public function get_events($args = [])
    {
        $default_args = [
            "post_type" => "jsm_wp_event",
            "posts_per_page" => 10,
            "post_status" => "publish",
            "meta_key" => "_event_start_date",
            "orderby" => "meta_value",
            "order" => "ASC",
            "meta_query" => [
                [
                    "key" => "_event_start_date",
                    "value" => date("Y-m-d"),
                    "compare" => ">=",
                    "type" => "DATE",
                ],
            ],
        ];

        $args = wp_parse_args($args, $default_args);

        // Add category filter if specified
        if (!empty($args['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'event_category',
                    'field'    => is_numeric($args['category']) ? 'term_id' : 'slug',
                    'terms'    => explode(',', $args['category']),
                ]
            ];
        }

        $query = new WP_Query($args);
        $events = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $start_date = get_post_meta($post_id, "_event_start_date", true);
                $end_date = get_post_meta($post_id, "_event_end_date", true);
                $start_time = get_post_meta($post_id, "_event_start_time", true);
                $end_time = get_post_meta($post_id, "_event_end_time", true);
                $all_day = get_post_meta($post_id, "_event_all_day", true);
                $url = get_post_meta($post_id, "_event_url", true);
                $button_text = get_post_meta($post_id, "_event_button_text", true);

                // Get event categories
                $event_categories = wp_get_post_terms($post_id, 'event_category', array('fields' => 'all'));
                $categories = array();

                foreach ($event_categories as $cat) {
                    $categories[] = array(
                        'id' => $cat->term_id,
                        'name' => $cat->name,
                        'slug' => $cat->slug
                    );
                }

                $events[] = [
                    "id" => $post_id,
                    "title" => get_the_title(),
                    "content" => get_the_content(),
                    "excerpt" => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20),
                    "permalink" => get_permalink($post_id),
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "start_time" => $start_time,
                    "end_time" => $end_time,
                    "all_day" => "1" === $all_day,
                    "custom_url" => $url,
                    "button_text" => !empty($button_text) ? $button_text : __("More Information", "jsm-wp-event-calendar"),
                    "thumbnail" => has_post_thumbnail() ? get_the_post_thumbnail_url($post_id, "medium") : "",
                    "categories" => $categories
                ];
            }
            wp_reset_postdata();
        }

        // 🔌 Addon events
        $external_events = apply_filters('jsm_event_calendar_external_events', []);
        if (is_array($external_events)) {
            foreach ($external_events as $external_event) {
                if (!isset($external_event['title']) || !isset($external_event['startDate'])) {
                    continue;
                }
                $events[] = $external_event;
            }
        }

        return $events;
    }

    /**
     * Render calendar
     *
     * @param array $atts Shortcode attributes
     * @return string Calendar HTML
     */
    public function render_calendar($atts = [])
    {
        $atts = shortcode_atts(
            [
                "month" => date("m"),
                "year" => date("Y"),
                "show_list" => "yes",
                "category" => "",
            ],
            $atts,
            "event_calendar"
        );

        // Load template
        ob_start();
        include WP_EVENT_CALENDAR_PLUGIN_DIR . "templates/calendar.php";
        return ob_get_clean();
    }

    /**
     * Render event list
     *
     * @param array $atts Shortcode attributes
     * @return string Event list HTML
     */
    public function render_event_list($atts = [])
    {
        $atts = shortcode_atts(
            [
                "limit" => 10,
                "category" => "",
                "past" => "no",
                "layout" => "list",
            ],
            $atts,
            "event_list"
        );

        $args = [
            "posts_per_page" => intval($atts["limit"]),
        ];

        // Add category
        if (!empty($atts["category"])) {
            $args["category"] = $atts["category"];
        }

        // Past or future events
        if ("yes" === $atts["past"]) {
            $args["meta_query"] = [
                [
                    "key" => "_event_start_date",
                    "value" => date("Y-m-d"),
                    "compare" => "<",
                    "type" => "DATE",
                ],
            ];
            $args["order"] = "DESC";
        } else {
            $args["meta_query"] = [
                [
                    "key" => "_event_start_date",
                    "value" => date("Y-m-d"),
                    "compare" => ">=",
                    "type" => "DATE",
                ],
            ];
        }

        $events = $this->get_events($args);

        // Load template
        ob_start();
        include WP_EVENT_CALENDAR_PLUGIN_DIR . "templates/event-list.php";
        return ob_get_clean();
    }

    /**
     * Render event detail
     *
     * @param int $post_id Event ID
     * @return string Event detail HTML
     */
    public function render_event_detail($post_id)
    {
        $post = get_post($post_id);

        if (!$post || "jsm_wp_event" !== $post->post_type) {
            return "";
        }

        $start_date = get_post_meta($post_id, "_event_start_date", true);
        $end_date = get_post_meta($post_id, "_event_end_date", true);
        $start_time = get_post_meta($post_id, "_event_start_time", true);
        $end_time = get_post_meta($post_id, "_event_end_time", true);
        $all_day = get_post_meta($post_id, "_event_all_day", true);
        $url = get_post_meta($post_id, "_event_url", true);
        $button_text = get_post_meta($post_id, "_event_button_text", true);

        // Get event categories
        $event_categories = wp_get_post_terms($post_id, 'event_category', array('fields' => 'all'));
        $categories = array();

        foreach ($event_categories as $cat) {
            $categories[] = array(
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'url' => get_term_link($cat)
            );
        }

        // Build event object
        $event = [
            "id" => $post_id,
            "title" => get_the_title($post_id),
            "content" => apply_filters("the_content", $post->post_content),
            "excerpt" => has_excerpt($post_id)
                ? get_the_excerpt($post_id)
                : wp_trim_words($post->post_content, 20),
            "permalink" => get_permalink($post_id),
            "start_date" => $start_date,
            "end_date" => $end_date,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "all_day" => "1" === $all_day,
            "custom_url" => $url,
            "button_text" => !empty($button_text)
                ? $button_text
                : __("More Information", "jsm-wp-event-calendar"),
            "thumbnail" => has_post_thumbnail($post_id)
                ? get_the_post_thumbnail_url($post_id, "large")
                : "",
            "categories" => $categories
        ];

        // Load template
        ob_start();
        include WP_EVENT_CALENDAR_PLUGIN_DIR . "templates/event-detail.php";
        return ob_get_clean();
    }
}