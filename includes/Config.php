<?php

define('PLUGIN_DB_PREFIX' , 'fs_');
define('LIB_DIR' , __DIR__ . '/../lib/');
define('PLUGIN_URL' , plugins_url('/' , __DIR__));
define('INCLUDES_DIR' , __DIR__ . '/');
define('VIEWS_DIR' , __DIR__ . '/../views/');
define('FS_API_URL' , 'https://poster.fs-code.com/api/');

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
	if( strpos($link , '?') !== false )
	{
		$link .= '&feed_id=' . $feedId;
	}
	else
	{
		$link .= '?feed_id=' . $feedId;
	}

	return $link;
}

function getAccessToken( $nodeType , $nodeId )
{
	if( $nodeType == 'account' )
	{
		$nodeInf = wpFetch('accounts' , $nodeId);
		$nodeProfileId = $nodeInf['profile_id'];
		$accessTokenGet = wpFetch('account_access_tokens', ['account_id' => $nodeId]);
		$accessToken = $accessTokenGet['access_token'];
		$accessTokenSecret = $accessTokenGet['access_token_secret'];
		$appId = $accessTokenGet['app_id'];
		$driver = $nodeInf['driver'];
		$username = $nodeInf['username'];
		$password = $nodeInf['password'];
		$proxy = $nodeInf['proxy'];

		if( $driver == 'reddit' && (time()+30) > strtotime($accessTokenGet['expires_on']) )
		{
			require_once LIB_DIR . 'reddit/Reddit.php';
			$accessToken = Reddit::refreshToken($accessTokenGet);
		}
	}
	else
	{
		$nodeInf = wpFetch('account_nodes' , $nodeId);

		// get proxy
		$accountInf = wpFetch('accounts' , $nodeInf['account_id']);
		$nodeInf['proxy'] = $accountInf['proxy'];
		$username = $accountInf['username'];
		$password = $accountInf['password'];
		$proxy = $accountInf['proxy'];

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
		'proxy'					=>	$proxy
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

	if( !empty( $whiteList ) && !in_array( $res , $whiteList ) )
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

	if( !empty( $whiteList ) && !in_array( $res , $whiteList ) )
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
	else if( $info['driver'] == 'google' )
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
	else
	{

	}
}

function profileLink($info)
{
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
		else if( $info['driver'] == 'google' )
		{
			return "https://plus.google.com/communities/" . esc_html($info['node_id']);
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
		return "https://www.linkedin.com/in/".esc_html($info['username']);
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
	else if( $info['driver'] == 'google' )
	{
		return 'https://plus.google.com/'.urlencode($info['profile_id']);
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
		return 'https://www.linkedin.com/updates?topic=' . $postId;
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
	else if( $driver == 'google' )
	{
		$postId = explode(':' , $postId);
		return 'https://plus.google.com/' . urlencode($postId[0]) . '/posts/' . urlencode(isset($postId[1]) ? $postId[1] : '-');
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

function gerProductPrice( $productInf )
{
	$productRegularPrice = '';
	$productSalePrice = '';

	if( $productInf['post_type'] == 'product' && function_exists('wc_get_product') )
	{
		$product = wc_get_product( $productInf['ID'] );

		if ( $product->is_type( 'simple' ) )
		{
			$productRegularPrice = $product->get_price();
			$productSalePrice = $product->get_sale_price();
		}
		else if( $product->is_type( 'variable' ) )
		{
			$variation_id			=	$product->get_children();
			$variable_product		=	new WC_Product_Variation( reset($variation_id) );

			$productRegularPrice	=	$variable_product->get_regular_price();
			$productSalePrice		=	$variable_product->get_sale_price();
		}
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

	$getPrice = gerProductPrice($postInf);

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
		'{excerpt}'
	] , [
		$postInf['ID'] ,
		strip_tags( $postInf['post_title'] ) ,
		strip_tags( $postInf['post_content'] ) ,
		$link ,
		$shortLink ,
		$productRegularPrice ,
		$productSalePrice ,
		uniqid(),
		getPostTags( $postInf['ID'] ),
		getPostCats( $postInf['ID'] ),
		$postInf['post_excerpt']
	] , $message);
}

function standartFSAppRedirectURL($sn)
{
	return FS_API_URL . '?sn=' . $sn . '&r_url=' .urlencode(site_url() . '/?fs_app_redirect=1&sn=' . $sn);
}

function getPostTags( $postId )
{
	if( get_post_type( $postId ) == 'product' )
	{
		$tags = wp_get_post_terms( $postId ,'product_tag' );
	}
	else
	{
		$tags = wp_get_post_tags( $postId );
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

function getPostCats( $postId )
{
	if( get_post_type($postId) == 'product' )
	{
		$cats = wp_get_post_terms( $postId ,'product_cat' );
	}
	else
	{
		$cats = get_the_category( $postId );
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
	if( !get_option('url_shortener', '0') )
	{
		return $url;
	}

	if( get_option('shortener_service') == 'tinyurl' )
	{
		return shortURLtinyurl( $url );
	}
	else if( get_option('shortener_service') == 'bitly' )
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

	$params['access_token'] = get_option('url_short_access_token_bitly');

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
		response(false , esc_html__('"allow_url_fopen" disabled in your php.ini settings! Please actiavte id and try again!' , 'fs-poster'));
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
	$interval = $scheduleInf['intarvel'];
	$endDate = strtotime($scheduleInf['end_date']);

	if( strtotime(date('Y-m-d' , ( time() + $interval * 3600 ))) > $endDate )
	{
		wp_clear_scheduled_hook( 'check_scheduled_posts' , [$scheduleId] );
		wpDB()->update(wpTable('schedules') , ['status' => 'finished'] , ['id' => $scheduleId]);
	}

	if( $scheduleInf['status'] != 'active' )
	{
		return false;
	}

	/* Post type filter */
	$postTypes = explode('|' , $scheduleInf['post_type_filter']);
	$postTypesArr = [];

	foreach( $postTypes AS $postType )
	{
		$postType = preg_replace('/[^a-zA-Z0-9\-\_ ]/' , '' , $postType);
		if( empty($postType) )
			continue;

		$postTypesArr[] = esc_sql( $postType );
	}

	if( empty($postTypesArr) )
	{
		$postTypeFilter = '';
	}
	else
	{
		$postTypes = "'" . implode("','" , $postTypesArr) . "'";

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
		$categoriesFilter .= " AND id IN (SELECT object_id FROM `".wpDB()->base_prefix."term_relationships` WHERE term_taxonomy_id IN ('" . implode("' , '" , $categoriesArr ) . "') ) ";
	}
	/* / End of Categories filter */


	/* post_date_filter */
	switch( $scheduleInf['post_date_filter'] )
	{
		case "this_week":
			$week = date('w');
			$week = $week == 0 ? 7 : $week;

			$startDateFilter = date('Y-m-d 00:00' , strtotime('-'.($week-1).' day'));
			$endDateFilter = date('Y-m-d 23:59');
			break;
		case "previously_week":
			$week = date('w');
			$week = $week == 0 ? 7 : $week;
			$week += 7;

			$startDateFilter = date('Y-m-d 00:00' , strtotime('-'.($week-1).' day'));
			$endDateFilter = date('Y-m-d 23:59' , strtotime('-'.($week-7).' day'));
			break;
		case "this_month":
			$startDateFilter = date('Y-m-01 00:00');
			$endDateFilter = date('Y-m-t 23:59');
			break;
		case "previously_month":
			$startDateFilter = date('Y-m-01 00:00' , strtotime('-1 month'));
			$endDateFilter = date('Y-m-t 23:59' , strtotime('-1 month'));
			break;
		case "this_year":
			$startDateFilter = date('Y-01-01 00:00');
			$endDateFilter = date('Y-12-31 23:59');
			break;
	}

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