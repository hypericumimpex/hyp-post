<?php

class Tumblr
{
	private static $apps = [];

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '/?tumblr_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $consumerKey
	 * @param string $consumerSecret
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param string $proxy
	 */
	public static function authorizeTumblrUser( $appId , $consumerKey, $consumerSecret , $accessToken , $accessTokenSecret , $proxy )
	{
		$client = new Tumblr\API\Client(
			$consumerKey,
			$consumerSecret,
			$accessToken,
			$accessTokenSecret,
			$proxy
		);

		try
		{
			$me = $client->getUserInfo();
		}
		catch (Exception $e)
		{
			response(false , esc_html($e->getMessage()));
		}

		$username = $me->user->name;

		$checkLoginRegistered = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'tumblr', 'username' => $username]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$username,
			'driver'			=>	'tumblr',
			'username'			=>	$username,
			'proxy'             =>  $proxy
		];

		if( !$checkLoginRegistered )
		{
			wpDB()->insert(wpTable('accounts') , $dataSQL);

			$accId = wpDB()->insert_id;
		}
		else
		{
			$accId = $checkLoginRegistered['id'];

			wpDB()->update(wpTable('accounts') , $dataSQL , ['id' => $accId]);

			wpDB()->delete( wpTable('account_access_tokens')  , ['account_id' => $accId , 'app_id' => $appId] );

			wpDB()->delete( wpTable('account_nodes')  , ['account_id' => $accId] );
		}

		// acccess token
		wpDB()->insert( wpTable('account_access_tokens') ,  [
			'account_id'	        =>	$accId,
			'app_id'		        =>	$appId,
			'access_token'	        =>	$accessToken,
			'access_token_secret'	=>	$accessTokenSecret
		]);

		foreach($me->user->blogs AS $blogInf)
		{
			wpDB()->insert(wpTable('account_nodes') , [
				'user_id'			=>	get_current_user_id(),
				'driver'			=>	'tumblr',
				'screen_name'		=>	$blogInf->name,
				'account_id'		=>	$accId,
				'node_type'			=>	'blog',
				'node_id'			=>	$blogInf->name,
				'name'				=>	$blogInf->name,
				'access_token'		=>	null,
				'category'			=>	$blogInf->primary ? 'primary' : 'not-primary',
				'fan_count'			=>	$blogInf->followers
			]);
		}
	}

	/**
	 * @param integer $appId
	 * @return mixed
	 */
	private static function getAppInf( $appId )
	{
		if( !isset(self::$apps[$appId]) )
		{
			self::$apps[$appId] = wpFetch('apps' , $appId);
		}

		return self::$apps[$appId];
	}

	/**
	 * @param array $blogInfo
	 * @param string $type
	 * @param string $title
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param integer $appId
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $blogInfo , $type , $title , $message , $link , $images , $video , $accessToken , $accessTokenSecret , $appId , $proxy )
	{
		require_once LIB_DIR . 'vendor/autoload.php';

		$appInf = self::getAppInf($appId);

		$sendData = [];

		$client = new Tumblr\API\Client(
			$appInf['app_key'],
			$appInf['app_secret'],
			$accessToken,
			$accessTokenSecret,
			$proxy
		);

		if( $type == 'image' )
		{
			$sendData['type'] = 'photo';
			if( !empty($link) )
			{
				$sendData['link'] = $link;
			}
			$sendData['data'] = $images;
			$sendData['caption'] = $message;
		}
		else if($type == 'video')
		{
			$sendData['type'] = 'video';
			$sendData['data'] = $video;
			$sendData['caption'] = $message;
		}
		else
		{
			$sendData['type'] = 'link';
			$sendData['title'] = $title;
			$sendData['url'] = $link;

			if( !empty($images) )
			{
				$sendData['description'] = '<img src="' . htmlspecialchars(reset($images)) . '"><br>';
				$sendData['description'] .= $message;
			}
			else
			{
				$sendData['description'] = $message;
			}
		}

		try
		{
			$result = $client->createPost( $blogInfo['node_id'] , $sendData );
		}
		catch (Exception $e)
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	esc_html($e->getMessage())
			];
		}

		return [
			'status'	=>  'ok',
			'id'		=>	$type == 'video' ? ''/*????????????*/ : $result->id
		];
	}

	/**
	 * @param integer $appId
	 * @return string
	 */
	public static function getLoginURL($appId)
	{
		do_action('registerSession');
		$_SESSION['save_app_id'] = $appId;
		$_SESSION['fs_proxy_save'] = _get('proxy' , '' , 'string');

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'tumblr']);
		if( !$appInf )
		{
			print 'Error! App not found!';
			exit();
		}
		$consumerKey = urlencode($appInf['app_key']);
		$consumerSecret = urlencode($appInf['app_secret']);

		$callbackUrl = self::callbackUrl();

		require_once LIB_DIR . 'vendor/autoload.php';

		$client = new Tumblr\API\Client($consumerKey, $consumerSecret , null, null , $_SESSION['fs_proxy_save']);

		$requestHandler = $client->getRequestHandler();
		$requestHandler->setBaseUrl('https://www.tumblr.com/');

		try
		{
			$resp = $requestHandler->request('POST', 'oauth/request_token', array(
				'oauth_callback' => $callbackUrl
			));
		}
		catch (Exception $e)
		{
			print $e->getMessage();
			exit();
		}

		$result = (string)$resp->body;
		parse_str($result, $keys);

		$_SESSION['tmp_oauth_token'] = $keys['oauth_token'];
		$_SESSION['tmp_oauth_token_secret'] = $keys['oauth_token_secret'];

		$url = 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $keys['oauth_token'];

		return $url;
	}

	/**
	 * @return bool
	 */
	public static function getAccessToken( )
	{
		do_action('registerSession');
		if( !isset($_SESSION['save_app_id']) || !isset($_SESSION['tmp_oauth_token']) || !isset($_SESSION['tmp_oauth_token_secret']) )
		{
			return false;
		}

		$code = _get('oauth_verifier' , '' , 'string');

		if( empty($code) )
		{
			if( isset($_GET['error_message']) && is_string($_GET['error_message']) )
			{
				$errorMsg = esc_html($_GET['error_message']);
				print 'Loading... <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(false , "'.$errorMsg.'");window.close();}else{document.write("This account already has been added!");} </script>';
				exit;
			}
			return false;
		}

		$appId = (int)$_SESSION['save_app_id'];

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'tumblr']);
		$consumerKey = urlencode($appInf['app_key']);
		$consumerSecret = urlencode($appInf['app_secret']);

		$proxy = '';
		if( isset($_SESSION['fs_proxy_save']) )
		{
			$proxy = is_string($_SESSION['fs_proxy_save']) ? $_SESSION['fs_proxy_save'] : '';

			unset($_SESSION['fs_proxy_save']);
		}

		require_once LIB_DIR . 'vendor/autoload.php';

		$client = new Tumblr\API\Client($consumerKey, $consumerSecret , $_SESSION['tmp_oauth_token'] , $_SESSION['tmp_oauth_token_secret'] , $proxy);

		$requestHandler = $client->getRequestHandler();
		$requestHandler->setBaseUrl('https://www.tumblr.com/');

		unset($_SESSION['save_app_id']);
		unset($_SESSION['tmp_oauth_token']);
		unset($_SESSION['tmp_oauth_token_secret']);

		try
		{
			$resp = $requestHandler->request('POST', 'oauth/access_token', array('oauth_verifier' => $code));
		}
		catch (Exception $e)
		{
			print $e->getMessage();
			exit();
		}

		$out = (string)$resp->body;
		$data = array();
		parse_str($out, $data);

		$access_token = $data['oauth_token'];
		$access_token_secret = $data['oauth_token_secret'];

		self::authorizeTumblrUser( $appId , $consumerKey, $consumerSecret , $access_token , $access_token_secret , $proxy );

		print 'Loading... <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("Error! Please try again!");} </script>';
		exit;
	}

	/**
	 * @param integer $postId
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function getStats($postId , $accessToken , $proxy)
	{
		return [
			'comments'      =>  0,
			'like'          =>  0,
			'shares'        =>  0,
			'details'       =>  0
		];
	}

}