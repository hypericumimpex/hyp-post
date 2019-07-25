<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$schedules = FSwpDB()->get_results(
	FSwpDB()->prepare( 'SELECT *, (SELECT COUNT(0) FROM `' . FSwpTable('feeds') . '` WHERE `schedule_id`=tb1.id) AS `shares_count` FROM `' . FSwpTable('schedules') . '` tb1 WHERE `user_id`=%d', [get_current_user_id()] ),
	ARRAY_A
);



$namesArray1 = [
	'random2'	=>	'Randomly ( without dublicates )',
	'random'	=>	'Randomly',
	'old_first'	=>	'Old posts first',
	'new_first'	=>	'New posts first'
];

$namesArray2 = [
	'all'				=>	'All posts',
	'this_week'			=>	'This week added posts',
	'previously_week'	=>	'Previously week added posts',
	'this_month'		=>	'This month added posts',
	'previously_month'	=>	'Previously month added posts',
	'this_year'			=>	'This year added posts',
	'last_30_days'		=>	'Last 30 days',
	'last_60_days'		=>	'Last 60 days',
];

?>
<style>
	span>i
	{
		color: #ffb700;
	}

	.ws_table .selected_tr > td
	{
		background: #E9F8FF;
	}

	.select_all_bulk
	{
		cursor: pointer;
		display: flex;
		width: 35px;
		height: 35px;
		align-items: center;
		justify-content: center;
		border: 1px solid #ffb700;
		margin-right: 10px;
		-webkit-border-radius: 20px;
		-moz-border-radius: 20px;
		border-radius: 20px;
	}

	.remove_all_bulk
	{
		cursor: pointer;
		display: flex;
		width: 35px;
		height: 35px;
		align-items: center;
		justify-content: center;
		border: 1px solid #DDD;
		-webkit-border-radius: 20px;
		-moz-border-radius: 20px;
		border-radius: 20px;
	}

	#selected_count
	{
		padding: 9px 20px;
		font-size: 14px;
		font-weight: 600;
		color: #888;
		display: none;
	}
</style>

<div style="margin: 40px 80px;">
	<span style="color: #888; font-size: 17px; font-weight: 600; line-height: 36px;"><span id="schedules_count"><?php print count($schedules);?></span> <?=esc_html__('schedule(s) added' , 'fs-poster');?></span>
	<div style="float: right;">
		<a href="?page=fs-poster-schedule&view=calendar" class="ws_btn ws_bg_info" style="margin-right: 10px;"><i class="fa fa-calendar-check"></i> <?=esc_html__('CALENDAR' , 'fs-poster');?></a>
		<button type="button" class="ws_btn ws_bg_dark" data-load-modal="add_schedule" id="createNewScheduleBtn"><i class="fa fa-plus"></i> <?=esc_html__('SCHEDULE' , 'fs-poster');?></button>
	</div>
</div>

<div style="margin-left: 80px; margin-bottom: 10px; display: flex;">
	<span class="ws_tooltip select_all_bulk" data-title="Select all"><i class="fa fa-check"></i> </span>
	<span class="ws_tooltip remove_all_bulk" data-title="Remove selected schedules"><i style="color: #CCC;" class="fa fa-trash"></i></span>
	<span id="selected_count"><span></span> schedule(s) selected</span>
</div>

