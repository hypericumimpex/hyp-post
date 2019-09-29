<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$accountsList = FSwpDB()->get_results(FSwpDB()->prepare("
	SELECT 
		*,
		(SELECT filter_type FROM ".FSwpTable('account_status')." WHERE account_id=tb1.id AND user_id=%d) is_active
	FROM ".FSwpTable('accounts')." tb1 
	WHERE (user_id=%d OR is_public=1) AND driver='twitter'",  [get_current_user_id(), get_current_user_id()]) , ARRAY_A);

?>

<div style="margin: 40px 80px;">
	<span style="color: #888; font-size: 17px; font-weight: 600; line-height: 36px;"><span id="accounts_count"><?php print count($accountsList);?></span> <?=esc_html__('twitter account added', 'fs-poster')?></span>
	<button type="button" class="ws_btn ws_bg_dark" style="float: right;" data-load-modal="add_twitter_account"><i class="fa fa-plus"></i> <?=esc_html__('ADD ACCOUNT', 'fs-poster')?></button>
</div>
<div class="ws_table_wraper">
	<table class="ws_table">
		<thead>
		<tr>
			<th><?=esc_html__('NAME', 'fs-poster')?> <i class="fa fa-caret-down"></i></th>
			<th style="width: 190px;"><?=esc_html__('USERNAME', 'fs-poster')?></th>
			<th style="width: 15%;"><?=esc_html__('ACTIVATE', 'fs-poster')?></th>
		</tr>
		</thead>
	</table>
		<?php
		foreach($accountsList AS $accountInf)
		{
			?>
			<table class="ws_table">
				<tbody>
					<tr data-id="<?=$accountInf['id']?>" data-type="account">
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
							<span style="padding-left: 10px; color: #ffbd72;<?=!$accountInf['is_public']?' display: none;':''?>" class="fs_account_is_public ws_tooltip" data-title="<?=esc_html__('This profile is public for all WordPress users.', 'fs-poster')?>"><i class="fa fa-star"></i></span>
							<a href="<?=FSprofileLink($accountInf)?>" target="_blank" class="ws_btn ws_tooltip" data-title="<?=esc_html__('Profile link', 'fs-poster')?>" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
						</td>
						<td style="width: 190px;"><?=esc_html($accountInf['username']);?></td>
						<td style="width: 15%;">
							<div class="fs_account_checkbox<?=$accountInf['is_active'] == '' ? '' : ' fs_account_checked' . ($accountInf['is_active']=='no'?'':'2') ?> ws_tooltip" data-title="<?=esc_html__('Click to change status', 'fs-poster')?>">
								<i class="fa fa-check"></i>
							</div>
							<button class="more_options_account fs_more_options_design"><i class="fa fa-ellipsis-v"></i></button>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}
		if( empty($accountsList) )
		{
			?>
			<table class="ws_table"><tbody><tr><td colspan="100%" style="color: #999;"><?=esc_html__('No accound found', 'fs-poster')?></td></tr></tbody></table>
			<?php
		}
		?>
</div>