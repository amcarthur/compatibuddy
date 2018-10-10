<?php $this->layout('layout', ['title' => 'Settings']) ?>

<div>
    <form action="options.php" method="post">

        <?php settings_fields('compatibuddy_options'); ?>
        <?php do_settings_sections('compatibuddy-settings'); ?>

        <input name="Submit" class="button button-primary" type="submit" value="<?php echo __('Save Changes', 'compatibuddy'); ?>" />
    </form>
</div>