<?php
/**
 * Třída pro správu shortcodů kalendáře
 */
class WP_Event_Shortcodes {

    /**
     * Registrace shortcodů
     */
    public function register() {
        add_shortcode('event_calendar', array($this, 'calendar_shortcode'));
        add_shortcode('event_list', array($this, 'event_list_shortcode'));
        add_shortcode('event_detail', array($this, 'event_detail_shortcode'));
    }

    /**
     * Shortcode pro zobrazení kalendáře
     *
     * @param array $atts Atributy shortcodu
     * @return string HTML kalendáře
     */
    public function calendar_shortcode($atts = array()) {
        $calendar = new WP_Event_Calendar();
        return $calendar->render_calendar($atts);
    }

    /**
     * Shortcode pro zobrazení seznamu událostí
     *
     * @param array $atts Atributy shortcodu
     * @return string HTML seznamu událostí
     */
    public function event_list_shortcode($atts = array()) {
        $calendar = new WP_Event_Calendar();
        return $calendar->render_event_list($atts);
    }

    /**
     * Shortcode pro zobrazení detailu události
     *
     * @param array $atts Atributy shortcodu
     * @return string HTML detailu události
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