<?php
/**
* @var array $tabData
 */
?>
<div id="compatibuddy-duplicate-filters-tree" class="compatibuddy-tree">
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