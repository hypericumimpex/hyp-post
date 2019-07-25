<?php


trait FSPSchedule
{

	public function schedule_save()
	{
		$title				= FS_post('title' , '' , 'string');
		$start_date			= FS_post('start_date' , '' , 'string');
		$start_time			= FS_post('start_time' , '' , 'string');
		$interval 			= FS_post('interval' , '0' , 'num');
		$share_time 		= FS_post('share_time' , '' , 'string');

		$post_type_filter = FS_post('post_type_filter' , '' , 'string');
		$category_filter = FS_post('category_filter' , [] , 'array');
		$post_sort = FS_post('post_sort' , 'random' , 'string' , ['random', 'random2' , 'old_first' , 'new_first']);
		$post_date_filter = FS_post('post_date_filter' , 'all' , 'string' , ['all' , 'this_week' , 'previously_week' , 'this_month' , 'previously_month' , 'this_year' , 'last_30_days' , 'last_60_days']);

		$custom_messages = FS_post('custom_messages' , '' , 'string');
		$accounts_list = FS_post('accounts_list' , '' , 'string');

		$_custom_messages = [];
		if( !empty( $custom_messages ) )
		{
			$custom_messages = json_decode($custom_messages, true);
			$custom_messages = is_array($custom_messages) ? $custom_messages : [];

			foreach ($custom_messages AS $socialNetwork => $message1 )
			{
				if( in_array( $socialNetwork , ['fb', 'instagram', 'linkedin', 'twitter', 'pinterest', 'vk', 'ok', 'tumblr', 'reddit', 'google_b', 'telegram', 'medium'] ) && is_string( $message1 ) )
				{
					$_custom_messages[ $socialNetwork ] = $message1;
				}
			}
		}
		$_custom_messages = empty( $_custom_messages ) ? null : json_encode( $_custom_messages );

		$_accounts_list = [];
		if( !empty( $accounts_list ) )
		{
			$accounts_list = json_decode($accounts_list, true);
			$accounts_list = is_array($accounts_list) ? $accounts_list : [];

			foreach ($accounts_list AS $socialAccount )
			{
				if( is_string( $socialAccount ) )
				{
					$socialAccount = explode(':' , $socialAccount);
					if( !(count($socialAccount) == 2 && is_numeric($socialAccount[1])) )
						continue;

					$_accounts_list[] = ($socialAccount[0] == 'account' ? 'account' : 'node') . ':' . $socialAccount[1] ;
				}
			}
		}
		$_accounts_list = empty($_accounts_list) ? null : implode(',' , $_accounts_list);

		// sanitize categories array...
		$category_filterNew = [];
		foreach( $category_filter AS $categId )
		{
			if( is_numeric($categId) && $categId > 0 )
			{
				$category_filterNew[] = $categId;
			}
		}
		$category_filter = implode('|' , $category_filterNew);
		unset($category_filterNew);

		// sanitize post types array...
		$allowedPostTypes = explode('|', get_option('fs_allowed_post_types', ''));

		if( !in_array( $post_type_filter , $allowedPostTypes ) )
		{
			$post_type_filter = '';
		}

		if( empty($title) || empty($start_date) || empty($start_time) || !( is_numeric($interval) && $interval > 0 ) )
		{
			FSresponse(false , ['error_msg' => esc_html__('Validation error' , 'fs-poster')]);
		}

		$start_date = date('Y-m-d' , strtotime($start_date));
		$start_time = date('H:i' , strtotime($start_time));

		$cronStartTime = $start_date . ' ' . $start_time;

		FSwpDB()->insert(FSwpTable('schedules') , [
			'title'					=>	$title,
			'start_date'			=>	$start_date,
			'interval'				=>	$interval,
			'status'				=>	'active',
			'insert_date'	 		=>	date('Y-m-d H:i:s'),
			'user_id'				=>	get_current_user_id(),
			'share_time'			=>	$start_time,
			'next_execute_time'		=>	$cronStartTime,

			'post_type_filter'		=>	$post_type_filter,
			'category_filter'		=>	$category_filter,
			'post_sort'				=>	$post_sort,
			'post_date_filter'		=>	$post_date_filter,

			'custom_post_message'	=>	$_custom_messages,
			'share_on_accounts'		=>	$_accounts_list
		]);

		CronJob::setScheduleTask( FSwpDB()->insert_id , $cronStartTime );

		FSresponse(true);
	}

