<?php

require_once __DIR__ . '/../vendor/autoload.php';

class FacebookCookieMethod
{

	private $client;
	private $fb_dtsg;
	private $fbUserId;
	private $fbSess;
	private $proxy;

	public function authorizeFbUser()
	{
		$myInfo = $this->myInfo();

		if( $this->fbUserId !== $myInfo['id'] )
		{
			return false;
		}

		$checkLoginRegistered = FSwpDB()->get_row( FSwpDB()->prepare( "SELECT * FROM ".FSwpTable('accounts')." WHERE user_id=%d AND driver='fb' AND profile_id=%s" , [get_current_user_id() ,$myInfo['id']] ) , ARRAY_A );

		$dataSQL = [
			'user_id'			=>	get_current_user_id(),
			'name'		  		=>	$myInfo['name'],
			'driver'			=>	'fb',
			'profile_id'		=>	$myInfo['id'],
			'proxy'             =>  $this->proxy,
			'options'			=>	$this->fbSess
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

			FSwpDB()->delete( FSwpTable('account_access_tokens')  , ['account_id' => $fbAccId] );

			FSwpDB()->delete( FSwpTable('account_nodes')  , ['account_id' => $fbAccId] );
		}

		$returnStatisticsData = [
			'ownpages'  => false,
			'pages'	 => false,
			'groups'	=> false
		];

		// my pages load
		$loadedOwnPages = [];
		if( get_option('fs_load_own_pages' , 1) == 1 )
		{
			$accountsList = $this->getMyPages();

			$returnStatisticsData['ownpages'] = [];
			foreach($accountsList AS $accountInfo)
			{
				$returnStatisticsData['ownpages'][] = [$accountInfo['id'] , $accountInfo['name']];

				FSwpDB()->insert(FSwpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'fb',
					'account_id'		=>	$fbAccId,
					'node_type'			=>	'ownpage',
					'node_id'			=>	$accountInfo['id'],
					'name'				=>	$accountInfo['name'],
					'access_token'		=>	null,
					'category'			=>	$accountInfo['category'],
					'fan_count'			=>	null
				]);
				$loadedOwnPages[ $accountInfo['id'] ] = true;
			}

		}

