<div class="wrap">
    <?php if ($this->section('header')) { ?>
        <?php echo $this->section('header') ?>
    <?php } else { ?>
        <h1 class="wp-heading-inline"><?php echo esc_html($title); ?></h1>
    <?php } ?>
    <hr class="wp-header-end">
    <?php echo $this->section('content') ?>
</div>
