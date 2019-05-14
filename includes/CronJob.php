<?php

class CronJob
{

	public static function initCronJobs()
	{
		if( !get_option('fs_use_wp_cron_jobs', '1') )
		{
			if( _get('fs-poster-cron-job' , '' , 'string') == '1' )
			{
				add_action( 'init', function ()
				{
					set_time_limit(0);

					self::checkBackgroundPosts();

					self::checkScheduledPosts();

					exit();
				});

			}
		}
		else
		{
			if ( defined( 'DOING_CRON' ) )
			{
				set_time_limit(0);
			}

			self::createScheduleTime();

			add_action( 'check_scheduled_posts' , [self::class , 'scheduledPost'] );
			add_action( 'check_background_shared_posts' , [self::class , 'sendPostBackground'] );
		}
	}

	public static function checkBackgroundPosts()
	{
		$backgroundShare = (int)get_option('fs_share_on_background' , '1');

		if( !$backgroundShare )
			return;

		$sendPosts = wpDB()->get_results( wpDB()->prepare("SELECT * FROM " . wpTable('feeds') . " WHERE send_time<=%s AND is_sended=0", [ current_time('Y-m-d H:i:s') ]) , ARRAY_A );

		require_once LIB_DIR . 'SocialNetworkPost.php';

		foreach ($sendPosts AS $postInf)
		{
			if( get_post_status( $postInf['post_id'] ) != 'publish' )
			{
				continue;
			}

			// prevent dublicates...
			$checkIsSended = wpDB()->get_row( wpDB()->prepare("SELECT is_sended FROM " . wpTable('feeds') . " WHERE id=%d", [ $postInf['id'] ]) , ARRAY_A );
			if( $checkIsSended['is_sended'] != 0 )
			{
				continue;
			}

			wpDB()->update(wpTable('feeds') , [ 'is_sended' => '2' ] , [ 'id' => $postInf['id'] ]);

			SocialNetworkPost::post( $postInf['id'] );

			if( is_numeric($postInf['interval']) && $postInf['interval'] > 0 )
			{
				sleep( (int)$postInf['interval'] );
			}
		}
	}

	public static function checkScheduledPosts()
	{
		$getSchedules = wpDB()->get_results( wpDB()->prepare("SELECT * FROM " . wpTable('schedules') . " WHERE status='active' AND next_execute_time<=%s", [ current_time('Y-m-d H:i:s') ]) , ARRAY_A );

		foreach ( $getSchedules AS $key => $scheduleInf )
		{
			$currentDate = current_time('Y-m-d H:i:s');

			if( $key )
			{
				// check if not sened yet
				$checkSchedule = wpDB()->get_results( wpDB()->prepare("SELECT * FROM " . wpTable('schedules') . " WHERE id=%d AND status='active' AND next_execute_time<=%s", [ (int)$scheduleInf['id'] , current_time('Y-m-d H:i:s') ]) , ARRAY_A );

				if( !$checkSchedule )
				{
					continue;
				}
			}

			wpDB()->query("UPDATE " . wpTable('schedules') . " SET next_execute_time=(next_execute_time + INTERVAL IF( ( TIMESTAMPDIFF( HOUR, next_execute_time, '{$currentDate}' ) / `interval` > 1 ) , (CEIL(TIMESTAMPDIFF( HOUR, next_execute_time, '{$currentDate}' ) / `interval`) * `interval` + 1) , `interval` ) HOUR) WHERE id='" . (int)$scheduleInf['id'] . "'");

			self::scheduledPost( $scheduleInf['id'] );
		}
	}

	public static function setScheduleTask( $scheduleId , $interval , $startTime )
	{
		CronJob::createScheduleTime();

		wp_schedule_event( strtotime($startTime), $interval.'hour', 'check_scheduled_posts' , [ $scheduleId ] );
	}

