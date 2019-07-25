<?php

require_once FS_LIB_DIR . 'vendor/autoload.php';

class FSInstagramApi
{

	/**
	 * @var array
	 */
	private $_cookies;

	/**
	 * @var String
	 */
	private $_proxy;

	/**
	 * @var \GuzzleHttp\Client
	 */
	private $_client;

	public function __construct( $cookies , $proxy )
	{
		$this->_cookies	= $cookies;
		$this->_proxy	= $proxy;

		$cookieJar		= new \GuzzleHttp\Cookie\CookieJar(false , $this->_cookies);

		$this->_client	= new \GuzzleHttp\Client([
			'cookies' 			=>	$cookieJar,
			'allow_redirects'	=>	[ 'max' => 10 ],
			'proxy'				=>	empty($this->_proxy) ? null : $this->_proxy,
			'verify'			=>	false,
			'http_errors'		=>	false,
			'headers'			=>	[
				'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'
			]
		]);
	}

	public function getCsrfToken()
	{
		$cookies = $this->_client->getConfig('cookies')->toArray();
		$csrf = '';

		foreach ( $cookies AS $cookieInf )
		{
			if( $cookieInf['Name'] == 'csrftoken' )
			{
				$csrf = $cookieInf['Value'];
			}
		}

		return $csrf;
	}

