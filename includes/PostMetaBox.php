<?php
defined('ABSPATH') or exit();

class PostMetaBox
{

	public function __construct()
	{
		// when post status changed ( saved, updated, scheduled and etc... )
		add_action( 'transition_post_status', [$this , 'onSave'], 10, 3 );
		//add_action( 'save_post', [$this , 'onSave2'], 10, 3 );

		// if is wp admin panel
		if( is_admin() )
		{
			// add meta boxes , columns, buttons ...
			$allowedPostTypes = explode( '|' , get_option('fs_allowed_post_types' , 'post|page|attachment|product') );

			add_action( 'add_meta_boxes', function() use( $allowedPostTypes )
			{
				add_meta_box( 'share_on_fb', 'FS Poster', [$this , 'publishMetaBox'], $allowedPostTypes , 'side' , 'high'  );
			});


			if( get_option('fs_show_fs_poster_column', '1') )
			{
				$usedColumnsSave = [];

				foreach( $allowedPostTypes AS $postType )
				{
					$postType = preg_replace('/[^a-zA-Z0-9\-\_]/' , '' , $postType);

					switch($postType)
					{
						case 'post':
							$typeName = 'posts';
							break;
						case 'poge':
							$typeName = 'pages';
							break;
						case 'attachment':
							$typeName = 'media';
							break;
						default:
							$typeName = $postType . '_posts';
					}

					add_action( 'manage_'.$typeName.'_custom_column', function ( $column_name, $post_id ) use( &$usedColumnsSave )
					{
						if ( $column_name == 'share_btn' && get_post_status($post_id) == 'publish' && !isset($usedColumnsSave[$post_id]) )
						{
							printf( '<button type="button" class="button" data-load-modal="share_saved_post" data-parameter-post_id="%d">'.esc_html__('Share', 'fs-poster').'</button> ' , $post_id );
							printf( '<button type="button" class="button" data-load-modal="plan_saved_post" data-parameter-post_id="%d">'.esc_html__('Schedule', 'fs-poster').'</button>' , $post_id );

							$usedColumnsSave[$post_id] = true;
						}
					}, 10, 2 );
					add_filter('manage_'.$typeName.'_columns', function ( $columns )
					{
						if( is_array( $columns ) && ! isset( $columns['share_btn'] ) )
						{
							$columns['share_btn'] = esc_html__('FS Poster' , 'fs-poster');
						}

						return $columns;
					} );
				}
			}

			add_filter( 'bulk_actions-edit-post', function ($bulk_actions)
			{
				$bulk_actions['fs_schedule'] = __( 'FS Poster: Schedule', 'fs_schedule');
				return $bulk_actions;
			} );

			add_filter( 'handle_bulk_actions-edit-post', function ( $redirect_to, $doaction, $post_ids )
			{
				if ( $doaction !== 'fs_schedule' )
				{
					return $redirect_to;
				}

				$redirect_to = add_query_arg( 'fs_schedule_posts', implode(',' , $post_ids), $redirect_to );
				return $redirect_to;
			}, 10, 3 );

			add_action( 'admin_notices', function ()
			{
				if( empty($_GET['fs_schedule_posts']) || !is_string($_GET['fs_schedule_posts']) )
				{
					return;
				}

				$posts = explode(',' , (string)$_GET['fs_schedule_posts']);
				$postIds = [];
				foreach ($posts AS $postId)
				{
					if( is_numeric($postId) && $postId > 0 )
					{
						$postIds[] = (int)$postId;
					}
				}

				print '<script>$(document).ready(function(){ fsCode.loadModal("plan_saved_post" , {"post_id": '.json_encode($postIds).'}) });</script>';
			});

		}
	}

	function publishMetaBox( $post )
	{
		// post creating panel
		if( in_array( $post->post_status , ['new' , 'auto-draft' , 'draft' , 'pending'] ) )
		{
			$postId = $post->ID;
			$postType = 'post';
			require_once VIEWS_DIR . "post_meta_box.php";
		}
		else // post edit panel
		{
			require_once VIEWS_DIR . "post_meta_box_edit.php";
		}
	}

