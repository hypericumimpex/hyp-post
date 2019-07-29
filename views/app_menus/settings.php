<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settingTab = FS_get('setting' , 'share' , 'string' , ['general' , 'share', 'url' , 'facebook' , 'instagram' , 'twitter', 'linkedin' , 'vk' , 'tumblr' , 'pinterest' , 'reddit' , 'ok' , 'google_b', 'telegram', 'medium']);
?>

<div class="fs_settings_title"><?=esc_html__('Settings' , 'fs-poster');?></div>

<form class="settings_form fs_settings_panel">
	<div style="width: 220px; display: flex; flex-direction: column;">
		<div style="height: 30px;"></div>
		<a href="?page=fs-poster-settings&setting=share" class="fs_settings_menu<?=$settingTab=='share'?' active_menu':''?>"><i class="fa fa-share-alt"></i> <?=esc_html__('Publish settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=general" class="fs_settings_menu<?=$settingTab=='general'?' active_menu':''?>"><i class="fa fa-cog"></i> <?=esc_html__('General settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=url" class="fs_settings_menu<?=$settingTab=='url'?' active_menu':''?>"><i class="fa fa-link"></i> <?=esc_html__('URL settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=facebook" class="fs_settings_menu<?=$settingTab=='facebook'?' active_menu':''?>"><i class="fab fa-facebook-square"></i> <?=esc_html__('Facebook settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=instagram" class="fs_settings_menu<?=$settingTab=='instagram'?' active_menu':''?>"><i class="fab fa-instagram"></i> <?=esc_html__('Instagram settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=twitter" class="fs_settings_menu<?=$settingTab=='twitter'?' active_menu':''?>"><i class="fab fa-twitter"></i> <?=esc_html__('Twitter settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=linkedin" class="fs_settings_menu<?=$settingTab=='linkedin'?' active_menu':''?>"><i class="fab fa-linkedin"></i> <?=esc_html__('Linkedin settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=vk" class="fs_settings_menu<?=$settingTab=='vk'?' active_menu':''?>"><i class="fab fa-vk"></i> <?=esc_html__('VK settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=pinterest" class="fs_settings_menu<?=$settingTab=='pinterest'?' active_menu':''?>"><i class="fab fa-pinterest"></i> <?=esc_html__('Pinterest settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=tumblr" class="fs_settings_menu<?=$settingTab=='tumblr'?' active_menu':''?>"><i class="fab fa-tumblr"></i> <?=esc_html__('Tumblr settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=reddit" class="fs_settings_menu<?=$settingTab=='reddit'?' active_menu':''?>"><i class="fab fa-reddit"></i> <?=esc_html__('Reddit settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=ok" class="fs_settings_menu<?=$settingTab=='ok'?' active_menu':''?>"><i class="fab fa-odnoklassniki"></i> <?=esc_html__('OK settings' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=google_b" class="fs_settings_menu<?=$settingTab=='google_b'?' active_menu':''?>"><i class="fab fa-google"></i> <?=esc_html__('Google MyBusiness' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=telegram" class="fs_settings_menu<?=$settingTab=='telegram'?' active_menu':''?>"><i class="fab fa-telegram"></i> <?=esc_html__('Telegram' , 'fs-poster');?></a>
		<a href="?page=fs-poster-settings&setting=medium" class="fs_settings_menu<?=$settingTab=='medium'?' active_menu':''?>"><i class="fab fa-medium"></i> <?=esc_html__('Medium' , 'fs-poster');?></a>
	</div>
	<div class="fs_settings_panel_r">
		<?php
		require_once FS_VIEWS_DIR . 'app_menus/settings/' . $settingTab . '.php';
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

		$(".fs_image_buttons1 > div").click(function()
		{
			$(".fs_image_buttons1 > div.selected_btn").removeClass('selected_btn');
			$(this).addClass('selected_btn');

			$(".fs_image_buttons1").prev('input').val($(this).data('id'));
		});
	});
</script>

