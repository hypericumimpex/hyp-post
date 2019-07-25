<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checkRequirements = FScheckRequirments( false );
if( !$checkRequirements[0] )
{
	print '<div class="ws_bg_danger"  style="margin: 20px 50px; padding: 10px;">' . esc_html__('"allow_url_fopen" disabled in your php.ini settings! Please activate it and try again!' , 'fs-poster') . '</div>';
}

?>

<div class="ws_bg_info" style="margin-left: -20px; font-size: 15px; margin-bottom: 50px; font-weight: 600; padding: 10px 20px; -webkit-border-radius: 2px;-moz-border-radius: 2px;border-radius: 2px;">Please activate the plugin!</div>

<div style="display: flex; flex-direction: column; align-items: center; width: 100%;">

	<div style="margin: 50px;"><img src="<?=FS_PLUGIN_URL?>images/logo.svg" style="width: 400px;"></div>

	<div style="font-size: 14px; color: #9d928a;">Please help us to collect statistics for marketing reason</div>

	<div style="font-weight: 600; font-size: 14px; color: #777; text-align: center; margin-top: 15px;"><?=__('Where did You find us?' , 'fs-poster')?></div>

	<div style="width: 350px; margin-top: 5px;">
		<select class="ws_form_element" id="marketing_statistic">
			<?php
			print FSstatisticOption();
			?>
		</select>
	</div>

	<div style="font-weight: 600; font-size: 14px; color: #777; text-align: center; margin-top: 15px;"><?=__('Purchase key' , 'fs-poster')?>:</div>

	<div style="width: 350px; margin-top: 5px;"><input type="text" class="ws_form_element2" id="purchaseKey" placeholder="<?=__('Purchase key' , 'fs-poster')?>"></div>

	<div style="margin-top: 10px;"><button type="button" class="ws_btn ws_bg_danger" id="activateBtn" style="width: 350px;"><?=__('ACTIVATE' , 'fs-poster')?></button></div>
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

				var marketing_statistic = $("#marketing_statistic").val();

				if( !marketing_statistic )
				{
					$("#marketing_statistic").attr('style', 'border-color: #ffb1b1 !important;');
					return;
				}

				fsCode.ajax('activate_app' , {'code': purchaseKey, statistic: marketing_statistic} , function ()
				{
					fsCode.toast("<?=esc_attr__('Plugin installed!' , 'fs-poster')?>" , 'success');
					fsCode.loading(1);
					location.reload();
				});
			});

		});

	})();
</script>