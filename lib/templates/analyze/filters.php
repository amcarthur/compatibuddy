<?php
/**
* @var array $tabData
 */
function getFunctionNameKeyValue($functionNameKey) {
    switch ($functionNameKey) {
        case 'add_filter_calls':
            return 'add_filter';
        case 'remove_filter_calls':
            return 'remove_filter';
        case 'remove_all_filters_calls':
            return 'remove_all_filters';
        default:
            return '';
    }
}
?>
<div id="compatibuddy-filters-tree-container">
    <div class="compatibuddy-tree-top">
        <div class="compatibuddy-tree-top-item">
            <form id="compatibuddy-filters-subject" action="<?php echo esc_url($tabData['subjectAnalysisUri']) ?>" method="post">
                <label class="compatibuddy-tree-top-item-heading" for="compatibuddy-filters-subject-select"><?php echo esc_html(__('Subject', 'compatibuddy')) ?></label>
                <div class="compatibuddy-flex-inputs">
                    <div class="compatibuddy-flex-input-left">
                        <select id="compatibuddy-filters-subject-select" name="subject">
                            <option><?php echo esc_html(__('Select a module to analyze...', 'compatibuddy')) ?></option>
                            <option><?php echo esc_html(__('All modules', 'compatibuddy')) ?></option>
                            <optgroup label="<?php echo esc_attr(__('Plugins', 'compatibuddy')) ?>">
                                <?php foreach ($tabData['plugins'] as $plugin) {
                                    $isSelectedPlugin = (isset($tabData['subject']) && $tabData['subject']['type'] === 'plugin' && $tabData['subject']['id'] === $plugin['id']); ?>
                                    <option value="compatibuddy_plugin-<?php echo esc_attr($plugin['id']) ?>"<?php echo ($isSelectedPlugin ? ' selected' : '') ?>><?php echo esc_html($plugin['metadata']['Name']) ?>&nbsp;<?php echo is_plugin_active($plugin['id']) ? esc_attr(__('(Active)', 'compatibuddy')) : esc_attr(__('(Inactive)', 'compatibuddy')) ?></option>
                                <?php } ?>
                            </optgroup>
                            <optgroup label="<?php echo esc_attr(__('Themes', 'compatibuddy')) ?>">
                                <?php
                                $currentTheme = wp_get_theme();
                                if (!$currentTheme->exists()) {
                                    $currentTheme = null;
                                }

                                foreach ($tabData['themes'] as $theme) {
                                    $isSelectedTheme = (isset($tabData['subject']) && $tabData['subject']['type'] === 'theme' && $tabData['subject']['id'] === $theme['id']); ?>
                                    <option value="compatibuddy_theme-<?php echo esc_attr($theme['id']) ?>"<?php echo ($isSelectedTheme ? ' selected' : '') ?>><?php echo esc_html($theme['metadata']['Name']) ?><?php echo ($currentTheme !== null && $currentTheme->get_template() === $theme['id']) ? ' ' . esc_attr(__('(Active)', 'compatibuddy')) : '' ?></option>
                                <?php } ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="compatibuddy-flex-input-right">
                        <input type="submit" value="Submit" class="button" />
                    </div>
                </div>
                <?php wp_nonce_field('compatibuddy-filter-analyze-subject') ?>
            </form>
        </div>
        <div class="compatibuddy-tree-top-item">
            <fieldset>
                <legend class="compatibuddy-tree-top-item-heading"><?php echo esc_html(__('Included Modules', 'compatibuddy')) ?></legend>
                <div class="compatibuddy-flex-inputs">
                    <div class="compatibuddy-flex-input-left">
                        <label>
                            <input type="checkbox" class="compatibuddy-tree-include-plugins" value="1" checked />
                            <?php echo esc_html(__('Plugins', 'compatibuddy')) ?>
                        </label>
                    </div>
                    <div class="compatibuddy-flex-input-left">
                        <label>
                            <input type="checkbox" class="compatibuddy-tree-include-themes" value="1" checked />
                            <?php echo esc_html(__('Themes', 'compatibuddy')) ?>
                        </label>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="compatibuddy-tree-top-item compatibuddy-sorting">
            <form class="compatibuddy-tree-sort">
                <label class="compatibuddy-tree-top-item-heading" for="compatibuddy-filters-tree-sort-by"><?php echo esc_html(__('Sort By', 'compatibuddy')) ?></label>
                <div class="compatibuddy-flex-inputs">
                    <div class="compatibuddy-flex-input-left">
                        <select id="compatibuddy-filters-tree-sort-by" name="compatibuddy-tree-sort-by">
                            <option><?php echo esc_html(__('Select a field to sort by...', 'compatibuddy')) ?></option>
                            <optgroup label="Tag">
                                <option value="tag"><?php echo esc_html(__('Tag', 'compatibuddy')) ?></option>
                            </optgroup>
                            <optgroup label="Function">
                                <option value="function"><?php echo esc_html(__('Function', 'compatibuddy')) ?></option>
                            </optgroup>
                            <optgroup label="Module">
                                <option value="module-type"><?php echo esc_html(__('Module type', 'compatibuddy')) ?></option>
                                <option value="module-name"><?php echo esc_html(__('Module name', 'compatibuddy')) ?></option>
                            </optgroup>
                            <optgroup label="Function Call">
                                <option value="function-to-add"><?php echo esc_html(__('Function to add or remove', 'compatibuddy')) ?></option>
                                <option value="priority"><?php echo esc_html(__('Priority', 'compatibuddy')) ?></option>
                                <option value="file"><?php echo esc_html(__('File', 'compatibuddy')) ?></option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="compatibuddy-flex-input-right">
                        <label>
                            <input type="radio" name="compatibuddy-tree-sort-order" class="compatibuddy-order" value="asc" checked />&nbsp;<?php echo ' ' . esc_html(__('Asc', 'compatibuddy')) ?>
                        </label>
                    </div>
                    <div class="compatibuddy-flex-input-right">
                        <label>
                            <input type="radio" name="compatibuddy-tree-sort-order" value="desc" />&nbsp;<?php echo esc_html(__('Desc', 'compatibuddy')) ?>
                        </label>
                    </div>
                    <div class="compatibuddy-flex-input-right">
                        <input type="submit" value="Sort" class="button" />
                    </div>
                </div>
            </form>
        </div>
        <div class="compatibuddy-tree-top-item">
            <form class="compatibuddy-tree-search">
                <label class="compatibuddy-tree-top-item-heading" for="compatibuddy-filters-search-text"><?php echo esc_html(__('Search', 'compatibuddy')) ?></label>
                <div class="compatibuddy-flex-inputs">
                    <div class="compatibuddy-flex-input-left">
                        <input id="compatibuddy-filters-search-text" type="text" class="input-text" />
                    </div>
                    <div class="compatibuddy-flex-input-right">
                        <input type="submit" value="Submit" class="button compatibuddy-flex-input-right" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="compatibuddy-tree-loading">
        <h3><?php echo esc_html(__('Loading...', 'compatibuddy')) ?></h3>
    </div>
    <div class="compatibuddy-tree">
        <ul>
            <?php foreach ($tabData['analysis'] as $tag => $functionNameKeys) { ?>
                <li data-jstree='{"type": "root"}'><?php echo esc_html(__('Tag:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html($tag) ?></strong>
                    <ul>
                        <?php foreach ($functionNameKeys as $functionNameKey => $calls) { ?>
                            <li data-jstree='{"type": "function_name"}'><?php echo esc_html(__('Function:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html(getFunctionNameKeyValue($functionNameKey)) ?></strong>
                                <ul>
                                    <?php foreach ($calls as $call) { $moduleType = $call['module']['type']; $isPlugin = $moduleType === 'plugin'; ?>
                                        <li data-jstree='{"type": "<?php echo $moduleType ?>"}'><?php echo esc_html(ucfirst($moduleType)) ?>: <strong><?php echo esc_html($call['module']['metadata']['Name']) ?></strong>
                                            <ul>
                                                <li data-jstree='{"type": "function"}'><?php echo esc_html(__('Function to Add:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html($call['function_to_add']) ?></strong></li>
                                                <li data-jstree='{"type": "priority"}'><?php echo esc_html(__('Priority:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html(isset($call['priority']) ? $call['priority'] : esc_html(__('<N/A>', 'compatibuddy'))) ?></strong></li>
                                                <li data-jstree='{"type": "accepted_args"}'><?php echo esc_html(__('Accepted Arguments:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html(isset($call['accepted_args']) ? $call['accepted_args'] : esc_html(__('<N/A>', 'compatibuddy'))) ?></strong></li>
                                                <li data-jstree='{"type": "file"}'><?php echo esc_html(__('File:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html($call['file']) ?></strong></li>
                                                <li data-jstree='{"type": "line"}'><?php echo esc_html(__('Line:', 'compatibuddy')) ?>&nbsp;<strong><?php echo esc_html($call['line']) ?></strong></li>
                                                <?php if ($call['module']['type'] === 'plugin') { ?>
                                                    <li data-jstree='{"type": "edit"}'><a href="<?php echo esc_url(add_query_arg([
                                                            'plugin' => rawurlencode($call['module']['id']),
                                                            'file' => rawurlencode($call['file'])
                                                        ], self_admin_url('plugin-editor.php'))) ?>"><?php echo esc_html(__('Open in Editor', 'compatibuddy')) ?></a></li>
                                                <?php } else { ?>
                                                    <li data-jstree='{"type": "edit"}'><a class="compatibuddy-tree-link" href="<?php echo esc_url(add_query_arg([
                                                            'theme' => rawurlencode($call['module']['id']),
                                                            'file' => rawurlencode(substr($call['file'], strpos($call['file'], '/') + 1))
                                                        ], self_admin_url('theme-editor.php'))) ?>"><?php echo esc_html(__('Open in Editor', 'compatibuddy')) ?></a></li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>