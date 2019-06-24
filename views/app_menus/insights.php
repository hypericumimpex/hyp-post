<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$userId = (int)get_current_user_id();
//report 3 data
$report3Data = wpDB()->get_results("SELECT driver , SUM(visit_count) AS c FROM ".wpTable('feeds')." tb1 WHERE ( (node_type='account' AND (SELECT COUNT(0) FROM ".wpTable('accounts')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM ".wpTable('account_nodes')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId')>0 OR tb2.is_public=1)) ) GROUP BY driver ORDER BY c DESC LIMIT 0,10" , ARRAY_A);
$report3 = [
	'data' => [],
	'labels' => []
];
foreach($report3Data AS $r3Data)
{
	$report3['data'][] = $r3Data['c'];
	$report3['labels'][] = esc_html(ucfirst($r3Data['driver']));
}

//report 4 data
$report4Data = wpDB()->get_results("SELECT CONCAT(node_id,'_',node_type) AS node , SUM(visit_count) AS c FROM ".wpTable('feeds')." tb1 WHERE ( (node_type='account' AND (SELECT COUNT(0) FROM ".wpTable('accounts')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM ".wpTable('account_nodes')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId')>0 OR tb2.is_public=1)) ) GROUP BY CONCAT(node_id,'_',node_type) ORDER BY c DESC LIMIT 0,10" , ARRAY_A);
$report4 = [
	'data' => [],
	'labels' => []
];
foreach($report4Data AS $r4Data)
{
	$node = explode('_' , $r4Data['node']);
	$nodeType = $node[1];
	$nodeId = $node[0];

	$nodeInfTable = $nodeType == 'account' ? 'accounts' : 'account_nodes';

	$nodeInf = wpFetch($nodeInfTable , $nodeId);

	if( !$nodeInf )
		continue;

	if( $r4Data['c'] == 0 )
		continue;

	$report4['data'][] = $r4Data['c'];
	$report4['labels'][] = esc_html(ucfirst($nodeInf['driver']) . ' / ' . cutText($nodeInf['name'] , 20));
}

$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-d');
$sharesThisMonth = wpDB()->get_row("SELECT COUNT(0) AS c FROM ".wpTable('feeds')." tb1 WHERE is_sended='1' AND status='ok' AND CAST(send_time AS DATE) BETWEEN '$monthStart' AND '$monthEnd' AND ( (node_type='account' AND (SELECT COUNT(0) FROM ".wpTable('accounts')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM ".wpTable('account_nodes')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId')>0 OR tb2.is_public=1)) )" , ARRAY_A);

$hitsThisMonth = wpDB()->get_row("SELECT SUM(visit_count) AS c FROM ".wpTable('feeds')." tb1 WHERE is_sended='1' AND status='ok' AND CAST(send_time AS DATE) BETWEEN '$monthStart' AND '$monthEnd' AND ( (node_type='account' AND (SELECT COUNT(0) FROM ".wpTable('accounts')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM ".wpTable('account_nodes')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId')>0 OR tb2.is_public=1)) )" , ARRAY_A);

$accounts = wpDB()->get_row(wpDB()->prepare( "SELECT COUNT(0) AS c FROM ".wpTable('accounts') . " tb1 WHERE is_public=1 OR user_id=%d", [get_current_user_id()] ) , ARRAY_A);

$nodes = wpDB()->get_row(wpDB()->prepare("SELECT COUNT(0) AS c FROM ".wpTable('account_nodes') . " tb1 WHERE is_public=1 OR user_id=%d", [get_current_user_id()]) , ARRAY_A);

