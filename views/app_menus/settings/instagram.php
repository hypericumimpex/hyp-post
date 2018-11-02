

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Share instagram posts in:' , 'fs-poster');?></div>
				<div class="s_help"><?=esc_html__('Instagram posts together with the profile will be shared in the story.' , 'fs-poster');?></div>
			</div>
			<div style="width: 200px;">
				<select name="instagram_post_in_type" class="ws_form_element" style="width: 150px;">
					<option value="1"<?=get_option('instagram_post_in_type', '1')=='1'?' selected':''?>>Profile only</option>
					<option value="2"<?=get_option('instagram_post_in_type', '1')=='2'?' selected':''?>>Story ony</option>
					<option value="3"<?=get_option('instagram_post_in_type', '1')=='3'?' selected':''?>>Profile and Story</option>
				</select>
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div><?=esc_html__('Add link in story:' , 'fs-poster');?></div>
				<div class="s_help"><?=esc_html__('The post link shared within the story will be automatically added ( ONLY if you have a business account with >= 10k followers ).' , 'fs-poster');?></div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" name="instagram_story_link" class="onoffswitch-checkbox" id="instagram_story_link"<?=get_option('instagram_story_link', '1')?' checked':''?>>
					<label class="onoffswitch-label" for="instagram_story_link"></label>
				</div>
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
		<textarea class="ws_form_element2" name="post_text_message_instagram" id="custom_text_area" style="height: 150px !important;"><?=esc_html(get_option('post_text_message_instagram', "{title}"))?></textarea>
	</div>
</div>


<!--
<div style="display: flex;">
	<div style="width: 50%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div>Hashtag for story:</div>
				<div class="s_help">Paylashilan story-e hashtag elave etmek isteyirsizse aktiv edin</div>
			</div>
			<div class="s_input">
				<div class="onoffswitch">
					<input type="checkbox" name="instagram_story_hashtag" class="onoffswitch-checkbox" id="instagram_story_hashtag"<?/*=get_option('instagram_story_hashtag', '0')?' checked':''*/?> onchange="if($(this).is(':checked')){$('#hashtag_details').show(fadeSpeed);}else{$('#hashtag_details').hide(fadeSpeed);}">
					<label class="onoffswitch-label" for="instagram_story_hashtag"></label>
				</div>
			</div>
		</div>
	</div>

</div>

<div style="display: flex;" id="hashtag_details">
	<div style="width: 35%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div>Hashtag:</div>
				<div class="s_help">Hashtagi buraya daxil edin</div>
			</div>
			<input type="text" name="instagram_story_hashtag_name" value="<?/*=esc_html(get_option('instagram_story_hashtag_name'))*/?>" class="ws_form_element" placeholder="#example" style="max-width: 150px;">
		</div>
	</div>
	<div style="width: 100px;"></div>
	<div style="width: 35%;">
		<div class="setting_item">
			<div class="setting_item_label">
				<div>Hashtag Position:</div>
				<div class="s_help">Hashtagin yerlesheceyi yeri sechin</div>
			</div>
			<select class="ws_form_element" name="instagram_story_hashtag_position" style="max-width: 120px;">
				<option value="top">Top</option>
				<option value="bottom"<?/*=get_option('instagram_story_hashtag_position') == 'bottom'?' selected':''*/?>>Bottom</option>
			</select>
		</div>
	</div>
</div>-->

<script>
	var fadeSpeed = 0;
	jQuery(document).ready(function()
	{
		$("#save_btn").click(function()
		{
			var data = fsCode.serialize($(".settings_form"));

			fsCode.ajax('settings_instagram_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster');?>" , 'success');
			});
		});

		$("#instagram_story_hashtag").trigger('change');

		fadeSpeed = 400;
	});
</script>