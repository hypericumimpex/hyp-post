<?php defined('MODAL') or exit();?>

<style>
	#proModal<?=$mn?> .share_main_box
	{
		display: flex;
		justify-content: center;

	}
	#proModal<?=$mn?> .share_main_box > div
	{
		width: 350px;
	}

	#proModal<?=$mn?> .share_switch
	{
		display: none !important;
	}
</style>

<span class="close" data-modal-close="true">&times;</span>

<div style="padding-top: 40px; text-align: center; font-size: 17px; color: #888; font-weight: 600; margin-bottom: 20px;">
	<?=esc_html__('Select accounts to share post' , 'fs-poster')?>
</div>

<div class="share_main_box">
	<?php
	$postId = (int)$parameters['postId'];
	$postType = 'post';
	define('NOT_CHECK_SP', 'true');
	require_once __DIR__ . "/../post_meta_box.php";
	?>
</div>

<div style="padding-bottom: 20px; text-align: center; margin-top: 10px;">
	<div style="margin: 20px;"><label>Share on background: <input type="checkbox" id="background_share_chckbx"<?=get_option('fs_share_on_background','1')==1?' checked':''?>> </label></div>
	<div><button class="ws_btn ws_bg_danger share_btn" type="button" style="width: 100px;"><?=esc_html__('Share', 'fs-poster')?></button></div>
</div>

<script>

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .share_btn").click(function()
		{
			var nodes = [];
			$("#proModal<?=$mn?> input[name='share_on_nodes[]']").each(function()
			{
				nodes.push($(this).val());
			});

			if( nodes.length == 0 )
			{
				fsCode.toast("<?=esc_html__('No selected account!' , 'fs-poster')?>" , 'danger');
				return;
			}

			var background = $("#background_share_chckbx").is(':checked') ? 1 : 0;

			var custom_messages = {};
			$("#custom_messages textarea[name]").each(function()
			{
				custom_messages[$(this).attr('name').replace('fs_post_text_message_' , '')] = $(this).val();
			});

			fsCode.ajax('share_saved_post' , {
				'post_id': '<?=(int)$parameters['postId']?>' ,
				'nodes': nodes ,
				'background': background,
				'custom_messages': custom_messages
			}, function()
			{
				fsCode.modalHide($("#proModal<?=$mn?>"));

				if( background )
				{
					fsCode.toast("<?=esc_html__('Post will be share on background!' , 'fs-poster')?>");
				}
				else
				{
					fsCode.loadModal('share_feeds' , {'post_id': '<?=(int)$parameters['postId']?>'});
				}
			});
		});
	});

</script>