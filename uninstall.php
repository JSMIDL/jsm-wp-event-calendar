<?php
/**
 * Odinstalace pluginu
 *
 * Tento soubor se spustí, když je plugin odinstalován (ne pouze deaktivován).
 * Odstraní všechna data spojená s pluginem, aby po sobě nezanechal nepořádek.
 */

// Pokud tento soubor není volán z WordPressu, ukončit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Odstranění všech událostí
$events = get_posts(array(
    'post_type' => 'wp_event',
    'numberposts' => -1,
    'post_status' => 'any'
));

foreach ($events as $event) {
    wp_delete_post($event->ID, true);
}

// Odstranění všech meta dat spojených s událostmi
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_event_%'");

// Propláchnutí pravidel přesměrování
flush_rewrite_rules();