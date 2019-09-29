<?php defined('MODAL') or exit();?>

<?php
$applications = FSwpFetchAll('apps' , ['driver' => 'medium']);
?>
<style>
	.medium_logo > img
	{
		width: 60%;
		height: 60% !important;
		height: 180px;
		margin: 20px;
	}
	.medium_logo
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

<div style="width: 100%; margin-top: 47px; display: flex; justify-content: center; align-items: center;">
	<div class="medium_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/medium.png'?>"></div>
	<div style="width: 45%;">

		<div style="margin-bottom: 25px; font-size: 15px; font-weight: 500; color: #969696;margin-right: 30px; border-left: 3px solid #DDD; padding-left: 10px;"> <?=esc_html__('Select an app. Next click the "GET ACCESS" button. Next, click the "Authorize App" button within an open window.' , 'fs-poster')?></div>

		<div style="width: 200px; margin-bottom: 10px; ">
			<select class="ws_form_element" id="appIdSelect">
				<?php
				foreach ($applications AS $appInf)
				{
					print '<option value="'.(int)$appInf['id'].'" data-standart="'.(int)$appInf['is_standart'].'">'.esc_html($appInf['name']).'</option>';
				}
				?>
			</select>
		</div>

		<div style="margin-bottom: 20px;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show').slideDown(200); });">
			<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
		</div>

		<div style="display: none;" id="proxy_show">
			<div style="position: relative;">
				<input type="text" placeholder="Proxy" class="ws_form_element2" id="proxyInput" style="padding-left: 30px">
				<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
			</div>
			<div style="width: 30px; text-align: right; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
		</div>

		<div>
			<button type='button' id="addAccountBTN" class="ws_btn ws_bg_danger"><?=esc_html__('GET ACCESS' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('CANCEL' , 'fs-poster')?></button>
		</div>

	</div>
</div>

<script>

	function compleateOperation( status, errorMsg )
	{
		if( status )
		{
			fsCode.toast("<?=esc_html__('Account added successfully!' , 'fs-poster')?>" , 'success');
			fsCode.modalHide($("#proModal<?=$mn?>"));
			$('#fs_account_supports .fs_social_network_div[data-setting="medium"]').click();
		}
		else
		{
			fsCode.toast(errorMsg , 'danger', 10000);
		}
	}

	$("#addAccountBTN").click(function ()
	{
		var openURL = "<?=site_url().'/?medium_app_redirect='?>" + $("#appIdSelect").val() + '&proxy=' + $("#proxyInput").val();
		if( $("#appIdSelect>:selected").attr('data-standart') == '1' )
		{
			openURL = "<?=standartFSAppRedirectURL('medium')?>&proxy=" + $("#proxyInput").val();
		}

		window.open(openURL , 'fs-standart-app', 'width=750,height=550');
	});

</script>