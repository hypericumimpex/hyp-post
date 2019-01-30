<?php

trait FSPSettings
{
	private function isAdmin()
	{
		if( !current_user_can('administrator') )
		{
			exit();
		}
	}

	public function settings_general_save()
	{
		$this->isAdmin();

		$fs_auto_share_new_posts = _post('fs_auto_share_new_posts' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_use_wp_cron_jobs = _post('fs_use_wp_cron_jobs' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_show_fs_poster_column = _post('fs_show_fs_poster_column' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_unique_link = _post('fs_unique_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_on_background = _post('fs_share_on_background' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_timer = _post('fs_share_timer' , '0' , 'integer' );

		$fs_url_shortener = _post('fs_url_shortener' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_shortener_service = _post('fs_shortener_service' , 0 , 'string' , ['tinyurl' , 'bitly']);
		$fs_url_short_access_token_bitly = _post('fs_url_short_access_token_bitly' , '' , 'string' );
		$fs_keep_logs = _post('fs_keep_logs' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_allowed_post_types = _post('fs_allowed_post_types' , ['post' , 'attachment' , 'page' , 'product'] , 'array');
		$newArrPostTypes = [];
		$allTypes = get_post_types();
		foreach( $fs_allowed_post_types AS $fsAPT )
		{
			if( is_string($fsAPT) && in_array( $fsAPT , $allTypes ))
			{
				$newArrPostTypes[] = $fsAPT;
			}
		}
		$newArrPostTypes = implode('|' , $newArrPostTypes);

		$fs_hide_for_roles = _post('fs_hide_for_roles' , [] , 'array');
		$newArrHideForRoles = [];
		$allRoles = get_editable_roles();
		foreach( $fs_hide_for_roles AS $fsAPT )
		{
			if( $fsAPT != 'administrator' && is_string($fsAPT) && isset( $allRoles[$fsAPT] ) )
			{
				$newArrHideForRoles[] = $fsAPT;
			}
		}
		$newArrHideForRoles = implode('|' , $newArrHideForRoles);

		update_option('fs_auto_share_new_posts' , $fs_auto_share_new_posts);
		update_option('fs_use_wp_cron_jobs' , $fs_use_wp_cron_jobs);
		update_option('fs_show_fs_poster_column' , $fs_show_fs_poster_column);

		update_option('fs_unique_link' , $fs_unique_link);
		update_option('fs_share_on_background' , $fs_share_on_background);
		update_option('fs_share_timer' , $fs_share_timer);
		update_option('fs_url_shortener' , $fs_url_shortener);
		update_option('fs_shortener_service' , $fs_shortener_service);
		update_option('fs_url_short_access_token_bitly' , $fs_url_short_access_token_bitly);
		update_option('fs_keep_logs' , $fs_keep_logs);

		update_option('fs_allowed_post_types' , $newArrPostTypes);
		update_option('fs_hide_menu_for' , $newArrHideForRoles);

		response(true);
	}

	public function settings_facebook_save()
	{
		$this->isAdmin();

		$fs_load_own_pages = _post('fs_load_own_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_load_groups = _post('fs_load_groups' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_load_liked_pages = _post('fs_load_liked_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_max_liked_pages_limit = _post('fs_max_liked_pages_limit' , '50' , 'num');
		$fs_max_groups_limit = _post('fs_max_groups_limit' , '50' , 'num');

		if( $fs_max_liked_pages_limit > 1000 )
			$fs_max_liked_pages_limit = 1000;

		if( $fs_max_groups_limit > 1000 )
			$fs_max_groups_limit = 1000;

		$fs_post_text_message_fb = _post('fs_post_text_message_fb' , '' , 'string');
		$fs_facebook_posting_type = _post('fs_facebook_posting_type' , '1' , 'num' , ['1', '2', '3'] );


		update_option('fs_post_text_message_fb' , $fs_post_text_message_fb);

		update_option('fs_load_own_pages' , $fs_load_own_pages);
		update_option('fs_load_groups' , $fs_load_groups);
		update_option('fs_load_liked_pages' , $fs_load_liked_pages);

		update_option('fs_max_liked_pages_limit' , $fs_max_liked_pages_limit);
		update_option('fs_max_groups_limit' , $fs_max_groups_limit);

		update_option('fs_facebook_posting_type' , $fs_facebook_posting_type);

		response(true);
	}

	public function settings_instagram_save()
	{
		$this->isAdmin();

		$fs_instagram_post_in_type = _post('fs_instagram_post_in_type' , 0 , 'int' , [1,2,3]);
		$fs_instagram_story_link = _post('fs_instagram_story_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_instagram_story_hashtag = _post('fs_instagram_story_hashtag' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_instagram_story_hashtag_name = _post('fs_instagram_story_hashtag_name' , '' , 'string');
		$fs_instagram_story_hashtag_position = _post('fs_instagram_story_hashtag_position' , 'top' , 'string' , ['top' , 'bottom']);

		if( $fs_instagram_story_hashtag && empty($fs_instagram_story_hashtag_name) )
		{
			response(false , 'Plase type the hashtag');
		}

		$fs_post_text_message_instagram = _post('fs_post_text_message_instagram' , '' , 'string');

		update_option('fs_post_text_message_instagram' , $fs_post_text_message_instagram);

		update_option('fs_instagram_post_in_type' , $fs_instagram_post_in_type);
		update_option('fs_instagram_story_link' , $fs_instagram_story_link);
		update_option('fs_instagram_story_hashtag' , $fs_instagram_story_hashtag);

		update_option('fs_instagram_story_hashtag_name' , $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_name : '');
		update_option('fs_instagram_story_hashtag_position' , $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_position : '');

		response(true);
	}

	public function settings_vk_save()
	{
		$this->isAdmin();

		$fs_vk_load_admin_communities = _post('fs_vk_load_admin_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_vk_load_members_communities = _post('fs_vk_load_members_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_vk_max_communities_limit = _post('fs_vk_max_communities_limit' , '50' , 'num');

		if( $fs_vk_max_communities_limit > 1000 )
			$fs_vk_max_communities_limit = 1000;


		$fs_post_text_message_vk = _post('fs_post_text_message_vk' , '' , 'string');

		update_option('fs_post_text_message_vk' , $fs_post_text_message_vk);

		update_option('fs_vk_load_admin_communities' , $fs_vk_load_admin_communities);
		update_option('fs_vk_load_members_communities' , $fs_vk_load_members_communities);

		update_option('fs_vk_max_communities_limit' , $fs_vk_max_communities_limit);

		response(true);
	}

	public function settings_twitter_save()
	{
		$this->isAdmin();

		$fs_post_text_message_twitter = _post('fs_post_text_message_twitter' , '' , 'string');
		$fs_twitter_auto_cut_tweets = _post('fs_twitter_auto_cut_tweets' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_twitter_posting_type = _post('fs_twitter_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('fs_post_text_message_twitter' , $fs_post_text_message_twitter);
		update_option('fs_twitter_auto_cut_tweets' , $fs_twitter_auto_cut_tweets);
		update_option('fs_twitter_posting_type' , $fs_twitter_posting_type);

		response(true);
	}

	public function settings_linkedin_save()
	{
		$this->isAdmin();

		$fs_post_text_message_linkedin = _post('fs_post_text_message_linkedin' , '' , 'string');

		update_option('fs_post_text_message_linkedin' , $fs_post_text_message_linkedin);

		response(true);
	}

	public function settings_pinterest_save()
	{
		$this->isAdmin();

		$fs_post_text_message_pinterest = _post('fs_post_text_message_pinterest' , '' , 'string');

		update_option('fs_post_text_message_pinterest' , $fs_post_text_message_pinterest);

		response(true);
	}

	public function settings_tumblr_save()
	{
		$this->isAdmin();

		$fs_post_text_message_tumblr = _post('fs_post_text_message_tumblr' , '' , 'string');

		update_option('fs_post_text_message_tumblr' , $fs_post_text_message_tumblr);

		response(true);
	}

	public function settings_reddit_save()
	{
		$this->isAdmin();

		$fs_post_text_message_reddit = _post('fs_post_text_message_reddit' , '' , 'string');

		update_option('fs_post_text_message_reddit' , $fs_post_text_message_reddit);

		response(true);
	}

	public function settings_google_save()
	{
		$this->isAdmin();

		$fs_post_text_message_google = _post('fs_post_text_message_google' , '' , 'string');

		update_option('fs_post_text_message_google' , $fs_post_text_message_google);

		response(true);
	}

	public function settings_ok_save()
	{
		$this->isAdmin();

		$fs_post_text_message_ok = _post('fs_post_text_message_ok' , '' , 'string');
		$fs_ok_posting_type = _post('fs_ok_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('fs_post_text_message_ok' , $fs_post_text_message_ok);
		update_option('fs_ok_posting_type' , $fs_ok_posting_type);

		response(true);
	}

}