<?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'home'; ?>

<?php
    $data = get_transient('open_wp_backup_message');

    if ($data !== false) {
        if ($data['result'] == 'success') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($data['message']) . '</p></div>';
        } elseif ($data['result'] == 'error') {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($data['message']) . '</p></div>';
        }

        delete_transient('open_wp_backup_message');
    }
?>

<div class="wrap">
    <h2>Open WP Backup</h2>

    <h2 class="nav-tab-wrapper">
        <a href="?page=open-wp-backup&tab=home" class="nav-tab <?php echo $active_tab == 'home' ? 'nav-tab-active' : ''; ?>">Create Backup</a>
        <a href="?page=open-wp-backup&tab=list" class="nav-tab <?php echo $active_tab == 'list' ? 'nav-tab-active' : ''; ?>">List of Backups</a>
    </h2>

    <?php
        if ($active_tab == 'home') {
            include_once __DIR__ . '/tab-home/home.php';
        }

        if ($active_tab == 'list') {
            include_once __DIR__ . '/tab-list/list.php';
        }
    ?>
</div>
