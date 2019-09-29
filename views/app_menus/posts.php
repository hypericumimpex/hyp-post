<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$scheudleId = FS_post('schedule_id' , '0' , 'int');
?>

<style>
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
		padding-bottom: 18px;
		display: flex;
		justify-content: space-between;
	}
	.report_title .title1
	{
		font-size: 17px;
		font-weight: 700;
		color: #7f8c8d;
	}

	.ws_table2
	{
		width: 100%;
		border-collapse: separate;
		border: 0px;
		border-spacing: 0px 5px;
	}
	.ws_table2 tr:not(:last-child) td
	{
		border-bottom: 1px solid #e0e0e0;
	}
	.ws_table2  td
	{
		padding: 7px;
		color: #555;
		vertical-align: middle;
		height: 74px;
	}

	.td_post_inf
	{
		display: flex;
		align-items: center;
	}
	.tg_img>img
	{
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
		width: 40px;
		height: 40px;
	}

	.post_names
	{
		padding-left: 15px;
	}

	.post_acc_name
	{
		font-size: 16px;
		font-weight: 600;
		color: #7b7b7b;
	}

	.post_link > a
	{
		color: #ff7675 !important;
		text-decoration: none;
		outline: none !important;
		border: 0 !important;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		box-shadow: none !important;
		text-shadow: none !important;
		font-size: 13px;
		font-weight: 600;
	}
	.post_time
	{
		color: #888;
		font-size: 12px;
		font-weight: 600;
	}
	.stat_span1
	{
		color: #f39c12;
		font-weight: 700;
	}
	.stat_span2
	{
		color: #9b59b6;
		font-weight: 700;
	}
	.stat_span3
	{
		color: #e74c3c;
		font-weight: 700;
	}
	.stat_span4
	{
		color: #2ecc71;
		font-weight: 700;
	}
</style>

