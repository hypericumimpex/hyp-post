<?php

define('FS_PLUGIN_DB_PREFIX' , 'fs_');
define('FS_LIB_DIR' , __DIR__ . '/../lib/');
define('FS_PLUGIN_URL' , plugins_url('/' , __DIR__));
define('FS_INCLUDES_DIR' , __DIR__ . '/');
define('FS_VIEWS_DIR' , __DIR__ . '/../views/');
define('FS_API_URL' , 'https://www.fs-poster.com/api/');

function FSresponse($status , $arr = [])
{
	$arr = is_array($arr) ? $arr : ( is_string($arr) ? ['error_msg' => $arr] : [] );

	if( $status )
	{
		$arr['status'] = 'ok';
	}
	else
	{
		$arr['status'] = 'error';
		if( !isset($arr['error_msg']) )
		{
			$arr['error_msg'] = 'Error!';
		}
	}

	print json_encode($arr);
	exit();
}

function FSmodalView( $view , $parameters = [] )
{
	$mn = isset($_POST['_mn']) && is_numeric($_POST['_mn']) && $_POST['_mn'] > 0 ? (int)$_POST['_mn'] : 0;

	ob_start();
	require( plugin_dir_path( __FILE__ ) . '../views/modals/' . $view . '.php' );
	$viewOutput = ob_get_clean();

	FSresponse(true , [
		'html' => htmlspecialchars($viewOutput)
	]);
}

function FSwpDB()
{
	global $wpdb;

	return $wpdb;
}

function FSwpTable( $tbName )
{
	return FSwpDB()->base_prefix . FS_PLUGIN_DB_PREFIX . $tbName;
}

function FSwpFetch( $table , $where = null )
{
	$whereQuery = '';
	$argss = [];
	$where = is_numeric($where) && $where > 0 ? [$where] : $where;
	if( !empty($where) && is_array($where) )
	{
		$whereQuery =  '';

		foreach($where AS $filed => $value)
		{
			$filed = $filed === 0 ? 'id' : $filed;
			$whereQuery .= ($whereQuery == '' ? '' : ' AND ') . $filed.'=%s';
			$argss[] = (string)$value;
		}

		$whereQuery =  ' WHERE ' . $whereQuery;
	}

	if( empty($argss) )
	{
		return FSwpDB()->get_row("SELECT * FROM " . FSwpTable($table) . $whereQuery ,ARRAY_A );
	}

	return FSwpDB()->get_row(
		FSwpDB()->prepare("SELECT * FROM " . FSwpTable($table) . $whereQuery , $argss)
		,ARRAY_A
	);

}

function FSwpFetchAll( $table , $where = null )
{
	$whereQuery = '';
	$argss = [];
	$where = is_numeric($where) && $where > 0 ? [$where] : $where;
	if( !empty($where) && is_array($where) )
	{
		$whereQuery =  '';

		foreach($where AS $filed => $value)
		{
			$filed = $filed === 0 ? 'id' : $filed;
			$whereQuery .= ($whereQuery == '' ? '' : ' AND ') . $filed.'=%s';
			$argss[] = (string)$value;
		}

		$whereQuery =  ' WHERE ' . $whereQuery;
	}

	if( empty($argss) )
	{
		return FSwpDB()->get_results("SELECT * FROM " . FSwpTable($table) . $whereQuery ,ARRAY_A );
	}

	return FSwpDB()->get_results(
		FSwpDB()->prepare("SELECT * FROM " . FSwpTable($table) . $whereQuery , $argss)
		,ARRAY_A
	);

}

function FSspintax( $text )
{
	$text = is_string($text) ? (string)$text : '';
	return preg_replace_callback(
		'/\{(((?>[^\{\}]+)|(?R))*)\}/x',
		function ($text)
		{
			$text = FSspintax( $text[1] );
			$parts = explode('|', $text);

			return $parts[ array_rand($parts) ];
		},
		$text
	);
}

