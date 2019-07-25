<?php

trait FSPReports
{
	public function report1_data()
	{
		if( !(isset($_POST['type']) && is_string($_POST['type']) && in_array($_POST['type'] , ['dayly' , 'monthly' , 'yearly'])) )
		{
			exit();
		}

		$type = (string)$_POST['type'];

		$query = [
			'dayly'     =>  "SELECT CAST(send_time AS DATE) AS date , COUNT(0) AS c FROM ".FSwpTable('feeds')." WHERE is_sended=1 GROUP BY CAST(send_time AS DATE)",
			'monthly'   =>  "SELECT CONCAT(YEAR(send_time), '-', MONTH(send_time) , '-01') AS date , COUNT(0) AS c FROM ".FSwpTable('feeds')." WHERE is_sended=1 AND send_time > ADDDATE(now(),INTERVAL -1 YEAR) GROUP BY YEAR(send_time), MONTH(send_time)",
			'yearly'    =>  "SELECT CONCAT(YEAR(send_time), '-01-01') AS date , COUNT(0) AS c FROM ".FSwpTable('feeds')." WHERE is_sended=1 GROUP BY YEAR(send_time)"
		];

		$dateFormat = [
			'dayly'   => 'Y-m-d',
			'monthly' => 'Y M',
			'yearly'  => 'Y',
		];

		$dataSQL = FSwpDB()->get_results($query[$type] , ARRAY_A);

		$labels = [];
		$datas = [];
		foreach( $dataSQL AS $dInf )
		{
			$datas[] = $dInf['c'];
			$labels[] = date( $dateFormat[$type] , strtotime($dInf['date']) );
		}

		FSresponse(true , [
			'data' => $datas,
			'labels' => $labels
		]);
	}

	public function report2_data()
	{
		if( !(isset($_POST['type']) && is_string($_POST['type']) && in_array($_POST['type'] , ['dayly' , 'monthly' , 'yearly'])) )
		{
			exit();
		}

		$type = (string)$_POST['type'];

		$query = [
			'dayly'     =>  "SELECT CAST(send_time AS DATE) AS date , SUM(visit_count) AS c FROM ".FSwpTable('feeds')." WHERE is_sended=1 GROUP BY CAST(send_time AS DATE)",
			'monthly'   =>  "SELECT CONCAT(YEAR(send_time), '-', MONTH(send_time) , '-01') AS date , SUM(visit_count) AS c FROM ".FSwpTable('feeds')." WHERE send_time > ADDDATE(now(),INTERVAL -1 YEAR) AND is_sended=1 GROUP BY YEAR(send_time), MONTH(send_time)",
			'yearly'    =>  "SELECT CONCAT(YEAR(send_time), '-01-01') AS date , SUM(visit_count) AS c FROM ".FSwpTable('feeds')." WHERE is_sended=1 GROUP BY YEAR(send_time)"
		];

		$dateFormat = [
			'dayly'   => 'Y-m-d',
			'monthly' => 'Y M',
			'yearly'  => 'Y',
		];

		$dataSQL = FSwpDB()->get_results($query[$type] , ARRAY_A);

		$labels = [];
		$datas = [];
		foreach( $dataSQL AS $dInf )
		{
			$datas[] = $dInf['c'];
			$labels[] = date( $dateFormat[$type] , strtotime($dInf['date']) );
		}

		FSresponse(true , [
			'data' => $datas,
			'labels' => $labels
		]);
	}

