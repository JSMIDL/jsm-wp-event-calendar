<?php
/**
 * Plugin uninstallation
 *
 * This file runs when the plugin is uninstalled (not just deactivated).
 * It removes all data associated with the plugin to leave no trace.
 */

// If this file is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all events
$events = get_posts(array(
    'post_type' => 'jsm_wp_event',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($events as $event) {
    wp_delete_post($event->ID, true);
}

// Remove all meta data associated with events
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_event_%'");

// Remove plugin settings
delete_option('wp_event_calendar_settings');

// Flush rewrite rules
flush_rewrite_rules();