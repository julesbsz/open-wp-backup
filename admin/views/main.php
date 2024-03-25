<?php $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'home'; ?>

<div class="wrap">
    <h2>Open WP Backup</h2>

    <h2 class="nav-tab-wrapper">
        <a href="?page=open-wp-backup&tab=home" class="nav-tab <?php echo $active_tab == 'home' ? 'nav-tab-active' : ''; ?>">Create Backup</a>
    </h2>

    <?php
        if ($active_tab == 'home') {
            include_once __DIR__ . '/tab-home/home.php';
        }
    ?>
</div>
