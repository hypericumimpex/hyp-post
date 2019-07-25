<?php defined('MODAL') or exit();?>

<style>
	.instagram_logo > img
	{
		width: 60%;
		height: 180px;
		margin: 20px;
	}
	.instagram_logo
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
</style>

<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 60px; display: flex; justify-content: center; align-items: center;">
	<div class="instagram_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/instagram.png'?>"></div>
	<div style="width: 45%;">

		<div style="margin-bottom: 20px; font-size: 15px; font-weight: 600; color: #888;"><?=esc_html__('Add new instagram account' , 'fs-poster')?></div>

		<div style="position: relative; margin-bottom: 20px; margin-right: 70px;">
			<input type="text" placeholder="Instagram username" class="ws_form_element username" style="padding-left: 30px">
			<i class="fa fa-user" style="position: absolute; left: 10px; color: #AAA; top: 10px;"></i>
		</div>
		<div style="position: relative; margin-bottom: 20px; margin-right: 70px;">
			<input type="password" placeholder="Instagram password" class="ws_form_element password" style="padding-left: 30px">
			<i class="fa fa-key" style="position: absolute; left: 10px; color: #AAA; top: 10px;"></i>
		</div>

		<div style="margin-bottom: 20px;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show').slideDown(200); });">
			<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
		</div>

		<div style="display: none;" id="proxy_show">
			<div style="position: relative;">
				<input type="text" placeholder="Proxy" class="ws_form_element proxy" style="padding-left: 30px">
				<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
			</div>
			<div style="width: 30px; text-align: right; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
		</div>

		<div>
			<button type="button" class="ws_btn ws_bg_danger add_account_btn"><?=esc_html__('ADD ACCOUNT' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('CANCEL' , 'fs-poster')?></button>
		</div>

	</div>
</div>



<script>

	function require_action(response , username , password , proxy)
	{
		if( 'do' in response && response['do'] == 'challenge' )
		{
			var cModalId = fsCode.modal('<div style="display: flex; height: 400px;align-items: center;justify-content: center;flex-direction: column;"><div style="margin: 5px;font-size: 15px;color: #777; text-align: center;">Challenge required! Activation code was sent (' + fsCode.htmlspecialchars(response['message']) + ')<br> Please copy this code here:</div><div style="margin: 5px;"><input class="ws_form_element2" style="width: 150px;text-align: center;" placeholder="Code" id="insta_confirm_code" type="text"></div><div style="margin: 5px;"><button type="button" class="ws_btn ws_bg_danger" id="insta_confirm_button">Confirm</button></div></div>');

			$(cModalId[2] + " #insta_confirm_button").click(function()
			{
				var code = $(cModalId[2] + " #insta_confirm_code").val();

				if( code.trim() == '' )
				{
					fsCode.alert('Please enter the code!');
					return;
				}

				fsCode.ajax('confirm_instagram_challenge' , {
					'username': username ,
					'password': password ,
					'proxy': proxy ,
					'code': code ,
					'user_id': response['user_id'] ,
					'nonce_code': response['nonce_code']
				}, function(result)
				{
					fsCode.modalHide($(cModalId[2]));

					require_action(result , username , password , proxy);
				});
			});
		}
		else if( 'do' in response && response['do'] == 'two_factor' )
		{
			var cModalId = fsCode.modal('<div style="display: flex; height: 400px;align-items: center;justify-content: center;flex-direction: column;"><div style="margin: 5px;font-size: 15px;color: #777; text-align: center;">Two factor authentication. Activation code was sent (' + fsCode.htmlspecialchars(response['message']) + ')<br> Please copy this code here:</div><div style="margin: 5px;"><input class="ws_form_element2" style="width: 150px;text-align: center;" placeholder="Code" id="insta_confirm_code" type="text"></div><div style="margin: 5px;"><button type="button" class="ws_btn ws_bg_danger" id="insta_confirm_button">Confirm</button></div></div>');

			$(cModalId[2] + " #insta_confirm_button").click(function()
			{
				var code = $(cModalId[2] + " #insta_confirm_code").val();

				if( code.trim() == '' )
				{
					fsCode.alert('Please enter the code!');
					return;
				}

				fsCode.ajax('confirm_two_factor' , {
					'username': username ,
					'password': password ,
					'proxy': proxy ,
					'code': code ,
					'two_factor_identifier': response['two_factor_identifier']
				}, function(result)
				{
					fsCode.modalHide($(cModalId[2]));

					require_action(result , username , password , proxy);
				});
			});
		}
		else
		{
			fsCode.toast("<?=esc_html__('Account added successfully!' , 'fs-poster')?>" , 'success');
			fsCode.modalHide($("#proModal<?=$mn?>"));
			$('#fs_account_supports .fs_social_network_div[data-setting="instagram"]').click();
		}
	}

	$("#proModal<?=$mn?> .add_account_btn").click(function()
	{
		var username    = $("#proModal<?=$mn?> .username").val(),
			password    = $("#proModal<?=$mn?> .password").val(),
			proxy       = $("#proModal<?=$mn?> .proxy").val();

		fsCode.ajax('add_instagram_account' , {'username': username , 'password': password , 'proxy': proxy}, function(response)
		{
			require_action(response , username , password , proxy);
		});
	});

</script>