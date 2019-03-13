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
		modalView('add_fb_account');
	}

	public function modal_add_twitter_account()
	{
		modalView('add_twitter_account');
	}

	public function modal_add_linkedin_account()
	{
		modalView('add_linkedin_account');
	}

	public function modal_add_ok_account()
	{
		modalView('add_ok_account');
	}

	public function modal_add_google_account_methods()
	{
		modalView('add_google_account_methods');
	}

	public function modal_add_google_account()
	{
		modalView('add_google_account');
	}

	public function modal_add_google_account_cookies_method()
	{
		modalView('add_google_account_cookies_method');
	}

	public function modal_add_pinterest_account()
	{
		modalView('add_pinterest_account');
	}

	public function modal_add_reddit_account()
	{
		modalView('add_reddit_account');
	}

	public function modal_add_tumblr_account()
	{
		modalView('add_tumblr_account');
	}

	public function modal_edit_reddit_account_subreddit()
	{
		modalView('edit_reddit_account_subreddit');
	}

	public function modal_reddit_show_subreddits()
	{
		modalView('reddit_show_subreddits');
	}

	public function modal_reddit_add_subreddit()
	{
		modalView('reddit_add_subreddit');
	}


	public function modal_edit_pinterest_account_board()
	{
		modalView('edit_pinterest_account_board');
	}

	public function modal_posts_list()
	{
		modalView('posts_list');
	}

	public function modal_add_vk_account()
	{
		modalView('add_vk_account');
	}

	public function modal_add_instagram_account()
	{
		modalView('add_instagram_account');
	}

	public function modal_add_instagram_account_case()
	{
		modalView('add_instagram_account_case');
	}

	public function modal_add_instagram_account_cookies_method()
	{
		modalView('add_instagram_account_cookies_method');
	}

	public function modal_add_app()
	{
		modalView('add_app');
	}

	public function modal_add_node_to_list()
	{
		modalView('add_node_to_list');
	}

	public function modal_share_feeds()
	{
		$postId = _post('post_id' , '0' , 'num');
		if( !($postId > 0) )
		{
			exit();
		}

		$feeds = wpFetchAll('feeds' , ['post_id' => $postId , 'is_sended' => 0]);

		modalView('share_feeds' , [
			'feeds' =>  $feeds
		]);
	}

	public function modal_share_saved_post()
	{
		$postId = _post('post_id' , '0' , 'num');

		if( !($postId > 0) )
		{
			exit();
		}

		modalView('share_saved_post' , [
			'postId'    =>  $postId
		]);
	}

	public function modal_plan_saved_post()
	{
		$postId1 = _post('post_id' , '0' , 'num');

		if( $postId1 > 0 )
		{
			$posts = [ (int)$postId1 ];
		}
		else
		{
			$postIds = _post('post_id' , [] , 'array');
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

		modalView('plan_post' , [
			'postIds'    =>  $posts
		]);
	}

	public function modal_show_nodes_list()
	{

		modalView('show_nodes_list');
	}

	public function modal_add_schedule()
	{

		modalView('add_schedule');
	}

	public function modal_activate_with_condition()
	{
		$id = _post('id' , '0' , 'num');
		$type = _post('type' , '' , 'string');

		$ajaxUrl = $type == 'node' ? 'settings_node_activity_change' : 'account_activity_change';

		modalView('activate_with_condition' , ['id' => $id , 'ajaxUrl' => $ajaxUrl]);
	}

	public function modal_google_show_communities_list()
	{

		modalView('google_show_communities_list' , []);
	}

	public function modal_google_add_community()
	{
		modalView('google_add_community' , []);
	}

}

new ModalClass();
