<?php

define('PLUGIN_DB_PREFIX' , 'fs_');
define('LIB_DIR' , __DIR__ . '/../lib/');
define('PLUGIN_URL' , plugins_url('/' , __DIR__));
define('INCLUDES_DIR' , __DIR__ . '/');
define('VIEWS_DIR' , __DIR__ . '/../views/');
define('FS_API_URL' , 'https://www.fs-poster.com/api/');

function calcDays( $date )
{
	$secs = strtotime($date);
	if( $secs < strtotime('01-01-2000') )
		return '~';

	$currentSec = current_time('timestamp');

	return floor(($currentSec - $secs) / 60 / 60 / 24 / 30.4);
}

function response($status , $arr = [])
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

function modalView( $view , $parameters = [] )
{
	$mn = isset($_POST['_mn']) && is_numeric($_POST['_mn']) && $_POST['_mn'] > 0 ? (int)$_POST['_mn'] : 0;

	ob_start();
	require( plugin_dir_path( __FILE__ ) . '../views/modals/' . $view . '.php' );
	$viewOutput = ob_get_clean();

	response(true , [
		'html' => htmlspecialchars($viewOutput)
	]);
}

function wpDB()
{
	global $wpdb;

	return $wpdb;
}

function wpTable( $tbName )
{
	return wpDB()->base_prefix . PLUGIN_DB_PREFIX . $tbName;
}

function wpFetch( $table , $where = null )
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
		return wpDB()->get_row("SELECT * FROM " . wpTable($table) . $whereQuery ,ARRAY_A );
	}

	return wpDB()->get_row(
		wpDB()->prepare("SELECT * FROM " . wpTable($table) . $whereQuery , $argss)
		,ARRAY_A
	);

}

function wpFetchAll( $table , $where = null )
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
		return wpDB()->get_results("SELECT * FROM " . wpTable($table) . $whereQuery ,ARRAY_A );
	}

	return wpDB()->get_results(
		wpDB()->prepare("SELECT * FROM " . wpTable($table) . $whereQuery , $argss)
		,ARRAY_A
	);

}

function spintax( $text )
{
	$text = is_string($text) ? (string)$text : '';
	return preg_replace_callback(
		'/\{(((?>[^\{\}]+)|(?R))*)\}/x',
		function ($text)
		{
			$text = spintax( $text[1] );
			$parts = explode('|', $text);

			return $parts[ array_rand($parts) ];
		},
		$text
	);
}

function customizePostLink( $link , $feedId )
{
	if( get_option('fs_keep_logs', '1') )
	{
		if( strpos($link , '?') !== false )
		{
			$link .= '&feed_id=' . $feedId;
		}
		else
		{
			$link .= '?feed_id=' . $feedId;
		}
	}

	return $link;
}

function getAccessToken( $nodeType , $nodeId )
{
	if( $nodeType == 'account' )
	{
		$nodeInf			= wpFetch('accounts' , $nodeId);
		$nodeProfileId		= $nodeInf['profile_id'];
		$nAccountId			= $nodeProfileId;

		$accessTokenGet		= wpFetch('account_access_tokens', ['account_id' => $nodeId]);
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
			require_once LIB_DIR . 'reddit/Reddit.php';
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}
		else if( $driver == 'ok' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			require_once LIB_DIR . 'ok/OdnoKlassniki.php';
			$accessToken = OdnoKlassniki::refreshToken($accessTokenGet);
		}
	}
	else
	{
		$nodeInf = wpFetch('account_nodes' , $nodeId);

		// get proxy
		$accountInf = wpFetch('accounts' , $nodeInf['account_id']);

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
			$accessTokenGet = wpFetch('account_access_tokens', ['account_id' => $nodeInf['account_id']]);
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

function _post( $key , $default = null , $check_type = null , $whiteList = [] )
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

function _get( $key , $default = null , $check_type = null , $whiteList = [] )
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

function registerSession()
{
	if( !session_id() )
	{
		session_start();
	}
}

function end_session()
{
	session_destroy();
}

function checkPermission( $p )
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

function profilePic($info , $w = 40 , $h = 40)
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
			$twitterAppInfo = wpFetch('apps' , ['driver' => 'twitter']);
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
	else
	{

	}
}

function profileLink($info)
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
	else
	{

	}
}

function postLink( $postId , $driver , $username = '' )
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
		return 'https://www.instagram.com/p/' . $postId;
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
}

function cutText( $text , $n = 35 )
{
	return mb_strlen($text , 'UTF-8') > $n ? mb_substr($text , 0 , $n , 'UTF-8') . '...' : $text;
}

function appIcon( $appInfo )
{
	if( $appInfo['driver'] == 'fb' )
	{
		return "https://graph.facebook.com/".esc_html($appInfo['app_id'])."/picture?redirect=1&height=40&width=40&type=small";
	}
	else
	{
		return PLUGIN_URL . 'images/app_icon.svg';
	}
}

function getProductPrice( $productInf )
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

	return [
		'regular'	=>	$productRegularPrice,
		'sale'		=>	$productSalePrice
	];
}

function replaceTags($message , $postInf , $link , $shortLink)
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

		return cutText(strip_tags( $postInf['post_content'] ) , $cut );
	} , $message);

	$getPrice = getProductPrice($postInf);

	$productRegularPrice = $getPrice['regular'];
	$productSalePrice = $getPrice['sale'];

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
		'{author}'
	] , [
		$postInf['ID'] ,
		strip_tags( $postInf['post_title'] ) ,
		strip_tags( $postInf['post_content'] ) ,
		$link ,
		$shortLink ,
		$productRegularPrice ,
		$productSalePrice ,
		uniqid(),
		getPostTags( $postInf ),
		getPostCats( $postInf ),
		$postInf['post_excerpt'],
		get_the_author_meta( 'display_name', $postInf['post_author'] )
	] , $message);
}

