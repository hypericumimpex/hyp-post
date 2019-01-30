<?php

trait FSPAccounts
{

	public function add_new_fb_account_with_at()
	{
		$accessToken = _post('access_token' , '' , 'string');
		$proxy       = _post('proxy' , '' , 'string');

		$getAppDetails = wpDB()->get_row("SELECT * FROM " . wpTable('apps') . " WHERE is_standart=3 AND driver='fb' LIMIT 1" , ARRAY_A);

		if( !$getAppDetails )
		{
			response(false , ['error_msg' => esc_html__('No FB App found!' , 'fs-poster')]);
		}

		require_once LIB_DIR . "fb/FacebookLib.php";

		$accessToken = FacebookLib::extractAccessToken($accessToken);

		if( $accessToken['status'] == false )
		{
			response(false , $accessToken['message']);
		}

		$accessToken = $accessToken['access_token'];

		$data = FacebookLib::authorizeFbUser( $getAppDetails['id'] , $accessToken , $proxy );

		response(true , ['data' => $data]);
	}

	public function delete_account()
	{
		if( !(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) )
		{
			exit();
		}
		$id = (int)$_POST['id'];

		$checkAccount = wpFetch('accounts' , $id);
		if( !$checkAccount )
		{
			response(false , esc_html__('Account not found!' , 'fs-poster'));
		}
		else if( $checkAccount['user_id'] != get_current_user_id() )
		{
			response(false , esc_html__('You do not have a permission to delete this account!' , 'fs-poster'));
		}

		wpDB()->delete(wpTable('accounts') , ['id' => $id]);
		wpDB()->delete(wpTable('account_status') , ['account_id' => $id]);
		wpDB()->delete(wpTable('account_access_tokens') , ['account_id' => $id]);

		wpDB()->query("DELETE FROM ".wpTable('account_node_status')." WHERE node_id IN (SELECT id FROM ".wpTable('account_nodes')." WHERE account_id='$id')");
		wpDB()->delete(wpTable('account_nodes') , ['account_id' => $id]);

		if( $checkAccount['driver'] == 'instagram' || $checkAccount['driver'] == 'google' )
		{
			$checkIfUsernameExist = wpFetch('accounts' , ['username' => $checkAccount['username'] , 'driver' => $checkAccount['driver']]);

			if( !$checkIfUsernameExist )
			{
				wpDB()->delete(wpTable('account_sessions') , ['driver' => $checkAccount['driver']  , 'username' => $checkAccount['username']]);
			}
		}


		response(true);
	}

	public function get_accounts()
	{
		$name = _post('name' , '' , 'string');

		$supported = ['fb' , 'twitter' , 'instagram' , 'linkedin', 'google', 'vk', 'pinterest' , 'reddit' , 'tumblr' , 'ok'];
		if( empty($name) || !in_array( $name , $supported ) )
		{
			response(false);
		}

		ob_start();
		require( VIEWS_DIR . 'app_menus/account/' . $name . '.php' );
		$viewOutput = ob_get_clean();

		response(true , [
			'html' => htmlspecialchars($viewOutput)
		]);
	}

	public function account_activity_change()
	{
		$id = _post('id' , '0' , 'num');
		$checked = _post('checked' , -1 , 'num' , ['0','1']);
		$filter_type = _post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = _post('categories' , [], 'array');

		if( !($id > 0 && $checked > -1) )
		{
			response(false );
		}

		$categoriesArr = [];
		foreach($categories AS $categId)
		{
			if(is_numeric($categId) && $categId > 0)
			{
				$categoriesArr[] = (int)$categId;
			}
		}
		$categoriesArr = implode(',' , $categoriesArr);

		if( (!empty($categoriesArr) && empty($filter_type)) || (empty($categoriesArr) && !empty($filter_type)) )
		{
			response(false , 'Please select categories and filter type!');
		}

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) || empty($categoriesArr) ? 'no' : $filter_type;

