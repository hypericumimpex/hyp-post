<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<style>
	#wpcontent
	{
		padding-left: 0 !important;
	}
</style>

<div>
	<div style="position: relative; height: 100px; background-color: #289BE5; width: 100%; text-align: center; color: #FFF; display: flex; align-items: center; justify-content: center;">

		<a href="?page=fs-poster" class="ws_setting_menu<?=$menuKey=='account'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-user"></i></div>
			<div class="ws_menu_title"><?=__('ACCOUNTS' , 'fs-poster')?></div>
		</a>

		<a href="?page=fs-poster-nodes" class="ws_setting_menu<?=$menuKey=='nodes'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-file"></i></div>
			<div class="ws_menu_title"><?=__('COMMUNITIES' , 'fs-poster')?></div>
		</a>

		<a href="?page=fs-poster-schedule" class="ws_setting_menu<?=$menuKey=='schedule'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-clock"></i></div>
			<div class="ws_menu_title"><?=__('SCHEDULE' , 'fs-poster')?></div>
		</a>

		<a href="?page=fs-poster-share" class="ws_setting_menu<?=$menuKey=='share'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-edit fa-pencil-square-o"></i></div>
			<div class="ws_menu_title"><?=__('SHARE' , 'fs-poster')?></div>
		</a>

		<a href="?page=fs-poster-posts" class="ws_setting_menu<?=$menuKey=='posts'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-bullhorn "></i></div>
			<div class="ws_menu_title"><?=__('POSTS' , 'fs-poster')?></div>
		</a>

		<a href="?page=fs-poster-insights" class="ws_setting_menu<?=$menuKey=='insights'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-chart-bar"></i></div>
			<div class="ws_menu_title"><?=__('INSIGHTS' , 'fs-poster')?></div>
		</a>

		<a href="?page=fs-poster-app" class="ws_setting_menu<?=$menuKey=='app'?' active_menu':''?>">
			<div class="ws_menu_image"><i class="fa fa-rocket"></i></div>
			<div class="ws_menu_title"><?=__('APPS' , 'fs-poster')?></div>
		</a>
		<?php
		if( current_user_can('administrator') )
		{
			?>
			<a href="?page=fs-poster-settings" class="ws_setting_menu<?=$menuKey=='settings'?' active_menu':''?>">
				<div class="ws_menu_image"><i class="fa fa-cogs"></i></div>
				<div class="ws_menu_title"><?=__('SETTINGS' , 'fs-poster')?></div>
			</a>
			<?php
		}
		?>
	</div>
	<div>
		<?php require_once VIEWS_DIR . "app_menus/" . $menuKey . ".php";?>
	</div>
</div>

<script>
	(function()
	{
		jQuery(document).ready(function()
		{
			$("body").on('click' , '.delete_fb_account_btn' , function()
			{
				$("#deleteConfirmationModal").fadeIn(200);
			});

		});
	})();
</script>