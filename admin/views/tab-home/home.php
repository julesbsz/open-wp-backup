<div id="tab-home-content">
    <h2><?php _e('Create a New Backup', 'wp-backup-plugin'); ?></h2>

    <p><?php _e('Click the button below to start the backup process.', 'wp-backup-plugin'); ?></p>
    <form id="backup-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('open_wp_backup_create_backup_action', 'open_wp_backup_create_backup_nonce'); ?>

        <input type="hidden" name="action" value="open_wp_backup_start_backup">
        <?php submit_button(__('Start Backup', 'open-wp-backup'), 'primary', 'start-backup'); ?>
    </form>
</div>