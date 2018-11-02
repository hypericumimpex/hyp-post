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
		<textarea class="ws_form_element2" name="post_text_message_twitter" id="custom_text_area" style="height: 150px !important;"><?=esc_html(get_option('post_text_message_twitter', "{title}"))?></textarea>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Limit the Custom text symbols:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Twitter limits Tweet length to a specific number of characters for display. Max allowed Tweet length is 280 characters. If you enable this option then your Tweet is first 140 symbol of your Custome text.' , 'fs-poster')?></div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" name="fs_twitter_auto_cut_tweets" class="onoffswitch-checkbox" id="fs_twitter_auto_cut_tweets"<?=get_option('fs_twitter_auto_cut_tweets', '1')?' checked':''?>>
					<label class="onoffswitch-label" for="fs_twitter_auto_cut_tweets"></label>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Posting type:' , 'fs-poster')?></div>
				<div class="s_help"><?=esc_html__('Which method you want to post your tweets. With uploading featured image or uploading all contains images in post or only attach post link on tweet.' , 'fs-poster')?></div>
			</div>
			<select class="ws_form_element" name="fs_twitter_posting_type">
				<option value="1"<?=(get_option('fs_twitter_posting_type', '1')=='1' ? ' selected' : '') ?>>Link card method</option>
				<option value="2"<?=(get_option('fs_twitter_posting_type', '1')=='2' ? ' selected' : '') ?>>Upload featured image only</option>
				<option value="3"<?=(get_option('fs_twitter_posting_type', '1')=='3' ? ' selected' : '') ?>>Upload all post images</option>
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

			fsCode.ajax('settings_twitter_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster');?>" , 'success');
			});
		});

		$("#vk_load_members_communities").trigger('change');

		fadeSpeed = 200;
	});
</script>