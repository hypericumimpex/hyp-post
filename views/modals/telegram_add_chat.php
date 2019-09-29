<?php defined('MODAL') or exit();?>

<?php
$accountId = (int)FS_post('account_id' , '0' , 'num');
?>

<style>
	.telegram_logo > img
	{
		width: 60%;
		height: 180px;
		margin: 20px;
	}
	.telegram_logo
	{
		width: 55%;
		display: flex;
		justify-content: center;
	}
	#proxy_show
	{
		margin-bottom: 20px;
		margin-right: 70px;
		display: flex;
		align-items: center;
	}
	#proxy_show
	{
		width: 180px;
	}

	.last_active_chats_title
	{
		font-size: 13px;
		font-weight: 600;
	}

	.last_active_chats_list > div
	{
		padding: 2px 0;
		color: #999;
		font-weight: 600;
		white-space: nowrap;
		overflow: hidden;
		cursor: pointer;
	}

	.last_active_chats_list > div:hover
	{
		color: #ff7675;
	}

	.last_active_chats_list > div > i
	{
		color: #999;
	}

	.last_active_chats_list
	{
		padding: 10px;
		margin-right: 20px;
		margin-top: 5px;
		margin-bottom: 15px;

		-webkit-box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.10);
		-moz-box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.10);
		box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.10);

		max-height: 150px;
		overflow: auto;
	}

	.reload-list
	{
		color: #999;
		padding: 5px;
		cursor: pointer;
	}

</style>
<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 20px; min-height: 350px; display: flex; justify-content: center; align-items: center;">
	<div class="telegram_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/telegram.svg'?>"></div>
	<div style="width: 45%;">

		<div style="margin-bottom: 20px; font-size: 15px; font-weight: 600; color: #888;"><?=esc_html__('Add chat' , 'fs-poster')?></div>

		<div style="padding-right: 25px; margin-bottom: 20px;">
			<input type="text" placeholder="Chat ID" class="ws_form_element chat_id">
		</div>

		<div class="last_active_chats_title"><?=esc_html__('Last active chats:' , 'fs-poster')?> <i class="fa fa-sync-alt reload-list ws_tooltip" data-title="Reload list"></i> </div>
		<div class="last_active_chats_list">

		</div>

		<div style="margin-bottom: 30px;">
			<button type="button" class="ws_btn ws_bg_danger save-btn"><?=esc_html__('ADD CHAT' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('CANCEL' , 'fs-poster')?></button>
		</div>
	</div>
</div>

<script>

	$("#proModal<?=$mn?> .reload-list").click(function()
	{
		fsCode.ajax('telegram_last_active_chats', {account: '<?=$accountId?>'}, function( result )
		{
			$("#proModal<?=$mn?> .last_active_chats_list").empty();
			for( var i in result['list'] )
			{
				$("#proModal<?=$mn?> .last_active_chats_list").append('<div data-id="' + result['list'][i]['id'] + '"><i class="far fa-comment-alt"></i> ' + result['list'][i]['name'] + '</div>');
			}

			if( result['list'].length == 0 )
			{
				$("#proModal<?=$mn?> .last_active_chats_list").html('<i>No active chat(s) found.</i>');
			}
		})
	}).trigger('click');

	$("#proModal<?=$mn?> .save-btn").click(function()
	{
		var chat_id	= $("#proModal<?=$mn?> .chat_id").val();

		if( chat_id == '' )
		{
			fsCode.alert('Please type chat id!');
			return;
		}

		fsCode.ajax( 'telegram_chat_save' , {'account_id': '<?=$accountId?>', 'chat_id': chat_id}, function(result)
		{
			$("#telegram-chat-list-tbl > tbody").append('<tr data-id="'+result['id']+'"><td><img class="ws_img_style" src="'+result['chat_pic']+'"><span style="vertical-align: middle;">'+result['chat_name']+'</span><a href="'+result['chat_link']+'" target="_blank" class="ws_btn ws_tooltip" data-title="Chat link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a></td><td style="padding-right: 44px;"><div class="node_chckbx ws_tooltip" data-title="<?=esc_html__('Click to change status' , 'fs-poster');?>" data-float="left" style="float: right;"><i class="fa fa-check"></i></div><button class="delete_btn delete_btn_desing ws_tooltip" data-title="Delete account" data-float="left"><i class="fa fa-trash"></i></button></td></tr>');

			fsCode.modalHide( $("#proModal<?=$mn?>") );
		});

	});

	$("#proModal<?=$mn?> .last_active_chats_list").on('click', ' > div', function ()
	{
		var id = $(this).data('id');

		$("#proModal<?=$mn?> .chat_id").val( id );
	});

</script>