<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

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
		<textarea class="ws_form_element2" name="fs_post_text_message_ok" id="custom_text_area" style="height: 150px !important;"><?=esc_html(get_option('fs_post_text_message_ok', "{title}"))?></textarea>
	</div>
</div>

<div style="display: flex;">
	<div style="">
		<div class="setting_item">
			<div class="setting_item_label" style="width: 55%;">
				<div><?=esc_html__('Posting type:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Which method you want to post your tweets. With uploading featured image or uploading all contains images in post or only attach post link on tweet.' , 'fs-poster')?></div>
			</div>
			<input type="hidden" name="fs_ok_posting_type" id="fs_ok_posting_type" value="<?=get_option('fs_ok_posting_type', '1')?>">
			<div class="fs_image_buttons1">
				<div<?=(get_option('fs_ok_posting_type', '1')=='1' ? ' class="selected_btn"' : '') ?> data-id="1">
					<img src="<?=plugin_dir_url(__FILE__).'../../../images/post_link_type.png'?>">
					<span>Link card view</span>
				</div>
				<div<?=(get_option('fs_ok_posting_type', '1')=='2' ? ' class="selected_btn"' : '') ?> data-id="2">
					<img src="<?=plugin_dir_url(__FILE__).'../../../images/post_image_type.png'?>">
					<span>Upload featured image only</span>
				</div>
				<div<?=(get_option('fs_ok_posting_type', '1')=='3' ? ' class="selected_btn"' : '') ?> data-id="3">
					<img src="<?=plugin_dir_url(__FILE__).'../../../images/post_multi_image_type.png'?>">
					<span>Upload all post images</span>
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

			fsCode.ajax('settings_ok_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster');?>" , 'success');
			});
		});

		$("#fs_vk_load_members_communities").trigger('change');

		fadeSpeed = 200;
	});
</script>