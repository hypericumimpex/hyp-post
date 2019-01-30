<?php defined('MODAL') or exit();?>

<style>
	.ws_method_box
	{
		position: relative;
		width: 85px;
		height: 115px;
		float: left;
		margin: 25px 40px;
		cursor: pointer;
	}

	.ws_method_box.ws_method_selectted_box>.ws_method_box_img,
	.ws_method_box:hover>.ws_method_box_img
	{
		border: 1px solid #ff7675 !important;
		color: #ff7675;
		-webkit-box-shadow: 0 0 3px 0 #fab1a0;
		-moz-box-shadow: 0 0 3px 0 #fab1a0;
		box-shadow: 0 0 3px 0 #fab1a0;
	}
	.ws_method_box.ws_method_selectted_box>.ws_method_box_label,
	.ws_method_box:hover>.ws_method_box_label
	{
		color: #ff7675 !important;
		text-shadow: 0px 0px 1px #fab1a0;
	}
	.ws_method_box.ws_method_selectted_box:before
	{
		position: absolute;
		right: 4px;
		top: 5px;
		width: 16px;
		height: 16px;
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
		background: #74b9ff;
		text-align: center;
		line-height: 16px;
		content: "✓";
		color: #FFF;
		border: 3px solid #FFF;
		font-size: 10px !important;
		font-weight: 600;
	}

	.ws_method_box>.ws_method_box_img
	{
		border: 1px solid #DDD;
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
		width: 80px;
		height: 80px;
		text-align: center;
		line-height: 80px;
		color: #DDD;
		font-size: 30px;
	}

	.ws_method_box>.ws_method_box_label
	{
		text-align: center;
		padding-top: 8px;
		color: #999;
		font-size: 14px;
		font-weight: 600;
	}

	.ws_methods
	{
		width: 400px;
		margin-top: 15px !important;
		position:absolute; left: 0;
		right: 0;
		margin: auto;
		display: flex;
		justify-content: center;
	}

	.ws_steps > div
	{
		position: relative;
		padding: 10px 90px 10px 10px;
	}
	.ws_steps > div:before
	{
		content: '';
		position: absolute;
		left: -25px;
		top: 0;
		height: 100%;
		border-left: 4px solid #afc7d0;
	}
	.ws_steps > div:after
	{
		content: attr(data-step);
		position: absolute;
		left: -35px;
		height: 24px;
		width: 24px;
		-webkit-border-radius: 15px;
		-moz-border-radius: 15px;
		border-radius: 15px;
		background: #74b9ff;
		color: #FFF;
		font-weight: 700;
		font-size: 14px;
		top: 0;
		bottom: 0;
		margin: auto;
		display: flex;
		align-items: center;
		justify-items: center;
		justify-content: center;
		align-content: center;
	}

	.ws_steps > div:first-child:before
	{
		top: 50% !important;
		height: 50% !important;
	}

	.ws_steps > div:last-child:before
	{
		height: 50% !important;
	}
</style>

<span class="close" data-modal-close="true">&times;</span>

<div class="ws_step1">
	<div style="padding-top: 60px;text-align: center;font-size: 17px;color: #888;font-weight: 600;">
		<?=esc_html__('Select authorization method', 'fs-poster')?>
	</div>

	<div class="ws_methods">
		<div class="ws_method_box ws_method_selectted_box" data-type="1">
			<div class="ws_method_box_img"><i class="fa fa-rocket"></i></div>
			<div class="ws_method_box_label ws_tooltip" data-title="Recomended" data-float="left"><?=esc_html__('Cookie method', 'fs-poster')?></div>
		</div>
		<div class="ws_method_box" data-type="2">
			<div class="ws_method_box_img"><i class="fa fa-key"></i></div>
			<div class="ws_method_box_label"><?=esc_html__('Login & Pass', 'fs-poster')?></div>
		</div>
		<div style="clear: both;"></div>
	</div>

	<div style="text-align: center; margin-top: 195px;">
		<button class="ws_btn ws_bg_danger next_step_btn" type="button" style="width: 100px;"><?=esc_html__('NEXT STEP', 'fs-poster')?></button>
	</div>

</div>

<script>

	jQuery(document).ready(function()
	{
		$(".ws_methods>.ws_method_box").click(function()
		{
			$(".ws_method_box.ws_method_selectted_box").removeClass('ws_method_selectted_box');
			$(this).addClass('ws_method_selectted_box');
		});


		$("#proModal<?=$mn?> .ws_step1 .next_step_btn").click(function()
		{
			var type = $(".ws_method_selectted_box").attr('data-type');

			if( type == '1' )
			{
				fsCode.loadModal('add_google_account_cookies_method' , {});
			}
			else
			{
				fsCode.loadModal('add_google_account' , {});
			}

			fsCode.modalHide( $("#proModal<?=$mn?>") );
		});

	});

</script>