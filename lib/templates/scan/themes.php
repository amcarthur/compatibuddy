<?php
/**
 * @var array $tabData
 */
?>
<form id="compatibuddy-scan-themes-form" method="post">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php $tabData['table']->display(); ?>
</form>

