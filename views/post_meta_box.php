<?php
if( !defined('ABSPATH') )
{
	exit;
}

$accounts = wpDB()->get_results(
	wpDB()->prepare("
		SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM ".wpDB()->base_prefix."terms WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name,'account' AS node_type FROM ".wpTable('account_status')." tb1
		LEFT JOIN ".wpTable('accounts')." tb2 ON tb2.id=tb1.account_id
		WHERE tb1.user_id=%d
		ORDER BY name" , [ get_current_user_id() ])
	, ARRAY_A
);

$activeNodes = wpDB()->get_results(
	wpDB()->prepare("
	SELECT tb2.*, tb1.filter_type, tb1.categories, (SELECT GROUP_CONCAT(`name`) FROM ".wpDB()->base_prefix."terms WHERE FIND_IN_SET(term_id,tb1.categories) ) AS categories_name FROM ".wpTable('account_node_status')." tb1
	LEFT JOIN ".wpTable('account_nodes')." tb2 ON tb2.id=tb1.node_id
	WHERE tb1.user_id=%d
	ORDER BY (CASE node_type WHEN 'ownpage' THEN 1 WHEN 'group' THEN 2 WHEN 'page' THEN 3 END), name" , [ get_current_user_id() ])
	, ARRAY_A
);

$activeNodes = array_merge($accounts , $activeNodes);

if( !defined('NOT_CHECK_SP') && isset($_GET['share']) && !empty($_GET['share']) && $_GET['share'] == '1' )
{
	$chechNotSendedFeeds = wpDB()->get_row(wpDB()->prepare("SELECT count(0) AS cc FROM ".wpTable('feeds')." WHERE post_id=%d AND is_sended=0" , [(int)$postId]) , ARRAY_A);
}

?>

<style>
	.onoffswitch
	{
		position: relative;
		width: 35px;
		-webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
	}
	.onoffswitch-checkbox
	{
		display: none !important;
	}
	.onoffswitch-label
	{
		display: block;
		overflow: hidden;
		cursor: pointer;
		height: 15px;
		padding: 0;
		line-height: 15px;
		border: 0px solid #FFFFFF;
		border-radius: 15px;
		background-color: #9E9E9E;
		transition: background-color 0.3s ease-in;
	}
	.onoffswitch-label:before
	{
		content: "";
		display: block;
		width: 20px;
		margin: -3px;
		background: #FFFFFF;
		position: absolute;
		top: 0;
		bottom: 0;
		right: 18px;
		border-radius: 20px;
		box-shadow: 0px 0px 5px 0px #DDD;
		transition: all 0.3s ease-in 0s;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label
	{
		background-color: #74B9FF;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label, .onoffswitch-checkbox:checked + .onoffswitch-label:before
	{
		border-color: #74B9FF;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner
	{
		margin-left: 0;
	}
	.onoffswitch-checkbox:checked + .onoffswitch-label:before
	{
		right: 0px;
		background-color: #2196F3;
		box-shadow: 0px 0px 5px 0px rgba(0, 0, 0, 0.2);
	}


	.share_box_items
	{
		max-height: 150px;
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

	.share_box_node
	{
		display: flex;
		align-items: center;
		padding: 5px;
		height: 35px;
		border-bottom: 1px solid #DDD;
	}
	.share_box_node:last-child
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
	.share_box_node:hover .node_remove>.node_remove_btn
	{
		display: block;
	}
	.node_label_help
	{
		font-size: 11px;
		color: #888;
	}
	.sn_tabs
	{
		display: flex;
		margin-left: 10px;
		margin-top: 10px;
		margin-bottom: -1px;
	}
	.sn_tabs > .sb_tab
	{
		font-size: 17px;
		color: #777;
		width: 32px;
		height: 26px;
		border-top: 1px solid #DDD;
		border-left: 1px solid #DDD;
		border-right: 1px solid #DDD;
		border-top-left-radius: 5px;
		border-top-right-radius: 5px;
		display: flex;
		justify-content: center;
		align-items: center;
		margin-right: 5px;
		cursor: pointer;
		box-shadow: inset 0px 2px 2px 0px #EEE;
		color: #7f96ad;
	}
	.sn_tabs > .active_tab
	{
		border-top: 2px solid #ff7675;
		border-bottom: 1px solid #FFFFFF;
	}

	.sn_tabs > .add_to_list_btn
	{
		margin-right: 10px;
		display: flex;
		justify-content: center;
		align-items: center;
		width: 20px;
		margin-left: auto;
		cursor: pointer;
		color: #777;
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
					<div class="onoffswitch">
						<input type="hidden" name="share_checked" value="off">
						<input type="checkbox" name="share_checked" class="onoffswitch-checkbox" id="shareCheckbox" checked>
						<label class="onoffswitch-label" for="shareCheckbox"></label>
					</div>
				</div>
			</div>
		</div>
		<div class="sn_tabs share_box_sh">
			<div data-tab-id="fb" class="sb_tab active_tab"><i class="fab fa-facebook-square "></i></div>
			<div data-tab-id="twitter" class="sb_tab"><i class="fab fa-twitter-square "></i></div>
			<div data-tab-id="instagram" class="sb_tab"><i class="fab fa-instagram "></i></div>
			<div data-tab-id="linkedin" class="sb_tab"><i class="fab fa-linkedin "></i></div>
			<div data-tab-id="vk" class="sb_tab"><i class="fab fa-vk "></i></div>
			<div data-tab-id="pinterest" class="sb_tab"><i class="fab fa-pinterest "></i></div>
			<div data-tab-id="reddit" class="sb_tab"><i class="fab fa-reddit "></i></div>
			<div data-tab-id="tumblr" class="sb_tab"><i class="fab fa-tumblr "></i></div>
			<div data-tab-id="google" class="sb_tab"><i class="fab fa-google "></i></div>

			<div class="add_to_list_btn"><i class="fa fa-plus"></i></div>
		</div>
		<div class="share_box_items share_box_sh" id="share_box1">
			<?php
			foreach ($activeNodes AS $nodeInf)
			{
				$coverPhoto = profilePic($nodeInf);
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
				<div class="share_box_node" data-tab="<?=$nodeInf['driver']?>">
					<input type="hidden" name="share_on_nodes[]" value="<?=$nodeInf['driver'].':'.$nodeInf['node_type'].':'.$nodeInf['id'].':'.htmlspecialchars($nodeInf['filter_type']).':'.htmlspecialchars($nodeInf['categories'])?>">
					<div class="node_img"><img src="<?=$coverPhoto?>"></div>
					<div class="node_label" style="width: 100%;">
						<div>
							<?=esc_html($nodeInf['name']);?>
							<a href="<?=profileLink($nodeInf)?>" target="_blank" class="ws_btn" title="Profile link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
						</div>
						<div class="node_label_help"><?=esc_html($nodeInf['node_type']);?> <?=empty($titleText) ? '' : '<i class="fa fa-filter" title="'.$titleText.'" style="padding-left: 5px; color: #fdcb6e;"></i>'?></div>
					</div>
					<div class="node_remove"><div class="node_remove_btn" type="button"><i class="fa fa-times"></i></div></div>
				</div>
				<?php
			}
			?>
		</div>
		<div id="custom_messages">
			<div data-tab="fb">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize FB post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_fb"><?=htmlspecialchars(get_option('post_text_message_fb'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="twitter">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Twitter post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_twitter"><?=htmlspecialchars(get_option('post_text_message_twitter'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="instagram">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Instagram post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_instagram"><?=htmlspecialchars(get_option('post_text_message_instagram'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="linkedin">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Linkedin post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_linkedin"><?=htmlspecialchars(get_option('post_text_message_linkedin'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="vk">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize VK post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_vk"><?=htmlspecialchars(get_option('post_text_message_vk'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="pinterest">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Pinterest post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_pinterest"><?=htmlspecialchars(get_option('post_text_message_pinterest'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="reddit">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Reddit post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_reddit"><?=htmlspecialchars(get_option('post_text_message_reddit'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="tumblr">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Thumblr post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_thumblr"><?=htmlspecialchars(get_option('post_text_message_thumblr'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
			</div>
			<div data-tab="google">
				<div class="fs_cm_d1"><label><i class="fa fa-angle-right"></i> <?=__('Customize Google+ post message' , 'fs-poster')?></label></div>
				<div class="fs_cm_d2"><textarea class="ws_form_element2" maxlength="2000" name="post_text_message_google"><?=htmlspecialchars(get_option('post_text_message_google'))?></textarea><span><?=__('Max length: 2000 symbol' , 'fs-poster')?></span></div>
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
			$(".sn_tabs > .active_tab").removeClass('active_tab');
			$(this).addClass('active_tab');

			var tab = $(this).attr('data-tab-id');

			$("#share_box1 > :not([data-tab='\"+tab+\"'])").hide(200);
			$("#share_box1 > [data-tab='"+tab+"']").show(200);

			$("#custom_messages > [data-tab]").hide();

			$("#custom_messages > [data-tab='"+tab+"']").show();
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
			var box = $(this).closest('.share_box_node');
			fsCode.confirm("<?=esc_html__('Do you want to remove the \'community\'?' , 'fs-poster')?>" , 'danger', function()
			{
				box.hide(500 , function()
				{
					$(this).remove();
				});
			} , true);
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
		<?php
		if(!defined('NOT_CHECK_SP') && isset($chechNotSendedFeeds) && $chechNotSendedFeeds['cc'] > 0)
		{
			?>
			fsCode.loadModal('share_feeds' , {'post_id': '<?=(int)$postId?>'})
			<?php
		}
		?>
	});

	function addNodeToList( dataId, cover, name)
	{
		dataId = dataId.split(':');
		var tab         =   dataId[0]
			nodeType    =   dataId[1],
			id          =   dataId[2];

		$(".share_box_items").append(
				'<div class="share_box_node" data-tab="'+tab+'">'+
					'<input type="hidden" name="share_on_nodes[]" value="' + dataId.join(':') + '">'+
					'<div class="node_img"><img src="'+cover+'"></div>'+
					'<div class="node_label" style="width: 100%;">'+
						'<div>'+name+'</div>'+
						'<div class="node_label_help">' + nodeType + '</div>'+
					'</div>'+
					'<div class="node_remove"><div class="node_remove_btn" type="button"><i class="fa fa-times"></i></div></div>'+
				'</div>');

		$(".sn_tabs > .active_tab").click();
	}
</script>
