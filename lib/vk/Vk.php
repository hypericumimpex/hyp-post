<?php

require_once __DIR__ . '/../FSCurl.php';

class Vk
{

	/**
	 * @return string
	 */
	public static function callbackURL()
	{
		return site_url() . '?vk_callback=1';
	}

	/**
	 * @param integer $appId
	 * @param string $accessToken
	 * @param string $proxy
	 * @return mixed
	 */
	public static function authorizeVkUser( $appId , $accessToken , $proxy )
	{
		$me = self::cmd('users.get', 'GET' , $accessToken , ['fields' => 'id,first_name,last_name,screen_name, sex, bdate,photo,followers_count,common_count'] , $proxy);

		if( isset($me['error']) && isset($me['error']['message']) )
		{
			FSresponse(false , $me['error']['message'] );
		}
		else if( isset($me['error']) )
		{
			return $me;
		}

		$me = reset($me);
		$meId = $me['id'];

		$checkLoginRegistered = FSwpFetch('accounts' , ['user_id' => get_current_user_id() , 'driver' => 'vk', 'profile_id' => $meId]);

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$me['first_name'] .' ' . $me['last_name'],
			'driver'			=>	'vk',
			'profile_id'		=>	$meId,
			'gender'			=>	$me['sex'],
			'birthday'			=>	date('Y-m-d' , strtotime($me['bdate'])),
			'profile_pic'		=>	$me['photo'],
			'followers_count'	=>	isset($me['followers_count']) && is_numeric($me['followers_count']) ? $me['followers_count'] : 0,
			'friends_count'		=>	isset($me['common_count']) && is_numeric($me['common_count']) ? $me['common_count'] : 0,
			'username'			=>	$me['screen_name'],
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

			FSwpDB()->delete( FSwpTable('account_nodes')  , ['account_id' => $accId] );
		}

		// acccess token
		FSwpDB()->insert( FSwpTable('account_access_tokens') ,  [
			'account_id'	=>	$accId,
			'app_id'		=>	$appId,
			'expires_on'	=>	null,
			'access_token'	=>	$accessToken
		]);


		$loadedOwnPages = [];
		// admins comunications
		if( get_option('fs_vk_load_admin_communities' , 1) == 1 )
		{
			$accountsList = self::cmd('groups.get', 'GET' , $accessToken , [
				'filter'    =>  'admin' ,
				'extended'  =>  '1',
				'fields'    =>  'members_count'
			] , $proxy);
			if( isset($accountsList['items']) && is_array($accountsList['items']) )
			{
				foreach($accountsList['items'] AS $accountInfo)
				{
					$loadedOwnPages[$accountInfo['id']] = true;

					FSwpDB()->insert(FSwpTable('account_nodes') , [
						'user_id'			=>	get_current_user_id(),
						'driver'			=>	'vk',
						'screen_name'		=>	$accountInfo['screen_name'],
						'account_id'		=>	$accId,
						'node_type'			=>	$accountInfo['type'],
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						'access_token'		=>	null,
						'category'			=>	'admin',
						'fan_count'			=>	$accountInfo['members_count'],
						'cover'				=>	$accountInfo['photo_50']
					]);
				}
			}
		}