<div class="ws_table_wraper">
	<table class="ws_table" id="app_list_table">
		<thead>
		<tr>
			<th><?=esc_html__('TITLE' , 'fs-poster');?> <i class="fa fa-caret-down"></i></th>
			<th style="width: 220px;"><?=esc_html__('DATE, TIME' , 'fs-poster');?></th>
			<th style="width: 125px;"><?=esc_html__('INTERVAL' , 'fs-poster');?></th>
			<th style="width: 200px;"><?=esc_html__('STATUS' , 'fs-poster');?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($schedules AS $scheduleInf)
		{
			$statusBtn = 'info';
			if( $scheduleInf['status'] == 'finished' )
				$statusBtn = 'success';

			if( $scheduleInf['status'] == 'paused' )
				$statusBtn = 'danger';

			$categoryFilter = (int)$scheduleInf['category_filter'];

			if( empty($categoryFilter) )
			{
				$categoryFiltersTxt = '';
			}
			else
			{
				$getCategNames = get_term($categoryFilter);
				$categoryFiltersTxt = ' , Category filter: <u>' . htmlspecialchars($getCategNames->name) . '</u>';
			}

			$addTxt = (isset($namesArray1[$scheduleInf['post_sort']]) ? ' , Order post by: ' . '<u>' . $namesArray1[$scheduleInf['post_sort']] . '</u>' : '');
			$addTxt .= (isset($namesArray2[$scheduleInf['post_date_filter']]) ? ' , Select posts added in: ' . '<u>'.$namesArray2[$scheduleInf['post_date_filter']].'</u>' : '');

			$postIds = $scheduleInf['post_ids'];
			$postIds = empty($postIds) ? [] : explode(',', $postIds);

			$nextPostDate = $scheduleInf['status'] == 'active' ? date('Y-m-d H:i' , strtotime($scheduleInf['next_execute_time'])) : '-';

			?>
			<tr data-id="<?=$scheduleInf['id']?>">
				<td>
					<span><input type="checkbox" class="tr_checkbox"></span>
					<i class="fa fa-rocket ws_color_danger" style="padding-right: 8px; padding-left: 8px;"></i>

					<div style="display: inline-block; vertical-align: middle">
						<div>
							<?=esc_html(FScutText($scheduleInf['title'], 55))?>
						</div>
						<div style="font-size: 11px; font-weight: 500; color: #999; margin-right: 25px;">
							Post type: <u><?=esc_html(ucfirst($scheduleInf['post_type_filter']))?></u><?=$categoryFiltersTxt?><?=$addTxt?>
						</div>
					</div>
				</td>
				<td style="font-size: 14px; color: #888;">
					<div>
						Start date: <i class="far fa-calendar-alt"></i> <?=date('Y-m-d H:i' , strtotime($scheduleInf['start_date'] . ' ' . $scheduleInf['share_time']))?>
					</div>
					<div>
						Next post: <i class="far fa-calendar-alt"></i> <?=$nextPostDate?>
					</div>
				</td>
				<td style="font-size: 14px; color: #888;"><i class="fa fa-sync"></i> <?=( count( $postIds ) == 1 ? 'no interval' : ($scheduleInf['interval'] % 1440 == 0 ? ($scheduleInf['interval'] / 1440) . ' day(s)' : ($scheduleInf['interval'] % 60 == 0 ? ($scheduleInf['interval'] / 60) . ' hour(s)' : $scheduleInf['interval'] . ' minute(s)')))?></td>
				<td>
					<button type="button" class="ws_btn ws_btn_small ws_bg_<?=$statusBtn?>"><?=esc_html($scheduleInf['status'])?></button>

					<?php
					if( $scheduleInf['status'] == 'active' )
					{
						?>
						<button type="button" class="ws_btn ws_btn_small ws_bg_purple ws_tooltip changeStatus" data-title="<?=esc_html__('Pause shares' , 'fs-poster');?>"><i class="fa fa-pause"></i></button>
						<?php
					}
					else if($scheduleInf['status'] == 'paused')
					{
						?>
						<button type="button" class="ws_btn ws_btn_small ws_bg_purple ws_tooltip changeStatus" data-title="<?=esc_html__('Resume shares' , 'fs-poster');?>"><i class="fa fa-play"></i></button>
						<?php
					}
					?>

					<button type="button" class="ws_btn ws_btn_small ws_bg_warning ws_tooltip" data-title="<?=esc_html__('Logs' , 'fs-poster');?>" data-load-modal="posts_list" data-parameter-schedule_id="<?=$scheduleInf['id']?>"><i class="fa fa-list"></i> <?=(int)$scheduleInf['shares_count']?></button>

					<button class="delete_schedule_btn delete_btn_desing"><i class="fa fa-trash"></i></button>
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
<script src="<?=FS_PLUGIN_URL?>js/jquery-ui.js"></script>
<link rel="stylesheet" href="<?=FS_PLUGIN_URL?>css/jquery-ui.css">
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
		}).on('click' , '.tr_checkbox', function()
		{
			var checked = $(this).is(':checked');

			if( checked )
			{
				$(this).closest('tr').addClass('selected_tr');
			}
			else
			{
				$(this).closest('tr').removeClass('selected_tr');
			}

			var selectedCount = $(".tr_checkbox:checked").length;
			$("#selected_count > span").text( selectedCount );
			if( selectedCount )
			{
				$(".remove_all_bulk>i").css('color' , '#ff7675');
				$("#selected_count").fadeIn(200);
			}
			else
			{
				$(".remove_all_bulk>i").css('color' , '#CCC');
				$("#selected_count").fadeOut(200);
			}
		});

		$(".select_all_bulk").click(function()
		{
			if( $(".tr_checkbox:not(:checked)").length )
			{
				$(".tr_checkbox:not(:checked)").click();
			}
			else
			{
				$(".tr_checkbox").click();
			}
		});

		$(".remove_all_bulk").click(function()
		{
			var selectedCount = $(".tr_checkbox:checked").length;

			if( selectedCount )
			{
				fsCode.confirm('Are you sure you want to delete all selected schedules?', 'danger', function()
				{
					var selectedIds = [];

					$(".tr_checkbox:checked").each(function()
					{
						selectedIds.push( $(this).closest('tr').data('id') );
					});

					fsCode.ajax('delete_schedules' , {'ids': selectedIds} , function(result)
					{
						$("#app_list_table tr.selected_tr").fadeOut(300 , function()
						{
							$(this).remove();
						});

						if( $("#app_list_table tbody tr").length - selectedCount <= 0 )
						{
							setTimeout(function()
							{
								$(".ws_table>tbody").append('<tr><td colspan="100%" style="color: #999;">No schedules found</td></tr>').children('tr').hide().fadeIn(200);
							}, 301);
						}

						$("#schedules_count").text(parseInt($("#schedules_count").text()) - selectedCount);

						$(".remove_all_bulk>i").css('color' , '#CCC');
						$("#selected_count").fadeOut(200);
					});
				});
			}
		});

		$(".tr_checkbox:checked").trigger('click');

		<?php
		if( isset($_GET['add']) )
		{
		?>
		$("#createNewScheduleBtn").click();
		<?php
		}
		?>
	});
</script>
