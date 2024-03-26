<div id="tab-list-content">
    <h2><?php _e('List of previous backups', 'wp-backup-plugin'); ?></h2>

    <?php
        $backups = open_wp_list_backups();
        if (!empty($backups)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>' . esc_html__('Name', 'wp-backup-plugin') . '</th>';
            echo '<th>' . esc_html__('Date', 'wp-backup-plugin') . '</th>';
            echo '<th>' . esc_html__('Size', 'wp-backup-plugin') . '</th>';
            echo '<th>' . esc_html__('Download Link', 'wp-backup-plugin') . '</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
        
            foreach ($backups as $backup) {
                $downloadLink = admin_url('admin-post.php?action=open_wp_backup_download&file=' . urlencode($backup['name']));

                echo '<tr>';
                echo '<td>' . esc_html($backup['name']) . '</td>';
                echo '<td>' . esc_html($backup['date']) . '</td>';
                echo '<td>' . esc_html($backup['size']) . '</td>';
                echo '<td><a href="' . esc_url($downloadLink) . '">' . esc_html__('Download', 'wp-backup-plugin') . '</a></td>';
                echo '</tr>';
            }
        
            echo '</tbody>';
            echo '</table>';
        } else {
            esc_html_e('You don\'t have any backup yet', 'wp-backup-plugin');
        }        
    ?>

</div>