function FScustomizePostLink( $link , $feedId, $postInf = [], $accountInf = [] )
{
	$parameters = [];

	if( get_option('fs_collect_statistics', '1') )
	{
		$parameters[] = 'feed_id=' . $feedId;
	}

	if( get_option('fs_unique_link', '1') == 1 )
	{
		$parameters[] = '_unique_id=' . uniqid();
	}

	$fs_url_additional = get_option('fs_url_additional', '');
	if( !empty( $fs_url_additional ) )
	{
		$postId		= isset($postInf['ID']) ? $postInf['ID'] : 0;
		$postTitle	= isset($postInf['post_title']) ? $postInf['post_title'] : '';
		$network	= isset($accountInf['driver']) ? $accountInf['driver'] : '-';

		$networks = [
			'fb'		=> ['FB', 'Facebook'],
			'twitter'	=> ['TW', 'Twitter'],
			'instagram'	=> ['IG', 'Instagram'],
			'linkedin'	=> ['LN', 'LinkedIn'],
			'vk'		=> ['VK', 'VKontakte'],
			'pinterest'	=> ['PI', 'Pinterest'],
			'reddit'	=> ['RE', 'Reddit'],
			'tumblr'	=> ['TU', 'Tumblr'],
			'ok'		=> ['OK', 'OK.ru'],
			'google_b'	=> ['GB', 'Google My Business'],
			'telegram'	=> ['TG', 'Telegram'],
			'medium'	=> ['ME', 'Medium'],
		];

		$networkCode	= isset($networks[$network]) ? $networks[$network][0] : '';
		$networkName	= isset($networks[$network]) ? $networks[$network][1] : '';

		$userInf		= wp_get_current_user();
		$accountName	= isset( $userInf->user_login ) ? $userInf->user_login : '-';

		$fs_url_additional = str_replace([
			'{post_id}',
			'{post_title}',
			'{network_name}',
			'{network_code}',
			'{account_name}',
			'{site_name}',
			'{uniq_id}'
		], [
			rawurlencode( $postId ),
			rawurlencode( $postTitle ),
			rawurlencode( $networkName ),
			rawurlencode( $networkCode ),
			rawurlencode( $accountName ),
			rawurlencode( get_bloginfo( 'name' ) ),
			uniqid( )
		], $fs_url_additional);

		$parameters[] = $fs_url_additional;
	}

	if( !empty( $parameters ) )
	{
		$link .= strpos($link , '?') !== false ? '' : '?';

		$parameters = implode('&', $parameters);

		$link .= $parameters;
	}

	return $link;
}

function FSgetAccessToken( $nodeType , $nodeId )
{
	if( $nodeType == 'account' )
	{
		$nodeInf			= FSwpFetch('accounts' , $nodeId);
		$nodeProfileId		= $nodeInf['profile_id'];
		$nAccountId			= $nodeProfileId;

		$accessTokenGet		= FSwpFetch('account_access_tokens', ['account_id' => $nodeId]);
		$accessToken		= $accessTokenGet['access_token'];
		$accessTokenSecret	= $accessTokenGet['access_token_secret'];
		$appId				= $accessTokenGet['app_id'];
		$driver				= $nodeInf['driver'];
		$username			= $nodeInf['username'];
		$password			= $nodeInf['password'];
		$proxy				= $nodeInf['proxy'];
		$options			= $nodeInf['options'];

		if( $driver == 'reddit' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			require_once FS_LIB_DIR . 'reddit/Reddit.php';
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}
		else if( $driver == 'ok' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			require_once FS_LIB_DIR . 'ok/OdnoKlassniki.php';
			$accessToken = OdnoKlassniki::refreshToken($accessTokenGet);
		}
		else if( $driver == 'medium' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			require_once FS_LIB_DIR . 'medium/Medium.php';
			$accessToken = Medium::refreshToken($accessTokenGet);
		}
	}
	else
	{
		$nodeInf = FSwpFetch('account_nodes' , $nodeId);

		// get proxy
		$accountInf = FSwpFetch('accounts' , $nodeInf['account_id']);

		if( $nodeInf )
		{
			$nodeInf['proxy'] = $accountInf['proxy'];
		}

		$username	= $accountInf['username'];
		$password	= $accountInf['password'];
		$proxy		= $accountInf['proxy'];
		$options	= $accountInf['options'];
		$nAccountId	= $accountInf['profile_id'];

		$nodeProfileId = $nodeInf['node_id'];
		$driver = $nodeInf['driver'];
		$appId = 0;
		$accessTokenSecret = '';

		if( $driver == 'fb' && $nodeInf['node_type'] == 'ownpage' )
		{
			$accessToken = $nodeInf['access_token'];
		}
		else
		{
			$accessTokenGet = FSwpFetch('account_access_tokens', ['account_id' => $nodeInf['account_id']]);
			$accessToken = $accessTokenGet['access_token'];
			$accessTokenSecret = $accessTokenGet['access_token_secret'];
			$appId = $accessTokenGet['app_id'];
		}

		if( $driver == 'vk' )
		{
			$nodeProfileId = '-' . $nodeProfileId;
		}
	}

	return [
		'node_id'               =>  $nodeProfileId,
		'access_token'          =>  $accessToken,
		'access_token_secret'   =>  $accessTokenSecret,
		'app_id'                =>  $appId,
		'driver'                =>  $driver,
		'info'                  =>  $nodeInf,
		'username'				=>	$username,
		'password'				=>	$password,
		'proxy'					=>	$proxy,
		'options'				=>	$options,
		'account_id'			=>	$nAccountId
	];
}

