<?php
/**
 * Class for managing event categories taxonomy
 */
class WP_Event_Taxonomy {

    /**
     * Register taxonomy
     */
    public function register() {
        add_action('init', array($this, 'register_taxonomies'));
    }

    /**
     * Register taxonomy for event categories
     */
    public function register_taxonomies() {
        $labels = array(
            'name'                       => _x('Event Categories', 'taxonomy general name', 'jsm-wp-event-calendar'),
            'singular_name'              => _x('Event Category', 'taxonomy singular name', 'jsm-wp-event-calendar'),
            'search_items'               => __('Search Categories', 'jsm-wp-event-calendar'),
            'popular_items'              => __('Popular Categories', 'jsm-wp-event-calendar'),
            'all_items'                  => __('All Categories', 'jsm-wp-event-calendar'),
            'parent_item'                => __('Parent Category', 'jsm-wp-event-calendar'),
            'parent_item_colon'          => __('Parent Category:', 'jsm-wp-event-calendar'),
            'edit_item'                  => __('Edit Category', 'jsm-wp-event-calendar'),
            'update_item'                => __('Update Category', 'jsm-wp-event-calendar'),
            'add_new_item'               => __('Add New Category', 'jsm-wp-event-calendar'),
            'new_item_name'              => __('New Category Name', 'jsm-wp-event-calendar'),
            'separate_items_with_commas' => __('Separate categories with commas', 'jsm-wp-event-calendar'),
            'add_or_remove_items'        => __('Add or remove categories', 'jsm-wp-event-calendar'),
            'choose_from_most_used'      => __('Choose from the most used categories', 'jsm-wp-event-calendar'),
            'not_found'                  => __('No categories found', 'jsm-wp-event-calendar'),
            'menu_name'                  => __('Categories', 'jsm-wp-event-calendar'),
        );

        $args = array(
            'hierarchical'      => true, // Hierarchical like categories (not tags)
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'event-category'),
            'show_in_rest'      => true, // Support for Gutenberg editor
        );

        register_taxonomy('event_category', 'jsm_wp_event', $args);
    }
}