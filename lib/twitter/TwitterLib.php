<?php

require __DIR__ . "/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterLib
{

	/**
	 * @param array $appInf
	 * @param string $oauth_token
	 * @param string $oauth_token_secret
	 * @param string $proxy
	 */
	public static function authorizeUser($appInf , $oauth_token , $oauth_token_secret , $proxy)
	{
		$connection = new Abraham\TwitterOAuth\TwitterOAuth($appInf['app_key'], $appInf['app_secret'], $oauth_token , $oauth_token_secret , $proxy);
		$user = $connection->get("account/verify_credentials");

		if( !(!empty($user) && isset($user->id)) )
		{
			print 'Error! ';
			exit();
		}

		$checkUserExist = wpFetch('accounts' , [
			'user_id'       =>  get_current_user_id(),
			'driver'        =>  'twitter',
			'profile_id'    =>  $user->id_str
		]);

		if($checkUserExist)
		{
			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(false , "'.esc_html__('This account already has been added!' , 'fs-poster').'");window.close();}else{document.write("'.esc_html__('This account already has been added!' , 'fs-poster').'");} </script>';
			exit();
		}

		wpDB()->insert(wpTable('accounts') , [
			'user_id'           =>  get_current_user_id(),
			'driver'            =>  'twitter',
			'name'              =>  $user->name,
			'profile_id'        =>  $user->id_str,
			'email'             =>  '',
			'gender'            =>  0,
			'birthday'          =>  null,
			'username'          =>  $user->screen_name,
			'followers_count'   =>  $user->followers_count,
			'friends_count'     =>  $user->friends_count,
			'listed_count'      =>  $user->listed_count,
			'proxy'             =>  $proxy
		]);

		wpDB()->insert(wpTable('account_access_tokens') , [
			'account_id'            =>  wpDB()->insert_id,
			'app_id'                =>  $appInf['id'],
			'access_token'          =>  $oauth_token,
			'access_token_secret'   =>  $oauth_token_secret
		]);
	}

	/**
	 * @param int $appId
	 * @param string $type
	 * @param string $message
	 * @param string $link
	 * @param array $images
	 * @param string $video
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param string $proxy
	 * @return array
	 */
	public static function sendPost( $appId , $type , $message , $link , $images , $video , $accessToken , $accessTokenSecret , $proxy )
	{
		$appInfo = wpFetch('apps' , ['id' => $appId , 'driver' => 'twitter']);
		if( !$appInfo )
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	'Error! Twitter App not found!'
			];
		}

		$parameters['status'] = $message;

		$connection = new TwitterOAuth($appInfo['app_key'], $appInfo['app_secret'], $accessToken, $accessTokenSecret , $proxy);

		if( $type == 'link' )
		{
			$parameters['status'] .= "\n" . $link;
		}

		if( $type == 'image' && !empty($images) && is_array($images) )
		{
			$uplaodedImages = [];
			$c = 0;
			foreach( $images AS $imageURL )
			{
				$c++;
				if( $c > 4 ) // max 4 image
					break;

				if( empty($imageURL) || !is_string($imageURL) )
				{
					continue;
				}

				try
				{
					$uploadImage = $connection->upload('media/upload', ['media' => $imageURL], true);

					if( isset($uploadImage->media_id_string) && !empty($uploadImage->media_id_string) && is_string($uploadImage->media_id_string) )
					{
						$uplaodedImages[] = $uploadImage->media_id_string;
					}
				}
				catch (Exception $e)
				{

				}
			}

			if( !empty($uplaodedImages) )
			{
				$parameters['media_ids'] = implode(',' , $uplaodedImages);
			}
		}

		if( $type == 'video' && !empty($video) && is_string($video) )
		{
			try
			{
				$uploadImage = $connection->upload('media/upload', [
					'media'          => $video,
					'media_type'     => mime_content_type($video),
					'media_category' => 'tweet_video'
				] , true);

				if( isset($uploadImage->media_id_string) && !empty($uploadImage->media_id_string) && is_string($uploadImage->media_id_string) )
				{
					$parameters['media_ids'] = $uploadImage->media_id_string;
				}
			}
			catch (Exception $e)
			{

			}
		}

		try
		{
			$result = $connection->post('statuses/update', $parameters);
		}
		catch (Exception $e)
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	esc_html( $e->getMessage() )
			];
		}

		if ($connection->getLastHttpCode() == 200)
		{
			return [
				'status'	=>  'ok',
				'id'		=>	isset($result->id_str) && is_string($result->id_str) ? (string)$result->id_str : ''
			];
		}
		else if( isset($result->errors) && is_array($result->errors) && !empty($result->errors) )
		{
			$error = reset($result->errors);
			$errorMsg = isset($error->message) && is_string($error->message) ? (string)$error->message : 'Error! (-)';
			return [
				'status'	=>	'error',
				'error_msg'	=>	$errorMsg
			];
		}
		else
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	'Error! (?)'
			];
		}
	}

	/**
	 * @param integer $postId
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 * @param integer $appId
	 * @param string $proxy
	 * @return array
	 */
	public static function getStats( $postId , $accessToken , $accessTokenSecret , $appId , $proxy )
	{
		$appInfo = wpFetch('apps' , ['id' => $appId , 'driver' => 'twitter']);

		$connection = new TwitterOAuth($appInfo['app_key'], $appInfo['app_secret'], $accessToken, $accessTokenSecret , $proxy);
		$stat = (array)$connection->get("statuses/show/" . $postId);

		return [
			'comments'      =>  0,
			'like'          =>  isset($stat['favorite_count']) ? (int)$stat['favorite_count'] : 0,
			'shares'        =>  isset($stat['retweet_count']) ? (int)$stat['retweet_count'] : 0,
			'details'       =>  ''
		];
	}

}