<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$accountsList = FSwpDB()->get_results(FSwpDB()->prepare("SELECT driver, COUNT(0) AS _count FROM ".FSwpTable('accounts')." WHERE (user_id=%d OR is_public=1) GROUP BY driver",  [get_current_user_id()]) , ARRAY_A);
$accountsCount = [
	'fb'		=>  0,
	'twitter'	=>  0,
	'instagram'	=>  0,
	'linkedin'	=>  0,
	'vk'		=>  0,
	'pinterest'	=>  0,
	'reddit'	=>  0,
	'tumblr'	=>  0,
	'google_b'	=>  0,
	'ok'		=>  0,
	'telegram'	=>	0,
	'medium'	=>	0
];
foreach( $accountsList AS $aInf )
{
	if( isset($accountsCount[$aInf['driver']]) )
	{
		$accountsCount[$aInf['driver']] = $aInf['_count'];
	}
}

$activeTab = FS_get('tab', 'fb', 'string');

?>

<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<div id="fs_sub_menu">
	<div class="activate_btn">Activate</div>
	<div class="activate_with_condition_btn">Activate with condition</div>
	<div class="deactivate_btn">Deactivate</div>
</div>

<div style="display: flex;">
	<div id="fs_account_supports">
		<div class="fs_social_network_div" data-setting="fb">
			<div><i class="fab fa-facebook-square"></i> Facebook</div>
			<div class="fs_snd_badge"><?=$accountsCount['fb']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="twitter">
			<div><i class="fab fa-twitter-square"></i> Twitter</div>
			<div class="fs_snd_badge"><?=$accountsCount['twitter']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="instagram">
			<div><i class="fab fa-instagram"></i> Instagram</div>
			<div class="fs_snd_badge"><?=$accountsCount['instagram']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="linkedin">
			<div><i class="fab fa-linkedin"></i> Linkedin</div>
			<div class="fs_snd_badge"><?=$accountsCount['linkedin']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="vk">
			<div><i class="fab fa-vk"></i> VK</div>
			<div class="fs_snd_badge"><?=$accountsCount['vk']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="pinterest">
			<div><i class="fab fa-pinterest"></i> Pinterest</div>
			<div class="fs_snd_badge"><?=$accountsCount['pinterest']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="reddit">
			<div><i class="fab fa-reddit"></i> Reddit</div>
			<div class="fs_snd_badge"><?=$accountsCount['reddit']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="tumblr">
			<div><i class="fab fa-tumblr-square"></i> Tumblr</div>
			<div class="fs_snd_badge"><?=$accountsCount['tumblr']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="ok">
			<div><i class="fab fa-odnoklassniki"></i> OK.ru</div>
			<div class="fs_snd_badge"><?=$accountsCount['ok']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="google_b">
			<div><i class="fab fa-google"></i> Google MyBusiness</div>
			<div class="fs_snd_badge"><?=$accountsCount['google_b']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="telegram">
			<div><i class="fab fa-telegram"></i> Telegram</div>
			<div class="fs_snd_badge"><?=$accountsCount['telegram']?></div>
		</div>
		<div class="fs_social_network_div" data-setting="medium">
			<div><i class="fab fa-medium"></i> Medium</div>
			<div class="fs_snd_badge"><?=$accountsCount['medium']?></div>
		</div>
	</div>
	<div style="width: 100%;" id="account_content">

	</div>
</div>

