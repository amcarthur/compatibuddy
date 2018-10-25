<?php
/**
* @var array $tabData
 */
echo '<pre>' . print_r($tabData['analysis'], true) . '</pre>';die();
?>
<div class="compatibuddy-tree-top">
    <div class="compatibuddy-tree-top-item">
        <form id="compatibuddy-filters-subject" action="<?php echo esc_url($tabData['subjectAnalysisUri']) ?>" method="post">
            <label class="compatibuddy-tree-top-item-heading" for="compatibuddy-filters-subject-select">Subject</label>
            <div class="compatibuddy-flex-inputs">
                <div class="compatibuddy-flex-input-left">
                    <select id="compatibuddy-filters-subject-select" name="subject">
                        <option>Select a module to analyze...</option>
                        <option>All modules</option>
                        <optgroup label="Plugins">
                            <?php foreach ($tabData['plugins'] as $plugin) {
                                $isSelectedPlugin = (isset($tabData['subject']) && $tabData['subject']['type'] === 'plugin' && $tabData['subject']['id'] === $plugin['id']); ?>
                                <option value="plugin-<?php echo esc_attr($plugin['id']) ?>"<?php echo ($isSelectedPlugin ? ' selected' : '') ?>><?php echo esc_html($plugin['metadata']['Name']) ?><?php echo is_plugin_active($plugin['id']) ? ' (Active)</em>' : ' (Inactive)' ?></option>
                            <?php } ?>
                        </optgroup>
                        <optgroup label="Themes">
                            <?php
                            $currentTheme = wp_get_theme();
                            if (!$currentTheme->exists()) {
                                $currentTheme = null;
                            }

                            foreach ($tabData['themes'] as $theme) {
                                $isSelectedTheme = (isset($tabData['subject']) && $tabData['subject']['type'] === 'theme' && $tabData['subject']['id'] === $theme['id']); ?>
                                <option value="theme-<?php echo esc_attr($theme['id']) ?>"<?php echo ($isSelectedTheme ? ' selected' : '') ?>><?php echo esc_html($theme['metadata']['Name']) ?><?php echo ($currentTheme !== null && $currentTheme->get_template() === $theme['id']) ? ' (Active)' : '' ?></option>
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
            <legend class="compatibuddy-tree-top-item-heading">Included Modules</legend>
            <div class="compatibuddy-flex-inputs">
                <div class="compatibuddy-flex-input-left">
                    <label>
                        <input type="checkbox" id="compatibuddy-filters-include-plugins" value="1" checked />
                        Plugins
                    </label>
                </div>
                <div class="compatibuddy-flex-input-left">
                    <label>
                        <input type="checkbox" id="compatibuddy-filters-include-themes" value="1" checked />
                        Themes
                    </label>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="compatibuddy-tree-top-item">
        <form id="compatibuddy-filters-sort">
            <label class="compatibuddy-tree-top-item-heading" for="compatibuddy-filters-sort-by">Sort By</label>
            <div class="compatibuddy-flex-inputs">
                <div class="compatibuddy-flex-input-left">
                    <select id="compatibuddy-filters-sort-by">
                        <option>Select a field to sort by...</option>
                        <optgroup label="Tag">
                            <option value="tag">Tag</option>
                        </optgroup>
                        <optgroup label="Module">
                            <option value="module-type">Module Type</option>
                            <option value="module-name">Module Name</option>
                        </optgroup>
                        <optgroup label="Function Call">
                            <option value="function-to-add">Function to Add</option>
                            <option value="priority">Priority</option>
                            <option value="file">File</option>
                        </optgroup>
                    </select>
                </div>
                <div class="compatibuddy-flex-input-right">
                    <label>
                        <input type="radio" name="compatibuddy-filters-sort-by-order" value="asc" checked /> Asc
                    </label>
                </div>
                <div class="compatibuddy-flex-input-right">
                    <label>
                        <input type="radio" name="compatibuddy-filters-sort-by-order" value="desc" /> Desc
                    </label>
                </div>
                <div class="compatibuddy-flex-input-right">
                    <input type="submit" value="Sort" class="button" />
                </div>
            </div>
        </form>
    </div>
    <div class="compatibuddy-tree-top-item">
        <form id="compatibuddy-filters-search">
            <label class="compatibuddy-tree-top-item-heading" for="compatibuddy-filters-search-text">Search</label>
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
    <h3>Loading...</h3>
</div>
<div id="compatibuddy-duplicate-filters-tree" class="compatibuddy-tree">
    <ul>
        <?php foreach ($tabData['analysis'] as $tag => $functionNames) { ?>
            <li data-jstree='{"type": "root"}'>Tag: <strong><?php echo esc_html($tag) ?></strong>
                <ul>
                    <?php foreach ($functionNames as $functionName => $calls) { ?>
                        <li data-jstree='{"type": "function_name"}'>Function Name: <strong><?php echo esc_html($functionName) ?></strong>
                            <ul>
                                <?php foreach ($calls as $call) { $moduleType = $call['module']['type']; $isPlugin = $moduleType === 'plugin'; ?>
                                    <li data-jstree='{"type": "<?php echo $moduleType ?>"}'><?php echo esc_html(ucfirst($moduleType)) ?>: <strong><?php echo esc_html($call['module']['metadata']['Name']) ?></strong>
                                        <ul>
                                            <li data-jstree='{"type": "function"}'>Function to Add: <strong><?php echo esc_html($call['function_to_add']) ?></strong></li>
                                            <li data-jstree='{"type": "priority"}'>Priority: <strong><?php echo esc_html(isset($call['priority']) ? $call['priority'] : '<N/A>') ?></strong></li>
                                            <li data-jstree='{"type": "accepted_args"}'>Accepted Arguments: <strong><?php echo esc_html(isset($call['accepted_args']) ? $call['accepted_args'] : '<N/A>') ?></strong></li>
                                            <li data-jstree='{"type": "file"}'>File: <strong><?php echo esc_html($call['file']) ?></strong></li>
                                            <li data-jstree='{"type": "line"}'>Line: <strong><?php echo esc_html($call['line']) ?></strong></li>
                                            <?php if ($call['module']['type'] === 'plugin') { ?>
                                                <li data-jstree='{"type": "edit"}'><a href="<?php echo esc_url(add_query_arg([
                                                        'plugin' => rawurlencode($call['module']['id']),
                                                        'file' => rawurlencode($call['file'])
                                                    ], self_admin_url('plugin-editor.php'))) ?>">Open in Editor</a></li>
                                            <?php } else { ?>
                                                <li data-jstree='{"type": "edit"}'><a href="<?php echo esc_url(add_query_arg([
                                                        'theme' => rawurlencode($call['module']['id']),
                                                        'file' => rawurlencode(substr($call['file'], strpos($call['file'], '/') + 1))
                                                    ], self_admin_url('theme-editor.php'))) ?>">Open in Editor</a></li>
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