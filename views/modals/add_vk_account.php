<?php defined('MODAL') or exit();?>

<?php
$application = wpFetchAll('apps' , ['driver' => 'vk']);
?>

<span class="close" data-modal-close="true">&times;</span>
<style>
	.ws_steps > div
	{
		position: relative;
		padding: 10px 40px 10px 10px;
	}
	.ws_steps > div:before
	{
		content: '';
		position: absolute;
		left: -25px;
		top: 0;
		height: 100%;
		border-left: 4px solid #afc7d0;
	}
	.ws_steps > div:after
	{
		content: attr(data-step);
		position: absolute;
		left: -35px;
		height: 24px;
		width: 24px;
		-webkit-border-radius: 15px;
		-moz-border-radius: 15px;
		border-radius: 15px;
		background: #74b9ff;
		color: #FFF;
		font-weight: 700;
		font-size: 14px;
		top: 0;
		bottom: 0;
		margin: auto;
		display: flex;
		align-items: center;
		justify-items: center;
		justify-content: center;
		align-content: center;
	}

	.ws_steps > div:first-child:before
	{
		top: 50% !important;
		height: 50% !important;
	}

	.ws_steps > div:last-child:before
	{
		height: 50% !important;
	}
	.vk_logo
	{
		width: 89%;
		display: flex;
		justify-content: center;
		height: 140px;
		background-size: 100% auto;
		background-position: center;
	}
	#proxy_show
	{
		margin-bottom: 20px;
		margin-right: 70px;
		display: flex;
		align-items: center;
		margin-left: 69px;
	}
</style>
<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 20px; margin-bottom: 20px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
	<div class="vk_logo" style="background-image: url('<?=plugin_dir_url(__FILE__).'../../images/vk2.png'?>');"></div>
	<div>
		<div class="ws_steps" data-for="firefox" style="margin-left: 60px; margin-top: 25px;">
			<div data-step="1">
				<?=esc_html__('Select an App:' , 'fs-poster')?>
				<select class="ws_form_element appSelect" style="width: 200px;">
					<?php
					foreach($application AS $appInf)
					{
						print '<option value="'.esc_html($appInf['app_id']).'">'.esc_html($appInf['name']).'</option>';
					}
					?>
				</select>
			</div>
			<div data-step="2"><button type="button" class="ws_btn ws_bg_info getAccessBtn"><i class="fab fa-facebook-f"></i> <?=esc_html__('GET ACCESS' , 'fs-poster')?></button></div>
			<div data-step="3" style="padding-top: 20px;font-size: 17px;font-weight: 500;color: #636e72;padding-bottom: 20px;"><?=esc_html__('When the authorize operation has finished, copy the URL' , 'fs-poster')?></div>
			<div data-step="4">
				<textarea class="ws_form_element2 access_token_txt" placeholder="<?=esc_html__('Paste copied URL here...' , 'fs-poster')?>"></textarea>
			</div>

		</div>

		<div style="margin-left: 69px; margin-bottom: 20px;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show').slideDown(200); });">
			<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
		</div>

		<div style="display: none;" id="proxy_show">
			<div style="position: relative;">
				<input type="text" placeholder="Proxy" class="ws_form_element2" id="proxyInput" style="padding-left: 30px">
				<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
			</div>
			<div style="width: 30px; text-align: right; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
		</div>

		<div style="text-align: center; margin-top: 20px;">
			<button class="ws_btn ws_bg_danger addAccountBtn" type="button"><?=esc_html__('ADD ACCOUNT' , 'fs-poster')?></button>
		</div>
	</div>
</div>

<script>

	$("#proModal<?=$mn?> .getAccessBtn").click(function()
	{
		var allId = $('#proModal<?=$mn?> .appSelect').val();
		if( allId.trim() == '' )
		{
			fsCode.toast("<?=esc_html__('Please select an application!' , 'fs-poster')?>" , 'danger');
			return;
		}

		window.open('https://oauth.vk.com/authorize?client_id='+allId+'&redirect_uri=https://oauth.vk.com/blank.html&display=page&scope=offline,wall,groups,email,photos,video&response_type=token&v=5.69' , '' , 'width=600,height=200');
	});

	$("#proModal<?=$mn?> .addAccountBtn").click(function()
	{
		var accessToken = $("#proModal<?=$mn?> .access_token_txt").val().trim(),
			allId = $('#proModal<?=$mn?> .appSelect').val(),
			proxy = $("#proModal<?=$mn?> #proxyInput").val();

		if( accessToken.trim() == '' )
		{
			fsCode.toast("<?=esc_html__('Access token is empty!' , 'fs-poster')?>" , 'danger');
			return;
		}

		fsCode.ajax('add_vk_account' , {'at': accessToken, 'app': allId , 'proxy': proxy}, function()
		{
			fsCode.toast("<?=esc_html__('Account added successfully!' , 'fs-poster')?>" , 'success');
			fsCode.modalHide($("#proModal<?=$mn?>"));
			$('#account_supports .social_network_div[data-setting="vk"]').click();
		});
	});



</script>