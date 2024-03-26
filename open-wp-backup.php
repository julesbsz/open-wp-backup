<?php
/**
 * Plugin Name: Open WP Backup
 * Plugin URI:  https://github.com/julesbsz/open-wp-backup
 * Description: Create & Schedule backups of your Wordpress site.
 * Version:     0.1.0
 * Author:      Jules Bousrez
 * Author URI:  https://julesbousrez.fr
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: open-wp-backup
**/

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

defined('PLUGIN_ROOT_DIR') or define('PLUGIN_ROOT_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';

include_once __DIR__ . '/includes/main-functions.php';
include_once __DIR__ . '/includes/backup-functions.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'open_wp_backup_activate');
register_deactivation_hook(__FILE__, 'open_wp_backup_deactivate');

function open_wp_backup_activate() {
    // Activation code here
}

function open_wp_backup_deactivate() {
    // Deactivation code here
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