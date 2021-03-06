<?php
if( !defined('ABSPATH') )
{
	exit;
}

if( !defined('NOT_CHECK_SP') && isset($_GET['share']) && !empty($_GET['share']) && $_GET['share'] == '1' )
{
	$chechNotSendedFeeds = FSwpDB()->get_row(FSwpDB()->prepare("SELECT count(0) AS cc FROM ".FSwpTable('feeds')." WHERE post_id=%d AND is_sended=0" , [(int)$postId]) , ARRAY_A);
}


if( isset($postId) && $postId > 0 && get_post_status() == 'draft' )
{
	$shareCheckbox						= get_post_meta($postId, '_fs_poster_share', true);

	$cm_fs_post_text_message_fb			= get_post_meta($postId, '_fs_poster_cm_fb', true);
	$cm_fs_post_text_message_twitter	= get_post_meta($postId, '_fs_poster_cm_twitter', true);
	$cm_fs_post_text_message_instagram	= get_post_meta($postId, '_fs_poster_cm_instagram', true);
	$cm_fs_post_text_message_instagram_h= get_post_meta($postId, '_fs_poster_cm_instagram_h', true);
	$cm_fs_post_text_message_linkedin	= get_post_meta($postId, '_fs_poster_cm_linkedin', true);
	$cm_fs_post_text_message_vk			= get_post_meta($postId, '_fs_poster_cm_vk', true);
	$cm_fs_post_text_message_pinterest	= get_post_meta($postId, '_fs_poster_cm_pinterest', true);
	$cm_fs_post_text_message_reddit		= get_post_meta($postId, '_fs_poster_cm_reddit', true);
	$cm_fs_post_text_message_tumblr		= get_post_meta($postId, '_fs_poster_cm_tumblr', true);
	$cm_fs_post_text_message_ok			= get_post_meta($postId, '_fs_poster_cm_ok', true);
	$cm_fs_post_text_message_google_b	= get_post_meta($postId, '_fs_poster_cm_google_b', true);
	$cm_fs_post_text_message_telegram	= get_post_meta($postId, '_fs_poster_cm_telegram', true);
	$cm_fs_post_text_message_medium		= get_post_meta($postId, '_fs_poster_cm_medium', true);

	$nodeList = get_post_meta($postId, '_fs_poster_node_list', true);
	$nodeList = is_array($nodeList) ? $nodeList : [];

	$accountsList = [];
	$nodesList = [];
	foreach( $nodeList AS $nodeInf01 )
	{
		$nodeInf01 = explode(':', $nodeInf01);

		if( count($nodeInf01) < 3 )
			continue;

		if( $nodeInf01[1] == 'account' )
		{
			$accountsList[] = (int)$nodeInf01[2];
		}
		else
		{
			$nodesList[] = (int)$nodeInf01[2];
		}
	}

	if( empty($accountsList) )
	{
		$accounts = [];
	}
	else
	{
		$accountsList = "'" . implode("','", $accountsList) . "'";

		$accounts = FSwpDB()->get_results(
			"SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM ".FSwpDB()->base_prefix."terms WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type FROM ".FSwpTable('account_status')." tb1
			LEFT JOIN ".FSwpTable('accounts')." tb2 ON tb2.id=tb1.account_id
			WHERE tb1.account_id IN ({$accountsList}) AND tb1.user_id='" . (int)get_current_user_id() . "'
			ORDER BY name"
			, ARRAY_A
		);
	}

	if( empty($nodesList) )
	{
		$activeNodes = [];
	}
	else
	{
		$nodesList = "'" . implode("','", $nodesList) . "'";

		$activeNodes = FSwpDB()->get_results(
			"
			SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM ".FSwpDB()->base_prefix."terms WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name FROM ".FSwpTable('account_node_status')." tb1
			LEFT JOIN ".FSwpTable('account_nodes')." tb2 ON tb2.id=tb1.node_id
			WHERE tb1.node_id IN ({$nodesList}) AND tb1.user_id='" . (int)get_current_user_id() . "'
			ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name"
			, ARRAY_A
		);
	}

	$activeNodes = array_merge($accounts , $activeNodes);
}
else
{
	$shareCheckbox						= get_option('fs_auto_share_new_posts', '1') || FS_get('page')=='fs-poster-share' || FS_post('post_id', null) !== null;

	$cm_fs_post_text_message_fb				= get_option('fs_post_text_message_fb');
	$cm_fs_post_text_message_twitter		= get_option('fs_post_text_message_twitter');
	$cm_fs_post_text_message_instagram		= get_option('fs_post_text_message_instagram');
	$cm_fs_post_text_message_instagram_h	= get_option('fs_post_text_message_instagram_h');
	$cm_fs_post_text_message_linkedin		= get_option('fs_post_text_message_linkedin');
	$cm_fs_post_text_message_vk				= get_option('fs_post_text_message_vk');
	$cm_fs_post_text_message_pinterest		= get_option('fs_post_text_message_pinterest');
	$cm_fs_post_text_message_reddit			= get_option('fs_post_text_message_reddit');
	$cm_fs_post_text_message_tumblr			= get_option('fs_post_text_message_tumblr');
	$cm_fs_post_text_message_ok				= get_option('fs_post_text_message_ok');
	$cm_fs_post_text_message_google_b		= get_option('fs_post_text_message_google_b');
	$cm_fs_post_text_message_telegram		= get_option('fs_post_text_message_telegram');
	$cm_fs_post_text_message_medium			= get_option('fs_post_text_message_medium');

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
}

