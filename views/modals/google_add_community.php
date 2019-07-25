<?php defined('MODAL') or exit();?>

<?php
$accountId = (int)FS_post('account_id' , '0' , 'num');
$userId = (int)get_current_user_id();

$accountInf = FSwpDB()->get_row("SELECT * FROM ".FSwpTable('accounts')." WHERE id='{$accountId}' AND driver='google' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

if( !$accountInf )
{
	print 'You have not a permission for adding community in this account!';
	return;
}

require_once FS_LIB_DIR . '/google/GooglePlus.php';
$google = new GooglePlus($accountInf['username'] , $accountInf['password'] , $accountInf['proxy']);

$communities = $google->getCommunities();

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
	<div style="text-align: center; margin-top: 30px; font-size: 18px; color: #999; font-weight: 600;">Communities list</div>

	<div style="width: 350px; margin-top: 20px;">
		<div style="text-align: center;">Select one of you joined communitie for adding:</div>
		<div>
			<select class="ws_form_element community_select select2-init2">
				<option></option>
				<?php
				foreach( $communities AS $cInfo )
				{
					print '<option value="'.htmlspecialchars($cInfo['id']).'" data-image="'.htmlspecialchars($cInfo['image']).'" data-members="'.number_format($cInfo['members']).'">'.htmlspecialchars($cInfo['name']).'</option>';
				}
				?>
			</select>
		</div>
		<div class="categories_area" style="display: none;">
			<div style="text-align: center;">Pick a category</div>
			<div>
				<select class="ws_form_element community_categ_select">

				</select>
			</div>
		</div>
		<div style="border-top: 1px solid #DDD; margin-top: 15px; padding-top: 15px;">

			<div style="margin-bottom: 10px;"><label><input type="checkbox" class="categ_filter"> Post categories filter</label></div>

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
	<button class="ws_btn ws_bg_danger save-btn" type="button"><i class="fa fa-save"></i> Add community</button>
	<button class="ws_btn" type="button" data-modal-close="true">Close</button>
</div>

<script>
	$("#proModal<?=$mn?> .community_select").change(function()
	{
		var community = $(this).val();

		$("#proModal<?=$mn?> .community_categ_select").empty();
		$("#proModal<?=$mn?> .categories_area").slideUp(200);
		fsCode.ajax('google_community_get_cats' , {'id': community, 'account_id': '<?=$accountId?>'}, function(res)
		{
			$("#proModal<?=$mn?> .community_categ_select").empty();

			if( res['cats'].length > 0 )
			{
				for( var i in res['cats'] )
				{
					var catInf = res['cats'][i];

					$("#proModal<?=$mn?> .community_categ_select").append('<option value="'+catInf['id']+'">'+catInf['name']+'</option>');
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
		'placeholder': 'Select categories...'
	});

	$("#proModal<?=$mn?> .select2-init2").select2({
		'placeholder': 'Select...',
		templateResult: function( data )
		{
			if (!data.id)
				return data.text;

			return $('<div style="display: flex; align-items: center;"><div style="width: 40px; height: 40px; overflow: hidden; display: flex; align-items: center;"><img src="' + $(data.element).data('image') + '" style="width: 40px;"></div><div style="width: calc(100% - 50px); margin-left: 10px;"><div style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden;">' + data.text + '</div><div>'+$(data.element).data('members')+' members</div></div></div>');
		}
	});

	$("#proModal<?=$mn?> .save-btn").click(function()
	{
		var community		=	$("#proModal<?=$mn?> .community_select").val(),
			community_categ	=	$("#proModal<?=$mn?> .community_categ_select").val(),
			categ_filter	=	$("#proModal<?=$mn?> .categ_filter").is(':checked') ? 1 : 0,
			cats			=	categ_filter == 1 ? $("#proModal<?=$mn?> .categories_filter").val() : null,
			filter_type		=	$("#proModal<?=$mn?> .filter_type > .selected").attr('data-name');

		if( community == '' )
		{
			fsCode.alert('Please select comminuty!');
			return;
		}

		fsCode.ajax( 'google_community_save' , {'account_id': '<?=$accountId?>', 'community': community, 'community_categ': community_categ , 'categories': cats, 'filter_type': filter_type}, function(result)
		{
			var communityName	= $(".community_select :selected").text(),
				categName		= $("#proModal<?=$mn?> .community_categ_select :selected").text();

			$("#google-communities-list-tbl > tbody").append('<tr data-id="'+result['id']+'"><td><img class="ws_img_style" src="'+$(".community_select :selected").data('image')+'"><span style="vertical-align: middle;">'+communityName+'</span><a href="https://plus.google.com/communities/'+community+'" target="_blank" class="ws_btn ws_tooltip" data-title="Community link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a></td><td>'+categName+'</td><td style="padding-right: 44px;"><div class="node_chckbx ws_tooltip node_checked'+(categ_filter==1?'2':'')+'" data-title="<?=esc_html__('Click to change status' , 'fs-poster');?>" data-float="left" style="float: right;"><i class="fa fa-check"></i></div><button class="delete_btn delete_btn_desing ws_tooltip" data-title="Delete account" data-float="left"><i class="fa fa-trash"></i></button></td></tr>');

			fsCode.modalHide( $("#proModal<?=$mn?>") );
		});

	});
</script>