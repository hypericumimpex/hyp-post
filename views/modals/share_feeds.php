<?php defined('MODAL') or exit();?>

<style>
	.feeds_table
	{
		width: 100%;
		border-collapse: separate;
		border: 0px;
		border-spacing: 0px 5px;
	}
	.feeds_table>thead>tr>th
	{
		background: #EFF7F7;
		text-align: left;
		height: 35px;
		padding-left: 20px;
		padding-right: 20px;
		color: #93979C;
		font-size: 12px;
		font-weight: 700;
	}

	.feeds_table>tbody>tr>td
	{
		padding: 7px 10px;
		border-bottom: 1px solid #EEE;
	}
	.feeds_table .node_name
	{
		font-size: 14px;
		font-weight: 600;
		color: #999;
		display: flex;
		align-items: center;
	}
	.popup_background
	{
		background-image: url('<?=plugin_dir_url(__FILE__) . '../../images/modal_bg1.png'?>');
		background-repeat: no-repeat;
		background-size: 100% auto;
		position: absolute;
		top: 0;
		width: 100%;
		left: 0;
		height: 200px;
		border-top-left-radius: 10px;
		border-top-right-radius: 10px;
		background-position: center;
	}

	.process_text
	{
		font-size: 15px;
		font-weight: 600;
		text-align: center;
		color: #888;
		padding-bottom: 10px;
	}

	.link_td a
	{
		text-decoration: none;
		color: #ff7675;
		font-weight: 600;
	}
</style>


