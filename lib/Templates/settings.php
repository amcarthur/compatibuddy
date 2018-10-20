<?php $this->layout('layout', ['title' => 'Settings']) ?>

<div>
    <form action="options.php" method="post">

        <?php
        settings_fields('compatibuddy_options');
        do_settings_sections('compatibuddy-settings');
        submit_button(__('Save Changes', 'compatibuddy'));
        ?>

    </form>
</div>