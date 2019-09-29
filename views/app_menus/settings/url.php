<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('Unique post link:' , 'fs-poster')?></div>
		<div class="fs_s_help"><?=esc_html__('You will have the ability to the share the same post in numerous amount of communities. In this case Facebook will block your duplicate post. If you activate this choice, the ending of every link of a post will include random symbols, therefore creating a unique post.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_unique_link" class="fs_onoffswitch-checkbox" id="fs_unique_link"<?=get_option('fs_unique_link', '1')?' checked':''?>>
			<label class="fs_onoffswitch-label" for="fs_unique_link"></label>
		</div>
	</div>
</div>

<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('URL shortener:' , 'fs-poster')?> <i class="fa fa-warning fa-exclamation-circle" style="padding-left: 6px; color: #ff7784;" title="Note: Pinterest and Reddit does not supports short links."></i></div>
		<div class="fs_s_help"><?=esc_html__('If you activate this option you will auto short your post URLs.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_url_shortener" class="fs_onoffswitch-checkbox" id="fs_url_shortener"<?=get_option('fs_url_shortener', '0')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide_shortener').fadeIn(fadeSpeed); }else{ $('#hide_shortener').fadeOut(fadeSpeed); }">
			<label class="fs_onoffswitch-label" for="fs_url_shortener"></label>
		</div>
	</div>
</div>

<div id="hide_shortener" style="display: none; width: 50%;">
	<div class="fs_setting_item">
		<div class="fs_setting_item_label">
			<div><?=esc_html__('URL shortener service:' , 'fs-poster')?></div>
			<div class="fs_s_help"><?=esc_html__('Select which URL shortener service you wont to use your short URLs.' , 'fs-poster')?></div>
		</div>
		<div class="fs_s_input">
			<select class="ws_form_element" id="fs_shortener_service" name="fs_shortener_service" style="width: 200px;" onautocomplete="off" onchange="if($(this).val()=='bitly'){$('#for_bitly').show();}else{$('#for_bitly').hide();}">
				<option value="tinyurl"<?=get_option('fs_shortener_service')=='tinyurl'?' selected':''?>>TinyURL</option>
				<option value="bitly"<?=get_option('fs_shortener_service')=='bitly'?' selected':''?>>Bitly</option>
			</select>
		</div>
	</div>
	<div class="fs_setting_item" id="for_bitly" style="display: none;">
		<div class="fs_setting_item_label">
			<div><?=esc_html__('Bitly Access token:' , 'fs-poster')?></div>
			<div class="fs_s_help"><?=esc_html__('For getting Access token you mast register bitly. After registration you will get a new access token!' , 'fs-poster')?></div>
		</div>
		<div class="fs_s_input">
			<input type="text" onautocomplete="off" name="fs_url_short_access_token_bitly" class="ws_form_element" style="width: 300px;" value="<?=esc_html(get_option('fs_url_short_access_token_bitly', '100'))?>">
		</div>
	</div>
</div>

<div class="fs_setting_item" style="width: 50%;">
	<div class="fs_setting_item_label">
		<div><?=esc_html__('Share custom URL instead of WP Post link:' , 'fs-poster')?></div>
		<div class="fs_s_help"><?=esc_html__('If you activate this option you will type your Custom Post URL using specific keywords.' , 'fs-poster')?></div>
	</div>
	<div class="fs_s_input">
		<div class="fs_onoffswitch">
			<input type="checkbox" onautocomplete="off" name="fs_share_custom_url" class="fs_onoffswitch-checkbox" id="fs_share_custom_url"<?=get_option('fs_share_custom_url', '0')?' checked':''?>>
			<label class="fs_onoffswitch-label" for="fs_share_custom_url"></label>
		</div>
	</div>
</div>

