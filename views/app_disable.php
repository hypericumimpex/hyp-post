<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="ws_bg_info" style="margin-left: -20px; font-size: 15px; margin-bottom: 50px; font-weight: 600; padding: 10px 20px; -webkit-border-radius: 2px;-moz-border-radius: 2px;border-radius: 2px;">Please activate the plugin!</div>

<div style="display: flex; flex-direction: column; align-items: center; width: 100%;">

	<div style="margin: 50px;"><img src="<?=PLUGIN_URL?>images/logo.svg" style="width: 400px;"></div>

	<div style="background: #FFF; padding: 20px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px; margin-right: 30px; -webkit-box-shadow: 0 0 20px 0 #DDD;-moz-box-shadow: 0 0 20px 0 #DDD;box-shadow: 0 0 20px 0 #DDD;">
		<div style="font-weight: 600; font-size: 18px; color: #fb7171;">Your plugin disabled!</div>
		<div style="margin-top: 20px; font-size: 15px; color: #777;">Reason: <?=get_option('fs_plugin_alert')?></div>
	</div>
</div>

