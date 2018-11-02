<?php defined('MODAL') or exit();?>

<style>
	.modal-content .background_div_c
	{
		width: calc(100% + 80px);
		display: flex;
		align-items: center;
		justify-content: center;
		background-image: url('<?=plugin_dir_url(__FILE__).'../../images/schedule_bg.png'?>');
		margin-left: -40px;
		margin-top: -10px;
		border-top-left-radius: 10px;
		border-top-right-radius: 10px;
	}
	.modal-content span>i
	{
		color: #ffb700;
	}
</style>

<span class="close" data-modal-close="true" style="color: #FFF;">&times;</span>

<div style="padding: 10px 0 20px; margin-left: 40px; margin-right: 40px; display: flex; flex-direction: column;">
	<div class="background_div_c">
		<img src="<?=plugin_dir_url(__FILE__).'../../images/schedule.svg'?>" style="width: 130px;">
	</div>
	<div style="width: 100%; margin-top: 15px;">
		<div style="width: 283px; display: flex;">
			<input type="text" class="ws_form_element2 title" placeholder="<?=esc_html__('Title' , 'fs-poster')?>">
			<span class="ws_tooltip" data-title="You can add multiple Schedules. Please fill the title field because of confusions." style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
		</div>
		<div style="display: flex; width: 250px; margin-top: 5px; align-items: center;">
			<input type="text" class="ws_form_element2 start_date" placeholder="<?=esc_html__('Start date' , 'fs-poster')?>" style="width: calc(50% - 8px); margin-right: 13px;">
			<input type="text" class="ws_form_element2 end_date" placeholder="<?=esc_html__('End date' , 'fs-poster')?>" style="width: calc(50% - 8px);">
			<span class="ws_tooltip" data-title="Which date this schedule will be start and when will be compleated." style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
		</div>

		<div style="display: flex; margin-top: 5px;">
			<div style="display: flex; width: 250px; align-items: center;">
				<select class="ws_form_element interval" style="margin-right: 15px;">
					<option><?=esc_html__('Interval' , 'fs-poster')?></option>
					<option value="1">1</option>
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
				<select class="ws_form_element interval_type" onchange="if($(this).val()=='24'){ $('#shareTimeId').show(200); }else{ $('#shareTimeId').hide(200); }">
					<option value="1"><?=esc_html__('Hour' , 'fs-poster')?></option>
					<option value="24"><?=esc_html__('Day' , 'fs-poster')?></option>
				</select>
			</div>
			<span class="ws_tooltip" data-title="Frequency of posting." style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
			<div style="margin-left: 30px; display: none;" id="shareTimeId">
				<input type="time" class="ws_form_element2 share_time" placeholder="<?=esc_html__('Share time' , 'fs-poster')?>" value="13:00" style="width: 110px;">
				<span class="ws_tooltip" data-title="<?=esc_html__('Share time:' , 'fs-poster')?>" style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
			</div>
		</div>

		<div style="display: flex; margin-left: 1px;">
			<div style="width: 45%; margin-top: 5px; display: flex; align-items: center;">
				<select class="ws_form_element select2-init post_type_filter" multiple data-placeholder="<?=esc_html__('Post types' , 'fs-poster')?>">
					<?php
					foreach( get_post_types() AS $post_type )
					{
						print '<option value="' . htmlspecialchars($post_type) . '">' . htmlspecialchars($post_type) . '</option>';
					}
					?>
				</select>
				<span class="ws_tooltip" data-title="Filter posts by type. If you do not want to add this filter then keep empty." style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
			</div>
			<div style="width: 5%;"></div>
			<div style="width: 50%; margin-top: 5px; display: flex; align-items: center;">
				<select class="ws_form_element select2-init category_filter" data-placeholder="<?=esc_html__('Category filter' , 'fs-poster')?>" multiple>
					<?php
					foreach( get_categories() AS $categ )
					{
						print '<option value="' . htmlspecialchars($categ->cat_ID) . '">' . htmlspecialchars($categ->cat_name) . '</option>';
					}
					?>
				</select>
				<span class="ws_tooltip" data-title="Filter posts by category. If you do not want to add this filter then keep empty." style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
			</div>
		</div>

		<div style="width: 100%; display: flex; margin-top: 5px;">
			<div style="width: 45%; display: flex; align-items: center;">
				<select class="ws_form_element post_sort">
					<option value="random">Random</option>
					<option value="old_first">Old posts first</option>
					<option value="new_first">New posts first</option>
				</select>
				<span class="ws_tooltip" data-title="Method for selecting posts." style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
			</div>
			<div style="width: 5%;"></div>
			<div style="width: 50%; display: flex; align-items: center;">
				<select class="ws_form_element post_date_filter">
					<option value="all">All posts</option>
					<option value="this_week">This week added posts</option>
					<option value="previously_week">Previously week added posts</option>
					<option value="this_month">This month added posts</option>
					<option value="previously_month">Previously month added posts</option>
					<option value="this_year">This year added posts</option>
				</select>
				<span class="ws_tooltip" data-title="Filter posts" style="padding: 10px;"><i class="fa fa-info-circle"></i></span>
			</div>
		</div>

		<div style="margin-top: 15px;">
			<button type="button" class="ws_btn ws_bg_danger saveScheduleBtn" style="width: 150px;"><?=esc_html__('SAVE SCHEDULE' , 'fs-poster')?></button>
			<button type="button" class="ws_btn" data-modal-close="true"><?=esc_html__('Cancel' , 'fs-poster')?></button>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .start_date , #proModal<?=$mn?> .end_date").datepicker({
			dateFormat: "yy-mm-dd"
		});

		$("#proModal<?=$mn?> .saveScheduleBtn").click(function()
		{
			var title				=	$("#proModal<?=$mn?> .title").val(),
				startDate 			=	$("#proModal<?=$mn?> .start_date").val(),
				endDate 			=	$("#proModal<?=$mn?> .end_date").val(),
				interval 			=	$("#proModal<?=$mn?> .interval").val(),
				intervalType 		=	$("#proModal<?=$mn?> .interval_type").val(),
				share_time  		=   $("#proModal<?=$mn?> .share_time").val(),
				post_type_filter 	=   $("#proModal<?=$mn?> .post_type_filter").val(),
				category_filter		=   $("#proModal<?=$mn?> .category_filter").val(),
				post_sort			=   $("#proModal<?=$mn?> .post_sort").val(),
				post_date_filter	=   $("#proModal<?=$mn?> .post_date_filter").val();

			if( title == '' )
			{
				fsCode.toast("<?=esc_html__('Please type the title field!' , 'fs-poster')?>" , 'danger');
				return false;
			}
			if( startDate == '' )
			{
				fsCode.toast("<?=esc_html__('Please type the start date!' , 'fs-poster')?>" , 'danger');
				return false;
			}
			if( endDate == '' )
			{
				fsCode.toast("<?=esc_html__('Please type the end date!' , 'fs-poster')?>" , 'danger');
				return false;
			}
			if( interval == '' || !( parseInt(interval) > 0 ) )
			{
				fsCode.toast("<?=esc_html__('Please type the interval!' , 'fs-poster')?>" , 'danger');
				return false;
			}

			fsCode.ajax('schedule_save' , {
				'title':				title,
				'start_date':			startDate,
				'end_date':				endDate ,
				'interval': 			(parseInt(interval) * parseInt(intervalType)) ,
				'share_time':			share_time,
				'post_type_filter':		post_type_filter,
				'category_filter':		category_filter,
				'post_sort':			post_sort,
				'post_date_filter':		post_date_filter,
			} , function(result)
			{
				fsCode.loading(1);
				location.reload();
			});
		});

		$(".select2-init").select2();

	});
</script>