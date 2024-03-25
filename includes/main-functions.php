<?php

/**
 * Main plugin functions
**/

if (!defined('ABSPATH')) {
    exit;
}

// Load scripts
function open_wp_backup_admin_scripts() {
    wp_enqueue_script('open-wp-backup-admin-script', plugin_dir_url(__FILE__) . 'admin/js/admin-script.js', array('jquery'), false, true);
}
add_action('admin_enqueue_scripts', 'open_wp_backup_admin_scripts');


// Load styles
function open_wp_backup_admin_styles() {
    wp_enqueue_style('open-wp-backup-admin-style', plugin_dir_url(__FILE__) . 'admin/css/admin-style.css');
}
add_action('admin_enqueue_scripts', 'open_wp_backup_admin_styles');

// Add plugin views to WP dashboard
add_action('admin_menu', 'open_wp_backup_add_admin_menu');
function open_wp_backup_add_admin_menu() {
    add_menu_page(
        __('Open WP Backup', 'open-wp-backup'), // Page title
        __('Open WP Backup', 'open-wp-backup'), // Menu title
        'manage_options', // Capability
        'open-wp-backup', // Menu slug
        'open_wp_backup_menu', // Callback function
        'dashicons-backup', // Icon
        100 // Position
    ); 
}

// Render settings page
function open_wp_backup_menu() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'open-wp-backup'));
    }

    include_once PLUGIN_ROOT_DIR . 'admin/views/main.php';
}

