<?php

require_once __DIR__ . '/../FSCurl.php';

class Reddit
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '/?reddit_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $refreshToken
	 * @param string $expiresIn
	 * @param string $proxy
	 */
	public static function authorizeRedditUser( $appId , $accessToken , $refreshToken, $expiresIn , $proxy )
	{
		$me = self::cmd('https://oauth.reddit.com/api/v1/me', 'GET' , $accessToken , [] , $proxy );

		if( isset($me['error']) && isset($me['error']['message']) )
		{
			response(false , $me['error']['message'] );
		}

		$meId = $me['id'];

		$checkLoginRegistered = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'reddit', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['subreddit']['title'],
			'driver'			=>	'reddit',
			'profile_id'		=>	$meId,
			'profile_pic'		=>	$me['icon_img'],
			'username'			=>	$me['name'],
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
		}

		// acccess token
		wpDB()->insert( wpTable('account_access_tokens') ,  [
			'account_id'	=>	$accId,
			'app_id'		=>	$appId,
			'access_token'	=>	$accessToken,
			'refresh_token'	=>	$refreshToken,
			'expires_on'    =>  $expiresIn
		]);
	}

	/**
	 * @param string $cmd
	 * @param string $method
	 * @param string $accessToken
	 * @param array $data
	 * @param string $proxy
	 * @return mixed
	 */
	public static function cmd( $cmd , $method , $accessToken , array $data = [] , $proxy = '' )
	{
		$url = $cmd;

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = FSCurl::getContents( $url , $method , $data, ['Authorization' => 'bearer ' . $accessToken] , $proxy );
		$data = json_decode( $data1 , true );

		if( !is_array($data) )
		{
			$data = [
				'error' =>  ['message' => 'Error data!']
			];
		}

		return $data;
	}

	/**
	 * @param array $accountInfo
	 * @param string $type
	 * @param string $title
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $accountInfo , $type , $title , $message , $link , $images , $video , $accessToken , $proxy )
	{
		$options = json_decode($accountInfo['options'] , true);

		$subReddit = !empty($options['subreddit']) ? $options['subreddit'] : 'u_' . $accountInfo['username'];

		$sendData = [
			'sr'            =>  $subReddit,
			'title'         =>  $title,
			'resubmit'      =>  'true',
			'send_replies'  =>  'true',
			'api_type'      =>  'json'
		];

		if( $type == 'image' )
		{
			$sendData['kind'] = 'image';
			$sendData['url'] = reset($images);
		}
		else if($type == 'video')
		{
			$sendData['kind'] = 'video';
			$sendData['url'] = $video;
		}
		else if( $type == 'link' )
		{
			$sendData['kind'] = 'link';
			$sendData['url'] = $link;
		}
		else
		{
			$sendData['kind'] = 'self';
			$sendData['text'] = $message;
		}

		$result = self::cmd('https://oauth.reddit.com/api/submit' , 'POST' , $accessToken , $sendData , $proxy);

		if( isset($result['error']) && isset( $result['error']['message'] ) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$result['error']['message']
			];
		}
		else if( isset($result['json']['errors']) && is_array($result['json']['errors']) && !empty($result['json']['errors']) )
		{
			$error = reset($result['json']['errors']);
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$error[1]
			];
		}
		else
		{
			$result2 = [
				'status'	=>  'ok',
				'id'		=>	$result['json']['data']['id']
			];
		}

		return $result2;
	}

	/**
	 * @param integer $appId
	 * @return string
	 */
	public static function getLoginURL($appId)
	{
		do_action('registerSession');
		$_SESSION['save_app_id'] = $appId;
		$_SESSION['_state'] = md5(rand(111111111, 911111111));
		$_SESSION['fs_proxy_save'] = _get('proxy' , '' , 'string');

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'reddit']);
		if( !$appInf )
		{
			print 'Error! App not found!';
			exit();
		}
		$appId = urlencode($appInf['app_id']);

		$callbackUrl = urlencode(self::callbackUrl());

		return "https://www.reddit.com/api/v1/authorize?client_id={$appId}&response_type=code&redirect_uri={$callbackUrl}&duration=permanent&scope=identity,submit&state=" . $_SESSION['_state'];
	}

	/**
	 * @return bool
	 */
	public static function getAccessToken( )
	{
		do_action('registerSession');
		if( !isset($_SESSION['save_app_id']) || !isset($_SESSION['_state']) )
		{
			return false;
		}

		$code = _get('code' , '' , 'string');
		$state = _get('state' , '' , 'string');

		if( empty($code) || $state != $_SESSION['_state']  )
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

		unset($_SESSION['save_app_id']);
		unset($_SESSION['_state']);

		$proxy = '';
		if( isset($_SESSION['fs_proxy_save']) )
		{
			$proxy = is_string($_SESSION['fs_proxy_save']) ? $_SESSION['fs_proxy_save'] : '';

			unset($_SESSION['fs_proxy_save']);
		}

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'reddit']);
		$appSecret = urlencode($appInf['app_secret']);
		$appId2 = urlencode($appInf['app_id']);

		$url = 'https://www.reddit.com/api/v1/access_token';

		$postData = [
			'grant_type'    => 'authorization_code',
			'code'          => $code,
			'redirect_uri'  => self::callbackURL(),
		];


		$headers = [ 'Authorization' => 'Basic '. base64_encode("{$appId2}:{$appSecret}") ];

		$response = FSCurl::getContents($url , 'POST' , $postData , $headers , $proxy);

		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			print $params['error']['message'];
			exit();
		}

		$access_token = esc_html($params['access_token']);
		$refreshToken = esc_html($params['refresh_token']);
		$expiresIn = date('Y-m-d H:i:s' , time() + (int)$params['expires_in']);

		self::authorizeRedditUser( $appId , $access_token , $refreshToken , $expiresIn , $proxy );

		print 'Loading... <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("Error! Please try again!");} </script>';
		exit;
	}

	/**
	 * @param array $tokenInfo
	 * @return string
	 */
	public static function refreshToken($tokenInfo)
	{
		$appId = $tokenInfo['app_id'];

		$accountInf = wpFetch('accounts' , $tokenInfo['account_id']);
		$proxy = $accountInf['proxy'];

		$appInf = wpFetch('apps' , $appId);
		$appId2 = urlencode($appInf['app_id']);
		$appSecret = urlencode($appInf['app_secret']);

		$url = 'https://www.reddit.com/api/v1/access_token';

		$postData = [
			'grant_type'    => 'refresh_token',
			'refresh_token' => $tokenInfo['refresh_token']
		];

		$headers = [ 'Authorization' => 'Basic '. base64_encode("{$appId2}:{$appSecret}") ];
		$response = FSCurl::getContents($url , 'POST' , $postData , $headers , $proxy);
		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			return false;
		}

		$access_token = esc_html($params['access_token']);
		$expiresIn = date('Y-m-d H:i:s' , time() + (int)$params['expires_in']);

		wpDB()->update(wpTable('account_access_tokens') , [
			'access_token'  =>  $access_token,
			'expires_on'    =>  $expiresIn
		] , ['id' => $tokenInfo['id']]);

		$tokenInfo['access_token'] = $access_token;
		$tokenInfo['expires_on'] = $expiresIn;

		return $access_token;
	}

	/**
	 * @param integer $postId
	 * @param string $accessToken
	 * @return array
	 */
	public static function getStats($postId , $accessToken , $proxy)
	{
		return [
			'comments'      =>  0,
			'like'          =>  0,
			'shares'        =>  0,
			'details'       =>  ''
		];
	}

}