function FS_post( $key , $default = null , $check_type = null , $whiteList = [] )
{
	$res = isset($_POST[$key]) ? $_POST[$key] : $default;

	if( !is_null( $check_type ) )
	{
		if( $check_type == 'num' || $check_type == 'int' || $check_type == 'integer' )
		{
			$res = is_numeric( $res ) ? (int)$res : $default;
		}
		else if($check_type == 'str' || $check_type == 'string')
		{
			$res = is_string( $res ) ? stripslashes_deep((string)$res) : $default;
		}
		else if($check_type == 'arr' || $check_type == 'array')
		{
			$res = is_array( $res ) ? stripslashes_deep((array)$res) : $default;
		}
		else if($check_type == 'float')
		{
			$res = is_numeric( $res ) ? (float)$res : $default;
		}
	}

	if( !empty( $whiteList ) && !in_array( (string)$res , $whiteList ) )
	{
		$res = $default;
	}

	return $res;
}

function FS_get( $key , $default = null , $check_type = null , $whiteList = [] )
{
	$res = isset($_GET[$key]) ? $_GET[$key] : $default;

	if( !is_null( $check_type ) )
	{
		if( $check_type == 'num' || $check_type == 'int' || $check_type == 'integer' )
		{
			$res = is_numeric( $res ) ? (int)$res : $default;
		}
		else if($check_type == 'str' || $check_type == 'string')
		{
			$res = is_string( $res ) ? (string)$res : $default;
		}
		else if($check_type == 'arr' || $check_type == 'array')
		{
			$res = is_array( $res ) ? (array)$res : $default;
		}
		else if($check_type == 'float')
		{
			$res = is_numeric( $res ) ? (float)$res : $default;
		}
	}

	if( !empty( $whiteList ) && !in_array( (string)$res , $whiteList ) )
	{
		$res = $default;
	}

	return $res;
}

function FSregisterSession()
{
	if( !session_id() )
	{
		session_start();
	}
}

function FSend_session()
{
	session_destroy();
}

function FScheckPermission( $p )
{
	$permissions = ['public_profile' , 'publish_actions' , 'manage_pages' , 'publish_pages' , 'user_managed_groups' , 'pages_show_list'];

	$p2 = [];
	foreach($p['data'] AS $pName)
	{
		$p2[] = $pName['permission'];
	}

	$not = [];
	foreach($permissions AS $pName)
	{
		if( !in_array($pName , $p2) )
		{
			$not[] = esc_html($pName);
		}
	}

	if( !empty( $not ) )
	{
		return esc_html__('This app does not include certain permissions!' , 'fs-poster') . ' ( '.implode(' , ' , $not).' )';
	}

	return true;
}

function FSprofilePic($info , $w = 40 , $h = 40)
{
	if( !isset( $info['driver'] ) )
		return '';

	if( empty($info) )
	{
		return plugin_dir_url(__FILE__) . '../images/no-photo.png';
	}

	if( is_array($info) && key_exists('cover' , $info) ) // nodes
	{
		if( !empty($info['cover']) )
		{
			return $info['cover'];
		}
		else if( $info['driver'] == 'fb' )
		{
			return "https://graph.facebook.com/".esc_html($info['node_id'])."/picture?redirect=1&height={$h}&width={$w}&type=normal";
		}
		else if( $info['driver'] == 'tumblr' )
		{
			return "https://api.tumblr.com/v2/blog/".esc_html($info['node_id'])."/avatar/" . ($w > $h ? $w : $h);
		}
		else if( $info['driver'] == 'reddit' )
		{
			return "https://www.redditstatic.com/avatars/avatar_default_10_25B79F.png";
		}
		else if( $info['driver'] == 'google_b' )
		{
			return "https://ssl.gstatic.com/images/branding/product/2x/google_my_business_32dp.png";
		}
		else if( $info['driver'] == 'telegram' )
		{
			return plugin_dir_url(__FILE__).'../images/telegram.svg';
		}
	}
	else if( $info['driver'] == 'fb' )
	{
		return "https://graph.facebook.com/".esc_html($info['profile_id'])."/picture?redirect=1&height={$h}&width={$w}&type=normal";
	}
	else if( $info['driver'] == 'twitter' )
	{
		require_once __DIR__ . '/../lib/twitter/autoload.php';
		static $twitterAppInfo;

		if( is_null($twitterAppInfo) )
		{
			$twitterAppInfo = FSwpFetch('apps' , ['driver' => 'twitter']);
		}

		$connection = new Abraham\TwitterOAuth\TwitterOAuth($twitterAppInfo['app_key'], $twitterAppInfo['app_secret']);
		$user = $connection->get("users/show", ['screen_name' => $info['username']]);
		return $user->profile_image_url;
	}
	else if( $info['driver'] == 'instagram' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'linkedin' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'vk' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'pinterest' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'reddit' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'tumblr' )
	{
		return "https://api.tumblr.com/v2/blog/".esc_html($info['username'])."/avatar/" . ($w > $h ? $w : $h);
	}
	else if( $info['driver'] == 'ok' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'google_b' )
	{
		return $info['profile_pic'];
	}
	else if( $info['driver'] == 'telegram' )
	{
		return plugin_dir_url(__FILE__).'../images/telegram.svg';
	}
	else if( $info['driver'] == 'medium' )
	{
		return $info['profile_pic'];
	}
	else
	{

	}
}

