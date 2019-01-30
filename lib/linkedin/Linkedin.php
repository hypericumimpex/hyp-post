<?php

require_once LIB_DIR . 'FSCurl.php';

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
		$me = self::cmd('people/~:(id,first-name,last-name,picture-url,public-profile-url,num-connections)', 'GET' , $accessToken , []  , $proxy );

		if( isset($me['error']) && isset($me['error']['message']) )
		{
			response(false , $me['error']['message'] );
		}

		$meId = $me['id'];

		$checkLoginRegistered = wpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'linkedin', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['firstName'] .' ' . $me['lastName'],
			'driver'			=>	'linkedin',
			'profile_id'		=>	$meId,
			'profile_pic'		=>	$me['pictureUrl'],
			'friends_count'		=>	$me['numConnections'],
			'username'			=>	str_replace(['https://www.linkedin.com/in/', 'http://www.linkedin.com/in/'] , '' , $me['publicProfileUrl']),
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
			'access_token'	=>	$accessToken
		]);

		// my pages load
		$companiesList = self::cmd('companies:(id,company-type,name,logo-url,num-followers)', 'GET' , $accessToken , ['is-company-admin' => 'true'] , $proxy );
		if( isset($companiesList['values']) && is_array($companiesList['values']) )
		{
			foreach($companiesList['values'] AS $companyInf)
			{
				wpDB()->insert(wpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'linkedin',
					'account_id'		=>	$accId,
					'node_type'			=>	'company',
					'node_id'			=>	$companyInf['id'],
					'name'				=>	$companyInf['name'],
					'category'			=>	isset($companyInf['companyType']['name']) ? $companyInf['companyType']['name'] : '',
					'fan_count'			=>	$companyInf['numFollowers'],
					'cover'				=>	isset($companyInf['logoUrl']) ? $companyInf['logoUrl'] : '',
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
		$url = 'https://api.linkedin.com/v1/' . $cmd . '?oauth2_access_token=' . $accessToken;

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$headers = [
			'Content-Type'  =>  'application/json',
			'x-li-format'   =>  'json'
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
	public static function sendPost( $nodeInf , $type , $message , $title , $link , $images , $video , $accessToken , $proxy )
	{
		$sendData = [
			'comment'       =>  $message,
			'visibility'    =>  ['code' =>  'anyone']
		];

		if( $type == 'link' && !empty($link) )
		{
			$sendData['content'] = [];
			$sendData['content']['title'] = $title;
			$sendData['content']['submitted-url'] = $link;
		}
		if( $type == 'image' )
		{
			$sendData['content'] = [];
			$sendData['content']['title'] = $title;
			$sendData['content']['submitted-image-url'] = reset($images);
			$sendData['content']['submitted-url'] = $link;
		}
		if( $type == 'video' )
		{
			$sendData['content'] = [];
			$sendData['content']['title'] = $title;
			$sendData['content']['submitted-url'] = $video;
		}

		$endPoint = 'people/~/shares';
		if( isset($nodeInf['node_type']) && $nodeInf['node_type'] == 'company' )
		{
			$endPoint = 'companies/' . urlencode($nodeInf['node_id']) . '/shares';
		}

		$result = self::cmd($endPoint , 'POST' , $accessToken , $sendData , $proxy);

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
			$postIdGet = explode('-' , $result['updateKey']);
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
		$permissions = ['r_basicprofile', 'rw_company_admin', 'w_share'];

		return implode(',' , array_map('urlencode' , $permissions));
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

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'linkedin']);
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

		$appInf = wpFetch('apps' , ['id' => $appId , 'driver' => 'linkedin']);
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
			'comments'      =>  0,
			'like'          =>  0,
			'shares'        =>  0,
			'details'       =>  ''
		];
	}

}