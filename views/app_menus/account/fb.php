<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$accountsList = wpDB()->get_results(wpDB()->prepare("
	SELECT 
		*,
		(SELECT COUNT(0) FROM ".wpTable('account_nodes')." WHERE account_id=tb1.id AND node_type='ownpage') ownpages,
		(SELECT COUNT(0) FROM ".wpTable('account_nodes')." WHERE account_id=tb1.id AND node_type='page') pages,
		(SELECT COUNT(0) FROM ".wpTable('account_nodes')." WHERE account_id=tb1.id AND node_type='group') groups,
		(SELECT filter_type FROM ".wpTable('account_status')." WHERE account_id=tb1.id AND user_id=%d) is_active
	FROM ".wpTable('accounts')." tb1
	WHERE (user_id=%d OR is_public=1) AND driver='fb'",  [get_current_user_id(),get_current_user_id()]) , ARRAY_A);

?>

<div style="margin: 40px 80px;">
	<span style="color: #888; font-size: 17px; font-weight: 600; line-height: 36px;"><span id="accounts_count"><?=count($accountsList);?></span> <?=esc_html__('Facebook account added', 'fs-poster')?></span>
	<button type="button" class="ws_btn ws_bg_dark" style="float: right;" data-load-modal="add_fb_account"><i class="fa fa-plus"></i> <?=esc_html__('ADD ACCOUNT' , 'fs-poster')?></button>
</div>
<div class="ws_table_wraper">
	<table class="ws_table">
		<thead>
			<tr>
				<th><?=__('NAME' , 'fs-poster')?> <i class="fa fa-caret-down"></i></th>
				<th style="width: 15%;"><?=__('GROUPS' , 'fs-poster')?></th>
				<th style="width: 15%;"><?=__('MY PAGES' , 'fs-poster')?></th>
				<th style="width: 15%;"><?=__('LIKED PAGES' , 'fs-poster')?></th>
				<th style="width: 10%;"><?=__('MAKE PUBLIC' , 'fs-poster')?></th>
				<th style="width: 10%;"><?=__('SHARE ON PROFILE' , 'fs-poster')?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($accountsList AS $accountInf)
		{
			?>
			<tr data-id="<?=$accountInf['id']?>">
				<td>
					<img class="ws_img_style" src="https://graph.facebook.com/<?php print esc_html($accountInf['profile_id']);?>/picture?redirect=1&height=40&width=40&type=normal">
					<span style="vertical-align: middle;"><?php print esc_html($accountInf['name']);?></span>
					<?php
					if( !empty($accountInf['proxy']) )
					{
						?>
						<span style="padding-left: 10px; color: #74b9ff;" class="ws_tooltip" data-title="Proxy: <?=esc_html($accountInf['proxy'])?>"><i class="fa fa-globe"></i></span>
						<?php
					}
					?>
					<a href="<?=profileLink($accountInf)?>" target="_blank" class="ws_btn ws_tooltip" data-title="<?=esc_html__('Profile link' , 'fs-poster')?>" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
				</td>
				<td class="ws_tooltip" data-title="<?=esc_html__('Click to see list' , 'fs-poster')?>" data-load-modal="show_nodes_list" data-parameter-account_id="<?=$accountInf['id']?>" data-parameter-type="group"><i class="ws_icon_style fa fa-users"></i> <?=$accountInf['groups']?> <?=esc_html__('group(s)' , 'fs-poster')?></td>
				<td class="ws_tooltip" data-title="<?=esc_html__('Click to see list' , 'fs-poster')?>" data-load-modal="show_nodes_list" data-parameter-account_id="<?=$accountInf['id']?>" data-parameter-type="ownpage"><i class="ws_icon_style fa fa-flag"></i> <?=$accountInf['ownpages']?> <?=esc_html__('page(s)' , 'fs-poster')?></td>
				<td class="ws_tooltip" data-title="<?=esc_html__('Click to see list' , 'fs-poster')?>" data-load-modal="show_nodes_list" data-parameter-account_id="<?=$accountInf['id']?>" data-parameter-type="page"><i class="ws_icon_style fa fa-flask"></i> <?=$accountInf['pages']?> <?=esc_html__('page(s)' , 'fs-poster')?></td>
				<td>
					<div class="account_checkbox_public<?=$accountInf['is_public']?' account_checked':''?><?=$accountInf['user_id']==get_current_user_id()?' my_account':''?> ws_tooltip" data-title="<?=esc_html__('Activate for making this profile public/private for other WordPress users' , 'fs-poster')?>" data-float="left">
						<i class="fa fa-check"></i>
					</div>
				</td>
				<td>
					<div class="account_checkbox<?=$accountInf['is_active'] == '' ? '' : ' account_checked' . ($accountInf['is_active']=='no'?'':'2') ?> ws_tooltip" data-title="<?=esc_html__('Click to change status' , 'fs-poster')?>">
						<i class="fa fa-check"></i>
					</div>
					<button class="delete_account_btn delete_btn_desing ws_tooltip" data-title="<?=esc_html__('Delete account' , 'fs-poster')?>" data-float="left"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			<?php
		}
		if( empty($accountsList) )
		{
			?>
			<tr><td colspan="100%" style="color: #999;"><?=esc_html__('No account found' , 'fs-poster')?></td></tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
