<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Load my pages:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Once a new account has been added all the included \'my\' pages will be automatically added as well. You will be able to share any posts to these pages.' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" name="fs_load_own_pages" class="onoffswitch-checkbox" id="fs_load_own_pages"<?=get_option('fs_load_own_pages', '1')?' checked':''?>>
					<label class="onoffswitch-label" for="fs_load_own_pages"></label>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Load groups:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Once a new account has been added all the included groups will be automatically added as well. You will be able to share any posts to these groups.' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" name="fs_load_groups" class="onoffswitch-checkbox" id="fs_load_groups"<?=get_option('fs_load_groups', '1')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide2').fadeIn(fadeSpeed); }else{ $('#hide2').fadeOut(fadeSpeed); }">
					<label class="onoffswitch-label" for="fs_load_groups"></label>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 50%;" id="hide2">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Maximum groups to load:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Within the added account, groups will have a limit in regards to amount of loads (you will have a MAX of 1000 groups to load).' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<input type="text" name="fs_max_groups_limit" class="ws_form_element" style="text-align: center; width: 50px;" value="<?=esc_html(get_option('fs_max_groups_limit', '100'))?>">
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Load my liked pages:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Once a new account has been added all the included \'liked\' pages will be automatically added as well. You will be able to share any posts to these pages.' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" name="fs_load_liked_pages" class="onoffswitch-checkbox" id="fs_load_liked_pages"<?=get_option('fs_load_liked_pages', '0')?' checked':''?> onchange="if($(this).is(':checked')){ $('#hide1').fadeIn(fadeSpeed); }else{ $('#hide1').fadeOut(fadeSpeed); }">
					<label class="onoffswitch-label" for="fs_load_liked_pages"></label>
				</div>
			</div>
		</div>
	</div>
	<div style="width: 50%;" id="hide1">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Maximum liked pages to load:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Within the added account, \'liked\' pages will have a limit in regards to amount of loads (you will have a MAX of 1000 \'liked\' pages to load).' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<input type="text" name="fs_max_liked_pages_limit" class="ws_form_element" style="text-align: center; width: 50px;" value="<?=esc_html(get_option('fs_max_liked_pages_limit', '100'))?>">
			</div>
		</div>
	</div>
</div>

<div class="setting_item">
	<div class="setting_item_label" style="width: 55%;">
		<div><?=esc_html__('Custom text:' , 'fs-poster')?></div>
		<div class="s_help">
			<div><?=esc_html__('Text of shared post. Using keywords, you can create your wanted text. Keywords:' , 'fs-poster')?></div>
			<div>
				<div class="text_codes">
					<div><?=esc_html__('Post ID' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{id}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post title' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{title}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post excerpt' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{excerpt}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post author name' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{author}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post content (first 40 symbols)' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{content_short_40}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post content Full' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{content_full}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post link' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{link}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post short link' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{short_link}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('WooCommerce - product price' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{product_regular_price}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('WooCommerce - product sale price' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{product_sale_price}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Unique ID' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{uniq_id}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post Tags' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{tags}</div>
				</div>
				<div class="text_codes">
					<div><?=esc_html__('Post Categories' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{categories}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="s_input" style="width: 45%;">
		<textarea class="ws_form_element2" name="fs_post_text_message_fb" id="custom_text_area" style="height: 150px !important;"><?=esc_html(get_option('fs_post_text_message_fb', "{title}"))?></textarea>
	</div>

</div>

<div style="display: flex;">
	<div class="setting_item">
		<div class="setting_item_label" style="width: 55%;">
			<div><?=esc_html__('Posting type:' , 'fs-poster')?></div>
			<div class="s_help"><?=esc_html__('Which method you want to share your posts. With uploading featured image or uploading all contains images in post or only attach post link.' , 'fs-poster')?></div>
		</div>
		<input type="hidden" name="fs_facebook_posting_type" id="fs_facebook_posting_type" value="<?=get_option('fs_facebook_posting_type', '1')?>">
		<div class="fs_image_buttons1">
			<div<?=(get_option('fs_facebook_posting_type', '1')=='1' ? ' class="selected_btn"' : '') ?> data-id="1">
				<img src="<?=plugin_dir_url(__FILE__).'../../../images/post_link_type.png'?>">
				<span>Link card view</span>
			</div>
			<div<?=(get_option('fs_facebook_posting_type', '1')=='2' ? ' class="selected_btn"' : '') ?> data-id="2">
				<img src="<?=plugin_dir_url(__FILE__).'../../../images/post_image_type.png'?>">
				<span>Upload featured image only</span>
			</div>
			<div<?=(get_option('fs_facebook_posting_type', '1')=='3' ? ' class="selected_btn"' : '') ?> data-id="3">
				<img src="<?=plugin_dir_url(__FILE__).'../../../images/post_multi_image_type.png'?>">
				<span>Upload all post images</span>
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

			fsCode.ajax('settings_facebook_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster')?>" , 'success');
			});
		});

		$("#fs_load_groups").trigger('change');
		$("#fs_load_liked_pages").trigger('change');

		fadeSpeed = 200;
	});
</script>