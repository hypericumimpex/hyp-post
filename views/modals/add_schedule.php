<?php
defined('MODAL') or exit();

$accounts = FSwpDB()->get_results(
	FSwpDB()->prepare("
		SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM ".FSwpDB()->base_prefix."terms WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type FROM ".FSwpTable('account_status')." tb1
		LEFT JOIN ".FSwpTable('accounts')." tb2 ON tb2.id=tb1.account_id
		WHERE tb1.user_id=%d
		ORDER BY name" , [ get_current_user_id() ])
	, ARRAY_A
);

$activeNodes = FSwpDB()->get_results(
	FSwpDB()->prepare("
	SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM ".FSwpDB()->base_prefix."terms WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name FROM ".FSwpTable('account_node_status')." tb1
	LEFT JOIN ".FSwpTable('account_nodes')." tb2 ON tb2.id=tb1.node_id
	WHERE tb1.user_id=%d
	ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name" , [ get_current_user_id() ])
	, ARRAY_A
);

$activeNodes = array_merge($accounts , $activeNodes);
?>

<style>
	.fs_modal_header_bg
	{
		height: 50px;
		line-height: 52px;
		font-size: 17px;
		color: #888;
		font-weight: 600;
	}
	.fsCodeModal .modal-content span>i
	{
		color: #CCC;
		padding-left: 5px;
	}

	#proModal<?=$mn?> .schedule-mdl-tabs
	{
		display: flex;
		margin-bottom: 20px;
	}
	#proModal<?=$mn?> .schedule-mdl-tabs > div
	{
		white-space: nowrap;
		color: #888;
		cursor: pointer;
		padding: 5px 35px 10px 10px;
		font-weight: 600;
		text-align: center;
		border-bottom: 1px solid #CCC;
	}

	#proModal<?=$mn?> .schedule-mdl-tabs > div > span
	{
		display: inline-block;
		width: 20px;
		height: 20px;
		-webkit-border-radius: 20px;
		-moz-border-radius: 20px;
		border-radius: 20px;
		background: #CCC;
		color: #FFF;
		font-size: 11px;
		line-height: 19px;
	}

	#proModal<?=$mn?> .schedule-mdl-tabs > div.active-tab
	{
		border-color: #FFAB00 !important;
	}

	#proModal<?=$mn?> .schedule-mdl-tabs > div.active-tab > span
	{
		background: #FFAB00;
	}

	#proModal<?=$mn?> .fs_text_codes
	{
		display: flex;
		font-size: 10px;
	}
	#proModal<?=$mn?> .fs_text_codes>:first-child
	{
		width: 180px;
	}
	#proModal<?=$mn?> .fs_text_codes>:last-child
	{
		font-weight: 700;
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	#proModal<?=$mn?> .custom-messages-sn,
		#proModal<?=$mn?> .sn_tabs
	{
		width: 170px;
		min-width: 170px;
	}
	#proModal<?=$mn?> .custom-messages-sn > div,
		#proModal<?=$mn?> .sn_tabs > div
	{
		padding: 5px;
		border: 1px solid #DDD;
		-webkit-border-radius: 2px;
		-moz-border-radius: 2px;
		border-radius: 2px;
		margin: 3px;
		color: #555;
		cursor: pointer;
	}
	#proModal<?=$mn?> .custom-messages-sn > div i,
		#proModal<?=$mn?> .sn_tabs > div i
	{
		color: #62d1ff;
		padding-right: 5px;
	}

	#proModal<?=$mn?> .custom-messages-sn > div.active-sn,
		#proModal<?=$mn?> .sn_tabs > div.active-sn
	{
		border-left: 2px solid #FFAB00;
	}

	#proModal<?=$mn?> .share_switch
	{
		display: none !important;
	}

	#proModal<?=$mn?> .fs_share_box_items
	{
		width: 295px;
		max-height: 300px;
		overflow: auto;
		border: 1px solid #DDD;
		background: #FFF;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;

		-webkit-box-shadow: 2px 2px 2px #EEE;
		-moz-box-shadow: 2px 2px 2px #EEE;
		box-shadow: 2px 2px 2px #EEE;
	}

	#proModal<?=$mn?> .fs_share_box_node
	{
		display: flex;
		align-items: center;
		padding: 5px;
		height: 35px;
		border-bottom: 1px solid #DDD;
	}
	#proModal<?=$mn?> .fs_share_box_node:last-child
	{
		border-bottom: 0 !important;
	}
	#proModal<?=$mn?> .node_img
	{
		width: 30px;
	}
	#proModal<?=$mn?> .node_img>img
	{
		width: 30px;
		height: 30px;
		-webkit-border-radius: 30px;
		-moz-border-radius: 30px;
		border-radius: 30px;
	}
	#proModal<?=$mn?> .node_label
	{
		margin-left: 10px;
		font-size: 14px;
		overflow: hidden;
		white-space: nowrap;
	}
	#proModal<?=$mn?> .node_remove
	{
		width: 50px;
		height: 100%;
		position: relative;
		background: #FFF;
	}
	#proModal<?=$mn?> .node_remove>.node_remove_btn
	{
		display: none;
		position: absolute;
		top: 0;
		bottom: 0;
		margin: auto;
		height: 20px;
		right: 3px;
		color: #ff7675;
		-webkit-border-radius: 15px;
		-moz-border-radius: 15px;
		border-radius: 15px;
		cursor: pointer;
	}
	#proModal<?=$mn?> .fs_share_box_node:hover .node_remove>.node_remove_btn
	{
		display: block;
	}
	#proModal<?=$mn?> .node_label_help
	{
		font-size: 11px;
		color: #888;
	}

	.ui-datepicker
	{
		z-index: 9999999999999 !important;
	}


	.fs_form_group
	{
		margin-bottom: 10px;
	}

	.fs_form_group > label
	{
		margin-bottom: 5px;
		display: block;
		color: #212529;
		padding-left: 2px;
	}

	#local_timezone
	{
		width: 200px;
		line-height: 39px;
		padding-left: 5px;
		color: #999;
		font-weight: 600;
	}

</style>

<span class="close" data-modal-close="true">&times;</span>

<div style="padding: 10px 0 20px; margin-left: 40px; margin-right: 40px; display: flex; flex-direction: column;">

	<div class="fs_modal_header_bg">ADD NEW SCHEDULE</div>

	<div style="width: 100%; margin-top: 15px;">
		<div class="schedule-mdl-tabs">
			<div data-tab-id="basic" class="active-tab"><span>1</span> Basic data</div>
			<div data-tab-id="filters"><span>2</span> Filters</div>
			<div data-tab-id="accounts"><span>3</span> Accounts</div>
			<div data-tab-id="custom_messages"><span>4</span> Custom messages</div>
		</div>

		<div>

			<div data-oppen-tab-id="basic">

				<div class="fs_form_group">
					<label><?=esc_html__('Title:' , 'fs-poster')?> <span class="ws_tooltip" data-title="You can add multiple Schedules. Please fill the title field because of confusions."><i class="fa fa-info-circle"></i></span></label>
					<input type="text" class="ws_form_element2 title" placeholder="<?=esc_html__('Title' , 'fs-poster')?>" style="width: 500px;">
				</div>

				<div class="fs_form_group">
					<label><?=esc_html__('Start date, time:' , 'fs-poster')?> <span class="ws_tooltip" data-title="Which date-time this schedule will be start?"><i class="fa fa-info-circle"></i></span></label>
					<div style="display: flex; width: 500px;">
						<input type="text" class="ws_form_element2 start_date" placeholder="<?=esc_html__('Select date' , 'fs-poster')?>" value="<?=current_time('Y-m-d')?>" style="width: 150px;">
						<input type="time" class="ws_form_element2 start_time" placeholder="<?=esc_html__('Select time' , 'fs-poster')?>" value="<?=current_time('H:i')?>" style="width: 150px;">
						<div id="local_timezone">Local time: <?=current_time('Y-m-d h:i A')?></div>
					</div>
				</div>

				<div class="fs_form_group">
					<label><?=esc_html__('Post every:' , 'fs-poster')?> <span class="ws_tooltip" data-title="Interval of every post."><i class="fa fa-info-circle"></i></span></label>

					<div style="display: flex; width: 300px;">
						<select class="ws_form_element interval">
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="15">15</option>
							<option value="20">20</option>
							<option value="30">30</option>
							<option value="40">40</option>
							<option value="45">45</option>
							<option value="50">50</option>
						</select>
						<select class="ws_form_element interval_type">
							<option value="60"><?=esc_html__('Hour' , 'fs-poster')?></option>
							<option value="1"><?=esc_html__('Minute' , 'fs-poster')?></option>
							<option value="1440"><?=esc_html__('Day' , 'fs-poster')?></option>
						</select>
					</div>
				</div>

				<div class="fs_form_group" style="width: 300px;">
					<label>Order post by: <span class="ws_tooltip" data-title="Method for selecting posts."><i class="fa fa-info-circle"></i></span></label>
					<select class="ws_form_element post_sort">
						<option value="random2">Randomly ( without duplicates )</option>
						<option value="random">Randomly</option>
						<option value="old_first">Old posts first</option>
						<option value="new_first">New posts first</option>
					</select>
				</div>
			</div>
			<div data-oppen-tab-id="filters" style="display: none; position: relative;">

				<div class="fs_form_group">
					<label>Select posts added in:</label>
					<select class="ws_form_element post_date_filter" style="width: 300px;">
						<option value="all">All time</option>
						<option value="this_week">This week</option>
						<option value="previously_week">Previously week</option>
						<option value="this_month">This month</option>
						<option value="previously_month">Previously month</option>
						<option value="this_year">This year</option>
						<option value="last_30_days">Last 30 days</option>
						<option value="last_60_days">Last 60 days</option>
					</select>
				</div>

				<div class="fs_form_group">
					<label>Post type filter: <span class="ws_tooltip" data-title="You can select new post types in [ FS Poster > Settings > Share post types ]."><i class="fa fa-info-circle"></i></span></label>
					<select class="ws_form_element post_type_filter" style="width: 300px;">
						<?php
						$allowedPostTypes = explode('|', get_option('fs_allowed_post_types', ''));

						foreach( get_post_types() AS $post_type )
						{
							if( !in_array( $post_type , $allowedPostTypes ) )
							{
								continue;
							}

							print '<option value="' . htmlspecialchars($post_type) . '">' . htmlspecialchars(ucfirst($post_type)) . '</option>';
						}
						?>
					</select>
				</div>

				<div class="fs_form_group">
					<label>Post category filter:</label>
					<select class="ws_form_element category_filter" style="width: 300px;" data-placeholder="<?=esc_html__('Not  selected' , 'fs-poster')?>">
						<option>-</option>
						<?php
						$lastTaxonomy = '';
						foreach( get_terms( ['category', 'product_cat'], [ 'hide_empty' => false, 'orderby' => 'taxonomy' ] ) AS $categ )
						{
							if( $lastTaxonomy != $categ->taxonomy )
							{
								print '<optgroup label="' . htmlspecialchars( $categ->taxonomy == 'category' ? 'Post categories' : ( $categ->taxonomy == 'product_cat' ? 'Product categories' : $categ->taxonomy ) ) . '">';
								$lastTaxonomy = $categ->taxonomy;
							}

							print '<option value="' . htmlspecialchars($categ->term_id) . '">' . htmlspecialchars($categ->name) . '</option>';
						}
						?>
					</select>
				</div>

			</div>
			<div data-oppen-tab-id="accounts" style="display: none; position: relative;">

				<div style="display: flex">
					<div class="sn_tabs">
						<div data-tab-id="fb" class="active-sn"><i class="fab fa-facebook-square"></i> Facebook</div>
						<div data-tab-id="instagram"><i class="fab fa-instagram"></i> Instagram</div>
						<div data-tab-id="twitter"><i class="fab fa-twitter-square"></i> Twitter</div>
						<div data-tab-id="linkedin"><i class="fab fa-linkedin"></i> Linkedin</div>
						<div data-tab-id="tumblr"><i class="fab fa-tumblr-square"></i> Tumblr</div>
						<div data-tab-id="reddit"><i class="fab fa-reddit-square"></i> Reddit</div>
						<div data-tab-id="vk"><i class="fab fa-vk"></i> VK.com</div>
						<div data-tab-id="ok"><i class="fab fa-odnoklassniki"></i> OK.ru</div>
						<div data-tab-id="pinterest"><i class="fab fa-pinterest-square"></i> Pinterest</div>
						<div data-tab-id="google_b"><i class="fab fa-google"></i> Google MyBusiness</div>
						<div data-tab-id="telegram"><i class="fab fa-telegram"></i> Telegram</div>
						<div data-tab-id="medium"><i class="fab fa-medium"></i> Medium</div>
					</div>
					<div style="padding: 3px 15px;">
						<div class="fs_share_box_items" id="share_box1">
							<?php
							foreach ($activeNodes AS $nodeInf)
							{
								$coverPhoto = FSprofilePic($nodeInf);
								if( $nodeInf['filter_type'] == 'no' )
								{
									$titleText = '';
								}
								else
								{
									$titleText = ($nodeInf['filter_type']=='in' ? 'Share only selected categories posts:' : 'Do not share selected categories posts:') . "\n";
									$titleText .= str_replace(',' , ', ' , $nodeInf['categories_name']);
								}

								?>
								<div class="fs_share_box_node" data-tab="<?=$nodeInf['driver']?>">
									<input type="hidden" name="share_on_nodes[]" value="<?=$nodeInf['node_type'].':'.$nodeInf['id']?>">
									<div class="node_img"><img src="<?=$coverPhoto?>"></div>
									<div class="node_label" style="width: 100%;">
										<div>
											<?=esc_html($nodeInf['name']);?>
											<a href="<?=FSprofileLink($nodeInf)?>" target="_blank" class="ws_btn" title="Profile link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
										</div>
										<div class="node_label_help"><?=esc_html($nodeInf['node_type']);?> <?=empty($titleText) ? '' : '<i class="fa fa-filter" title="'.$titleText.'" style="padding-left: 5px; color: #fdcb6e;"></i>'?></div>
									</div>
									<div class="node_remove"><div class="node_remove_btn" type="button"><i class="fa fa-times"></i></div></div>
								</div>
								<?php
							}
							?>
						</div>
						<div style="display: flex; justify-content: space-between; margin-top: 5px;">
							<div class="ws_bg_default ws_btn add_to_list_btn" style="padding: 4px 10px; border-radius: 3px; height: 18px !important;"><i class="fa fa-plus"></i> add account</div>
							<div class="ws_bg_warning ws_btn remove_all_from_list" style="padding: 4px 10px; border-radius: 3px; height: 18px !important;"><i class="fa fa-times"></i> empty list</div>
						</div>
					</div>
				</div>
			</div>
			<div data-oppen-tab-id="custom_messages" style="display: none; position: relative;">

				<div style="display: flex;">
					<div class="custom-messages-sn">
						<div data-sn-id="fb" class="active-sn"><i class="fab fa-facebook-square"></i> Facebook</div>
						<div data-sn-id="instagram"><i class="fab fa-instagram"></i> Instagram</div>
						<div data-sn-id="twitter"><i class="fab fa-twitter-square"></i> Twitter</div>
						<div data-sn-id="linkedin"><i class="fab fa-linkedin"></i> Linkedin</div>
						<div data-sn-id="tumblr"><i class="fab fa-tumblr-square"></i> Tumblr</div>
						<div data-sn-id="reddit"><i class="fab fa-reddit-square"></i> Reddit</div>
						<div data-sn-id="vk"><i class="fab fa-vk"></i> VK.com</div>
						<div data-sn-id="ok"><i class="fab fa-odnoklassniki"></i> OK.ru</div>
						<div data-sn-id="pinterest"><i class="fab fa-pinterest-square"></i> Pinterest</div>
						<div data-sn-id="google_b"><i class="fab fa-google"></i> Google MyBusiness</div>
						<div data-sn-id="telegram"><i class="fab fa-telegram"></i> Telegram</div>
						<div data-sn-id="medium"><i class="fab fa-medium"></i> Medium</div>
					</div>
					<div style="width: calc(100% - 150px) !important; margin-left: 10px;">
						<div>Custom text:</div>
						<div class="social_network_custom_texts">
							<textarea class="ws_form_element2" data-sn-id="fb"><?=esc_html(get_option('fs_post_text_message_fb', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="instagram"><?=esc_html(get_option('fs_post_text_message_instagram', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="twitter"><?=esc_html(get_option('fs_post_text_message_twitter', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="linkedin"><?=esc_html(get_option('fs_post_text_message_linkedin', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="tumblr"><?=esc_html(get_option('fs_post_text_message_tumblr', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="reddit"><?=esc_html(get_option('fs_post_text_message_reddit', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="vk"><?=esc_html(get_option('fs_post_text_message_vk', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="ok"><?=esc_html(get_option('fs_post_text_message_ok', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="pinterest"><?=esc_html(get_option('fs_post_text_message_pinterest', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="google_b"><?=esc_html(get_option('fs_post_text_message_google_b', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="telegram"><?=esc_html(get_option('fs_post_text_message_telegram', "{title}"))?></textarea>
							<textarea class="ws_form_element2" style="display: none;" data-sn-id="medium"><?=esc_html(get_option('fs_post_text_message_medium', "{title}"))?></textarea>
						</div>
						<div style="background: #FFFCF7; padding: 5px; margin-top: 5px; border: 1px solid #fceed8;">
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

			</div>
		</div>

		<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #FFAB00;">
			<button type="button" class="ws_btn ws_bg_warning saveScheduleBtn" style="width: 150px;"><?=esc_html__('ADD SCHEDULE' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('Close' , 'fs-poster')?></button>
		</div>
	</div>
</div>

<script>
	fsCode.modalWidth('<?=$mn?>' , '800px')

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .custom-messages-sn > [data-sn-id]").click(function()
		{
			if( $(this).hasClass('active-sn') )
				return;

			$("#proModal<?=$mn?> .custom-messages-sn > .active-sn").removeClass('active-sn');
			$(this).addClass('active-sn');

			var sn = $(this).data('sn-id');

			$("#proModal<?=$mn?> .social_network_custom_texts > textarea").hide();
			$("#proModal<?=$mn?> .social_network_custom_texts > [data-sn-id='" + sn + "']").fadeIn(200);
		});

		$("#proModal<?=$mn?> .schedule-mdl-tabs > div").click(function()
		{
			if( $(this).hasClass('active-tab') )
				return;

			$("#proModal<?=$mn?> .schedule-mdl-tabs > .active-tab").removeClass('active-tab');
			$(this).addClass('active-tab');

			var activeTabId = $(this).data('tab-id');console.log(activeTabId);

			$("#proModal<?=$mn?> [data-oppen-tab-id]:not([data-oppen-tab-id='\" + activeTabId + \"'])").slideUp(200);
			$("#proModal<?=$mn?> [data-oppen-tab-id='" + activeTabId + "']").slideDown(200);

		});

		$("#proModal<?=$mn?> .start_date").datepicker({
			dateFormat: "yy-mm-dd"
		});

		$("#proModal<?=$mn?> .saveScheduleBtn").click(function()
		{
			var title				=	$("#proModal<?=$mn?> .title").val(),
				startDate 			=	$("#proModal<?=$mn?> .start_date").val(),
				startTime 			=	$("#proModal<?=$mn?> .start_time").val(),
				interval 			=	$("#proModal<?=$mn?> .interval").val(),
				intervalType 		=	$("#proModal<?=$mn?> .interval_type").val(),
				share_time  		=   $("#proModal<?=$mn?> .share_time").val(),
				post_type_filter 	=   $("#proModal<?=$mn?> .post_type_filter").val(),
				category_filter		=   $("#proModal<?=$mn?> .category_filter").val(),
				post_sort			=   $("#proModal<?=$mn?> .post_sort").val(),
				post_date_filter	=   $("#proModal<?=$mn?> .post_date_filter").val(),
				custom_messages		=	{},
				accounts_list		=	[];

			if( title == '' )
			{
				fsCode.toast("<?=esc_html__('Please type the title field!' , 'fs-poster')?>" , 'danger');
				return false;
			}
			if( startDate == '' || startTime == '' )
			{
				fsCode.toast("<?=esc_html__('Please type the start date-time!' , 'fs-poster')?>" , 'danger');
				return false;
			}
			if( interval == '' || !( parseInt(interval) > 0 ) )
			{
				fsCode.toast("<?=esc_html__('Please type the interval!' , 'fs-poster')?>" , 'danger');
				return false;
			}

			$("#proModal<?=$mn?> .social_network_custom_texts > textarea").each(function()
			{
				custom_messages[ $(this).data('sn-id') ] = $(this).val();
			});

			$("#proModal<?=$mn?> #share_box1 > .fs_share_box_node").each(function()
			{
				accounts_list.push( $(this).find('input[name="share_on_nodes[]"]').val() );
			});

			<?php
			if( !get_option('fs_keep_logs', 1) )
			{
				?>

				if( post_sort == 'random2' )
				{
					fsCode.alert('You can not select "Random (no duplicates)" option. Because in your General settings "Keep shared posts log" is disabled. Please activate it firstly.');
					return false;
				}

				<?php
			}
			?>

			fsCode.ajax('schedule_save' , {
				'title':				title,
				'start_date':			startDate,
				'start_time':			startTime,
				'interval': 			(parseInt(interval) * parseInt(intervalType)) ,
				'share_time':			share_time,
				'post_type_filter':		post_type_filter,
				'category_filter':		category_filter,
				'post_sort':			post_sort,
				'post_date_filter':		post_date_filter,
				'custom_messages':		JSON.stringify(	custom_messages ),
				'accounts_list':		JSON.stringify( accounts_list )
			} , function(result)
			{
				fsCode.loading(1);
				location.reload();
			});
		});

		$("#proModal<?=$mn?> #share_box1").on('click' , '.node_remove_btn', function()
		{
			$(this).closest('.fs_share_box_node').slideUp(300 , function()
			{
				$(this).remove();
			});
		});

		$("#proModal<?=$mn?> .add_to_list_btn").click(function()
		{
			var excepts = [];
			$("#proModal<?=$mn?> #share_box1 input[name='share_on_nodes[]']").each(function()
			{
				excepts.push( $(this).val() );
			});
			fsCode.loadModal('add_node_to_list' , {'dont_show': excepts});
		});
		$("#proModal<?=$mn?> .remove_all_from_list").click(function()
		{
			fsCode.confirm("<?=esc_html__('Do you want to empty share list?' , 'fs-poster')?>" , 'danger', function()
			{
				$("#proModal<?=$mn?> #share_box1 > .fs_share_box_node").remove();
			} , true);
		});

		$("#proModal<?=$mn?> .sn_tabs > [data-tab-id]").click(function()
		{
			$("#proModal<?=$mn?> .sn_tabs > .active-sn").removeClass('active-sn');
			$(this).addClass('active-sn');

			var tab = $(this).attr('data-tab-id');

			$("#proModal<?=$mn?> #share_box1 > :not([data-tab='\"+tab+\"'])").slideUp(200);
			$("#proModal<?=$mn?> #share_box1 > [data-tab='"+tab+"']").slideDown(200);
		}).eq(0).trigger('click');

		$("#proModal<?=$mn?> .append_to_text").click(function()
		{
			var tag			= $(this).text().trim(),
				activeTab	= $("#proModal<?=$mn?> .custom-messages-sn > .active-sn").data('sn-id');

			$("#proModal<?=$mn?> .social_network_custom_texts > textarea[data-sn-id='" + activeTab + "']").append(tag);
		});
	});

	function addNodeToList( dataId, cover, name)
	{
		dataId = dataId.split(':');
		var tab         =   dataId[0],
			nodeType    =   dataId[1],
			id          =   dataId[2];

		$(".fs_share_box_items").append(
			'<div class="fs_share_box_node" data-tab="'+tab+'">'+
			'<input type="hidden" name="share_on_nodes[]" value="' + (nodeType+':'+id) + '">'+
			'<div class="node_img"><img src="'+cover+'"></div>'+
			'<div class="node_label" style="width: 100%;">'+
			'<div>'+name+'</div>'+
			'<div class="node_label_help">' + nodeType + '</div>'+
			'</div>'+
			'<div class="node_remove"><div class="node_remove_btn" type="button"><i class="fa fa-times"></i></div></div>'+
			'</div>');

		$(".sn_tabs > .active-sn").click();
	}
</script>