function FSprofileLink($info)
{
	if( !isset( $info['driver'] ) )
		return '';

	// IF NODE
	if( is_array($info) && key_exists('cover' , $info) ) // nodes
	{
		if( $info['driver'] == 'fb' )
		{
			return "https://fb.com/".esc_html($info['node_id']);
		}
		else if( $info['driver'] == 'vk' )
		{
			return "https://vk.com/".esc_html($info['screen_name']);
		}
		else if( $info['driver'] == 'tumblr' )
		{
			return "https://" . esc_html($info['screen_name']) . ".tumblr.com";
		}
		else if( $info['driver'] == 'linkedin' )
		{
			return "https://www.linkedin.com/company/" . esc_html($info['node_id']);
		}
		else if( $info['driver'] == 'ok' )
		{
			return "https://ok.ru/group/" . esc_html($info['node_id']);
		}
		else if( $info['driver'] == 'reddit' )
		{
			return "https://www.reddit.com/r/" . esc_html($info['screen_name']);
		}
		else if( $info['driver'] == 'google_b' )
		{
			return "https://business.google.com/posts/l/" . esc_html($info['node_id']);
		}
		else if( $info['driver'] == 'telegram' )
		{
			return "http://t.me/" . esc_html($info['screen_name']);
		}
		else if( $info['driver'] == 'pinterest' )
		{
			return "https://www.pinterest.com/" . esc_html($info['screen_name']);
		}
		else if( $info['driver'] == 'medium' )
		{
			return "https://medium.com/" . esc_html($info['screen_name']);
		}

		return '';
	}

	if( $info['driver'] == 'fb' )
	{
		return "https://fb.com/".esc_html($info['profile_id']);
	}
	else if( $info['driver'] == 'twitter' )
	{
		return "https://twitter.com/".esc_html($info['username']);
	}
	else if( $info['driver'] == 'instagram' )
	{
		return "https://instagram.com/".esc_html($info['username']);
	}
	else if( $info['driver'] == 'linkedin' )
	{
		return "https://www.linkedin.com/in/".esc_html(str_replace(['https://www.linkedin.com/in/', 'http://www.linkedin.com/in/'] , '' , $info['username']));
	}
	else if( $info['driver'] == 'vk' )
	{
		return "https://vk.com/id" . esc_html($info['profile_id']);
	}
	else if( $info['driver'] == 'pinterest' )
	{
		return "https://www.pinterest.com/" . esc_html($info['username']);
	}
	else if( $info['driver'] == 'reddit' )
	{
		return "https://www.reddit.com/u/" . esc_html($info['username']);
	}
	else if( $info['driver'] == 'tumblr' )
	{
		return "https://" . esc_html($info['username']) . ".tumblr.com";
	}
	else if( $info['driver'] == 'ok' )
	{
		return 'https://ok.ru/profile/'.urlencode($info['profile_id']);
	}
	else if( $info['driver'] == 'google_b' )
	{
		return 'https://business.google.com/locations';
	}
	else if( $info['driver'] == 'telegram' )
	{
		return "https://t.me/" . esc_html($info['username']);
	}
	else if( $info['driver'] == 'medium' )
	{
		return "https://medium.com/@" . esc_html($info['username']);
	}
	else
	{

	}
}

function FSpostLink( $postId , $driver , $username = '', $feedType = '' )
{
	if( $driver == 'fb' )
	{
		return 'https://fb.com/' . $postId;
	}
	else if( $driver == 'twitter' )
	{
		return 'https://twitter.com/statuses/' . $postId;
	}
	else if( $driver == 'instagram' )
	{
		if( $feedType == 'story' )
		{
			return 'https://www.instagram.com/stories/' . $username . '/';
		}
		else
		{
			return 'https://www.instagram.com/p/' . $postId . '/';
		}
	}
	else if( $driver == 'linkedin' )
	{
		return 'https://www.linkedin.com/feed/update/urn:li:activity:' . $postId . '/';
		//return 'https://www.linkedin.com/updates?topic=' . $postId;
	}
	else if( $driver == 'vk' )
	{
		return 'https://vk.com/wall' . $postId;
	}
	else if( $driver == 'pinterest' )
	{
		return 'https://www.pinterest.com/pin/' . $postId;
	}
	else if( $driver == 'reddit' )
	{
		return 'https://www.reddit.com/' . $postId;
	}
	else if( $driver == 'tumblr' )
	{
		return 'https://'.$username.'.tumblr.com/post/' . $postId;
	}
	else if( $driver == 'ok' )
	{
		if( strpos( $postId , 'topic' ) !== false )
		{
			return 'https://ok.ru/group/' . $postId;
		}
		else
		{
			return 'https://ok.ru/profile/' . $postId;
		}
	}
	else if( $driver == 'google_b' )
	{
		return 'https://business.google.com/posts/l/' . esc_html($username);
	}
	else if( $driver == 'telegram' )
	{
		return "http://t.me/" . esc_html($username);
	}
	else if( $driver == 'medium' )
	{
		return "https://medium.com/p/" . esc_html($postId);
	}
}

