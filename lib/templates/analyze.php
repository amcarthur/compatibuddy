<?php $this->start('header') ?>

    <h1 class="wp-heading-inline">
        <?php echo esc_html($title); ?>
        <a href="#" class="page-title-action" id="compatibuddy-filter-import-button">Import</a>
        <a href="<?php echo esc_url(add_query_arg(
            ['action' => 'compatibuddy-filter-export'],
            wp_nonce_url(admin_url('admin.php'), 'compatibuddy_filter_export'))) ?>"
           class="page-title-action">Export</a>
    </h1>
    <form id="compatibuddy-filter-import-upload" enctype="multipart/form-data" action="<?php echo esc_url(add_query_arg(
            ['action' => 'compatibuddy-filter-import'])) ?>" method="post" class="compatibuddy-upload-form">
        <label class="screen-reader-text" for="compatibuddy-filter-import-upload-file">Import file</label>
        <input type="file" id="compatibuddy-filter-import-upload-file" name="importfile" />
        <input type="submit" class="button" value="Import" />
        <?php wp_nonce_field('compatibuddy-filter-import') ?>
    </form>
<?php
$this->end();
$this->layout('layout', ['title' => $title])

/**
 * @var string $currentTab
 * @var string $duplicateFiltersUri
 * @var string $higherPriorityFiltersUri
 * @var array $tabData
 */
?>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo $duplicateFiltersUri ?>" class="nav-tab<?php echo $currentTab === 'duplicateFilters' ? ' nav-tab-active' : ''; ?>">
            <?php echo __('Duplicate Filters', 'compatibuddy') ?>
        </a>
        <a href="<?php echo $higherPriorityFiltersUri ?>" class="nav-tab<?php echo $currentTab === 'higherPriorityFilters' ? ' nav-tab-active' : ''; ?>">
            <?php echo __('Higher Priority Filters', 'compatibuddy') ?>
        </a>
    </h2>

<?php

switch ($currentTab) {
    case 'duplicateFilters':
        $this->insert('analyze::duplicateFilters', ['tabData' => $tabData]);
        break;
    case 'higherPriorityFilters':
        $this->insert('analyze::higherPriorityFilters', ['tabData' => $tabData]);
        break;
    default:
        $this->insert('analyze::duplicateFilters', ['tabData' => $tabData]);
}

?>