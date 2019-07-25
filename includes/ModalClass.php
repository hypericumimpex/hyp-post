<?php
defined('ABSPATH') or exit();

class ModalClass
{

	public function __construct()
	{
		$methods = get_class_methods($this);
		foreach ($methods AS $method)
		{
			if( strpos($method , 'modal_') !== 0 )
			{
				continue;
			}

			add_action( 'wp_ajax_' . $method, function() use($method)
			{
				define('MODAL' , true);
				$this->$method();
				exit();
			});
		}
	}

	public function modal_add_fb_account()
	{
		FSmodalView('add_fb_account');
	}

	public function modal_add_twitter_account()
	{
		FSmodalView('add_twitter_account');
	}

	public function modal_add_linkedin_account()
	{
		FSmodalView('add_linkedin_account');
	}

	public function modal_add_ok_account()
	{
		FSmodalView('add_ok_account');
	}

	public function modal_add_google_account_methods()
	{
		FSmodalView('add_google_account_methods');
	}

	public function modal_add_google_account()
	{
		FSmodalView('add_google_account');
	}

	public function modal_add_google_account_cookies_method()
	{
		FSmodalView('add_google_account_cookies_method');
	}

	public function modal_add_pinterest_account()
	{
		FSmodalView('add_pinterest_account');
	}

	public function modal_add_reddit_account()
	{
		FSmodalView('add_reddit_account');
	}

	public function modal_add_tumblr_account()
	{
		FSmodalView('add_tumblr_account');
	}

	public function modal_reddit_add_subreddit()
	{
		FSmodalView('reddit_add_subreddit');
	}


	public function modal_edit_pinterest_account_board()
	{
		FSmodalView('edit_pinterest_account_board');
	}

	public function modal_posts_list()
	{
		FSmodalView('posts_list');
	}

	public function modal_add_vk_account()
	{
		FSmodalView('add_vk_account');
	}

	public function modal_add_instagram_account()
	{
		FSmodalView('add_instagram_account');
	}

	public function modal_add_instagram_account_case()
	{
		FSmodalView('add_instagram_account_case');
	}

	public function modal_add_instagram_account_cookies_method()
	{
		FSmodalView('add_instagram_account_cookies_method');
	}

	public function modal_add_app()
	{
		FSmodalView('add_app');
	}

	public function modal_add_node_to_list()
	{
		FSmodalView('add_node_to_list');
	}

	public function modal_share_feeds()
	{
		$postId = FS_post('post_id' , '0' , 'num');
		if( !($postId > 0) )
		{
			exit();
		}

		$feeds = FSwpFetchAll('feeds' , ['post_id' => $postId , 'is_sended' => 0]);

		FSmodalView('share_feeds' , [
			'feeds' =>  $feeds
		]);
	}

	public function modal_share_saved_post()
	{
		$postId = FS_post('post_id' , '0' , 'num');

		if( !($postId > 0) )
		{
			exit();
		}

		FSmodalView('share_saved_post' , [
			'postId'    =>  $postId
		]);
	}

	public function modal_plan_saved_post()
	{
		$postId1 = FS_post('post_id' , '0' , 'num');

		if( $postId1 > 0 )
		{
			$posts = [ (int)$postId1 ];
		}
		else
		{
			$postIds = FS_post('post_id' , [] , 'array');
			$posts = [];
			foreach( $postIds AS $postId )
			{
				if( is_numeric($postId) && $postId > 0 )
				{
					$posts[] = (int)$postId;
				}
			}
		}

		if( empty($posts) )
		{
			exit();
		}

		FSmodalView('plan_post' , [
			'postIds'    =>  $posts
		]);
	}

	public function modal_show_nodes_list()
	{

		FSmodalView('show_nodes_list');
	}

	public function modal_add_schedule()
	{

		FSmodalView('add_schedule');
	}

	public function modal_activate_with_condition()
	{
		$id = FS_post('id' , '0' , 'num');
		$type = FS_post('type' , '' , 'string');

		$ajaxUrl = $type == 'node' ? 'settings_node_activity_change' : 'account_activity_change';

		FSmodalView('activate_with_condition' , ['id' => $id , 'ajaxUrl' => $ajaxUrl]);
	}

	public function modal_google_show_communities_list()
	{

		FSmodalView('google_show_communities_list' , []);
	}

	public function modal_google_add_community()
	{
		FSmodalView('google_add_community' , []);
	}

	public function modal_add_google_b_account()
	{
		FSmodalView('add_google_b_account' , []);
	}

	public function modal_add_telegram_bot()
	{
		FSmodalView('add_telegram_bot' , []);
	}

	public function modal_show_telegram_chats()
	{
		FSmodalView('show_telegram_chats', []);
	}

	public function modal_telegram_add_chat()
	{
		FSmodalView('telegram_add_chat', []);
	}

	public function modal_add_medium_account()
	{
		FSmodalView('add_medium_account', []);
	}

}

new ModalClass();
