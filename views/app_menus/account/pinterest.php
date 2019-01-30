<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$accountsList = wpDB()->get_results(wpDB()->prepare("
	SELECT 
		*,
		(SELECT filter_type FROM ".wpTable('account_status')." WHERE account_id=tb1.id AND user_id=%d) is_active
	FROM ".wpTable('accounts')." tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='pinterest'",  [get_current_user_id(), get_current_user_id()]) , ARRAY_A);

?>

<div style="margin: 40px 80px;">
	<span style="color: #888; font-size: 17px; font-weight: 600; line-height: 36px;"><span id="accounts_count"><?php print count($accountsList);?></span> <?=esc_html__('pinterest account added', 'fs-poster')?></span>
	<button type="button" class="ws_btn ws_bg_dark" style="float: right;" data-load-modal="add_pinterest_account"><i class="fa fa-plus"></i> <?=esc_html__('ADD ACCOUNT', 'fs-poster')?></button>
</div>
<div class="ws_table_wraper">
	<table class="ws_table">
		<thead>
		<tr>
			<th><?=esc_html__('NAME', 'fs-poster')?> <i class="fa fa-caret-down"></i></th>
			<th><?=esc_html__('POST TO BOARD', 'fs-poster')?></th>
			<th style="width: 15%;"><?=__('MAKE PUBLIC' , 'fs-poster')?></th>
			<th style="width: 15%;"><?=esc_html__('SHARE ON PROFILE', 'fs-poster')?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($accountsList AS $accountInf)
		{
			$options = json_decode($accountInf['options'] , true);
			$board = isset($options['board']) ? $options['board']['name'] : 'not selected';
			?>
			<tr data-id="<?=$accountInf['id']?>">
				<td>
					<img class="ws_img_style" src="<?=profilePic($accountInf)?>">
					<span style="vertical-align: middle;"><?php print esc_html($accountInf['name']);?></span>
					<?php
					if( !empty($accountInf['proxy']) )
					{
						?>
						<span style="padding-left: 10px; color: #74b9ff;" class="ws_tooltip" data-title="Proxy: <?=esc_html($accountInf['proxy'])?>"><i class="fa fa-globe"></i></span>
						<?php
					}
					?>
					<a href="<?=profileLink($accountInf)?>" target="_blank" class="ws_btn ws_tooltip" data-title="<?=esc_html__('Profile link', 'fs-poster')?>" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
				</td>
				<td><i class="ws_icon_style fa fa-clipboard"></i> <?=esc_html(cutText($board));?> <button type="button" class="ws_btn ws_color_info ws_tooltip" data-title="<?=esc_html__('Edit default board', 'fs-poster')?>" data-load-modal="edit_pinterest_account_board" data-parameter-account_id="<?=$accountInf['id']?>" style="font-size: 13px;"><i class="fa fa-pencil fa-pencil-alt"></i></button> </td>
				<td>
					<div class="account_checkbox_public<?=$accountInf['is_public']?' account_checked':''?><?=$accountInf['user_id']==get_current_user_id()?' my_account':''?> ws_tooltip" data-title="<?=esc_html__('Activate for making this profile public/private for other WordPress users' , 'fs-poster')?>" data-float="left">
						<i class="fa fa-check"></i>
					</div>
				</td>
				<td>
					<div class="account_checkbox<?=$accountInf['is_active'] == '' ? '' : ' account_checked' . ($accountInf['is_active']=='no'?'':'2') ?> ws_tooltip" data-title="<?=esc_html__('Click to change status', 'fs-poster')?>">
						<i class="fa fa-check"></i>
					</div>
					<button class="delete_account_btn delete_btn_desing ws_tooltip" data-title="<?=esc_html__('Delete account', 'fs-poster')?>" data-float="left"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			<?php
		}
		if( empty($accountsList) )
		{
			?>
			<tr><td colspan="100%" style="color: #999;"><?=esc_html__('No accound found', 'fs-poster')?></td></tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>