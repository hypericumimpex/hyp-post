<?php

require_once LIB_DIR . 'FSCurl.php';

class OdnoKlassniki
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '?ok_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $scExpireIn
	 * @param string $proxy
	 */
	public static function authorizeOkUser( $appId , $appPublicKey , $appPrivateKey , $accessToken , $refreshToken , $scExpireIn , $proxy)
	{
		$me = self::cmd('users.getCurrentUser', 'GET' , $accessToken , $appPublicKey , $appPrivateKey , []  , $proxy );

		if( isset($me['error_msg']) )
		{
			response(false , $me['error_msg'] );
		}

		$meId = $me['uid'];

		$checkLoginRegistered = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'ok', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['name'],
			'driver'			=>	'ok',
			'profile_id'		=>	$meId,
			'profile_pic'		=>	$me['pic_1'],
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
			'account_id'	=>	$accId,
			'app_id'		=>	$appId,
			'expires_on'	=>	$scExpireIn,
			'access_token'	=>	$accessToken,
			'refresh_token'	=>	$refreshToken
		]);

		// fetch groups list
		$groupIDsList = self::cmd('group.getUserGroupsV2', 'GET' , $accessToken , $appPublicKey , $appPrivateKey , [] , $proxy );
		$idsArr = [];

		foreach ($groupIDsList['groups'] AS $groupIdInf)
		{
			$idsArr[] = $groupIdInf['groupId'];
		}

		$idsArr = implode(',', $idsArr);

		$groupsList = self::cmd('group.getInfo', 'GET' , $accessToken , $appPublicKey , $appPrivateKey , ['uids' => $idsArr, 'fields' => 'pic_avatar,uid,name'] , $proxy );

		foreach($groupsList AS $groupInf)
		{
			wpDB()->insert(wpTable('account_nodes') , [
				'user_id'			=>	get_current_user_id(),
				'driver'			=>	'ok',
				'account_id'		=>	$accId,
				'node_type'			=>	'group',
				'node_id'			=>	$groupInf['uid'],
				'name'				=>	$groupInf['name'],
				'cover'				=>	$groupInf['picAvatar'],
				'category'			=>	'group'
			]);
		}
	}

	/**
	 * @param string $cmd
	 * @param string $method
	 * @param string $accessToken
	 * @param array $data
	 * @param string $proxy
	 * @return array|mixed|object|string|void
	 */
	public static function cmd( $cmd , $method , $accessToken , $appPublicKey , $appPrivateKey , array $data = [] , $proxy = '' )
	{
		$data["application_key"] = $appPublicKey;
		$data["method"] = $cmd;
		$data["sig"] = self::calcSignature($cmd, $data, $accessToken , $appPrivateKey);
		$data['access_token'] = $accessToken;

		$url = 'https://api.odnoklassniki.ru/fb.do';

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = FSCurl::getContents( $url , $method, $data , [] , $proxy , true );
		$data = json_decode( $data1 , true );

		if( !is_array( $data ) )
		{
			if( is_numeric( $data ) )
			{
				$data = [ 'id' => $data ];
			}
			else
			{
				$data = [
					'error' =>  ['message' => 'Error data!']
				];
			}
		}

		return $data;
	}

	private static function calcSignature($methodName, $parameters, $accessToken, $appPrivateKey)
	{
		ksort($parameters);

		$requestStr = '';
		foreach($parameters as $key=>$value)
		{
			$requestStr .= $key . '=' . $value;
		}

		$requestStr .= md5($accessToken . $appPrivateKey);

		return md5($requestStr);
	}

	/**
	 * @param array $nodeInf
	 * @param string $type
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $nodeInf , $type , $message , $link , $images , $video , $accessToken , $appPublicKey , $appPrivateKey , $proxy )
	{
		$sendData = ['text_link_preview' => true];

		if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'group' )
		{
			$sendData['gid'] = $nodeInf['node_id'];
			$sendData['type'] = 'GROUP_THEME';
		}

		$sendData['attachment'] = [
			'media' => [
				[ 'type' => 'text' , 'text' => $message ] ,
				[ 'type' => 'link' , 'url' => $link ]
			]
		];

		if( $type == 'image' )
		{
			$uplServerSendData = [ 'count' => count($images) ];

			if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'group' )
			{
				$uplServerSendData['gid'] = $nodeInf['node_id'];
			}

			$uplServer = self::cmd('photosV2.getUploadUrl' , 'GET' , $accessToken , $appPublicKey , $appPrivateKey , $uplServerSendData , $proxy);

			if( isset( $uplServer['upload_url'] ) )
			{
				$uplServer = $uplServer['upload_url'];

				$images2 = [];
				$i = 0;
				foreach($images AS $imageURL)
				{
					$i++;
					if(function_exists('curl_file_create'))
					{
						$images2['pic' . $i] = curl_file_create($imageURL);
					}
					else
					{
						$images2['pic' . $i] = '@' . $imageURL;
					}
				}

				$uploadFile = FSCurl::getContents( $uplServer , 'POST' , $images2 , [] , $proxy );
				$uploadFile = json_decode($uploadFile , true);

				$okMediaJson = [];
				$okMediaJson['type'] = 'photo';
				$okMediaJson['list'] = [];
				foreach($uploadFile['photos'] AS $photoTok)
				{
					$okMediaJson['list'][] = ['id' => $photoTok['token']];
				}

				$sendData['attachment']['media'][] = $okMediaJson;
			}
		}
		else if( $type == 'video' )
		{
			$uplServerSendData = [
				'file_name' => mb_substr($message , 0 , 50 , 'UTF-8'),
				'file_size'	=> 0
			];

			if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'group' )
			{
				$uplServerSendData['gid'] = $nodeInf['node_id'];
			}

			$videoUplServer = self::cmd('video.getUploadUrl' , 'GET' , $accessToken , $appPublicKey , $appPrivateKey , $uplServerSendData , $proxy);

			if( isset( $videoUplServer['upload_url'] ) )
			{
				$videoId = $videoUplServer['video_id'];
				$uploadURL = $videoUplServer['upload_url'];

				$uploadFile = FSCurl::getContents( $uploadURL , 'POST' , [
					'file' => function_exists('curl_file_create') ? curl_file_create($video) : '@' . $video
				]  , [] , $proxy );
				$uploadFile = json_decode($uploadFile , true);

				$okMediaJson = [];
				$okMediaJson['type'] = 'movie-reshare';
				$okMediaJson['movieId'] = $videoId;

				$sendData['attachment']['media'][] = $okMediaJson;
			}
		}

		$sendData['attachment'] = json_encode($sendData['attachment']);

		$endPoint = 'mediatopic.post';

		$result = self::cmd($endPoint , 'GET' , $accessToken , $appPublicKey , $appPrivateKey , $sendData , $proxy);

		if( isset($result['error_msg']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$result['error_msg']
			];
		}
		else
		{
			if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'group' )
			{
				$pIdFull = $nodeInf['node_id'] . '/topic/' . $result['id'];
			}
			else
			{
				$pIdFull = $nodeInf['profile_id'] . '/statuses/' . $result['id'];
			}

			$result2 = [
				'status'	=>  'ok',
				'id'		=>	$pIdFull
			];
		}

		return $result2;
	}

	/**
	 * @return string
	 */
	public static function getScope()
	{
		return 'VALUABLE_ACCESS,SET_STATUS,PHOTO_CONTENT,LONG_ACCESS_TOKEN,PUBLISH_TO_STREAM,GROUP_CONTENT,VIDEO_CONTENT';
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

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'ok']);
		$appId = $appInf['app_id'];

		$permissions = self::getScope();

		$callbackUrl = self::callbackUrl();

		return "https://www.odnoklassniki.ru/oauth/authorize?client_id={$appId}&scope={$permissions}&response_type=code&redirect_uri={$callbackUrl}";
	}

	/**
	 * @return bool
	 */
	public static function getAccessToken( )
	{
		do_action('registerSession');
		if( !isset($_SESSION['save_app_id']) )
		{
			return false;
		}

		$code = _get('code' , '' , 'string');

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

		unset($_SESSION['save_app_id']);

		$proxy = '';
		if( isset($_SESSION['fs_proxy_save']) )
		{
			$proxy = is_string($_SESSION['fs_proxy_save']) ? $_SESSION['fs_proxy_save'] : '';

			unset($_SESSION['fs_proxy_save']);
		}

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'ok']);
		$appSecret = $appInf['app_secret'];
		$appId2 = $appInf['app_id'];

		$token_url = 'https://api.odnoklassniki.ru/oauth/token.do';

		$postData = [
			'code'				=>	$code ,
			'redirect_uri'		=>	self::callbackUrl(),
			'grant_type'		=>	'authorization_code',
			'client_id'			=>	$appId2,
			'client_secret'		=>	$appSecret
		];

		$response = FSCurl::getContents($token_url, 'POST' , $postData, [] , $proxy, true);
		$params = json_decode($response , true);

		if( isset( $params['error_description'] ) )
		{
			print $params['error_description'];
			exit();
		}

		$access_token	= esc_html($params['access_token']);
		$refresh_token	= esc_html($params['refresh_token']);
		$expireIn = date('Y-m-d H:i:s' , time() + $params['expires_in']);

		self::authorizeOkUser( $appId , $appInf['app_key'] , $appInf['app_secret'] , $access_token , $refresh_token , $expireIn , $proxy );

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

		$appInf			= wpFetch('apps' , $appId);
		$appId2			= $appInf['app_id'];
		$appSecret		= $appInf['app_secret'];
		$refreshToken	= $tokenInfo['refresh_token'];

		$url = 'https://api.odnoklassniki.ru/oauth/token.do';

		$postData = [
			'refresh_token'		=>	$refreshToken ,
			'grant_type'		=>	'refresh_token',
			'client_id'			=>	$appId2,
			'client_secret'		=>	$appSecret,
		];

		$response = FSCurl::getContents($url , 'POST' , $postData , [] , $proxy , true);
		$params = json_decode($response , true);

		if( isset( $params['error_description'] ) )
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
	 * @return array
	 */
	public static function getStats($postId , $accessToken, $appPublicKey, $appPrivateKey, $proxy)
	{
		$result = self::cmd('mediatopic.getByIds' , 'GET' , $accessToken, $appPublicKey, $appPrivateKey, [
			'topic_ids'	=>	$postId,
			'fields'	=>	'media_topic.*'
		] , $proxy);

		return [
			'comments'      =>  isset($result['media_topics'][0]['discussion_summary']['comments_count']) ? $result['media_topics'][0]['discussion_summary']['comments_count'] : 0,
			'like'          =>  isset($result['media_topics'][0]['like_summary']['count']) ? $result['media_topics'][0]['like_summary']['count'] : 0,
			'shares'        =>  isset($result['media_topics'][0]['reshare_summary']['count']) ? $result['media_topics'][0]['reshare_summary']['count'] : 0,
			'details'       =>  ''
		];
	}

}
