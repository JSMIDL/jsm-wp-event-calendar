<?php
/**
 * Třída pro správu taxonomie kategorií událostí
 */
class WP_Event_Taxonomy {

    /**
     * Registrace taxonomie
     */
    public function register() {
        add_action('init', array($this, 'register_taxonomies'));
    }

    /**
     * Registrace taxonomie pro kategorie událostí
     */
    public function register_taxonomies() {
        $labels = array(
            'name'                       => _x('Kategorie událostí', 'taxonomy general name', 'jsm-wp-event-calendar'),
            'singular_name'              => _x('Kategorie události', 'taxonomy singular name', 'jsm-wp-event-calendar'),
            'search_items'               => __('Hledat kategorie', 'jsm-wp-event-calendar'),
            'popular_items'              => __('Populární kategorie', 'jsm-wp-event-calendar'),
            'all_items'                  => __('Všechny kategorie', 'jsm-wp-event-calendar'),
            'parent_item'                => __('Nadřazená kategorie', 'jsm-wp-event-calendar'),
            'parent_item_colon'          => __('Nadřazená kategorie:', 'jsm-wp-event-calendar'),
            'edit_item'                  => __('Upravit kategorii', 'jsm-wp-event-calendar'),
            'update_item'                => __('Aktualizovat kategorii', 'jsm-wp-event-calendar'),
            'add_new_item'               => __('Přidat novou kategorii', 'jsm-wp-event-calendar'),
            'new_item_name'              => __('Nová kategorie', 'jsm-wp-event-calendar'),
            'separate_items_with_commas' => __('Oddělte kategorie čárkami', 'jsm-wp-event-calendar'),
            'add_or_remove_items'        => __('Přidat nebo odebrat kategorie', 'jsm-wp-event-calendar'),
            'choose_from_most_used'      => __('Vybrat z nejpoužívanějších kategorií', 'jsm-wp-event-calendar'),
            'not_found'                  => __('Žádné kategorie nenalezeny', 'jsm-wp-event-calendar'),
            'menu_name'                  => __('Kategorie', 'jsm-wp-event-calendar'),
        );

        $args = array(
            'hierarchical'      => true, // Hierarchická jako kategorie (ne štítky)
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'event-category'),
            'show_in_rest'      => true, // Podpora pro Gutenberg editor
        );

        register_taxonomy('event_category', 'jsm_wp_event', $args);
    }
}