function standartFSAppRedirectURL($sn)
{
	$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

	return FS_API_URL . '?purchase_code=' . $fsPurchaseKey . '&domain=' . site_url() . '&sn=' . $sn . '&r_url=' .urlencode(site_url() . '/?fs_app_redirect=1&sn=' . $sn);
}

function getPostTags( $postInf )
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

function getPostCatsArr( $postId )
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

function getPostCats( $postInf )
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

function shortenerURL( $url )
{
	if( !get_option('fs_url_shortener', '0') )
	{
		return $url;
	}

	if( get_option('fs_shortener_service') == 'tinyurl' )
	{
		return shortURLtinyurl( $url );
	}
	else if( get_option('fs_shortener_service') == 'bitly' )
	{
		return shortURLbitly( $url );
	}

	return $url;
}

function shortURLtinyurl( $url )
{
	if( empty( $url ) )
	{
		return $url;
	}

	require_once LIB_DIR . 'FSCurl.php';

	$data = FSCurl::getURL('http://tinyurl.com/api-create.php?url=' . $url);

	return $data;
}

function shortURLbitly( $url )
{
	$params = array();

	$params['access_token'] = get_option('fs_url_short_access_token_bitly');

	if( empty($params['access_token']) )
	{
		return $url;
	}

	$params['longUrl'] = $url;
	require_once LIB_DIR . 'bitly.php';

	$results = bitly_get('shorten', $params);

	return isset($results['data']['url']) && !empty($results['data']['url']) ? $results['data']['url'] : $url;
}

function checkRequirments()
{
	if( !ini_get('allow_url_fopen') )
	{
		response(false , esc_html__('"allow_url_fopen" disabled in your php.ini settings! Please activate it and try again!' , 'fs-poster'));
	}
}

function getVersion()
{
	$plugin_data = get_file_data(LIB_DIR . '/../init.php' , array('Version' => 'Version') , false);

	return isset($plugin_data['Version']) ? $plugin_data['Version'] : '1.0.0';
}

function getInstalledVersion()
{
	$ver = get_option('fs_poster_plugin_installed' , '1.0.0');

	return ( $ver === '1' || empty($ver) ) ? '1.0.0' : $ver;
}

function scheduleNextPostFilters( $scheduleInf )
{
	$scheduleId = $scheduleInf['id'];

	if( $scheduleInf['status'] != 'active' )
	{
		return false;
	}

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
		$categoriesFilter = " AND id IN (SELECT object_id FROM `".wpDB()->base_prefix."term_relationships` WHERE term_taxonomy_id IN ('" . implode("' , '" , $categoriesArr ) . "') ) ";
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

	$postIDFilter = empty($postIDFilter) ? '' : " AND id IN ('" . implode("','" , $postIDFilter) . "') ";
	/* End ofid filter */

	/* post_sort */
	$sortQuery = '';
	switch( $scheduleInf['post_sort'] )
	{
		case "random":
			$sortQuery = 'ORDER BY RAND()';
			break;
		case "random2":
			$sortQuery = ' AND id NOT IN (SELECT post_id FROM `'.wpTable('feeds')."` WHERE schedule_id='" . (int)$scheduleId . "') ORDER BY RAND()";
			break;
		case "old_first":
			$getLastSharedPostId = wpDB()->get_row("SELECT post_id FROM `".wpTable('feeds')."` WHERE schedule_id='".(int)$scheduleId."' ORDER BY id DESC LIMIT 1" , ARRAY_A);
			if( $getLastSharedPostId )
			{
				$sortQuery = " AND id>'" . (int)$getLastSharedPostId['post_id'] . "' ";
			}

			$sortQuery .= 'ORDER BY id ASC';
			break;
		case "new_first":
			$getLastSharedPostId = wpDB()->get_row("SELECT post_id FROM `".wpTable('feeds')."` WHERE schedule_id='".(int)$scheduleId."' ORDER BY id DESC LIMIT 1" , ARRAY_A);
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

function sendTime()
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
		wpDB()->query("DROP TABLE IF EXISTS `" . wpTable($tableName) . "`");
	}

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
		'fs_use_wp_cron_jobs',
		'fs_vk_upload_image',
		'fs_instagram_post_in_type',
		'fs_load_groups',
		'fs_load_liked_pages',
		'fs_load_own_pages',
		'fs_max_groups_limit',
		'fs_max_liked_pages_limit',
		'fs_post_interval',
		'fs_post_text_message_fb',
		'fs_post_text_message_google',
		'fs_post_text_message_instagram',
		'fs_post_text_message_linkedin',
		'fs_post_text_message_ok',
		'fs_post_text_message_pinterest',
		'fs_post_text_message_reddit',
		'fs_post_text_message_tumblr',
		'fs_post_text_message_twitter',
		'fs_post_text_message_vk',
		'fs_shortener_service',
		'fs_unique_link',
		'fs_url_shortener',
		'fs_url_short_access_token_bitly',
		'fs_vk_load_admin_communities',
		'fs_vk_load_members_communities',
		'fs_plugin_alert',
		'fs_plugin_disabled'
	];

	foreach( $fsOptions AS $optionName )
	{
		delete_option($optionName);
	}
}

function socialIcon( $driver )
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
		case 'reddit':
			return "fab fa-{$driver}";
			break;

		case 'ok':
			return "fab fa-odnoklassniki";
			break;

	}

}