		$checkAccount = wpDB()->get_row("SELECT * FROM " . wpTable('accounts') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			response(false , 'Account not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			response(false , 'Account not found or you do not have a permission for this account!');
		}

		if( $checked )
		{
			$checkIfIsActive = wpFetch('account_status' , [
				'account_id'	=>	$id,
				'user_id'		=>	get_current_user_id(),
			]);

			if( !$checkIfIsActive )
			{
				wpDB()->insert(wpTable('account_status') , [
					'account_id'	=>	$id,
					'user_id'		=>	get_current_user_id(),
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				]);
			}
			else
			{
				wpDB()->update( wpTable('account_status') , [
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				] , ['id' => $checkIfIsActive['id']] );
			}
		}
		else
		{
			wpDB()->delete(wpTable('account_status') , [
				'account_id'	=>	$id,
				'user_id'		=>	get_current_user_id()
			]);
		}

		response(true);
	}

	public function make_account_public()
	{
		if(!( isset($_POST['checked']) && ( $_POST['checked'] == '1' || $_POST['checked'] == '0')
			&& isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0))
		{
			response(false);
		}

		$id = (int)$_POST['id'];
		$checked = (int)$_POST['checked'];

		$checkAccount = wpDB()->get_row("SELECT * FROM " . wpTable('accounts') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			response(false , 'Account not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() )
		{
			response(false , esc_html__('This is not one of you added account, therefore you do not have a permission for make this profile public or private.' , 'fs-poster'));
		}

		wpDB()->update(wpTable('accounts') , [
			'is_public' => $checked
		] , [
			'id' => $id,
			'user_id' => get_current_user_id()
		]);

		response(true);
	}

	public function add_instagram_account($forceLogin = true)
	{
		$username = _post('username' , '' , 'string');
		$password = _post('password' , '' , 'string');
		$proxy    = _post('proxy' , '' , 'string');

		if( empty($username) || empty($password) )
		{
			response(false, ['error_msg' => esc_html__('Please enter the instagram username and password!' , 'fs-poster')]);
		}

		if( $forceLogin )
		{
			// try to delete old session folder
			wpDB()->delete(wpTable('account_sessions') , ['driver'=>'instagram' , 'username' => $username]);
		}

		require_once LIB_DIR . "instagram/FSInstagram.php";

		$ig = FSInstagram::login($username , $password , $proxy , $forceLogin);
		if( isset($ig['do']) && $ig['do'] == 'challenge' )
		{
			response(true , [
				'do'            => 'challenge' ,
				'message'       => htmlspecialchars($ig['message']),
				'user_id'       => $ig['user_id'],
				'nonce_code'    => $ig['nonce_code']
			]);
		}
		else if( isset($ig['do']) && $ig['do'] == 'two_factor' )
		{
			response(true , [
				'do'                    => 'two_factor' ,
				'message'               => htmlspecialchars($ig['message']),
				'two_factor_identifier' => $ig['two_factor_identifier']
			]);
		}
		else if( $ig['status'] == 'error' )
		{
			response(false , htmlspecialchars(substr($ig['message'] , strpos($ig['message'] , ':')+2)) );
		}
		else if( !isset($ig['ig']) )
		{
			response(false);
		}

		$ig = $ig['ig'];

		$data = $ig->people->getSelfInfo()->asArray();
		$data = $data['user'];

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $ig->account_id,
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'instagram',
			'name'              =>  isset($data['full_name']) ? $data['full_name'] : '',
			'followers_count'   =>  isset($data['follower_count']) ? $data['follower_count'] : '0',
			'friends_count'     =>  isset($data['following_count']) ? $data['following_count'] : '0',
			'profile_pic'       =>  isset($data['profile_pic_url']) ? $data['profile_pic_url'] : ''
		];

		$checkIfExists = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $ig->account_id]);
		if( $checkIfExists )
		{
			wpDB()->update(wpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			wpDB()->insert(wpTable('accounts') , $sqlData);
		}

		response(true);
	}