function FScutText( $text , $n = 35 )
{
	return mb_strlen($text , 'UTF-8') > $n ? mb_substr($text , 0 , $n , 'UTF-8') . '...' : $text;
}

function FSappIcon( $appInfo )
{
	if( $appInfo['driver'] == 'fb' )
	{
		return "https://graph.facebook.com/".esc_html($appInfo['app_id'])."/picture?redirect=1&height=40&width=40&type=small";
	}
	else
	{
		return FS_PLUGIN_URL . 'images/app_icon.svg';
	}
}

function FSgetProductPrice( $productInf, $getType = '' )
{
	$productRegularPrice = '';
	$productSalePrice = '';
	$productId = $productInf['post_type'] == 'product_variation' ? $productInf['post_parent'] : $productInf['ID'];

	if( ($productInf['post_type'] == 'product' || $productInf['post_type'] == 'product_variation') && function_exists('wc_get_product') )
	{
		$product = wc_get_product( $productId );

		if( $product->is_type( 'variable' ) )
		{
			$variation_id			=	$product->get_children();
			$variable_product		=	new WC_Product_Variation( reset($variation_id) );

			$productRegularPrice	=	$variable_product->get_regular_price();
			$productSalePrice		=	$variable_product->get_sale_price();
		}
		else //else if ( $product->is_type( 'simple' ) )
		{
			$productRegularPrice = $product->get_regular_price();
			$productSalePrice = $product->get_sale_price();
		}
	}

	if( empty($productRegularPrice) && $productSalePrice > $productRegularPrice )
	{
		$productRegularPrice = $productSalePrice;
	}

	if( $getType == 'price' )
	{
		return empty($productSalePrice) ? $productRegularPrice : $productSalePrice;
	}
	else if( $getType == 'regular' )
	{
		return $productRegularPrice;
	}
	else if( $getType == 'sale' )
	{
		return $productSalePrice;
	}
	else
	{
		return [
			'regular'	=>	$productRegularPrice,
			'sale'		=>	$productSalePrice
		];
	}
}

function FSreplaceTags($message , $postInf , $link , $shortLink)
{
	$message = preg_replace_callback('/\{content_short_?([0-9]+)?\}/' , function($n) use( $postInf )
	{
		if( isset($n[1]) && is_numeric($n[1]) )
		{
			$cut = $n[1];
		}
		else
		{
			$cut = 40;
		}

		return FScutText(strip_tags( $postInf['post_content'] ) , $cut );
	} , $message);

	// custom fields
	$message = preg_replace_callback('/\{cf_(.+)\}/iU' , function($n) use( $postInf )
	{
		$customField = isset($n[1]) ? $n[1] : '';

		return get_post_meta($postInf['ID'], $customField, true);
	} , $message);


	$getPrice = FSgetProductPrice($postInf);

	$productRegularPrice = $getPrice['regular'];
	$productSalePrice = $getPrice['sale'];

	// featured image
	$mediaId = get_post_thumbnail_id($postInf['ID']);
	if( empty($mediaId) )
	{
		$media = get_attached_media( 'image' , $postInf['ID']);
		$first = reset($media);
		$mediaId = isset($first->ID) ? $first->ID : 0;
	}

	$featuredImage = $mediaId > 0 ? wp_get_attachment_url($mediaId) : '';

	return str_replace([
		'{id}' ,
		'{title}' ,
		'{content_full}' ,
		'{link}' ,
		'{short_link}' ,
		'{product_regular_price}',
		'{product_sale_price}',
		'{uniq_id}',
		'{tags}',
		'{categories}',
		'{excerpt}',
		'{author}',
		'{featured_image_url}'
	] , [
		$postInf['ID'] ,
		strip_tags( $postInf['post_title'] ) ,
		strip_tags( $postInf['post_content'] ) ,
		$link ,
		$shortLink ,
		$productRegularPrice ,
		$productSalePrice ,
		uniqid(),
		FSgetPostTags( $postInf ),
		FSgetPostCats( $postInf ),
		$postInf['post_excerpt'],
		get_the_author_meta( 'display_name', $postInf['post_author'] ),
		$featuredImage
	] , $message);
}