	public static function setbackgroundTask( $postId , $shareOn = null )
	{
		if( !get_option('fs_use_wp_cron_jobs', '1') )
			return;

		if( is_null( $shareOn ) )
		{
			$shareOn = current_time('timestamp');
			if( (int)get_option('fs_share_timer', '0') > 0 )
			{
				$shareOn += (int)get_option('fs_share_timer', '0') * 60;
			}
			else
			{
				$shareOn += 5;
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

		$interval = $scheduleInf['interval'];
		$endDate = strtotime($scheduleInf['end_date']);

		if( strtotime(date('Y-m-d' , ( current_time('timestamp') + $interval * 3600 ))) > $endDate )
		{
			wp_clear_scheduled_hook( 'check_scheduled_posts' , [$scheduleId] );
			wpDB()->update(wpTable('schedules') , ['status' => 'finished'] , ['id' => $scheduleId]);
		}

		$filterQuery = scheduleNextPostFilters( $scheduleInf );

		if( $scheduleInf['status'] != 'active' )
		{
			return false;
		}

		/* End post_sort */
		$getRandomPost = wpDB()->get_row("SELECT * FROM ".wpDB()->base_prefix."posts WHERE (post_status='publish' OR post_type='attachment') {$filterQuery} LIMIT 1" , ARRAY_A);
		$postId = $getRandomPost['ID'];

		if( !($postId > 0) )
		{
			return;
		}

		if( !empty($scheduleInf['post_ids']) )
		{
			wpDB()->query(wpDB()->prepare("UPDATE ".wpTable('schedules')." SET post_ids=TRIM(BOTH ',' FROM replace(concat(',',post_ids,','), ',%d,',',')), status=IF( post_ids='' , 'finished', status) WHERE id=%d" , [$postId, $scheduleId]));
		}

		$accountsList	= explode(',', $scheduleInf['share_on_accounts']);
		if( !empty($scheduleInf['share_on_accounts']) && is_array( $accountsList ) && !empty( $accountsList ) && count( $accountsList ) > 0 )
		{
			$_accountsList	= [];
			$_nodeList		= [];

			foreach( $accountsList AS $accountN )
			{
				$accountN = explode(':', $accountN);

				if( $accountN[0] == 'account' )
				{
					$_accountsList[] = (int)$accountN[1];
				}
				else
				{
					$_nodeList[] = (int)$accountN[1];
				}
			}

			if( !empty($_accountsList) )
			{
				$getActiveAccounts = wpDB()->get_results(
					wpDB()->prepare("
						SELECT tb1.*, IFNULL(filter_type,'no') AS filter_type, categories
						FROM ".wpTable('accounts')." tb1
						LEFT JOIN ".wpTable('account_status')." tb2 ON tb1.id=tb2.account_id AND tb2.user_id=%d
						WHERE (tb1.is_public=1 OR tb2.id > 0) AND tb1.id in (".implode(',', $_accountsList).")" , [ $userId ])
					, ARRAY_A
				);
			}
			else
			{
				$getActiveAccounts = [];
			}

			if( !empty($_nodeList) )
			{
				$getActiveNodes = wpDB()->get_results(
					wpDB()->prepare("
						SELECT tb1.*, IFNULL(filter_type,'no') AS filter_type, categories
						FROM ".wpTable('account_nodes')." tb1
						LEFT JOIN ".wpTable('account_node_status')." tb2 ON tb1.id=tb2.node_id AND tb2.user_id=%d
						WHERE (tb1.is_public=1 OR tb2.id > 0) AND tb1.id in (".implode(',', $_nodeList).")" , [ $userId ])
					, ARRAY_A
				);
			}
			else
			{
				$getActiveNodes = [];
			}
		}
		else
		{
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
		}

		$customPostMessages = json_decode($scheduleInf['custom_post_message'] , true);
		$customPostMessages = is_array($customPostMessages) ? $customPostMessages : [];

		$feedsArr = [];

		$postInterval = 1;

		$postCats = getPostCatsArr( $postId );

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

			$insertData = [
				'driver'        =>  $accountInf['driver'],
				'post_id'       =>  $postId,
				'node_type'     =>  'account',
				'node_id'       =>  (int)$accountInf['id'],
				'interval'      =>  $postInterval,
				'schedule_id'   =>  $scheduleId,
				'is_sended'		=>	2
			];

			if( isset($customPostMessages[ $accountInf['driver'] ]) )
			{
				$insertData['custom_post_message'] = (string)$customPostMessages[ $accountInf['driver'] ];
			}

			if( !($accountInf['driver'] == 'instagram' && get_option('fs_instagram_post_in_type', '1') == '2') )
			{
				if( wpDB()->insert( wpTable('feeds'), $insertData) )
				{
					$feedsArr[wpDB()->insert_id] = true;
				}
			}

			if( $accountInf['driver'] == 'instagram' && (get_option('fs_instagram_post_in_type', '1') == '2' || get_option('fs_instagram_post_in_type', '1') == '3') )
			{
				$insertData['feed_type'] = 'story';

				if( wpDB()->insert( wpTable('feeds'), $insertData) )
				{
					$feedsArr[wpDB()->insert_id] = true;
				}
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

			$insertData = [
				'driver'        =>  $nodeInf['driver'],
				'post_id'       =>  $postId,
				'node_type'     =>  $nodeInf['node_type'],
				'node_id'       =>  (int)$nodeInf['id'],
				'interval'      =>  $postInterval,
				'schedule_id'   =>  $scheduleId,
				'is_sended'		=>	2
			];

			if( isset($customPostMessages[ $nodeInf['driver'] ]) )
			{
				$insertData['custom_post_message'] = (string)$customPostMessages[ $nodeInf['driver'] ];
			}

			if( wpDB()->insert( wpTable('feeds'), $insertData) )
			{
				$feedsArr[wpDB()->insert_id] = true;
			}
		}

		require_once LIB_DIR . 'SocialNetworkPost.php';

		foreach ($feedsArr AS $feedId => $true)
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

CronJob::initCronJobs();
