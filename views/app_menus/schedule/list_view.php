<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$schedules = wpFetchAll('schedules' , ['user_id' => get_current_user_id()]);
?>
<style>
	span>i
	{
		color: #ffb700;
	}
</style>
<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>


<div style="margin: 40px 80px;">
	<span style="color: #888; font-size: 17px; font-weight: 600; line-height: 36px;"><span id="schedules_count"><?php print count($schedules);?></span> <?=esc_html__('schedule added' , 'fs-poster');?></span>
	<div style="float: right;">
		<a href="?page=fs-poster-schedule&view=calendar" class="ws_btn ws_bg_info" style="margin-right: 10px;"><i class="fa fa-calendar-check"></i> <?=esc_html__('CALENDAR' , 'fs-poster');?></a>
		<button type="button" class="ws_btn ws_bg_dark" data-load-modal="add_schedule" id="createNeScheduleBtn"><i class="fa fa-plus"></i> <?=esc_html__('SCHEDULE' , 'fs-poster');?></button>
	</div>
</div>

<div class="ws_table_wraper">
	<table class="ws_table" id="app_list_table">
		<thead>
		<tr>
			<th><?=esc_html__('TITLE' , 'fs-poster');?> <i class="fa fa-caret-down"></i></th>
			<th style="width: 150px;"><?=esc_html__('DATE' , 'fs-poster');?></th>
			<th style="width: 150px;"><?=esc_html__('INTERVAL' , 'fs-poster');?></th>
			<th style="width: 250px;"><?=esc_html__('STATUS' , 'fs-poster');?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($schedules AS $scheduleInf)
		{
			$statusBtn = '';
			if( $scheduleInf['status'] == 'finished' )
			{
				$statusBtn = 'success';
			}
			else if( $scheduleInf['status'] == 'paused' )
			{
				$statusBtn = 'danger';
			}
			else
			{
				$statusBtn = 'info';
			}

			$postTypesTxt = str_replace('|' , ', ' , $scheduleInf['post_type_filter']);
			$postTypesTxt = $postTypesTxt == '' ? ' - ' : htmlspecialchars($postTypesTxt);

			$categoryFilters = explode('|' , $scheduleInf['post_type_filter']);
			$categoryFiltersArr = [];
			foreach ($categoryFilters AS $categId)
			{
				if( is_numeric($categId) && $categId > 0 )
				{
					$categoryFiltersArr[] = (int)$categId;
				}
			}

			if( empty($categoryFiltersArr) )
			{
				$categoryFiltersTxt = ' none ';
			}
			else
			{
				$getCategNames = wpDB()->get_row("SELECT group_concat(name , ', ') AS categs_name FROM wp_terms WHERE term_id IN ('" . implode("','" , $categoryFiltersTxt) . "')");
				$categoryFiltersTxt = htmlspecialchars($getCategNames['categs_name']);
			}

			$namesArray1 = [
				'random'	=>	'Random',
				'old_first'	=>	'Old posts first',
				'new_first'	=>	'New posts first'
			];

			$namesArray2 = [
				'all'				=>	'All posts',
				'this_week'			=>	'This week added posts',
				'previously_week'	=>	'Previously week added posts',
				'this_month'		=>	'This month added posts',
				'previously_month'	=>	'Previously month added posts',
				'this_year'			=>	'This year added posts'
			];

			$addTxt = (isset($namesArray1[$scheduleInf['post_sort']]) ? $namesArray1[$scheduleInf['post_sort']] : '-') . ' ; ';
			$addTxt .= (isset($namesArray2[$scheduleInf['post_date_filter']]) ? $namesArray2[$scheduleInf['post_date_filter']] : '-');

			?>
			<tr data-id="<?=$scheduleInf['id']?>">
				<td>
					<i class="fa fa-rocket ws_color_danger" style="padding-right: 8px; padding-left: 8px;"></i>
					<?=cutText(esc_html($scheduleInf['title']))?>
					<span style="padding: 5px;" class="ws_tooltip" data-title="Category filter: <?=$categoryFiltersTxt?> ; Post types: <?=$postTypesTxt?> ; <?=$addTxt?>"><i class="fa fa-info-circle"></i></span>
				</td>
				<td style="font-size: 14px; color: #888; width: 300px;">
					<?php
					if( $scheduleInf['start_date'] == $scheduleInf['end_date'] && !empty( $scheduleInf['share_time'] ) )
					{
						?>
						<div style="margin-bottom: 5px;"><i class="far fa-calendar-alt"></i> <?=date('Y-m-d' , strtotime($scheduleInf['start_date']))?></div>
						<div><i class="far fa-clock"></i> <?=date('H:i' , strtotime($scheduleInf['share_time']))?></div>
						<?php
					}
					else
					{
						?>
						<div style="margin-bottom: 5px;"><b>Start date:</b> <i class="far fa-calendar-alt"></i> <?=date('Y-m-d' , strtotime($scheduleInf['start_date']))?></div>
						<div><b>End date:</b> <i class="far fa-calendar-alt"></i> <?=date('Y-m-d' , strtotime($scheduleInf['end_date']))?></div>
						<?php
					}
					?>

				</td>
				<td style="font-size: 14px; color: #888;"><i class="fa fa-sync"></i> <?=($scheduleInf['interval'] % 24 == 0 ? ($scheduleInf['interval'] / 24) . ' day(s)' : $scheduleInf['interval'] . ' hour(s)')?></td>
				<td>
					<button type="button" class="ws_btn ws_btn_small ws_bg_<?=$statusBtn?>"><?=esc_html($scheduleInf['status'])?></button>

					<?php
					if( $scheduleInf['status'] == 'active' )
					{
						?>
						<button type="button" class="ws_btn ws_btn_small ws_bg_purple ws_tooltip changeStatus" data-title="<?=esc_html__('Pause schedule' , 'fs-poster');?>"><i class="fa fa-pause"></i></button>
						<?php
					}
					else if($scheduleInf['status'] == 'paused')
					{
						?>
						<button type="button" class="ws_btn ws_btn_small ws_bg_purple ws_tooltip changeStatus" data-title="<?=esc_html__('Play schedule' , 'fs-poster');?>"><i class="fa fa-play"></i></button>
						<?php
					}
					?>

					<button type="button" class="ws_btn ws_btn_small ws_bg_warning ws_tooltip" data-title="<?=esc_html__('Show published posts list' , 'fs-poster');?>" data-load-modal="posts_list" data-parameter-schedule_id="<?=$scheduleInf['id']?>"><i class="fa fa-list"></i></button>

					<button class="delete_schedule_btn delete_btn_desing ws_tooltip" data-title="<?=esc_html__('Delete schedule' , 'fs-poster');?>" data-float="left"><i class="fa fa-trash"></i></button>
				</td>
			</tr>
			<?php
		}
		if( empty($schedules) )
		{
			?>
			<tr><td colspan="100%" style="color: #999;">No schedules found</td></tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
<script src="<?=PLUGIN_URL?>js/jquery-ui.js"></script>
<link rel="stylesheet" href="<?=PLUGIN_URL?>css/jquery-ui.css">
<script>
	jQuery(document).ready(function()
	{
		jQuery("body").on('click' , '.delete_schedule_btn' , function()
		{
			var tr = $(this).closest('tr'),
				aId = tr.attr('data-id');

			fsCode.confirm("<?=esc_html__('Are you sure you want to delete?' , 'fs-poster');?>" , 'danger' , function ()
			{
				fsCode.ajax('delete_schedule' , {'id': aId} , function(result)
				{
					tr.fadeOut(300, function()
					{
						$(this).remove();
						if( $(".ws_table>tbody>tr").length == 0 )
						{
							$(".ws_table>tbody").append('<tr><td colspan="100%" style="color: #999;">No schedules found</td></tr>').children('tr').hide().fadeIn(200);
						}
						$("#schedules_count").text(parseInt($("#schedules_count").text()) - 1);
					});
				});
			}, true);
		}).on('click' , '.changeStatus' , function()
		{
			var id = $(this).closest('tr').attr('data-id'),
				btn = $(this);

			fsCode.ajax('schedule_change_status' , {'id': id} , function(result)
			{
				fsCode.loading(1);
				location.reload();
			});
		});

		<?php
		if( isset($_GET['add']) )
		{
		?>
		$("#createNeScheduleBtn").click();
		<?php
		}
		?>
	});
</script>
