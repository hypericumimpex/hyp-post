<?php

trait FSPShare
{
	public function share_post()
	{
		error_reporting(E_ALL);
		ini_set('display_errors','on');
		if( !(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) )
		{
			exit();
		}

		$feedId = (int)$_POST['id'];

		require_once LIB_DIR . 'SocialNetworkPost.php';

		$res = SocialNetworkPost::post($feedId);

		response(true, ['result' => $res]);
	}

	public function share_saved_post()
	{
		$postId = _post('post_id' , '0' , 'num');
		$nodes = _post('nodes' , [] , 'array');
		$background = !(_post('background' , '0' , 'string')) ? 0 : 1;
		$custom_messages = _post('custom_messages' , [] , 'array');

		if( empty($postId) || empty($nodes) || $postId <= 0 )
		{
			response(false);
		}

		$postInterval = (int)get_option('fs_post_interval' , '3');

		$postCats = getPostCatsArr( $postId );
		$insertedCount = 0;

		foreach( $nodes AS $nodeId )
		{
			if( is_string($nodeId) && strpos( $nodeId , ':' ) !== false )
			{
				$parse = explode(':' , $nodeId);
				$driver = $parse[0];
				$nodeType = $parse[1];
				$nodeId = $parse[2];
				$filterType = isset($parse[3]) ? $parse[3] : 'no';
				$categoriesStr = isset($parse[4]) ? $parse[4] : '';

				$categoriesFilter = [];

				if( !empty($categoriesStr) && $filterType != 'no' )
				{
					foreach( explode(',' , $categoriesStr) AS $termId )
					{
						if( is_numeric($termId) && $termId > 0 )
						{
							$categoriesFilter[] = (int)$termId;
						}
					}
				}
				else
				{
					$filterType = 'no';
				}

				if( $filterType == 'in' )
				{
					$checkFilter = false;
					foreach( $postCats AS $termInf )
					{
						if( in_array( $termInf->term_id , $categoriesFilter ) )
						{
							$checkFilter = true;
							break;
						}
					}

					if( !$checkFilter )
					{
						continue;
					}
				}
				else if( $filterType == 'ex' )
				{
					$checkFilter = true;
					foreach( $postCats AS $termInf )
					{
						if( in_array( $termInf->term_id , $categoriesFilter ) )
						{
							$checkFilter = false;
							break;
						}
					}

					if( !$checkFilter )
					{
						continue;
					}
				}

				if( $driver == 'tumblr' && $nodeType == 'account' )
				{
					continue;
				}

				if( !( in_array( $nodeType , ['account' , 'ownpage' , 'page' , 'group' , 'event' , 'blog' , 'company' , 'community', 'subreddit'] ) && is_numeric($nodeId) && $nodeId > 0 ) )
				{
					continue;
				}

				$customMessage = isset($custom_messages[$driver]) && is_string($custom_messages[$driver]) ? $custom_messages[$driver] : null;

				if( $customMessage == get_option( 'fs_post_text_message_' . $driver , "{title}" ) )
				{
					$customMessage = null;
				}

				$insertedCount++;

				if( !($driver == 'instagram' && get_option('fs_instagram_post_in_type', '1') == '2') )
				{
					wpDB()->insert( wpTable('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $postId,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	sendTime()
					]);
				}

				if( $driver == 'instagram' && (get_option('fs_instagram_post_in_type', '1') == '2' || get_option('fs_instagram_post_in_type', '1') == '3') )
				{
					wpDB()->insert( wpTable('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $postId,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'feed_type'             =>  'story',
						'custom_post_message'   =>  $customMessage,
						'send_time'				=>	sendTime()
					]);
				}
			}
		}

		if( !$insertedCount )
		{
			response(false, 'Not found active account or cammunity for shareing this post!');
		}

		if( $background )
		{
			CronJob::setbackgroundTask( $postId );
		}

		response(true);
	}


}