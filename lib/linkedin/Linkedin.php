<?php

require_once FS_LIB_DIR . 'FSCurl.php';

class Linkedin
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '?linkedin_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $scExpireIn
	 * @param string $proxy
	 */
	public static function authorizeLinkedinUser( $appId , $accessToken , $scExpireIn , $proxy)
	{
		$me = self::cmd('me', 'GET' , $accessToken , []  , $proxy );

		if( isset($me['error']) && isset($me['error']['message']) )
		{
			FSresponse(false , $me['error']['message'] );
		}

		$meId = $me['id'];

		$checkLoginRegistered = FSwpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'linkedin', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'		=>	get_current_user_id(),
			'name'		  	=>	$me['localizedFirstName'] .' ' . $me['localizedLastName'],
			'driver'		=>	'linkedin',
			'profile_id'	=>	$meId,
			'profile_pic'	=>	isset($me['profilePicture']['displayImage']) ? $me['profilePicture']['displayImage'] : '',
			//'username'		=>	str_replace(['https://www.linkedin.com/in/', 'http://www.linkedin.com/in/'] , '' , $me['publicProfileUrl']),
			'proxy'			=>  $proxy
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

			FSwpDB()->delete( FSwpTable('account_nodes')  , ['account_id' => $accId] );
		}

		// acccess token
		FSwpDB()->insert( FSwpTable('account_access_tokens') ,  [
			'account_id'	=>	$accId,
			'app_id'		=>	$appId,
			'expires_on'	=>	$scExpireIn,
			'access_token'	=>	$accessToken
		]);

		// my pages load
		$companiesList = self::cmd('organizationalEntityAcls', 'GET' , $accessToken , [
			'q' 			=> 'roleAssignee',
			'role'			=>	'ADMINISTRATOR',
			'projection'	=>	'(elements*(organizationalTarget~(id,localizedName,vanityName,logoV2,organizationType)))'
		] , $proxy );

		if( isset($companiesList['elements']) && is_array($companiesList['elements']) )
		{
			foreach($companiesList['elements'] AS $companyInf)
			{
				FSwpDB()->insert(FSwpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'linkedin',
					'account_id'		=>	$accId,
					'node_type'			=>	'company',
					'node_id'			=>	isset($companyInf['id']) ? $companyInf['id'] : 0,
					'name'				=>	isset($companyInf['localizedName']) ? $companyInf['localizedName'] : '-',
					'category'			=>	isset($companyInf['organizationType']) && is_string($companyInf['organizationType']) ? $companyInf['organizationType'] : '',
					'cover'				=>	isset($companyInf['logoV2']['cropped']) && is_string($companyInf['logoV2']['cropped']) ? $companyInf['logoV2']['cropped'] : '',
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
	 * @return array|mixed|object|string|void
	 */
	public static function cmd( $cmd , $method , $accessToken , array $data = [] , $proxy = '' )
	{
		$url = 'https://api.linkedin.com/v2/' . $cmd;

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$headers = [
			'Connection'				=>  'Keep-Alive',
			'Content-Type'				=>	'text/plain',
			'X-RestLi-Protocol-Version'	=>  '2.0.0',
			'Authorization'				=>  'Bearer ' . $accessToken
		];

		if( $method == 'POST' )
		{
			$data = json_encode($data);
		}

		$data1 = FSCurl::getContents( $url , $method, $data , $headers , $proxy );
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
	public static function sendPost( $profileId , $nodeInf , $type , $message , $title , $link , $images , $video , $accessToken , $proxy )
	{
		$sendData = [
			'lifecycleState'	=> 'PUBLISHED',
			'specificContent'	=> [
				'com.linkedin.ugc.ShareContent'	=> [
					'shareCommentary'		=> [ 'text'	=> $message ],
					'shareMediaCategory'	=> 'ARTICLE'
				]
			],
			'visibility'	=> [ 'com.linkedin.ugc.MemberNetworkVisibility'	=> 'PUBLIC' ]
		];

		if( $type == 'link' && !empty($link) )
		{
			$sendData['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
				[
					'status'		=> 'READY',
					'originalUrl'	=> $link,
					'description'	=> [ 'text'	=> $message ],
					'title'			=> [ 'text'	=> $title ],
				]
			];

			if( !empty( $images ) )
			{
				$thumbImage = reset( $images );
				$sendData['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['thumbnails'] = [ [ 'url' => $thumbImage ] ];
			}
		}
		else if( $type == 'image' )
		{
			$thumbImage = reset( $images );

			$sendData['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
				[
					'status'		=> 'READY',
					'originalUrl'	=> $thumbImage,
					'description'	=> [ 'text'	=> $message ],
					'title'			=> [ 'text'	=> $title ],
					'thumbnails'	=> [ [ 'url' => $thumbImage ] ]
				]
			];
		}
		else if( $type == 'video' )
		{
			$sendData['specificContent']['com.linkedin.ugc.ShareContent']['media'] = [
				[
					'status'		=> 'READY',
					'originalUrl'	=> $video,
					'description'	=> [ 'text'	=> $message ],
					'title'			=> [ 'text'	=> $title ],
					'thumbnails'	=> [ [ 'url' => $video ] ]
				]
			];
		}

		if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'company' )
		{
			$sendData['author'] = 'urn:li:organization:' . $nodeInf['node_id'];
		}
		else if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'company' )
		{
			$sendData['author'] = 'urn:li:person:' . $profileId;
			$sendData['containerEntity'] = 'urn:li:group:' . $nodeInf['node_id'];
		}
		else
		{
			$sendData['author'] = 'urn:li:person:' . $nodeInf['profile_id'];
		}

		$result = self::cmd( 'ugcPosts' , 'POST' , $accessToken , $sendData , $proxy);

		if( isset($result['error']) && isset($result['error']['message']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$result['error']['message']
			];
		}
		else if( isset($result['errorCode']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	isset($result['message']) ? $result['message'] : 'Error!'
			];
		}
		else
		{
			$postIdGet = explode('-' , $result['id']);
			$postIdGet = end($postIdGet);

			$result2 = [
				'status'	=>  'ok',
				'id'		=>	$postIdGet
			];
		}

		return $result2;
	}

	/**
	 * @return string
	 */
	public static function getScope()
	{
		$permissions = ['r_liteprofile', 'rw_company_admin', 'w_member_social'];

		return implode(',' , array_map('urlencode' , $permissions));
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

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'linkedin']);
		$appId = $appInf['app_id'];

		$permissions = self::getScope();

		$callbackUrl = self::callbackUrl();

		return "https://www.linkedin.com/oauth/v2/authorization?redirect_uri={$callbackUrl}&scope={$permissions}&response_type=code&client_id={$appId}&state=" . uniqid();
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
			if( isset($_GET['error_description']) && is_string($_GET['error_description']) )
			{
				$errorMsg = esc_html(str_replace('&quot;', '', $_GET['error_description']));
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

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'linkedin']);
		$appSecret = $appInf['app_secret'];
		$appId2 = $appInf['app_id'];

		$token_url = "https://www.linkedin.com/oauth/v2/accessToken?"
			. "client_id=" . $appId2 . "&redirect_uri=" . urlencode(self::callbackUrl())
			. "&client_secret=" . $appSecret . "&code=" . $code . '&grant_type=authorization_code';

		$response = FSCurl::getURL($token_url , $proxy);
		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			print $params['error']['message'];
			exit();
		}

		$access_token = esc_html($params['access_token']);
		$expireIn = date('Y-m-d H:i:s' , time() + $params['expires_in']);

		self::authorizeLinkedinUser( $appId , $access_token , $expireIn , $proxy );

		print 'Loading... <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("Error! Please try again!");} </script>';
		exit;
	}

	/**
	 * @param integer $postId
	 * @return array
	 */
	public static function getStats($postId , $proxy)
	{
		//$result = self::cmd('people/~/shares' , 'GET' , 'AQUJ2UMke09Iqj1tj7iL9evS8sZRb0YtWV_l_orSEsmA_ypxDAtIq4UiOh1AFDEFWBSMnXnIgMp72VOAptz7Tbrzfh-FIv1LTtk6x3wCj-Y_6pNbZWKWyYDoFOaUHfZlQ1gY9_zGhcA_SahjyIAVdTPEzRmALl-ebjz94X1MTTRWk0P6k4_LShlxT0oSCcrcH95VdoEQKda3h2bNNwr9oMD5ydKHXsA6xsGBQNBlS2ieRTPOqJDJ97gd5aWDz1vw8ukF7ESwYNo1r86cWJ4duLovksy3yGL68_N41wd1Czt39AlBaiVeqSlIoj2jaRIc_-NQ8_GUyNEFprAM5F5fQqTK9feDDA' , []);

		return [
			'comments'	  =>  0,
			'like'		  =>  0,
			'shares'		=>  0,
			'details'	   =>  ''
		];
	}

}