	public function schedule_posts()
	{
		$plan_date			= FS_post('plan_date' , '' , 'string');
		$post_ids_p			= FS_post('post_ids', [], 'array');
		$interval			= FS_post('interval' , '0' , 'num');

		if( !in_array($interval , [1,2,3,4,5,6,7,8,9,10,1*24,2*24,3*24,4*24,5*24,6*24,7*24,8*24,9*24,10*24]) )
		{
			FSresponse(false , esc_html__('Validation error' , 'fs-poster'));
		}

		if( empty($plan_date) )
		{
			FSresponse(false , 'Schedule date is empty!');
		}
		else if( strtotime($plan_date) - (3600 * 24 * 30 * 24) > time() )
		{
			FSresponse(false , 'Plan date or time is not valid!');
		}
		else if( strtotime($plan_date) < current_time('timestamp') )
		{
			FSresponse(false , 'Plan date or time is not valid!<br>Please select Schedule date/time according to your server time. <br>Your server time is: ' . current_time('Y-m-d g:i:s A'));
		}

		$plan_date = date('Y-m-d H:i' , strtotime($plan_date));

		$post_ids = [];

		foreach( $post_ids_p AS $postId )
		{
			if( is_numeric($postId) && $postId > 0 )
			{
				$post_ids[] = (int)$postId;
			}
		}

		if( empty($post_ids) )
		{
			FSresponse(false , 'Please select at least one post.');
		}
		else if( count( $post_ids ) > 75 )
		{
			FSresponse(false , 'Too many post selected! You can select maximum 75 posts!');
		}

		$custom_messages = FS_post('custom_messages' , '' , 'string');
		$accounts_list = FS_post('accounts_list' , '' , 'string');

		$_custom_messages = [];
		if( !empty( $custom_messages ) )
		{
			$custom_messages = json_decode($custom_messages, true);
			$custom_messages = is_array($custom_messages) ? $custom_messages : [];

			foreach ($custom_messages AS $socialNetwork => $message1 )
			{
				if( in_array( $socialNetwork , ['fb', 'instagram', 'linkedin', 'twitter', 'pinterest', 'vk', 'ok', 'tumblr', 'reddit', 'google_b', 'telegram', 'medium'] ) && is_string( $message1 ) )
				{
					$_custom_messages[$socialNetwork] = $message1;
				}
			}
		}
		$_custom_messages = empty($_custom_messages) ? null : json_encode($_custom_messages);

		$_accounts_list = [];
		if( !empty( $accounts_list ) )
		{
			$accounts_list = json_decode($accounts_list, true);
			$accounts_list = is_array($accounts_list) ? $accounts_list : [];

			foreach ($accounts_list AS $socialAccount )
			{
				if( is_string( $socialAccount ) )
				{
					$socialAccount = explode(':' , $socialAccount);
					if( !(count($socialAccount) == 2 && is_numeric($socialAccount[1])) )
						continue;

					$_accounts_list[] = ($socialAccount[0] == 'account' ? 'account' : 'node') . ':' . $socialAccount[1] ;
				}
			}
		}
		$_accounts_list = empty($_accounts_list) ? null : implode(',' , $_accounts_list);

		$postsCount = count($post_ids);

		if( $postsCount == 1 )
		{
			$onePostId = reset($post_ids);
			$onePostInf = get_post( $onePostId, ARRAY_A );

			$title = 'Scheduled post: "' . FScutText( !empty($onePostInf['post_title']) ? $onePostInf['post_title'] : $onePostInf['post_content'] ) . '"';
		}
		else
		{
			$title = 'Schedule ( '.$postsCount.' posts )';
		}

		$post_ids = implode(',' , $post_ids);

		$start_date = date('Y-m-d', strtotime($plan_date));
		$end_date = date('Y-m-d', (strtotime($plan_date) + ($postsCount - 1) * $interval * 3600 ));
		$share_time = date('H:i' , strtotime($plan_date));

		$post_type_filter = '';
		$category_filter = '';
		$post_sort = $postsCount == 1 ? 'new_first' : FS_post('post_sort' , 'old_first' , 'string', ['old_first' , 'random' , 'new_first']);
		$post_date_filter = 'all';

		FSwpDB()->insert(FSwpTable('schedules') , [
			'title'					=>	$title,
			'start_date'			=>	$start_date,
			'end_date'				=>	$end_date,
			'interval'				=>	$interval,
			'status'				=>	'active',
			'insert_date'	 		=>	date('Y-m-d H:i:s'),
			'user_id'				=>	get_current_user_id(),
			'share_time'			=>	$share_time,

			'post_type_filter'		=>	$post_type_filter,
			'category_filter'		=>	$category_filter,
			'post_sort'				=>	$post_sort,
			'post_date_filter'		=>	$post_date_filter,

			'post_ids'				=>	$post_ids,
			'next_execute_time'		=>	$plan_date,

			'custom_post_message'	=>	$_custom_messages,
			'share_on_accounts'		=>	$_accounts_list
		]);

		CronJob::setScheduleTask( FSwpDB()->insert_id , $plan_date );

		FSresponse(true);
	}

