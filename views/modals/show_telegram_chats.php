<?php defined('MODAL') or exit();?>

<?php
$accountId = (int)FS_post('account_id' , '0' , 'num');

$nodeList = FSwpDB()->get_results(FSwpDB()->prepare("
	SELECT 
		*,
		(SELECT filter_type FROM ".FSwpTable('account_node_status')." WHERE node_id=tb1.id AND user_id=tb1.user_id) is_active
	FROM ".FSwpTable('account_nodes')." tb1
	WHERE (user_id=%d OR is_public=1) AND driver='telegram' AND account_id=%d",  [get_current_user_id() , $accountId]) , ARRAY_A);

?>
<style>
	#proModal<?=$mn?> .m2header
	{
		height: 70px;
		display: flex;
		align-items: center;
		justify-content: center;
		text-align: center;
		font-size: 20px;
		font-weight: 600;
		color: #FFF;
		background: #2d3436;
		border-top-left-radius: 10px;
		border-top-right-radius: 10px;
	}


	#proModal<?=$mn?> .node_chckbx>i
	{
		background: #DDD;
		padding: 5px;
		border-radius: 50%;
		color: #FFF;
		cursor: pointer;
		font-size: 12px;
	}
	#proModal<?=$mn?> .node_checked>i
	{
		background: #86d4ea;
	}
	#proModal<?=$mn?> .node_checked2>i
	{
		background: #fdcb6e;
	}

	#proModal<?=$mn?> #sub_menu2
	{
		position: fixed;
		background: #FFF;
		border-top: 2px solid #31465a;
		display: none;
		z-index: 999;
		width: 190px;
		margin-top: 10px;
	}

	#proModal<?=$mn?> #sub_menu2:before
	{
		content: '';
		border: 10px solid transparent;
		border-bottom-color: #31465a;
		position: absolute;
		top: -20px;
		left: calc(50% - 9px);
	}

	#proModal<?=$mn?> #sub_menu2 > div
	{
		padding: 8px 20px;
		font-size: 14px;
		font-weight: 500;
		color: #777;
	}

	#proModal<?=$mn?> #sub_menu2 > div:hover
	{
		background: #f5f5f5;
		cursor: pointer;
	}

	#proModal<?=$mn?> .modal-content
	{
		background: #F1F1F1;
	}
</style>

<div id="sub_menu2">
	<div class="activate_btn">Activate</div>
	<div class="activate_with_condition_btn">Activate with condition</div>
	<div class="deactivate_btn">Deactivate</div>
</div>
<span class="close" data-modal-close="true" style="color: #FFF !important; top: 18px;">&times;</span>

<div class="m2header">Chat list</div>

<div style="margin-top: 10px; padding: 20px; max-height: 260px; overflow: auto;">
	<table class="ws_table" id="telegram-chat-list-tbl" style="margin-bottom: 40px; ">
		<thead>
		<tr>
			<th>NAME</th>
			<th style="text-align: center; width: 100px;">IS ACTIVE</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach( $nodeList AS $node )
		{
			?>
			<tr data-id="<?=(int)$node['id']?>">
				<td>
					<img class="ws_img_style" src="<?=FSprofilePic($node)?>"><span style="vertical-align: middle;"><?=htmlspecialchars($node['name'])?></span>
					<a href="<?=FSprofileLink($node)?>" target="_blank" class="ws_btn ws_tooltip" data-title="Chat link" style="font-size: 13px; color: #fd79a8;"><i class="fa fa-external-link fa-external-link-alt"></i></a>
				</td>
				<td style="padding-right: 44px;">
					<div class="node_chckbx ws_tooltip<?=$node['is_active'] == '' ? '' : ' node_checked' . ($node['is_active']=='no'?'':'2') ?>" data-title="<?=esc_html__('Click to change status' , 'fs-poster');?>" data-float="left" style="float: right;">
						<i class="fa fa-check"></i>
					</div>
					<button class="delete_btn delete_btn_desing ws_tooltip" data-title="Delete account" data-float="left"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
<div style="margin-bottom: 20px; margin-right: 20px; text-align: center;">
	<button class="ws_btn ws_bg_danger" type="button" data-load-modal="telegram_add_chat" data-parameter-account_id="<?=$accountId?>">ADD NEW CHAT</button>
	<button class="ws_btn" type="button" data-modal-close="true">CLOSE</button>
</div>

<script>

	$("#proModal<?=$mn?>").on('click' , '.node_chckbx' , function()
	{
		var checked	= $(this).hasClass("node_checked"),
			dataId	= $(this).closest('tr').attr('data-id');

		$("#proModal<?=$mn?> #sub_menu2")
			.show()
			.css('top' , $(this).offset().top + 35)
			.css('left' , $(this).offset().left - ($("#sub_menu2").width()/2) + 10)
			.data('id' , dataId);
	}).on('click' , '.delete_btn' , function()
	{
		var nodeTr = $(this).closest('tr'),
			dataId = nodeTr.attr('data-id');

		fsCode.confirm("Are you sure you want to delete community?", 'danger', function(modal) {
			fsCode.ajax('settings_node_delete', {
				'id': dataId
			}, function() {
				fsCode.toast('Community has been deleted!');
				nodeTr.hide(500, function() {
					$(this).remove();
				});
				fsCode.modalHide(modal);
			});
		}, false);
	});

	$(document).click(function(e)
	{
		if( !$(e.target).is('.node_chckbx , .node_chckbx > i') )
		{
			$("#proModal<?=$mn?> #sub_menu2").hide();
		}
	});

	$("#proModal<?=$mn?> #sub_menu2 > .activate_with_condition_btn").click(function()
	{
		var dataId = $('#proModal<?=$mn?> #sub_menu2').data('id');

		fsCode.loadModal('activate_with_condition' , {'id': dataId, 'type': 'node'});
	});

	$("#proModal<?=$mn?> #sub_menu2 > .activate_btn").click(function()
	{
		var dataId = $('#proModal<?=$mn?> #sub_menu2').data('id');

		fsCode.ajax('settings_node_activity_change' , {'id': dataId, 'checked': 1});
		$("#proModal<?=$mn?> #telegram-chat-list-tbl tr[data-id=\"" + dataId + "\"] .node_chckbx")
			.addClass('node_checked')
			.removeClass('node_checked2');
	});

	$("#proModal<?=$mn?> #sub_menu2 > .deactivate_btn").click(function()
	{
		var dataId = $('#sub_menu2').data('id');

		fsCode.ajax('settings_node_activity_change' , {'id': dataId, 'checked': 0} );
		$("#proModal<?=$mn?> #telegram-chat-list-tbl tr[data-id=\"" + dataId + "\"] .node_chckbx")
			.removeClass('node_checked')
			.removeClass('node_checked2');
	});

</script>