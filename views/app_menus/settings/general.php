<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cronJobsCode = 'wget -O /dev/null ' . site_url() . '/?fs-poster-cron-job=1 >/dev/null 2>&1';
?>

<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>


<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('Show "FS Poster" column on post list table:' , 'fs-poster')?></div>
		<div class="fs_s_help"><?=__('If you don\'t want to show FS Poster <i class="fa fa-question-circle" style="padding: 5px; cursor: pointer; color: #FF7784;" title="Click to learn more" data-open-img="'.plugin_dir_url(__FILE__).'../../../images/fs_poster_column_help.png"></i> column on posts table, you can disable this option.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_show_fs_poster_column" class="fs_onoffswitch-checkbox" id="fs_show_fs_poster_column"<?=get_option('fs_show_fs_poster_column', '1')?' checked':''?>>
			<label class="fs_onoffswitch-label" for="fs_show_fs_poster_column"></label>
		</div>
	</div>
</div>

<div style="width: 70%;">
	<div class="fs_setting_item">
		<div class="fs_setting_item_label">
			<div><?=esc_html__('Custom post types:' , 'fs-poster')?></div>
			<div class="fs_s_help"><?=esc_html__('Select which post types you want to share.' , 'fs-poster')?></div>
		</div>
		<div class="fs_s_input" style="width: 300px;">
			<select class="ws_form_element select2-init" id="fs_allowed_post_types" name="fs_allowed_post_types[]" style="width: 300px;" multiple onautocomplete="off">
				<?php
				$selecttedTypes = explode( '|' , get_option('fs_allowed_post_types' , 'post|page|attachment|product') );
				foreach( get_post_types() AS $post_type )
				{
					print '<option value="' . htmlspecialchars($post_type) . '"' . (in_array( $post_type , $selecttedTypes ) ? ' selected' : '') . '>' . htmlspecialchars($post_type) . '</option>';
				}
				?>
			</select>
		</div>
	</div>
</div>

<div style="width: 70%;">
	<div class="fs_setting_item">
		<div class="fs_setting_item_label">
			<div><?=esc_html__('Hide FS Poster for:' , 'fs-poster')?></div>
			<div class="fs_s_help"><?=esc_html__('If you want to hide FS Poster plugin for some user roles, then this options is for you. You can select multiple user roles for hiding FS Poster menu for them.' , 'fs-poster')?></div>
		</div>
		<div class="fs_s_input" style="width: 300px;">
			<select class="ws_form_element select2-init" id="fs_hide_for_roles" name="fs_hide_for_roles[]" style="width: 300px;" multiple onautocomplete="off">
				<?php
				$hideForRoles = explode('|' , get_option('fs_hide_menu_for' , ''));
				$wp_roles = get_editable_roles();
				foreach( $wp_roles AS $roleId => $roleInf )
				{
					if( $roleId == 'administrator' )
						continue;

					print '<option value="' . htmlspecialchars($roleId) . '"' . (in_array( $roleId , $hideForRoles ) ? ' selected' : '') . '>' . htmlspecialchars($roleInf['name']) . '</option>';
				}
				?>
			</select>
		</div>
	</div>
</div>

<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('Collect FS Poster statistics:' , 'fs-poster')?></div>
		<div class="fs_s_help"><?=esc_html__('If you disable this option you can not get more statistics in the Insights menu. If you enable this option, "feed_id" parameter will append automatically every you shared post link.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_collect_statistics" class="fs_onoffswitch-checkbox" id="fs_collect_statistics"<?=get_option('fs_collect_statistics', '1')?' checked':''?>>
			<label class="fs_onoffswitch-label" for="fs_collect_statistics"></label>
		</div>
	</div>
</div>

<script>
	var fadeSpeed = 0;
	jQuery(document).ready(function()
	{
		$("#save_btn").click(function()
		{
			var data = fsCode.serialize($(".settings_form"));

			fsCode.ajax('settings_general_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster')?>" , 'success');
			});
		});

		fadeSpeed = 200;

		$(".select2-init").select2();

		$("[data-open-img]").click(function ()
		{
			var imgSrc = $(this).data('open-img');

			fsCode.modal('<div><img src="'+imgSrc+'" style="width: 100%;"></div><div style="border-top: 1px solid #999; text-align: center; padding: 10px; margin-top: 9px;"><button type="button" data-modal-close="true" class="ws_btn ws_bg_danger">Close modal</button></div>' , {'width': '60%'});
		});
	});
</script>