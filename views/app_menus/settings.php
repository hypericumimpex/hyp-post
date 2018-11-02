<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settingTab = _get('setting' , 'general' , 'string' , ['general' , 'facebook' , 'instagram' , 'twitter', 'linkedin' , 'vk' , 'tumblr' , 'pinterest' , 'reddit' , 'google']);
?>

<style>
	.settings_title
	{
		margin-left: 65px;
		margin-top: 30px;
		margin-bottom: 10px;
		font-size: 20px;
		font-weight: 500;
		color: #555;
	}
	.settings_panel
	{
		margin: 0 50px;
		display: flex;
	}

	.settings_panel_r
	{
		flex-shrink: 1;
		width: 100%;
		background: #FFF;
		border: 1px solid #DDD;
		min-height: 400px;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		padding: 40px;
		position: relative;
	}

	.settings_menu
	{
		padding: 10px 15px;
		color: #777 !important;
		cursor: pointer;
		text-decoration: none;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		box-shadow: none !important;
	}
	.settings_menu>i
	{
		padding: 5px;
		font-size: 11px;
	}

	.settings_menu.active_menu
	{
		position: relative;
		background: #FFF;
		border-top: 1px solid #DDD;
		border-bottom: 1px solid #DDD;
		border-left: 3px solid #74B9FF;
		border-bottom-left-radius: 4px;
		border-top-left-radius: 4px;
	}
	.settings_menu.active_menu:after
	{
		content: '';
		position: absolute;
		right: -1px;
		width: 0px;
		top: 0px;
		height: 100%;
		border-left: 2px solid #FFF;
		z-index: 99;
	}
	.setting_item
	{
		display: flex;
		margin-bottom: 25px;
	}
	.setting_item>.s_input
	{
		width: 100px;
		display: flex;
		align-items: center;
	}
	.setting_item_label
	{
		font-size: 14px;
		color: #555;
		font-weight: 600;
		flex-shrink: 1;
		width: 100%;
	}
	.setting_item_label>.s_help
	{
		content: attr(data-label);
		left: 0;
		font-size: 11px;
		font-weight: 500;
		color: #999;
		margin-right: 25px;
	}
</style>
<style>
	.onoffswitch
	{
		position: relative;
		width: 55px;
		-webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
	}
	.onoffswitch-checkbox
	{
		display: none !important;
	}
	.onoffswitch-label
	{
		display: block;
		overflow: hidden;
		cursor: pointer;
		height: 20px;
		padding: 0;
		line-height: 20px;
		border: 0px solid #FFFFFF;
		border-radius: 30px;
		background-color: #9E9E9E;
		transition: background-color 0.3s ease-in;
	}
	.onoffswitch-label:before
	{
		content: "";
		display: block;
		width: 30px;
		margin: -5px;
		background: #FFFFFF;
		position: absolute;
		top: 0;
		bottom: 0;
		right: 31px;
		border-radius: 30px;
		box-shadow: 0 6px 12px 0px #757575;
		transition: all 0.3s ease-in 0s;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label
	{
		background-color: #74B9FF;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label, .onoffswitch-checkbox:checked + .onoffswitch-label:before
	{
		border-color: #74B9FF;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner
	{
		margin-left: 0;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label:before
	{
		right: 0px;
		background-color: #2196F3;
		box-shadow: 3px 6px 18px 0px rgba(0, 0, 0, 0.2);
	}
</style>
<style>
	.text_codes
	{
		display: flex;
		height: 20px;
	}
	.text_codes>:first-child
	{
		width: 220px;
	}
	.text_codes>:last-child
	{
		font-weight: 700;
		cursor: pointer;
	}
</style>

<div class="settings_title"><?=esc_html__('Settings' , 'fs-poster');?></div>

<form class="settings_form settings_panel">
	<div style="width: 220px; display: flex; flex-direction: column;">
		<div style="height: 30px;"></div>
		<a href="?page=fs-poster-settings&setting=general" class="settings_menu<?=$settingTab=='general'?' active_menu':''?>"><i class="fa fa-share-alt"></i> <?=esc_html__('General settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=facebook" class="settings_menu<?=$settingTab=='facebook'?' active_menu':''?>"><i class="fab fa-facebook-square"></i> <?=esc_html__('Facebook settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=instagram" class="settings_menu<?=$settingTab=='instagram'?' active_menu':''?>"><i class="fab fa-instagram"></i> <?=esc_html__('Instagram settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=twitter" class="settings_menu<?=$settingTab=='twitter'?' active_menu':''?>"><i class="fab fa-twitter"></i> <?=esc_html__('Twitter settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=linkedin" class="settings_menu<?=$settingTab=='linkedin'?' active_menu':''?>"><i class="fab fa-linkedin"></i> <?=esc_html__('Linkedin settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=vk" class="settings_menu<?=$settingTab=='vk'?' active_menu':''?>"><i class="fab fa-vk"></i> <?=esc_html__('VK settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=pinterest" class="settings_menu<?=$settingTab=='pinterest'?' active_menu':''?>"><i class="fab fa-pinterest"></i> <?=esc_html__('Pinterest settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=tumblr" class="settings_menu<?=$settingTab=='tumblr'?' active_menu':''?>"><i class="fab fa-tumblr"></i> <?=esc_html__('Tumblr settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=reddit" class="settings_menu<?=$settingTab=='reddit'?' active_menu':''?>"><i class="fab fa-reddit"></i> <?=esc_html__('Reddit settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=google" class="settings_menu<?=$settingTab=='google'?' active_menu':''?>"><i class="fab fa-google"></i> <?=esc_html__('Google settings' , 'fs-poster');?></a>
	</div>
	<div class="settings_panel_r">
		<?php
		require_once VIEWS_DIR . 'app_menus/settings/' . $settingTab . '.php';
		?>

		<div style="position: absolute; bottom: 10px; width: calc(100% - 75px); text-align: center;">
			<button id="save_btn" class="ws_btn ws_bg_success" style="width: 100%;" type="button"><i class="fa fa-save"></i> <?=esc_html__('SAVE CHANGES' , 'fs-poster');?></button>
		</div>

	</div>
</form>

<script>
	jQuery(document).ready(function()
	{
		$('.append_to_text').click(function()
		{
			$("#custom_text_area").val($("#custom_text_area").val() + $(this).text().trim());
		});
	});
</script>