<div class="fs_setting_item" id="addition_url_parameters_section" style="width: 100%;">
	<div class="fs_setting_item_label" style="width: 50%;">
		<div>
			<?=esc_html__('Additional URL parameters:' , 'fs-poster')?>
			<button class="ws_btn ws_btn_small" id="use_google_a_template" type="button">( <i class="fa fa-hand-point-right"></i> Use Google Analytics template )</button>
		</div>
		<div class="fs_s_help">
			<div><?=esc_html__('Also, you can use the following keywords on your parameters:' , 'fs-poster')?></div>
			<div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post ID' , 'fs-poster')?></div>
					<div class="ws_color_info">{post_id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post title' , 'fs-poster')?></div>
					<div class="ws_color_info">{post_title}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Social Network name' , 'fs-poster')?></div>
					<div class="ws_color_info">{network_name}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Social Network code' , 'fs-poster')?></div>
					<div class="ws_color_info">{network_code}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Account name' , 'fs-poster')?></div>
					<div class="ws_color_info">{account_name}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Site name' , 'fs-poster')?></div>
					<div class="ws_color_info">{site_name}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Unique ID' , 'fs-poster')?></div>
					<div class="ws_color_info">{uniq_id}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="fs_s_input" style="width: 45%;">
		<input type="text" onautocomplete="off" name="fs_url_additional" id="fs_url_additional" class="ws_form_element" value="<?=esc_html(get_option('fs_url_additional', ''))?>">
	</div>
</div>

<div class="fs_setting_item" id="custom_url_section" style="width: 100%;">
	<div class="fs_setting_item_label" style="width: 50%;">
		<div><?=esc_html__('Custom URL:' , 'fs-poster')?></div>
		<div class="fs_s_help">
			<div><?=esc_html__('Also, you can use the following keywords on your parameters:' , 'fs-poster')?></div>
			<div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post ID' , 'fs-poster')?></div>
					<div class="ws_color_info">{post_id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Feed ID' , 'fs-poster')?></div>
					<div class="ws_color_info">{feed_id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post title' , 'fs-poster')?></div>
					<div class="ws_color_info">{post_title}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Social Network name' , 'fs-poster')?></div>
					<div class="ws_color_info">{network_name}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Social Network code' , 'fs-poster')?></div>
					<div class="ws_color_info">{network_code}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Account name' , 'fs-poster')?></div>
					<div class="ws_color_info">{account_name}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Site name' , 'fs-poster')?></div>
					<div class="ws_color_info">{site_name}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Site URL' , 'fs-poster')?></div>
					<div class="ws_color_info">{site_url}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Site URL ( URL Encoded )' , 'fs-poster')?></div>
					<div class="ws_color_info">{site_url_encoded}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post URL' , 'fs-poster')?></div>
					<div class="ws_color_info">{post_url}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post URL ( URL Encoded )' , 'fs-poster')?></div>
					<div class="ws_color_info">{post_url_encoded}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Unique ID' , 'fs-poster')?></div>
					<div class="ws_color_info">{uniq_id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Custom fields' , 'fs-poster')?></div>
					<div class="ws_color_info">{cf_KEY}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="fs_s_input" style="width: 45%;">
		<input type="text" onautocomplete="off" name="fs_custom_url_to_share" id="fs_custom_url_to_share" class="ws_form_element" value="<?=esc_html(get_option('fs_custom_url_to_share', '{site_url}/?feed_id={feed_id}'))?>">
	</div>
</div>

<script>
	var fadeSpeed = 0;
	jQuery(document).ready(function()
	{
		$("#fs_share_custom_url").change(function()
		{
			if( $(this).is(':checked') )
			{
				$("#addition_url_parameters_section").slideUp(fadeSpeed);
				$("#custom_url_section").slideDown(fadeSpeed);
			}
			else
			{
				$("#custom_url_section").slideUp(fadeSpeed);
				$("#addition_url_parameters_section").slideDown(fadeSpeed);
			}
		}).trigger('change');

		$("#save_btn").click(function()
		{
			var data = fsCode.serialize($(".settings_form"));

			fsCode.ajax('settings_url_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster')?>" , 'success');
			});
		});

		$("#fs_url_shortener").trigger('change');
		$("#fs_shortener_service").trigger('change');

		fadeSpeed = 200;

		$("#use_google_a_template").click(function()
		{
			$("#fs_url_additional").val( 'utm_source={network_name}&utm_medium={account_name}&utm_campaign=FS%20Poster' );
		});
	});
</script>