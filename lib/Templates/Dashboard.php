<?php $this->layout('Layout', ['title' => $title]) ?>

<form id="compatibuddy-detect-issues-all" method="post" action="">
    <input type="submit" name="compatibuddy-detect-issues-all" class="button button-primary" value="<?php
    echo esc_attr( __( 'Detect issues with all plugins', 'compatibuddy' ) );
    ?>" />
</form>

<?php
echo '<pre>' . print_r($duplicateAddFilters, true) . '</pre>';
?>