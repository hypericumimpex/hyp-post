<?php defined('MODAL') or exit();?>

<?php
$authURL = FSwpDB()->get_row("SELECT * FROM " . FSwpTable('apps') . " WHERE is_standart=3 AND driver='fb' LIMIT 1" , ARRAY_A);
$authURL = esc_html($authURL['app_authenticate_link']);
$applications = FSwpFetchAll('apps' , ['is_standart' => '0', 'driver' => 'fb','user_id' => get_current_user_id()]);
?>

<style>
	.ws_method_box
	{
		position: relative;
		width: 85px;
		height: 115px;
		float: left;
		margin: 25px;
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
		width: 406px;
		margin-top: 15px !important;
		position:absolute; left: 0;
		right: 0;
		margin: auto;
	}

	.ws_fb_login
	{
		margin-top: 10px;
	}
	.ws_fb_login>div
	{
		position: relative;
		width: 200px;
		padding: 10px;
		padding-left: 0;
	}
	.ws_fb_login input
	{
		width: 100%;
		height: 30px;
		border: 0 !important;
		border-bottom: 1px solid #74B9FF !important;
		padding: 5px;
		outline: 0;
		background: transparent !important;
		-webkit-box-shadow: none !important;
		-moz-box-shadow: none !important;
		box-shadow: none !important;
		color: #95A5A6;
		font-weight: 600;
	}
	.ws_fb_login input::placeholder
	{
		color: #CCC;
		opacity: 1;
		font-weight: 600;
	}

	.ws_fb_login input:-ms-input-placeholder
	{
		color: #CCC;
		opacity: 1;
		font-weight: 600;
	}

	.ws_fb_login input::-ms-input-placeholder
	{
		color: #CCC;
		opacity: 1;
		font-weight: 600;
	}

	.ws_fb_login>div>i
	{
		position: absolute;
		right: 16px;
		color: #999;
		top: 20px;
	}
	.ws_step21 .warning_text
	{
		padding: 20px 50px;
		text-align: center;
		color: #c5bf66;
		font-weight: 300;
	}

	.ws_step3>.ws_step3_left
	{
		position: absolute;
		width: 230px;
		height: 100%;
		background: #F7FAFF;
		border-top-left-radius: 10px;
		border-bottom-left-radius: 10px;
		border-right: 1px solid #f6f9fe;
	}
	.ws_step3 .ws_step3_profile_img
	{
		text-align: center;
		margin-top: 55px;
	}
	.ws_step3 .ws_step3_profile_img>img
	{
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
	}

	.ws_step3_profile_title
	{
		text-align: center;
		color: #5e738b;
		font-weight: 700;
		font-size: 15px;
		padding-bottom: 10px;
	}
	.ws_step3_profile_email,.ws_step3_profile_birthday
	{
		text-align: center;
		color: #808e9b;
		margin-top: 10px;
	}
	.ws_step3_right
	{
		margin-left: 231px;
	}
	.ws_step3_right_node
	{
		position: relative;
		padding-top: 20px;
	}
	.ws_step3_right_node_title
	{
		font-size: 15px;
		font-weight: 700;
		color: #bdc3c7;
		padding: 10px 0;
		padding-left: 10px;
	}
	.ws_step3_right_node_title>b
	{
		font-weight: 700;
		color: #a7adb1;
	}
	.ws_step3_right_node_pics
	{
		height: 45px;
		text-align: left;
		white-space: nowrap;
		overflow: hidden;
	}
	.ws_step3_right_node_pics>div
	{
		display: inline-block;
		padding: 5px;
		width: 35px;
		height: 35px;
	}
	.ws_step3_right_node_pics img
	{
		-webkit-border-radius: 50%;
		-moz-border-radius: 50%;
		border-radius: 50%;
		width: 100%;
		height: 100%;
	}
	.open_profile_btn
	{
		background: transparent;
		border: 1px solid #808e9b;
		color: #808e9b !important;
	}
	.ws_step3_right_node .ws_right_arrow
	{
		position: absolute;
		right: 0;
		bottom: 0px;
		margin: auto;
		background: rgba(0,0,0,0.2);
		text-align: center;
		width: 20px;
		cursor: pointer;
		height: 45px;
		display: none;
		-webkit-animation: fadein1 0.6s;
		animation: fadein1 0.6s;
	}
	.ws_step3_right_node:hover .ws_right_arrow
	{
		display: block;
	}
	.ws_step3_right_node .ws_right_arrow>i
	{
		margin-top: 14px;
		font-size: 16px;
		color: #FFF;
	}
	.ws_step3_right_node .ws_left_arrow
	{
		position: absolute;
		left: 0;
		bottom: 0px;
		margin: auto;
		background: rgba(0,0,0,0.2);
		text-align: center;
		width: 20px;
		cursor: pointer;
		height: 45px;
		display: none;
		-webkit-animation: fadein1 0.6s;
		animation: fadein1 0.6s;
	}
	.ws_step3_right_node:hover .ws_left_arrow
	{
		display: block;
	}
	.ws_step3_right_node .ws_left_arrow>i
	{
		margin-top: 14px;
		font-size: 16px;
		color: #FFF;
	}

	@keyframes fadein1 {
		from { opacity: 0; }
		to   { opacity: 1; }
	}
	@-webkit-keyframes fadein1 {
		from { opacity: 0; }
		to   { opacity: 1; }
	}



	.ws_steps > div
	{
		position: relative;
		padding: 10px 40px 10px 10px;
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
	.ws_step22 .warning_text
	{
		padding: 20px 50px;
		text-align: center;
		color: #c5bf66;
		font-weight: 300;
	}
	.ws_step23_head
	{
		display: flex;
		justify-content: center;
		padding: 0 30px;
		background: #DDD;
		border-top-left-radius: 10px;
		border-top-right-radius: 10px;
		background-size: 100% auto;
		background-position: center;
		height: 100px;
		align-items: center;
		font-weight: 700;
		font-size: 27px;
		color: #FFF;
		text-shadow: 0 0px 4px #555;
	}

	#console_code_input
	{
		cursor: pointer;
		background: #EFEFEF !important;
		width: 150px;
		font-size: 10px !important;
	}


	.fb_logo > img
	{
		width: 60%;
		height: 180px;
		margin: 20px;
	}
	.fb_logo
	{
		width: 55%;
		display: flex;
		justify-content: center;
	}
	.display-flex
	{
		display: flex;
	}
</style>

<span class="close" data-modal-close="true">&times;</span>

<div class="ws_step1">
	<div style="padding-top: 60px;text-align: center;font-size: 17px;color: #888;font-weight: 600;">
		<?=esc_html__('Select authorization method', 'fs-poster')?>
	</div>

	<div class="ws_methods">
		<div class="ws_method_box ws_method_selectted_box" data-type="1">
			<div class="ws_method_box_img"><i class="fa fa-key"></i></div>
			<div class="ws_method_box_label ws_tooltip" data-title="Recomended" data-float="left"><?=esc_html__('Login & Pass', 'fs-poster')?></div>
		</div>
		<div class="ws_method_box" data-type="2">
			<div class="ws_method_box_img"><i class="fa fa-rocket" style="font-size: 38px;"></i></div>
			<div class="ws_method_box_label"><?=esc_html__('Cookie method', 'fs-poster')?></div>
		</div>
		<div class="ws_method_box" data-type="3">
			<div class="ws_method_box_img"><i class="fab fa-android"></i></div>
			<div class="ws_method_box_label"><?=esc_html__('Personal App', 'fs-poster')?></div>
		</div>
		<div style="clear: both;"></div>
	</div>

	<div style="text-align: center; margin-top: 180px;">
		<button class="ws_btn ws_bg_danger next_step_btn" type="button" style="width: 100px;"><?=esc_html__('NEXT STEP', 'fs-poster')?></button>
	</div>

	<div style="text-align: center; margin-top: 15px;">
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #74B9FF; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
	</div>
</div>

<div class="ws_step21" style="display: none;">

	<div style="width: 100%; margin-top: 30px; display: flex; justify-content: center; align-items: center;">
		<div class="fb_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/fb.svg'?>"></div>
		<div style="width: 45%;">

			<div style="padding-top: 20px; font-size: 17px; color: #888; font-weight: 600; margin-right: 20px;">
				<?=esc_html__('Enter your Facebook email and password', 'fs-poster')?>
			</div>

			<form method="POST" target="fs-fb-login" action="<?=admin_url('admin-ajax.php')?>">
				<input type="hidden" name="action" value="fs_account_login">
				<div class="ws_fb_login">
					<div>
						<input type="text" placeholder="Email address" name="email" class="email_input">
						<i class="fa fa-user"></i>
					</div>
					<div>
						<input type="password" placeholder="Password" name="password" class="pass_input">
						<i class="fa fa-key"></i>
					</div>
					<div style="margin-bottom: 10px;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show1').slideDown(200); });">
						<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
					</div>
					<div style="display: none; align-items: center" class="display-flex" id="proxy_show1">
						<div style="position: relative;">
							<input type="text" placeholder="Proxy" class="ws_form_element proxy" style="padding-left: 30px">
							<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
						</div>
						<div style="width: 30px; text-align: right; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
					</div>
				</div>

				<div style="margin-top: 10px;">
					<button class="ws_btn ws_bg_danger next_step_btn" type="button" style="width: 160px;"><?=esc_html__('GET ACCESS TOKEN', 'fs-poster')?></button>
				</div>
			</form>
		</div>
	</div>

	<div style="margin: 20px 50px 10px; display: none;" id="fb_at_panel">
		<div><textarea class="ws_form_element2 access_token_txtbox" placeholder="<?=esc_html__('Copy full content within openned window here', 'fs-poster')?>"></textarea></div>
		<div style="text-align: center; margin-top: 10px;"><button class="ws_btn ws_bg_danger next_step_btn2" type="button" style="width: 100px;"><?=esc_html__('NEXT STEP', 'fs-poster')?></button></div>
	</div>

	<div style="text-align: center; margin-top: 15px;">
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #74B9FF; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
	</div>
</div>

<div class="ws_step22" style="display: none;">

	<div style="width: 100%; margin-top: 30px; display: flex; justify-content: center; align-items: center;">
		<div class="fb_logo"><img src="<?=plugin_dir_url(__FILE__).'../../images/fb.svg'?>"></div>
		<div style="width: 45%;">

			<div style="padding-top: 20px; font-size: 17px; color: #888; font-weight: 600; margin-right: 20px;">
				<?=esc_html__('Enter your cookies', 'fs-poster')?>
				<a href="https://youtu.be/8W5WRw5LpNc" target="_blank" class="ws_tooltip" data-title="How to?"><i class="fab fa-youtube" style="color: #ff7171;"></i></a>
			</div>

			<form method="POST" target="fs-fb-login" action="<?=admin_url('admin-ajax.php')?>" style="margin-top: 20px;">
				<input type="hidden" name="action" value="fs_account_login">
				<div class="ws_fb_login">
					<div>
						<input type="text" placeholder="Cookie - c_user" name="c_user" class="cookie_c_user_input">
						<i class="fa fa-user"></i>
					</div>
					<div>
						<input type="text" placeholder="Cookie - xs" name="xs" class="cookie_xs_input">
						<i class="fa fa-key"></i>
					</div>
					<div style="margin-bottom: 10px;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show2').slideDown(200); });">
						<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
					</div>
					<div style="display: none; align-items: center" class="display-flex" id="proxy_show2">
						<div style="position: relative;">
							<input type="text" placeholder="Proxy" class="ws_form_element proxy" style="padding-left: 30px">
							<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
						</div>
						<div style="width: 30px; text-align: right; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
					</div>
				</div>

				<div style="margin-top: 10px;">
					<button class="ws_btn ws_bg_danger next_step_btn" type="button" style="width: 160px;"><?=esc_html__('ADD ACCOUNT', 'fs-poster')?></button>
				</div>
			</form>
		</div>
	</div>

	<div style="text-align: center; margin-top: 15px;">
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #74B9FF; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
	</div>
</div>

<div class="ws_step23" style="display: none;">

	<div class="ws_step23_head" style="background-image: url('<?=plugin_dir_url(__FILE__).'../../images/bg3.png'?>');">
		<?=esc_html__('Select your APP', 'fs-poster')?>
	</div>

	<div style="display: flex; justify-content: center; align-items: center; padding-top: 60px; padding-bottom: 15px;">
		<select class="ws_form_element" style="width: 250px;" id="appSelect2">
			<?php
			foreach($applications AS $application)
			{
				print '<option value="'.$application['id'].'">' . esc_html($application['name']) . '</option>';
			}
			if( empty($applications) )
			{
				print '<option disabled>'.esc_html__('No FB App found!', 'fs-poster').'</option>';
			}
			?>
		</select>
		<a href="admin.php?page=fs-poster-app&tab=fb" style="margin-left: 10px; color: #ff7675 !important;" class="ws_tooltip" data-title="<?=esc_html__('Add a new FB App', 'fs-poster')?>"><i class="fa fa-plus"></i></a>
	</div>

	<div style="display: flex; justify-content: center; align-items: center;" onclick="$(this).slideUp(200 , function(){ $('#proxy_show3').slideDown(200); });">
		<label style="color: #74b9ff;"><i class="fa fa-globe"></i> Use proxy</label>
	</div>

	<div style="display: none; justify-content: center; align-items: center;" class="display-flex" id="proxy_show3">
		<div style="position: relative; width: 250px;">
			<input type="text" placeholder="Proxy" class="ws_form_element2 proxy" style="padding-left: 30px">
			<i class="fas fa-globe" style="position: absolute; left: 10px; color: #74b9ff; top: 10px;"></i>
		</div>
		<div style="margin-left: 10px; cursor: help;" class="ws_tooltip" data-float="left" data-title="<?=esc_html__('Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' , 'fs-poster')?>"><i class="fa fa-info-circle" style="color: #999;"></i></div>
	</div>

	<div style="text-align: center; margin-top: 20px;">
		<button class="ws_btn ws_bg_danger next_step_btn" type="button" style="width: 100px;"><?=esc_html__('NEXT STEP', 'fs-poster')?></button>
	</div>

	<div style="text-align: center; margin-top: 15px;">
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #74B9FF; display: inline-block; margin: 1px;"></div>
		<div style="width: 6px; height: 6px; -webkit-border-radius: 50%;-moz-border-radius: 50%;border-radius: 50%; background: #CCC; display: inline-block; margin: 1px;"></div>
	</div>
</div>

<div class="ws_step3" style="display: none;">
	<div class="ws_step3_left">

		<div class="ws_step3_profile_img"><img src=""></div>
		<div class="ws_step3_profile_title"></div>
		<div class="ws_step3_profile_email"><i class="far fa-envelope-open "></i> <span></span></div>
		<div class="ws_step3_profile_birthday"><i class="far fa-calendar-alt "></i> <span></span></div>
		<div style="margin-top: 15px; text-align: center;">
			<a class="ws_btn open_profile_btn" target="_blank"><?=esc_html__('OPEN PROFILE', 'fs-poster')?></a>

			<button class="ws_btn ws_bg_danger finishBTN" type="button" data-modal-close="true"><?=esc_html__('FINISH', 'fs-poster')?></button>
		</div>

	</div>
	<div class="ws_step3_right">

		<div class="ws_step3_right_node" data-type="groups">
			<div class="ws_step3_right_node_title"><b>0</b> <?=esc_html__('groups', 'fs-poster')?></div>
			<div class="ws_step3_right_node_pics">

			</div>
		</div>

		<div class="ws_step3_right_node" data-type="ownpages">
			<div class="ws_step3_right_node_title"><b>0</b> <?=esc_html__('my pages', 'fs-poster')?></div>
			<div class="ws_step3_right_node_pics">

			</div>
		</div>

		<div class="ws_step3_right_node" data-type="pages">
			<div class="ws_step3_right_node_title"><b>0</b> <?=esc_html__('liked pages', 'fs-poster')?></div>
			<div class="ws_step3_right_node_pics">

			</div>
		</div>

	</div>
</div>

<script>

	function setAccessToken(access_token)
	{
		fsCode.loading(0);

		if( access_token.trim() == '' )
		{
			fsCode.toast("<?=esc_html__('Error! Please try again later!', 'fs-poster')?>");
			return false;
		}

		var proxy = $("#proModal<?=$mn?> .ws_step23 .proxy").val();

		fsCode.ajax('add_new_fb_account_with_at' , {'access_token': access_token , 'proxy': proxy} , function(result)
		{
			$("#proModal<?=$mn?> .ws_step23").fadeOut(200, function()
			{
				$("#proModal<?=$mn?> .ws_step3").fadeIn(200);
			});

			displayStep3(result);
		});
	}

	function displayStep3(result)
	{
		$(".ws_step3_profile_title").text(result['data']['name']);
		$(".ws_step3_profile_email>span").text(result['data']['email']);
		$(".ws_step3_profile_birthday>span").text(result['data']['birthday']);
		$(".ws_step3_profile_img>img").attr('src' , 'https://graph.facebook.com/' + result['data']['id'] + '/picture?redirect=0&height=80&width=80&type=normal&redirect=1');

		$(".open_profile_btn").attr('href' , 'https://fb.com/' + result['data']['id']);

		if( result['data']['nodes']['ownpages'] )
		{
			$(".ws_step3_right_node[data-type=ownpages]>.ws_step3_right_node_title>b").text(result['data']['nodes']['ownpages'].length);
			for(var i in result['data']['nodes']['ownpages'])
			{
				$(".ws_step3_right_node[data-type=ownpages]>.ws_step3_right_node_pics").append('<div title="'+result['data']['nodes']['ownpages'][i][1]+'"><img src="https://graph.facebook.com/' + result['data']['nodes']['ownpages'][i][0] + '/picture?redirect=1&height=40&width=40&type=normal"></div>');
			}

			$(".ws_step3_right_node[data-type=ownpages]").append('<div class="ws_left_arrow"><i class="fa fa-angle-left"></i></div>');
			$(".ws_step3_right_node[data-type=ownpages]").append('<div class="ws_right_arrow"><i class="fa fa-angle-right"></i></div>');
		}
		else
		{
			$(".ws_step3_right_node[data-type=ownpages]>.ws_step3_right_node_pics").html('<span style="padding-left: 11px; color: #888;">'+"<?=esc_html__('\'Load own-pages\' option is disabled in settings', 'fs-poster')?>"+'</span>');
		}


		if( result['data']['nodes']['pages'] )
		{
			$(".ws_step3_right_node[data-type=pages]>.ws_step3_right_node_title>b").text(result['data']['nodes']['pages'].length);
			for(var i in result['data']['nodes']['pages'])
			{
				$(".ws_step3_right_node[data-type=pages]>.ws_step3_right_node_pics").append('<div title="'+result['data']['nodes']['pages'][i][1]+'"><img src="https://graph.facebook.com/' + result['data']['nodes']['pages'][i][0] + '/picture?redirect=1&height=40&width=40&type=normal"></div>');
			}

			$(".ws_step3_right_node[data-type=pages]").append('<div class="ws_left_arrow"><i class="fa fa-angle-left"></i></div>');
			$(".ws_step3_right_node[data-type=pages]").append('<div class="ws_right_arrow"><i class="fa fa-angle-right"></i></div>');
		}
		else
		{
			$(".ws_step3_right_node[data-type=pages]>.ws_step3_right_node_pics").html('<span style="padding-left: 11px; color: #888;">'+"<?=esc_html__('\'Load my liked pages\' option is disabled in settings', 'fs-poster')?>"+'</span>');
		}

		if( result['data']['nodes']['groups'] )
		{
			$(".ws_step3_right_node[data-type=groups]>.ws_step3_right_node_title>b").text(result['data']['nodes']['groups'].length);
			for(var i in result['data']['nodes']['groups'])
			{
				$(".ws_step3_right_node[data-type=groups]>.ws_step3_right_node_pics").append('<div title="'+result['data']['nodes']['groups'][i][1]+'"><img src="' + result['data']['nodes']['groups'][i][2] + '"></div>');
			}

			$(".ws_step3_right_node[data-type=groups]").append('<div class="ws_left_arrow"><i class="fa fa-angle-left"></i></div>');
			$(".ws_step3_right_node[data-type=groups]").append('<div class="ws_right_arrow"><i class="fa fa-angle-right"></i></div>');
		}
		else
		{
			$(".ws_step3_right_node[data-type=groups]>.ws_step3_right_node_pics").html('<span style="padding-left: 11px; color: #888;">'+"<?=esc_html__('\'Load groups\' option is disabled in settings', 'fs-poster')?>"+'</span>');
		}

		$(".ws_left_arrow").click(function()
		{
			var leftPos = $(this).closest('.ws_step3_right_node').children('.ws_step3_right_node_pics').scrollLeft();
			$(this).closest('.ws_step3_right_node').children('.ws_step3_right_node_pics').stop().animate({scrollLeft: leftPos - 150}, 500);
		});

		$(".ws_right_arrow").click(function()
		{
			var leftPos = $(this).closest('.ws_step3_right_node').children('.ws_step3_right_node_pics').scrollLeft();
			$(this).closest('.ws_step3_right_node').children('.ws_step3_right_node_pics').stop(true).animate({scrollLeft: leftPos + 150}, 500);
		});

		fsCode.toast("<?=esc_html__('New account successfully added', 'fs-poster')?>");
	}

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

			$("#proModal<?=$mn?> .ws_step1").fadeOut(200, function()
			{
				$("#proModal<?=$mn?> .ws_step2"+type).fadeIn(200);
			});
		});

		$("#proModal<?=$mn?> .ws_step21 .next_step_btn").click(function()
		{
			var email       =   $("#proModal<?=$mn?> .ws_step21 .email_input").val(),
				password    =   $("#proModal<?=$mn?> .ws_step21 .pass_input").val();

			if( email == '' || password == '' )
			{
				fsCode.toast("<?=esc_html__('Email or Password is empty!', 'fs-poster')?>" , 'danger');
				return;
			}

			window.open('' , 'fs-fb-login' , 'width=700,height=350');
			$(this).closest('form').submit();

			$("#proModal<?=$mn?> #fb_at_panel").show(1000);
		});

		$("#proModal<?=$mn?> .ws_step21 .next_step_btn2").click(function()
		{
			var actoken = $("#proModal<?=$mn?> .ws_step21 .access_token_txtbox").val(),
				proxy   =   $("#proModal<?=$mn?> .ws_step21 .proxy").val();

			if( actoken == '' )
			{
				fsCode.toast("<?=esc_html__('Please copy content within opened window here!', 'fs-poster')?>" , 'danger');
				return;
			}

			fsCode.ajax('add_new_fb_account_with_at' , {'access_token': actoken , 'proxy': proxy} , function(result)
			{
				$("#proModal<?=$mn?> .ws_step21").fadeOut(200, function()
				{
					$("#proModal<?=$mn?> .ws_step3").fadeIn(200);
				});

				displayStep3(result);
			});
		});

		$("#proModal<?=$mn?> .ws_step22 .next_step_btn").click(function()
		{
			var cookie_c_user	=	$("#proModal<?=$mn?> .ws_step22 .cookie_c_user_input").val(),
				cookie_xs		=	$("#proModal<?=$mn?> .ws_step22 .cookie_xs_input").val(),
				proxy			=   $("#proModal<?=$mn?> .ws_step22 .proxy").val();

			if( cookie_c_user == '' || cookie_xs == '' )
			{
				fsCode.toast("<?=esc_html__('Please enter your Cookies!', 'fs-poster')?>" , 'danger');
				return;
			}

			fsCode.ajax('add_new_fb_account_with_cookie' , {'cookie_c_user': cookie_c_user , 'cookie_xs': cookie_xs, 'proxy': proxy} , function(result)
			{
				$("#proModal<?=$mn?> .ws_step22").fadeOut(200, function()
				{
					$("#proModal<?=$mn?> .ws_step3").fadeIn(200);
				});

				displayStep3(result);
			});
		});

		$("#proModal<?=$mn?> .ws_step23 .next_step_btn").click(function()
		{
			var appId = $("#appSelect2").val(),
				proxy = $("#proModal<?=$mn?> .ws_step23 .proxy").val();

			if( !(appId > 0) )
			{
				fsCode.toast("<?=esc_html__('Please select an application!', 'fs-poster')?>" , 'danger');
				return;
			}

			fsCode.loading(1);
			window.open('<?=site_url()?>?fb_app_redirect=' + appId + '&proxy=' + proxy , 'fs-app', 'width=750,height=550');
		});

		$(".finishBTN").click(function()
		{
			location.reload();
		});

		$("#console_code_input").click(function()
		{
			var target = $("#console_code_input")[0],
				s1 = target.selectionStart,
				s2 = target.selectionEnd;

			var currentFocus = document.activeElement;
			target.focus();
			target.setSelectionRange(0, target.value.length);

			try
			{
				document.execCommand("copy");
			} catch(e) { }

			if (currentFocus && typeof currentFocus.focus === "function")
			{
				currentFocus.focus();
			}
			target.setSelectionRange(s1,s2);

			fsCode.toast("<?=esc_html__('Code has been copied!', 'fs-poster')?>" , 'success')
		});

	});

</script>