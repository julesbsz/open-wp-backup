<?php

/**
 * 
 * Start a new backup of wordpress.
 *
 */

function open_wp_backup_start_backup() {
    error_log('Open WP Backup: Starting backup process.');

    // Checking if nonce is set
    if(!wp_verify_nonce($_REQUEST['open_wp_backup_create_backup_nonce'], 'open_wp_backup_create_backup_action')){
        error_log('Open WP Backup: Failed security check');
        open_wp_backup_admin_notice('Failed security check', 'error', true);
        exit;
    }

    // Checking if user has permission
    if (!current_user_can('manage_options')) {
        error_log('You do not have permission to perform this action.');
        open_wp_backup_admin_notice('You do not have permission to perform this action.', 'error', true);
        exit;
    }

    // Checking if backup directory exists
    $backup_files_path = WP_CONTENT_DIR . '/open-wp-backups/';
    if (!file_exists($backup_files_path)) {
        error_log('Open WP Backup: Backup directory does not exist.');
        if (!wp_mkdir_p($backup_files_path)) {
            error_log('Open WP Backup: Failed to create backup directory.');
            open_wp_backup_admin_notice('Failed to create backup directory.', 'error', true);
            exit;
        }

        if (!is_writable($backup_files_path)) {
            error_log('Open WP Backup: Backup directory is not writable.');
            open_wp_backup_admin_notice('Backup directory is not writable.', 'error', true);
            exit;
        }

        error_log('Open WP Backup: Backup directory created successfully.');
    }

    $rootPath = ABSPATH; 
    $zipFilePath = $backup_files_path . 'open-wp-backup-' . date('Y-m-d_H-i-s') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        error_log('Open WP Backup: Cannot open zip file for writing: ' . $zipFilePath);
        open_wp_backup_admin_notice('Cannot open zip file for writing: ' . $zipFilePath, 'error', true);
        exit;
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath));
            if (!$zip->addFile($filePath, $relativePath)) {
                error_log('Open WP Backup: Failed to add file to zip archive: ' . $filePath);
                open_wp_backup_admin_notice('Failed to add file to zip archive: ' . $filePath, 'error', true);
                exit;
            }
        }
    }
    
    if (!$zip->close()) {
        error_log('Open WP Backup: Failed to finalize the zip archive.');
        open_wp_backup_admin_notice('Failed to finalize the zip archive.', 'error', true);
        exit;
    }

    error_log('Open WP Backup: Backup completed successfully.');
    open_wp_backup_admin_notice('Backup completed successfully.', 'success', true);
    exit;
}
add_action('admin_post_open_wp_backup_start_backup', 'open_wp_backup_start_backup');