	public function delete_schedule()
	{
		$id = FS_post('id' , 0 , 'num');
		if( $id <= 0 )
		{
			FSresponse(false);
		}

		$checkSchedule = FSwpFetch('schedules' , $id);
		if( !$checkSchedule )
		{
			FSresponse(false , esc_html__('Schedule not found!' , 'fs-poster'));
		}
		else if( $checkSchedule['user_id'] != get_current_user_id() )
		{
			FSresponse(false , esc_html__('You do not have a permission to delete this schedule!' , 'fs-poster'));
		}

		FSwpDB()->delete(FSwpTable('schedules') , ['id' => $id]);

		CronJob::clearSchedule($id);

		FSresponse(true);
	}

	public function delete_schedules()
	{
		$ids = FS_post('ids' , [] , 'array');
		if( count($ids) == 0 )
		{
			FSresponse(false , 'No schedule selected!');
		}

		foreach ($ids AS $id)
		{
			if( is_numeric($id) && $id > 0 )
			{
				$checkSchedule = FSwpFetch('schedules' , $id);
				if( !$checkSchedule )
				{
					FSresponse(false , esc_html__('Schedule not found!' , 'fs-poster'));
				}

				else if( $checkSchedule['user_id'] != get_current_user_id() )
				{
					FSresponse(false , esc_html__('You do not have a permission to delete this schedule!' , 'fs-poster'));
				}

				FSwpDB()->delete(FSwpTable('schedules') , ['id' => $id]);

				CronJob::clearSchedule($id);
			}
		}

		FSresponse(true);
	}

	public function schedule_change_status()
	{
		$id = FS_post('id' , 0 , 'num');

		if( $id <= 0 )
		{
			FSresponse(false);
		}

		$checkSchedule = FSwpFetch('schedules' , $id);
		if( !$checkSchedule )
		{
			FSresponse(false , esc_html__('Schedule not found!' , 'fs-poster'));
		}
		else if( $checkSchedule['user_id'] != get_current_user_id() )
		{
			FSresponse(false , esc_html__('You do not have a permission to Pause/Play this schedule!' , 'fs-poster'));
		}

		if( $checkSchedule['status'] != 'paused' && $checkSchedule['status'] != 'active' )
		{
			FSresponse(false , esc_html__('This schedule has finished!' , 'fs-poster'));
		}

		$newStatus = $checkSchedule['status'] == 'active' ? 'paused' : 'active';

		$updateArr = ['status' => $newStatus];

		if( $newStatus == 'paused' )
		{
			wp_clear_scheduled_hook( 'fs_check_scheduled_posts' , [ $id ] );
		}
		else
		{
			$locTime = current_time('timestamp');
			$scheduleStarted = strtotime( $checkSchedule['start_date'] . ' ' . $checkSchedule['share_time'] );

			$dif = $locTime - $scheduleStarted;

			$interval = $checkSchedule['interval'] * 60;

			$nextExecTime = ( $dif % $interval ) === 0 ? $locTime : $locTime + $interval - ( $dif % $interval );

			$updateArr['next_execute_time'] = date('Y-m-d H:i', $nextExecTime);

			CronJob::setScheduleTask( $id , $updateArr['next_execute_time'] );
		}

		FSwpDB()->update(FSwpTable('schedules') , $updateArr , ['id' => $id]);

		FSresponse(true );
	}

