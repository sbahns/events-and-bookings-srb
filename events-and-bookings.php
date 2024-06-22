<?php
/**
 * Plugin Name: Events and Bookings
 * Description: Manage events and bookings with ease.
 * Version: 1.0
 * Author: Your Name
 */

namespace EventsAndBookings;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Main {
    public function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    public function init(): void {
        // Initialize the plugin functionality
        $this->register_post_types();
    }

    public function add_admin_pages(): void {
        add_menu_page(
            __('Events and Bookings', 'events-and-bookings'),
            __('Events and Bookings', 'events-and-bookings'),
            'manage_options',
            'events-and-bookings',
            [$this, 'render_admin_page']
        );
    }

    public function enqueue_admin_scripts(): void {
        wp_enqueue_script('events-bookings-admin-js', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], '1.0.0', true);
        wp_enqueue_style('events-bookings-admin-css', plugin_dir_url(__FILE__) . 'admin.css', [], '1.0.0');
    }

    public function register_post_types(): void {
        register_post_type('event', [
            'labels' => [
                'name' => __('Events', 'events-and-bookings'),
                'singular_name' => __('Event', 'events-and-bookings'),
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);
    }

    public function render_admin_page(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Events and Bookings', 'events-and-bookings'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('events_bookings_options_group');
                do_settings_sections('events-and-bookings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

new Main();

// Admin functions
function events_bookings_admin_init(): void {
    register_setting('events_bookings_options_group', 'events_bookings_options', 'sanitize_text_field');
    add_settings_section('events_bookings_main_section', __('Main Settings', 'events-and-bookings'), 'events_bookings_section_callback', 'events-and-bookings');
    add_settings_field('events_bookings_setting', __('Setting', 'events-and-bookings'), 'events_bookings_setting_callback', 'events-and-bookings', 'events_bookings_main_section');
}

add_action('admin_init', 'events_bookings_admin_init');

function events_bookings_section_callback(): void {
    echo '<p>' . esc_html__('Main settings of the plugin.', 'events-and-bookings') . '</p>';
}

function events_bookings_setting_callback(): void {
    $setting = get_option('events_bookings_options');
    echo '<input type="text" name="events_bookings_options" value="' . esc_attr($setting) . '" />';
}

// AJAX handler
function events_bookings_handle_ajax(): void {
    check_ajax_referer('events_bookings_nonce', 'nonce');
    $response = ['success' => true, 'data' => sanitize_text_field($_POST['data'])];
    wp_send_json($response);
}

add_action('wp_ajax_events_bookings_action', 'events_bookings_handle_ajax');
