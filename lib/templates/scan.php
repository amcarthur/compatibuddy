<?php
$this->layout('layout', ['title' => $title])

/**
 * @var string $currentTab
 * @var string $pluginsUri
 * @var string $themesUri
 * @var array $tabData
 */
?>

<h2 class="nav-tab-wrapper">
    <a href="<?php echo $pluginsUri ?>" class="nav-tab<?php echo $currentTab === 'plugins' ? ' nav-tab-active' : ''; ?>">
        <?php echo __('Plugins', 'compatibuddy') ?>
    </a>
    <a href="<?php echo $themesUri ?>" class="nav-tab<?php echo $currentTab === 'themes' ? ' nav-tab-active' : ''; ?>">
        <?php echo __('Themes', 'compatibuddy') ?>
    </a>
</h2>

<?php

switch ($currentTab) {
    case 'plugins':
        $this->insert('scan::plugins', ['tabData' => $tabData]);
        break;
    case 'themes':
        $this->insert('scan::themes', ['tabData' => $tabData]);
        break;
    default:
        $this->insert('scan::plugins', ['tabData' => $tabData]);
}

?>