	public function add_instagram_account_cookie_method()
	{
		$username			= _post('username' , '' , 'string');
		$cookie_csrftoken	= _post('cookie_csrftoken', '' , 'string');
		$cookie_sessionid	= _post('cookie_sessionid', '' , 'string');
		$cookie_ds_user_id	= _post('cookie_ds_user_id', '' , 'string');
		$cookie_mcd			= _post('cookie_mcd', '' , 'string');
		$proxy				= _post('proxy' , '' , 'string');

		$password			= '*****';

		if( empty($username) || empty($cookie_csrftoken) || empty($cookie_sessionid) || empty($cookie_mcd) || empty($cookie_ds_user_id) )
		{
			response(false, ['error_msg' => esc_html__('Please enter the instagram username and password!' , 'fs-poster')]);
		}

		require_once LIB_DIR . "instagram/FSInstagram.php";

		$cookiesArr = [
			["Name" => "sessionid", "Value" => $cookie_sessionid, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" =>	true],
			["Name" => "csrftoken", "Value" => $cookie_csrftoken, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" => false],
			["Name" => "mcd", "Value" => $cookie_mcd, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" => false]
		];

		$settingsArr = [
			"devicestring"		=> InstagramAPI\Devices\GoodDevices::getRandomGoodDevice(),
			"device_id"			=> InstagramAPI\Signatures::generateDeviceId(),
			"phone_id"			=> InstagramAPI\Signatures::generateUUID(true),
			"uuid"				=> InstagramAPI\Signatures::generateUUID(true),
			"advertising_id"	=> InstagramAPI\Signatures::generateUUID(true),
			"session_id"		=> InstagramAPI\Signatures::generateUUID(true),
			"last_login"		=> time(),
			"last_experiments"	=> time(),
			"account_id"		=> $cookie_ds_user_id
		];

		wpDB()->delete(wpTable('account_sessions') , ['driver'=>'instagram' , 'username' => $username]);
		wpDB()->insert(wpTable('account_sessions') , [
			'driver'	=>	'instagram',
			'username'	=>	$username,
			'settings'	=>	json_encode($settingsArr),
			'cookies'	=>	json_encode($cookiesArr)
		]);

		$insertedId = wpDB()->insert_id;

		$ig = FSInstagram::login($username , $password , $proxy , false);
		if( !isset($ig['ig']) )
		{
			wpDB()->delete(wpTable('account_sessions') , ['id' => $insertedId]);
			response(false);
		}

		$ig = $ig['ig'];

		$data = $ig->people->getSelfInfo()->asArray();
		$data = $data['user'];

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $ig->account_id,
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'instagram',
			'name'              =>  isset($data['full_name']) ? $data['full_name'] : '',
			'followers_count'   =>  isset($data['follower_count']) ? $data['follower_count'] : '0',
			'friends_count'     =>  isset($data['following_count']) ? $data['following_count'] : '0',
			'profile_pic'       =>  isset($data['profile_pic_url']) ? $data['profile_pic_url'] : ''
		];

		$checkIfExists = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $ig->account_id]);
		if( $checkIfExists )
		{
			wpDB()->update(wpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			wpDB()->insert(wpTable('accounts') , $sqlData);
		}

		response(true);
	}

	public function confirm_instagram_challenge()
	{
		$username    = _post('username' , '' , 'string');
		$password    = _post('password' , '' , 'string');
		$proxy       = _post('proxy' , '' , 'string');
		$code        = _post('code' , '' , 'string');
		$user_id     = _post('user_id' , '' , 'string');
		$nonce_code  = _post('nonce_code' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) || empty($user_id) || empty($nonce_code) )
		{
			response(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		require_once LIB_DIR . "instagram/FSInstagram.php";

		$ig = FSInstagram::challenge($username , $password , $proxy , $user_id , $nonce_code , $code);

		if( $ig['status'] == 'error' )
		{
			response(false , htmlspecialchars($ig['message']));
		}

		$this->add_instagram_account(false);
	}

	public function confirm_two_factor()
	{
		$username               = _post('username' , '' , 'string');
		$password               = _post('password' , '' , 'string');
		$proxy                  = _post('proxy' , '' , 'string');
		$code                   = _post('code' , '' , 'string');
		$two_factor_identifier  = _post('two_factor_identifier' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) || empty($two_factor_identifier) )
		{
			response(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		require_once LIB_DIR . "instagram/FSInstagram.php";

		$ig = FSInstagram::verifyTwoFactor($username , $password , $proxy , $two_factor_identifier , $code);

		if( $ig['status'] == 'error' )
		{
			response(false , htmlspecialchars($ig['message']));
		}

		$this->add_instagram_account(false);
	}

	public function add_vk_account()
	{
		$accessToken    = _post('at' , '' , 'string');
		$app            = _post('app' , '0' , 'int');
		$proxy          = _post('proxy' , '0' , 'string');

		if( empty($accessToken) )
		{
			response(false , ['error_msg' => esc_html__('Access token is empty!' , 'fs-poster')]);
		}

		preg_match('/access_token\=([^\&]+)/' , $accessToken , $accessToken2);

		if( isset($accessToken2[1]) )
		{
			$accessToken = $accessToken2[1];
		}

		$getApp = wpFetch('apps' , ['driver' => 'vk' , 'app_id' => $app]);

		require_once LIB_DIR . "vk/Vk.php";

		$result = Vk::authorizeVkUser((int)$getApp['id'] , $accessToken , $proxy);

		if( isset($result['error']) )
		{
			response(false , $result['error']);
		}

		response(true);
	}

	public function pinterest_account_board_change()
	{
		$accountId = _post('account_id' , '0' , 'num');
		$board = _post('board' , '' , 'string');

		if( empty($board) || !($accountId > 0) )
		{
			response(false);
		}

		$boardId = mb_substr($board , 0 , mb_strpos($board , ':' , 0 , 'UTF-8') , 'UTF-8');
		$boardName = mb_substr($board , mb_strpos($board , ':' , 0 , 'UTF-8') + 1 , null , 'UTF-8');

		$board = json_encode(['board' => ['id' => $boardId , 'name' => $boardName]]);

		wpDB()->update(wpTable('accounts') , ['options' => $board] , [
			'user_id'   =>  get_current_user_id(),
			'id'        =>  $accountId
		]);

		response(true);
	}

	public function reddit_account_subreddit_change()
	{
		$accountId = _post('account_id' , '0' , 'num');
		$subreddit = _post('subreddit' , '' , 'string');

		if( !($accountId > 0) )
		{
			response(false);
		}

		$subreddit = json_encode(['subreddit' => $subreddit]);

		wpDB()->update(wpTable('accounts') , ['options' => $subreddit] , [
			'user_id'   =>  get_current_user_id(),
			'id'        =>  $accountId
		]);

		response(true);
	}

	public function add_google_account($forceLogin = true)
	{
		$username	= _post('username' , '' , 'string');
		$password	= _post('password' , '' , 'string');
		$proxy		= _post('proxy' , '' , 'string');
		$capcha		= _post('capcha' , '' , 'string');

		if( empty($username) || empty($password) )
		{
			response(false, ['error_msg' => esc_html__('Please enter you email and password!' , 'fs-poster')]);
		}

		if( $forceLogin )
		{
			// try to delete old session folder
			// wpDB()->delete(wpTable('account_sessions') , ['driver'=>'google' , 'username' => $username]);
		}

		require_once LIB_DIR . "google/GooglePlus.php";

		$googlePlus = new GooglePlus($username , $password , $proxy);

		if( !empty($capcha) )
		{
			$googlePlus->setCapcha($capcha);
		}

		$login = $googlePlus->login();
		$googlePlus->saveData();

		if( $login['status'] === 'error' )
		{
			if( isset($login['capcha_url']) )
			{
				response(true , [
					'do'		=>	'capcha',
					'error_msg' =>	htmlspecialchars($login['message']),
					'capcha'	=>	$login['capcha_url']
				] );
			}
			else
			{
				response(false , htmlspecialchars($login['message']) );
			}

		}
		else if( $login['status'] === 'capcha' )
		{
			response(true , [
				'do'		=>	'capcha',
				'error_msg' =>	'Please fill the capcha field',
				'capcha'	=>	isset($login['capcha_url']) ? $login['capcha_url'] : null,
			] );
		}
		else if( $login['status'] === 'two_factor_auth' )
		{
			response(true , [
				'do'            => 'two_factor' ,
				'message'       => htmlspecialchars($login['message'])
			]);
		}
		else if( $login['status'] === 'challenge_1st_step' )
		{
			response(true , [
				'do'            => 'challenge_1st_step' ,
				'message'       => htmlspecialchars($login['message'])
			]);
		}
		else if( $login['status'] === 'challenge' )
		{
			response(true , [
				'do'            => 'challenge' ,
				'message'       => htmlspecialchars($login['message'])
			]);
		}

		$data = $googlePlus->getUserInfo();

		if( empty($data['id']) )
		{
			response(false, 'Colud not to get user info!');
		}

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'google',
			'name'              =>  $data['name'],
			'profile_pic'       =>  $data['profile_image']
		];

		$checkIfExists = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			wpDB()->update(wpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			wpDB()->insert(wpTable('accounts') , $sqlData);
		}

		response(true);
	}

	public function add_google_account_cookie_method($forceLogin = true)
	{
		$username		= _post('username' , '' , 'string');
		$cookie_sid		= _post('cookie_sid' , '' , 'string');
		$cookie_hsid	= _post('cookie_hsid' , '' , 'string');
		$cookie_ssid	= _post('cookie_ssid' , '' , 'string');
		$proxy			= _post('proxy' , '' , 'string');
		$password		= '*****';

		if( empty($username) || empty($cookie_sid) || empty($cookie_hsid) || empty($cookie_ssid) )
		{
			response(false, ['error_msg' => esc_html__('Please enter your email and cookies!' , 'fs-poster')]);
		}

		$cookies = [
			["Name" => "SID","Value"  => $cookie_sid,  "Domain" => ".google.com","Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => false,"Discard" => false,"HttpOnly" => false,"Priority" => "HIGH"],
			["Name" => "HSID","Value" => $cookie_hsid, "Domain" => ".google.com","Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => false,"Discard" => false,"HttpOnly" => true,"Priority" => "HIGH"],
			["Name" => "SSID","Value" => $cookie_ssid, "Domain" => ".google.com","Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => false,"Discard" => false,"HttpOnly" => false,"Priority" => "HIGH"]
		];


		require_once LIB_DIR . "google/GooglePlus.php";

		$googlePlus = new GooglePlus($username , $password , $proxy , $cookies);

		$data = $googlePlus->getUserInfo();

		if( empty($data['id']) )
		{
			response(false, 'Colud not to get user info!');
		}

		$googlePlus->saveData();

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'google',
			'name'              =>  $data['name'],
			'profile_pic'       =>  $data['profile_image']
		];

		$checkIfExists = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			wpDB()->update(wpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			wpDB()->insert(wpTable('accounts') , $sqlData);
		}

		response(true);
	}

	public function google_confirm_two_factor()
	{
		$username               = _post('username' , '' , 'string');
		$password               = _post('password' , '' , 'string');
		$proxy                  = _post('proxy' , '' , 'string');
		$code                   = _post('code' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) )
		{
			response(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		require_once LIB_DIR . "google/GooglePlus.php";

		$googlePlus = new GooglePlus( $username , $password , $proxy );
		$activate = $googlePlus->approve2FactorAuth( $code );
		$googlePlus->saveData();

		if( $activate['status'] === 'error' )
		{
			response(false , htmlspecialchars($activate['message']));
		}

		$data = $googlePlus->getUserInfo();

		if( empty($data['id']) )
		{
			response(false, 'Colud not to get user info!');
		}

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'google',
			'name'              =>  $data['name'],
			'profile_pic'       =>  $data['profile_image']
		];

		$checkIfExists = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			wpDB()->update(wpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			wpDB()->insert(wpTable('accounts') , $sqlData);
		}

		response(true);
	}

	public function google_confirm_challenge_1st_step()
	{
		$username               = _post('username' , '' , 'string');
		$password               = _post('password' , '' , 'string');
		$proxy                  = _post('proxy' , '' , 'string');
		$code                   = _post('code' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) )
		{
			response(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		require_once LIB_DIR . "google/GooglePlus.php";

		$googlePlus = new GooglePlus( $username , $password , $proxy );
		$activate = $googlePlus->challenge1stStep( $code );
		$googlePlus->saveData();

		if( $activate['status'] === 'error' )
		{
			response(false , htmlspecialchars($activate['message']));
		}

		$data = $googlePlus->getUserInfo();

		if( empty($data['id']) )
		{
			response(false, 'Colud not to get user info!');
		}

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  $username,
			'password'          =>  $password,
			'proxy'             =>  $proxy,
			'driver'            =>  'google',
			'name'              =>  $data['name'],
			'profile_pic'       =>  $data['profile_image']
		];

		$checkIfExists = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			wpDB()->update(wpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			wpDB()->insert(wpTable('accounts') , $sqlData);
		}

		response(true);
	}

	public function google_community_get_cats()
	{
		$id	= _post('id' , '' , 'string');
		$accountId	= _post('account_id' , '0' , 'num');
		if( !(!empty($id) && $accountId > 0) )
		{
			response(false);
		}

		$userId = (int)get_current_user_id();

		$accountInf = wpDB()->get_row("SELECT * FROM ".wpTable('accounts')." WHERE id='{$accountId}' AND driver='google' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			response(false, 'You have not a permission for adding community in this account!');
		}

		require_once LIB_DIR . '/google/GooglePlus.php';
		$google = new GooglePlus($accountInf['username'] , $accountInf['password'] , $accountInf['proxy']);

		$communities = $google->getCategories($id);

		response(true , ['cats' => $communities]);
	}

	public function google_community_save()
	{
		$accountId	= _post('account_id' , '0' , 'num');
		$community	= _post('community' , '' , 'string');
		$communityCateg	= _post('community_categ' , '' , 'string');

		$filter_type = _post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = _post('categories' , [], 'array');

		$categoriesArr = [];
		foreach($categories AS $categId)
		{
			if(is_numeric($categId) && $categId > 0)
			{
				$categoriesArr[] = (int)$categId;
			}
		}
		$categoriesArr = implode(',' , $categoriesArr);

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) || empty($categoriesArr) ? 'no' : $filter_type;

		if( !(!empty($community) && $accountId > 0) )
		{
			response(false);
		}

		$userId = (int)get_current_user_id();

		$accountInf = wpDB()->get_row("SELECT * FROM ".wpTable('accounts')." WHERE id='{$accountId}' AND driver='google' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			response(false, 'You have not a permission for adding community in this account!');
		}

		require_once LIB_DIR . '/google/GooglePlus.php';
		$google = new GooglePlus($accountInf['username'] , $accountInf['password'] , $accountInf['proxy']);

		$communities = $google->getCommunities();

		$communityName = null;
		$members = null;
		$communityCover = null;
		foreach( $communities AS $cInf )
		{
			if( $cInf['id'] == $community )
			{
				$communityName = $cInf['name'];
				$members = $cInf['members'];
				$communityCover = $cInf['image'];
				break;
			}
		}

		if( is_null( $communityName ) )
		{
			response(false, 'Community not found!');
		}

		$communityCategories = $google->getCategories( $community );
		$communityCategName = null;
		foreach( $communityCategories AS $categInf )
		{
			if( $categInf['id'] == $communityCateg )
			{
				$communityCategName = $categInf['name'];
			}
		}

		if( is_null( $communityCategName ) )
		{
			response(false, 'Community category not found!');
		}

		wpDB()->insert(wpTable('account_nodes') , [
			'user_id'			=>	$userId,
			'driver'			=>	'google',
			'account_id'		=>	$accountId,
			'node_type'			=>	'community',
			'node_id'			=>	$community,
			'name'				=>	$communityName,
			'access_token'		=>	$communityCateg,
			'category'			=>	$communityCategName,
			'fan_count'			=>	$members,
			'cover'				=>	$communityCover
		]);

		$nodeId = wpDB()->insert_id;

		// actiavte...
		wpDB()->insert(wpTable('account_node_status') , [
			'node_id'		=>	$nodeId,
			'user_id'		=>	$userId,
			'filter_type'	=>	$filter_type,
			'categories'	=>	$categoriesArr
		]);

		response(true , ['id' => $nodeId]);
	}

}