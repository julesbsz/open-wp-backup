<?php

use Ifsnop\Mysqldump as IMysqldump;

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
            $relativePath = 'wordpress/' . substr($filePath, strlen($rootPath));

            if (strpos($filePath, $backup_files_path) === 0) {
                continue;
            }

            if (!$zip->addFile($filePath, $relativePath)) {
                error_log('Open WP Backup: Failed to add file to zip archive: ' . $filePath);
                open_wp_backup_admin_notice('Failed to add file to zip archive: ' . $filePath, 'error', true);
                exit;
            }
        }
    }

    // Backup the database
    try {
        $dumpSettings = array('add-drop-table' => true);
        $dump = new IMysqldump\Mysqldump('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD, $dumpSettings);
        
        $dbBackupFilePath = $backup_files_path . 'wp_db_backup-' . date('Y-m-d_H-i-s') . '.sql';
        $dump->start($dbBackupFilePath);

        if (!$zip->addFile($dbBackupFilePath, basename($dbBackupFilePath))) {
            error_log('Open WP Backup: Failed to add database backup to zip archive: ' . $dbBackupFilePath);
            open_wp_backup_admin_notice('Failed to add database backup to zip archive: ' . $dbBackupFilePath, 'error', true);
            exit;
        }

    } catch (\Exception $e) {
        error_log('Open WP Backup: ' . $e->getMessage());
        open_wp_backup_admin_notice('Database backup error: ' . $e->getMessage(), 'error', true);
        return;
    }
    
    if (!$zip->close()) {
        error_log('Open WP Backup: Failed to finalize the zip archive.');
        open_wp_backup_admin_notice('Failed to finalize the zip archive.', 'error', true);
        exit;
    }

    if (file_exists($dbBackupFilePath) && is_writable($dbBackupFilePath)) {
        unlink($dbBackupFilePath);
    } else {
        error_log('Open WP Backup: Unable to locate or delete the temporary database backup file.');
    }

    error_log('Open WP Backup: Backup completed successfully.');
    open_wp_backup_admin_notice('Backup completed successfully.', 'success', true);
    exit;
}
add_action('admin_post_open_wp_backup_start_backup', 'open_wp_backup_start_backup');

/**
 * 
 * Get all previous backups.
 * 
 * @return array
 *
*/
function open_wp_list_backups() {
    $backupDir = WP_CONTENT_DIR . '/open-wp-backups/';
    $backupFiles = glob($backupDir . 'open-wp-backup-*.zip');

    $backups = [];

    foreach ($backupFiles as $file) {
        $filename = basename($file);
        
        $datePart = str_replace(['open-wp-backup-', '.zip'], '', $filename);
        $date = DateTime::createFromFormat('Y-m-d_H-i-s', $datePart);

        if ($date !== false) {
            $size = filesize($file);

            $sizeFormatted = size_format($size, 2);

            $backups[] = [
                'name' => $filename,
                'date' => $date->format('Y-m-d H:i:s'), 
                'size' => $sizeFormatted
            ];
        }
    }

    return $backups;
}

/**
 * 
 * Download a backup file.
 *
 */
function open_wp_backup_download() {
    if (current_user_can('manage_options')) {
        $fileName = isset($_GET['file']) ? sanitize_file_name($_GET['file']) : null;
        $filePath = WP_CONTENT_DIR . '/open-wp-backups/' . $fileName;

        if (file_exists($filePath) && is_file($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            flush();
            readfile($filePath);
            exit;
        } else {
            error_log('Open WP Backup: File not found: ' . $filePath);
            open_wp_backup_admin_notice('File not found.', 'error', true);
            exit;
        }
    } else {
        error_log('Open WP Backup: You do not have permission to perform this action.');
        open_wp_backup_admin_notice('You do not have permission to perform this action.', 'error', true);
        exit;
    }
}
add_action('admin_post_open_wp_backup_download', 'open_wp_backup_download');

/**
 * 
 * Delete a backup file.
 *
 */
function open_wp_backup_delete() {
    if (current_user_can('manage_options')) {
        // TODO: Add nonce check

        $fileName = isset($_GET['file']) ? sanitize_file_name($_GET['file']) : null;
        $filePath = WP_CONTENT_DIR . '/open-wp-backups/' . $fileName;

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
            open_wp_backup_admin_notice('Backup deleted successfully.', 'success', true, 'open-wp-backup&tab=list');
            exit;
        } else {
            error_log('The requested backup file could not be found or is not valid.');
            open_wp_backup_admin_notice('The requested backup file could not be found or is not valid.', 'error', true);
            exit;
        }
    } else {
        error_log('You do not have permission to perform this action.');
        open_wp_backup_admin_notice('You do not have permission to perform this action.', 'error', true);
        exit;
    }
}
add_action('admin_post_open_wp_backup_delete', 'open_wp_backup_delete');
