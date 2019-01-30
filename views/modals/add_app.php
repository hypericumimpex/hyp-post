<?php defined('MODAL') or exit();?>
<?php
$fields = _post('fields' , '' , 'string');
$fields = explode(',' , $fields);
$driver = _post('driver' , '' , 'string');
?>

<div style="width: 100%; height: 100%; display: flex; align-items: center; flex-direction: column; justify-content: center; position: absolute;">
	<div style="margin-bottom: 20px; text-align: center;font-size: 17px;color: #888;font-weight: 600;">
		Type <b style="font-weight: 700;">app id</b> and <b style="font-weight: 700;">app secret</b>
	</div>
	<div style="<?=in_array('app_id' , $fields) ? '' : 'display: none; '?>margin: 15px; width: 250px;"><input type="text" class="ws_form_element app_id" placeholder="App ID"></div>
	<div style="<?=in_array('app_key' , $fields) ? '' : 'display: none; '?>margin: 15px; width: 250px;"><input type="text" class="ws_form_element app_key" placeholder="App Key"></div>
	<div style="<?=in_array('app_secret' , $fields) ? '' : 'display: none; '?>margin: 15px; width: 250px;"><input type="text" class="ws_form_element app_secret" placeholder="App Secret"></div>
	<div style="margin: 15px; width: 250px; text-align: center;"><button type="button" class="ws_btn ws_bg_danger add_btn" style="width: 130px;"><?=esc_html__('ADD APP', 'fs-poster')?></button> </div>
</div>

<span class="close" data-modal-close="true">&times;</span>

<script>

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .add_btn").click(function()
		{
			var appId = $("#proModal<?=$mn?> .app_id").val(),
				appKey = $("#proModal<?=$mn?> .app_key").val(),
				appSecret = $("#proModal<?=$mn?> .app_secret").val();

			fsCode.ajax('add_new_app' , {'app_id': appId , 'app_key': appKey , 'app_secret': appSecret, 'driver': "<?=esc_html($driver);?>"} , function(result)
			{
				fsCode.toast("<?=esc_html__('App has been added successfully!', 'fs-poster')?>" , 'success');
				fsCode.loading(1);
				location.reload();
			});
		});
	});


</script>