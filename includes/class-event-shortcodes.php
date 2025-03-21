<?php
/**
 * Class for managing calendar shortcodes
 */
class WP_Event_Shortcodes {

    /**
     * Register shortcodes
     */
    public function register() {
        add_shortcode('jsm_event_calendar', array($this, 'calendar_shortcode'));
        add_shortcode('jsm_event_list', array($this, 'event_list_shortcode'));
        add_shortcode('jsm_event_detail', array($this, 'event_detail_shortcode'));
    }

    /**
     * Shortcode for displaying calendar
     *
     * @param array $atts Shortcode attributes
     * @return string Calendar HTML
     */
    public function calendar_shortcode($atts = array()) {
        $calendar = new WP_Event_Calendar();
        return $calendar->render_calendar($atts);
    }

    /**
     * Shortcode for displaying event list
     *
     * @param array $atts Shortcode attributes
     * @return string Event list HTML
     */
    public function event_list_shortcode($atts = array()) {
        $calendar = new WP_Event_Calendar();
        return $calendar->render_event_list($atts);
    }

    /**
     * Shortcode for displaying event details
     *
     * @param array $atts Shortcode attributes
     * @return string Event detail HTML
     */
    public function event_detail_shortcode($atts = array()) {
        $atts = shortcode_atts(array(
            'id' => 0
        ), $atts, 'event_detail');

        $post_id = intval($atts['id']);

        if (0 === $post_id && is_singular('wp_event')) {
            $post_id = get_the_ID();
        }

        if ($post_id > 0) {
            $calendar = new WP_Event_Calendar();
            return $calendar->render_event_detail($post_id);
        }

        return '';
    }
}