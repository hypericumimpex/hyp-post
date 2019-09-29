<?php

trait FSPAccounts
{

	public function add_new_fb_account_with_cookie()
	{
		$cookieCuser	= FS_post('cookie_c_user' , '' , 'string');
		$cookieXs		= FS_post('cookie_xs' , '' , 'string');
		$proxy			= FS_post('proxy' , '' , 'string');

		require_once FS_LIB_DIR . "fb/FacebookCookieMethod.php";

		$fb = new FacebookCookieMethod($cookieCuser, $cookieXs, $proxy);
		$data = $fb->authorizeFbUser();

		if( $data === false )
		{
			FSresponse(false, 'The entered cookies are wrong!');
		}

		FSresponse(true , ['data' => $data]);
	}

	public function add_new_fb_account_with_at()
	{
		$accessToken = FS_post('access_token' , '' , 'string');
		$proxy       = FS_post('proxy' , '' , 'string');

		require_once FS_LIB_DIR . "fb/FacebookLib.php";

		$accessToken = FacebookLib::extractAccessToken($accessToken);

		if( $accessToken['status'] == false )
		{
			FSresponse(false , $accessToken['message']);
		}

		$accessToken = $accessToken['access_token'];

		$data = FacebookLib::authorizeFbUser( 0 , $accessToken , $proxy );

		FSresponse(true , ['data' => $data]);
	}

	public function delete_account()
	{
		if( !(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) )
		{
			exit();
		}
		$id = (int)$_POST['id'];

		$checkAccount = FSwpFetch('accounts' , $id);
		if( !$checkAccount )
		{
			FSresponse(false , esc_html__('Account not found!' , 'fs-poster'));
		}
		else if( $checkAccount['user_id'] != get_current_user_id() )
		{
			FSresponse(false , esc_html__('You do not have a permission to delete this account!' , 'fs-poster'));
		}

		FSwpDB()->delete(FSwpTable('accounts') , ['id' => $id]);
		FSwpDB()->delete(FSwpTable('account_status') , ['account_id' => $id]);
		FSwpDB()->delete(FSwpTable('account_access_tokens') , ['account_id' => $id]);

		FSwpDB()->query("DELETE FROM ".FSwpTable('account_node_status')." WHERE node_id IN (SELECT id FROM ".FSwpTable('account_nodes')." WHERE account_id='$id')");
		FSwpDB()->delete(FSwpTable('account_nodes') , ['account_id' => $id]);

		if( $checkAccount['driver'] == 'instagram' )
		{
			$checkIfUsernameExist = FSwpFetch('accounts' , ['username' => $checkAccount['username'] , 'driver' => $checkAccount['driver']]);

			if( !$checkIfUsernameExist )
			{
				FSwpDB()->delete(FSwpTable('account_sessions') , ['driver' => $checkAccount['driver']  , 'username' => $checkAccount['username']]);
			}
		}


		FSresponse(true);
	}

	public function get_accounts()
	{
		$name = FS_post('name' , '' , 'string');

		$supported = ['fb' , 'twitter' , 'instagram' , 'linkedin', 'vk', 'pinterest' , 'reddit' , 'tumblr' , 'ok', 'google_b', 'telegram', 'medium'];
		if( empty($name) || !in_array( $name , $supported ) )
		{
			FSresponse(false);
		}

		ob_start();
		require( FS_VIEWS_DIR . 'app_menus/account/' . $name . '.php' );
		$viewOutput = ob_get_clean();

		FSresponse(true , [
			'html' => htmlspecialchars($viewOutput)
		]);
	}

	public function account_activity_change()
	{
		$id = FS_post('id' , '0' , 'num');
		$checked = FS_post('checked' , -1 , 'num' , ['0','1']);
		$filter_type = FS_post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = FS_post('categories' , [], 'array');

		if( !($id > 0 && $checked > -1) )
		{
			FSresponse(false );
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
			FSresponse(false , 'Please select categories and filter type!');
		}

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) || empty($categoriesArr) ? 'no' : $filter_type;