<div class="reports_group">
	<div style="width: 100%;">
		<div class="report_title">
			<div class="title1">
				<i class="fa fa-copy"></i> <?=esc_html__('Last posts' , 'fs-poster');?>
			</div>
			<div>
				Count of rows:
				<select class="ws_form_element" id="rows_count_select" style="width: 60px; height: 28px !important; line-height: 1px; padding-left: 15px;">
					<option selected>4</option>
					<option>8</option>
					<option>15</option>
				</select>

				<button class="ws_btn ws_bg_danger ws_btn_small clearLogBtn" type="button"><i class="fa fa-trash"></i> Clear logs</button>
			</div>
		</div>
		<div class="report_chart_box" style="min-height: 405px; background: #F7F7F7; border-top: 1px solid #DDD; border-bottom: 1px solid #DDD; margin-left: -20px; margin-right: -20px; padding: 0 20px;">
			<table class="ws_table2">
				<thead>
					<tr>
						<th style="text-align: left; color: #BBB; padding-left: 25px;"><?=esc_html__('PROFILE / POST' , 'fs-poster');?></th>
						<th style="text-align: left; color: #BBB; padding-left: 5px;"><?=esc_html__('POST LINK' , 'fs-poster');?></th>
						<th style="text-align: left; color: #BBB; padding-left: 23px;"><?=esc_html__('STATUS' , 'fs-poster');?></th>
						<th style="text-align: left; color: #BBB; padding-left: 23px;"><?=esc_html__('LIKES' , 'fs-poster');?></th>
					</tr>
				</thead>
				<tbody id="report3_table">

				</tbody>
			</table>
		</div>
		<div style="text-align: center; margin: 15px; margin-bottom: -5px;">
			<button type="button" class="ws_btn ws_btn_small ws_bg_info" id="r3_page_prev"><i class="fa fa-arrow-left"></i> <?=esc_html__('Prev' , 'fs-poster');?></button>
			<button type="button" class="ws_btn ws_btn_small ws_bg_info" style="margin-left: 10px;" id="r3_page_next"><?=esc_html__('Next' , 'fs-poster');?> <i class="fa fa-arrow-right"></i> </button>
		</div>
		<script>

			function loadData( r3_page )
			{
				var rows_count = $("#rows_count_select").val();

				fsCode.ajax('report3_data' , {'page': r3_page, 'schedule_id': '<?=$scheudleId?>', 'rows_count': rows_count} , function(result)
				{
					$("#report3_table").empty();

					if( result['disable_btn'] )
					{
						$("#r3_page_next").attr('disabled' , true);
					}

					for( var i in result['data'] )
					{
						var statusBtn;

						if( result['data'][i]['is_sended'] == '1' && result['data'][i]['status'] == 'ok' )
						{
							statusBtn = '<button class="ws_btn ws_bg_success ws_btn_small" type="button"><i class="fa fa-check"></i> '+"<?=esc_html__('success' , 'fs-poster');?>"+'</button>';
						}
						else if( result['data'][i]['is_sended'] == '1' && result['data'][i]['status'] == 'error' )
						{
							statusBtn = '<button class="ws_btn ws_bg_danger ws_btn_small ws_tooltip" data-title="'+result['data'][i]['error_msg']+'" type="button"><i class="fa fa-times"></i> '+"<?=esc_html__('error' , 'fs-poster');?>"+'</button>';
						}
						else
						{
							statusBtn = '<button class="ws_btn ws_bg_warning ws_btn_small" type="button"><i class="fa fa-check"></i> '+"<?=esc_html__('not sent' , 'fs-poster');?>"+'</button>';
						}

						var driverIcon = result['data'][i]['driver'];
						if( driverIcon == 'ok' )
							driverIcon = 'odnoklassniki';

						$("#report3_table").append(
							'<tr>' +
							'<td class="td_post_inf">' +
							'<div class="tg_img">' +
							'<img src="' + result['data'][i]['cover'] + '" onerror="$(this).attr(\'src\', \'<?=plugin_dir_url(__FILE__).'../../images/no-photo.png'?>\');">' +
							'</div>' +
							'<div class="post_names">' +
							'<div class="post_acc_name">' +
							result['data'][i]['name'] +
							'<a href="'+result['data'][i]['profile_link']+'" target="_blank" class="ws_btn ws_tooltip" data-title="<?=esc_html__('Profile link' , 'fs-poster');?>" style="font-size: 13px; color: #fd79a8; background-color: transparent !important;"><i class="fa fa-external-link fa-external-link-alt"></i></a>' +
							'</div>' +
							'<div class="post_time">' +
							'<i class="far fa-clock"></i> '+result['data'][i]['date']+
							'<a href="<?=site_url()?>/?p='+result['data'][i]['wp_post_id']+'" target="_blank" class="ws_btn ws_tooltip" data-title="<?=esc_html__('Post link' , 'fs-poster');?>" style="font-size: 13px; color: #fd79a8; background-color: transparent !important;"><i class="fa fa-external-link fa-link"></i></a>' +
							'</div>' +
							'</div>' +
							'</td>' +
							'<td>' +
							(( result['data'][i]['is_sended'] == '1' && result['data'][i]['status'] == 'ok' ) ?
								(
									'<div class="post_link">' +
									'<a href="' + result['data'][i]['post_link'] + '" target="_blank">' +
									'<i class="fa fa-external-link fa-external-link-alt"></i> ' + "<?=esc_html__('Publication Link' , 'fs-poster');?>" +
									'</a>' +
									'</div>'
								) : ' --- ') +
							'<div><i class="fab fa-'+driverIcon+'"></i> ' + (result['data'][i]['driver'][0].toUpperCase() + result['data'][i]['driver'].substring(1)) + ' > ' + result['data'][i]['node_type'] + ( result['data'][i]['feed_type'] != '' ? ' > ' + result['data'][i]['feed_type'] : '' ) + '</div>'+
							'</td>' +
							'<td class="status_lnk">'+statusBtn+'</td>' +
							'<td>' +
							( result['data'][i]['driver'] == 'linkedin' || result['data'][i]['driver'] == 'reddit' || result['data'][i]['driver'] == 'tumblr' || result['data'][i]['driver'] == 'google_b' || result['data'][i]['driver'] == 'telegram' || result['data'][i]['driver'] == 'medium' ? '---' :
								'<div><i class="far fa-eye"></i> Hits: <span class="stat_span1">' + result['data'][i]['hits'] + '</span></div>' +
								'<div><i class="far fa-thumbs-up"></i> Likes: <span class="stat_span2">' + result['data'][i]['insights']['like'] + '</span> ' + ( result['data'][i]['insights']['details'] != '' ? '<span class="ws_tooltip" data-title="'+result['data'][i]['insights']['details']+'"><i class="fa fa-info-circle"></i></span></div>' : '') +
								'<div><i class="far fa-comment-dots"></i> Comments: <span class="stat_span3">' + (typeof result['data'][i]['insights']['comments'] != 'undefined' ? result['data'][i]['insights']['comments'] : 0) + '</span></div>' +
								'<div><i class="far fa-share-square"></i> Shares: <span class="stat_span4">' + (typeof result['data'][i]['insights']['shares'] != 'undefined' ? result['data'][i]['insights']['shares'] : 0) + '</span></div>') +
							'</td>' +
							'</tr>');
					}
				});
			}

			jQuery(document).ready(function()
			{
				var r3_page = 0;

				$("#rows_count_select").change(function()
				{
					loadData( r3_page );
				});

				$("#r3_page_next , #r3_page_prev").click(function()
				{
					if( $(this).attr('id') == 'r3_page_prev' && r3_page == 1 )
					{
						return;
					}

					if( $(this).attr('id') == 'r3_page_prev' )
					{
						r3_page--;
						$("#r3_page_next").removeAttr('disabled');
					}
					else
					{
						r3_page++;
						$("#r3_page_prev").removeAttr('disabled');
					}

					if( r3_page == 1 )
					{
						$("#r3_page_prev").attr('disabled' , true);
					}

					loadData( r3_page );
				});
				$("#r3_page_next").click();

				$(".clearLogBtn").click(function()
				{
					fsCode.confirm('Are you sure you want to clear logs?', 'danger' , function()
					{
						fsCode.ajax('fs_clear_logs' , {} , function()
						{
							location.reload();
						});
					});
				});
			});
		</script>
	</div>
</div>