function standartFSAppRedirectURL($sn)
{
	$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

	return FS_API_URL . '?purchase_code=' . $fsPurchaseKey . '&domain=' . site_url() . '&sn=' . $sn . '&r_url=' .urlencode(site_url() . '/?fs_app_redirect=1&sn=' . $sn);
}

function FSgetPostTags( $postInf )
{
	if( get_post_type( $postInf['ID'] ) == 'product' )
	{
		$tags = wp_get_post_terms( $postInf['ID'] ,'product_tag' );
	}
	else if( get_post_type( $postInf['ID'] ) == 'product_variation' )
	{
		$tags = wp_get_post_terms( $postInf['post_parent'] ,'product_tag' );
	}
	else
	{
		$tags = wp_get_post_tags( $postInf['ID'] );
	}


	$tagsString = [];
	foreach( $tags AS $tagInf )
	{
		$tagsString[] = '#' . preg_replace("/[\!\@\#\$\%\^\&\*\(\)\=\+\{\}\[\]\'\"\,\>\/\?\;\:\\\\\s]/" , "" , $tagInf->name);
	}
	$tagsString = implode(' ' , $tagsString);

	return $tagsString;
}

function FSgetPostCatsArr( $postId )
{
	if( get_post_type($postId) == 'product' )
	{
		return wp_get_post_terms( $postId ,'product_cat' );
	}
	else
	{
		return get_the_category( $postId );
	}
}

function FSgetPostCats( $postInf )
{
	if( get_post_type($postInf['ID']) == 'product' )
	{
		$cats = wp_get_post_terms( $postInf['ID'] ,'product_cat' );
	}
	else if( get_post_type($postInf['ID']) == 'product_variation' )
	{
		$cats = wp_get_post_terms( $postInf['post_parent'] ,'product_cat' );
	}
	else
	{
		$cats = get_the_category( $postInf['ID'] );
	}

	$catsString = [];
	foreach( $cats AS $catInf )
	{
		$catsString[] = '#' . preg_replace("/[\!\@\#\$\%\^\&\*\(\)\=\+\{\}\[\]\'\"\,\>\/\?\;\:\\\\\s]/" , "" , $catInf->name);
	}
	$catsString = implode(' ' , $catsString);


	return $catsString;
}

function FSshortenerURL( $url )
{
	if( !get_option('fs_url_shortener', '0') )
	{
		return $url;
	}

	if( get_option('fs_shortener_service') == 'tinyurl' )
	{
		return FSshortURLtinyurl( $url );
	}
	else if( get_option('fs_shortener_service') == 'bitly' )
	{
		return FSshortURLbitly( $url );
	}

	return $url;
}

function FSshortURLtinyurl( $url )
{
	if( empty( $url ) )
	{
		return $url;
	}

	require_once FS_LIB_DIR . 'FSCurl.php';

	$data = FSCurl::getURL('http://tinyurl.com/api-create.php?url=' . $url);

	return $data;
}

function FSshortURLbitly( $url )
{
	$params = array();

	$params['access_token'] = get_option('fs_url_short_access_token_bitly');

	if( empty($params['access_token']) )
	{
		return $url;
	}

	$params['longUrl'] = $url;
	require_once FS_LIB_DIR . 'bitly.php';

	$results = bitly_get('shorten', $params);

	return isset($results['data']['url']) && !empty($results['data']['url']) ? $results['data']['url'] : $url;
}

function FScheckRequirments( $response = true )
{
	if( !ini_get('allow_url_fopen') )
	{
		$errMsg = esc_html__('"allow_url_fopen" disabled in your php.ini settings! Please activate it and try again!' , 'fs-poster');

		if( $response )
		{
			FSresponse(false , $errMsg );
		}
		else
		{
			return [ false, $errMsg ];
		}
	}

	return [ true ];
}

function FSgetVersion()
{
	$plugin_data = get_file_data(FS_LIB_DIR . '/../init.php' , array('Version' => 'Version') , false);

	return isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
}

function FSgetInstalledVersion()
{
	$ver = get_option('fs_poster_plugin_installed' , '1.0.0');

	return ( $ver === '1' || empty($ver) ) ? '1.0.0' : $ver;
}

