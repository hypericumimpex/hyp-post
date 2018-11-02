<?php

if ( defined( 'DOING_CRON' ) )
{
	set_time_limit(0);
}

CronJob::createScheduleTime();

add_action( 'check_scheduled_posts' , ['CronJob' , 'scheduledPost'] );
add_action( 'check_background_shared_posts' , ['CronJob' , 'sendPostBackground'] );

class CronJob
{

	public static function setScheduleTask( $scheduleId , $interval , $startTime )
	{
		CronJob::createScheduleTime();

		wp_schedule_event( strtotime($startTime), $interval.'hour', 'check_scheduled_posts' , [ $scheduleId ] );
	}

	public static function setbackgroundTask( $postId , $shareOn = null )
	{
		if( is_null( $shareOn ) )
		{
			$shareOn = time();
			if( (int)get_option('fs_share_timer', '0') > 0 )
			{
				$shareOn += (int)get_option('fs_share_timer', '0') * 60;
			}
		}

		wp_schedule_single_event( $shareOn ,  'check_background_shared_posts' , [ $postId ] );
	}

	public static function clearSchedule($scheduleId)
	{
		wp_clear_scheduled_hook( 'check_scheduled_posts' , [ $scheduleId ] );
	}

	public static function createScheduleTime( )
	{
		add_filter('cron_schedules', function($schedules)
		{
			for($h = 1; $h <= 10; $h++)
			{
				$schedules[$h . "hour"] = array(
					'interval'  => 3600 * $h,
					'display'   => 'Once every ' . $h . ' hour'
				);

				$schedules[($h * 24) . "hour"] = array(
					'interval'  => 3600 * $h * 24,
					'display'   => 'Once every ' . ($h*24) . ' hour'
				);
			}

			return $schedules;
		});
	}

	public static function scheduledPost( $scheduleId )
	{
		set_time_limit(0);

		$scheduleInf = wpFetch('schedules' , $scheduleId);

		// if deleted ...
		if( !$scheduleInf )
		{
			return;
		}

		$userId = $scheduleInf['user_id'];

		$filterQuery = scheduleNextPostFilters( $scheduleInf );

		if( $scheduleInf['status'] != 'active' )
		{
			return false;
		}

		/* End post_sort */
		$getRandomPost = wpDB()->get_row("SELECT * FROM ".wpDB()->base_prefix."posts WHERE post_status='publish' {$filterQuery} LIMIT 1" , ARRAY_A);
		$postId = $getRandomPost['ID'];

		if( !($postId > 0) )
		{
			return;
		}

		$getActiveAccounts = wpDB()->get_results(
			wpDB()->prepare("
				SELECT tb2.*, filter_type, categories FROM ".wpTable('account_status')." tb1
				LEFT JOIN ".wpTable('accounts')." tb2 ON tb2.id=tb1.account_id
				WHERE tb1.user_id=%d" , [ $userId ])
			, ARRAY_A
		);

		$getActiveNodes = wpDB()->get_results(
			wpDB()->prepare("
				SELECT tb2.*, filter_type, categories FROM ".wpTable('account_node_status')." tb1
				LEFT JOIN ".wpTable('account_nodes')." tb2 ON tb2.id=tb1.node_id
				WHERE tb1.user_id=%d" , [ $userId ])
			, ARRAY_A
		);

		$feedsArr = [];

		$postInterval = 1;

		$postCats = getPostCatsArr( $post_id );

		foreach( $getActiveAccounts AS $accountInf )
		{
			$filterType = $accountInf['filter_type'];
			$categoriesFilter = explode(',' , $accountInf['categories']);

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

			if( !($accountInf['driver'] == 'instagram' && get_option('instagram_post_in_type', '1') == '2') )
			{
				wpDB()->insert( wpTable('feeds'), [
					'driver'        =>  $accountInf['driver'],
					'post_id'       =>  $postId,
					'node_type'     =>  'account',
					'node_id'       =>  (int)$accountInf['id'],
					'interval'      =>  $postInterval,
					'schedule_id'   =>  $scheduleId
				]);
			}

			$feedsArr[] = wpDB()->insert_id;

			if( $accountInf['driver'] == 'instagram' && (get_option('instagram_post_in_type', '1') == '2' || get_option('instagram_post_in_type', '1') == '3') )
			{
				wpDB()->insert( wpTable('feeds'), [
					'driver'        =>  $accountInf['driver'],
					'post_id'       =>  $postId,
					'node_type'     =>  'account',
					'node_id'       =>  (int)$accountInf['id'],
					'interval'      =>  $postInterval,
					'schedule_id'   =>  $scheduleId,
					'feed_type' =>  'story'
				]);

				$feedsArr[] = wpDB()->insert_id;
			}
		}

		foreach( $getActiveNodes AS $nodeInf )
		{
			$filterType = $nodeInf['filter_type'];
			$categoriesFilter = explode(',' , $nodeInf['categories']);

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

			wpDB()->insert( wpTable('feeds'), [
				'driver'        =>  $nodeInf['driver'],
				'post_id'       =>  $postId,
				'node_type'     =>  $nodeInf['node_type'],
				'node_id'       =>  (int)$nodeInf['id'],
				'interval'      =>  $postInterval,
				'schedule_id'   =>  $scheduleId
			]);

			$feedsArr[] = wpDB()->insert_id;
		}

		require_once LIB_DIR . 'SocialNetworkPost.php';

		foreach ($feedsArr AS $feedId)
		{
			SocialNetworkPost::post( $feedId );
			sleep($postInterval);
		}
	}

	public static function sendPostBackground( $postId )
	{
		set_time_limit(0);
		$getFeeds = wpFetchAll('feeds' , ['post_id' => $postId , 'is_sended' => '0']);
		require_once LIB_DIR . 'SocialNetworkPost.php';

		// for preventing dublicat shares...
		wpDB()->update(wpTable('feeds') , ['is_sended' => '2'] , ['post_id' => $postId , 'is_sended' => '0']);

		foreach ($getFeeds AS $feedInf)
		{
			SocialNetworkPost::post( $feedInf['id'] );

			if( is_numeric($feedInf['interval']) && $feedInf['interval'] > 0 )
			{
				sleep($feedInf['interval']);
			}
		}
	}

}


