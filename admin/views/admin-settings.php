<?php
/**
 * Template for plugin settings page in admin
 */

// Check permissions
if (!current_user_can('manage_options')) {
    return;
}


// Get current values
$options = get_option('wp_event_calendar_settings', array());
$options = wp_parse_args($options, WP_Event_Settings::get_defaults());

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?post_type=jsm_wp_event&page=wp_event_docs" class="nav-tab"><?php _e('Documentation', 'jsm-wp-event-calendar'); ?></a>
        <a href="?post_type=jsm_wp_event&page=wp_event_settings" class="nav-tab nav-tab-active"><?php _e('Plugin Settings', 'jsm-wp-event-calendar'); ?></a>
    </h2>

    <!-- Settings tab -->
    <div class="jsm-settings-content">
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_event_settings');
            ?>

            <div class="jsm-settings-form">
            <!-- Reset button at top -->
                <div class="jsm-reset-settings-top-right">
                    <button type="button" id="reset-settings" class="button button-secondary">
                        <?php _e('Reset to Default Settings', 'jsm-wp-event-calendar'); ?>
                    </button>
                </div>
                <div class="jsm-settings-columns">
                    <div class="jsm-settings-column">
                        <h3><?php _e('Color Scheme', 'jsm-wp-event-calendar'); ?></h3>
                        <table class="form-table">
                            <tbody>
                                <?php do_settings_fields('wp_event_settings', 'colors_section'); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="jsm-settings-column">
                        <h3><?php _e('Dimensions and Effects', 'jsm-wp-event-calendar'); ?></h3>
                        <table class="form-table">
                            <tbody>
                                <?php do_settings_fields('wp_event_settings', 'dimensions_section'); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="jsm-settings-preview">
                <h3><?php _e('Settings Preview', 'jsm-wp-event-calendar'); ?></h3>
                <div class="jsm-color-previews">
                    <div class="jsm-preview-section">
                        <h4><?php _e('Primary Colors', 'jsm-wp-event-calendar'); ?></h4>
                        <div class="jsm-color-preview" data-color-id="primary_color" style="background-color: <?php echo esc_attr($options['primary_color']); ?>">
                            <?php echo esc_html($options['primary_color']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="primary_hover" style="background-color: <?php echo esc_attr($options['primary_hover']); ?>">
                            <?php echo esc_html($options['primary_hover']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="secondary_color" style="background-color: <?php echo esc_attr($options['secondary_color']); ?>">
                            <?php echo esc_html($options['secondary_color']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="secondary_hover" style="background-color: <?php echo esc_attr($options['secondary_hover']); ?>">
                            <?php echo esc_html($options['secondary_hover']); ?>
                        </div>
                    </div>

                    <div class="jsm-preview-section">
                        <h4><?php _e('Background Colors', 'jsm-wp-event-calendar'); ?></h4>
                        <div class="jsm-color-preview" data-color-id="background_color" style="background-color: <?php echo esc_attr($options['background_color']); ?>; color: #333;">
                            <?php echo esc_html($options['background_color']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="surface_color" style="background-color: <?php echo esc_attr($options['surface_color']); ?>; color: #333;">
                            <?php echo esc_html($options['surface_color']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="surface_hover" style="background-color: <?php echo esc_attr($options['surface_hover']); ?>; color: #333;">
                            <?php echo esc_html($options['surface_hover']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="border_color" style="background-color: <?php echo esc_attr($options['border_color']); ?>; color: #333;">
                            <?php echo esc_html($options['border_color']); ?>
                        </div>
                    </div>

                    <div class="jsm-preview-section">
                        <h4><?php _e('Text Colors', 'jsm-wp-event-calendar'); ?></h4>
                        <div class="jsm-color-preview" data-color-id="text_primary" style="background-color: <?php echo esc_attr($options['text_primary']); ?>">
                            <?php echo esc_html($options['text_primary']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="text_secondary" style="background-color: <?php echo esc_attr($options['text_secondary']); ?>">
                            <?php echo esc_html($options['text_secondary']); ?>
                        </div>
                        <div class="jsm-color-preview" data-color-id="button_text" style="background-color: <?php echo esc_attr($options['button_text']); ?>">
                            <?php echo esc_html($options['button_text']); ?>
                        </div>
                    </div>
                </div>

                <div class="jsm-calendar-preview">
                    <h4><?php _e('Calendar Example', 'jsm-wp-event-calendar'); ?></h4>
                    <div class="jsm-preview-calendar-wrapper" style="
                        background-color: <?php echo esc_attr($options['background_color']); ?>;
                        color: <?php echo esc_attr($options['text_primary']); ?>;
                        border-radius: <?php echo esc_attr($options['border_radius_lg']); ?>;
                        box-shadow: <?php echo esc_attr($options['shadow_md']); ?>;
                        border: 1px solid <?php echo esc_attr($options['border_color']); ?>;
                        overflow: hidden;
                        max-width: 500px;
                        margin: 0 auto;
                    ">
                        <!-- Navigation example -->
                        <div class="jsm-preview-calendar-header" style="
                            background: linear-gradient(135deg, <?php echo esc_attr($options['primary_color']); ?>, <?php echo esc_attr($options['secondary_color']); ?>);
                            color: white;
                            padding: 1rem;
                            text-align: center;
                            font-weight: bold;
                        ">
                            <?php _e('March 2025', 'jsm-wp-event-calendar'); ?>
                        </div>

                        <!-- Days and events example -->
                        <div class="jsm-preview-calendar-days" style="
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            grid-gap: <?php echo esc_attr($options['calendar_spacing']); ?>;
                            padding: 1rem;
                        ">
                            <div class="jsm-preview-calendar-day" style="
                                background-color: <?php echo esc_attr($options['surface_color']); ?>;
                                border: 1px solid <?php echo esc_attr($options['border_color']); ?>;
                                border-radius: <?php echo esc_attr($options['border_radius_sm']); ?>;
                                padding: 0.75rem;
                                min-height: 80px;
                            ">
                                <div style="font-weight: bold; color: <?php echo esc_attr($options['text_primary']); ?>;">15</div>
                            </div>

                            <!-- Today -->
                            <div class="jsm-preview-calendar-day" style="
                                background-color: #EFF6FF;
                                border: 1px solid <?php echo esc_attr($options['primary_color']); ?>;
                                border-radius: <?php echo esc_attr($options['border_radius_sm']); ?>;
                                padding: 0.75rem;
                                min-height: 80px;
                            ">
                                <div style="
                                    font-weight: bold;
                                    display: inline-block;
                                    width: 24px;
                                    height: 24px;
                                    line-height: 24px;
                                    text-align: center;
                                    background-color: <?php echo esc_attr($options['primary_color']); ?>;
                                    color: <?php echo esc_attr($options['button_text']); ?>;
                                    border-radius: 50%;
                                ">16</div>

                                <div style="
                                    margin-top: 0.5rem;
                                    background-color: <?php echo esc_attr($options['primary_color']); ?>;
                                    color: <?php echo esc_attr($options['button_text']); ?>;
                                    padding: 0.375rem;
                                    border-radius: <?php echo esc_attr($options['border_radius_sm']); ?>;
                                    font-size: 0.75rem;
                                    text-align: center;
                                ">Event</div>
                            </div>

                            <div class="jsm-preview-calendar-day" style="
                                background-color: <?php echo esc_attr($options['surface_color']); ?>;
                                border: 1px solid <?php echo esc_attr($options['border_color']); ?>;
                                border-radius: <?php echo esc_attr($options['border_radius_sm']); ?>;
                                padding: 0.75rem;
                                min-height: 80px;
                            ">
                                <div style="font-weight: bold; color: <?php echo esc_attr($options['text_primary']); ?>;">17</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="jsm-button-preview">
                    <h4><?php _e('Button Example', 'jsm-wp-event-calendar'); ?></h4>
                    <button class="jsm-preview-button" style="
                        background-color: <?php echo esc_attr($options['primary_color']); ?>;
                        color: <?php echo esc_attr($options['button_text']); ?>;
                        border-radius: <?php echo esc_attr($options['button_radius']); ?>;
                        box-shadow: <?php echo esc_attr($options['shadow_sm']); ?>;
                        border: none;
                        padding: 8px 16px;
                        cursor: pointer;
                        transition: all 0.2s;
                    "><?php _e('Example Button', 'jsm-wp-event-calendar'); ?></button>
                </div>
            </div>

            <?php submit_button(__('Save Settings', 'jsm-wp-event-calendar'), 'primary', 'submit', true, array('id' => 'jsm-save-settings')); ?>
        </form>
    </div>
</div>
<style>
    /* Settings styles */
    .jsm-settings-content {
        margin-top: 20px;
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    }

    .jsm-settings-columns {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 30px;
    }

    .jsm-settings-column {
        flex: 1;
        min-width: 300px;
    }

    .jsm-settings-preview {
        margin-top: 30px;
        padding: 20px;
        background-color: #f9f9f9;
        border: 1px solid #e5e5e5;
        border-radius: 5px;
    }

    .jsm-color-previews {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }

    .jsm-preview-section {
        flex: 1;
        min-width: 200px;
    }

    .jsm-color-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 40px;
        color: white;
        margin-bottom: 10px;
        border-radius: 4px;
        font-family: monospace;
    }

    .jsm-calendar-preview {
        margin-top: 30px;
        margin-bottom: 30px;
    }

    .jsm-button-preview {
        margin-top: 20px;
        padding: 15px;
        background-color: #f0f0f0;
        border-radius: 4px;
        text-align: center;
    }

    .jsm-reset-settings-top-right {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1em;
    }

    /* WP Color Picker adjustments */
    .wp-picker-container {
        display: inline-block;
    }

    .wp-picker-container .wp-color-result.button {
        margin: 0 6px 0 0;
    }

    /* Responsive styles */
    @media screen and (max-width: 782px) {
        .jsm-color-previews,
        .jsm-settings-columns {
            flex-direction: column;
        }

        .jsm-settings-column {
            min-width: 100%;
        }
    }
</style>