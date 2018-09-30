<?php
/**
 * @var array $tabData
 */
?>
<form id="compatibuddy-analyze-higher-priority-filters-subject-select" method="post">
    <label>
        Subject
        <select id="compatibuddy-higher-priority-filters-subject" name="compatibuddy-higher-priority-filters-subject">
            <option>Select a plugin to analyze...</option>
            <?php foreach ($tabData['plugins'] as $plugin) { ?>
                <option value="<?php echo esc_attr($plugin['id']) ?>"><?php echo esc_html($plugin['metadata']['Name']) ?></option>
            <?php } ?>
        </select>
    </label>
    <input type="submit" class="button button-primary" name="submit" value="Analyze" />
    <?php wp_nonce_field('compatibuddy_analyze_higher_priority_filters_subject_select') ?>
</form>
<?php if (isset($tabData['analysis'])) { ?>
    <div id="compatibuddy-higher-priority-filters-tree" class="compatibuddy-tree">
        <ul>
            <?php foreach ($tabData['analysis'] as $tag => $calls) { ?>
                <li><?php echo esc_html($tag) ?>
                    <ul>
                        <?php foreach ($calls as $call) { ?>
                            <li><?php echo esc_html($call['module']['metadata']['Name']) ?>
                                <ul>
                                    <li><?php echo esc_html($call['function_to_add']) ?></li>
                                    <li><?php echo esc_html(isset($call['priority']) ? $call['priority'] : '<N/A>') ?></li>
                                    <li><?php echo esc_html(isset($call['accepted_args']) ? $call['accepted_args'] : '<N/A>') ?></li>
                                    <li><?php echo esc_html($call['file']) ?></li>
                                    <li><?php echo esc_html($call['line']) ?></li>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>