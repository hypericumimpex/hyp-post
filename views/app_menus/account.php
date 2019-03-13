<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$accountsList = wpDB()->get_results(wpDB()->prepare("SELECT driver, COUNT(0) AS _count FROM ".wpTable('accounts')." WHERE (user_id=%d OR is_public=1) GROUP BY driver",  [get_current_user_id()]) , ARRAY_A);
$accountsCount = [
	'fb'        =>  0,
	'twitter'   =>  0,
	'instagram' =>  0,
	'linkedin'  =>  0,
	'vk'        =>  0,
	'pinterest' =>  0,
	'reddit'    =>  0,
	'tumblr'    =>  0,
	'google'    =>  0,
	'ok'	    =>  0
];
foreach( $accountsList AS $aInf )
{
	if( isset($accountsCount[$aInf['driver']]) )
	{
		$accountsCount[$aInf['driver']] = $aInf['_count'];
	}
}

?>
<style>
	.social_network_div
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

		-webkit-box-shadow: 2px 2px 2px 0px #DDD;
		-moz-box-shadow: 2px 2px 2px 0px #DDD;
		box-shadow: 2px 2px 2px 0px #DDD;

		cursor: pointer;
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
		border-left: 3px solid #fd79a8 !important;
		background: #f9f9f9 !important;
	}
	.snd_active .snd_badge
	{
		margin-right: 12px;
	}
	#account_supports
	{
		width: 200px;
		margin-top: 70px;
		margin-left: 20px;
	}

	.account_checkbox, .account_checkbox_public
	{
		margin-left: 15px;
	}
	.account_checkbox>i , .account_checkbox_public>i
	{
		background: #DDD;
		padding: 5px;
		border-radius: 50%;
		color: #FFF;
		cursor: pointer;
		font-size: 13px !important;
	}
	.account_checked>i
	{
		background: #86d4ea;
	}
	.account_checked2>i
	{
		background: #fdcb6e;
	}
	td.ws_tooltip
	{
		cursor: pointer;
	}

	#sub_menu
	{
		position: fixed;
		background: #FFF;
		border-top: 2px solid #ffb8b8;
		display: none;
		z-index: 999;
		width: 190px;
		margin-top: 10px;
	}

	#sub_menu:before
	{
		content: '';
		border: 10px solid transparent;
		border-bottom-color: #ffb8b8;
		position: absolute;
		top: -20px;
		left: calc(50% - 9px);
	}

	#sub_menu > div
	{
		padding: 8px 20px;
		font-size: 14px;
		font-weight: 500;
		color: #777;
	}

	#sub_menu > div:hover
	{
		background: #f5f5f5;
		cursor: pointer;
	}
</style>
<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>

<div id="sub_menu">
	<div class="activate_btn">Activate</div>
	<div class="activate_with_condition_btn">Activate with condition</div>
	<div class="deactivate_btn">Deactivate</div>
</div>

<div style="display: flex;">
	<div id="account_supports">
		<div class="social_network_div snd_active" data-setting="fb">
			<div><i class="fab fa-facebook-square"></i> Facebook</div>
			<div class="snd_badge"><?=$accountsCount['fb']?></div>
		</div>
		<div class="social_network_div" data-setting="twitter">
			<div><i class="fab fa-twitter-square"></i> Twitter</div>
			<div class="snd_badge"><?=$accountsCount['twitter']?></div>
		</div>
		<div class="social_network_div" data-setting="instagram">
			<div><i class="fab fa-instagram"></i> Instagram</div>
			<div class="snd_badge"><?=$accountsCount['instagram']?></div>
		</div>
		<div class="social_network_div" data-setting="linkedin">
			<div><i class="fab fa-linkedin"></i> Linkedin</div>
			<div class="snd_badge"><?=$accountsCount['linkedin']?></div>
		</div>
		<div class="social_network_div" data-setting="vk">
			<div><i class="fab fa-vk"></i> VK</div>
			<div class="snd_badge"><?=$accountsCount['vk']?></div>
		</div>
		<div class="social_network_div" data-setting="pinterest">
			<div><i class="fab fa-pinterest"></i> Pinterest</div>
			<div class="snd_badge"><?=$accountsCount['pinterest']?></div>
		</div>
		<div class="social_network_div" data-setting="reddit">
			<div><i class="fab fa-reddit"></i> Reddit</div>
			<div class="snd_badge"><?=$accountsCount['reddit']?></div>
		</div>
		<div class="social_network_div" data-setting="tumblr">
			<div><i class="fab fa-tumblr-square"></i> Tumblr</div>
			<div class="snd_badge"><?=$accountsCount['tumblr']?></div>
		</div>
		<div class="social_network_div" data-setting="google">
			<div><i class="fab fa-google"></i> Google+</div>
			<div class="snd_badge"><?=$accountsCount['google']?></div>
		</div>
		<div class="social_network_div" data-setting="ok">
			<div><i class="fab fa-odnoklassniki"></i> OK.ru</div>
			<div class="snd_badge"><?=$accountsCount['ok']?></div>
		</div>
	</div>
	<div style="width: 100%;" id="account_content">

	</div>
