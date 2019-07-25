<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$appsCount = FSwpDB()->get_results("SELECT driver, COUNT(0) AS _count FROM ".FSwpTable('apps')." GROUP BY driver" , ARRAY_A);
$appCounts = [
	'fb'        =>  [0 , ['app_id' , 'app_key']],
	'twitter'   =>  [0 , ['app_key' , 'app_secret']],
	'linkedin'  =>  [0 , ['app_id' , 'app_secret']],
	'vk'        =>  [0 , ['app_id' , 'app_secret']],
	'pinterest' =>  [0 , ['app_id' , 'app_secret']],
	'reddit'    =>  [0 , ['app_id' , 'app_secret']],
	'tumblr'    =>  [0 , ['app_key' , 'app_secret']],
	'ok'	    =>  [0 , ['app_id' , 'app_key' , 'app_secret']],
	'medium'    =>  [0 , ['app_id' , 'app_secret']],
];
foreach( $appsCount AS $aInf )
{
	if( isset($appCounts[$aInf['driver']]) )
	{
		$appCounts[$aInf['driver']][0] = $aInf['_count'];
	}
}

$tab = FS_get('tab' , 'fb' , 'string');
if( !key_exists($tab , $appCounts) )
{
	$tab = 'fb';
}

$appList = FSwpFetchAll('apps' , ['driver' => $tab]);

?>
<style>
	.fs_social_network_div
	{
		width: 100%;
		height: 35px;
		background: #FFF;
		border: 1px solid #DDD;
		margin-top: 3px;
		padding-left: 15px;
		display: flex;
		align-items: center;
		justify-content: space-between;
		font-size: 14px;
		color: #666 !important;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;

		-webkit-box-shadow: 2px 2px 2px 0px #DDD !important;
		-moz-box-shadow: 2px 2px 2px 0px #DDD !important;
		box-shadow: 2px 2px 2px 0px #DDD !important;

		cursor: pointer;

		text-decoration: none;
	}
	.fs_social_network_div:hover
	{
		background: #f9f9f9;
	}
	.fs_social_network_div i
	{
		margin-right: 5px;
		color: #74b9ff;
	}
	.fs_snd_badge
	{
		margin-right: 10px;
		background: #fd79a8;
		color: #FFF;
		width: 18px;
		height: 18px;
		-webkit-border-radius: 18px;
		-moz-border-radius: 18px;
		border-radius: 18px;
		text-align: center;
		font-size: 11px;
		font-weight: 700;
		-webkit-box-shadow: 2px 2px 2px 0px #EEE;
		-moz-box-shadow: 2px 2px 2px 0px #EEE;
		box-shadow: 2px 2px 2px 0px #EEE;
	}

	.fs_snd_active
	{
		border-left: 3px solid #fd79a8 !important;
		background: #f9f9f9 !important;
	}
	.fs_snd_active .fs_snd_badge
	{
		margin-right: 12px;
	}
	#app_supports
	{
			width: 200px;
		margin-top: 70px;
		margin-left: 20px;
	}

	.credential_name
	{
		font-size: 12px;
		width: 68px;
		display: inline-block;
	}
	.credential_value
	{
		font-size: 12px;
		font-weight: 500;
		display: inline-block;
	}
</style>

