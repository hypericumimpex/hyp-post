<?php

require_once FS_LIB_DIR . 'FSCurl.php';

class FacebookLib
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '/?fb_callback=1';
	}

	/**
	 * @param string $userName
	 * @param string $password
	 * @param string $apiKey
	 * @param string $apiSecret
	 * @return string
	 */
	public static function getLoginUrlWithAuth( $userName , $password , $apiKey , $apiSecret )
	{
		$data = array(
			'api_key' => $apiKey,
			'credentials_type' => 'password',
			'email' => $userName,
			'format' => 'JSON',
			'generate_machine_id' => '1',
			'generate_session_cookies' => '0',
			'locale' => 'en_US',
			'method' => 'auth.login',
			'password' => $password,
			'return_ssl_resources' => '0',
			'v' => '1.0'
		);

		$sig = '';

		foreach($data as $key => $value)
		{
			$sig .= $key . '=' . $value;
		}

		$sig .= $apiSecret;

		$data['sig'] = md5($sig);

		$url = 'https://api.facebook.com/restserver.php?' . http_build_query($data);

		return $url;
	}

	/**
	 * @param string $appId
	 * @param string $appSecret
	 * @return mixed
	 */
	public static function validateAppSecret( $appId , $appSecret )
	{
		$getInfo = json_decode( FSCurl::getContents( 'https://graph.facebook.com/'.$appId.'?fields=permissions{permission},roles,name,link,category&access_token='.$appId.'|'.$appSecret ) , true );

		return is_array($getInfo) && !isset($getInfo['error']) && isset($getInfo['name']) ? $getInfo : false;
	}

	/**
	 * @param string $accessToken
	 * @param string $proxy
	 * @return false|null|string
	 */
	public static function getAccessTokenExpiresDate( $accessToken , $proxy )
	{
		$url = 'https://graph.facebook.com/oauth/access_token_info?fields=id,category,company,name&access_token=' . $accessToken;

		$data = json_decode( FSCurl::getContents( $url , 'GET' , [] , [] , $proxy ) , true );

		return is_array($data) && isset($data['expires_in']) && $data['expires_in'] > 0 ? date('Y-m-d H:i:s' , time() + $data['expires_in']) : null;
	}

	/**
	 * @param string $accessTokenCode
	 * @return array
	 */
	public static function extractAccessToken( $accessTokenCode )
	{
		$res =  [
			"status" => false,
			"message" => "Invalid Access token",
			"access_token" => "",
		];

		preg_match('~access_token=(.*)(?=&expires_in)~' , $accessTokenCode ,$m);

		if(isset($m[1]))
		{
			$res['status'] = true;
			$res['message'] = "";
			$res['access_token'] = $m[1];

			return $res;
		}

		$r = json_decode($accessTokenCode,true);

		if( is_array( $r ) )
		{
			if( isset( $r['access_token'] ) )
			{
				$res['status'] = true;
				$res['message'] = "";
				$res['access_token'] = $r['access_token'];

				return $res;
			}

			if(isset($r['error_data']))
			{
				$rr = json_decode($r['error_data'],true);

				if( is_array( $rr ) )
				{
					if(isset($rr['error_message']))
					{
						$res['status'] = false;
						$res['message'] = $rr['error_message'];
						$res['access_token'] = "";

						return $res;
					}
				}
			}

			if(isset( $r['error_msg'] ))
			{
				$res['status'] = false;
				$res['message'] = $r['error_msg'];
				$res['access_token'] = "";
				return $res;
			}

		}

		preg_match('~"access_token":"(.*)(?=","machine_id)~',$accessTokenCode,$m);

		if( isset( $m[1] ) )
		{
			$res['status'] = true;
			$res['message'] = "";
			$res['access_token'] = $m[1];

			return $res;
		}

		if(trim($accessTokenCode) != "")
		{
			$res['status'] = true;
			$res['message'] = "";
			$res['access_token'] = $accessTokenCode;
		}

		return $res;
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function authorizeFbUser( $appId , $accessToken , $proxy)
	{
		$me = self::cmd('/me', 'GET' , $accessToken , ['fields' => 'id,name,email,birthday,gender'] , $proxy );

		if( isset($me['error']) )
		{
			FSresponse(false , isset($me['error']['message']) ? $me['error']['message'] : 'Error!');
		}

		if( !isset($me['id']) )
			$me['id'] = 0;

		if( !isset($me['name']) )
			$me['name'] = '?';

		if( !isset($me['email']) )
			$me['email'] = '?';

		if( !isset($me['birthday']) )
			$me['birthday'] = '?';

		$meId = isset($me['id']) ? $me['id'] : 0;

		$checkLoginRegistered = FSwpDB()->get_row( FSwpDB()->prepare( "SELECT * FROM ".FSwpTable('accounts')." WHERE user_id=%d AND driver='fb' AND profile_id=%s" , [get_current_user_id() ,$meId] ) , ARRAY_A );

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['name'],
			'driver'			=>	'fb',
			'profile_id'		=>	$meId,
			'email'				=>	$me['email'],
			'gender'			=>	isset($me['gender']) && $me['gender'] == 'male' ? '1' : '2',
			'birthday'			=>	date('Y-m-d' , strtotime(isset($me['birthday']) ? $me['birthday'] : '')),
			'proxy'             =>  $proxy
		];

		if( !$checkLoginRegistered )
		{
			FSwpDB()->insert(FSwpTable('accounts') , $dataSQL);

			$fbAccId = FSwpDB()->insert_id;
		}
		else
		{
			$fbAccId = $checkLoginRegistered['id'];

			FSwpDB()->update(FSwpTable('accounts') , $dataSQL , ['id' => $fbAccId]);

			FSwpDB()->delete( FSwpTable('account_access_tokens')  , ['account_id' => $fbAccId , 'app_id' => $appId] );

			FSwpDB()->delete( FSwpTable('account_nodes')  , ['account_id' => $fbAccId] );
		}

		$expiresOn = self::getAccessTokenExpiresDate( $accessToken , $proxy );

		// acccess token
		FSwpDB()->insert( FSwpTable('account_access_tokens') ,  [
			'account_id'	=>	$fbAccId,
			'app_id'		=>	$appId,
			'expires_on'	=>	$expiresOn,
			'access_token'	=>	$accessToken
		]);

		$returnStatisticsData = [
			'ownpages'  => false,
			'pages'	 => false,
			'groups'	=> false
		];

		// my pages load
		$loadedOwnPages = [];
		if( get_option('fs_load_own_pages' , 1) == 1 )
		{
			$accountsList = self::cmd('/me/accounts', 'GET' , $accessToken , ['fields' => 'access_token,category,name,id,likes', 'limit' => 100] , $proxy );

			if( isset( $accountsList['error']['code'] ) && $accountsList['error']['code'] == '4' && isset( $accountsList['error']['error_subcode'] ) && $accountsList['error']['error_subcode'] == '1349193' )
			{
				$accountsList = self::cmd('/me/accounts', 'GET' , $accessToken , ['fields' => 'access_token,category,name,id,likes', 'limit' => '3'] , $proxy );
			}

			if( isset($accountsList['data']) && is_array($accountsList['data']) )
			{
				$returnStatisticsData['ownpages'] = [];
				foreach($accountsList['data'] AS $accountInfo)
				{
					$returnStatisticsData['ownpages'][] = [$accountInfo['id'] , $accountInfo['name']];

					FSwpDB()->insert(FSwpTable('account_nodes') , [
						'user_id'			=>	get_current_user_id(),
						'driver'			=>	'fb',
						'account_id'		=>	$fbAccId,
						'node_type'			=>	'ownpage',
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						'access_token'		=>	$accountInfo['access_token'],
						'category'			=>	$accountInfo['category'],
						'fan_count'			=>	isset($accountInfo['likes']) ? $accountInfo['likes'] : 0
					]);
					$loadedOwnPages[ $accountInfo['id'] ] = true;
				}
			}
		}

		// pages load
		if( get_option('fs_load_liked_pages' , 0) == 1 )
		{
			$limit = get_option('fs_max_liked_pages_limit' , 100);
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = self::cmd('/me/likes', 'GET' , $accessToken , [
				'fields' => 'category,name,id,likes' ,
				'limit' => $limit
			] , $proxy);
			if( isset($accountsList['data']) && is_array($accountsList['data']) )
			{
				$returnStatisticsData['pages'] = [];
				foreach($accountsList['data'] AS $accountInfo)
				{
					if( isset( $loadedOwnPages[$accountInfo['id']] ) )
					{
						continue;
					}

					$returnStatisticsData['pages'][] = [$accountInfo['id'] , $accountInfo['name']];

					FSwpDB()->insert(FSwpTable('account_nodes') , [
						'user_id'			=>	get_current_user_id(),
						'driver'			=>	'fb',
						'account_id'		=>	$fbAccId,
						'node_type'			=>	'page',
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						'access_token'		=>	null,
						'category'			=>	$accountInfo['category'],
						'fan_count'			=>	$accountInfo['likes']
					]);
				}
			}
		}

		// groups load
		if( get_option('fs_load_groups' , 1) == 1 )
		{
			$limit = get_option('fs_max_groups_limit' , 100);
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = self::cmd('/me/groups' , 'GET' , $accessToken , [
				'fields'	=>	'name,privacy,id,icon,cover{source},administrator',
				'limit'		=>	$limit
			] , $proxy);
			if( isset($accountsList['data']) && is_array($accountsList['data']) )
			{
				$returnStatisticsData['groups'] = [];
				foreach($accountsList['data'] AS $accountInfo)
				{
					if( $appId > 0 && !$accountInfo['administrator'] )
						continue;

					$cover = '';
					if( isset($accountInfo['cover']['source']) )
					{
						$cover = $accountInfo['cover']['source'];
					}
					else if( isset( $accountInfo['icon'] ) )
					{
						$cover = $accountInfo['icon'];
					}
					$returnStatisticsData['groups'][] = [$accountInfo['id'] , $accountInfo['name'] , $cover];

					FSwpDB()->insert(FSwpTable('account_nodes') , [
						'user_id'			=>	get_current_user_id(),
						'driver'			=>	'fb',
						'account_id'		=>	$fbAccId,
						'node_type'			=>	'group',
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						//'access_token'		=>	null,
						'category'			=>	isset($accountInfo['privacy']) ? $accountInfo['privacy'] : 'group',
						'cover'				=>	$cover
					]);
				}
			}
		}

		return [
			'name'	  => $me['name'],
			'email'	 => $me['email'],
			'birthday'  => date('d M Y' , strtotime($me['birthday'])),
			'id'		=> $me['id'],
			'nodes'	 => $returnStatisticsData
		];
	}

	/**
	 * @param string $cmd
	 * @param string $method
	 * @param string $accessToken
	 * @param array $data
	 * @param string $proxy
	 * @return array|mixed|object
	 */
	public static function cmd( $cmd , $method , $accessToken , array $data = [] , $proxy = '' )
	{
		$data['access_token'] = $accessToken;

		$url = 'https://graph.facebook.com/' . $cmd; //. '?' . http_build_query( $data );

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = FSCurl::getContents( $url , $method , $data , [] , $proxy , true );
		$data = json_decode( $data1 , true );

		if( !is_array($data) )
		{
			$data = [
				'error' =>  ['message' => 'Error data! (' . $data1 . ')']
			];
		}

		return $data;
	}

	/**
	 * @param string $nodeFbId
	 * @param string $type
	 * @param string $message
	 * @param string $preset_id
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $nodeFbId , $type , $message , $preset_id , $link , $images , $video , $accessToken , $proxy )
	{
		$sendData = [
			'message'	=>	$message
		];

		if( $preset_id > 0 && $type == 'status' )
		{
			$sendData['text_format_preset_id'] = $preset_id;
		}
		else if( $type == 'link' )
		{
			$sendData['link'] = $link;
		}

		$endPoint = 'feed';

		if( $type == 'image' )
		{
			$sendData['attached_media'] = [];

			$images = is_array($images) ? $images : [$images];
			foreach($images AS $imageURL)
			{
				$sendData2 = [
					'url' 		=>	$imageURL,
					'published'	=>	'false',
					'caption'	=>	''
				];

				$imageUpload = self::cmd('/' . $nodeFbId . '/photos' , 'POST' , $accessToken , $sendData2 , $proxy);

				if( isset($imageUpload['id']) )
				{
					$sendData['attached_media'][] = json_encode([ 'media_fbid' => $imageUpload['id'] ]);
				}
			}

		}

		if( $type == 'video' )
		{
			$endPoint = 'videos';
			$sendData['file_url']		= $video;
			$sendData['description']	= $message;
		}

		$result = self::cmd('/' . $nodeFbId . '/' . $endPoint , 'POST' , $accessToken , $sendData , $proxy);

		if( isset($result['error']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	isset($result['error']['message']) ? $result['error']['message'] : 'Error!'
			];
		}
		else
		{
			if(isset($result['id']))
			{
				$stsId = explode('_' , $result['id']);
				$stsId = end($stsId);
			}
			else
			{
				$stsId = 0;
			}

			$result2 = [
				'status'	=>  'ok',
				'id'		=>	$stsId
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
		$_SESSION['fs_proxy_save'] = FS_get('proxy' , '' , 'string');

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'fb']);
		$appId = $appInf['app_id'];

		$permissions = [ 'public_profile', 'email', 'manage_pages' , 'publish_pages' , 'publish_to_groups' ];
		$permissions = implode(',' , array_map('urlencode' , $permissions));

		$callbackUrl = self::callbackUrl();

		return "https://www.facebook.com/v3.3/dialog/oauth?redirect_uri={$callbackUrl}&scope={$permissions}&response_type=code&client_id={$appId}";
	}

	/**
	 * @return bool
	 */
	public static function getAccessToken( )
	{
		do_action('FSregisterSession');
		if( !isset($_SESSION['save_app_id']) )
		{
			return false;
		}

		$code = FS_get('code' , '' , 'string');

		if( empty($code) )
		{
			if( isset($_GET['error_message']) && is_string($_GET['error_message']) )
			{
				$errorMsg = esc_html($_GET['error_message']);
				print 'Error... <script>if(typeof window.opener.setAccessToken == "function"){window.opener.fsCode.loading(0);window.opener.fsCode.toast("'.esc_html($errorMsg).'" , "danger" , 30000);window.close();}else{document.write("Error! Please try again!");} </script>';
				exit;
			}
			return false;
		}

		$appId = (int)$_SESSION['save_app_id'];
		unset($_SESSION['save_app_id']);
		$proxy = '';
		if( isset($_SESSION['fs_proxy_save']) )
		{
			$proxy = is_string($_SESSION['fs_proxy_save']) ? $_SESSION['fs_proxy_save'] : '';

			unset($_SESSION['fs_proxy_save']);
		}
		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'fb']);
		$appSecret = $appInf['app_key'];
		$appId = $appInf['app_id'];

		$token_url = "https://graph.facebook.com/oauth/access_token?"
			. "client_id=" . $appId . "&redirect_uri=" . urlencode(self::callbackUrl())
			. "&client_secret=" . $appSecret . "&code=" . $code;

		$response = FSCurl::getURL($token_url , $proxy);

		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			print 'Error... <script>if(typeof window.opener.setAccessToken == "function"){window.opener.fsCode.loading(0);window.opener.fsCode.toast("'.$token_url .esc_html($params['error']['message']).'" , "danger" , 30000);window.close();}else{document.write("Error! Please try again!");} </script>';
			exit();
		}

		$access_token = esc_html($params['access_token']);

		if( is_string($access_token) && !empty($access_token) )
		{
			print 'Loading... <script>if(typeof window.opener.setAccessToken == "function"){window.opener.setAccessToken("'.$access_token.'");window.close();}else{document.write("Error! Please try again!");} </script>';
		}
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
		$insights = FacebookLib::cmd('/' . $postId , 'GET' , $accessToken , [
			'fields'	=>	'reactions.type(LIKE).limit(0).summary(total_count).as(like),reactions.type(LOVE).summary(total_count).limit(0).as(love),reactions.type(WOW).summary(total_count).limit(0).as(wow),reactions.type(HAHA).summary(total_count).limit(0).as(haha),reactions.type(SAD).summary(total_count).limit(0).as(sad),reactions.type(ANGRY).summary(total_count).limit(0).as(angry),comments.limit(0).summary(true),sharedposts.limit(5000).summary(true)'
		] , $proxy);

		$insights = [
			'like'		=>	isset($insights['like']['summary']['total_count']) ? $insights['like']['summary']['total_count'] : 0,
			'love'		=>	isset($insights['love']['summary']['total_count']) ? $insights['love']['summary']['total_count'] : 0,
			'wow'		=>	isset($insights['wow']['summary']['total_count'] ) ? $insights['wow']['summary']['total_count']  :0,
			'haha'		=>	isset($insights['haha']['summary']['total_count']) ? $insights['haha']['summary']['total_count'] : 0,
			'sad'		=>	isset($insights['sad']['summary']['total_count'] ) ? $insights['sad']['summary']['total_count']  :0,
			'angry'		=>	isset($insights['angry']['summary']['total_count']) ? $insights['angry']['summary']['total_count'] : 0
		];

		$details = 'Like: ' . $insights['like'] . "\n";
		$details .= 'Love: ' . $insights['love'] . "\n";
		$details .= 'Wow: ' . $insights['wow'] . "\n";
		$details .= 'Haha: ' . $insights['haha'] . "\n";
		$details .= 'Sad: ' . $insights['sad'] . "\n";
		$details .= 'Angry: ' . $insights['angry'];

		$likesSum = $insights['like'] + $insights['love'] + $insights['wow'] + $insights['haha'] + $insights['sad'] + $insights['angry'];

		return [
			'like'      =>  $likesSum,
			'comments'  =>  isset($insights['comments']['count']) ? $insights['comments']['count'] : 0,
			'shares'    =>  isset($insights['sharedposts']['count']) ? $insights['sharedposts']['count'] : 0,
			'details'   =>  $details
		];
	}

}