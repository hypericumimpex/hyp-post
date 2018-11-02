<?php defined('MODAL') or exit();?>
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
		width: 145px;
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
		width: 300px;
		margin-top: 10px;
	}
</style>
<div style="width: 100%; height: 100%; display: flex; align-items: center; flex-direction: column; justify-content: center; position: absolute;">
	<div style="margin-bottom: 20px; text-align: center;font-size: 17px;color: #888;font-weight: 600;">
		Construct your <b>conditions</b> and <b>activate</b> account:
	</div>

	<div style="width: 300px;">
		<select class="ws_form_element select2-init categories_filter" style="width: 300px;" multiple>
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

	<div style="margin: 15px; width: 250px; text-align: center;"><button type="button" class="ws_btn ws_bg_danger save_btn" style="width: 130px;"><?=esc_html__('ACTIVATE', 'fs-poster')?></button> </div>
</div>

<span class="close" data-modal-close="true">&times;</span>

<script>

	$("#proModal<?=$mn?> .save_btn").click(function()
	{
		var cats = $("#proModal<?=$mn?> .categories_filter").val(),
			filter_type = $("#proModal<?=$mn?> .filter_type > .selected").attr('data-name');

		fsCode.ajax( '<?=$parameters['ajaxUrl']?>' , {'id': '<?=(int)$parameters['id']?>', 'checked': 1 , 'categories': cats, 'filter_type': filter_type}, function()
		{
			$("<?=($parameters['ajaxUrl']=='account_activity_change'?'tr':'.node_div')?>[data-id=\"<?=(int)$parameters['id']?>\"] .<?=($parameters['ajaxUrl']=='account_activity_change'?'account_checkbox':'node_chckbx')?>")
				.removeClass('<?=($parameters['ajaxUrl']=='account_activity_change'?'account_checked':'node_checked')?>')
				.addClass('<?=($parameters['ajaxUrl']=='account_activity_change'?'account_checked':'node_checked')?>2');

			fsCode.modalHide( $("#proModal<?=$mn?>") );
		});
	});

	$("#proModal<?=$mn?> .filter_type > div").click(function ()
	{
		$("#proModal<?=$mn?> .filter_type > .selected").removeClass('selected');
		$(this).addClass('selected');
	});

	$("#proModal<?=$mn?> .select2-init").select2({
		'placeholder': 'Select categories...'
	});

</script>