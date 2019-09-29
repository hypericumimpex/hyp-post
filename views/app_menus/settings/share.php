<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cronJobsCode = 'wget -O /dev/null ' . site_url() . '/?fs-poster-cron-job=1 >/dev/null 2>&1';
?>

<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('Auto publish new posts:' , 'fs-poster')?></div>
		<div class="fs_s_help"><?=__('If you enable this option, when you will add a new post, it will be published automatically by the plugin on all active social accounts.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_auto_share_new_posts" class="fs_onoffswitch-checkbox" id="fs_auto_share_new_posts"<?=get_option('fs_auto_share_new_posts', '1')?' checked':''?>>
			<label class="fs_onoffswitch-label" for="fs_auto_share_new_posts"></label>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Share on background:' , 'fs-poster')?></div>
				<div class="fs_s_help"><?=esc_html__('If you activated this option, share process will compleate on background and you do not have to wait share process.' , 'fs-poster')?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" onautocomplete="off" name="fs_share_on_background" class="fs_onoffswitch-checkbox" id="fs_share_on_background"<?=get_option('fs_share_on_background', '1')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide_11').fadeIn(fadeSpeed); }else{ $('#hide_11').fadeOut(fadeSpeed); }">
					<label class="fs_onoffswitch-label" for="fs_share_on_background"></label>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 50%;" id="hide_11">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Timer for publications (min.):' , 'fs-poster')?></div>
				<div class="fs_s_help"><?=esc_html__('After creating how many minutes do you want the posts are sharing? If you want to share posts immediately after creating, enter: 0' , 'fs-poster')?></div>
			</div>
			<div class="fs_s_input">
				<input type="text" onautocomplete="off" name="fs_share_timer" class="ws_form_element" style="text-align: center; width: 50px;" value="<?=esc_html(get_option('fs_share_timer', '0'))?>">
			</div>
		</div>
	</div>
</div>

<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('Keep shared posts log:' , 'fs-poster')?></div>
		<div class="fs_s_help"><?=__('If you do not want to keep logs of shared posts you must disable this option. <span class="ws_color_warning">Note!</span> If you disable this option, you can not view your insights.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_keep_logs" class="fs_onoffswitch-checkbox" id="fs_keep_logs"<?=get_option('fs_keep_logs', '1')?' checked':''?>>
			<label class="fs_onoffswitch-label" for="fs_keep_logs"></label>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Post interval:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('With this option you can define interval per social account\'s publications and limit posting speed.' , 'fs-poster');?></div>
			</div>
			<div style="margin-right: 25px;">
				<select name="fs_post_interval" id="fs_post_interval" class="ws_form_element" style="width: 150px;">
					<option value="0"<?=get_option('fs_post_interval', '0')=='0'?' selected':''?>>- Immediately</option>
					<option value="5"<?=get_option('fs_post_interval', '0')=='5'?' selected':''?>>5 seconds</option>
					<option value="10"<?=get_option('fs_post_interval', '0')=='10'?' selected':''?>>10 seconds</option>
					<option value="20"<?=get_option('fs_post_interval', '0')=='20'?' selected':''?>>20 seconds</option>
					<option value="30"<?=get_option('fs_post_interval', '0')=='30'?' selected':''?>>30 seconds</option>
					<option value="45"<?=get_option('fs_post_interval', '0')=='45'?' selected':''?>>45 seconds</option>
					<option value="60"<?=get_option('fs_post_interval', '0')=='60'?' selected':''?>>1 minute(s)</option>
					<option value="120"<?=get_option('fs_post_interval', '0')=='120'?' selected':''?>>2 minute(s)</option>
					<option value="180"<?=get_option('fs_post_interval', '0')=='180'?' selected':''?>>3 minute(s)</option>
					<option value="240"<?=get_option('fs_post_interval', '0')=='240'?' selected':''?>>4 minute(s)</option>
					<option value="300"<?=get_option('fs_post_interval', '0')=='300'?' selected':''?>>5 minute(s)</option>
					<option value="600"<?=get_option('fs_post_interval', '0')=='600'?' selected':''?>>10 minute(s)</option>
					<option value="900"<?=get_option('fs_post_interval', '0')=='900'?' selected':''?>>15 minute(s)</option>
					<option value="1200"<?=get_option('fs_post_interval', '0')=='1200'?' selected':''?>>20 minute(s)</option>
					<option value="1500"<?=get_option('fs_post_interval', '0')=='1500'?' selected':''?>>25 minute(s)</option>
					<option value="1800"<?=get_option('fs_post_interval', '0')=='1800'?' selected':''?>>30 minute(s)</option>
					<option value="2400"<?=get_option('fs_post_interval', '0')=='2400'?' selected':''?>>40 minute(s)</option>
					<option value="3000"<?=get_option('fs_post_interval', '0')=='3000'?' selected':''?>>50 minute(s)</option>
					<option value="3600"<?=get_option('fs_post_interval', '0')=='3600'?' selected':''?>>1 hour(s)</option>
					<option value="7200"<?=get_option('fs_post_interval', '0')=='7200'?' selected':''?>>2 hour(s)</option>
					<option value="10800"<?=get_option('fs_post_interval', '0')=='10800'?' selected':''?>>3 hour(s)</option>
					<option value="18000"<?=get_option('fs_post_interval', '0')=='18000'?' selected':''?>>5 hour(s)</option>
				</select>
			</div>
		</div>
	</div>
	<div style="width: 50%;" id="hide_limit_speed_add">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Use "Post interval" option only same social networks:' , 'fs-poster')?></div>
				<div class="fs_s_help"><?=esc_html__('Limit posting speed only same social network accounts. ' , 'fs-poster')?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" onautocomplete="off" name="fs_post_interval_type" class="fs_onoffswitch-checkbox" id="fs_post_interval_type"<?=get_option('fs_post_interval_type', '1')?' checked':''?>>
					<label class="fs_onoffswitch-label" for="fs_post_interval_type"></label>
				</div>
			</div>
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

			fsCode.ajax('settings_share_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster')?>" , 'success');
			});
		});

		fadeSpeed = 200;

		$("[data-open-img]").click(function ()
		{
			var imgSrc = $(this).data('open-img');

			fsCode.modal('<div><img src="'+imgSrc+'" style="width: 100%;"></div><div style="border-top: 1px solid #999; text-align: center; padding: 10px; margin-top: 9px;"><button type="button" data-modal-close="true" class="ws_btn ws_bg_danger">Close modal</button></div>' , {'width': '60%'});
		});

		$("#fs_post_interval").change(function()
		{
			if ($(this).val() > 0)
			{
				$("#hide_limit_speed_add").fadeIn(300);
			}
			else
			{
				$("#hide_limit_speed_add").fadeOut(300);
			}
		}).trigger('change');
	});
</script>