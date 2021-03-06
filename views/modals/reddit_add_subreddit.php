<?php defined('MODAL') or exit();?>

<?php
$accountId = (int)FS_post('account_id' , '0' , 'num');
$userId = (int)get_current_user_id();

$accountInf = FSwpDB()->get_row("SELECT * FROM ".FSwpTable('accounts')." WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

if( !$accountInf )
{
	print 'You have not a permission for adding subreddit in this account!';
	return;
}

?>
<style>
	.select2-search__field::placeholder
	{
		color: #999;
		font-weight: 600;
		font-size: 14px;
		line-height: 21px;
	}

	.btn_select > div
	{
		width: 170px;
		text-align: center;
		height: 70px;
		background: #EEE;
		color: #999;
		font-width: 600 !important;;
		font-size: 14px !important;
		padding-top: 5px;
		padding-bottom: 5px;
		cursor: pointer;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
	}

	.btn_select > .selected
	{
		background: #fdcb6e;
		color: #FFF;
	}

	.btn_select
	{
		display: flex;
		justify-content: space-between;
		width: 350px;
		margin-top: 10px;
	}




</style>

<span class="close" data-modal-close="true" >&times;</span>

<div style="display: flex; flex-direction: column; align-items: center;">
	<div style="text-align: center; margin-top: 30px; font-size: 18px; color: #999; font-weight: 600;">Add new subreddit</div>

	<div style="width: 350px; margin-top: 20px;">
		<div class="fs_label_c">Select subreddit:</div>
		<div>
			<select class="ws_form_element subreddit_select select2-init2">
				<option></option>
			</select>
		</div>
		<div class="categories_area" style="display: none;">
			<div class="fs_label_c">Select flair</div>
			<div>
				<select class="ws_form_element flairs_select">

				</select>
			</div>
		</div>
		<div style="border-top: 1px solid #DDD; margin-top: 15px; padding-top: 15px;">

			<div style="margin-bottom: 10px;"><label><input type="checkbox" class="categ_filter"> Filter by WP Post categories</label></div>

			<div style="display: none;" class="post_categs_filter_area">
				<div style="width: 350px;">
					<select class="ws_form_element select2-init categories_filter" style="width: 350px;" multiple>
						<?php
						foreach(get_categories() AS $categInf)
						{
							print '<option value="'.(int)$categInf->term_id.'">'.htmlspecialchars($categInf->name).'</option>';
						}
						?>
					</select>
				</div>

				<div class="btn_select filter_type">
					<div class="selected" data-name="in">
						<div><i class="far fa-check-circle"></i></div>
						<div>Share only selected categories posts</div>
					</div>
					<div data-name="ex">
						<div><i class="fa fa-ban"></i></div>
						<div>Do not share selected categories posts</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<div style="margin-top: 10px; margin-bottom: 20px; margin-right: 20px; text-align: center;">
	<button class="ws_btn ws_bg_danger save-btn" type="button"><i class="fa fa-save"></i> Add subreddit</button>
	<button class="ws_btn" type="button" data-modal-close="true">Close</button>
</div>

<script>
	$("#proModal<?=$mn?> .subreddit_select").change(function()
	{
		var subreddit = $(this).val();

		$("#proModal<?=$mn?> .flairs_select").empty();
		fsCode.ajax('reddit_get_subreddt_flairs' , {'subreddit': subreddit, 'account_id': '<?=$accountId?>'}, function(res)
		{
			if( res['flairs'].length > 0 )
			{
				for( var i in res['flairs'] )
				{
					var flairInf = res['flairs'][i];

					$("#proModal<?=$mn?> .flairs_select").append('<option value="'+flairInf['id']+'">'+flairInf['text']+'</option>');
				}

				$("#proModal<?=$mn?> .categories_area").slideDown(250);
			}
			else
			{
				$("#proModal<?=$mn?> .categories_area").slideUp(250);
			}
		});
	});

	$("#proModal<?=$mn?> .categ_filter").change(function()
	{
		if( $(this).is(':checked') )
		{
			$("#proModal<?=$mn?> .post_categs_filter_area").slideDown(200);
		}
		else
		{
			$("#proModal<?=$mn?> .post_categs_filter_area").slideUp(200);
		}
	});

	$("#proModal<?=$mn?> .filter_type > div").click(function ()
	{
		$("#proModal<?=$mn?> .filter_type > .selected").removeClass('selected');
		$(this).addClass('selected');
	});

	$("#proModal<?=$mn?> .select2-init").select2({
		'placeholder': 'WP Post categ.'
	});

	$("#proModal<?=$mn?> .select2-init2").select2({
		'placeholder': 'Search subreddit... ( type minimum 1 char. for search )',
		ajax: {
			url: ajaxurl,
			type: "POST",
			dataType: 'json',
			data: function (params)
			{
				var query = {
					account_id: '<?=$accountId?>',
					action: 'search_subreddits',
					search: params.term
				}

				return query;
			},
			processResults: function (data)
			{
				return {
					results: data.subreddits
				};
			}
		}
	});

	$("#proModal<?=$mn?> .save-btn").click(function()
	{
		var subreddit		=	$("#proModal<?=$mn?> .subreddit_select").val(),
			flair			=	$("#proModal<?=$mn?> .flairs_select").val(),
			flairName		=	$("#proModal<?=$mn?> .flairs_select > :selected").text(),
			categ_filter	=	$("#proModal<?=$mn?> .categ_filter").is(':checked') ? 1 : 0,
			cats			=	categ_filter == 1 ? $("#proModal<?=$mn?> .categories_filter").val() : null,
			filter_type		=	$("#proModal<?=$mn?> .filter_type > .selected").attr('data-name');

		if( subreddit == '' )
		{
			fsCode.alert('Please select subreddit!');
			return;
		}

		fsCode.ajax( 'reddit_subreddit_save' , {'account_id': '<?=$accountId?>', 'subreddit': subreddit, 'flair': flair, 'flair_name': flairName , 'categories': cats, 'filter_type': filter_type}, function(result)
		{
			fsCode.modalHide( $("#proModal<?=$mn?>") );
			$('#fs_account_supports .fs_social_network_div[data-setting="reddit"]').click();
		});

	});
</script>