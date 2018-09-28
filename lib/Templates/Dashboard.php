<?php $this->layout('Layout', ['title' => $title]) ?>

<?php
$table->search_box( 'search', 'search_id' );
$table->display();
?>