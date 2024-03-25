<?php

/**
 * 
 * Start a new backup of wordpress.
 *
 */

function open_wp_backup_start_backup() {
    // Checking if nonce is set
    if(!wp_verify_nonce($_REQUEST['open_wp_backup_create_backup_nonce'], 'open_wp_backup_create_backup_action')){
        wp_die('Failed security check');
    }

    // Checking if user has permission
    if (!current_user_can('manage_options')) {
        wp_die('You do not have permission to perform this action.');
    }

    // Checking if backup directory exists
    $backup_files_path = WP_CONTENT_DIR . '/backups/';
    if (!file_exists($backup_files_path)) {
        if (!wp_mkdir_p($backup_files_path)) {
            error_log('Open WP Backup: Failed to create backup directory.');
            die('Failed to create backup directory.');
            exit;
        }
    }

    $rootPath = ABSPATH; 
    $zipFilePath = $backup_files_path . 'open-wp-backup-' . date('Y-m-d_H-i-s') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        error_log('Open WP Backup: Cannot open zip file for writing: ' . $zipFilePath);
        die('Cannot open zip file for writing: ' . $zipFilePath);
        exit;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath));
            if (!$zip->addFile($filePath, $relativePath)) {
                error_log('Open WP Backup: Failed to add file to zip: ' . $filePath);
            }
        }
    }
    
    if (!$zip->close()) {
        error_log('Open WP Backup: Failed to finalize the zip archive.');
        die('Failed to finalize the zip archive.');
        exit;
    }

    error_log('Open WP Backup: Backup completed successfully.');
    die('Backup completed successfully.');
    exit;
}
add_action('admin_post_open_wp_backup_start_backup', 'open_wp_backup_start_backup');