?>

<style>

	.fs_share_box_items
	{
		max-height: 150px;
		min-height: 30px;
		overflow: auto;
		border: 1px solid #DDD;
		background: #FFF;
		position: relative;
	}

	.fs_share_box_items:before
	{
		content: 'No active account found!';
		position: absolute;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		margin: auto;
		z-index: 1;
		text-align: center;
		width: 100%;
		height: 20px;
		color: #999;
	}

	.fs_share_box_node
	{
		display: flex;
		align-items: center;
		padding: 5px;
		height: 35px;
		border-bottom: 1px solid #DDD;
		position: relative;
		z-index: 2;
		background: #FFF;
	}
	.fs_share_box_node:last-child
	{
		border-bottom: 0 !important;
	}
	.node_img
	{
		width: 30px;
	}
	.node_img>img
	{
		width: 30px;
		height: 30px;
		-webkit-border-radius: 30px;
		-moz-border-radius: 30px;
		border-radius: 30px;
	}
	.node_label
	{
		margin-left: 10px;
		font-size: 14px;
		overflow: hidden;
		white-space: nowrap;
	}
	.node_remove
	{
		width: 50px;
		height: 100%;
		position: relative;
		background: #FFF;
	}
	.node_remove>.node_remove_btn
	{
		position: absolute;
		top: 0;
		bottom: 0;
		margin: auto;
		height: 20px;
		right: 15px;
		color: #ffb9b7;
		-webkit-border-radius: 15px;
		-moz-border-radius: 15px;
		border-radius: 15px;
		cursor: pointer;
	}

	.node_label_help
	{
		font-size: 11px;
		color: #888;
	}
	.sn_tabs
	{
		margin-left: 10px;
		margin-top: 10px;
		margin-bottom: -1px;
		overflow: auto;
		position: relative;
	}
	.sn_tabs > .sb_tab
	{
		display: inline-block;
		color: #777;
		width: 24px;
		height: 24px;
		border: 1px solid #bdc3c7;

		text-align: center;
		vertical-align: top;
		line-height: 22px;

		margin-right: 2px;
		margin-bottom: 5px;
		cursor: pointer;
		color: #bdc3c7;

		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
	}

	.sn_tabs > .active_tab
	{
		background: #ff7675;
		color: #FFF;
		border-color: #ff7675;
	}



	#custom_messages
	{
		padding-left: 10px;
		margin-top: 5px;
	}

	#custom_messages .fs_cm_d1
	{
		color: #fd79a8;
		font-weight: 600;
		font-size: 12px;
	}

	#custom_messages .fs_cm_d2
	{
		display: none;
		margin-top: 5px;
	}

	#custom_messages > [data-tab]:not(:first-child)
	{
		display: none;
	}

	#custom_messages .fs_cm_d2 span
	{
		color: #999;
		font-size: 12px;
		font-style: italic;
	}
</style>