	public function uploadPhoto( $photo , $caption )
	{
		$photo = new \InstagramAPI\Media\Photo\InstagramPhoto( $photo );

		try
		{
			$photoDetails = new \InstagramAPI\Media\Photo\PhotoDetails( $photo->getFile() );
		}
		catch (Exception $e)
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> $e->getMessage()
			];
		}

		$uploadId = $this->createUploadId();

		$params = [
			'media_type'				=> '1',
			'upload_media_height'		=> (string) $photoDetails->getHeight(),
			'upload_media_width'		=> (string) $photoDetails->getWidth(),
			'upload_id'					=> $uploadId,
		];

		try
		{
			$response = (string)$this->_client->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId , [
				'headers'	=>	[
					'X-Requested-With'				=>	'XMLHttpRequest',
					'X-CSRFToken'					=>	$this->getCsrfToken(),
					'X-Instagram-Rupload-Params'	=>	json_encode( $params ),
					'X-Entity-Name'					=>	'feed_' . $uploadId,
					'X-Entity-Length'				=>	filesize( $photo->getFile() ),
					'Offset'						=>	'0'
				],
				'body'		=>	fopen($photo->getFile(), 'r')
			])->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$response = json_decode($response , true);

		if( isset($response['status']) && $response['status'] == 'fail' )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($response['message']) && is_string($response['message']) ? $response['message'] : 'Error!'
			];
		}

		if( !isset( $response['upload_id'] ) || $response['upload_id'] != $uploadId )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($response['message']) && is_string($response['message']) ? $response['message'] : 'Error!'
			];
		}

		try
		{
			$result = (string)$this->_client->post( 'https://www.instagram.com/create/configure/' , [
				'form_params' =>
					[
						'upload_id'						=>	$uploadId,
						'caption'						=>	$caption,
						'usertags'						=>	'',
						'custom_accessibility_caption'	=>	'',
						'retry_timeout'					=>	''
					],
				'headers'	=>	[
					'X-Requested-With'	=> 'XMLHttpRequest',
					'X-CSRFToken'		=> $this->getCsrfToken()
				]
			])->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$result = json_decode($result , true);

		if( isset($result['status']) && $result['status'] == 'fail' )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($result['message']) && is_string($result['message']) ? $result['message'] : 'Error!'
			];
		}

		return [
			'status'	=>  'ok',
			'id'		=>	isset($result['media']['code']) ? $result['media']['code'] : '?',
			'id2'		=>	isset($result['media']['id']) ? $result['media']['id'] : '?'
		];
	}

	public function uploadVide( $video , $caption )
	{
		try
		{
			$video = new \InstagramAPI\Media\Video\InstagramVideo($video);
		}
		catch (Exception $e)
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> $e->getMessage()
			];
		}

		try
		{
			$videoDetails = new \InstagramAPI\Media\Video\VideoDetails( $video->getFile() );
		}
		catch (Exception $e)
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> $e->getMessage()
			];
		}

		$uploadId = $this->createUploadId();

		$params = [
			'is_igtv_video'				=> false,
			'media_type'				=> '2',
			'video_format'				=> 'video/mp4',
			'upload_media_height'		=> (string) $videoDetails->getHeight(),
			'upload_media_width'		=> (string) $videoDetails->getWidth(),
			'upload_media_duration_ms'	=> (string) $videoDetails->getDurationInMsec(),
			'upload_id'					=> $uploadId,
		];

		try
		{
			$response = $this->_client->post( 'https://www.instagram.com/rupload_igvideo/feed_' . $uploadId , [
				'headers'	=>	[
					'X-Requested-With'				=>	'XMLHttpRequest',
					'X-CSRFToken'					=>	$this->getCsrfToken(),
					'X-Instagram-Rupload-Params'	=>	json_encode( $params ),
					'X-Entity-Name'					=>	'feed_' . $uploadId,
					'X-Entity-Length'				=>	filesize( $video->getFile() ),
					'Offset'						=>	'0'
				],
				'body'		=>	fopen($video->getFile(), 'r')
			]);
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$response = json_decode($response , true);

		if( isset($response['status']) && $response['status'] == 'fail' )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($response['message']) && is_string($response['message']) ? $response['message'] : 'Error!'
			];
		}

		$videoThumbnail = new \InstagramAPI\Media\Video\InstagramThumbnail( $videoDetails->getFilename( ) );

		$params = [
			'media_type'				=> '2',
			'upload_media_height'		=> (string) $videoDetails->getHeight(),
			'upload_media_width'		=> (string) $videoDetails->getWidth(),
			'upload_id'					=> $uploadId
		];

		try
		{
			$response = $this->_client->post( 'https://www.instagram.com/rupload_igphoto/feed_' . $uploadId , [
				'headers'	=>	[
					'X-Requested-With'				=>	'XMLHttpRequest',
					'X-CSRFToken'					=>	$this->getCsrfToken(),
					'X-Instagram-Rupload-Params'	=>	json_encode( $params ),
					'X-Entity-Name'					=>	'feed_' . $uploadId,
					'X-Entity-Length'				=>	filesize( $videoThumbnail->getFile() ),
					'Offset'						=>	'0'
				],
				'body'		=>	fopen( $videoThumbnail->getFile() , 'r' )
			]);
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		try
		{
			$result = (string)$this->_client->post( 'https://www.instagram.com/create/configure/' , [
				'form_params' =>
					[
						'upload_id'						=>	$uploadId,
						'caption'						=>	$caption,
						'usertags'						=>	'',
						'custom_accessibility_caption'	=>	'',
						'retry_timeout'					=>	'12'
					],
				'headers'	=>	[
					'X-Requested-With'	=> 'XMLHttpRequest',
					'X-CSRFToken'		=> $this->getCsrfToken()
				]
			])->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$result = json_decode($result , true);

		if( isset($result['status']) && $result['status'] == 'fail' )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($result['message']) && is_string($result['message']) ? $result['message'] : 'Error!'
			];
		}

		return [
			'status'	=>  'ok',
			'id'		=>	isset($result['media']['code']) ? $result['media']['code'] : '?',
			'id2'		=>	isset($result['media']['id']) ? $result['media']['id'] : '?'
		];
	}

	public function uploadPhotoStory( $photo , $link )
	{
		$photo = new \InstagramAPI\Media\Photo\InstagramPhoto( $photo );

		try
		{
			$photoDetails = new \InstagramAPI\Media\Photo\PhotoDetails( $photo->getFile() );
		}
		catch (Exception $e)
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> $e->getMessage()
			];
		}

		$uploadId = $this->createUploadId();

		$params = [
			'media_type'				=> '1',
			'upload_media_height'		=> (string) $photoDetails->getHeight(),
			'upload_media_width'		=> (string) $photoDetails->getWidth(),
			'upload_id'					=> $uploadId,
		];

		try
		{
			$response = (string)$this->_client->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId , [
				'headers'	=>	[
					'X-Requested-With'				=>	'XMLHttpRequest',
					'X-CSRFToken'					=>	$this->getCsrfToken(),
					'X-Instagram-Rupload-Params'	=>	json_encode( $params ),
					'X-Entity-Name'					=>	'feed_' . $uploadId,
					'X-Entity-Length'				=>	filesize( $photo->getFile() ),
					'Offset'						=>	'0'
				],
				'body'		=>	fopen($photo->getFile(), 'r')
			])->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$response = json_decode($response , true);

		if( isset($response['status']) && $response['status'] == 'fail' )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($response['message']) && is_string($response['message']) ? $response['message'] : 'Error!'
			];
		}

		if( !isset( $response['upload_id'] ) || $response['upload_id'] != $uploadId )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($response['message']) && is_string($response['message']) ? $response['message'] : 'Error!'
			];
		}

		try
		{
			$result = (string)$this->_client->post( 'https://www.instagram.com/create/configure_to_story/' , [
				'form_params' =>
					[
						'upload_id'						=>	$uploadId,
						'caption'						=>	'',
						'story_cta'						=>	json_encode( [["links" => [["webUri" => $link ]] ]] )
					],
				'headers'	=>	[
					'X-Requested-With'	=> 'XMLHttpRequest',
					'X-CSRFToken'		=> $this->getCsrfToken()
				]
			])->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$result = json_decode($result , true);

		if( isset($result['status']) && $result['status'] == 'fail' )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> !empty($result['message']) && is_string($result['message']) ? $result['message'] : 'Error!'
			];
		}

		return [
			'status'	=>  'ok',
			'id'		=>	isset($result['media']['code']) ? $result['media']['code'] : '?',
			'id2'		=>	isset($result['media']['id']) ? $result['media']['id'] : '?'
		];
	}

	public function getPostInfo( $postId )
	{
		try
		{
			$response = (string)$this->_client->get( 'https://www.instagram.com/p/' . $postId . '/')->getBody();
		}
		catch( Exception $e )
		{
			$response = '';
		}

		preg_match("/\"edge_media_to_comment\"\:\{\"count\"\:([0-9]+)\,/i" , $response , $commentsCount);
		preg_match("/\"edge_media_preview_like\"\:\{\"count\"\:([0-9]+)\,/i" , $response , $likesCount);

		$commentsCount = isset($commentsCount[1]) ? $commentsCount[1] : 0;
		$likesCount = isset($likesCount[1]) ? $likesCount[1] : 0;

		return [
			'likes'		=> $likesCount,
			'comments'	=> $commentsCount
		];
	}

	private function createUploadId()
	{
		return time() . (string)rand(100,999);
	}

	public static function getDetailsBySessId( $sessId, $proxy = '' )
	{
		$cookiesArr = [
			["Name" => "sessionid", "Value" => $sessId, "Domain" => ".instagram.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" =>	true]
		];

		$cookieJar		= new \GuzzleHttp\Cookie\CookieJar(false , $cookiesArr);

		$clientt	= new \GuzzleHttp\Client([
			'cookies' 			=>	$cookieJar,
			'allow_redirects'	=>	[ 'max' => 10 ],
			'verify'			=>	false,
			'http_errors'		=>	false,
			'proxy'				=>	empty($proxy) ? null : $proxy,
			'headers'			=>	[
				'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'
			]
		]);

		$response = (string)$clientt->get( 'https://www.instagram.com/' )->getBody();

		preg_match('/\"username\"\:\"([^\"]+)\"/i', $response, $username);
		$username = isset($username[1]) ? $username[1] : '-';

		preg_match('/\"csrf_token\"\:\"([^\"]+)\"/i', $response, $csrfToken);
		$csrfToken = isset($csrfToken[1]) ? $csrfToken[1] : '-';

		preg_match('/\"id\"\:\"([^\"]+)\"/i', $response, $accountId);
		$accountId = isset($accountId[1]) ? $accountId[1] : '-';

		return [
			'id'		=>	$accountId,
			'csrf'		=>	$csrfToken,
			'username'	=>	$username
		];
	}

}