	public function schedule_get_calendar()
	{
		$month = (int)FS_post('month' , date('m') , 'num', [1,2,3,4,5,6,7,8,9,10,11,12]);
		$year = (int)FS_post('year' , date('Y') , 'num');

		if( $year > date('Y')+4 || $year < date('Y')-4 )
		{
			FSresponse(false, 'Loooooooooooooooolll :)');
		}

		$firstDate = date('Y-m-01' , strtotime("{$year}-{$month}-01"));
		$lastDate = date('Y-m-t' , strtotime("{$year}-{$month}-01"));
		$myId = (int)get_current_user_id();

		if( strtotime( $firstDate ) < strtotime(date('Y-m-d')) )
		{
			$firstDate = date('Y-m-d');
		}

		$getPlannedDays = FSwpDB()->get_results("SELECT * FROM `".FSwpTable('schedules')."` WHERE `start_date`<='$lastDate' AND `status`='active' AND user_id='$myId'", ARRAY_A);

		$days = [];

		foreach( $getPlannedDays AS $planInf )
		{
			$scheduleId = (int)$planInf['id'];
			$planStart = strtotime($planInf['start_date']);
			$planEnd = strtotime($lastDate);
			$interval = (int)$planInf['interval']>0 ? (int)$planInf['interval'] : 1;


			if( $planStart < strtotime($firstDate) )
			{
				$planStart = strtotime($firstDate);
			}

			if( $planInf['post_sort'] != 'random' && $planInf['post_sort'] != 'random2' )
			{
				$filterQuery = FSscheduleNextPostFilters( $planInf );
				$calcLimit = 1+(int)(( $planEnd - $planStart ) / 60 / $interval);

				$calcLimit = $calcLimit > 0 ? $calcLimit : 1;

				$getRandomPost = FSwpDB()->get_results("SELECT * FROM ".FSwpDB()->base_prefix."posts WHERE post_status='publish' {$filterQuery} LIMIT " . $calcLimit , ARRAY_A);
			}

			if( ( $planInf['interval'] % 24 ) != 0 || empty($planInf['share_time']) )
			{
				$getLastShareTime = FSwpDB()->get_row("SELECT MAX(send_time) AS max_share_time FROM ".FSwpTable('feeds')." WHERE schedule_id='$scheduleId'", ARRAY_A);
				$planInf['share_time'] = date('H:i:s' , strtotime($getLastShareTime['max_share_time']));
			}

			$cursorDayTimestamp = strtotime( date('Y-m-d', $planStart) . ' ' . $planInf['share_time'] );
			$planEnd = strtotime( date('Y-m-d', $planEnd) . ' 23:59:59' );

			while( $cursorDayTimestamp <= $planEnd )
			{
				$currentDate = date('Y-m-d', $cursorDayTimestamp);
				$time = date('H:i', $cursorDayTimestamp);

				$cursorDayTimestamp += 60 * $interval;

				if( strtotime( $currentDate . ' ' . $time ) < time() )
				{
					continue;
				}

				if( $planInf['post_sort'] == 'random' || $planInf['post_sort'] == 'random2' )
				{
					$postDetails = 'Will select randomly';
					$postId = null;
				}
				else
				{
					$thisPostInf = current( $getRandomPost );
					next( $getRandomPost );

					if( $thisPostInf )
					{
						$postDetails = '<b>Post ID:</b> ' . $thisPostInf['ID'] . "<br><b>Title:</b> " . htmlspecialchars(FScutText($thisPostInf['post_title']) . '<br><br><i>Click to get the post page</i>');
						$postId = $thisPostInf['ID'];
					}
					else
					{
						$postDetails = 'Post not found with your filters for this date!';
						$postId = null;
					}
				}

				$days[] = [
					'id'		=>	$planInf['id'],
					'title'		=>	htmlspecialchars( FScutText($planInf['title'], 22) ),
					'post_data'	=>	$postDetails,
					'post_id'	=>	$postId,
					'date'		=>	$currentDate,
					'time'		=>	$time
				];


			}

		}

		FSresponse(true, ['days' => $days]);
	}

}