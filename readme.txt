=== JSM WP Event Calendar ===
Contributors: jansmidl
Tags: calendar, events, event calendar, scheduling, responsive calendar, event categories
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Responsive event calendar with category filtering, list view and customizable appearance.

== Description ==

JSM WP Event Calendar is a simple and efficient plugin for managing and displaying events in a calendar or list format. With fully customizable appearance through the admin interface and powerful category filtering capabilities, it's now even more flexible.

= Key Features =

* **Responsive Design** - Calendar adjusts to screen size, including mobile devices.
* **Multilingual Support** - Full support for English and Czech languages.
* **Flexible Display** - Show events as a calendar or list.
* **Event Categories** - Create and manage event categories for better organization.
* **Category Filtering** - Filter events by category in both calendar and list views.
* **Custom Events** - Easy addition and management of events with details like date, time, description, and URL.
* **Add-on Support** - Extensible architecture for custom add-ons through hooks and filters.
* **Shortcodes** - Easily insert calendar or event list anywhere on your site.
* **Customizable Appearance** - Complete control over colors, shadows, corner rounding, and other visual aspects.
* **Intuitive Settings Management** - Clear interface with color pickers and live preview.

= Usage =

You can insert the event calendar into a page or post using the shortcode:

`[jsm_event_calendar]`

Or an event list:

`[jsm_event_list]`

To display a single event's details:

`[jsm_event_detail id="123"]`

= Optional Parameters =

**For calendar:**
* `month` - Month number (1-12)
* `year` - Year (e.g., 2023)
* `show_list` - Show event list below the calendar (yes/no)
* `category` - Category ID or slug for filtering events

**For event list:**
* `limit` - Number of events to display
* `category` - Category ID or slug for filtering events
* `past` - Show past events (yes/no)
* `layout` - Display type (list/grid)

**For event detail:**
* `id` - The ID of the event to display

= Customizing Appearance =

The plugin offers complete control over the calendar's appearance directly from the WordPress admin:

* Set all colors with intuitive color pickers
* Adjust shadows for various elements
* Set corner rounding for buttons and other elements
* Live preview of changes
* Reset to default settings with one click

= Add-on Development =

JSM WP Event Calendar supports add-ons through a simple filter. To create an add-on that adds external events:

```php
add_filter('jsm_event_calendar_external_events', 'my_custom_events_addon');

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
            "buttonText" => "Register Now",
            "categories" => [
                ['id' => 1, 'name' => 'Custom Category', 'slug' => 'custom-category']
            ]
        ],
    ];

    return array_merge($events, $custom_events);
}
```

This allows for integration with external event sources, APIs, or other plugins.

== Installation ==

1. Download the plugin files and upload them to the `/wp-content/plugins/jsm-wp-event-calendar` directory
2. Activate the plugin in the 'Plugins' section of WordPress admin
3. To add a new event, go to the 'Events' section in the admin menu
4. To customize the appearance, go to 'Events' > 'Settings'
5. To display the calendar or event list, use the shortcodes `[jsm_event_calendar]` or `[jsm_event_list]`

== Frequently Asked Questions ==

= How do I add a new event? =

Go to the 'Events' section in the admin menu and click 'Add New'.

= How do I create event categories? =

Go to 'Events' > 'Categories' in the admin menu to add, edit, or delete event categories.

= How do I filter events by category? =

Use the category parameter in the shortcode: `[jsm_event_calendar category="your-category-slug"]` or `[jsm_event_list category="your-category-slug"]`.

= How do I change the calendar colors? =

Go to 'Events' > 'Settings' in the admin menu and use the color pickers to set all colors. Changes will take effect immediately after saving.

= Can I reset the appearance settings to default? =

Yes, on the 'Events' > 'Settings' page you will find a 'Reset to Default Settings' button that will restore all values to their original defaults.

= Can I display only upcoming events? =

Yes, for the event list use the shortcode `[jsm_event_list past="no"]`. For the calendar, by default all events in the given month are displayed.

= Is the plugin compatible with Gutenberg? =

Yes, you can insert the shortcodes into a Custom HTML block in the Gutenberg editor.

= Is the plugin responsive for mobile devices? =

Yes, the plugin uses a fully responsive design that displays optimally on devices of all sizes. On mobile devices, the calendar automatically switches to a more readable view.

= Is this plugin multilingual? =

Yes, the plugin supports Czech and English languages. The language will match your WordPress installation language.

== Screenshots ==

1. Event Calendar
2. Event List
3. Event Detail
4. Mobile View
5. Events Admin
6. Calendar Appearance Settings
7. Event Categories Management

== Changelog ==

= 1.0.0 =
* First version of the plugin
* Added support for responsive design
* Implemented basic shortcodes [jsm_event_calendar], [jsm_event_list], and [jsm_event_detail]
* Added ability to customize appearance directly in admin
* Added event categories with filtering capabilities
* Added add-on support through filters
* Added multilingual support (English, Czech)

== Upgrade Notice ==

= 1.0.0 =
First version of the plugin with complete features for managing and displaying an event calendar with category support

== About the Author ==

JSM WP Event Calendar was created by [Jan Smidl](https://jansmidl.cz).