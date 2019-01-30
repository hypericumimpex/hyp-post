<?php defined('MODAL') or exit();?>

<style>
	.google_logo > img
	{
		width: 60%;
		height: 180px;
		margin: 20px;
	}
	.google_logo
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
</style>
<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 60px; display: flex; justify-content: center; align-items: center;">
	<div class="google_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/google.png'?>"></div>
	<div style="width: 45%;">

		<div style="margin-bottom: 20px; font-size: 15px; font-weight: 600; color: #888;"><?=esc_html__('Add new google account' , 'fs-poster')?></div>

		<div style="position: relative; margin-bottom: 20px; margin-right: 70px;">
			<input type="text" placeholder="Email" class="ws_form_element username" style="padding-left: 30px">
			<i class="fa fa-user" style="position: absolute; left: 10px; color: #AAA; top: 10px;"></i>
		</div>
		<div style="position: relative; margin-bottom: 20px; margin-right: 70px;">
			<input type="password" placeholder="Password" class="ws_form_element password" style="padding-left: 30px">
			<i class="fa fa-key" style="position: absolute; left: 10px; color: #AAA; top: 10px;"></i>
		</div>

		<div id="capcha_area" style="display: none;">
			<div>
				<img src="" id="google_capcha">
			</div>
			<div style="position: relative; margin-bottom: 20px; margin-right: 70px;">
				<input type="text" placeholder="Capcha" class="ws_form_element capcha" style="padding-left: 30px">
				<i class="fa fa-keyboard" style="position: absolute; left: 10px; color: #AAA; top: 10px;"></i>
			</div>
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

		<div style="margin-bottom: 30px;">
			<button type="button" class="ws_btn ws_bg_danger add_account_btn"><?=esc_html__('ADD ACCOUNT' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('CANCEL' , 'fs-poster')?></button>
		</div>
	</div>
</div>

<script>

	function require_action(response , username , password , proxy)
	{
		if( 'do' in response && response['do'] == 'capcha')
		{
			fsCode.toast(response['error_msg'] , 'warning', 5000);
			$("#capcha_area").slideDown(400);
			$("#google_capcha").attr('src' , response['capcha']);
			$("#proModal<?=$mn?> .capcha").val('');
		}
		else if( 'do' in response && response['do'] == 'challenge_1st_step' )
		{
			var cModalId = fsCode.modal('<div style="display: flex; height: 400px;align-items: center;justify-content: center;flex-direction: column;"><div style="margin: 5px;font-size: 15px;color: #777; text-align: center;">' + fsCode.htmlspecialchars(response['message']) + '</div><div style="margin: 5px;"><input class="ws_form_element2" style="width: 150px;text-align: center;" placeholder="Code" id="google_confirm_code" type="text"></div><div style="margin: 5px;"><button type="button" class="ws_btn ws_bg_danger" id="google_confirm_button">Confirm</button></div></div>');

			$(cModalId[2] + " #google_confirm_button").click(function()
			{
				var code = $(cModalId[2] + " #google_confirm_code").val();

				if( code.trim() == '' )
				{
					fsCode.alert('Input is empty!');
					return;
				}

				fsCode.ajax('google_confirm_challenge_1st_step' , {
					'username': username ,
					'password': password ,
					'proxy': proxy ,
					'code': code
				}, function(result)
				{
					fsCode.modalHide($(cModalId[2]));

					require_action(result , username , password , proxy);
				});
			});

			$("#capcha_area").hide();
		}
		else if( 'do' in response && response['do'] == 'challenge' )
		{
			var cModalId = fsCode.modal('<div style="display: flex; height: 400px;align-items: center;justify-content: center;flex-direction: column;"><div style="margin: 5px;font-size: 15px;color: #777; text-align: center;">' + fsCode.htmlspecialchars(response['message']) + '<br></div><div style="margin: 5px; position: relative;"><input class="ws_form_element2" style="width: 200px;text-align: center;" id="google_confirm_code" type="text"></div><div style="margin: 5px;"><button type="button" class="ws_btn ws_bg_danger" id="google_confirm_button">Confirm</button></div></div>');

			$(cModalId[2] + " #google_confirm_button").click(function()
			{
				var code = $(cModalId[2] + " #google_confirm_code").val();

				//if( code.trim() == '' )
				//{
				//	fsCode.alert('Please enter the code!');
				//	return;
				//}

				fsCode.ajax('google_confirm_challenge_1st_step' , {
					'username': username ,
					'password': password ,
					'proxy': proxy ,
					'code': code
				}, function(result)
				{
					fsCode.modalHide($(cModalId[2]));

					require_action(result , username , password , proxy);
				});
			});

			$("#capcha_area").hide();
		}
		else if( 'do' in response && response['do'] == 'two_factor' )
		{
			var cModalId = fsCode.modal('<div style="display: flex; height: 400px;align-items: center;justify-content: center;flex-direction: column;"><div style="margin: 5px;font-size: 15px;color: #777; text-align: center;">' + fsCode.htmlspecialchars(response['message']) + '<br> Please copy this code here:</div><div style="margin: 5px; position: relative;"><span style="position: absolute; top: 9px; left: 10px; font-size: 15px; font-weight: 600;color: #888;">G-</span><input class="ws_form_element2" style="width: 150px;text-align: center;" placeholder="Code" id="google_confirm_code" type="text"></div><div style="margin: 5px;"><button type="button" class="ws_btn ws_bg_danger" id="google_confirm_button">Confirm</button></div></div>');

			$(cModalId[2] + " #google_confirm_button").click(function()
			{
				var code = $(cModalId[2] + " #google_confirm_code").val();

				if( code.trim() == '' )
				{
					fsCode.alert('Please enter the code!');
					return;
				}

				fsCode.ajax('google_confirm_two_factor' , {
					'username': username ,
					'password': password ,
					'proxy': proxy ,
					'code': code
				}, function(result)
				{
					fsCode.modalHide($(cModalId[2]));

					require_action(result , username , password , proxy);
				});
			});

			$("#capcha_area").hide();
		}
		else
		{
			fsCode.toast("<?=esc_html__('Account added successfully!' , 'fs-poster')?>" , 'success');
			fsCode.modalHide($("#proModal<?=$mn?>"));
			$('#account_supports .social_network_div[data-setting="google"]').click();
		}
	}

	$("#proModal<?=$mn?> .add_account_btn").click(function()
	{
		var username    = $("#proModal<?=$mn?> .username").val(),
			password    = $("#proModal<?=$mn?> .password").val(),
			proxy       = $("#proModal<?=$mn?> .proxy").val(),
			capcha		= $("#capcha_area").css('display') == 'none' ? '' : $("#proModal<?=$mn?> .capcha").val();

		fsCode.ajax('add_google_account' , {'username': username , 'password': password , 'proxy': proxy, 'capcha': capcha}, function(response)
		{
			require_action(response , username , password , proxy);
		});
	});

</script>