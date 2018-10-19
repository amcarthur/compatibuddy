<?php
/**
* @var array $tabData
 */
?>
<div class="tablenav top">
    <div class="alignleft actions bulkactions">
        <form id="compatibuddy-filters-search">
            <label>
                Search
                <input type="text" class="input-text" />
            </label>
            <input type="submit" value="Submit" class="button" />
        </form>
    </div>
</div>
<div id="compatibuddy-duplicate-filters-tree" class="compatibuddy-tree">
    <ul>
        <?php foreach ($tabData['analysis'] as $tag => $calls) { ?>
            <li data-jstree='{"type": "root"}'>Tag: <strong><?php echo esc_html($tag) ?></strong>
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
</div>