	public function report3_data()
	{
		$page = FS_post('page' , '0' , 'num');
		$schedule_id = FS_post('schedule_id' , '0' , 'num');

		$rows_count2 = FS_post('rows_count' , '4' , 'int', ['4', '8', '15']);

		if( !($page > 0) )
		{
			FSresponse(false);
		}

		$limit = $rows_count2;
		$offset = ($page - 1) * $limit;

		$queryAdd = '';
		if( $schedule_id > 0 )
		{
			$queryAdd = ' AND schedule_id="'.(int)$schedule_id.'"';
		}

		$userId = (int)get_current_user_id();

		$allCount = FSwpDB()->get_row("SELECT COUNT(0) AS c FROM " . FSwpTable('feeds') . ' tb1 WHERE is_sended=1 AND ( (node_type=\'account\' AND (SELECT COUNT(0) FROM '.FSwpTable('accounts').' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=\'' . $userId . '\' OR tb2.is_public=1))>0) OR (node_type<>\'account\' AND (SELECT COUNT(0) FROM '.FSwpTable('account_nodes').' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=\'' . $userId . '\')>0 OR tb2.is_public=1)) ) ' . $queryAdd , ARRAY_A);
		$getData = FSwpDB()->get_results("SELECT * FROM " . FSwpTable('feeds') . ' tb1 WHERE is_sended=1 AND ( (node_type=\'account\' AND (SELECT COUNT(0) FROM '.FSwpTable('accounts').' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=\'' . $userId . '\' OR tb2.is_public=1))>0) OR (node_type<>\'account\' AND (SELECT COUNT(0) FROM '.FSwpTable('account_nodes').' tb2 WHERE tb2.id=tb1.node_id AND (tb2.user_id=\'' . $userId . '\')>0 OR tb2.is_public=1)) ) ' . $queryAdd . " ORDER BY id DESC LIMIT $offset , $limit" , ARRAY_A);
		$resultData = [];

		foreach($getData AS $feedInf)
		{
			$postInf = get_post($feedInf['post_id']);

			$nodeInfTable = $feedInf['node_type'] == 'account' ? 'accounts' : 'account_nodes';

			$nodeInf = FSwpFetch($nodeInfTable , $feedInf['node_id']);
			if( $nodeInf && $feedInf['node_type'] == 'account' )
			{
				$nodeInf['node_type'] = 'account';
			}

			$insights = [
				'like'		=>	0,
				'details'	=>	'',
				'comments'	=>	0,
				'shares'	=>	0
			];

			if( !empty($feedInf['driver_post_id']) )
			{
				$nInf = FSgetAccessToken($feedInf['node_type'] , $feedInf['node_id']);

				$proxy          = $nInf['info']['proxy'];
				$accessToken    = $nInf['access_token'];
				$options	    = $nInf['options'];
				$accountId	    = $nInf['account_id'];
				$appId			= $nInf['app_id'];

				$appInf			= FSwpFetch('apps' , $appId);

				if( $feedInf['driver'] == 'fb' )
				{
					if( empty( $options ) )
					{
						require_once FS_LIB_DIR . "fb/FacebookLib.php";
						$insights = FacebookLib::getStats($feedInf['driver_post_id'] , $accessToken , $proxy);
					}
					else
					{
						require_once FS_LIB_DIR . "fb/FacebookCookieMethod.php";
						$fbDriver = new FacebookCookieMethod( $accountId, $options, $proxy );
						$insights = $fbDriver->getStats( $feedInf['driver_post_id'] );
					}

				}
				else if( $feedInf['driver'] == 'vk' )
				{
					require_once FS_LIB_DIR . "vk/Vk.php";
					$insights = Vk::getStats($feedInf['driver_post_id'] , $accessToken , $proxy);
				}
				else if( $feedInf['driver'] == 'twitter' )
				{
					require_once FS_LIB_DIR . "twitter/TwitterLib.php";
					$insights = TwitterLib::getStats($feedInf['driver_post_id'] , $accessToken , $nInf['access_token_secret'] , $appId , $proxy);
				}
				else if( $feedInf['driver'] == 'instagram' )
				{
					require_once FS_LIB_DIR . "instagram/FSInstagram.php";
					$insights = FSInstagram::getStats($feedInf['driver_post_id2'], $feedInf['driver_post_id'] , $nInf['info'] , $proxy);
				}
				else if( $feedInf['driver'] == 'linkedin' )
				{
					require_once FS_LIB_DIR . "linkedin/Linkedin.php";
					$insights = Linkedin::getStats(null , $proxy);
				}
				else if( $feedInf['driver'] == 'pinterest' )
				{
					require_once FS_LIB_DIR . "pinterest/Pinterest.php";
					$insights = Pinterest::getStats($feedInf['driver_post_id'] , $accessToken , $proxy);
				}
				else if( $feedInf['driver'] == 'reddit' )
				{
					require_once FS_LIB_DIR . "reddit/Reddit.php";
					$insights = Reddit::getStats($feedInf['driver_post_id'] , $accessToken , $proxy);
				}
				else if( $feedInf['driver'] == 'ok' )
				{
					require_once FS_LIB_DIR . "ok/OdnoKlassniki.php";
					$postId2 = explode('/' , $feedInf['driver_post_id']);
					$postId2 = end($postId2);
					$insights = OdnoKlassniki::getStats($postId2 , $accessToken , $appInf['app_key'] , $appInf['app_secret'] , $proxy);
				}
			}

			if( $feedInf['driver'] == 'google_b' )
			{
				$username = $nodeInf['node_id'];
			}
			else
			{
				$username = isset($nodeInf['screen_name']) ? $nodeInf['screen_name'] : (isset($nodeInf['username']) ? $nodeInf['username'] : '-');
			}

			$resultData[] = [
				'id'            =>  $feedInf['id'],
				'name'          =>  $nodeInf ? htmlspecialchars($nodeInf['name']) : ' - deleted',
				'post_id'       =>  htmlspecialchars($feedInf['driver_post_id']),
				'post_title'    =>  htmlspecialchars(isset($postInf->post_title) ? $postInf->post_title : 'Deleted'),
				'cover'         =>  FSprofilePic($nodeInf),
				'profile_link'  =>  FSprofileLink($nodeInf),
				'is_sended'     =>  $feedInf['is_sended'],
				'post_link'     =>  FSpostLink($feedInf['driver_post_id'] , $feedInf['driver'] , $username , $feedInf['feed_type']),
				'status'        =>  $feedInf['status'],
				'error_msg'     =>  $feedInf['error_msg'],
				'hits'          =>  $feedInf['visit_count'],
				'driver'        =>  $feedInf['driver'],
				'insights'      =>  $insights,
				'node_type'     =>  ucfirst($feedInf['node_type']),
				'feed_type'     =>  ucfirst((string)$feedInf['feed_type']),
				'date'          =>  date('Y-m-d H:i' , strtotime($feedInf['send_time']))
			];
		}

		$nextBtnDisable = ($page * $limit >= $allCount['c']);

		FSresponse(true , ['data' => $resultData , 'disable_btn' => $nextBtnDisable]);

	}

	public function fs_clear_logs()
	{
		$userId = (int)get_current_user_id();

		FSwpDB()->query( "DELETE FROM " . FSwpTable('feeds') . ' WHERE (is_sended=1 OR (send_time+INTERVAL 1 DAY)<NOW()) AND ( (node_type=\'account\' AND (SELECT COUNT(0) FROM '.FSwpTable('accounts').' tb2 WHERE tb2.id='.FSwpTable('feeds').'.node_id AND (tb2.user_id=\'' . $userId . '\' OR tb2.is_public=1))>0) OR (node_type<>\'account\' AND (SELECT COUNT(0) FROM '.FSwpTable('account_nodes').' tb2 WHERE tb2.id='.FSwpTable('feeds').'.node_id AND (tb2.user_id=\'' . $userId . '\')>0 OR tb2.is_public=1)) )');

		FSresponse(true);
	}
}