<script>
	jQuery(document).ready(function()
	{

		var currentSettings;
		$("#fs_account_supports>[data-setting]").click(function()
		{
			currentSettings = $(this).attr('data-setting');
			$("#fs_account_supports .fs_snd_active").removeClass('fs_snd_active');
			$(this).addClass('fs_snd_active');

			fsCode.ajax('get_accounts' , {'name': currentSettings}, function(result)
			{
				$("#account_content").html(fsCode.htmlspecialchars_decode(result['html']));

				$('#fs_account_supports .fs_social_network_div[data-setting="'+currentSettings+'"] .fs_snd_badge').text( $("#accounts_count").text() );
			});

			window.history.pushState({},"", '?page=fs-poster&tab=' + currentSettings);
		});

		$('#fs_account_supports>[data-setting="<?=htmlspecialchars($activeTab)?>"]').click();

		$("#account_content").on('click' , '.delete_account_btn' , function()
		{
			var tr      = $(this).closest('tr'),
				aId     = tr.data('id'),
				type    = tr.data('type');

			fsCode.confirm("<?=esc_html__('Are you sure you want to delete?' , 'fs-poster')?>" , 'danger' , function ()
			{
				var ajaxAction = type == 'community' ? 'settings_node_delete' : 'delete_account';

				fsCode.ajax(ajaxAction , {'id': aId} , function(result)
				{
					if( type == 'community' )
					{
						tr.fadeOut(300, function()
						{
							$(this).remove();
						});
					}
					else
					{
						tr.closest('table').fadeOut(300, function()
						{
							$(this).remove();

							if( $(".ws_table>tbody>tr").length == 0 )
							{
								$("#account_content .ws_table_wraper").append('<table class="ws_table"><tbody><tr><td colspan="100%" style="color: #999;">No accound found</td></tr></tbody></table>').children('tr').hide().fadeIn(200);
							}
							$("#accounts_count").text(parseInt($("#accounts_count").text()) - 1);
							var oldCount = $('#fs_account_supports .fs_social_network_div[data-setting="'+currentSettings+'"] .fs_snd_badge').text().trim();
							$('#fs_account_supports .fs_social_network_div[data-setting="'+currentSettings+'"] .fs_snd_badge').text(parseInt(oldCount) - 1);
						});

						tr.closest('table').next('.fs_communities_area').fadeOut(200, function()
						{
							$(this).remove();
						});
					}

				});
			}, true);
		}).on('click' , '.fs_account_checkbox' , function()
		{
			var checked     = $(this).hasClass("fs_account_checked"),
				dataId      = $(this).closest('tr').data('id'),
				dataType    = $(this).closest('tr').data('type');

			$("#fs_sub_menu")
				.show()
				.css('top' , $(this).offset().top + 25 - $(window).scrollTop())
				.css('left' , $(this).offset().left - ($("#fs_sub_menu").width()/2) + 10)
				.data('id' , dataId)
				.data('type' , dataType);
		}).on('click' , '.fs_account_checkbox_public' , function()
		{
			var checked			= $(this).hasClass("fs_account_checked"),
				is_my_account	= $(this).hasClass('my_account'),
				dataId			= $(this).closest('tr').data('id'),
				dataType        = $(this).closest('tr').data('type');

			if( !is_my_account )
			{
				fsCode.alert("<?=esc_html__('This is not one of you added account/community, therefore you do not have a permission for make this profile/community public or private.' , 'fs-poster')?>");
				return;
			}

			if( checked )
			{
				$(this).removeClass('fs_account_checked');
			}
			else
			{
				$(this).addClass('fs_account_checked');
			}

			var ajaxAction = dataType == 'community' ? 'settings_node_make_public' : 'make_account_public';

			fsCode.ajax(ajaxAction , {'id': dataId, 'checked': checked?0:1});
		}).on('click', '.fs_expand_all_btn', function()
		{
			$('.fs_communities_area').slideDown(400, function()
			{
				$(this).prev('table').find('.fs_expand_icon > .fa-angle-right').attr('class', 'fa fa-angle-down');
			});
		}).on('click', '.fs_collapse_all_btn', function()
		{
			$('.fs_communities_area').slideUp(400, function()
			{
				$(this).prev('table').find('.fs_expand_icon > .fa-angle-down').attr('class', 'fa fa-angle-right');
			});
		}).on('click', '.fs_expand_icon', function()
		{
			var arrow = $(this).children('i');

			$(this).closest('table').next('.fs_communities_area').slideToggle(400, function()
			{
				arrow.attr('class', arrow.hasClass('fa-angle-down') ? 'fa fa-angle-right' : 'fa fa-angle-down');
			});
		});

		$(document).click(function(e)
		{
			if( !$(e.target).is('.fs_account_checkbox , .fs_account_checkbox > i') )
			{
				$("#fs_sub_menu").hide();
			}
		});

		$("#fs_sub_menu > .activate_with_condition_btn").click(function()
		{
			var dataId      = $('#fs_sub_menu').data('id'),
				dataType    = $('#fs_sub_menu').data('type') == 'community' ? 'node' : 'account';

			fsCode.loadModal('activate_with_condition' , {'id': dataId, 'type': dataType});
		});

		$("#fs_sub_menu > .activate_btn").click(function()
		{
			var dataId      = $('#fs_sub_menu').data('id'),
				dataType    = $('#fs_sub_menu').data('type') == 'community' ? 'settings_node_activity_change' : 'account_activity_change';

			fsCode.ajax(dataType , {'id': dataId, 'checked': 1});
			$("tr[data-id=\"" + dataId + "\"] .fs_account_checkbox").addClass('fs_account_checked').removeClass('fs_account_checked2');
		});

		$("#fs_sub_menu > .deactivate_btn").click(function()
		{
			var dataId = $('#fs_sub_menu').data('id'),
				dataType    = $('#fs_sub_menu').data('type') == 'community' ? 'settings_node_activity_change' : 'account_activity_change';

			fsCode.ajax(dataType , {'id': dataId, 'checked': 0} );
			$("tr[data-id=\"" + dataId + "\"] .fs_account_checkbox").removeClass('fs_account_checked').removeClass('fs_account_checked2');
		});

	});
</script>