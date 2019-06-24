<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$accountFilter = isset($_GET['account']) && is_numeric($_GET['account']) && $_GET['account'] > 0 ? (int)$_GET['account'] : 0;

$queryAdd = '';
if( $accountFilter > 0 )
{
	$queryAdd = " AND account_id='$accountFilter'";
}

$drivers = [
	'fb'        =>  ['ownpage' , 'group' , 'page'],
	'vk'        =>  ['page' , 'group' , 'event' ] ,
	'linkedin'  =>  ['company'] ,
	'tumblr'    =>  ['blog'] ,
	'google'    =>  ['community']
];

$tab = isset($_GET['tab']) && is_string($_GET['tab']) && key_exists($_GET['tab'] , $drivers) ? (string)$_GET['tab'] : 'fb';

$nodeList = wpDB()->get_results(wpDB()->prepare("
	SELECT 
		*,
		(SELECT filter_type FROM ".wpTable('account_node_status')." WHERE node_id=tb1.id AND user_id=%d) is_active
	FROM ".wpTable('account_nodes')." tb1
	WHERE (user_id=%d OR is_public=1) AND driver=%s{$queryAdd}",  [get_current_user_id() , get_current_user_id() , $tab]) , ARRAY_A);

?>
<style>
	.nodes_container
	{
		display: flex;
		justify-content: space-around;
		margin: 35px 0;
	}
	.nodes_container>div
	{
		display: flex;
		flex-direction: column;
		width: 365px;
		height: 500px;
		background: #FBFBFD;
		-webkit-box-shadow: 0 0 10px 0 #DDD;
		-moz-box-shadow: 0 0 10px 0 #DDD;
		box-shadow: 0 0 10px 0 #DDD;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		overflow: hidden;

	}
	.nodes_list
	{
		padding: 8px 0;
		overflow: auto;
		height: 100%;
	}
	.node_toolbar
	{
		padding: 20px;
		padding-bottom: 10px;
		padding-top: 10px;
	}
	.node_toolbar
	{
		display: flex;
		align-items: stretch;
	}
	.search_input
	{
		position: relative;
		flex-shrink: 1;
		width: 100%;
	}
	.search_input>i
	{
		position: absolute;
		top: 3px;
		bottom: 0;
		margin: auto;
		height: 15px;
		right: 15px;
		color: #CCC;
	}
	.search_input>input
	{
		width: 100%;
		height: 35px;
		padding-left: 20px;
		color: #999;
		font-weight: 600;

		border: 0 !important;
		outline: none !important;

		-webkit-box-shadow: 0 0 3px 0 #DDD !important;
		-moz-box-shadow: 0 0 3px 0 #DDD !important;
		box-shadow: 0 0 3px 0 #DDD !important;

		-webkit-border-radius: 50px;
		-moz-border-radius: 50px;
		border-radius: 50px;
	}

	.search_input>input::placeholder
	{
		color: #CCC;
	}

	.node_div
	{
		position: relative;
		margin: 0 20px 10px;
		padding: 13px;
		border-bottom: 1px solid #EEE;
		background: #FFF;
		-webkit-box-shadow: 0 0 10px 0 #DDD;
		-moz-box-shadow: 0 0 10px 0 #DDD;
		box-shadow: 0 0 10px 0 #DDD;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
	}
	.node_div>div
	{
		float: left;
	}
	.node_div:after
	{
		content: " ";
		clear: both;
		display: block;
	}
	.node_img>img
	{
		-webkit-border-radius: 50% !important;
		-moz-border-radius: 50% !important;
		border-radius: 50% !important;
		width: 40px;
		height: 40px;
	}
	.node_label
	{
		padding-left: 10px;
	}
	.node_label_title
	{
		white-space: nowrap;
		overflow: hidden;
		max-width: 155px;

		display: inline-block;
	}
	.node_label_title>a
	{
		color: #888 !important;
		font-size: 14px;
		font-weight: 600;
		text-decoration: none;
		-webkit-box-shadow: none;
		-moz-box-shadow: none;
		box-shadow: none;
	}
	.node_category
	{
		font-weight: 500;
		color: #95a5a6;
		padding-top: 5px;
		white-space: nowrap;
		overflow: hidden;
		max-width: 180px;
	}
	.node_chckbx>i
	{
		background: #DDD;
		padding: 5px;
		border-radius: 50%;
		color: #FFF;
		margin-top: 12px;
		cursor: pointer;
	}
	.node_checked>i
	{
		background: #86d4ea;
	}
	.node_checked2>i
	{
		background: #fdcb6e;
	}
	.node_box_label
	{
		text-align: center;
		padding-top: 10px;
		position: relative;
		height: 37px;
	}
	.node_box_label>div
	{
		position: absolute;
		width: 120px;
		height: 22px;
		color: #FFF;
		margin: auto;
		left: 0;
		right: 0;
		background: #94A0B2;
		-webkit-border-radius: 15px;
		-moz-border-radius: 15px;
		border-radius: 15px;
		font-weight: 600;
		font-size: 14px;
		line-height: 20px;
		border: 5px solid #FBFBFD;
	}
	.node_box_label:before
	{
		content: '';
		width: calc(100% - 60px);
		height: 1px;
		border-top: 1px solid #94A0B2;
		top: 16px;
		bottom: 0px;
		left: 0px;
		margin: 10px 30px;
		position: absolute;
	}

	.node_delete
	{
		position: absolute;
		color: #ff7675;
		right: 40px;
		bottom: 0px;
		top: 5px;
		margin: auto;
		background: #FFF;
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
		width: 20px;
		height: 20px;
		padding: 2px;
		cursor: pointer;
		text-align: center;
		display: none;
	}
	.node_div:hover .node_delete
	{
		display: block;
	}


	#sub_menu2
	{
		position: fixed;
		background: #FFF;
		border-top: 2px solid #c8e7ff;
		display: none;
		z-index: 999;
		width: 190px;
		margin-top: 10px;
	}

	#sub_menu2:before
	{
		content: '';
		border: 10px solid transparent;
		border-bottom-color: #c8e7ff;
		position: absolute;
		top: -20px;
		left: calc(50% - 9px);
	}

	#sub_menu2 > div
	{
		padding: 8px 20px;
		font-size: 14px;
		font-weight: 500;
		color: #777;
	}

	#sub_menu2 > div:hover
	{
		background: #f5f5f5;
		cursor: pointer;
	}

	.social_network_div
	{
		width: 140px;
		height: 35px;
		background: #FFF;
		border: 1px solid #DDD;
		margin-left: 3px;
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

		user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		-webkit-user-select: none;
	}
	.social_network_div:hover
	{
		background: #f9f9f9;
	}
	.social_network_div i
	{
		margin-right: 5px;
		color: #74b9ff;
	}
	.snd_badge
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

	.snd_active
	{
		border-top: 3px solid #fd79a8 !important;
		background: #f9f9f9 !important;
		margin-top: -2px;
	}
	.snd_active .snd_badge
	{
		margin-right: 12px;
	}
	#account_supports
	{
		width: 100%;
		margin-top: 25px;
		margin-bottom: 25px;
		margin-left: 40px;
		display: flex;
	}

	.node_public_icon
	{
		color: #888;
		padding-left: 7px;
		cursor: pointer;

		display: none;
		vertical-align: top;
	}

	.node_div[data-public='0']:hover .node_public_icon
	{
		display: inline-block !important;
	}

	.node_div[data-public='1'] .node_public_icon
	{
		display: inline-block !important;
		color: #74b9ff;
	}
</style>

<div id="sub_menu2">
	<div class="activate_btn">Activate</div>
	<div class="activate_with_condition_btn">Activate with condition</div>
	<div class="deactivate_btn">Deactivate</div>
</div>

<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<div>
	<div id="account_supports">
		<a href="?page=fs-poster-nodes&tab=fb" class="social_network_div<?=$tab=='fb'?' snd_active':''?>">
			<div><i class="fab fa-facebook-square"></i> Facebook</div>
		</a>
		<a href="?page=fs-poster-nodes&tab=linkedin" class="social_network_div<?=$tab=='linkedin'?' snd_active':''?>">
			<div><i class="fab fa-linkedin"></i> Linkedin</div>
		</a>
		<a href="?page=fs-poster-nodes&tab=vk" class="social_network_div<?=$tab=='vk'?' snd_active':''?>">
			<div><i class="fab fa-vk"></i> VK</div>
		</a>
		<a href="?page=fs-poster-nodes&tab=tumblr" class="social_network_div<?=$tab=='tumblr'?' snd_active':''?>">
			<div><i class="fab fa-tumblr"></i> Tumblr</div>
		</a>
		<!--<a href="?page=fs-poster-nodes&tab=google" class="social_network_div<?/*=$tab=='google'?' snd_active':''*/?>">
			<div><i class="fab fa-google"></i> Google+</div>
		</a>-->
	</div>
</div>

<div style="display: flex; margin-left: 50px; margin-top: 15px; align-items: center;">
	<div style="width: auto; padding-right: 15px; font-size: 15px; font-weight: 600; color: #999;"><?=esc_html__('Account filter:' , 'fs-poster');?></div>
	<div style="width: 220px;">
		<select class="ws_form_element" id="accountFilterSelect">
			<option><?=esc_html__('All' , 'fs-poster');?></option>
			<?php
			$accounts = wpDB()->get_results( wpDB()->prepare("SELECT * FROM " . wpTable('accounts') . " WHERE user_id=%d AND driver=%s" , [get_current_user_id() , $tab]) , ARRAY_A );

			foreach( $accounts AS $account )
			{
				print '<option value="'.(int)$account['id'].'"'.($accountFilter == (int)$account['id'] ? ' selected' : '').'>'.esc_html($account['name']).'</option>';
			}
			?>
		</select>
	</div>
</div>

<div class="nodes_container">
	<?php
	foreach( $drivers[$tab] AS $type )
	{
		?>
		<div>
			<div class="node_box_label">
				<div><?=strtoupper($type)?></div>
			</div>
			<div class="node_toolbar">
				<div class="search_input">
					<input type="text" placeholder="<?=esc_html__('Search...' , 'fs-poster');?>">
					<i class="fa fa-search"></i>
				</div>
			</div>
			<div class="nodes_list">
				<?php
				$count = 0;
				foreach ($nodeList AS $node)
				{
					if( $node['node_type'] != $type ) continue;
					$count++;
					?>
					<div class="node_div" data-id="<?=(int)$node['id']?>" data-public="<?=(int)$node['is_public']?>">
						<div class="node_img"><img src="<?=profilePic($node)?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../../images/no-photo.png'?>');"></div>
						<div class="node_label">
							<div>
								<div class="node_label_title">
									<a href="<?=profileLink($node)?>" target="_blank" title="<?=esc_html__('Profile link' , 'fs-poster');?>"><?=esc_html($node['name']);?></a>
								</div>
								<span class="node_public_icon ws_tooltip" data-title="Make public for use this community by other WordPress users"><i class="fa fa-globe"></i></span>
							</div>
							<div class="node_category">
								<i class="far fa-paper-plane"></i> <?=ucfirst(esc_html($node['driver'] == 'vk'?($node['node_type']).($node['category'] == 'admin' ? ' (admin)' : ''):$node['category']));?>
							</div>
						</div>
						<div class="node_chckbx ws_tooltip<?=$node['is_active'] == '' ? '' : ' node_checked' . ($node['is_active']=='no'?'':'2') ?>" data-title="<?=esc_html__('Click to change status' , 'fs-poster');?>" data-float="left" style="float: right;">
							<i class="fa fa-check"></i>
						</div>
						<div class="node_delete ws_tooltip" data-title="<?=esc_html__('Delete' , 'fs-poster');?>" data-float="left">
							<i class="fa fa-trash"></i>
						</div>
					</div>
					<?php
				}
				if(!$count)
				{
					print '<div style="text-align: center;margin: 20px;font-size: 20px;color: #D2D2D2;font-weight: 700;">Empty!</div>';
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>

<script>
	jQuery(document).ready(function()
	{
		$("#accountFilterSelect").change(function()
		{
			var separator = location.href.indexOf('?') > -1 ? '&' : '?',
				val = $(this).val(),
				hrefOld = location.href;

			if( hrefOld.indexOf('account=') > -1 )
			{
				hrefOld = hrefOld.replace(/(\&?account\=[^&]*)/ , '');
			}
			fsCode.loading(1);
			location.href = hrefOld + separator + 'account=' + val;
		});

		$(".node_chckbx").click(function()
		{
			var checked	= $(this).hasClass("node_checked"),
				dataId	= $(this).closest('.node_div').attr('data-id');

			$("#sub_menu2")
				.show()
				.css('top' , $(this).offset().top + 35 - $(window).scrollTop())
				.css('left' , $(this).offset().left - ($("#sub_menu2").width()/2) + 10)
				.data('id' , dataId);
		});

		$(document).click(function(e)
		{
			if( !$(e.target).is('.node_chckbx , .node_chckbx > i') )
			{
				$("#sub_menu2").hide();
			}
		});

		$("#sub_menu2 > .activate_with_condition_btn").click(function()
		{
			var dataId = $('#sub_menu2').data('id');

			fsCode.loadModal('activate_with_condition' , {'id': dataId, 'type': 'node'});
		});

		$("#sub_menu2 > .activate_btn").click(function()
		{
			var dataId = $('#sub_menu2').data('id');

			fsCode.ajax('settings_node_activity_change' , {'id': dataId, 'checked': 1});
			$(".node_div[data-id=\"" + dataId + "\"] .node_chckbx")
				.addClass('node_checked')
				.removeClass('node_checked2');
		});

		$("#sub_menu2 > .deactivate_btn").click(function()
		{
			var dataId = $('#sub_menu2').data('id');

			fsCode.ajax('settings_node_activity_change' , {'id': dataId, 'checked': 0} );
			$(".node_div[data-id=\"" + dataId + "\"] .node_chckbx")
				.removeClass('node_checked')
				.removeClass('node_checked2');
		});

		$(".node_delete").click(function()
		{
			var nodeDiv = $(this).closest('.node_div'),
				dataId = nodeDiv.attr('data-id');
			fsCode.confirm("<?=esc_html__('Are you sure you want to delete your account?' , 'fs-poster');?>" , 'danger' , function(modal)
			{
				fsCode.ajax('settings_node_delete' , {'id': dataId} , function()
				{
					fsCode.toast('\'Community\' has been deleted!');
					nodeDiv.hide(500, function()
					{
						$(this).remove();
					});
					fsCode.modalHide(modal);
				});
			}, false);
		});

		$(".search_input>input").keyup(function()
		{
			var val = $(this).val();

			$(this).closest('.node_toolbar').next('.nodes_list').children('.node_div:not(:contains("' + fsCode.htmlspecialchars(val) + '"))').hide(500);
			$(this).closest('.node_toolbar').next('.nodes_list').children('.node_div:contains("' + fsCode.htmlspecialchars(val) + '")').show(500);
		});

		$(document).on('click' , ".node_public_icon" , function ()
		{
			var nodeId		=	$(this).closest('.node_div').attr('data-id'),
				t			=	$(this);

			fsCode.ajax('settings_node_make_public' , {'id': nodeId}, function()
			{
				t.closest('.node_div').attr('data-public' , (t.closest('.node_div').attr('data-public') == '1' ? '0' : '1'));
			});
		});

		jQuery.expr[':'].contains = function(a, i, m) {
			return jQuery(a).text().toUpperCase()
				.indexOf(m[3].toUpperCase()) >= 0;
		};
	});
</script>