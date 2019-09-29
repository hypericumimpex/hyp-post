<?php

require_once __DIR__ . '/../FSCurl.php';

class Medium
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '/?medium_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $refreshToken
	 * @param string $expiresIn
	 * @param string $proxy
	 */
	public static function authorizeMediumUser( $appId , $accessToken , $refreshToken, $expiresIn , $proxy )
	{
		$me = self::cmd('https://api.medium.com/v1/me', 'GET' , $accessToken , [] , $proxy );

		if( isset($me['error']) && isset($me['error']['message']) )
		{
			FSresponse(false , $me['error']['message'] );
		}

		$me = $me['data'];

		$meId = $me['id'];

		$checkLoginRegistered = FSwpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'medium', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['name'],
			'driver'			=>	'medium',
			'profile_id'		=>	$meId,
			'profile_pic'		=>	$me['imageUrl'],
			'username'			=>	$me['username'],
			'proxy'             =>  $proxy
		];

		if( !$checkLoginRegistered )
		{
			FSwpDB()->insert(FSwpTable('accounts') , $dataSQL);

			$accId = FSwpDB()->insert_id;
		}
		else
		{
			$accId = $checkLoginRegistered['id'];

			FSwpDB()->update(FSwpTable('accounts') , $dataSQL , ['id' => $accId]);

			FSwpDB()->delete( FSwpTable('account_access_tokens')  , ['account_id' => $accId , 'app_id' => $appId] );
		}

		// acccess token
		FSwpDB()->insert( FSwpTable('account_access_tokens') ,  [
			'account_id'	=>	$accId,
			'app_id'		=>	$appId,
			'access_token'	=>	$accessToken,
			'refresh_token'	=>	$refreshToken,
			'expires_on'    =>  $expiresIn
		]);


		$publications = self::cmd('https://api.medium.com/v1/users/' . $meId . '/publications', 'GET' , $accessToken , [ ] , $proxy);
		if( isset($publications['data']) && is_array($publications['data']) )
		{
			foreach($publications['data'] AS $publicationInf)
			{
				$loadedOwnPages[$publicationInf['id']] = true;

				FSwpDB()->insert(FSwpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'medium',
					'screen_name'		=>	str_replace('https://medium.com/', '', $publicationInf['url']),
					'account_id'		=>	$accId,
					'node_type'			=>	'publication',
					'node_id'			=>	$publicationInf['id'],
					'name'				=>	$publicationInf['name'],
					'cover'				=>	$publicationInf['imageUrl']
				]);
			}
		}

	}

	/**
	 * @param string $cmd
	 * @param string $method
	 * @param string $accessToken
	 * @param array $data
	 * @param string $proxy
	 * @return mixed
	 */
	public static function cmd( $cmd , $method , $accessToken ,  $data = [] , $proxy = '' )
	{
		$url = $cmd;

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = FSCurl::getContents( $url , $method , $data, [
			'Authorization' 	=>	'Bearer ' . $accessToken,
			'Content-Type'		=>	'application/json',
			'Accept'			=>	'application/json',
			'Accept-Charset'	=>	'utf-8'
		] , $proxy, true );
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
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $accountInfo , $title , $message , $accessToken , $proxy )
	{
		$sendData = [
			'title'         =>  $title,
			'contentFormat'	=>	'html',
			'content'		=>	$message
		];

		if( isset($accountInfo['screen_name']) )
		{
			$endpoint = 'https://api.medium.com/v1/publications/' . $accountInfo['id'] . '/posts';
		}
		else
		{
			$endpoint = 'https://api.medium.com/v1/users/' . $accountInfo['id'] . '/posts';
		}

		$result = self::cmd( $endpoint , 'POST' , $accessToken , $sendData , $proxy);

		if( isset($result['error']) && isset( $result['error']['message'] ) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$result['error']['message']
			];
		}
		else if( isset($result['errors']) && is_array($result['errors']) && !empty($result['errors']) )
		{
			$error = reset($result['errors']);
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$error[1]
			];
		}
		else
		{
			$result2 = [
				'status'	=>  'ok',
				'id'		=>	isset($result['data']['id']) ? $result['data']['id'] : 0
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
		do_action('FSregisterSession');
		$_SESSION['save_app_id'] = $appId;
		$_SESSION['_state'] = md5(rand(111111111, 911111111));
		$_SESSION['fs_proxy_save'] = FS_get('proxy' , '' , 'string');

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'medium']);
		if( !$appInf )
		{
			print 'Error! App not found!';
			exit();
		}
		$appId = urlencode($appInf['app_id']);

		$callbackUrl = urlencode(self::callbackUrl());

		return "https://medium.com/m/oauth/authorize?client_id={$appId}&response_type=code&redirect_uri={$callbackUrl}&scope=basicProfile,listPublications,publishPost&state=" . $_SESSION['_state'];
	}

	/**
	 * @return bool
	 */
	public static function getAccessToken( )
	{
		do_action('FSregisterSession');
		if( !isset($_SESSION['save_app_id']) || !isset($_SESSION['_state']) )
		{
			return false;
		}

		$code = FS_get('code' , '' , 'string');
		$state = FS_get('state' , '' , 'string');

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

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'medium']);
		$appSecret = urlencode($appInf['app_secret']);
		$appId2 = urlencode($appInf['app_id']);

		$url = 'https://api.medium.com/v1/tokens';

		$postData = [
			'grant_type'    =>	'authorization_code',
			'code'          =>	$code,
			'client_id'		=>	$appId2,
			'client_secret'	=>	$appSecret,
			'redirect_uri'  =>	self::callbackURL(),
		];

		$headers = [
			'Accept'			=> 'application/json',
			'Accept-Charset'	=> 'utf-8'
		];

		$response = FSCurl::getContents($url , 'POST' , $postData , $headers , $proxy);

		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			print $params['error']['message'];
			exit();
		}

		$access_token = esc_html($params['access_token']);
		$refreshToken = esc_html($params['refresh_token']);
		$expiresIn = date('Y-m-d H:i:s' , time() + (int)$params['expires_at']);

		self::authorizeMediumUser( $appId , $access_token , $refreshToken , $expiresIn , $proxy );

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

		$accountInf = FSwpFetch('accounts' , $tokenInfo['account_id']);
		$proxy = $accountInf['proxy'];

		$appInf = FSwpFetch('apps' , $appId);
		$appId2 = urlencode($appInf['app_id']);
		$appSecret = urlencode($appInf['app_secret']);

		$url = 'https://api.medium.com/v1/tokens';

		$postData = [
			'grant_type'    =>	'refresh_token',
			'client_id'		=>	$appId2,
			'client_secret'	=>	$appSecret,
			'refresh_token' =>	$tokenInfo['refresh_token']
		];

		$headers = [
			'Accept'			=> 'application/json',
			'Accept-Charset'	=> 'utf-8'
		];
		$response = FSCurl::getContents($url , 'POST' , $postData , $headers , $proxy);
		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			return false;
		}

		$access_token = esc_html($params['access_token']);
		$expiresIn = date('Y-m-d H:i:s' , time() + (int)$params['expires_at']);

		FSwpDB()->update(FSwpTable('account_access_tokens') , [
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