		$checkAccount = FSwpDB()->get_row("SELECT * FROM " . FSwpTable('accounts') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			FSresponse(false , 'Account not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			FSresponse(false , 'Account not found or you do not have a permission for this account!');
		}

		if( $checked )
		{
			$checkIfIsActive = FSwpFetch('account_status' , [
				'account_id'	=>	$id,
				'user_id'		=>	get_current_user_id(),
			]);

			if( !$checkIfIsActive )
			{
				FSwpDB()->insert(FSwpTable('account_status') , [
					'account_id'	=>	$id,
					'user_id'		=>	get_current_user_id(),
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				]);
			}
			else
			{
				FSwpDB()->update( FSwpTable('account_status') , [
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				] , ['id' => $checkIfIsActive['id']] );
			}
		}
		else
		{
			FSwpDB()->delete(FSwpTable('account_status') , [
				'account_id'	=>	$id,
				'user_id'		=>	get_current_user_id()
			]);
		}

		FSresponse(true);
	}

	public function make_account_public()
	{
		if(!( isset($_POST['checked']) && ( $_POST['checked'] == '1' || $_POST['checked'] == '0')
			&& isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0))
		{
			FSresponse(false);
		}

		$id = (int)$_POST['id'];
		$checked = (int)$_POST['checked'];

		$checkAccount = FSwpDB()->get_row("SELECT * FROM " . FSwpTable('accounts') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			FSresponse(false , 'Account not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() )
		{
			FSresponse(false , esc_html__('This is not one of you added account, therefore you do not have a permission for make this profile public or private.' , 'fs-poster'));
		}

		FSwpDB()->update(FSwpTable('accounts') , [
			'is_public' => $checked
		] , [
			'id' => $id,
			'user_id' => get_current_user_id()
		]);

		FSresponse(true);
	}

	public function add_instagram_account($forceLogin = true)
	{
		$username = FS_post('username' , '' , 'string');
		$password = FS_post('password' , '' , 'string');
		$proxy    = FS_post('proxy' , '' , 'string');

		if( empty($username) || empty($password) )
		{
			FSresponse(false, ['error_msg' => esc_html__('Please enter the instagram username and password!' , 'fs-poster')]);
		}

		if( $forceLogin )
		{
			// try to delete old session folder
			FSwpDB()->delete(FSwpTable('account_sessions') , ['driver'=>'instagram' , 'username' => $username]);
		}

		require_once FS_LIB_DIR . "instagram/FSInstagram.php";

		$ig = FSInstagram::login($username , $password , $proxy , $forceLogin);
		if( isset($ig['do']) && $ig['do'] == 'challenge' )
		{
			FSresponse(true , [
				'do'            => 'challenge' ,
				'message'       => htmlspecialchars($ig['message']),
				'user_id'       => $ig['user_id'],
				'nonce_code'    => $ig['nonce_code']
			]);
		}
		else if( isset($ig['do']) && $ig['do'] == 'two_factor' )
		{
			FSresponse(true , [
				'do'                    => 'two_factor' ,
				'message'               => htmlspecialchars($ig['message']),
				'two_factor_identifier' => $ig['two_factor_identifier']
			]);
		}
		else if( $ig['status'] == 'error' )
		{
			FSresponse(false , htmlspecialchars(substr($ig['message'] , strpos($ig['message'] , ':')+2)) );
		}
		else if( !isset($ig['ig']) )
		{
			FSresponse(false);
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

		$checkIfExists = FSwpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $ig->account_id]);
		if( $checkIfExists )
		{
			FSwpDB()->update(FSwpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			FSwpDB()->insert(FSwpTable('accounts') , $sqlData);
		}

		FSresponse(true);
	}

	public function add_instagram_account_cookie_method()
	{
		$cookie_sessionid	= FS_post('cookie_sessionid', '' , 'string');
		$proxy				= FS_post('proxy' , '' , 'string');

		$password			= '*****';

		if( empty($cookie_sessionid) )
		{
			FSresponse(false, ['error_msg' => esc_html__('Please enter the instagram username and password!' , 'fs-poster')]);
		}
		require_once FS_LIB_DIR . "instagram/FSInstagramApi.php";

		$details			= FSInstagramApi::getDetailsBySessId($cookie_sessionid, $proxy);

		$username			= $details['username'];
		$cookie_ds_user_id	= $details['id'];
		$cookie_mcd			= '3';
		$cookie_csrftoken	= $details['csrf'];

		require_once FS_LIB_DIR . "instagram/FSInstagram.php";

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

		FSwpDB()->delete(FSwpTable('account_sessions') , ['driver'=>'instagram' , 'username' => $username]);
		FSwpDB()->insert(FSwpTable('account_sessions') , [
			'driver'	=>	'instagram',
			'username'	=>	$username,
			'settings'	=>	json_encode($settingsArr),
			'cookies'	=>	json_encode($cookiesArr)
		]);

		$insertedId = FSwpDB()->insert_id;

		$ig = FSInstagram::login($username , $password , $proxy , false);
		if( !isset($ig['ig']) )
		{
			FSwpDB()->delete(FSwpTable('account_sessions') , ['id' => $insertedId]);
			FSresponse(false, ( isset($ig['message']) && is_string($ig['message']) ? htmlspecialchars($ig['message']) : '' ) );
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

		$checkIfExists = FSwpFetch('accounts' , ['user_id' => get_current_user_id() , 'profile_id' => $ig->account_id]);
		if( $checkIfExists )
		{
			FSwpDB()->update(FSwpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			FSwpDB()->insert(FSwpTable('accounts') , $sqlData);
		}

		FSresponse(true);
	}

	public function confirm_instagram_challenge()
	{
		$username    = FS_post('username' , '' , 'string');
		$password    = FS_post('password' , '' , 'string');
		$proxy       = FS_post('proxy' , '' , 'string');
		$code        = FS_post('code' , '' , 'string');
		$user_id     = FS_post('user_id' , '' , 'string');
		$nonce_code  = FS_post('nonce_code' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) || empty($user_id) || empty($nonce_code) )
		{
			FSresponse(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		require_once FS_LIB_DIR . "instagram/FSInstagram.php";

		$ig = FSInstagram::challenge($username , $password , $proxy , $user_id , $nonce_code , $code);

		if( $ig['status'] == 'error' )
		{
			FSresponse(false , htmlspecialchars($ig['message']));
		}

		$this->add_instagram_account(false);
	}

	public function confirm_two_factor()
	{
		$username               = FS_post('username' , '' , 'string');
		$password               = FS_post('password' , '' , 'string');
		$proxy                  = FS_post('proxy' , '' , 'string');
		$code                   = FS_post('code' , '' , 'string');
		$two_factor_identifier  = FS_post('two_factor_identifier' , '' , 'string');

		if( empty($username) || empty($password) || empty($code) || empty($two_factor_identifier) )
		{
			FSresponse(false, ['error_msg' => esc_html__('Please enter the code!' , 'fs-poster')]);
		}

		require_once FS_LIB_DIR . "instagram/FSInstagram.php";

		$ig = FSInstagram::verifyTwoFactor($username , $password , $proxy , $two_factor_identifier , $code);

		if( $ig['status'] == 'error' )
		{
			FSresponse(false , htmlspecialchars($ig['message']));
		}

		$this->add_instagram_account(false);
	}

	public function add_vk_account()
	{
		$accessToken    = FS_post('at' , '' , 'string');
		$app            = FS_post('app' , '0' , 'int');
		$proxy          = FS_post('proxy' , '0' , 'string');

		if( empty($accessToken) )
		{
			FSresponse(false , ['error_msg' => esc_html__('Access token is empty!' , 'fs-poster')]);
		}

		preg_match('/access_token\=([^\&]+)/' , $accessToken , $accessToken2);

		if( isset($accessToken2[1]) )
		{
			$accessToken = $accessToken2[1];
		}

		$getApp = FSwpFetch('apps' , ['driver' => 'vk' , 'app_id' => $app]);

		require_once FS_LIB_DIR . "vk/Vk.php";

		$result = Vk::authorizeVkUser((int)$getApp['id'] , $accessToken , $proxy);

		if( isset($result['error']) )
		{
			FSresponse(false , $result['error']);
		}

		FSresponse(true);
	}

	public function pinterest_account_board_change()
	{
		$accountId = FS_post('account_id' , '0' , 'num');
		$board = FS_post('board' , '' , 'string');

		if( empty($board) || !($accountId > 0) )
		{
			FSresponse(false);
		}

		$boardId = mb_substr($board , 0 , mb_strpos($board , ':' , 0 , 'UTF-8') , 'UTF-8');
		$boardName = mb_substr($board , mb_strpos($board , ':' , 0 , 'UTF-8') + 1 , null , 'UTF-8');

		$board = json_encode(['board' => ['id' => $boardId , 'name' => $boardName]]);

		FSwpDB()->update(FSwpTable('accounts') , ['options' => $board] , [
			'user_id'   =>  get_current_user_id(),
			'id'        =>  $accountId
		]);

		FSresponse(true);
	}

	public function search_subreddits()
	{
		$accountId	= FS_post('account_id' , '0' , 'num');
		$search		= FS_post('search', '', 'string');

		$userId = get_current_user_id();

		$accountInf = FSwpDB()->get_row("SELECT * FROM ".FSwpTable('accounts')." tb1 WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			FSresponse(false, 'You have not a permission for adding subreddit in this account!');
		}

		$accessTokenGet = FSwpFetch('account_access_tokens', ['account_id' => $accountId]);

		require_once FS_LIB_DIR . 'reddit/Reddit.php';

		$accessToken = $accessTokenGet['access_token'];
		if( (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}

		$searchSubreddits = Reddit::cmd('https://oauth.reddit.com/api/search_subreddits', 'POST', $accessToken, [
			'query'						=> $search,
			'include_over_18'			=>	true,
			'exact'						=>	false,
			'include_unadvertisable'	=>	true
		]);

		$newArr = [];
		$preventDublicates = [];

		foreach( $searchSubreddits['subreddits'] AS $subreddit )
		{
			$preventDublicates[ $subreddit['name'] ] = true;

			$newArr[] = [
				'text'	=> htmlspecialchars($subreddit['name'] . ' ( ' . $subreddit['subscriber_count'] . ' subscribers )'),
				'id'	=> htmlspecialchars($subreddit['name'])
			];
		}

		// for fixing Reddit API bug
		$searchSubreddits = Reddit::cmd('https://oauth.reddit.com/api/search_subreddits', 'POST', $accessToken, [
			'query'						=> $search,
			'exact'						=>	true
		]);

		foreach( $searchSubreddits['subreddits'] AS $subreddit )
		{
			if( isset( $preventDublicates[ $subreddit['name'] ] ) )
			{
				continue;
			}

			$newArr[] = [
				'text'	=> htmlspecialchars($subreddit['name'] . ' ( ' . $subreddit['subscriber_count'] . ' subscribers )'),
				'id'	=> htmlspecialchars($subreddit['name'])
			];
		}

		FSresponse(true, ['subreddits' => $newArr]);
	}

	public function reddit_get_subreddt_flairs()
	{
		$accountId	= FS_post('account_id' , '0' , 'num');
		$subreddit	= FS_post('subreddit', '', 'string');

		$subreddit = basename($subreddit);

		$userId = get_current_user_id();

		$accountInf = FSwpDB()->get_row("SELECT * FROM ".FSwpTable('accounts')." tb1 WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			FSresponse(false, 'You have not a permission for adding subreddit in this account!');
		}

		$accessTokenGet = FSwpFetch('account_access_tokens', ['account_id' => $accountId]);

		require_once FS_LIB_DIR . 'reddit/Reddit.php';

		$accessToken = $accessTokenGet['access_token'];
		if( (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}

		$flairs = Reddit::cmd('https://oauth.reddit.com/r/'.$subreddit.'/api/link_flair', 'GET', $accessToken);

		$newArr = [];
		if( !isset($flairs['error']) )
		{
			foreach( $flairs AS $flair )
			{
				$newArr[] = [
					'text'	=> htmlspecialchars($flair['text']),
					'id'	=> htmlspecialchars($flair['id'])
				];
			}
		}

		FSresponse(true, ['flairs' => $newArr]);
	}

	public function reddit_subreddit_save()
	{
		$accountId		= FS_post('account_id' , '0' , 'num');
		$subreddit		= FS_post('subreddit' , '' , 'string');
		$flairId		= FS_post('flair' , '' , 'string');
		$flairName		= FS_post('flair_name' , '' , 'string');

		$filter_type = FS_post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = FS_post('categories' , [], 'array');

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

		if( !(!empty($subreddit) && $accountId > 0) )
		{
			FSresponse(false);
		}

		$userId = (int)get_current_user_id();

		$accountInf = FSwpDB()->get_row("SELECT * FROM ".FSwpTable('accounts')." WHERE id='{$accountId}' AND driver='reddit' AND (user_id='{$userId}' OR is_public=1) " , ARRAY_A);

		if( !$accountInf )
		{
			FSresponse(false, 'You have not a permission for adding subreddit in this account!');
		}

		FSwpDB()->insert(FSwpTable('account_nodes') , [
			'user_id'			=>	$userId,
			'driver'			=>	'reddit',
			'account_id'		=>	$accountId,
			'node_type'			=>	'subreddit',
			'screen_name'		=>	$subreddit,
			'name'				=>	$subreddit,
			'access_token'		=>	$flairId,
			'category'			=>	$flairName
		]);

		$nodeId = FSwpDB()->insert_id;

		// actiavte...
		FSwpDB()->insert(FSwpTable('account_node_status') , [
			'node_id'		=>	$nodeId,
			'user_id'		=>	$userId,
			'filter_type'	=>	$filter_type,
			'categories'	=>	$categoriesArr
		]);

		FSresponse(true , ['id' => $nodeId]);
	}

	public function add_google_b_account()
	{
		$cookie_sid		= FS_post('cookie_sid' , '' , 'string');
		$cookie_hsid	= FS_post('cookie_hsid' , '' , 'string');
		$cookie_ssid	= FS_post('cookie_ssid' , '' , 'string');
		$proxy			= FS_post('proxy' , '' , 'string');

		if( empty( $cookie_sid ) || empty( $cookie_hsid ) || empty( $cookie_ssid ) )
		{
			FSresponse(false, 'Please type your Cookies!');
		}

		require_once FS_LIB_DIR . "google/GoogleMyBusiness.php";

		$google = new GoogleMyBusiness($cookie_sid, $cookie_hsid, $cookie_ssid, $proxy);
		$data = $google->getUserInfo();

		if( empty( $data['id'] ) )
		{
			FSresponse(false, 'The entered cookies are wrong!');
		}

		$options = json_encode( [
			'sid'	=>	$cookie_sid,
			'hsid'	=>	$cookie_hsid,
			'ssid'	=>	$cookie_ssid,
		] );

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  isset($data['email']) ? $data['email'] : '',
			'password'          =>  '',
			'proxy'             =>  $proxy,
			'driver'            =>  'google_b',
			'name'              =>  isset($data['name']) ? $data['name'] : '',
			'profile_pic'       =>  isset($data['profile_image']) ? $data['profile_image'] : '',
			'options'			=>	$options
		];

		$checkIfExists = FSwpFetch('accounts' , [ 'driver' => 'google_b', 'user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			FSwpDB()->update(FSwpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
			$accountId = $checkIfExists['id'];
		}
		else
		{
			FSwpDB()->insert(FSwpTable('accounts') , $sqlData);
			$accountId = FSwpDB()->insert_id;
		}

		$locations = $google->getMyLocations();
		foreach ( $locations AS $location )
		{
			FSwpDB()->insert(FSwpTable('account_nodes') , [
				'user_id'		=>  get_current_user_id(),
				'account_id'	=>  $accountId,
				'node_type'		=>  'location',
				'node_id'		=>  $location['id'],
				'name'			=>  $location['name'],
				'category'		=>  $location['category'],
				'driver'		=>	'google_b'
			]);
		}

		FSresponse(true );
	}

	public function add_telegram_bot()
	{
		$bot_token	= FS_post('bot_token' , '' , 'string');
		$proxy		= FS_post('proxy' , '' , 'string');

		if( empty( $bot_token ) )
		{
			FSresponse(false, 'Please type your Bot Token!');
		}

		require_once FS_LIB_DIR . "telegram/Telegram.php";

		$tg = new Telegram( $bot_token, $proxy );
		$data = $tg->getBotInfo();

		if( empty( $data['id'] ) )
		{
			FSresponse(false, 'The entered Bot Token is invalid!');
		}

		$sqlData = [
			'user_id'           =>  get_current_user_id(),
			'profile_id'        =>  $data['id'],
			'username'          =>  $data['username'],
			'proxy'             =>  $proxy,
			'driver'            =>  'telegram',
			'name'              =>  $data['name'],
			'options'			=>	$bot_token
		];

		$checkIfExists = FSwpFetch('accounts' , [ 'driver' => 'telegram', 'user_id' => get_current_user_id() , 'profile_id' => $data['id']]);
		if( $checkIfExists )
		{
			FSwpDB()->update(FSwpTable('accounts') , $sqlData , ['id' => $checkIfExists['id']]);
		}
		else
		{
			FSwpDB()->insert(FSwpTable('accounts') , $sqlData);
		}

		FSresponse(true );
	}

	public function telegram_chat_save()
	{
		$account_id		= FS_post('account_id' , '' , 'int');
		$chat_id		= FS_post('chat_id' , '' , 'string');

		if( empty($account_id) || empty( $chat_id ) )
		{
			FSresponse( false );
		}

		$accountInf = FSwpFetch( 'accounts', ['id' => $account_id] );
		if( !$accountInf )
		{
			FSresponse(false);
		}

		require_once FS_LIB_DIR . "telegram/Telegram.php";

		$tg = new Telegram( $accountInf['options'] , $accountInf['proxy'] );
		$data = $tg->getChatInfo( $chat_id );

		if( empty($data['id']) )
		{
			FSresponse(false, 'Chat not found!');
		}

		FSwpDB()->insert(FSwpTable('account_nodes') , [
			'user_id'		=>  get_current_user_id(),
			'account_id'	=>  $account_id,
			'node_type'		=>  'chat',
			'node_id'		=>  $data['id'],
			'name'			=>  $data['name'],
			'screen_name'	=>  $data['username'],
			'category'		=>  $data['type'],
			'driver'		=>	'telegram'
		]);

		FSresponse( true, [
			'id'		=>	FSwpDB()->insert_id,
			'chat_pic'	=>	plugin_dir_url(__FILE__) . '../../images/telegram.svg',
			'chat_name'	=>	htmlspecialchars( $data['name'] ),
			'chat_link'	=>	FSprofileLink( [ 'driver' => 'telegram', 'username' => $data['username'] ] )
		] );
	}

	public function telegram_last_active_chats()
	{
		$account_id	= FS_post('account' , '' , 'int');

		if( !( is_numeric( $account_id ) && $account_id > 0 ) )
		{
			FSresponse(false);
		}

		$list = [];

		$accountInf = FSwpFetch( 'accounts', ['id' => $account_id] );
		if( !$accountInf )
		{
			FSresponse(false);
		}

		require_once FS_LIB_DIR . "telegram/Telegram.php";

		$tg = new Telegram( $accountInf['options'] , $accountInf['proxy'] );
		$data = $tg->getActiveChats( );

		FSresponse( true, [ 'list' => $data ] );
	}


}