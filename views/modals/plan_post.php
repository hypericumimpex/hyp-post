<?php defined('MODAL') or exit();?>

<style>
	#proModal<?=$mn?> .share_main_box
	{
		display: flex;
		justify-content: center;
	}
	#proModal<?=$mn?> .share_main_box > div
	{
		width: 350px;
	}

	#proModal<?=$mn?> .share_switch
	{
		display: none !important;
	}

	#proModal<?=$mn?> .logo11
	{
		width: 45%;
		display: flex;
		justify-content: center;
		align-items: center;
	}
	#proModal<?=$mn?> .logo11 > img
	{
		width: 100%;
		height: 180px;
		margin: 20px;
	}
</style>

<span class="close" data-modal-close="true">&times;</span>

<div style="display: flex; width: 100%;">
	<div class="logo11">
		<img src="<?=plugin_dir_url(__FILE__).'../../images/schedule.svg'?>">
	</div>
	<div style="width: 55%;">
		<div style="padding-top: 45px; font-size: 17px; color: #888; font-weight: 600; margin-bottom: <?=(count($parameters['postIds']) > 1 ? '25px' : '70px')?>;">
			<?=esc_html__('Select schedule date and time' , 'fs-poster')?>
		</div>

		<div style="padding-bottom: 20px; margin-top: 10px;">
			<div<?=(count($parameters['postIds']) > 1 ? ' style="display: flex; align-items: center; "' : ' style="display: none;"')?>>
				<label style="color: #999; width: 90px;">Order by:</label>
				<select class="ws_form_element post_sort" style="width: 150px;">
					<option value="random" selected>Random</option>
					<option value="old_first">Old posts first</option>
					<option value="new_first">New posts first</option>
				</select>
			</div>
			<div style="margin-top: 10px; display: flex; align-items: center; <?=(count($parameters['postIds']) > 1 ? '' : 'display: none;')?>">
				<label style="color: #999; width: 90px;">Interval: </label>
				<select class="ws_form_element interval" style="width: 85px;">
					<option>Interval</option>
					<option value="1" selected>1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
				</select>
				<select class="ws_form_element interval_type" style="width: 70px;" onchange="if($(this).val()=='24'){ $('#shareTimeId').show(200); }else{ $('#shareTimeId').hide(200); }">
					<option value="1">Hour</option>
					<option value="24" selected>Day</option>
				</select>
			</div>
			<div style="margin-top: 10px; margin-bottom: <?=(count($parameters['postIds']) > 1 ? '20px' : '50px')?>; display: flex; align-items: center;">
				<label style="color: #999; width: 90px;">Share on:</label>
				<div>
					<div><input type="date" class="ws_form_element2" id="plan_date" style="width: 130px;" value="<?=date('Y-m-d', strtotime('+1 day' ))?>"></div>
					<div><input type="time" class="ws_form_element2" id="plan_time" style="width: 115px;" value="<?=date('H:i')?>"></div>
					<div style="margin-bottom: 10px;"><i>Server time: <?=date('Y-m-d H:i')?></i></div>
				</div>
			</div>
			<div>
				<button class="ws_btn ws_bg_danger share_btn" type="button" style="width: 100px;"><?=esc_html__('SCHEDULE', 'fs-poster')?></button>
				<button type="button" class="ws_btn" data-modal-close="true">CANCEL</button>
			</div>
		</div>
	</div>
</div>

<script>

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .share_btn").click(function()
		{
			var planDate 		=	$('#proModal<?=$mn?> #plan_date').val(),
				planTime 		=	$('#proModal<?=$mn?> #plan_time').val(),
				postSort 		=	$('#proModal<?=$mn?> .post_sort').val(),
				interval 		=	$("#proModal<?=$mn?> .interval").val(),
				intervalType 	=	$("#proModal<?=$mn?> .interval_type").val();

			if( planDate == '' || planTime == '' )
			{
				fsCode.alert('Plan date is empty!');
				return;
			}

			if( interval == '' || intervalType == '' )
			{
				fsCode.alert('Please select an interval!');
				return;
			}

			fsCode.ajax('schedule_posts' , {
				'post_ids'			:	<?=json_encode($parameters['postIds'])?>,
				'plan_date'			:	planDate + ' ' + planTime,
				'post_sort'			:	postSort,
				'interval'			: 	(parseInt(interval) * parseInt(intervalType))
			}, function()
			{
				fsCode.modalHide($("#proModal<?=$mn?>"));

				fsCode.toast('Post has been scheduled successfully.');

				setTimeout(function()
				{
					location.href = 'admin.php?page=fs-poster-schedule&view=list';
				}, 1000)
			});
		});

	});

</script>