<?php
/*
Plugin Name: Front-page Event Editing
Description: Allows you to embed front-page editing for events into your site public pages, using a shortcode.
Plugin URI: http://premium.wpmudev.org/project/events-and-booking
Version: 1.1
Author: WPMU DEV
AddonType: Integration
*/

defined('ABSPATH') or die('No script kiddies please!');

class Eab_Events_FrontPageEditing {

    const SLUG = 'edit-event';
    private $_data;
    private $_options = array();

    private function __construct() {
        $this->_data = Eab_Options::get_instance();
        $this->_options = wp_parse_args($this->_data->get_option('eab-events-fpe'), array(
            'id' => false,
            'integrate_with_my_events' => false,
        ));
    }

    public static function serve() {
        $me = new self();
        $me->_add_hooks();
    }

    private function _add_hooks() {
        add_action('wp', array($this, 'check_page_location'));
        add_action('eab-settings-after_plugin_settings', array($this, 'show_settings'));
        add_filter('eab-settings-before_save', array($this, 'save_settings'));
        add_filter('eab-events-after_event_details', array($this, 'add_edit_link'), 10, 2);
        add_filter('eab-buddypress-group_events-after_head', array($this, 'add_new_link'));
        
        if (!is_admin()) {
            add_action('admin_bar_menu', array($this, 'admin_bar_add_menu_links'), 99);
        }
        
        if ($this->_options['integrate_with_my_events']) {
            add_action('eab-events-my_events-set_up_navigation', array($this, 'my_events_add_event'));
        }

        add_shortcode('eab_event_editor', array($this, 'handle_editor_shortcode'));
        add_action('wp_ajax_eab_events_fpe-save_event', array($this, 'json_save_event'));
    }

    public function show_settings() {
        $pages = get_pages();
        $integrate_with_my_events = $this->_options['integrate_with_my_events'] ? 'checked="checked"' : '';
        $tips = new WpmuDev_HelpTooltips();
        $tips->set_icon_url(EAB_PLUGIN_URL . 'img/information.png');
        
        ?>
        <div id="eab-settings-fpe" class="eab-metabox postbox">
            <h3 class="eab-hndle"><?php esc_html_e('Front-page editing', 'eab'); ?></h3>
            <div class="eab-inside">
                <div class="eab-settings-settings_item">
                    <label for="eab-events-fpe-use_slug">
                        <?php esc_html_e('I want to use this page as my Front Editor page', 'eab'); ?>:
                    </label>
                    <select id="eab-events-fpe-use_slug" name="eab-events-fpe[id]">
                        <option value=""><?php esc_html_e('Use default value', 'eab'); ?>&nbsp;</option>
                        <?php
                        foreach ($pages as $page) {
                            $selected = ($this->_options['id'] == $page->ID) ? 'selected="selected"' : '';
                            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
                        }
                        ?>
                    </select>
                    <?php echo $tips->add_tip(esc_html__("Don't forget to add this shortcode to your selected page: [eab_event_editor]", 'eab')); ?>
                </div>
                <?php if (Eab_AddonHandler::is_plugin_active('eab-buddypres-my_events')) : ?>
                <div class="eab-settings-settings_item">
                    <label for="eab-events-fpe-integrate_with_my_events">
                        <input type="hidden" name="eab-events-fpe[integrate_with_my_events]" value="" />
                        <input type="checkbox" id="eab-events-fpe-integrate_with_my_events" name="eab-events-fpe[integrate_with_my_events]" value="1" <?php echo $integrate_with_my_events; ?> />
                        <?php esc_html_e('Integrate with My Events add-on', 'eab'); ?>
                    </label>
                    <?php echo $tips->add_tip(esc_html__('Enabling this option will add a new "Add Event" tab to "My Events"', 'eab')); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function save_settings($options) {
        $options['eab-events-fpe'] = isset($_POST['eab-events-fpe']) ? $_POST['eab-events-fpe'] : array();
        return $options;
    }

    public function add_edit_link($content, $event) {
        if (!$this->_check_perms($event->get_id())) {
            return $content;
        }

        if ($event->is_recurring() || count($event->get_start_dates()) > 1) {
            return $content;
        }

        $edit_link = $this->_get_front_editor_link($event->get_id());
        return $content . '<p><a href="' . esc_url($edit_link) . '">' . esc_html__('Edit event', 'eab') . '</a></p>';
    }

    // ... (other methods would follow, refactored in a similar manner)

    private function _check_perms($event_id) {
        $post_type = get_post_type_object(Eab_EventModel::POST_TYPE);
        if ($event_id) {
            return current_user_can($post_type->cap->edit_post, $event_id);
        } else {
            return current_user_can($post_type->cap->edit_posts);
        }
    }

    private function _get_front_editor_link($event_id = false) {
        $url = $this->_options['id']
            ? get_permalink($this->_options['id'])
            : home_url(self::SLUG);
        
        $event_id = (int)$event_id ? "?event_id={$event_id}" : '';
        return "{$url}{$event_id}";
    }

    // ... (other methods would follow)
}

Eab_Events_FrontPageEditing::serve();