<?php
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