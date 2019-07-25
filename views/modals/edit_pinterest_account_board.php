<?php defined('MODAL') or exit();?>

<?php
$accountId = FS_post('account_id' , '0' , 'num');
if( empty($accountId) )
{
	return false;
}

$accauntInf = FSwpFetch('accounts' , $accountId);

$accessToken = FSwpFetch('account_access_tokens' , ['account_id' => $accountId]);
$accessToken = $accessToken['access_token'];
if( empty($accessToken) )
{
	return false;
}

require_once FS_LIB_DIR . 'pinterest/Pinterest.php';

$boards = Pinterest::cmd('me/boards' , 'GET' , $accessToken , ['fields' => 'id,name'] , $accauntInf['proxy']);
if( isset( $boards['error']['message'] ) )
{
	print '<span class="close" data-modal-close="true">&times;</span> <br> <center style="margin-top: 130px; color: #d4414c; font-style: 15px; font-weight: 500;">' . htmlspecialchars($boards['error']['message']) . '</center>';
	return;
}
?>

<span class="close" data-modal-close="true">&times;</span>

<style>
	.pinterest_logo > img
	{
		width: 60%;
		height: 180px;
		margin: 20px;
	}
	.pinterest_logo
	{
		width: 52%;
		display: flex;
		justify-content: center;
	}
</style>
<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 60px; display: flex; justify-content: center; align-items: center;">
	<div class="pinterest_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/pinterest.svg'?>"></div>
	<div style="width: 48%;">
		<div style="display: flex; flex-direction: column; width: 100%; justify-content: center;">
			<div style="font-size: 17px; font-weight: 600; color: #888;"><?=esc_html__('Select board:' , 'fs-poster')?></div>
			<div style="width: 90%; margin: 20px; margin-left: 0;">
				<select class="ws_form_element boardSelect">
					<?php
					foreach($boards['data'] AS $board)
					{
						print '<option value="'.esc_html($board['id'].':'.esc_html($board['name'])).'">'.esc_html($board['name']).'</option>';
					}
					?>
				</select>
			</div>
			<div><button class="ws_btn ws_bg_danger saveBtn" type="button"><?=esc_html__('SAVE BOARD SETTINGS' , 'fs-poster')?></button></div>
		</div>
	</div>
</div>





<script>

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .saveBtn").click(function()
		{
			var board = $("#proModal<?=$mn?> .boardSelect").val();

			fsCode.ajax('pinterest_account_board_change' , {'account_id': '<?=(int)$accountId?>', 'board': board} , function(result)
			{
				fsCode.toast("<?=esc_html__('Board saved!' , 'fs-poster')?>" , 'success');
				fsCode.modalHide($("#proModal<?=$mn?>"));
				$('#fs_account_supports .fs_social_network_div[data-setting="pinterest"]').click();
			});
		});
	});


</script>