function FSscheduleNextPostFilters( $scheduleInf )
{
	$scheduleId = $scheduleInf['id'];

	/* Post type filter */
	$_postTypeFilter = $scheduleInf['post_type_filter'];

	$allowedPostTypes = explode('|', get_option('fs_allowed_post_types', ''));
	if( !in_array( $_postTypeFilter, $allowedPostTypes ) )
	{
		$_postTypeFilter = '';
	}

	$_postTypeFilter = esc_sql( $_postTypeFilter );

	if( !empty($_postTypeFilter) )
	{
		$postTypeFilter = "AND post_type='" . $_postTypeFilter . "'";
	}
	else
	{
		$postTypes = "'" . implode("','" , array_map('esc_sql', $allowedPostTypes)) . "'";

		$postTypeFilter = "AND post_type IN ({$postTypes})";
	}
	/* /End of post type filer */

	/* Categories filter */
	$categoriesArr = explode('|' , $scheduleInf['category_filter']);
	$categoriesArrNew = [];
	foreach( $categoriesArr AS $categ )
	{
		if( is_numeric($categ) && $categ > 0 )
		{
			$categoriesArrNew[] = (int)$categ;
		}
	}
	$categoriesArr = $categoriesArrNew;
	unset($categoriesArrNew);

	if( empty($categoriesArr) )
	{
		$categoriesFilter = '';
	}
	else
	{
		$categoriesFilter = " AND id IN (SELECT object_id FROM `".FSwpDB()->base_prefix."term_relationships` WHERE term_taxonomy_id IN ('" . implode("' , '" , $categoriesArr ) . "') ) ";
	}
	/* / End of Categories filter */


	/* post_date_filter */
	switch( $scheduleInf['post_date_filter'] )
	{
		case "this_week":
			$week = current_time('w');
			$week = $week == 0 ? 7 : $week;

			$startDateFilter = date('Y-m-d 00:00' , strtotime('-'.($week-1).' day'));
			$endDateFilter = date('Y-m-d 23:59');
			break;
		case "previously_week":
			$week = current_time('w');
			$week = $week == 0 ? 7 : $week;
			$week += 7;

			$startDateFilter = date('Y-m-d 00:00' , strtotime('-'.($week-1).' day'));
			$endDateFilter = date('Y-m-d 23:59' , strtotime('-'.($week-7).' day'));
			break;
		case "this_month":
			$startDateFilter = current_time('Y-m-01 00:00');
			$endDateFilter = current_time('Y-m-t 23:59');
			break;
		case "previously_month":
			$startDateFilter = date('Y-m-01 00:00' , strtotime('-1 month'));
			$endDateFilter = date('Y-m-t 23:59' , strtotime('-1 month'));
			break;
		case "this_year":
			$startDateFilter = current_time('Y-01-01 00:00');
			$endDateFilter = current_time('Y-12-31 23:59');
			break;
		case "last_30_days":
			$startDateFilter = date('Y-m-d 00:00' , strtotime('-30 day'));
			$endDateFilter = date('Y-m-d 23:59');
			break;
		case "last_60_days":
			$startDateFilter = date('Y-m-d 00:00' , strtotime('-60 day'));
			$endDateFilter = date('Y-m-d 23:59');
			break;
	}

	$dateFilter = "";

	if( isset($startDateFilter) && isset($endDateFilter) )
	{
		$dateFilter = " AND post_date BETWEEN '{$startDateFilter}' AND '{$endDateFilter}'";
	}
	/* End of post_date_filter */

	/* Filter by id */
	$postIDs = explode(',' , $scheduleInf['post_ids']);
	$postIDFilter = [];
	foreach( $postIDs AS $postId1 )
	{
		if( is_numeric($postId1) && $postId1 > 0 )
		{
			$postIDFilter[] = (int)$postId1;
		}
	}

	if( empty($postIDFilter) )
	{
		$postIDFilter = '';
	}
	else
	{
		$postIDFilter = " AND id IN ('" . implode("','" , $postIDFilter) . "') ";
		$postTypeFilter = '';
	}

	/* End ofid filter */

	/* post_sort */
	$sortQuery = '';
	switch( $scheduleInf['post_sort'] )
	{
		case "random":
			$sortQuery = 'ORDER BY RAND()';
			break;
		case "random2":
			$sortQuery = ' AND id NOT IN (SELECT post_id FROM `'.FSwpTable('feeds')."` WHERE schedule_id='" . (int)$scheduleId . "') ORDER BY RAND()";
			break;
		case "old_first":
			$getLastSharedPostId = FSwpDB()->get_row("SELECT post_id FROM `".FSwpTable('feeds')."` WHERE schedule_id='".(int)$scheduleId."' ORDER BY id DESC LIMIT 1" , ARRAY_A);
			if( $getLastSharedPostId )
			{
				$sortQuery = " AND id>'" . (int)$getLastSharedPostId['post_id'] . "' ";
			}

			$sortQuery .= 'ORDER BY id ASC';
			break;
		case "new_first":
			$getLastSharedPostId = FSwpDB()->get_row("SELECT post_id FROM `".FSwpTable('feeds')."` WHERE schedule_id='".(int)$scheduleId."' ORDER BY id DESC LIMIT 1" , ARRAY_A);
			if( $getLastSharedPostId )
			{
				$sortQuery = " AND id<'" . (int)$getLastSharedPostId['post_id'] . "' ";
			}

			$sortQuery = 'ORDER BY id DESC';
			break;
	}

	return "{$postIDFilter} {$postTypeFilter} {$categoriesFilter} {$dateFilter} {$sortQuery}";
}