</div>

<script>
	jQuery(document).ready(function()
	{
		var currentSettings;
		$("#account_supports>[data-setting]").click(function()
		{
			currentSettings = $(this).attr('data-setting');
			$("#account_supports .snd_active").removeClass('snd_active');
			$(this).addClass('snd_active');

			fsCode.ajax('get_accounts' , {'name': currentSettings}, function(result)
			{
				$("#account_content").html(fsCode.htmlspecialchars_decode(result['html']));

				$('#account_supports .social_network_div[data-setting="'+currentSettings+'"] .snd_badge').text( $("#accounts_count").text() );
			});
		}).eq(0).trigger('click');

		$("#account_content").on('click' , '.delete_account_btn' , function()
		{
			var tr = $(this).closest('tr'),
				aId = tr.attr('data-id');

			fsCode.confirm("<?=esc_html__('Are you sure you want to delete?' , 'fs-poster')?>" , 'danger' , function ()
			{
				fsCode.ajax('delete_account' , {'id': aId} , function(result)
				{
					tr.fadeOut(300, function()
					{
						$(this).remove();
						if( $(".ws_table>tbody>tr").length == 0 )
						{
							$(".ws_table>tbody").append('<tr><td colspan="100%" style="color: #999;">No accound found</td></tr>').children('tr').hide().fadeIn(200);
						}
						$("#accounts_count").text(parseInt($("#accounts_count").text()) - 1);
						var oldCount = $('#account_supports .social_network_div[data-setting="'+currentSettings+'"] .snd_badge').text().trim();
						$('#account_supports .social_network_div[data-setting="'+currentSettings+'"] .snd_badge').text(parseInt(oldCount) - 1);
					});
				});
			}, true);
		}).on('click' , '.account_checkbox' , function()
		{
			var checked = $(this).hasClass("account_checked"),
				dataId = $(this).closest('tr').attr('data-id');

			$("#sub_menu")
				.show()
				.css('top' , $(this).offset().top + 25 - $(window).scrollTop())
				.css('left' , $(this).offset().left - ($("#sub_menu").width()/2) + 10)
				.data('id' , dataId);
		}).on('click' , '.account_checkbox_public' , function()
		{
			var checked			= $(this).hasClass("account_checked"),
				is_my_account	= $(this).hasClass('my_account'),
				dataId			= $(this).closest('tr').attr('data-id');

			if( !is_my_account )
			{
				fsCode.alert("<?=esc_html__('This is not one of you added account, therefore you do not have a permission for make this profile public or private.' , 'fs-poster')?>");
				return;
			}

			if( checked )
			{
				$(this).removeClass('account_checked');
			}
			else
			{
				$(this).addClass('account_checked');
			}

			fsCode.ajax('make_account_public' , {'id': dataId, 'checked': checked?0:1});
		});

		$(document).click(function(e)
		{
			if( !$(e.target).is('.account_checkbox , .account_checkbox > i') )
			{
				$("#sub_menu").hide();
			}
		});

		$("#sub_menu > .activate_with_condition_btn").click(function()
		{
			var dataId = $('#sub_menu').data('id');

			fsCode.loadModal('activate_with_condition' , {'id': dataId});
		});

		$("#sub_menu > .activate_btn").click(function()
		{
			var dataId = $('#sub_menu').data('id');

			fsCode.ajax('account_activity_change' , {'id': dataId, 'checked': 1});
			$("tr[data-id=\"" + dataId + "\"] .account_checkbox").addClass('account_checked').removeClass('account_checked2');
		});

		$("#sub_menu > .deactivate_btn").click(function()
		{
			var dataId = $('#sub_menu').data('id');

			fsCode.ajax('account_activity_change' , {'id': dataId, 'checked': 0} );
			$("tr[data-id=\"" + dataId + "\"] .account_checkbox").removeClass('account_checked').removeClass('account_checked2');
		});


	});

</script>