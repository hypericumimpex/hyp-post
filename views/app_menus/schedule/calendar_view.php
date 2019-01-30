<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<link rel="stylesheet" href="<?=PLUGIN_URL?>css/fullcalendar.min.css">

<style>
	.monthPre{
		color: gray;
		text-align: center;
	}
	.monthNow{
		color: blue;
		text-align: center;
	}
	.dayNow{
		text-align: center;
	}
	.calendar td{
		htmlContent: 2px;
		width: 40px;
	}
	.monthNow th{
		text-align: center;
	}
	.dayNames{
		text-align: center;
	}


	.calendar-container
	{
		display: flex;
		background: #FFF;
		border: 1px solid #DDD;
		-webkit-border-radius: 10px !important;
		-moz-border-radius: 10px !important;
		border-radius: 10px !important;
		height: 500px;
		width: 1000px;
		margin: 50px;

	}

	.yearMonthHead > th
	{
		text-transform: uppercase;
		padding: 15px;
		color: #fd79a8;
		font-weight: 700;
		font-size: 17px;
	}

	.dayNames > td
	{
		text-transform: uppercase;
		color: #b2bec3;
		padding: 10px 5px 10px;
		font-weight: 700;
		font-size: 15px;
	}

	.days
	{
		width: 50px;
		height: 50px;
		color: #999;
		text-align: center;
		vertical-align: middle;
		position: relative;
		font-size: 15px;
		font-weight: 600;
		text-shadow: 5px 5px 10px #AAA;
	}

	.days[data-count]
	{
		cursor: pointer;
	}

	.days[data-count]:after
	{
		content: attr(data-count);
		position: absolute;
		right: 6px;
		top: 6px;
		width: 15px;
		height: 15px;
		font-size: 10px;
		font-weight: 800;
		line-height: 14px;
		background: #FFF;
		color: #fd79a8;
		-webkit-border-radius: 14px;
		-moz-border-radius: 14px;
		border-radius: 14px;
	}

	.dayNow > span
	{
		color: #FFF;
		position: relative;
	}

	.dayNow:before
	{
		content: ' ';
		position: absolute;
		left: 8px;
		top: 9px;
		width: 34px;
		height: 34px;
		background: #fd79a8;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
	}

	.monthArrows
	{
		width: 40px;
		text-align: center;
	}

	.monthArrows > i
	{
		padding: 7px;
		border: 1px solid #fd79a8;
		color: #fd79a8;
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
		cursor: pointer;
	}


	.calendar-right-panel
	{
		width: 400px;
		background: #2C3E50;
		border-top-right-radius: 10px;
		border-bottom-right-radius: 10px;
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.head_title
	{
		color: #fff;
		font-size: 16px;
		font-weight: 600;
		text-align: center;
		margin-top: 50px;
	}

	.head_separator
	{
		width: 50px;
		height: 3px;
		background: #FFF;
		margin: 15px;
	}

	.plan_posts_list
	{
		width: 100%;
		height: 100%;
		overflow: auto;
	}

	.plan_posts_list > .plan_box
	{
		padding: 10px 15px;
		margin: 5px 20px 10px;
		color: #FFF;
		font-size: 15px;
		font-weight: 600;
		border: 1px solid #999;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		position: relative;
	}

	.plan_posts_list > .plan_box i
	{
		padding: 5px;
		color: #fd79a8;
	}

	.remove_plan
	{
		position: absolute;
		right: 10px;
		top: 0;
		bottom: 0;
		margin: auto;
		height: 25px;
		padding: 5px;
		cursor: pointer;
		display: none;
	}

	.plan_box:hover .remove_plan
	{
		display: block;
	}

	.remove_plan > i
	{
		color: #fd79a8 !important;
	}

	.plan_post_title
	{
		max-width: 270px;
		overflow: hidden;
		white-space: nowrap;
	}

	.ws_tooltip2 > i
	{
		color: #DDD !important;
	}

	.ws_tooltip2
	{
		outline: none;
		box-shadow: none !important;
	}
</style>

<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<div style="display: flex; justify-content: center;">
	<div class="calendar-container">
		<div style="display: flex; align-items: center; width: 600px; justify-content: center;">
			<div class="monthArrows" id="prev_month"><i class="fa fa-arrow-left"></i></div>
			<div>
				<div id="calendar_area"></div>
				<div style="text-align: center; margin-top: 20px;">
					<button class="ws_btn ws_bg_dark" data-load-modal="add_schedule" type="button"><i class="fa fa-plus-circle"></i> Add new schedule</button>
					<a href="?page=fs-poster-schedule&view=list" class="ws_btn ws_bg_warning"><i class="fa fa-list"></i> List view</a>
				</div>
			</div>
			<div class="monthArrows" id="next_month"><i class="fa fa-arrow-right"></i></div>
		</div>
		<div class="calendar-right-panel">
			<div class="head_title">SCHEDULED POSTS</div>
			<div class="head_separator"></div>
			<div class="plan_posts_list"></div>
		</div>
	</div>
</div>


<script src="<?=PLUGIN_URL?>js/jquery-ui.js"></script>
<link rel="stylesheet" href="<?=PLUGIN_URL?>css/jquery-ui.css">

<script>
	function displayCalendar(_year , _month)
	{
		fsCode.ajax('schedule_get_calendar' , {'month': _month+1 , 'year': _year}, function(result)
		{
			var scheduleCountsByDay = {};
			$(".plan_posts_list").empty();
			for( var date in result['days'] )
			{
				var tInfo = result['days'][date],
					day = (new Date(tInfo['date'])).getDate();

				if( !(day in scheduleCountsByDay) )
				{
					scheduleCountsByDay[day] = 0;
				}
				scheduleCountsByDay[day]++;

				$(".plan_posts_list").append(
					'<div class="plan_box" data-schedule-id="'+tInfo['id']+'" data-date="'+tInfo['date']+'">' +
					'<div style="display: flex;">' +
					'<div class="plan_post_title"><i class="fa fa-thumbtack fa-thumb-tack"></i> ' + tInfo['title'] + ' </div>' +
					'<a '+(tInfo['post_id'] > 0 ? 'href="<?=site_url()?>/?p=' + tInfo['post_id'] + '" target="_blank"' : '')+' class="ws_tooltip2" title="' + tInfo['post_data'] + '" style="margin-left: 10px;"><i class="fa fa-external-link-square-alt fa-external-link-square"></i></a>' +
					'</div>' +
					'<div style="font-weight: 600; font-size: 13px;">' +
					'<i class="far fa-calendar-plus"></i> '+tInfo['date']+' , <i class="fa fa-clock"></i> ' + tInfo['time'] +
					'</div>' +
					'<span class="remove_plan ws_tooltip" data-title="Remove plan" data-float="left"><i class="far fa-trash fa-trash-o"></i></span>' +
					'</div>');
			}
			$(".plan_posts_list > .plan_box").hide();

			var htmlContent = "",
				febNumberOfDays = "",
				counter = 1,
				dateNow = new Date(_year , _month),
				month = dateNow.getMonth()+1,
				year = dateNow.getFullYear(),
				currentDate = new Date();

			if (month == 2)
			{
				febNumberOfDays = ( (year%100!=0) && (year%4==0) || (year%400==0)) ? '29' : '28';
			}

			var monthNames = ['', 'January','February','March','April','May','June','July','August','September','October','November', 'December'];
			var dayPerMonth = [null, '31', febNumberOfDays ,'31','30','31','30','31','31','30','31','30','31']

			var nextDate = new Date(month +' 1 ,'+year);
			var weekdays= nextDate.getDay();
			var weekdays2 = weekdays == 0 ? 7 : weekdays;
			var numOfDays = dayPerMonth[month];

			for( var w=1; w < weekdays2; w++ )
			{
				htmlContent += "<td class='monthPre'></td>";
			}

			while (counter <= numOfDays)
			{
				if (weekdays2 > 7)
				{
					weekdays2 = 1;
					htmlContent += "</tr><tr>";
				}

				var addClass = counter == currentDate.getDate() && month == (currentDate.getMonth()+1) && year == currentDate.getFullYear() ? ' dayNow' : '';

				htmlContent +="<td class='days"+addClass+"'"+( counter in scheduleCountsByDay ? ' data-count="'+scheduleCountsByDay[counter]+'"' : '' )+" data-date=\"" + (year + '-' + fsCode.zeroPad(month) + '-' + fsCode.zeroPad(counter)) + "\"><span>"+counter+"</span></td>";

				weekdays2++;
				counter++;
			}

			var calendarBody =
				"<table class='calendar'>" +
				"<tr class='yearMonthHead'>" +
				"<th colspan='7'>" +
				monthNames[month] + " " + year +
				"</th>" +
				"</tr>";

			calendarBody +=
				"<tr class='dayNames'>" +
				"<td>Mon</td>" +
				"<td>Tue</td>"+
				"<td>Wed</td>"+
				"<td>Thu</td>"+
				"<td>Fri</td>"+
				"<td>Sat</td>"+
				"<td>Sun</td>" +
				"</tr>";

			calendarBody += "<tr>";
			calendarBody += htmlContent;
			calendarBody += "</tr></table>";

			$("#calendar_area").html( calendarBody );

			$("#calendar_area .days[data-count]:first").trigger('click');
		});
	}

	jQuery(document).ready(function()
	{
		var now = new Date(),
			currentMonth = now.getMonth(),
			currentYear = now.getFullYear();

		displayCalendar(currentYear, currentMonth);

		$("#prev_month").click(function()
		{
			currentMonth--;
			if( currentMonth == -1 )
			{
				currentMonth = 11;
				currentYear--;
			}

			displayCalendar(currentYear, currentMonth);
		});

		$("#next_month").click(function()
		{
			currentMonth++;
			if( currentMonth == 12 )
			{
				currentMonth = 0;
				currentYear++;
			}

			displayCalendar(currentYear, currentMonth);
		});

		$(".plan_posts_list").on('click' , '.remove_plan' , function()
		{
			var scheduleId = $(this).closest('.plan_box').data('schedule-id');

			fsCode.confirm('Do you want to remove this schedule?<br>Note: if you remove this shceudle then all planned posts also will be stopped automatically.', 'warning', function()
			{
				fsCode.ajax('delete_schedule' , {'id': scheduleId}, function()
				{
					displayCalendar(currentYear, currentMonth);
				});
			});

		});

		$("#calendar_area").on('click', '.days[data-count]', function( )
		{
			var date = $(this).attr('data-date');

			$(".plan_posts_list > .plan_box:not([data-date=\"" + date + "\"])").slideUp(200);
			$(".plan_posts_list > .plan_box[data-date=\"" + date + "\"]").slideDown(200);

			$("#calendar_area .dayNow").removeClass('dayNow');
			$(this).addClass('dayNow');
		});
	});

</script>