<div style="display: flex;">
	<div id="app_supports">
		<a href="?page=fs-poster-app&tab=fb" class="fs_social_network_div<?=$tab=='fb'?' fs_snd_active':''?>" data-setting="fb">
			<div><i class="fab fa-facebook-square"></i> Facebook</div>
			<div class="fs_snd_badge"><?=$appCounts['fb'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=twitter" class="fs_social_network_div<?=$tab=='twitter'?' fs_snd_active':''?>" data-setting="twitter">
			<div><i class="fab fa-twitter-square"></i> Twitter</div>
			<div class="fs_snd_badge"><?=$appCounts['twitter'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=linkedin" class="fs_social_network_div<?=$tab=='linkedin'?' fs_snd_active':''?>" data-setting="linkedin">
			<div><i class="fab fa-linkedin"></i> Linkedin</div>
			<div class="fs_snd_badge"><?=$appCounts['linkedin'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=vk" class="fs_social_network_div<?=$tab=='vk'?' fs_snd_active':''?>" data-setting="vk">
			<div><i class="fab fa-vk"></i> VK</div>
			<div class="fs_snd_badge"><?=$appCounts['vk'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=pinterest" class="fs_social_network_div<?=$tab=='pinterest'?' fs_snd_active':''?>" data-setting="pinterest">
			<div><i class="fab fa-pinterest"></i> Pinterest</div>
			<div class="fs_snd_badge"><?=$appCounts['pinterest'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=reddit" class="fs_social_network_div<?=$tab=='reddit'?' fs_snd_active':''?>" data-setting="reddit">
			<div><i class="fab fa-reddit"></i> Reddit</div>
			<div class="fs_snd_badge"><?=$appCounts['reddit'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=tumblr" class="fs_social_network_div<?=$tab=='tumblr'?' fs_snd_active':''?>" data-setting="tumblr">
			<div><i class="fab fa-tumblr-square"></i> Tumblr</div>
			<div class="fs_snd_badge"><?=$appCounts['tumblr'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=ok" class="fs_social_network_div<?=$tab=='ok'?' fs_snd_active':''?>" data-setting="ok">
			<div><i class="fab fa-odnoklassniki"></i> OK.ru</div>
			<div class="fs_snd_badge"><?=$appCounts['ok'][0]?></div>
		</a>
		<a href="?page=fs-poster-app&tab=medium" class="fs_social_network_div<?=$tab=='medium'?' fs_snd_active':''?>" data-setting="medium">
			<div><i class="fab fa-medium"></i> Medium</div>
			<div class="fs_snd_badge"><?=$appCounts['medium'][0]?></div>
		</a>
	</div>
	<div style="width: 90%; margin: 40px;" id="app_content">
		<div style="margin: 40px 80px;">
			<span style="color: #888; font-size: 17px; font-weight: 600; line-height: 36px;"><span id="apps_count"><?php print count($appList);?></span> <?=esc_html__('app(s) added' , 'fs-poster');?></span>
			<button type="button" class="ws_btn ws_bg_dark" style="float: right;" data-load-modal="add_app" data-parameter-fields="<?=implode(',' , $appCounts[$tab][1])?>" data-parameter-driver="<?=$tab?>"><i class="fa fa-plus"></i> <?=esc_html__('ADD APP' , 'fs-poster');?></button>
		</div>
		<div class="ws_table_wraper" >
			<table class="ws_table" id="app_list_table">
				<thead>
				<tr>
					<th><?=esc_html__('NAME' , 'fs-poster');?> <i class="fa fa-caret-down"></i></th>
					<th><?=esc_html__('CREDENTIALS' , 'fs-poster');?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach($appList AS $appInf)
				{
					?>
					<tr data-id="<?=$appInf['id']?>">
						<td>
							<img class="ws_img_style" src="<?=FSappIcon($appInf)?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../../images/no-photo.png'?>');">
							<span style="vertical-align: middle;"><?php print esc_html($appInf['name']);?></span>
						</td>
						<td>
							<?php
							foreach($appCounts[$tab][1] AS $crdntls)
							{
								$label = 'App ID';
								if( $crdntls == 'app_key' )
								{
									$label = 'App Key';
								}
								else if( $crdntls == 'app_secret' )
								{
									$label = 'App Secret';
								}
								?>
								<div>
									<div class="credential_name"><?=$label?>:</div>
									<div class="credential_value"><?=esc_html($appInf[$crdntls])?></div>
								</div>
								<?php
							}
							if( $appInf['is_standart'] >= 1 )
							{
								?>
								<button class="delete_btn_desing ws_tooltip" data-title="<?=esc_html__('You can\'t delete this app' , 'fs-poster');?>" data-float="left">
									<i class="fa fa-exclamation-circle" style="color: #72adff !important;"></i>
								</button>
								<?php
							}
							else
							{
								?>
								<button class="delete_app_btn delete_btn_desing ws_tooltip" data-title="<?=esc_html__('Delete app' , 'fs-poster');?>" data-float="left">
									<i class="fa fa-trash "></i>
								</button>
								<?php
							}
							?>
						</td>
					</tr>
					<?php
				}
				if( empty($appList) )
				{
					?>
					<tr><td colspan="100%" style="color: #999;"><?=esc_html__('No app found' , 'fs-poster');?></td></tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function()
	{
		$("#app_content").on('click' , '.delete_app_btn' , function()
		{
			var tr = $(this).closest('tr'),
				aId = tr.attr('data-id');

			fsCode.confirm("<?=esc_html__('Are you sure you want to delete?' , 'fs-poster');?>" , 'danger' , function ()
			{
				fsCode.ajax('delete_app' , {'id': aId} , function(result)
				{
					tr.fadeOut(300, function()
					{
						$(this).remove();
						if( $(".ws_table>tbody>tr").length == 0 )
						{
							$(".ws_table>tbody").append('<tr><td colspan="100%" style="color: #999;">No accound found</td></tr>').children('tr').hide().fadeIn(200);
						}
						$("#apps_count").text(parseInt($("#apps_count").text()) - 1);
						var oldCount = $('#app_supports .fs_social_network_div[data-setting="<?=esc_html($tab)?>"] .fs_snd_badge').text().trim();
						$('#app_supports .fs_social_network_div[data-setting="<?=esc_html($tab)?>"] .fs_snd_badge').text(parseInt(oldCount) - 1);
					});
				});
			}, true);
		});
	});

</script>

