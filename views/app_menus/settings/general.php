<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cronJobsCode = 'wget -O /dev/null ' . site_url() . '/?fs-poster-cron-job=1 >/dev/null 2>&1';
?>

<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>


<div class="setting_item" style="width: 50%;">
	<div class="setting_item_label">
		<div><?=esc_html__('Auto publish new posts:' , 'fs-poster')?></div>
		<div class="s_help"><?=__('If you enable this option, when you will add a new post, it will be published automatically by the plugin on all active social accounts.' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_auto_share_new_posts" class="onoffswitch-checkbox" id="fs_auto_share_new_posts"<?=get_option('fs_auto_share_new_posts', '1')?' checked':''?>>
			<label class="onoffswitch-label" for="fs_auto_share_new_posts"></label>
		</div>
	</div>
</div>

<div class="setting_item" style="width: 50%;">
	<div class="setting_item_label">
		<div><?=esc_html__('Use WP-Cron Jobs for background publications:' , 'fs-poster')?> <a href="https://youtu.be/CAuZAhBNwFQ" target="_blank" class="ws_color_info ws_tooltip" data-title="How to?"><i class="fab fa-youtube"></i></a></div>
		<div class="s_help"><?=__('If you do not want to use standart WP-Cron jobs you must to disable this option and add this code on your cPanel Cron Jobs panel with per minutes option:<br><code><small>'.$cronJobsCode.'</small></code>' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_use_wp_cron_jobs" class="onoffswitch-checkbox" id="fs_use_wp_cron_jobs"<?=get_option('fs_use_wp_cron_jobs', '1')?' checked':''?>>
			<label class="onoffswitch-label" for="fs_use_wp_cron_jobs"></label>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item" style="width: 50%;">
			<div class="setting_item_label">
				<div><?=esc_html__('Share on background:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('If you activated this option, share process will compleate on background and you do not must to wait share process.' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" onautocomplete="off" name="fs_share_on_background" class="onoffswitch-checkbox" id="fs_share_on_background"<?=get_option('fs_share_on_background', '1')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide_11').fadeIn(fadeSpeed); }else{ $('#hide_11').fadeOut(fadeSpeed); }">
					<label class="onoffswitch-label" for="fs_share_on_background"></label>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 50%;" id="hide_11">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Timer for publications (min.):' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('After creating how many minutes do you want the posts are sharing? If you want to share posts immediately after creating, enter: 0' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<input type="text" onautocomplete="off" name="fs_share_timer" class="ws_form_element" style="text-align: center; width: 50px;" value="<?=esc_html(get_option('fs_share_timer', '0'))?>">
			</div>
		</div>
	</div>
</div>

<div class="setting_item" style="width: 50%;">
	<div class="setting_item_label">
		<div><?=esc_html__('Show "FS Poster" column on post list table:' , 'fs-poster')?></div>
		<div class="s_help"><?=__('If you don\'t want to show FS Poster <i class="fa fa-question-circle" style="padding: 5px; cursor: pointer; color: #FF7784;" title="Click to learn more" data-open-img="'.plugin_dir_url(__FILE__).'../../../images/fs_poster_column_help.png"></i> column on posts table, you can disable this option.' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_show_fs_poster_column" class="onoffswitch-checkbox" id="fs_show_fs_poster_column"<?=get_option('fs_show_fs_poster_column', '1')?' checked':''?>>
			<label class="onoffswitch-label" for="fs_show_fs_poster_column"></label>
		</div>
	</div>
</div>

<div class="setting_item" style="width: 50%;">
	<div class="setting_item_label">
		<div><?=esc_html__('Unique post link:' , 'fs-poster')?></div>
		<div class="s_help"><?=esc_html__('You will have the ability to the share the same post in numerous amount of communities. In this case Facebook will block your duplicate post. If you activate this choice, the ending of every link of a post will include random symbols, therefore creating a unique post.' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_unique_link" class="onoffswitch-checkbox" id="fs_unique_link"<?=get_option('fs_unique_link', '1')?' checked':''?>>
			<label class="onoffswitch-label" for="fs_unique_link"></label>
		</div>
	</div>
</div>

<div style="height: 30px;"><hr></div>

<div class="setting_item" style="width: 50%;">
	<div class="setting_item_label">
		<div><?=esc_html__('URL shortener:' , 'fs-poster')?> <i class="fa fa-warning fa-exclamation-circle" style="padding-left: 6px; color: #ff7784;" title="Note: Pinterest and Reddit does not supports short links."></i></div>
		<div class="s_help"><?=esc_html__('If you activate this option you will auto short your post URLs.' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_url_shortener" class="onoffswitch-checkbox" id="fs_url_shortener"<?=get_option('fs_url_shortener', '0')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide_shortener').fadeIn(fadeSpeed); }else{ $('#hide_shortener').fadeOut(fadeSpeed); }">
			<label class="onoffswitch-label" for="fs_url_shortener"></label>
		</div>
	</div>
</div>

<div id="hide_shortener" style="display: none; width: 50%;">
	<div class="setting_item">
		<div class="setting_item_label">
			<div><?=esc_html__('URL shortener service:' , 'fs-poster')?></div>
			<div class="s_help"><?=esc_html__('Select which URL shortener service you wont to use your short URLs.' , 'fs-poster')?></div>
		</div>
		<div class="s_input">
			<select class="ws_form_element" id="fs_shortener_service" name="fs_shortener_service" style="width: 200px;" onautocomplete="off" onchange="if($(this).val()=='bitly'){$('#for_bitly').show();}else{$('#for_bitly').hide();}">
				<option value="tinyurl"<?=get_option('fs_shortener_service')=='tinyurl'?' selected':''?>>TinyURL</option>
				<option value="bitly"<?=get_option('fs_shortener_service')=='bitly'?' selected':''?>>Bitly</option>
			</select>
		</div>
	</div>
	<div class="setting_item" id="for_bitly" style="display: none;">
		<div class="setting_item_label">
			<div><?=esc_html__('Bitly Access token:' , 'fs-poster')?></div>
			<div class="s_help"><?=esc_html__('For getting Access token you mast register bitly. After registration you will get a new access token!' , 'fs-poster')?></div>
		</div>
		<div class="s_input">
			<input type="text" onautocomplete="off" name="fs_url_short_access_token_bitly" class="ws_form_element" style="width: 300px;" value="<?=esc_html(get_option('fs_url_short_access_token_bitly', '100'))?>">
		</div>
	</div>
</div>

<div style="height: 30px;"><hr></div>

<div style="width: 70%;">
	<div class="setting_item">
		<div class="setting_item_label">
			<div><?=esc_html__('Share post types:' , 'fs-poster')?></div>
			<div class="s_help"><?=esc_html__('Select which post types you want to share.' , 'fs-poster')?></div>
		</div>
		<div class="s_input" style="width: 300px;">
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
	<div class="setting_item">
		<div class="setting_item_label">
			<div><?=esc_html__('Hide FS Poster for:' , 'fs-poster')?></div>
			<div class="s_help"><?=esc_html__('If you want to hide FS Poster plugin for some user roles, then this options is for you. You can select multiple user roles for hiding FS Poster menu for them.' , 'fs-poster')?></div>
		</div>
		<div class="s_input" style="width: 300px;">
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

<div class="setting_item" style="width: 50%;">
	<div class="setting_item_label">
		<div><?=esc_html__('Keep shared posts log:' , 'fs-poster')?></div>
		<div class="s_help"><?=__('If you do not want to keep logs of shared posts you must disable this option. <span class="ws_color_warning">Note!</span> If you disable this option, you can not view your insights.' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_keep_logs" class="onoffswitch-checkbox" id="fs_keep_logs"<?=get_option('fs_keep_logs', '1')?' checked':''?>>
			<label class="onoffswitch-label" for="fs_keep_logs"></label>
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

		$("#fs_url_shortener").trigger('change');
		$("#fs_shortener_service").trigger('change');

		fadeSpeed = 200;

		$(".select2-init").select2();

		$("#fs_use_wp_cron_jobs").change(function()
		{
			if( !$(this).is(':checked') )
			{
				fsCode.alert('<div>Now you must to add this code on your cPanel Cron Jobs panel with per minutes option:</div><div style="margin-top: 25px;"><input class="ws_form_element2" type="text" readonly value="<?=htmlspecialchars($cronJobsCode)?>"></div>' , 'info')
			}
		});

		$("[data-open-img]").click(function ()
		{
			var imgSrc = $(this).data('open-img');

			fsCode.modal('<div><img src="'+imgSrc+'" style="width: 100%;"></div><div style="border-top: 1px solid #999; text-align: center; padding: 10px; margin-top: 9px;"><button type="button" data-modal-close="true" class="ws_btn ws_bg_danger">Close modal</button></div>' , {'width': '60%'});
		});
	});
</script>