	function onSave( $new_status, $old_status, $post )
	{
		// For WordPress 5 (Gutenberg)...
		$metaBoxLoader = (int)_get('meta-box-loader', 0, 'num', ['1']);
		if( $metaBoxLoader === 1 && _post('original_post_status', '', 'string') == 'publish' )
		{
			$metaBoxLoader = 0;
		}

		if( !( ($new_status == 'publish' || $new_status == 'future') && ( $old_status != 'publish' || $metaBoxLoader === 1 ) ) )
		{
			return;
		}

		// if not allowed post type...
		if( !in_array( $post->post_type , explode( '|' , get_option('fs_allowed_post_types' , 'post|page|attachment|product') ) ) )
		{
			return;
		}

		$post_id	= $post->ID;
		$userId		= $post->post_author;

		// if not checked the 'Share' checkbox exit the function
		$share_checked_inpt = _post('share_checked' , ( get_option('fs_auto_share_new_posts', '1') ? 'on' : 'off' ) , 'string' , ['on' , 'off']);
		$share_checked = $share_checked_inpt === 'on' ? 1 : 0;

		if( !$share_checked )
		{
			wpDB()->delete(wpTable('feeds') , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);

			return;
		}

		// if scheduled post, publish it using cron and exit the function
		if( $new_status == 'publish' && $old_status == 'future' )
		{
			$checkFeedsExist = wpFetch('feeds' , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);

			if( $checkFeedsExist )
			{
				CronJob::setbackgroundTask( $post_id );
			}

			return;
		}

		// interval for each publication
		$postInterval = (int)get_option('fs_post_interval' , '1');

		// run share process on background
		$backgroundShare = (int)get_option('fs_share_on_background' , '1');

		// social networks lists
		$nodesList = _post('share_on_nodes' , false , 'array' );

		// if false, may be from xmlrpc, external application or etc... then load ol active nodes
		if( $nodesList === false && !isset($_POST['share_checked']) )
		{
			$nodesList = [];

			$accounts = wpDB()->get_results(
				wpDB()->prepare("
					SELECT tb2.id, tb2.driver, tb1.filter_type, tb1.categories, 'account' AS node_type FROM ".wpTable('account_status')." tb1
					LEFT JOIN ".wpTable('accounts')." tb2 ON tb2.id=tb1.account_id
					WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$activeNodes = wpDB()->get_results(
				wpDB()->prepare("
					SELECT tb2.id, tb2.driver, tb2.node_type, tb1.filter_type, tb1.categories FROM ".wpTable('account_node_status')." tb1
					LEFT JOIN ".wpTable('account_nodes')." tb2 ON tb2.id=tb1.node_id
					WHERE tb1.user_id=%d" , [ $userId ])
				, ARRAY_A
			);

			$activeNodes = array_merge($accounts , $activeNodes);

			foreach ($activeNodes AS $nodeInf)
			{
				$nodesList[] = $nodeInf['driver'].':'.$nodeInf['node_type'].':'.$nodeInf['id'].':'.htmlspecialchars($nodeInf['filter_type']).':'.htmlspecialchars($nodeInf['categories']);
			}
		}

		if( !empty( $nodesList ) || $metaBoxLoader === 1 )
		{
			wpDB()->delete(wpTable('feeds') , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);
		}

		$post_text_message = [];

		$post_text_message['fb']        = _post('fs_post_text_message_fb' , '' , 'string');
		$post_text_message['twitter']   = _post('fs_post_text_message_twitter' , '' , 'string');
		$post_text_message['instagram'] = _post('fs_post_text_message_instagram' , '' , 'string');
		$post_text_message['linkedin']  = _post('fs_post_text_message_linkedin' , '' , 'string');
		$post_text_message['vk']        = _post('fs_post_text_message_vk' , '' , 'string');
		$post_text_message['pinterest'] = _post('fs_post_text_message_pinterest' , '' , 'string');
		$post_text_message['reddit']    = _post('fs_post_text_message_reddit' , '' , 'string');
		$post_text_message['thumblr']   = _post('fs_post_text_message_thumblr' , '' , 'string');
		$post_text_message['google']  	= _post('fs_post_text_message_google' , '' , 'string');
		$post_text_message['google']  	= _post('fs_post_text_message_google' , '' , 'string');
		$post_text_message['ok'] 		= _post('fs_post_text_message_ok' , '' , 'string');

		$postCats = getPostCatsArr( $post_id );

		foreach( $nodesList AS $nodeId )
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

				if( !( in_array( $nodeType , ['account' , 'ownpage' , 'page' , 'group' , 'event' , 'blog' , 'company'] ) && is_numeric($nodeId) && $nodeId > 0 ) )
				{
					continue;
				}

				$customMessage = isset($post_text_message[$driver]) ? $post_text_message[$driver] : null;

				if( $customMessage == get_option( 'fs_post_text_message_' . $driver , "{title}" ) )
				{
					$customMessage = null;
				}

				if( !($driver == 'instagram' && get_option('fs_instagram_post_in_type', '1') == '2') )
				{
					wpDB()->insert( wpTable('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $post_id,
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
						'post_id'               =>  $post_id,
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

		// if backround process activated then create a new cron job
		if( $backgroundShare && $new_status == 'publish' )
		{
			CronJob::setbackgroundTask( $post_id );
		}

		// if not scheduled post then add arguments end of url
		if( $new_status == 'publish' )
		{
			add_filter('redirect_post_location', function($location) use( $backgroundShare )
			{
				return $location . '&share=1&background=' . $backgroundShare;
			});
		}

	}

}

new PostMetaBox();
