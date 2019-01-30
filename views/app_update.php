<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>


<div class="ws_bg_info" style="margin-left: -20px; font-size: 15px; margin-bottom: 50px; font-weight: 600; padding: 10px 20px; -webkit-border-radius: 2px;-moz-border-radius: 2px;border-radius: 2px;">Please update the plugin! New version: <?=esc_html(getVersion())?></div>

<div style="display: flex; flex-direction: column; align-items: center; width: 100%;">

	<div style="margin: 50px;"><img src="<?=PLUGIN_URL?>images/logo.svg" style="width: 400px;"></div>

	<div style="display: flex; align-items: center;">
		<div style="font-weight: 600; font-size: 14px; color: #777;"><?=__('Purchase key' , 'fs-poster')?>:</div>
		<div style=" margin-left: 15px; width: 350px;"><input type="text" class="ws_form_element2" id="purchaseKey" placeholder="<?=__('Purchase key' , 'fs-poster')?>..."></div>
		<div style="margin-left: 10px;"><button type="button" class="ws_btn ws_bg_danger" id="activateBtn"><?=__('UPDATE AND ACTIVATE' , 'fs-poster')?></button></div>
	</div>
</div>


<script>
	(function()
	{
		jQuery(document).ready(function()
		{
			$("body").on('click' , '#activateBtn' , function()
			{
				var purchaseKey = $("#purchaseKey").val().trim();

				if( purchaseKey == '' )
				{
					fsCode.toast("<?=esc_attr__('Please enter the purcache code!' , 'fs-poster')?>" , 'danger');
					return;
				}

				fsCode.ajax('update_app' , {'code': purchaseKey} , function ()
				{
					fsCode.toast("<?=esc_attr__('Plugin installed!' , 'fs-poster')?>" , 'success');
					fsCode.loading(1);
					location.reload();
				});
			});

		});

	})();
</script>