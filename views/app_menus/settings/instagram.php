<script type="text/javascript" src="<?=FS_PLUGIN_URL?>/js/jscolor.js"></script>

<style>


	.ig_story_customize_inpt
	{
		width: 70px;
	}

	#instagram_status_preview
	{
		position: relative;
		background-color: #636e72;

		width: 720px;
		height: 1280px;
		margin-left: 30px;
		margin-bottom: 30px;

		transform: scale(0.3);

		margin-top: -475px;
		margin-left: -200px;

	}
	.story_img
	{
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.story_img > img
	{
		width: 100%;
	}
	.story_title
	{
		position: absolute;
		top: 125px;
		left: 20px;
		width: 660px;
		padding: 30px 0;
		text-align: center;
		background-color: rgba(0, 0, 0, 0.3);
		font-size: 30px;
		color: #FFF;
		line-height: normal;
	}
</style>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Share instagram posts in:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=esc_html__('Instagram posts together with the profile will be shared in the story.' , 'fs-poster');?></div>
			</div>
			<div style="width: 200px;">
				<select name="fs_instagram_post_in_type" class="ws_form_element" style="width: 150px;">
					<option value="1"<?=get_option('fs_instagram_post_in_type', '1')=='1'?' selected':''?>>Profile only</option>
					<option value="2"<?=get_option('fs_instagram_post_in_type', '1')=='2'?' selected':''?>>Story only</option>
					<option value="3"<?=get_option('fs_instagram_post_in_type', '1')=='3'?' selected':''?>>Profile and Story</option>
				</select>
			</div>
		</div>
	</div>
</div>

<div style="display: flex;">
	<div style="width: 50%;">
		<div class="fs_setting_item">
			<div class="fs_setting_item_label">
				<div><?=esc_html__('Add link in story:' , 'fs-poster');?></div>
				<div class="fs_s_help"><?=__('The post link shared within the story will be automatically added ( ONLY if you have a business account with >= 10k followers. <b style="color: #9e6a6d; font-weight: 700;">Note:</b> Cookie method not allowed this. ).', 'fs-poster');?></div>
			</div>
			<div class="fs_s_input">
				<div class="fs_onoffswitch">
					<input type="checkbox" name="fs_instagram_story_link" class="fs_onoffswitch-checkbox" id="fs_instagram_story_link"<?=get_option('fs_instagram_story_link', '1')?' checked':''?>>
					<label class="fs_onoffswitch-label" for="fs_instagram_story_link"></label>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="fs_setting_item">
	<div class="fs_setting_item_label" style="width: 40%;">
		<div><?=esc_html__('Custom text ( for post ):' , 'fs-poster')?></div>
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
	<div class="fs_s_input" style="width: 60%; display: flex;">
		<div style="width: 50%;">
			<label>For Post</label>
			<textarea class="ws_form_element2" name="fs_post_text_message_instagram" id="custom_text_area" style="width: 100%; height: 150px !important;"><?=esc_html(get_option('fs_post_text_message_instagram', "{title}"))?></textarea>
		</div>
		<div style="width: 50%; padding-left: 5px;">
			<label>For Story</label>
			<textarea class="ws_form_element2" name="fs_post_text_message_instagram_h" style="height: 150px !important;"><?=esc_html(get_option('fs_post_text_message_instagram_h', "{title}"))?></textarea>
		</div>
	</div>
</div>

<hr>

<div style="color: #ff7675; font-size: 16px; margin: 10px 0 20px 0;">Customize Story Image</div>

<div style="display: flex; height: 450px;">
	<div style="width: 300px;">

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Story background color: </div>
			</div>
			<div class="fs_s_input">
				#<input type="text" class="jscolor ig_story_customize_inpt" name="fs_instagram_story_background" value="<?=get_option('fs_instagram_story_background', '636e72')?>" data-type="story-background"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title background: </div>
			</div>
			<div class="fs_s_input">
				#<input type="text" class="jscolor ig_story_customize_inpt" name="fs_instagram_story_title_background" value="<?=get_option('fs_instagram_story_title_background', '000000')?>" data-type="title-background-color"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title background opacity: </div>
			</div>
			<div class="fs_s_input">
				%<input type="text" name="fs_instagram_story_title_background_opacity" value="<?=get_option('fs_instagram_story_title_background_opacity', '30')?>" class="ig_story_customize_inpt" data-type="title-background-opacity"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title color: </div>
			</div>
			<div class="fs_s_input">
				#<input type="text" name="fs_instagram_story_title_color" class="jscolor ig_story_customize_inpt" data-type="title-color" value="<?=get_option('fs_instagram_story_title_color', 'FFFFFF')?>"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title - top position: </div>
			</div>
			<div class="fs_s_input">
				px<input type="text" name="fs_instagram_story_title_top" value="<?=get_option('fs_instagram_story_title_top', '125')?>" class="ig_story_customize_inpt" data-type="title-top"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title - left position:</div>
			</div>
			<div class="fs_s_input">
				px<input type="text" name="fs_instagram_story_title_left" value="<?=get_option('fs_instagram_story_title_left', '30')?>" class="ig_story_customize_inpt" data-type="title-left"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title - width <small>(max: 720px)</small>:</div>
			</div>
			<div class="fs_s_input">
				px<input type="text" name="fs_instagram_story_title_width" value="<?=get_option('fs_instagram_story_title_width', '660')?>" class="ig_story_customize_inpt" data-type="title-width"/>
			</div>
		</div>

		<div class="fs_setting_item" style="margin-bottom: 10px;">
			<div class="fs_setting_item_label">
				<div>Title - font-size:</div>
			</div>
			<div class="fs_s_input">
				px<input type="text" name="fs_instagram_story_title_font_size" value="<?=get_option('fs_instagram_story_title_font_size', '30')?>" class="ig_story_customize_inpt" data-type="title-font-size"/>
			</div>
		</div>

	</div>
	<div style="width: 400px;">
		<div id="instagram_status_preview">
			<div class="story_title">{Story title}</div>
			<div class="story_img"><img src="<?=FS_PLUGIN_URL?>/images/sample_story_img.png"</div>
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

			fsCode.ajax('settings_instagram_save' , data , function(result)
			{
				fsCode.toast("<?=esc_html__('Save successful!' , 'fs-poster');?>" , 'success');
			});
		});

		$("#instagram_story_hashtag").trigger('change');

		fadeSpeed = 400;

		$(".ig_story_customize_inpt").on('change keyup', function()
		{
			var type	= $(this).data('type'),
				val		= $(this).val();

			if( type == 'story-background' )
			{
				$("#instagram_status_preview").css('background', '#' + val);
			}
			else if( type == 'title-background-color' || type == 'title-background-opacity' )
			{
				var hex = $(".ig_story_customize_inpt[data-type='title-background-color']").val();
				var rgb = hexToRgb( hex );
				var opacity = $(".ig_story_customize_inpt[data-type='title-background-opacity']").val();
				opacity = (opacity > 100 ? 100 : opacity) / 100;

				$("#instagram_status_preview .story_title").css('background', 'rgba(' + rgb + ',' + opacity + ')');
			}
			else
			{
				type = type.substr(6);

				if( type == 'color' )
				{
					val = '#' + val;
				}
				else
				{
					val = val + 'px';
				}

				$("#instagram_status_preview .story_title").css(type, val);
			}

		}).trigger('change');

		function hexToRgb(hex)
		{
			var bigint = parseInt(hex, 16);
			var r = (bigint >> 16) & 255;
			var g = (bigint >> 8) & 255;
			var b = bigint & 255;

			return r + "," + g + "," + b;
		}

	});
</script>