		// members comunications
		if( get_option('fs_vk_load_members_communities' , 1) == 1 )
		{
			$limit = get_option('vk_max_communities_limit' , 100);
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = self::cmd('groups.get', 'GET' , $accessToken , [
				'extended'  =>  '1',
				'fields'    =>  'members_count',
				'count'     =>  $limit
			] , $proxy);

			if( isset($accountsList['items']) && is_array($accountsList['items']) )
			{
				foreach($accountsList['items'] AS $accountInfo)
				{
					if( isset($loadedOwnPages[$accountInfo['id']]) )
					{
						continue;
					}

					FSwpDB()->insert(FSwpTable('account_nodes') , [
						'user_id'			=>	get_current_user_id(),
						'driver'			=>	'vk',
						'screen_name'		=>	$accountInfo['screen_name'],
						'account_id'		=>	$accId,
						'node_type'			=>	$accountInfo['type'],
						'node_id'			=>	$accountInfo['id'],
						'name'				=>	$accountInfo['name'],
						'access_token'		=>	null,
						'category'			=>	'',
						'fan_count'			=>	isset($accountInfo['members_count']) ? $accountInfo['members_count'] : 0,
						'cover'				=>	isset($accountInfo['photo_50']) ? $accountInfo['photo_50'] : ''
					]);
				}
			}
		}
	}

	/**
	 * @param string $cmd
	 * @param string $method
	 * @param string $accessToken
	 * @param array $data
	 * @param string $proxy
	 * @return array|mixed
	 */
	public static function cmd( $cmd , $method , $accessToken , array $data = [] , $proxy = '' )
	{
		$data['access_token'] = $accessToken;
		$data['v'] = '5.69';

		$url = 'https://api.vk.com/method/' . $cmd ;

		$method = $method == 'POST' ? 'POST' : ( $method == 'DELETE' ? 'DELETE' : 'GET' );

		$data1 = FSCurl::getContents( $url , $method , $data , [] , $proxy );
		$data = json_decode( $data1 , true );

		if( !is_array($data) || !isset($data['response']) )
		{
			return [
				'error' =>  ['message' => isset($data['error']) && isset($data['error']['error_msg']) ? $data['error']['error_msg'] : ( isset($data['error']) && isset($data['error']['message']) ? $data['error']['message'] : 'Error!' . htmlspecialchars( $data1 )) ]
			];
		}

		return $data['response'];
	}

	/**
	 * @param string $nodeFbId
	 * @param string $type
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $nodeFbId , $type , $message , $link , $images , $video , $accessToken , $proxy )
	{
		$sendData = [
			'message'	=>	$message,
			'owner_id'	=>	$nodeFbId
		];

		if( $type == 'link' )
		{
			$sendData['attachments'] = $link;
		}
		else if( $type == 'image' || $type == 'image_link' )
		{
			if( $type == 'image_link' )
			{
				$sendData['attachments'] = [ $link ];
			}
			else
			{
				$sendData['attachments'] = [ ];
			}

			$uplData = [ ];
			if( $nodeFbId < 0 )
			{
				$uplData['group_id'] = abs( $nodeFbId );
			}

			$uplServer = self::cmd('photos.getWallUploadServer' , 'GET' , $accessToken , $uplData , $proxy);

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
						$images2['file' . $i] = curl_file_create($imageURL);
					}
					else
					{
						$images2['file' . $i] = '@' . $imageURL;
					}
				}

				$uploadFile = FSCurl::getContents( $uplServer , 'POST' , $images2 , [] , $proxy );
				$uploadFile = json_decode($uploadFile , true);

				if( $nodeFbId < 0 )
				{
					$uploadFile['group_id'] = abs( $nodeFbId );

				}
				else
				{
					$uploadFile['user_id'] = $nodeFbId;
				}

				if( is_array($uploadFile) && !empty($uploadFile) )
				{
					$uploadPhoto = self::cmd('photos.saveWallPhoto' , 'GET' , $accessToken , $uploadFile , $proxy);

					if( is_array($uploadPhoto) && !isset($uploadPhoto['error']) )
					{
						foreach($uploadPhoto AS $photoInf)
						{
							$sendData['attachments'][] = 'photo' . $photoInf['owner_id'] . '_' . $photoInf['id'];
						}
					}
					$sendData['attachments'] = implode(',' , $sendData['attachments']);
				}
			}
		}
		else if( $type == 'video' )
		{
			$videoUplServer = self::cmd('video.save' , 'GET' , $accessToken , [
				'name'      =>  mb_substr($message , 0 , 50 , 'UTF-8'),
				'wallpost'  =>  1
			] , $proxy);

			if( isset( $videoUplServer['owner_id'] ) && isset( $videoUplServer['video_id'] ) && isset( $videoUplServer['upload_url'] ) )
			{
				$ownerId = $videoUplServer['owner_id'];
				$videoId = $videoUplServer['video_id'];
				$uploadURL = $videoUplServer['upload_url'];

				$uploadFile = FSCurl::getContents( $uploadURL , 'POST' , [
					'file' => function_exists('curl_file_create') ? curl_file_create($video) : '@' . $video
				]  , [] , $proxy );
				$uploadFile = json_decode($uploadFile , true);

				if( !isset($uploadFile['error']) )
				{
					$sendData['attachments'] = 'video'.$ownerId.'_'.$videoId;
				}
			}
		}

		$result = self::cmd('wall.post' , 'POST' , $accessToken , $sendData , $proxy );

		if( isset($result['error']) )
		{
			$result2 = [
				'status'	=>	'error',
				'error_msg'	=>	isset($result['error']['message']) ? $result['error']['message'] : 'Error!'
			];
		}
		else
		{
			$result2 = [
				'status'	=>  'ok',
				'id'		=>	$nodeFbId . '_' . $result['post_id']
			];
		}

		return $result2;
	}

	/**
	 * @param integer $postId
	 * @param string $accessToken
	 * @param string $proxy
	 * @return array
	 */
	public static function getStats( $postId , $accessToken , $proxy )
	{
		$stat = self::cmd('wall.getById' , 'GET' , $accessToken , ['posts' => $postId] , $proxy);
		$stat = is_array($stat) && isset($stat[0]) ? $stat[0] : array();

		return [
			'comments'      =>  isset($stat['comments']['count']) ? (int)$stat['comments']['count'] : 0,
			'like'          =>  isset($stat['likes']['count']) ? (int)$stat['likes']['count'] : 0,
			'shares'        =>  isset($stat['reposts']['count']) ? (int)$stat['reposts']['count'] : 0,
			'details'       =>  ''
		];
	}

}