<div>

	<div>
		<div style="display: flex; align-items: center; justify-content: space-between;">
			<div class="share_switch" style="display: flex; align-items: center; padding: 10px;">
				<div style="margin-right: 10px;"><?=__('Share', 'fs-poster')?>:</div>
				<div>
					<div class="fs_onoffswitch fs_onoffswitch_small">
						<input type="hidden" name="share_checked" value="off">
						<input type="checkbox" name="share_checked" class="fs_onoffswitch-checkbox" id="shareCheckbox"<?=$shareCheckbox?' checked':' '?>>
						<label class="fs_onoffswitch-label" for="shareCheckbox"></label>
					</div>
				</div>
			</div>
		</div>
		<div class="sn_tabs share_box_sh">
			<div data-tab-id="all" class="sb_tab active_tab" style="font-size: 10px; font-weight: 800; letter-spacing: 0.1em;">all</div>
			<div data-tab-id="fb" class="sb_tab"><i class="fab fa-facebook-square "></i></div>
			<div data-tab-id="twitter" class="sb_tab"><i class="fab fa-twitter-square "></i></div>
			<div data-tab-id="instagram" class="sb_tab"><i class="fab fa-instagram "></i></div>
			<div data-tab-id="linkedin" class="sb_tab"><i class="fab fa-linkedin "></i></div>
			<div data-tab-id="vk" class="sb_tab"><i class="fab fa-vk "></i></div>
			<div data-tab-id="pinterest" class="sb_tab"><i class="fab fa-pinterest "></i></div>
			<div data-tab-id="reddit" class="sb_tab"><i class="fab fa-reddit "></i></div>
			<div data-tab-id="tumblr" class="sb_tab"><i class="fab fa-tumblr "></i></div>
			<div data-tab-id="ok" class="sb_tab"><i class="fab fa-odnoklassniki"></i></div>
			<div data-tab-id="google_b" class="sb_tab"><i class="fab fa-google"></i></div>
			<div data-tab-id="telegram" class="sb_tab"><i class="fab fa-telegram"></i></div>
			<div data-tab-id="medium" class="sb_tab"><i class="fab fa-medium"></i></div>
		</div>
		<div class="fs_share_box_items share_box_sh" id="share_box1">
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
					<input type="hidden" name="share_on_nodes[]" value="<?=$nodeInf['driver'].':'.$nodeInf['node_type'].':'.$nodeInf['id'].':'.htmlspecialchars($nodeInf['filter_type']).':'.htmlspecialchars($nodeInf['categories'])?>">
					<div class="node_img"><img src="<?=$coverPhoto?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../images/no-photo.png'?>');"></div>
					<div class="node_label" style="width: 100%;">
						<div>
							<?=esc_html($nodeInf['name']);?>
							<a href="<?=FSprofileLink($nodeInf)?>" target="_blank" class="ws_btn ws_tooltip" data-title="Profile link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
						</div>
						<div class="node_label_help"><i class="<?=FSsocialIcon($nodeInf['driver'])?>"></i> <?=ucfirst($nodeInf['driver'])?> <i class="fa fa-chevron-right " style="font-size: 10px; color: #CCC;"></i> <?=esc_html($nodeInf['node_type']);?> <?=empty($titleText) ? '' : '<i class="fa fa-filter" title="'.$titleText.'" style="padding-left: 5px; color: #fdcb6e;"></i>'?></div>
					</div>
					<div class="node_remove"><div class="node_remove_btn" type="button"><i class="fa fa-times"></i></div></div>
				</div>
				<?php
			}
			?>
		</div>
		<div style="display: flex; justify-content: space-between; margin-top: 5px;" class="share_box_sh">
			<button type="button" class="ws_bg_light ws_btn ws_btn_small add_to_list_btn" style="height: 28px !important; padding: 0 8px !important;">+ add</button>
			<button type="button" class="ws_bg_danger ws_btn ws_btn_small remove_all_from_list" style="height: 28px !important; padding: 0 8px !important;">clear</button>
		</div>
		<div id="custom_messages" class="share_box_sh">
			<div data-tab="fb">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize FB post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_fb"><?=htmlspecialchars($cm_fs_post_text_message_fb)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="twitter">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Twitter post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_twitter"><?=htmlspecialchars($cm_fs_post_text_message_twitter)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="instagram">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Instagram post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2">
					<label style="display: block;">Instagram Post message:</label>
					<textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_instagram"><?=htmlspecialchars($cm_fs_post_text_message_instagram)?></textarea>
					<span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span>
					<label style="display: block;">Instagram History title:</label>
					<textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_instagram_h"><?=htmlspecialchars($cm_fs_post_text_message_instagram_h)?></textarea>
					<span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span>
				</div>
			</div>
			<div data-tab="linkedin">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Linkedin post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_linkedin"><?=htmlspecialchars($cm_fs_post_text_message_linkedin)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="vk">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize VK post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_vk"><?=htmlspecialchars($cm_fs_post_text_message_vk)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="pinterest">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Pinterest post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_pinterest"><?=htmlspecialchars($cm_fs_post_text_message_pinterest)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="reddit">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Reddit post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_reddit"><?=htmlspecialchars($cm_fs_post_text_message_reddit)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="tumblr">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Tumblr post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_tumblr"><?=htmlspecialchars($cm_fs_post_text_message_tumblr)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="ok">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize OK.ru post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_ok"><?=htmlspecialchars($cm_fs_post_text_message_ok)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="google_b">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Google MyBusiness post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_google_b"><?=htmlspecialchars($cm_fs_post_text_message_google_b)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="telegram">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Telegram post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_telegram"><?=htmlspecialchars($cm_fs_post_text_message_telegram)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="medium">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Medium post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="fs_post_text_message_medium"><?=htmlspecialchars($cm_fs_post_text_message_medium)?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function()
	{
		$("#custom_messages .fs_cm_d1").click(function()
		{
			$(this).next().slideToggle(200);

			if( $(this).find('i.fa-angle-right').length)
			{
				$(this).find('i.fa-angle-right').removeClass('fa-angle-right').addClass('fa-angle-down');
			}
			else
			{
				$(this).find('i.fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-right');
			}
		});

		$(".sn_tabs > .sb_tab").click(function()
		{
			if( $(this).hasClass('active_tab') )
				return;

			$(".sn_tabs > .active_tab").removeClass('active_tab');
			$(this).addClass('active_tab');

			var tab = $(this).attr('data-tab-id');

			if( tab == 'all' )
			{
				$("#share_box1 > [data-tab]").slideDown(200);

				$("#custom_messages > [data-tab]").hide();
			}
			else
			{
				$("#share_box1 > :not([data-tab='\"+tab+\"'])").slideUp(200);
				$("#share_box1 > [data-tab='"+tab+"']").slideDown(200);

				$("#custom_messages > [data-tab]").hide();

				$("#custom_messages > [data-tab='"+tab+"']").show();
			}

		}).eq(0).trigger('click');

		$("#shareCheckbox").change(function()
		{
			if( $(this).is(':checked') )
			{
				$(".share_box_sh").show(500);
			}
			else
			{
				$(".share_box_sh").hide(500);
			}
		}).trigger('change');

		$("#share_box1").on('click' , '.node_remove_btn', function()
		{
			$(this).closest('.fs_share_box_node').slideUp(500 , function()
			{
				$(this).remove();
			});
		});

		$(".add_to_list_btn").click(function()
		{
			var excepts = [];
			$("#share_box1 input[name='share_on_nodes[]']").each(function()
			{
				excepts.push( $(this).val() );
			});
			fsCode.loadModal('add_node_to_list' , {'dont_show': excepts});
		});
		$(".remove_all_from_list").click(function()
		{
			fsCode.confirm("<?=esc_html__('Do you want to empty share list?' , 'fs-poster')?>" , 'danger', function()
			{
				$("#share_box1 > .fs_share_box_node").remove();
			} , true);
		});
		<?php
		if(!defined('NOT_CHECK_SP') && isset($chechNotSendedFeeds) && $chechNotSendedFeeds['cc'] > 0)
		{
			?>
			fsCode.loadModal('share_feeds' , {'post_id': '<?=(int)$postId?>'})
			<?php
		}

		if( (int)get_option('fs_share_on_background' , '1') == 0 && get_post_status() != 'publish' )
		{
			?>
			if( $('.block-editor__container').length && !location.href.match(/post\.php\?post\=([0-9]+)/) )
			{
				var publishHook = setInterval(function()
				{
					var newUrl = location.href,
						check = newUrl.match(/post\.php\?post\=([0-9]+)/);

					if( check )
					{
						var postId = check[1];

						setTimeout(function()
						{
							fsCode.ajax('check_post_is_published', {'id': postId}, function(result)
							{
								if( result['post_status'] )
								{
									fsCode.loadModal('share_feeds' , {'post_id': postId, 'dont_reload':'1'});
								}
							});
						}, 1500);

						clearInterval(publishHook);
					}
				}, 1000);
			}
			<?php
		}
		?>
	});

	function addNodeToList( dataId, cover, name)
	{
		dataId = dataId.split(':');
		var tab         =   dataId[0],
			nodeType    =   dataId[1],
			id          =   dataId[2],
			tabName		=	tab.charAt(0).toUpperCase() + tab.slice(1),
			dIcon;

		switch( tab )
		{
			case 'fb':
				dIcon = "fab fa-facebook-square";
				break;
			case 'twitter':
			case 'tumblr':
				dIcon = "fab fa-"+tab+"-square";
				break;

			case 'instagram':
			case 'vk':
			case 'linkedin':
			case 'pinterest':
			case 'reddit':
			case 'medium':
			case 'telegram':
				dIcon = "fab fa-" + tab;
				break;
			case 'ok':
				dIcon = "fab fa-odnoklassniki";
				break;
			case 'google_b':
				dIcon = "fab fa-google";
				break;
		}

		$(".fs_share_box_items").append(
				'<div class="fs_share_box_node" data-tab="'+tab+'">'+
					'<input type="hidden" name="share_on_nodes[]" value="' + dataId.join(':') + '">'+
					'<div class="node_img"><img src="'+cover+'"></div>'+
					'<div class="node_label" style="width: 100%;">'+
						'<div>'+name+'</div>'+
						'<div class="node_label_help"><i class="'+ dIcon +'"></i> ' + tabName + ' <i class="fa fa-chevron-right " style="font-size: 10px; color: #CCC;"></i> ' + nodeType + '</div>'+
					'</div>'+
					'<div class="node_remove"><div class="node_remove_btn" type="button"><i class="fa fa-times"></i></div></div>'+
				'</div>');

		$(".sn_tabs > .active_tab").click();
	}

</script>