<div style="padding: 40px; padding-bottom: 10px;">

	<div class="popup_background">

	</div>

	<div style="margin-top: 190px;">
		<div class="process_text"><?=esc_html__('Posting feeds...' , 'fs-poster')?> ( <span id="finished_count">0</span> / <?=count($parameters['feeds'])?> )</div>
		<div style="position: relative; margin-top: 10px; height: 10px; -webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px; width: 100%; background: #EEE;">
			<div id="share_progress_bar" style="position: absolute; top: 0; left: 0; width: 50%; height: 100%; -webkit-border-radius: 10px;-moz-border-radius: 10px;border-radius: 10px;" class="ws_bg_success"></div>
		</div>
	</div>

	<div style="margin-top: 20px; max-height: 300px; overflow: auto; padding-bottom: 30px;" >
		<table class="feeds_table" id="share_table">
			<thead>
			<tr>
				<th><?=esc_html__('Community' , 'fs-poster')?></th>
				<th style="text-align: center; width: 120px;"><?=esc_html__('Link' , 'fs-poster')?></th>
				<th style="text-align: center; width: 140px;"><?=esc_html__('Status' , 'fs-poster')?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($parameters['feeds'] AS $feedInf)
			{
				$nodeInfTable = $feedInf['node_type'] == 'account' ? 'accounts' : 'account_nodes';
				$nodeIdKey = $feedInf['node_type'] == 'account' ? 'profile_id' : 'node_id';
				$nodeInf = wpFetch($nodeInfTable , $feedInf['node_id']);

				if( !$nodeInf )
					continue;

				$username = isset($nodeInf['screen_name']) ? $nodeInf['screen_name'] : (isset($nodeInf['username']) ? $nodeInf['username'] : ' - ');
				?>
				<tr data-id="<?=(int)$feedInf['id']?>" data-interval="<?=(int)$feedInf['interval']?>" data-status="<?=(int)$feedInf['is_sended']?>">
					<td class="node_name">
						<div><img class="ws_img_style" src="<?=profilePic($nodeInf)?>"></div>
						<div>
							<div style="color: #888;">
								<?=esc_html($nodeInf['name'])?>
								<a href="<?=profileLink($nodeInf)?>" target="_blank" class="ws_btn" title="Profile link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
							</div>
							<div style="font-size: 11px;color: #999;">
								<?=esc_html(isset($nodeInf['driver'])?ucfirst(esc_html($nodeInf['driver'])):'Fb') .
								   ' <i class="fa fa-chevron-right " style="font-size: 10px; color: #CCC;"></i> ' .
								   esc_html($feedInf['node_type']) .
								   ( $feedInf['feed_type'] != '' ? ' <i class="fa fa-chevron-right " style="font-size: 10px; color: #CCC;"></i> ' . ucfirst(esc_html($feedInf['feed_type'])) : '' )
								?></div>
						</div>
					</td>
					<td class="link_td">
						<?php
						if( !empty($feedInf['driver_post_id']) )
						{
							?>
							<a href="<?=postLink($nodeInf['driver_post_id'] , (isset($nodeInf['driver']) ? $nodeInf['driver'] : 'fb') , $username)?>" target="_blank"><i class="fa fa-external-link fa-external-link-alt"></i> <?=esc_html__('Post link' , 'fs-poster')?></a>
						<?php
						}
						?>
					</td>
					<td align="center" class="status_td">
						<?php
						if( $feedInf['is_sended']=='1' && $feedInf['status'] != 'ok' )
						{
							?>
							<div class="ws_bg_danger ws_tooltip" data-title="<?=esc_html($feedInf['error_msg'])?>" data-float="left" style="width: 55px; padding: 0px 10px; padding-bottom: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;"><?=esc_html__('error' , 'fs-poster')?> <i class="fa fa-info-circle"></i></div>
							<?php
						}
						else if( $feedInf['is_sended']=='1' && $feedInf['status'] == 'ok' )
						{
							?>
							<div class="ws_bg_success" style="width: 55px; padding: 0px 10px; padding-bottom: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;"><?=esc_html__('ok' , 'fs-poster')?></div>
							<?php
						}
						?>

					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</div>
</div>

<script>
	fsCode.modalWidth(<?=$mn?>, 60);
	jQuery(document).ready(function()
	{
		function reloadStats()
		{
			var sendedCount = $("#share_table>tbody>tr[data-status=1]").length;
			$("#finished_count").text(sendedCount);

			var percent = parseInt( sendedCount / '<?=count($parameters['feeds'])?>' * 100 );
			$("#share_progress_bar").css('width' , percent + '%');
		}
		reloadStats();

		function sendNext()
		{
			var next = $("#share_table>tbody>tr[data-status=0]:eq(0)");

			next.find('.status_td').html('<div class="ws_bg_warning" style="width: 55px; padding: 0px 10px; padding-bottom: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;">'+"<?=esc_html__('sending...' , 'fs-poster')?>"+'</div>');

			fsCode.ajax('share_post' , {'id': next.attr('data-id')}, function(result)
			{
				next.attr('data-status' , '1');
				if( result['result']['status'] == 'ok' )
				{
					next.find('.status_td').html('<div class="ws_bg_success" style="width: 55px; padding: 0px 10px; padding-bottom: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;">'+"<?=esc_html__('ok' , 'fs-poster')?>"+'</div>');
					next.find('.link_td').html('<a href="'+result['result']['post_link']+'" target="_blank"><i class="fa fa-external-link fa-external-link-alt"></i> Post link</a>');
				}
				else
				{
					next.find('.status_td').html('<div class="ws_bg_danger ws_tooltip" data-title="'+result['result']['error_msg']+'" data-float="left" style="width: 55px; padding: 0px 10px; padding-bottom: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;">'+"<?=esc_html__('error' , 'fs-poster')?>"+' <i class="fa fa-info-circle"></i></div>');
				}

				if( $("#share_table>tbody>tr[data-status=0]").length > 0 )
				{
					$("#share_table>tbody>tr[data-status=0]:eq(0)").find('.status_td').html('<div class="ws_bg_warning" style="width: 55px; padding: 0px 10px; padding-bottom: 3px; -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;">'+"<?=esc_html__('waiting interval...' , 'fs-poster')?>"+'</div>');

					setTimeout(sendNext , parseInt($("#share_table>tbody>tr[data-status=0]:eq(0)").attr('data-interval')) * 1000 );
				}
				else
				{
					$(".process_text").html('<span class="ws_color_success">'+"<?=esc_html__('Process Finished!' , 'fs-poster')?>"+'</span><span><button type="button" data-modal-close="true" onclick="<?=isset($parameters['dont_reload'])?'':'location.reload();'?>" class="ws_btn ws_color_danger">'+"<?=esc_html__('Close window' , 'fs-poster')?>"+'</button></span>');
				}

				reloadStats();
			} , true);

		}
		sendNext();
	});
</script>
