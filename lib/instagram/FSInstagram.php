<?php

require_once LIB_DIR . "vendor/autoload.php";
require_once LIB_DIR . 'instagram/CustomHandler.php';

class FSInstagram
{
	/**
	 * @var string
	 */
	private static $error = '';

	/**
	 * @var \InstagramAPI\Instagram
	 */
	private static $ig = [];

	/**
	 * @param string $userName
	 * @param string $proxy
	 */
	private static function constuctInstagramApi( $userName, $proxy )
	{
		if( !isset(self::$ig[$userName]) )
		{
			// non CLI method
			\InstagramAPI\Instagram::$allowDangerousWebUsageAtMyOwnRisk = true;

			// create instance...
			try
			{

				self::$ig[$userName] = new \InstagramAPI\Instagram( false, false, [
					'storage'	=>	'custom',
					'class'		=>	new CustomHandler(),
				]);
			}
			catch(Exception $e)
			{
				return $e->getMessage();
			}

			// set proxy...
			if( is_string($proxy) && !empty( trim( $proxy ) ) )
			{
				self::$ig[$userName]->setProxy($proxy);
			}
		}

		return true;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $user_id
	 * @param string $nonce_code
	 * @param string $code
	 * @return array
	 */
	public static function challenge($username , $password , $proxy , $user_id , $nonce_code , $code)
	{
		$checkConnection = self::constuctInstagramApi($username , $proxy);
		if( $checkConnection !== true )
		{
			return ['status' => 'error' , 'message' => $checkConnection];
		}

		$result = null;

		try
		{
			$result = self::$ig[$username]->challengeApprove($username, $password , $code , $user_id , $nonce_code);
		}
		catch(\InstagramAPI\Exception\InstagramException $e)
		{
			return ['status' => 'error' , 'message' => substr($e->getMessage() , strpos($e->getMessage() , ':')+1)];
		}

		return ['status' => 'ok'];
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $two_factor_identifier
	 * @param string $code
	 * @return array
	 */
	public static function verifyTwoFactor($username , $password , $proxy , $two_factor_identifier , $code)
	{
		$checkConnection = self::constuctInstagramApi($username , $proxy);
		if( $checkConnection !== true )
		{
			return ['status' => 'error' , 'message' => $checkConnection];
		}

		try
		{
			$result = self::$ig[$username]->finishTwoFactorLogin($username, $password , $two_factor_identifier, $code );
		}
		catch(\InstagramAPI\Exception\InstagramException $e)
		{
			return ['status' => 'error' , 'message' => substr($e->getMessage() , strpos($e->getMessage() , ':')+1)];
		}

		return ['status' => 'ok'];
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param string $proxy
	 * @param bool $forceLogin
	 * @return array
	 */
	public static function login($username , $password , $proxy , $forceLogin = false)
	{
		$checkConnection = self::constuctInstagramApi($username , $proxy);
		if( $checkConnection !== true )
		{
			return ['status' => 'error' , 'message' => '  ' . $checkConnection];
		}

		$error = false;
		$result = null;

		try
		{
			$result = self::$ig[$username]->login($username, $password , 1800 , $forceLogin);
		}
		catch(\InstagramAPI\Exception\InstagramException $e)
		{
			$error = true;
			self::$error = $e->getMessage();
		}

		if( !is_null($result) && ($result2 = $result->asArray()) && is_array($result2) && isset($result2['step_name']) && strpos($result2['step_name'] , 'verify') === 0 && isset($result2['step_data']) && isset($result2['step_data']['contact_point']) && is_string($result2['step_data']['contact_point']) )
		{
			return [
				'status'        => 'error',
				'do'            => 'challenge',
				'message'       => $result2['step_data']['contact_point'],
				'user_id'       => $result2['user_id'],
				'nonce_code'    => $result2['nonce_code']
			];
		}
		else if( !is_null($result) && ($result2 = $result->asArray()) && is_array($result2) && isset($result2['two_factor_info']['two_factor_identifier']) )
		{
			return [
				'status'                    => 'error',
				'do'                        => 'two_factor',
				'two_factor_identifier'     => $result2['two_factor_info']['two_factor_identifier'],
				'message'                   => isset($result2['two_factor_info']['obfuscated_phone_number']) ? $result2['two_factor_info']['obfuscated_phone_number'] : ''
			];
		}

		return $error===false ? ['status' => 'ok' , 'ig' => self::$ig[$username]] : ['status' => 'error' , 'message' => self::$error];
	}

	/**
	 * @param array $accountInfo
	 * @param string $type
	 * @param string $message
	 * @param string $link
	 * @param $images
	 * @param $video
	 * @return array
	 */
	public static function sendStory( $accountInfo , $type , $message , $link , $images , $video )
	{
		/* account added with cookie method */
		if( $accountInfo['password'] == '*****' )
		{
			require_once LIB_DIR . 'instagram/FSInstagramApi.php';

			$cookies = wpFetch('account_sessions' , ['driver' => 'instagram' , 'username' => $accountInfo['username']]);

			$api = new FSInstagramApi(json_decode($cookies['cookies'] , true) , $accountInfo['proxy']);
			if( $type == 'image' )
			{
				$resizedImage = self::imageForInstagramStory(reset($images) , $message);

				$photo = is_null( $resizedImage ) ? reset($images) : $resizedImage;
				$result = $api->uploadPhotoStory($photo, $link);

				// destroy resized temp image
				if( !is_null($resizedImage) )
				{
					unlink($resizedImage);
				}

				return $result;
			}
			else if( $type == 'video' )
			{
				return [
					'status'	=>	'error',
					'error_msg'	=>	'With the "Cookie method" video story not supported!'
				];
			}
			else
			{
				return [
					'status'	=>	'error',
					'error_msg'	=>	'Error! In post image or video not found!'
				];
			}
		}

		$ig = self::login($accountInfo['username'] , $accountInfo['password'] , $accountInfo['proxy']);

		if( !( is_array($ig) && isset($ig['ig']) ) )
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	self::$error
			];
		}
		$ig = $ig['ig'];

		$metadata = [
			'link'      =>  $link
		];

		try
		{
			if( $type == 'image' )
			{
				$resizedImage = self::imageForInstagramStory(reset($images) , $message);

				$photo = new \InstagramAPI\Media\Photo\InstagramPhoto( is_null( $resizedImage ) ? reset($images) : $resizedImage , ['targetFeed' => \InstagramAPI\Constants::FEED_STORY]);
				$result = $ig->story->uploadPhoto($photo->getFile(), $metadata);

				if( !is_null($resizedImage) )
				{
					// destroy resized temp image
					unlink($resizedImage);
				}
			}
			else if( $type == 'video' )
			{
				$video = new \InstagramAPI\Media\Video\InstagramVideo($video, ['targetFeed' => \InstagramAPI\Constants::FEED_STORY]);
				$result = $ig->story->uploadVideo($video->getFile(), $metadata);
			}
			else
			{
				return [
					'status'	=>	'error',
					'error_msg'	=>	'Error! In post image or video not found!'
				];
			}
		}
		catch (\Exception $e)
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	$e->getMessage()
			];
		}

