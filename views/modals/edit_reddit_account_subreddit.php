<?php defined('MODAL') or exit();?>

<?php
$accountId = _post('account_id' , '0' , 'num');
if( empty($accountId) )
{
	return false;
}
$accountInf = wpFetch('accounts' , ['id' => $accountId , 'user_id' => get_current_user_id()]);
if( !$accountInf )
{
	return false;
}
$options = json_decode($accountInf['options'] , true);
$subReddit = isset($options['subreddit']) ? $options['subreddit'] : '';
?>

<span class="close" data-modal-close="true">&times;</span>

<style>
	.reddit_logo > img
	{
		width: 60%;
		height: 60% !important;
		height: 180px;
		margin: 20px;
	}
	.reddit_logo
	{
		width: 52%;
		display: flex;
		justify-content: center;
	}
</style>
<span class="close" data-modal-close="true">&times;</span>

<div style="width: 100%; margin-top: 60px; display: flex; justify-content: center; align-items: center;">
	<div class="reddit_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/reddit.svg'?>"></div>
	<div style="width: 48%;">
		<div style="display: flex; flex-direction: column; width: 100%; justify-content: center;">
			<div style="font-size: 17px; font-weight: 600; color: #888;"><?=esc_html__('SubReddit:' , 'fs-poster')?> <span class="ws_tooltip" data-title="<?=esc_html__('Keep the field empty in order to post your profile or to be able to type the subreddit name (example: r/AskReddit)' , 'fs-poster')?>"><i class="fa fa-question-circle"></i></div>
			<div style="width: 90%; margin: 20px; margin-left: 0;">
				<input type="text" class="ws_form_element subreddit" placeholder="<?=esc_html__('Your profile' , 'fs-poster')?>" value="<?=esc_html($subReddit)?>">
			</div>
			<div><button class="ws_btn ws_bg_danger saveBtn" type="button"><?=esc_html__('SAVE SUBREDDIT' , 'fs-poster')?></button></div>
		</div>
	</div>
</div>


<script>

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .saveBtn").click(function()
		{
			var subreddit = $("#proModal<?=$mn?> .subreddit").val();

			fsCode.ajax('reddit_account_subreddit_change' , {'account_id': '<?=(int)$accountId?>', 'subreddit': subreddit} , function(result)
			{
				fsCode.toast("<?=esc_html__('Subreddit saved!' , 'fs-poster')?>" , 'success');
				fsCode.modalHide($("#proModal<?=$mn?>"));
				$('#account_supports .social_network_div[data-setting="reddit"]').click();
			});
		});
	});


</script>