$hitsThisMonthSchedule = wpDB()->get_row("SELECT SUM(visit_count) AS c FROM ".wpTable('feeds')." tb1 WHERE is_sended='1' AND status='ok' AND CAST(send_time AS DATE) BETWEEN '$monthStart' AND '$monthEnd' AND schedule_id>0 AND ( (node_type='account' AND (SELECT COUNT(0) FROM ".wpTable('accounts')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId' OR tb2.is_public=1))>0) OR (node_type<>'account' AND (SELECT COUNT(0) FROM ".wpTable('account_nodes')." tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id='$userId')>0 OR tb2.is_public=1)) )" , ARRAY_A);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<style>
	.stat_boxes
	{
		margin-top: 20px;
		margin-left: 10px;
		margin-right: 10px;
		display: flex;
		justify-content: space-around;

	}

	.stat_boxes>div
	{
		width: 190px;
		height: 90px;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;

		-webkit-box-shadow: 0 0 10px 0 #FFF;
		-moz-box-shadow: 0 0 10px 0 #FFF;
		box-shadow: 0 0 10px 0 #FFF;
	}

	.stat_boxes .num1
	{
		font-size: 30px;
		margin-bottom: 15px;
		font-weight: 600;
	}

	.stat_boxes .title1
	{
		font-size: 15px;
		text-align: center;
		padding-left: 7px;
		padding-right: 7px;
	}
	.reports_group
	{
		display: flex;
		margin-top: 25px;
	}
	.reports_group>div
	{
		margin-left: 25px;
		margin-right: 25px;
		background: #FFF;
		border: 1px solid #DDD;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		padding: 20px;
	}
	.reports_group>div:not(:first-child)
	{
		margin-left: 0;
	}

	.report_title
	{
		margin-left: 10px;
		margin-right: 22px;
		padding-bottom: 25px;
		display: flex;
		justify-content: space-between;
	}
	.report_title .title1
	{
		font-size: 17px;
		font-weight: 700;
		color: #7f8c8d;
	}

	.sad_smile
	{
		position: absolute;
		right: 5px;
		top: 5px;
	}

	.sad_smile>img
	{
		width: 30px;
		height: 30px;
	}

	.ws_bg_purple
	{
		position: relative;
	}
</style>

<div class="stat_boxes">

	<div class="ws_bg_success">
		<div class="num1"><?=(int)$sharesThisMonth['c']?></div>
		<div class="title1"><?=esc_html__('Shares this month' , 'fs-poster');?></div>
	</div>

	<div class="ws_bg_warning">
		<div class="num1"><?=(int)$hitsThisMonth['c']?></div>
		<div class="title1"><?=esc_html__('Views this month' , 'fs-poster');?></div>
	</div>

	<div class="ws_bg_danger">
		<div class="num1"><?=(int)$accounts['c']?></div>
		<div class="title1"><?=esc_html__('Total accounts' , 'fs-poster');?></div>
	</div>

	<div class="ws_bg_info">
		<div class="num1"><?=(int)$nodes['c']?></div>
		<div class="title1"><?=esc_html__('Total communities' , 'fs-poster');?></div>
	</div>

	<div class="ws_bg_purple">
		<div class="num1"><?=(int)$hitsThisMonthSchedule['c'] . ( (int)$hitsThisMonthSchedule['c'] == 0 ? '<a href="?page=fs-poster-schedule&add" class="sad_smile ws_tooltip" data-title="Click and create a schedules and increase your site visitors now" data-float="left"><img src="'. (plugin_dir_url(__FILE__).'../../images/sad.png') . '" ></a>' : '' )?></div>
		<div class="title1"><?=esc_html__('Views this month from Schedules' , 'fs-poster')?></div>
	</div>
</div>

<div class="reports_group">
	<div style="width: 50%;">
		<div class="report_title">
			<div class="title1"><i class="fa fa-chart-bar"></i> <?=esc_html__('Shares count' , 'fs-poster');?></div>
			<div id="report1_types">
				<button class="ws_btn ws_bg_default ws_btn_small" data-type="dayly"><?=esc_html__('Daily' , 'fs-poster');?></button>
				<button class="ws_btn ws_bg_default ws_btn_small" data-type="monthly"><?=esc_html__('Monthly' , 'fs-poster');?></button>
				<button class="ws_btn ws_bg_default ws_btn_small" data-type="yearly"><?=esc_html__('Annually' , 'fs-poster');?></button>
			</div>
		</div>
		<div class="report_chart_box">

			<canvas id="sharesChart" ></canvas>
			<script>
				jQuery(document).ready(function()
				{
					var myChart1;

					$("#report1_types>[data-type]").click(function()
					{
						var type = $(this).attr('data-type');

						if( $(this).hasClass('ws_bg_dark') )
							return;

						$("#report1_types>.ws_bg_dark").removeClass('ws_bg_dark').addClass('ws_bg_default');
						$(this).removeClass('ws_bg_default').addClass('ws_bg_dark');

						fsCode.ajax('report1_data' , {'type': type} , function(result)
						{
							if( myChart1 )
							{
								myChart1.destroy();
							}
							var ctx = document.getElementById("sharesChart").getContext('2d');
							myChart1 = new Chart(ctx, {
								type: 'bar',
								data: {
									labels: result['labels'],
									datasets: [{
										data: result['data'],
										backgroundColor: [
											'rgba(255, 99, 132, 0.6)',
											'rgba(54, 162, 235, 0.6)',
											'rgba(255, 206, 86, 0.6)',
											'rgba(75, 192, 192, 0.6)',
											'rgba(153, 102, 255, 0.6)',
											'rgba(255, 159, 64, 0.6)',
											'rgba(46, 204, 113, 0.6)',
											'rgba(230, 126, 34, 0.6)',
											'rgba(155, 89, 182 0.6)',
											'rgba(72, 126, 176,0.6)'
										],
										borderColor: [
											'rgba(255, 99, 132, 0.8)',
											'rgba(54, 162, 235, 0.8)',
											'rgba(255, 206, 86, 0.8)',
											'rgba(75, 192, 192, 0.8)',
											'rgba(153, 102, 255, 0.8)',
											'rgba(255, 159, 64, 0.8)',
											'rgba(46, 204, 113, 0.8)',
											'rgba(230, 126, 34, 0.8)',
											'rgba(155, 89, 182 0.8)',
											'rgba(72, 126, 176,0.8)'
										],
										borderWidth: 1
									}]
								},
								options: {
									legend: {
										display: false
									},
									scales: {
										yAxes: [{
											ticks: {
												beginAtZero:true
											}
										}]
									}
								}
							});
						});
					}).eq(0).trigger('click');
				});
			</script>

		</div>
	</div>

	<div style="width: 50%;">
		<div class="report_title">
			<div class="title1"><i class="fa fa-chart-bar"></i> <?=esc_html__('View count' , 'fs-poster');?></div>
			<div id="report2_types">
				<button class="ws_btn ws_bg_default ws_btn_small" data-type="dayly"><?=esc_html__('Daily' , 'fs-poster');?></button>
				<button class="ws_btn ws_bg_default ws_btn_small" data-type="monthly"><?=esc_html__('Monthly' , 'fs-poster');?></button>
				<button class="ws_btn ws_bg_default ws_btn_small" data-type="yearly"><?=esc_html__('Annually' , 'fs-poster');?></button>
			</div>
		</div>
		<div class="report_chart_box">

			<canvas id="hitsChart" ></canvas>
			<script>
				jQuery(document).ready(function()
				{
					var myChart2;

					$("#report2_types>[data-type]").click(function()
					{
						var type = $(this).attr('data-type');

						if( $(this).hasClass('ws_bg_dark') )
							return;

						$("#report2_types>.ws_bg_dark").removeClass('ws_bg_dark').addClass('ws_bg_default');
						$(this).removeClass('ws_bg_default').addClass('ws_bg_dark');

						fsCode.ajax('report2_data' , {'type': type} , function(result)
						{
							if( myChart2 )
							{
								myChart2.destroy();
							}

							var ctx = document.getElementById("hitsChart").getContext('2d');
							myChart2 = new Chart(ctx, {
								type: 'line',
								data: {
									"labels": result['labels'],

									"datasets":[{
										"data": result['data'],
										"fill": true,
										"borderColor": "rgb(75, 192, 192 , 0.7)",
										"lineTension": 0.1
									}]
								},
								options: {
									legend: {
										display: false
									},
									scales: {
										yAxes: [{
											ticks: {
												beginAtZero:true
											}
										}]
									}
								}
							});
						});
					}).eq(0).trigger('click');
				});
			</script>

		</div>
	</div>
</div>

<div class="reports_group">
	<div style="width: 50%;">
		<div class="report_title">
			<div class="title1"><i class="fa fa-copy"></i> <?=esc_html__('Social networks comparison ( by views )' , 'fs-poster');?></div>
		</div>
		<div class="report_chart_box">
			<canvas id="compChart1"></canvas>
			<script>
				jQuery(document).ready(function()
				{
					var ctx = document.getElementById("compChart1").getContext('2d');
					var myPieChart = new Chart(ctx, {
						type: 'pie',
						data: {
							datasets: [{
								backgroundColor: [
									'rgba(255, 99, 132, 0.7)',
									'rgba(54, 162, 235, 0.7)',
									'rgba(255, 206, 86, 0.7)',
									'rgba(75, 192, 192, 0.7)',
									'rgba(153, 102, 255, 0.7)',
									'rgba(255, 159, 64, 0.7)',
									'rgba(46, 204, 113, 0.7)',
									'rgba(230, 126, 34, 0.7)',
									'rgba(155, 89, 182 0.7)',
									'rgba(72, 126, 176,0.7)'
								],
								data: <?=json_encode($report3['data'])?>
							}],

							labels: <?=json_encode($report3['labels'])?>
						},
						options: {
							legend: {
								position: 'right',

								labels: {
									usePointStyle: true,
									generateLabels: function(chart)
									{
										var data = chart.data;
										if (data.labels.length && data.datasets.length)
										{
											return data.labels.map(function(label, i)
											{
												var meta = chart.getDatasetMeta(0);
												var ds = data.datasets[0];
												var arc = meta.data[i];
												var custom = arc && arc.custom || {};
												var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
												var arcOpts = chart.options.elements.arc;
												var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
												var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
												var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

												var value = chart.config.data.datasets[arc._datasetIndex].data[arc._index];

												return {
													// Instead of `text: label,`
													// We add the value to the string
													text: label + " : " + value,
													fillStyle: fill,
													strokeStyle: stroke,
													lineWidth: bw,
													hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
													index: i
												};
											});
										} else {
											return [];
										}
									}
								}


							},
							responsive: true
						}
					});
				});
			</script>
		</div>
	</div>

	<div style="width: 50%;">
		<div class="report_title">
			<div class="title1"><i class="fa fa-copy"></i> <?=esc_html__('Accounts comparison ( by views )' , 'fs-poster');?></div>
		</div>
		<div class="report_chart_box">
			<canvas id="compChart2"></canvas>
			<script>
				jQuery(document).ready(function()
				{
					var ctx = document.getElementById("compChart2").getContext('2d');
					var myPieChart = new Chart(ctx, {
						type: 'doughnut',
						data: {
							datasets: [{
								backgroundColor: [
									'rgba(255, 99, 132, 0.7)',
									'rgba(54, 162, 235, 0.7)',
									'rgba(255, 206, 86, 0.7)',
									'rgba(75, 192, 192, 0.7)',
									'rgba(153, 102, 255, 0.7)',
									'rgba(255, 159, 64, 0.7)',
									'rgba(46, 204, 113, 0.7)',
									'rgba(230, 126, 34, 0.7)',
									'rgba(155, 89, 182 0.7)',
									'rgba(72, 126, 176,0.7)'
								],
								data: <?=json_encode($report4['data'])?>
							}],

							labels: <?=json_encode($report4['labels'])?>
						},
						options: {
							legend: {
								position: 'right',

								labels: {
									usePointStyle: true,
									generateLabels: function(chart)
									{
										var data = chart.data;
										if (data.labels.length && data.datasets.length)
										{
											return data.labels.map(function(label, i)
											{
												var meta = chart.getDatasetMeta(0);
												var ds = data.datasets[0];
												var arc = meta.data[i];
												var custom = arc && arc.custom || {};
												var getValueAtIndexOrDefault = Chart.helpers.getValueAtIndexOrDefault;
												var arcOpts = chart.options.elements.arc;
												var fill = custom.backgroundColor ? custom.backgroundColor : getValueAtIndexOrDefault(ds.backgroundColor, i, arcOpts.backgroundColor);
												var stroke = custom.borderColor ? custom.borderColor : getValueAtIndexOrDefault(ds.borderColor, i, arcOpts.borderColor);
												var bw = custom.borderWidth ? custom.borderWidth : getValueAtIndexOrDefault(ds.borderWidth, i, arcOpts.borderWidth);

												var value = chart.config.data.datasets[arc._datasetIndex].data[arc._index];

												return {
													// Instead of `text: label,`
													// We add the value to the string
													text: label + " : " + value,
													fillStyle: fill,
													strokeStyle: stroke,
													lineWidth: bw,
													hidden: isNaN(ds.data[i]) || meta.data[i].hidden,
													index: i
												};
											});
										} else {
											return [];
										}
									}
								}


							},
							responsive: true
						}
					});
				});
			</script>
		</div>
	</div>
</div>