		if( isset($result) )
		{
			$result = $result->asArray();

			$result2 = [
				'status'	=>  'ok',
				'id'		=>	isset($result['media']['code']) ? $result['media']['code'] : '?',
				'id2'		=>	isset($result['media']['id']) ? $result['media']['id'] : '?'
			];
		}

		return $result2;
	}

	/**
	 * @param array $accountInfo
	 * @param string $type
	 * @param string $message
	 * @param string $link
	 * @param $images
	 * @param $video
	 * @return array
	 */
	public static function sendPost( $accountInfo , $type , $message , $link , $images , $video )
	{
		/* account added with cookie method */
		if( $accountInfo['password'] == '*****' )
		{
			require_once LIB_DIR . 'instagram/FSInstagramApi.php';

			$cookies = wpFetch('account_sessions' , ['driver' => 'instagram' , 'username' => $accountInfo['username']]);

			$api = new FSInstagramApi(json_decode($cookies['cookies'] , true) , $accountInfo['proxy']);
			if( $type == 'image' )
			{
				return $api->uploadPhoto(reset($images) , $message);
			}
			else if( $type == 'vide' )
			{
				return $api->uploadVide($video , $message);
			}
			else
			{
				return [
					'status'	=>	'error',
					'error_msg'	=>	'Error! In post image or video not found!'
				];
			}
		}

		$ig = self::login($accountInfo['username'] , $accountInfo['password'] , $accountInfo['proxy']);

		if( !( is_array($ig) && isset($ig['ig']) ) )
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	self::$error
			];
		}
		$ig = $ig['ig'];

		$metadata = ['caption' => $message];

		try
		{
			if( $type == 'image' )
			{
				$photo = new \InstagramAPI\Media\Photo\InstagramPhoto(reset($images));
				$result = $ig->timeline->uploadPhoto($photo->getFile(), $metadata);
			}
			else if( $type == 'video' )
			{
				$video = new \InstagramAPI\Media\Video\InstagramVideo($video);
				$result = $ig->timeline->uploadVideo($video->getFile(), $metadata);
			}
			else
			{
				return [
					'status'	=>	'error',
					'error_msg'	=>	'Error! In post image or video not found!'
				];
			}
		}
		catch (\Exception $e)
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	$e->getMessage()
			];
		}

		if( isset($result) )
		{
			$result = $result->asArray();

			$result2 = [
				'status'	=>  'ok',
				'id'		=>	isset($result['media']['code']) ? $result['media']['code'] : '?',
				'id2'		=>	isset($result['media']['id']) ? $result['media']['id'] : '?'
			];
		}

		return $result2;
	}

	/**
	 * @param int $postId
	 * @param array $accountInfo
	 * @return array
	 */
	public static function getStats( $postId, $postId2, $accountInfo )
	{
		if( empty($accountInfo) || empty($accountInfo['username']) || empty($accountInfo['password']) )
		{
			return [
				'comments'      =>  0,
				'like'          =>  0,
				'shares'        =>  0,
				'details'       =>  ''
			];
		}

		if( $accountInfo['password'] == '*****' )
		{
			if( empty($accountInfo['cookies']) )
			{
				return [
					'comments'      =>  0,
					'like'          =>  0,
					'shares'        =>  0,
					'details'       =>  ''
				];
			}

			require_once LIB_DIR . 'instagram/FSInstagramApi.php';

			$api = new FSInstagramApi(json_decode($cookies['cookies'] , true) , $accountInfo['proxy']);
			$commentsLikes = $api->getPostInfo( $postId2 );

			$info = [
				'items'	=>	[
					[
						'comment_count'	=>	$commentsLikes['comments'],
						'like_count'	=>	$commentsLikes['likes']
					]
				]
			];
		}
		else
		{
			$ig = self::login($accountInfo['username'] , $accountInfo['password'] , $accountInfo['proxy']);

			if( !( is_array($ig) && isset($ig['ig']) ) )
			{
				$info = [];
			}
			else
			{
				$ig = $ig['ig'];

				try
				{
					$info = $ig->media->getInfo( $postId )->asArray();
				}
				catch (Exception $e)
				{
					$info = [];
				}
			}
		}

		return [
			'comments'      =>  isset($info['items'][0]['comment_count']) ? (int)$info['items'][0]['comment_count'] : 0,
			'like'          =>  isset($info['items'][0]['like_count']) ? (int)$info['items'][0]['like_count'] : 0,
			'shares'        =>  0,
			'details'       =>  ''
		];
	}

	/**
	 * @param $imageURL
	 * @param $title
	 * @return null|string
	 */
	private static function imageForInstagramStory($imageURL , $title)
	{
		error_reporting(E_ALL);

		$newFileName = null;
		$storyW = 1080 / 1.5;
		$storyH = 1920 / 1.5;

		require_once LIB_DIR . 'PHPImage/PHPImage.php';

		$imageInf = new PHPImage($imageURL);
		$imageWidth = $imageInf->getWidth();
		$imageHeight = $imageInf->getHeight();

		if( $imageWidth * $imageHeight > 3400 * 3400 ) // large file
		{
			return $newFileName;
		}

		$imageInf->cleanup();
		unset($imageInf);

		if( $imageWidth > $imageHeight )
		{
			$w1 = $storyW;
			$h1 = ($w1 / $imageWidth) * $imageHeight;
		}
		else
		{
			$h1 = $storyH;
			$w1 = ( $h1 / $imageHeight ) * $imageWidth;
		}

		$image = new PHPImage();
		$image->initialiseCanvas($storyW , $storyH , 'img' , [99, 110, 114 , 0]);

		$image->draw($imageURL , '50%' , '50%' , $w1 , $h1);


		$titleLength = mb_strlen($title, 'UTF-8');
		$titlePercent = $titleLength - 40;
		if( $titlePercent < 0 )
		{
			$titlePercent = 0;
		}
		else if( $titlePercent > 100 )
		{
			$titlePercent = 100;
		}

		// write title
		$textPadding = 10;
		$textWidth = 660;
		$textHeight = 100 + $titlePercent;
		$iX = floor(($storyW - $textWidth) / 2);
		$iY = 125;

		$image->setFont(LIB_DIR . 'PHPImage/font/Exo2-Regular.ttf');
		$image->rectangle($iX, $iY, $textWidth + $textPadding, $textHeight - $textPadding, array(0, 0, 0), 0.3);

		$image->textBox( $title , array(
			'fontSize' => 30,
			'x' => $iX,
			'y' => $iY,
			'strokeWidth' => 1,
			'strokeColor' => array(99, 110, 114),
			'width' => $textWidth,
			'height' => $textHeight,
			'alignHorizontal' => 'center',
			'alignVertical' => 'center'
		));

		$newFileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'instagram_stroy_tmp_' . rand(10000, 999999) . '_' . microtime(1) . '.png';
		$image->save( $newFileName );


		return $newFileName;
	}

}