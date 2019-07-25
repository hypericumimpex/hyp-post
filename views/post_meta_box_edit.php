<?php
if( !defined('ABSPATH') )
{
	exit;
}

if( isset($_GET['share']) && !empty($_GET['share']) && $_GET['share'] == '1' )
{
	if( !empty($_GET['background']) )
	{
		?>
		<script>
			jQuery(document).ready(function()
			{
				fsCode.toast("<?=esc_html__('Post will be share on background!' , 'fs-poster')?>");
			});
		</script>
		<?php
	}
	else
	{
		$chechNotSendedFeeds = FSwpDB()->get_row(FSwpDB()->prepare("SELECT count(0) AS cc FROM ".FSwpTable('feeds')." WHERE post_id=%d AND is_sended=0" , [(int)$post->ID]) , ARRAY_A);
	}
}

$feeds = FSwpFetchAll('feeds' , ['post_id' => $post->ID]);
?>

<style>
	.share_box_items_edit a
	{
		text-decoration: none;
		color: #0984e3;
		outline: none !important;
		box-shadow: none !important;
		text-shadow: none !important;
	}
	.share_box_items_edit
	{
		margin-top: 10px;
		border: 1px solid #DDD;
		background: #FFF;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;

		max-height: 300px;
		overflow: auto;
	}

	.share_box_node_edit
	{
		display: flex;
		align-items: center;
		padding: 5px;
		height: 35px;
		border-bottom: 1px solid #DDD;
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
	.node_actions
	{
		width: 50px;
		height: 100%;
		background: #FFF;
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.node_actions>.status_action
	{
		width: 19px;
		line-height: 18px;
		height: 19px;
		text-align: center;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		font-size: 10px;
	}

	.node_label_help
	{
		font-size: 11px;
		color: #888;
	}
	.post_link
	{
		padding-left: 5px;
		color: #fdcb6e !important;
		cursor: pointer;
	}
</style>

<div>
	<div id="share_box1_edit">
		<div>Shared on:</div>
		<div class="share_box_items_edit">
			<?php
			foreach ($feeds AS $feedInf)
			{
				$nodeInfTable = $feedInf['node_type'] == 'account' ? 'accounts' : 'account_nodes';

				$nodeInf = FSwpFetch($nodeInfTable , $feedInf['node_id']);

				if( empty($nodeInf) )
				{
					continue;
				}

				if( $feedInf['node_type'] == 'account' )
				{
					$nodeInf['node_type'] = 'account';
				}

				?>
				<div class="share_box_node_edit">
					<div class="node_img"><img src="<?=FSprofilePic($nodeInf)?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../images/no-photo.png'?>');"></div>
					<div class="node_label" style="width: 100%;">
						<div><a href="<?=FSprofileLink($nodeInf)?>" target="_blank"><?=esc_html($nodeInf['name']);?></a></div>
						<div class="node_label_help"><?=esc_html(ucfirst($nodeInf['driver']) . ' > ' . $nodeInf['node_type']);?></div>
					</div>
					<div class="node_actions">
						<?php
						if( $feedInf['status'] == 'ok' )
						{
							if( $nodeInf['driver'] == 'google_b' )
							{
								$username = isset( $nodeInf['node_id'] ) ? $nodeInf['node_id'] : '';
							}
							else
							{
								$username = (isset($nodeInf['screen_name']) ? $nodeInf['screen_name'] : '');
							}
							?>
							<span class="ws_bg_success status_action ws_tooltip" data-title="<?=esc_html__('Posted successfully' , 'fs-poster')?>">
								<i class="fa fa-check"></i>
							</span>
							<a href="<?=FSpostLink($feedInf['driver_post_id'] , $feedInf['driver'] , $username , $feedInf['feed_type'])?>" target="_blank" class="post_link" title="<?=esc_html__('Open post in facebook!' , 'fs-poster')?>"><i class="fa fa-external-link fa-external-link-alt"></i></a>
							<?php
						}
						else if( $feedInf['is_sended'] == '0' && get_post_status($post->ID) == 'future' )
						{
							?>
							<span class="ws_bg_warning status_action ws_tooltip" data-title="<?=esc_html__('Scheduled' , 'fs-poster')?>">
								<i class="fa fa-clock"></i>
							</span>
							<?php
						}
						else if( $feedInf['is_sended'] == '0' || $feedInf['is_sended'] == '2' )
						{
							?>
							<span class="ws_bg_warning status_action ws_tooltip" data-title="<?=esc_html__('Sharing...' , 'fs-poster')?>">
								<i class="fa fa-clock"></i>
							</span>
							<?php
						}
						else
						{
							?>
							<span class="ws_bg_danger status_action ws_tooltip" data-title="<?php printf(esc_html__('Post failed! %s' , 'fs-poster') , esc_html($feedInf['error_msg']))?>">
								<i class="fa fa-exclamation-triangle"></i>
							</span>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}

			if( empty($feeds) )
			{
				print '<div style="margin: 10px; color: #b05858; text-align: center;">This post not shared any social network!</div>';
			}
			?>
		</div>
		<?php
		if( get_post_status($post->ID) == 'publish' )
		{
			?>
			<div style="margin-top: 10px; display: flex; justify-content: space-between;">
				<button type="button" class="button button-primary" style="width: 49%;" data-load-modal="share_saved_post" data-parameter-post_id="<?=$post->ID?>"><?=empty($feeds) ? 'Share' : 'Share again'?></button>
				<button type="button" class="button button-secondary" style="width: 49%; background: #e84393; color: #FFF; border: 0;" data-load-modal="plan_saved_post" data-parameter-post_id="<?=$post->ID?>">Schedule</button>
			</div>
			<?php
		}
		?>
	</div>
</div>

<script>
	jQuery(document).ready(function()
	{
		<?php
		if(isset($chechNotSendedFeeds) && $chechNotSendedFeeds['cc'] > 0)
		{
			?>
			fsCode.loadModal('share_feeds' , {'post_id': '<?=(int)$post->ID?>'})
			<?php
		}
		?>
	});
</script>