function fsDebug()
{
	error_reporting(E_ALL);
	ini_set('display_errors' , 'on');
}

function FSsendTime()
{
	$sendTime = current_time('timestamp');

	if( (int)get_option('fs_share_timer', '0') > 0 )
	{
		$sendTime += (int)get_option('fs_share_timer', '0') * 60;
	}

	return date('Y-m-d H:i:s' , $sendTime);
}

function fsPosterPluginRemove()
{
	$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

	$checkPurchaseCodeURL = FS_API_URL . "api.php?act=delete&purchase_code=" . urlencode($fsPurchaseKey) . "&domain=" . site_url();

	$result2 = file_get_contents($checkPurchaseCodeURL);

	// drop tables...
	$fsTables = [
		'account_access_tokens',
		'account_node_status',
		'account_nodes',
		'account_sessions',
		'account_status',
		'accounts',
		'apps',
		'feeds',
		'schedules'
	];

	foreach( $fsTables AS $tableName )
	{
		FSwpDB()->query("DROP TABLE IF EXISTS `" . FSwpTable($tableName) . "`");
	}

	// delete options...
	$fsOptions = [
		'fs_allowed_post_types',
		'fs_facebook_posting_type',
		'fs_hide_menu_for',
		'fs_keep_logs',
		'fs_ok_posting_type',
		'fs_poster_plugin_installed',
		'fs_poster_plugin_purchase_key',
		'fs_share_on_background',
		'fs_share_timer',
		'fs_twitter_auto_cut_tweets',
		'fs_twitter_posting_type',
		'fs_vk_upload_image',
		'fs_instagram_post_in_type',
		'fs_load_groups',
		'fs_load_liked_pages',
		'fs_load_own_pages',
		'fs_max_groups_limit',
		'fs_max_liked_pages_limit',
		'fs_post_interval',
		'fs_post_interval_type',
		'fs_post_text_message_fb',
		'fs_post_text_message_google',
		'fs_post_text_message_instagram',
		'fs_post_text_message_linkedin',
		'fs_post_text_message_ok',
		'fs_post_text_message_pinterest',
		'fs_post_text_message_reddit',
		'fs_post_text_message_tumblr',
		'fs_google_b_share_as_product',
		'fs_google_b_button_type',
		'fs_post_text_message_twitter',
		'fs_post_text_message_vk',
		'fs_post_text_message_google_b',
		'fs_post_text_message_telegram',
		'fs_telegram_type_of_sharing',
		'fs_shortener_service',
		'fs_unique_link',
		'fs_url_shortener',
		'fs_url_short_access_token_bitly',
		'fs_vk_load_admin_communities',
		'fs_vk_load_members_communities',
		'fs_plugin_alert',
		'fs_plugin_disabled',
		'fs_collect_statistics',
		'fs_url_additional',
		'fs_post_text_message_medium'
	];

	foreach( $fsOptions AS $optionName )
	{
		delete_option($optionName);
	}

	// delete custom post types...
	FSwpDB()->query("DELETE FROM " . FSwpDB()->base_prefix . "posts WHERE post_type='fs_post_tmp' OR post_type='fs_post'");
}

function FSsocialIcon( $driver )
{
	switch( $driver )
	{

		case 'fb':
			return "fab fa-facebook-square";
			break;
		case 'twitter':
		case 'tumblr':
			return "fab fa-{$driver}-square";
			break;

		case 'instagram':
		case 'vk':
		case 'linkedin':
		case 'pinterest':
		case 'telegram':
		case 'reddit':
		case 'medium':
			return "fab fa-{$driver}";
			break;

		case 'ok':
			return "fab fa-odnoklassniki";
			break;
		case 'google_b':
			return "fab fa-google";
			break;

	}

}

function FSstatisticOption()
{
	$getOptions = file_get_contents( FS_API_URL . 'api.php?act=statistic_option' );
	$getOptions = json_decode($getOptions, true);

	$options = '<option selected disabled>Please select</option>';
	foreach ( $getOptions AS $optionName => $optionValue )
	{
		$options .= '<option value="' . htmlspecialchars($optionName) . '">' . htmlspecialchars($optionValue) . '</option>';
	}

	return $options;
}

function FSLocalTime2UTC( $dateTime )
{
	$timezone_string = get_option( 'timezone_string' );
	if ( ! empty( $timezone_string ) )
	{
		$wpTimezoneStr = $timezone_string;
	}
	else
	{
		$offset  = get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = abs( ( $offset - (int) $offset ) * 60 );
		$offset  = sprintf( '%+03d:%02d', $hours, $minutes );

		$wpTimezoneStr = $offset;
	}

	$dateTime = new DateTime( $dateTime, new DateTimeZone( $wpTimezoneStr ) );
	$dateTime->setTimezone( new DateTimeZone( date_default_timezone_get( ) ) );

	return $dateTime->getTimestamp();
}
