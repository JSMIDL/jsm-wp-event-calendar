<?php
/**
 * Template for plugin documentation page in admin
 */

// Check permissions
if (!current_user_can('manage_options')) {
    return;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?post_type=jsm_wp_event&page=wp_event_docs" class="nav-tab nav-tab-active"><?php _e('Documentation', 'jsm-wp-event-calendar'); ?></a>
        <a href="?post_type=jsm_wp_event&page=wp_event_settings" class="nav-tab"><?php _e('Plugin Settings', 'jsm-wp-event-calendar'); ?></a>
    </h2>

    <!-- Documentation tab -->
    <div class="jsm-admin-content">
        <div class="jsm-admin-main">
            <div class="jsm-admin-card">
                <h2><?php _e('How to Use the Plugin', 'jsm-wp-event-calendar'); ?></h2>

                <div class="jsm-admin-section">
                    <h3><?php _e('Adding a New Event', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('To add a new event, go to the <strong>Events</strong> section in the admin menu and click <strong>Add New</strong>.', 'jsm-wp-event-calendar'); ?></p>
                    <p><?php _e('Fill in the title, description, and set the date and time of the event. You can also set a custom URL for the button to link to.', 'jsm-wp-event-calendar'); ?></p>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Managing Event Categories', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('To create and manage event categories, go to the <strong>Events</strong> > <strong>Categories</strong> section in the admin menu.', 'jsm-wp-event-calendar'); ?></p>
                    <p><?php _e('Categories help you better organize events and allow visitors to filter events by their interests.', 'jsm-wp-event-calendar'); ?></p>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Adding Calendar to a Page', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('To display the calendar on a page, use this shortcode:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[jsm_event_calendar]</code></pre>

                    <p><?php _e('You can use the following attributes to customize the calendar:', 'jsm-wp-event-calendar'); ?></p>
                    <ul>
                        <li><code>month</code> - <?php _e('Month number (1-12)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>year</code> - <?php _e('Year (e.g. 2023)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>show_list</code> - <?php _e('Show event list below the calendar (yes/no)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>category</code> - <?php _e('Category ID or slug for filtering events', 'jsm-wp-event-calendar'); ?></li>
                    </ul>

                    <p><?php _e('Example with parameters:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[jsm_event_calendar month="1" year="2023" show_list="yes" category="events"]</code></pre>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Adding Event List to a Page', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('To display an event list on a page, use this shortcode:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[jsm_event_list]</code></pre>

                    <p><?php _e('You can use the following attributes to customize the list:', 'jsm-wp-event-calendar'); ?></p>
                    <ul>
                        <li><code>limit</code> - <?php _e('Number of events to display', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>category</code> - <?php _e('Category ID or slug for filtering events', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>past</code> - <?php _e('Show past events (yes/no)', 'jsm-wp-event-calendar'); ?></li>
                        <li><code>layout</code> - <?php _e('Display type (list/grid)', 'jsm-wp-event-calendar'); ?></li>
                    </ul>

                    <p><?php _e('Example with parameters:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[jsm_event_list limit="5" past="no" layout="grid" category="events"]</code></pre>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Adding Event Detail to a Page', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('To display the detail of a specific event on a page, use this shortcode:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>[jsm_event_detail id="123"]</code></pre>

                    <p><?php _e('Where <code>id</code> is the ID of the event you want to display.', 'jsm-wp-event-calendar'); ?></p>
                </div>

                <div class="jsm-admin-section">
                    <h3><?php _e('Creating External Events Add-on', 'jsm-wp-event-calendar'); ?></h3>
                    <p><?php _e('The plugin supports external event sources through add-ons. You can create your own add-on using the <code>jsm_event_calendar_external_events</code> filter:', 'jsm-wp-event-calendar'); ?></p>
                    <pre><code>add_filter('jsm_event_calendar_external_events', 'my_custom_events_addon');

function my_custom_events_addon($events) {
    // Your custom events in the same format as plugin events
    $custom_events = [
        [
            "id" => "custom-1",
            "title" => "Custom Event",
            "startDate" => "2023-12-25",
            "endDate" => "2023-12-25",
            "dateDisplay" => "December 25, 2023",
            "timeDisplay" => "All Day",
            "allDay" => true,
            "url" => "https://example.com/event",
            "excerpt" => "This is a custom event from an add-on",
            "customUrl" => "https://example.com/custom",
            "buttonText" => "Register",
            "categories" => [
                ['id' => 1, 'name' => 'Custom Category', 'slug' => 'custom-category']
            ]
        ],
    ];

    return array_merge($events, $custom_events);
}</code></pre>
                    <p><?php _e('This allows integration with external event sources, APIs, or other plugins.', 'jsm-wp-event-calendar'); ?></p>
                </div>
            </div>
        </div>

        <div class="jsm-admin-sidebar">
            <div class="jsm-admin-card">
                <h3><?php _e('About the Plugin', 'jsm-wp-event-calendar'); ?></h3>
                <p><?php _e('JSM WP Event Calendar is a simple plugin for managing and displaying event calendars on your website with category support and filtering.', 'jsm-wp-event-calendar'); ?></p>
                <p><?php _e('Version:', 'jsm-wp-event-calendar'); ?> <?php echo defined('WP_EVENT_CALENDAR_VERSION') ? WP_EVENT_CALENDAR_VERSION : '1.0'; ?></p>
            </div>

            <div class="jsm-admin-card">
                <h3><?php _e('Default Features', 'jsm-wp-event-calendar'); ?></h3>
                <p><?php _e('The entire plugin is designed to work immediately after activation.', 'jsm-wp-event-calendar'); ?></p>
                <ul>
                    <li><?php _e('Responsive design', 'jsm-wp-event-calendar'); ?></li>
                    <li><?php _e('English and Czech language support', 'jsm-wp-event-calendar'); ?></li>
                    <li><?php _e('Event categories with filtering', 'jsm-wp-event-calendar'); ?></li>
                    <li><?php _e('Easy to use shortcodes', 'jsm-wp-event-calendar'); ?></li>
                    <li><?php _e('Add-on support', 'jsm-wp-event-calendar'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    /* General styles */
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

    /* Responsive styles */
    @media screen and (max-width: 782px) {
        .jsm-admin-content {
            flex-direction: column;
        }
    }
</style>