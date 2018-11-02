<?php
defined('ABSPATH') or exit();

class PostMetaBox
{

	public function __construct()
	{
		// when post status changed ( saved, updated, scheduled and etc... )
		add_action( 'transition_post_status', [$this , 'onSave'], 10, 3 );

		// if is wp admin panel
		if( is_admin() )
		{
			// add meta boxes , columns, buttons ...

			add_action( 'add_meta_boxes', function()
			{
				add_meta_box( 'share_on_fb', 'FS Poster', [$this , 'publishMetaBox'], ['post','product','page'] , 'side' , 'high'  );
			});

			add_action( 'manage_posts_custom_column', function ( $column_name, $post_id )
			{
				if ( $column_name == 'share_btn' && get_post_status($post_id) == 'publish' )
				{
					printf( '<button type="button" class="button" data-load-modal="share_saved_post" data-parameter-post_id="%d">'.esc_html__('Share', 'fs-poster').'</button> ' , $post_id );
					printf( '<button type="button" class="button" data-load-modal="plan_saved_post" data-parameter-post_id="%d">'.esc_html__('Schedule', 'fs-poster').'</button>' , $post_id );
				}
			}, 10, 2 );
			add_filter('manage_posts_columns', function ( $columns )
			{
				if( is_array( $columns ) && ! isset( $columns['share_btn'] ) )
				{
					$columns['share_btn'] = esc_html__('FS Poster' , 'fs-poster');
				}

				return $columns;
			} );

			add_action( 'manage_pages_custom_column', function ( $column_name, $post_id )
			{
				if ( $column_name == 'share_btn' && get_post_status($post_id) == 'publish')
				{
					printf( '<button type="button" class="button" data-load-modal="share_saved_post" data-parameter-post_id="%d">'.esc_html__('Share', 'fs-poster').'</button> ' , $post_id );
					printf( '<button type="button" class="button" data-load-modal="plan_saved_post" data-parameter-post_id="%d">'.esc_html__('Schedule', 'fs-poster').'</button>' , $post_id );
				}
			}, 10, 2 );
			add_filter('manage_pages_columns', function ( $columns )
			{
				if( is_array( $columns ) && ! isset( $columns['share_btn'] ) )
				{
					$columns['share_btn'] = esc_html__('FS Poster' , 'fs-poster');
				}

				return $columns;
			} );

			add_action( 'manage_media_custom_column', function ( $column_name, $post_id )
			{
				if ( $column_name == 'share_btn' && get_post_status($post_id) == 'publish')
				{
					printf( '<button type="button" class="button" data-load-modal="share_saved_post" data-parameter-post_id="%d">'.esc_html__('Share', 'fs-poster').'</button> ' , $post_id );
					printf( '<button type="button" class="button" data-load-modal="plan_saved_post" data-parameter-post_id="%d">'.esc_html__('Schedule', 'fs-poster').'</button>' , $post_id );
				}
			}, 10, 2 );

			add_filter('manage_media_columns', function ( $columns )
			{
				if( is_array( $columns ) && ! isset( $columns['share_btn'] ) )
				{
					$columns['share_btn'] = esc_html__('FS Poster' , 'fs-poster');
				}

				return $columns;
			} );

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
		if( !( ($new_status == 'publish' || $new_status == 'future' || $new_status == 'draft') && $old_status != 'publish' ) )
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

		// if not checked the 'Shear' checkbox exit the function
		$share_checked = _post('share_checked' , 'on' , 'string' , ['on' , 'off']) === 'on' ? 1 : 0;
		if( !$share_checked )
		{
			return;
		}

		// interval for each publication
		$postInterval = (int)get_option('post_interval' , '1');

		// run share process on background
		$backgroundShare = (int)get_option('fs_share_on_background' , '1');

		// social networks lists
		$nodesList = _post('share_on_nodes' , false , 'array' );

		// if false, may be from xmlrpc, external application or etc... then load ol active nodes
		if( $nodesList === false )
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

		if( !empty( $nodesList ) )
		{
			wpDB()->delete(wpTable('feeds') , [
				'post_id'       =>  $post_id,
				'is_sended'     =>  '0'
			]);
		}

		$post_text_message = [];

		$post_text_message['fb']        = _post('post_text_message_fb' , '' , 'string');
		$post_text_message['twitter']   = _post('post_text_message_twitter' , '' , 'string');
		$post_text_message['instagram'] = _post('post_text_message_instagram' , '' , 'string');
		$post_text_message['linkedin']  = _post('post_text_message_linkedin' , '' , 'string');
		$post_text_message['vk']        = _post('post_text_message_vk' , '' , 'string');
		$post_text_message['pinterest'] = _post('post_text_message_pinterest' , '' , 'string');
		$post_text_message['reddit']    = _post('post_text_message_reddit' , '' , 'string');
		$post_text_message['thumblr']   = _post('post_text_message_thumblr' , '' , 'string');
		$post_text_message['google']  	= _post('post_text_message_google' , '' , 'string');

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

				if( $customMessage == get_option( 'post_text_message_' . $driver , "{title}" ) )
				{
					$customMessage = null;
				}

				if( !($driver == 'instagram' && get_option('instagram_post_in_type', '1') == '2') )
				{
					wpDB()->insert( wpTable('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $post_id,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'custom_post_message'   =>  $customMessage
					]);
				}

				if( $driver == 'instagram' && (get_option('instagram_post_in_type', '1') == '2' || get_option('instagram_post_in_type', '1') == '3') )
				{
					wpDB()->insert( wpTable('feeds'), [
						'driver'                =>  $driver,
						'post_id'               =>  $post_id,
						'node_type'             =>  $nodeType,
						'node_id'               =>  (int)$nodeId,
						'interval'              =>  $postInterval,
						'feed_type'             =>  'story',
						'custom_post_message'   =>  $customMessage
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

// trigger class
new PostMetaBox();