		// pages load
		if( get_option('fs_load_liked_pages' , 0) == 1 )
		{
			$limit = get_option('fs_max_liked_pages_limit' , 100);
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = $this->getLikedPages();

			$returnStatisticsData['pages'] = [];
			foreach($accountsList AS $accountInfo)
			{
				if( isset( $loadedOwnPages[$accountInfo['id']] ) )
				{
					continue;
				}

				if( $limit <= 0 )
					break;

				$returnStatisticsData['pages'][] = [$accountInfo['id'] , $accountInfo['name']];

				FSwpDB()->insert(FSwpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'fb',
					'account_id'		=>	$fbAccId,
					'node_type'			=>	'page',
					'node_id'			=>	$accountInfo['id'],
					'name'				=>	$accountInfo['name'],
					'access_token'		=>	null,
					'category'			=>	null,
					'fan_count'			=>	null
				]);

				$limit--;
			}
		}

		// groups load
		if( get_option('fs_load_groups' , 1) == 1 )
		{
			$limit = get_option('fs_max_groups_limit' , 100);
			$limit = $limit >= 0 ? $limit : 0;

			$accountsList = $this->getGroups();

			$returnStatisticsData['groups'] = [];
			foreach($accountsList AS $accountInfo)
			{
				if( $limit <= 0 )
					break;

				$cover = 'https://static.xx.fbcdn.net/rsrc.php/v3/yF/r/MzwrKZOhtIS.png';

				$returnStatisticsData['groups'][] = [$accountInfo['id'] , $accountInfo['name'] , $cover];

				FSwpDB()->insert(FSwpTable('account_nodes') , [
					'user_id'			=>	get_current_user_id(),
					'driver'			=>	'fb',
					'account_id'		=>	$fbAccId,
					'node_type'			=>	'group',
					'node_id'			=>	$accountInfo['id'],
					'name'				=>	$accountInfo['name'],
					'access_token'		=>	null,
					'category'			=>	null,
					'fan_count'			=>	null,
					'cover'				=>	$cover
				]);

				$limit--;
			}
		}

		return [
			'name'		=> $myInfo['name'],
			'email'		=> '-',
			'birthday'	=> '-',
			'id'		=> $myInfo['id'],
			'nodes'		=> $returnStatisticsData
		];
	}

	public function __construct( $fbUserId, $fbSess, $proxy = null )
	{
		$this->fbUserId	= $fbUserId;
		$this->fbSess	= $fbSess;
		$this->proxy	= $proxy;

		$cookies = [
			["Name" => "c_user", "Value"  => $fbUserId,  "Domain" => ".facebook.com","Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => false,"Discard" => false,"HttpOnly" => false,"Priority" => "HIGH"],
			["Name" => "xs","Value" => $fbSess, "Domain" => ".facebook.com","Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => false,"Discard" => false,"HttpOnly" => true,"Priority" => "HIGH"]
		];

		$cookieJar		= new \GuzzleHttp\Cookie\CookieJar(false , $cookies);

		$this->client	= new \GuzzleHttp\Client([
			'cookies' 			=>	$cookieJar,
			'allow_redirects'	=>	[ 'max' => 20 ],
			'proxy'				=>	empty($proxy) ? null : $proxy,
			'verify'			=>	false,
			'http_errors'		=>	false,
			'headers'			=>	['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0']
		]);
	}

	public function getMyPages()
	{
		$myPagesArr = [];

		try
		{
			$result = (string)$this->client->request('GET' , 'https://www.facebook.com/bookmarks/pages?ref=u2u' )->getBody();
		}
		catch( Exception $e )
		{
			$result = '';
		}

		preg_match('/require\:(\[\[\"BookmarkSeeAllEntsSectionController\".+\}\]\]\]\])\,/Ui', $result, $myPages);

		$myPages = preg_replace('/(\,|\{)([a-zA-Z0-9\_]+)\:/', '$1"$2":', $myPages[1]);
		$myPages = json_decode( $myPages, true );
		$myPages = is_array( $myPages ) && isset($myPages[0][3][1]) ? $myPages[0][3][1] : [];

		foreach( $myPages AS $myPageInf )
		{
			$myPagesArr[] = [
				'id'    	=>  isset($myPageInf['id']) ? $myPageInf['id'] : 0,
				'name'  	=>  isset($myPageInf['name']) ? $myPageInf['name'] : '-',
				'category'	=>	''
			];
		}

		return $myPagesArr;
	}

	public function getLikedPages()
	{
		try
		{
			$result = (string)$this->client->request('GET' , 'https://touch.facebook.com/pages/launchpoint/liked_pages/' )->getBody();
		}
		catch( Exception $e )
		{
			$result = '';
		}

		preg_match('/\<ul.*\>(.+)\<\/ul\>/Ui', $result, $likedPagesList);

		if( ! isset( $likedPagesList[1] ) )
			return [];

		preg_match_all('/\<li.+\>(.+)\<\/li\>/Ui', $likedPagesList[1], $likedPages);

		if( ! isset( $likedPages[1] ) )
			return [];

		$likedPagesArr = [];

		foreach( $likedPages[1] AS $myPage )
		{
			preg_match( '/page_id\"\: ?([0-9]+)/i', urldecode($myPage), $pageId );
			$pageId = isset($pageId[1]) ? $pageId[1] : 0;

			preg_match( '/\<div class\=\"_4nwe\"\>(.+)\<\/div\>/Ui', $myPage, $pageName );
			$pageName = isset($pageName[1]) ? $pageName[1] : '???';

			preg_match( '/\<div class\=\"_4nw9\"\>(.+)\<span/Ui', $myPage, $pageCategory );
			$pageCategory = isset($pageCategory[1]) ? $pageCategory[1] : '???';

			$likedPagesArr[] = [
				'id'        =>  $pageId,
				'name'  =>  $pageName,
				'category'  =>  $pageCategory
			];
		}

		return $likedPagesArr;
	}

	public function getGroups()
	{
		try
		{
			$result = (string)$this->client->request('GET' , 'https://m.facebook.com/groups/?seemore' )->getBody();
		}
		catch( Exception $e )
		{
			$result = '';
		}

		preg_match_all('/\<a href\=\"\/groups\/([0-9]+)(?:\?refid\=[0-9]+)?\"\>(.+)\<\/a\>/Ui', $result, $groups);

		if( ! isset( $groups[1] ) )
			return [];

		$groupsArr = [];

		foreach( $groups[1] AS $key => $group )
		{
			$groupsArr[] = [
				'id'        =>  $group,
				'name'      =>  isset($groups[2][$key]) ? $groups[2][$key] : '???'
			];
		}

		return $groupsArr;
	}

	public function getStats( $postId )
	{
		try
		{
			$result = (string)$this->client->request('GET' , 'https://touch.facebook.com/' . $postId )->getBody();
		}
		catch ( Exception $e )
		{
			$result = '';
		}

		preg_match('/\,comment_count\:([0-9]+)\,/i', $result, $comments);
		preg_match('/\,share_count\:([0-9]+)\,/i', $result, $shares);
		preg_match('/\,reactioncount\:([0-9]+)\,/i', $result, $likes);

		return [
			'like'      =>  isset($likes[1]) ? $likes[1] : 0,
			'comments'  =>  isset($comments[1]) ? $comments[1] : 0,
			'shares'    =>  isset($shares[1]) ? $shares[1] : 0,
			'details'   =>  ''
		];
	}

	public function sendPost( $nodeFbId, $nodeType , $type , $message , $preset_id , $link , $images , $video )
	{
		$sendData = [
			'fb_dtsg'   =>  $this->fb_dtsg(),
			'__ajax__'  =>  'true'
		];

		if( empty( $sendData['fb_dtsg'] ) )
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	'Session expired. Please remove your Facebook account from the plugin and add it again! P.S. Don\'t Log out from your Facebook account after adding that . When you Log out your account, session will be destroyed and you can\'t share any post. Best practic is to add your account within Incognito mode!'
			];
		}

		if( $preset_id > 0 && $type == 'status' )
		{
			$sendData['text_format_preset_id'] = $preset_id;
		}
		else if( $type == 'link' )
		{
			$sendData['linkUrl'] = $link;
		}

		$postType = 'form_params';

		if( $type == 'image' )
		{
			$sendData['photo_ids'] = [];

			$images = is_array($images) ? $images : [$images];
			foreach($images AS $imageURL)
			{
				$photoId = $this->uploadPhoto($imageURL, $nodeFbId, $nodeType);

				if( $photoId > 0 )
				{
					$sendData['photo_ids'][ $photoId ] = $photoId;
				}
			}

			if( $nodeType == 'group' )
			{
				$endpoint = "https://touch.facebook.com/_mupload_/composer/?target=" . $nodeFbId;

				$sendData['message'] = $message;
			}
			else if( $nodeType == 'ownpage' )
			{
				$endpoint = 'https://upload.facebook.com/_mupload_/composer/?target=' . $nodeFbId . '&av=' . $nodeFbId;

				$sendData['status']             = $message;
				$sendData['waterfall_id']       = $this->waterfallId();
				$sendData['waterfall_source']   = 'composer_pages_feed';

				$postType = 'multipart';
			}
			else if( $nodeType == 'page' )
			{
				$endpoint = 'https://upload.facebook.com/_mupload_/composer/?target=' . $nodeFbId . '&av=' . $nodeFbId;

				$sendData['message']            = $message;
				$sendData['waterfall_id']       = $this->waterfallId();
				$sendData['waterfall_source']   = 'composer_pages_feed';

				$postType = 'multipart';
			}
			else
			{
				$endpoint = "https://touch.facebook.com/_mupload_/composer/?target=" . $nodeFbId;

				$sendData['status']             = $message;
				$sendData['waterfall_id']       = $this->waterfallId();
				$sendData['waterfall_source']   = 'composer_pages_feed';
				$sendData['privacyx']           = $this->getPrivacyX();
			}

		}
		else if( $type == 'video' )
		{
			$endPoint = 'videos';
			$sendData['file_url']		= $video;
			$sendData['description']	= $message;
		}
		else
		{
			if( $nodeType == 'group' )
			{
				$endpoint = 'https://touch.facebook.com/a/group/post/add/?gid=' . $nodeFbId;
				$sendData['message'] = $message;
			}
			else if( $nodeType == 'ownpage' )
			{
				$endpoint = 'https://touch.facebook.com/a/home.php?av=' . $nodeFbId;
				$sendData['status'] = $message;
			}
			else if( $nodeType == 'page' )
			{
				$endpoint = 'https://touch.facebook.com/a/wall.php?id=' . $nodeFbId;
				$sendData['message'] = $message;
			}
			else
			{
				$endpoint = 'https://touch.facebook.com/a/home.php';

				$sendData['status']     = $message;
				$sendData['target']     = $nodeFbId;
				$sendData['privacyx']   = $this->getPrivacyX();
			}
		}

		if( $postType == 'multipart' )
		{
			$sendData = $this->conertToMultipartArray( $sendData );
		}

		try
		{
			$post = (string)$this->client->request(
				'POST' ,
				$endpoint ,
				[
					$postType	=> $sendData ,
					'headers'	=> [ 'Referer' => 'https://touch.facebook.com/' ]
				]
			)->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		$hasError = $this->parsePostRepsonse( $post );

		if( !$hasError[0] )
		{
			return [
				'status'	=>	'error',
				'error_msg'	=>	$hasError[1]
			];
		}

		if( $nodeType == 'page' )
		{
			try
			{
				$getLastPostId = (string)$this->client->request('GET' , 'https://m.facebook.com/' . $nodeFbId . '/?filter=2' )->getBody();
			}
			catch( Exception $e )
			{
				$getLastPostId = '';
			}

			preg_match('/top_level_post_id\.([0-9]+)/i', $getLastPostId, $postId);
			$postId = isset($postId[1]) ? $postId[1] : 0;
		}
		else if( $nodeType == 'account' && $type == 'image' )
		{
			try
			{
				$getLastPostId = (string)$this->client->request('GET' , 'https://m.facebook.com/' . $nodeFbId . '/' )->getBody();
			}
			catch( Exception $e )
			{
				$getLastPostId = '';
			}

			preg_match('/id\=\"like_([0-9]+)\"/i', $getLastPostId, $postId);
			$postId = isset($postId[1]) ? $postId[1] : 0;
		}
		else
		{
			preg_match('/story_fbid\=([0-9]+)/i', $post, $postId);
			$postId = isset($postId[1]) ? $postId[1] : 0;

			if( !$postId ) // for: group photo upload case
			{
				preg_match('/\&(?:amp\;)?id\=([0-9]+)/i', $post, $postId);
				$postId = isset($postId[1]) ? $postId[1] : 0;

				if( empty( $postId ) )
				{
					try
					{
						$getLastPostId = (string)$this->client->request('GET' , 'https://m.facebook.com/' . $nodeFbId . '/' )->getBody();
					}
					catch( Exception $e )
					{
						$getLastPostId = '';
					}

					preg_match('/id\=\"like_([0-9]+)\"/i', $getLastPostId, $postId);
					$postId = isset($postId[1]) ? $postId[1] : 0;
				}
			}
		}

		return [
			'status'	=>  'ok',
			'id'		=>	$postId
		];
	}

	private function parsePostRepsonse( $response )
	{
		if( empty($response) )
		{
			return [false, 'Error! Response is empty!' ];
		}

		$hasError = preg_match('/\,\"error\"\:([0-9]+)\,/iU', $response, $errCode);

		if( $hasError && (int)$errCode[1] > 0 )
		{
			$errCode = (int)$errCode[1];

			preg_match('/\,\"errorDescription\"\:\"(.+)\"\,/iU', $response, $errMsg);
			$errMsg = isset($errMsg[1]) ? $errMsg[1] : 'Error!';

			return [false, $errMsg . ' ( Error code: ' . $errCode . ')' ];
		}

		return [true];
	}

	private function uploadPhoto( $photo, $target, $targetType )
	{
		$postData = [
			[
				'name'      => 'file1',
				'contents'  => file_get_contents($photo),
				'filename'  => basename( $photo )
			]
		];

		$endpoint = 'https://upload.facebook.com/_mupload_/photo/x/saveunpublished/?thumbnail_width=80&thumbnail_height=80&waterfall_id=' . $this->waterfallId() . '&waterfall_app_name=web_m_touch&waterfall_source=composer_pages_feed&target_id=' . urlencode( $target ) . '&fb_dtsg=' . urlencode( $this->fb_dtsg() ) . '&__ajax__=true';
		if( $targetType == 'ownpage' )
		{
			$endpoint .= '&av=' . urlencode( $target );
		}

		try
		{
			$post = (string)$this->client->request(
				'POST' ,
				$endpoint ,
				[
					'multipart' => $postData ,
					'headers'   => [ 'Referer' => 'https://touch.facebook.com/' ]
				]
			)->getBody();
		}
		catch( Exception $e )
		{
			$post = '';
		}

		preg_match('/\"fbid\"\:\"([0-9]+)/i', $post, $photoId);

		return isset($photoId[1]) ? $photoId[1] : 0;
	}

	private function fb_dtsg()
	{
		if( is_null( $this->fb_dtsg ) )
		{
			try
			{
				$getFbDtsg = (string)$this->client->request('GET' , 'https://m.facebook.com/' )->getBody();
			}
			catch( Exception $e )
			{
				$getFbDtsg = '';
			}

			preg_match('/name\=\"fb_dtsg\" value\=\"(.+)\"/Ui', $getFbDtsg, $fb_dtsg);

			if( !isset($fb_dtsg[1]) )
			{
				$this->fb_dtsg = '';
				//var_dump($getFbDtsg);
				//die;
			}
			else
			{
				$this->fb_dtsg = $fb_dtsg[1];
			}
		}

		return $this->fb_dtsg;
	}

	private function conertToMultipartArray( $arr )
	{
		$newArr = [];

		foreach( $arr AS $name => $value )
		{
			if( is_array( $value ) )
			{
				foreach($value AS $name2 => $value2)
				{
					$newArr[] = [
						'name'      => $name . '[' . $name2 . ']',
						'contents'  => $value2
					];
				}
			}
			else
			{
				$newArr[] = [
					'name'      => $name,
					'contents'  => $value
				];
			}
		}

		return $newArr;
	}

	private function waterfallId()
	{
		return md5(uniqid() . rand(0,99999999) . uniqid());
	}

	private function getPrivacyX()
	{
		$url = 'https://touch.facebook.com/privacy/timeline/saved_custom_audience_selector_dialog/?fb_dtsg=' . $this->fb_dtsg();

		try
		{
			$getData = (string)$this->client->request('GET' , $url )->getBody();
		}
		catch( Exception $e )
		{
			$getData = '';
		}

		preg_match('/\:\"([0-9]+)\"/i', htmlspecialchars_decode( $getData ), $firstPrivacyX);

		return isset($firstPrivacyX[1]) ? $firstPrivacyX[1] : '0';
	}

	public function myInfo()
	{
		try
		{
			$getInfo = (string)$this->client->request('GET' , 'https://touch.facebook.com/' )->getBody();
		}
		catch( Exception $e )
		{
			$getInfo = '';
		}

		preg_match('/\"USER_ID\"\:\"([0-9]+)\"/i', $getInfo, $accountId);
		$accountId = isset($accountId[1]) ? $accountId[1] : '?';

		preg_match('/\"NAME\"\:\"([^\"]+)\"/i', $getInfo, $name);
		$name = json_decode( '"' . ( isset($name[1]) ? $name[1] : '?' ) . '"' );

		return [
			'id'    =>  $accountId,
			'name'  =>  $name
		];
	}

}