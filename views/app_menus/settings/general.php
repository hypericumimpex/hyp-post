<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

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
		<div><?=esc_html__('Unique post link:' , 'fs-poster')?></div>
		<div class="s_help"><?=esc_html__('You will have the ability to the share the same post in numerous amount of communities. In this case Facebook will block your duplicate post. If you activate this choice, the ending of every link of a post will include random symbols, therefore creating a unique post.' , 'fs-poster')?></div>
	</div>
	<div class="s_input">
		<div class="onoffswitch">
			<input type="checkbox" onautocomplete="off" name="unique_link" class="onoffswitch-checkbox" id="unique_link"<?=get_option('unique_link', '1')?' checked':''?>>
			<label class="onoffswitch-label" for="unique_link"></label>
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
			<input type="checkbox" onautocomplete="off" name="url_shortener" class="onoffswitch-checkbox" id="url_shortener"<?=get_option('url_shortener', '0')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide_shortener').fadeIn(fadeSpeed); }else{ $('#hide_shortener').fadeOut(fadeSpeed); }">
			<label class="onoffswitch-label" for="url_shortener"></label>
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
			<select class="ws_form_element" id="shortener_service" name="shortener_service" style="width: 200px;" onautocomplete="off" onchange="if($(this).val()=='bitly'){$('#for_bitly').show();}else{$('#for_bitly').hide();}">
				<option value="tinyurl"<?=get_option('shortener_service')=='tinyurl'?' selected':''?>>TinyURL</option>
				<option value="bitly"<?=get_option('shortener_service')=='bitly'?' selected':''?>>Bitly</option>
			</select>
		</div>
	</div>
	<div class="setting_item" id="for_bitly" style="display: none;">
		<div class="setting_item_label">
			<div><?=esc_html__('Bitly Access token:' , 'fs-poster')?></div>
			<div class="s_help"><?=esc_html__('For getting Access token you mast register bitly. After registration you will get a new access token!' , 'fs-poster')?></div>
		</div>
		<div class="s_input">
			<input type="text" onautocomplete="off" name="url_short_access_token_bitly" class="ws_form_element" style="width: 300px;" value="<?=esc_html(get_option('url_short_access_token_bitly', '100'))?>">
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

		$("#url_shortener").trigger('change');
		$("#shortener_service").trigger('change');

		fadeSpeed = 200;

		$(".select2-init").select2();
	});
</script>