<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_media();

$postId = _get('post_id' , '0' , 'num');

$postInf = get_post($postId , ARRAY_A);
if( $postInf['post_type'] != 'fs_post' || $postInf['post_author'] != get_current_user_id() )
{
	$postId = 0;
	$link = '';
	$imageURL = '';
	$imageId = '';
	$message = '';
}
else
{
	$link = get_post_meta( $postId, '_fs_link', true );
	$imageId = get_post_thumbnail_id($postId);
	$imageURL = $imageId > 0 ? wp_get_attachment_url( $imageId ) : '';
	$message = $postInf['post_content'];
}
?>
<style>
	#imageShow
	{
		position: relative;
	}
	#imageShow>img
	{
		width: 100%;
	}
	#wpMediaBtn
	{
		margin-bottom: 5px;
	}

	#closeImg
	{
		position: absolute;
		color: #FFF;
		top: 8px;
		right: 10px;
		font-size: 20px;
		background: rgba(0,0,0,0.1);
		padding: 4px 7px;
		border-radius: 15px;
		cursor: pointer;
	}

	#custom_messages , .share_switch
	{
		display: none !important;
	}

	.sb_tab:not(.active_tab)
	{
		border-color: #FFF !important;
	}

	.sb_tab
	{
		color: #FFF !important;
		box-shadow: none !important;
	}

	.active_tab
	{
		border-bottom: 0 !important;
		background: #FFF !important;
		color: #ff7675 !important;
	}

	.add_to_list_btn
	{
		color: #FFF !important;
	}

	#share_box1
	{
		max-height: 300px !important;
		border: 0 !important;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		box-shadow: none !important;
	}


	.accounts_list_panel
	{
		background: #fdcb6e;
		width: 400px;
		border: 5px solid #FFF;
		border-top-right-radius: 10px;
		border-bottom-right-radius: 10px;
		border-top-left-radius: 10px;
		border-bottom-left-radius: 10px;
		padding: 10px;
		position: relative;
	}

	.accounts_list_panel::before
	{
		content: '';
		position: absolute;
		opacity: 0.3;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		background-image: url('<?=plugin_dir_url(__FILE__).'../../images/schedule_bg.png'?>');
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
		border-radius: 10px;
	}
</style>
<div style="display: flex; justify-content: center; margin-top: 50px;">
	<div style="background: #FFF; display: flex; width: 900px; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; border: 1px solid #DDD;">
		<div style="width: 500px; padding: 30px;">

			<div style="margin-bottom: 20px; text-align: center; font-size: 20px; font-weight: 600; color: #AAA;">Share posts</div>

			<div>
				<div><button type="button" class="ws_btn ws_bg_danger ws_btn_small" style="<?=$imageId==0?'':'display: none;'?>" id="wpMediaBtn"><i class="far fa-image"></i> Select image</button></div>
				<div style="<?=$imageId>0?'':'display: none;'?>" id="imageShow" data-id="<?=$imageId?>">
					<img src="<?=esc_html($imageURL)?>">
					<i class="fa fa-close fa-times" id="closeImg"></i>
				</div>
			</div>

			<div style="margin-top: 5px;">
				<input type="text" class="ws_form_element2 link_url" value="<?=esc_html($link)?>" style="<?=$imageId==0?'':'display: none;'?>" placeholder="Link ( example: http://google.com )">
			</div>

			<div style="margin-top: 10px;">
				<textarea class="ws_form_element2 message_box" style="height: 150px !important;" placeholder="Post Message" maxlength="2000"><?=esc_html($message)?></textarea>
			</div>

			<div style="margin-top: 10px; display: flex; justify-content: space-between">
				<button type="button" class="ws_btn ws_bg_success shareNowBtn" typeof="button" style="width: 32.5%;">Share now</button>
				<button type="button" class="ws_btn ws_bg_danger scheduleBtn" typeof="button" style="width: 32.5%;">Schedule</button>
				<button type="button" class="ws_btn ws_bg_default saveBtn" typeof="button" style="width: 32.5%;">Save</button>
			</div>
			
		</div>

		<div class="accounts_list_panel">
			<div style="position: relative;">
				<?php
				require_once VIEWS_DIR . 'post_meta_box.php';
				?>
			</div>
		</div>

	</div>
</div>

<script>
	jQuery(function($)
	{
		var frame = wp.media(
		{
			title: 'Select or Upload Media Of Your Chosen Persuasion',
			button: {
				text: 'Use this media'
			},
			multiple: false  // Set to true to allow multiple files to be selected
		});

		var saveId = 0;

		frame.on( 'select', function()
		{
			var attachment = frame.state().get('selection').first().toJSON();console.log(attachment)
			$("#imageID").val(attachment.id);
			$("#imageShow")
				.slideDown(500)
				.data('id' , attachment.id)
				.children('img').attr('src' , attachment.url);
			$("#wpMediaBtn , .link_url").slideUp(500);
		});

		$("#wpMediaBtn").click( function( event )
		{
			frame.open();
		});

		$("#closeImg").click(function()
		{
			$("#imageShow")
				.slideUp(500, function(){ $(this).children('img').attr('src' , ''); })
				.data('id' , 0);
			$("#wpMediaBtn , .link_url").slideDown(500);
		});


		$(".saveBtn").click(function()
		{
			savePost(function()
			{
				fsCode.toast('Saved successfully!');
			});
		});

		$(".shareNowBtn").click(function()
		{
			savePost(function()
			{
				var nodes = [];
				$(".accounts_list_panel input[name='share_on_nodes[]']").each(function()
				{
					nodes.push($(this).val());
				});

				if( nodes.length == 0 )
				{
					fsCode.toast("<?=esc_html__('No selected account!' , 'fs-poster')?>" , 'danger');
					return;
				}

				fsCode.ajax('share_saved_post' , {
					'post_id': saveId ,
					'nodes': nodes ,
					'background': 0
				}, function()
				{
					fsCode.loadModal('share_feeds' , {'post_id': saveId});
				});
			});
		});

		$(".scheduleBtn").click(function()
		{
			savePost(function()
			{
				fsCode.loadModal('plan_saved_post' , {'post_id': saveId});
			});
		});

		function savePost( callback )
		{
			var link		=	$(".link_url").val(),
				message		=	$(".message_box").val(),
				image		=	$("#imageShow").data('id');

			fsCode.ajax('manual_share_save' , {'id': saveId , 'link': link, 'message': message, 'image': image}, function (result)
			{
				saveId = result['id'];

				var url = window.location.href;
				if( url.indexOf('post_id=') > -1 )
				{
					url = url.replace(/post_id\=([0-9]+)/ , 'post_id=' + saveId ,  url);
				}
				else
				{
					url += (url.indexOf('?') > -1 ? '&' : '?') + 'post_id=' + saveId;
				}

				window.history.pushState("", "", url);

				if( typeof callback == 'function')
				{
					callback( );
				}
			});
		}
	});
</script>