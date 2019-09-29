<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_enqueue_media();

$postId = (int)FS_get('post_id' , '0' , 'num');

FSwpDB()->query("DELETE FROM " . FSwpDB()->base_prefix . "posts WHERE post_type='fs_post_tmp' AND id<>'{$postId}' AND CAST(post_date AS DATE)<CAST(NOW() AS DATE)");

$postInf = get_post($postId , ARRAY_A);
if( !in_array( $postInf['post_type'] , ['fs_post', 'fs_post_tmp'] ) || $postInf['post_author'] != get_current_user_id() )
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
		border-color: #EEE !important;
	}

	.sb_tab
	{
		color: #FFF !important;
		box-shadow: none !important;
	}

	#share_box1
	{
		max-height: 300px !important;
		border: 0 !important;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		box-shadow: none !important;
		min-height: 45px;
	}


	.accounts_list_panel
	{
		background: #fdcb6e;
		width: 45%;
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

<div style="width: 100%; margin-top: 50px; overflow: auto;">
	<div style="margin: auto; background: #FFF; display: flex; width: 95%; max-width: 900px; min-width: 650px; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; border: 1px solid #DDD;">
		<div style="width: 55%; padding: 30px;">

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
			<div>Characters count: <span id="char_count">0</span></div>

			<div style="margin-top: 10px; display: flex; justify-content: space-between">
				<button type="button" class="ws_btn ws_bg_success shareNowBtn" typeof="button" style="width: 32.5%;">Share now</button>
				<button type="button" class="ws_btn ws_bg_danger scheduleBtn" typeof="button" style="width: 32.5%;">Schedule</button>
				<button type="button" class="ws_btn ws_bg_default saveBtn" typeof="button" style="width: 32.5%;">Save</button>
			</div>

		</div>

		<div class="accounts_list_panel">
			<div style="position: relative;">
				<?php
				require_once FS_VIEWS_DIR . 'post_meta_box.php';
				?>
			</div>
		</div>

	</div>
</div>

<div style="display: flex; justify-content: center; margin-top: 30px;">
	<div style="width: 95%; max-width: 900px;">

			<div style="margin-bottom: 20px; text-align: center; font-size: 20px; font-weight: 600; color: #AAA;">Saved posts</div>

			<div>
				<table class="ws_table">
					<thead>
						<tr>
							<th style="width: 50px;">ID</th>
							<th>Content</th>
							<th style="width: 200px;">Date</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$currentUserId = (int)get_current_user_id();

						$posts = FSwpDB()->get_results('SELECT * FROM ' . FSwpDB()->base_prefix . "posts WHERE post_type='fs_post' AND post_author='" . $currentUserId ."' ORDER BY ID DESC", ARRAY_A);

						foreach( $posts AS $post )
						{
							print '<tr data-id="' . (int)$post['ID'] . '">';
							print '<td>' . (int)$post['ID'] . '</td>';
							print '<td><a href="?page=fs-poster-share&post_id=' . (int)$post['ID'] . '" style="text-decoration: none;">[ ' . htmlspecialchars(FScutText($post['post_content'])) . ' ]</a></td>';
							print '<td>
								' . date('Y-m-d H:i', strtotime($post['post_date'])) . '
								<button class="delete_post_btn delete_btn_desing ws_tooltip" data-title="Delete saved post" data-float="left"><i class="fa fa-trash"></i></button>
							</td>';
							print '</tr>';
						}
						?>
					</tbody>
				</table>
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

		var saveId = <?=$postId?>;

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
			savePost(false, function()
			{
				fsCode.toast('Saved successfully!');
			});
		});

		$(".shareNowBtn").click(function()
		{
			savePost(true, function()
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
			savePost(false, function()
			{
				fsCode.loadModal('plan_saved_post' , {'post_id': saveId});
			});
		});

		$(".delete_post_btn").click(function()
		{
			var tr = $(this).closest('tr'),
				postId = tr.data('id');

			fsCode.confirm('Are you sure you want to delete?', 'danger', function()
			{
				fsCode.ajax('manual_share_delete', {'id': postId}, function()
				{
					tr.fadeOut(500, function()
					{
						if( postId == saveId )
						{
							location.href = '?page=fs-poster-share';
						}
						$(this).remove();
					});
				});
			});
		});

		function savePost( tmp, callback )
		{
			var link		=	$(".link_url").val(),
				message		=	$(".message_box").val(),
				image		=	$("#imageShow").data('id');

			fsCode.ajax('manual_share_save' , {'id': saveId , 'link': link, 'message': message, 'image': image, 'tmp': tmp?1:0}, function (result)
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

		$(".message_box").on('keyup', function()
		{
			var length = $(this).val().length;

			$("#char_count").text( length );
		}).trigger('keyup');
	});
</script>