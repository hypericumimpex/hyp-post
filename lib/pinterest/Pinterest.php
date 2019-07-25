<?php

require_once __DIR__ . '/../FSCurl.php';

class Pinterest
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '/?pinterest_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $proxy
	 */
	public static function authorizePinterestUser( $appId , $accessToken , $proxy )
	{
		$me = self::cmd('me', 'GET' , $accessToken , ['fields' => 'id,username,image,first_name,last_name,counts'] , $proxy );

		if( isset($me['error']) && isset($me['error']['message']) )
		{
			FSresponse(false , $me['error']['message'] );
		}

		if( !isset($me['data']) )
		{
			FSresponse(false);
		}
		$me = $me['data'];

		$meId = $me['id'];

		$checkLoginRegistered = FSwpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'pinterest', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['first_name'] .' ' . $me['last_name'],
			'driver'			=>	'pinterest',
			'profile_id'		=>	$meId,
			'profile_pic'		=>	$me['image']['60x60']['url'],
			'followers_count'	=>	$me['counts']['followers'],
			'friends_count'		=>	$me['counts']['following'],
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
			'access_token'	=>	$accessToken
		]);

		// set default board
		$boards = self::cmd('me/boards' , 'GET' , $accessToken , ['fields' => 'id,name,url,image'] , $proxy);

		if( is_array($boards['data']) && !empty($boards['data']) )
		{
			foreach ( $boards['data'] AS $board )
			{
				$boardId	= $board['id'];
				$boardName	= $board['name'];
				$screenName	= str_replace('https://www.pinterest.com/', '', $board['url']);
				$image		= $board['image'];

				$image		= reset($image);
				$image		= isset($image['url']) ? $image['url'] : '';

				FSwpDB()->insert(FSwpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'pinterest',
					'account_id'		=>	$accId,
					'node_type'			=>	'board',
					'node_id'			=>	$boardId,
					'name'				=>	$boardName,
					'cover'				=>	$image,
					'screen_name'		=>	$screenName
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
	 * @return array|mixed|object
	 */
	public static function cmd( $cmd , $method , $accessToken , array $data = [] , $proxy = '' )
	{
		$data['access_token'] = $accessToken;

		$url = 'https://api.pinterest.com/v1/' . trim($cmd , '/') . '/';

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = FSCurl::getContents( $url , $method , $data , [] , $proxy );
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
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $boardId , $type , $message , $link , $images , $video , $accessToken , $proxy )
	{
		$sendData = [
			'board'   =>    $boardId,
			'note'    =>    $message,
			'link'    =>    $link
		];

		if( $type == 'image' )
		{
			$sendData['image_url'] = reset($images);
		}
		else
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	'Image not fount for pin to board!'
			];
		}

		$result = self::cmd('pins' , 'POST' , $accessToken , $sendData , $proxy);

		if( isset($result['error']) && isset( $result['error']['message'] ) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$result['error']['message']
			];
		}
		else if( isset($result['message']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	$result['message']
			];
		}
		else
		{


			$result2 = [
				'status'	=>  'ok',
				'id'		=>	$result['data']['id']
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

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'pinterest']);
		if( !$appInf )
		{
			print 'Error! App not found!';
			exit();
		}
		$appId = urlencode($appInf['app_id']);

		$callbackUrl = urlencode(self::callbackUrl());

		return "https://api.pinterest.com/oauth/?response_type=code&redirect_uri=".$callbackUrl."&client_id=".$appId."&scope=read_public,write_public";
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

		$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'pinterest']);
		$appSecret = urlencode($appInf['app_secret']);
		$appId2 = urlencode($appInf['app_id']);


		$token_url = "https://api.pinterest.com/v1/oauth/token?grant_type=authorization_code&client_id={$appId2}&client_secret={$appSecret}&code={$code}";

		$response = FSCurl::getContents($token_url , 'POST' , [] , [] , $proxy);
		$params = json_decode($response , true);

		if( isset( $params['error']['message'] ) )
		{
			print $params['error']['message'];
			exit();
		}

		$access_token = esc_html($params['access_token']);

		self::authorizePinterestUser( $appId , $access_token , $proxy );

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
		$result = self::cmd('pins/' . $postId , 'GET' , $accessToken, ['fields' => 'counts'] , $proxy);

		return [
			'comments'      =>  isset($result['data']['counts']['comments']) ? $result['data']['counts']['comments'] : 0,
			'like'          =>  isset($result['data']['counts']['saves']) ? $result['data']['counts']['saves'] : 0,
			'shares'        =>  0,
			'details'       =>  'Saves: ' . (isset($result['data']['counts']['saves']) ? $result['data']['counts']['saves'] : 0)
		];
	}

}