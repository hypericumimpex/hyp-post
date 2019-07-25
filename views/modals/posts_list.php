<?php defined('MODAL') or exit();?>

<span class="close" data-modal-close="true">&times;</span>

<div style="margin: 10px;">
	<?php
	require_once FS_VIEWS_DIR . 'app_menus/posts.php';
	?>
</div>

<script>
	fsCode.modalWidth('<?=$mn?>' , '80');
</script>
