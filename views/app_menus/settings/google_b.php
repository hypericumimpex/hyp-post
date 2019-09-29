<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Share WooCommerce products as a product:' , 'fs-poster')?></div>
				<div class="fs_s_help"><?=esc_html__('If you want to share WooCommerce products as a post, then deactivate this option.' , 'fs-poster')?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" name="fs_google_b_share_as_product" class="fs_onoffswitch-checkbox" id="fs_google_b_share_as_product"<?=get_option('fs_google_b_share_as_product', '1')?' checked':''?>>
					<label class="fs_onoffswitch-label" for="fs_google_b_share_as_product"></label>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Add a button:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('You can choice post link button.' , 'fs-poster');?></div>
			</div>
			<div style="width: 200px;">
				<select name="fs_google_b_button_type" class="ws_form_element" style="width: 150px;">
					<option value="BOOK"<?=get_option('fs_google_b_button_type', 'LEARN_MORE')=='1'?' selected':''?>>Book</option>
					<option value="ORDER"<?=get_option('fs_google_b_button_type', 'LEARN_MORE')=='ORDER'?' selected':''?>>Order online</option>
					<option value="SHOP"<?=get_option('fs_google_b_button_type', 'LEARN_MORE')=='SHOP'?' selected':''?>>Buy</option>
					<option value="LEARN_MORE"<?=get_option('fs_google_b_button_type', 'LEARN_MORE')=='LEARN_MORE'?' selected':''?>>Learn more</option>
					<option value="SIGN_UP"<?=get_option('fs_google_b_button_type', 'LEARN_MORE')=='SIGN_UP'?' selected':''?>>Sign up</option>
					<option value="-"<?=get_option('fs_google_b_button_type', 'LEARN_MORE')=='-'?' selected':''?>>Don't share link</option>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="fs_setting_item">
	<div class="fs_setting_item_label" style="width: 55%;">
		<div><?=esc_html__('Custom text:' , 'fs-poster')?></div>
		<div class="fs_s_help">
			<div><?=esc_html__('Text of shared post. Using keywords, you can create your wanted text. Keywords:' , 'fs-poster')?></div>
			<div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post ID' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post title' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{title}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post excerpt' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{excerpt}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post author name' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{author}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post content (first 40 symbols)' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{content_short_40}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post content Full' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{content_full}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post link' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{link}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post short link' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{short_link}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Featured image URL' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{featured_image_url}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('WooCommerce - product price' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{product_regular_price}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('WooCommerce - product sale price' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{product_sale_price}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Unique ID' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{uniq_id}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post Tags' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{tags}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Post Categories' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{categories}</div>
				</div>
				<div class="fs_text_codes">
					<div><?=esc_html__('Custom fields' , 'fs-poster')?></div>
					<div class="ws_tooltip ws_color_info append_to_text" data-title="<?=esc_html__('Click to append in text' , 'fs-poster')?>">{cf_KEY}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="fs_s_input" style="width: 45%;">
		<textarea class="ws_form_element2" name="fs_post_text_message_google_b" id="custom_text_area" style="height: 150px !important;"><?=esc_html(get_option('fs_post_text_message_google_b', "{title}"))?></textarea>
	</div>
</div>

<script>
	var fadeSpeed = 0;
	jQuery(document).ready(function()
	{
		$("#save_btn").click(function()
		{
			var data = fsCode.serialize($(".settings_form"));

			fsCode.ajax('settings_google_b_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster');?>" , 'success');
			});
		});

		fadeSpeed = 200;
	});
</script>