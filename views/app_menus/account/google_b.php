<?php
if ( ! defined( 'ABSPATH' ) )
{
	exit;
}

$accountsList = FSwpDB()->get_results(FSwpDB()->prepare("
	SELECT 
		*,
		(SELECT COUNT(0) FROM ".FSwpTable('account_nodes')." WHERE account_id=tb1.id) locations,
		(SELECT filter_type FROM ".FSwpTable('account_status')." WHERE account_id=tb1.id AND user_id=%d) is_active 
	FROM ".FSwpTable('accounts')." tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='google_b'",  [get_current_user_id(), get_current_user_id()]) , ARRAY_A);

$collectMyAccountIDs = [];
foreach ( $accountsList AS $accountInf1 )
{
	$collectMyAccountIDs[] = (int)$accountInf1['id'];
}

$publicCommunities = FSwpDB()->get_results(FSwpDB()->prepare("
	SELECT 
		*,
		(SELECT filter_type FROM ".FSwpTable('account_node_status')." WHERE node_id=tb1.id AND user_id=%d) is_active
	FROM ".FSwpTable('account_nodes')." tb1
	WHERE driver='google_b' AND (user_id=%d OR is_public=1) AND account_id NOT IN ('" . implode("','", $collectMyAccountIDs) . "')",  [get_current_user_id() , get_current_user_id() ]) , ARRAY_A);

?>

<div class="fs_accounts_m_header">
	<button type="button" class="ws_btn ws_bg_light ws_btn_small ws_tooltip fs_expand_all_btn" data-title="Expand all"><i class="fa fa-plus"></i></button>
	<button type="button" class="ws_btn ws_bg_light ws_btn_small ws_tooltip fs_collapse_all_btn" data-title="Collapse all"><i class="fa fa-minus"></i></button>
	<span class="fs_accounts_count"><span id="accounts_count"><?php print count($accountsList);?></span> <?=esc_html__('Google account added', 'fs-poster')?></span>
	<button type="button" class="ws_btn ws_bg_dark" style="float: right;" data-load-modal="add_google_b_account"><i class="fa fa-plus"></i> <?=esc_html__('ADD ACCOUNT', 'fs-poster')?></button>
</div>

<div class="ws_table_wraper">
	<table class="ws_table">
		<thead>
		<tr>
			<th style="width: 10px;"></th>
			<th><?=esc_html__('NAME', 'fs-poster')?> <i class="fa fa-caret-down"></i></th>
			<th style="width: 15%;"><?=__('MAKE PUBLIC' , 'fs-poster')?> <i style="color: #ff9c97;" class="fa fa-question-circle ws_tooltip" data-title="<?=__('If you would like to allow do publications for other WordPress Users in this profile/community, active the MAKE PUBLIC checkbox.' , 'fs-poster')?>"></i></th>
			<th style="width: 15%;"><?=esc_html__('SHARE', 'fs-poster')?> <i style="color: #ff9c97;" class="fa fa-question-circle ws_tooltip" data-title="<?=__('If you would like to happen your publications in this profile/community automatically, active the SHARE checkbox.' , 'fs-poster')?>"></i></th>
		</tr>
		</thead>
	</table>

	<?php
	foreach($accountsList AS $accountInf)
	{
		$nodeList = FSwpDB()->get_results(FSwpDB()->prepare("
			SELECT 
				*,
				(SELECT filter_type FROM ".FSwpTable('account_node_status')." WHERE node_id=tb1.id AND user_id=%d) is_active
			FROM ".FSwpTable('account_nodes')." tb1
			WHERE (user_id=%d OR is_public=1) AND account_id=%d",  [get_current_user_id() , get_current_user_id() , $accountInf['id']]) , ARRAY_A);

		?>
		<table class="ws_table">
			<tbody>
			<tr data-id="<?=(int)$accountInf['id']?>" data-type="account">
				<td class="fs_expand_icon"><i class="fa fa-angle-down"></i></td>
				<td>
					<img class="ws_img_style" src="<?=FSprofilePic($accountInf)?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../../../images/no-photo.png'?>');">
					<span style="vertical-align: middle;"><?php print esc_html($accountInf['name']);?></span>
					<?php
					if( !empty($accountInf['proxy']) )
					{
						?>
						<span style="padding-left: 10px; color: #74b9ff;" class="ws_tooltip" data-title="Proxy: <?=esc_html($accountInf['proxy'])?>"><i class="fa fa-globe"></i></span>
						<?php
					}
					?>
					<a href="<?=FSprofileLink($accountInf)?>" target="_blank" class="ws_btn" title="<?=esc_html__('Profile link', 'fs-poster')?>" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
					<span class="fs_communities_count"><?=$accountInf['locations']?> LOCATIONS</span>
				</td>
				<td style="width: 15%;">

				</td>
				<td style="width: 15%;">
					<button class="delete_account_btn delete_btn_desing"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			</tbody>
		</table>

		<div class="fs_communities_area">
			<div>
				<table class="ws_table" style="margin-left: 40px; width: calc(100% - 40px);">
					<tbody>
					<?php
					foreach ( $nodeList AS $nodeInf )
					{
						?>
						<tr data-id="<?=$nodeInf['id']?>" data-type="community">
							<td>
								<img class="ws_img_style" src="<?=FSprofilePic($nodeInf)?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../../../images/no-photo.png'?>');">
								<span style="vertical-align: middle;"><?php print esc_html($nodeInf['name']);?></span>
								<a href="<?=FSprofileLink($nodeInf)?>" target="_blank" class="ws_btn" title="<?=esc_html__('Profile link', 'fs-poster')?>" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>

								<span class="fs_community_categ">
								<i class="far fa-flag"></i>
								<?=ucfirst(esc_html( $nodeInf['category'] ))?>
							</span>
							</td>
							<td style="width: 15%;">
								<div class="fs_account_checkbox_public<?=$nodeInf['is_public']?' fs_account_checked':''?><?=$nodeInf['user_id']==get_current_user_id()?' my_account':''?>">
									<i class="fa fa-check"></i>
								</div>
							</td>
							<td style="width: 15%;">
								<div class="fs_account_checkbox<?=$nodeInf['is_active'] == '' ? '' : ' fs_account_checked' . ($nodeInf['is_active']=='no'?'':'2') ?>">
									<i class="fa fa-check"></i>
								</div>
								<button class="delete_account_btn delete_btn_desing"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	if( !empty($publicCommunities) )
	{
		?>
		<table class="ws_table" style="margin-left: 20px; width: calc(100% - 20px);">
			<tbody>
			<?php
			foreach ( $publicCommunities AS $nodeInf )
			{
				?>
				<tr data-id="<?=$nodeInf['id']?>" data-type="community">
					<td>
						<img class="ws_img_style" src="<?=FSprofilePic($nodeInf)?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../../../images/no-photo.png'?>');">
						<span style="vertical-align: middle;"><?php print esc_html($nodeInf['name']);?></span>
						<a href="<?=FSprofileLink($nodeInf)?>" target="_blank" class="ws_btn" title="<?=esc_html__('Profile link', 'fs-poster')?>" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>

						<span class="fs_community_categ">
							<i class="far fa-flag"></i>
							<?=ucfirst(esc_html( $nodeInf['category'] ))?>
						</span>
					</td>
					<td style="width: 15%;">
						<div class="fs_account_checkbox_public<?=$nodeInf['is_public']?' fs_account_checked':''?><?=$nodeInf['user_id']==get_current_user_id()?' my_account':''?>">
							<i class="fa fa-check"></i>
						</div>
					</td>
					<td style="width: 15%;">
						<div class="fs_account_checkbox<?=$nodeInf['is_active'] == '' ? '' : ' fs_account_checked' . ($nodeInf['is_active']=='no'?'':'2') ?>">
							<i class="fa fa-check"></i>
						</div>
						<button class="delete_account_btn delete_btn_desing"><i class="fa fa-trash"></i></button>
					</td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		<?php
	}

	if( empty($accountsList) && empty($publicCommunities) )
	{
		?>
		<table class="ws_table">
			<tbody>
			<tr>
				<td colspan="100%" style="color: #999;"><?=esc_html__('No accound found', 'fs-poster')?></td>
			</tr>
			</tbody>
		</table>
		<?php
	}
	?>

</div>