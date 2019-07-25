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

		$fs_show_fs_poster_column = FS_post('fs_show_fs_poster_column' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_allowed_post_types = FS_post('fs_allowed_post_types' , ['post' , 'attachment' , 'page' , 'product'] , 'array');
		$fs_collect_statistics				= FS_post('fs_collect_statistics' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

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

		$fs_hide_for_roles = FS_post('fs_hide_for_roles' , [] , 'array');
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

		update_option('fs_show_fs_poster_column' , (string)$fs_show_fs_poster_column);
		update_option('fs_allowed_post_types' , $newArrPostTypes);
		update_option('fs_hide_menu_for' , $newArrHideForRoles);
		update_option('fs_collect_statistics' , (string)$fs_collect_statistics);

		FSresponse(true);
	}

	public function settings_share_save()
	{
		$this->isAdmin();

		$fs_auto_share_new_posts	= FS_post('fs_auto_share_new_posts' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_on_background		= FS_post('fs_share_on_background' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_timer				= FS_post('fs_share_timer' , '0' , 'integer' );
		$fs_keep_logs				= FS_post('fs_keep_logs' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_post_interval			= FS_post('fs_post_interval' , '0' , 'integer' );
		$fs_post_interval_type		= FS_post('fs_post_interval_type' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		update_option('fs_auto_share_new_posts' , (string)$fs_auto_share_new_posts);
		update_option('fs_share_on_background' , (string)$fs_share_on_background);
		update_option('fs_share_timer' , $fs_share_timer);
		update_option('fs_keep_logs' , (string)$fs_keep_logs);
		update_option('fs_post_interval' , (string)$fs_post_interval);
		update_option('fs_post_interval_type' , (string)$fs_post_interval_type);

		FSresponse(true);
	}

	public function settings_url_save()
	{
		$this->isAdmin();

		$fs_unique_link						= FS_post('fs_unique_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_url_shortener					= FS_post('fs_url_shortener' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_shortener_service				= FS_post('fs_shortener_service' , 0 , 'string' , ['tinyurl' , 'bitly']);
		$fs_url_short_access_token_bitly	= FS_post('fs_url_short_access_token_bitly' , '' , 'string' );
		$fs_url_additional					= FS_post('fs_url_additional' , '' , 'string' );


		update_option('fs_unique_link' , (string)$fs_unique_link);
		update_option('fs_url_shortener' , (string)$fs_url_shortener);
		update_option('fs_shortener_service' , $fs_shortener_service);
		update_option('fs_url_short_access_token_bitly' , $fs_url_short_access_token_bitly);
		update_option('fs_url_additional' , $fs_url_additional);

		FSresponse(true);
	}

	public function settings_facebook_save()
	{
		$this->isAdmin();

		$fs_load_own_pages = FS_post('fs_load_own_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_load_groups = FS_post('fs_load_groups' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_load_liked_pages = FS_post('fs_load_liked_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_max_liked_pages_limit = FS_post('fs_max_liked_pages_limit' , '50' , 'num');
		$fs_max_groups_limit = FS_post('fs_max_groups_limit' , '50' , 'num');

		if( $fs_max_liked_pages_limit > 1000 )
			$fs_max_liked_pages_limit = 1000;

		if( $fs_max_groups_limit > 1000 )
			$fs_max_groups_limit = 1000;

		$fs_post_text_message_fb = FS_post('fs_post_text_message_fb' , '' , 'string');
		$fs_facebook_posting_type = FS_post('fs_facebook_posting_type' , '1' , 'num' , ['1', '2', '3'] );


		update_option('fs_post_text_message_fb' , $fs_post_text_message_fb);

		update_option('fs_load_own_pages' , (string)$fs_load_own_pages);
		update_option('fs_load_groups' , (string)$fs_load_groups);
		update_option('fs_load_liked_pages' , (string)$fs_load_liked_pages);

		update_option('fs_max_liked_pages_limit' , $fs_max_liked_pages_limit);
		update_option('fs_max_groups_limit' , $fs_max_groups_limit);

		update_option('fs_facebook_posting_type' , $fs_facebook_posting_type);

		FSresponse(true);
	}

	public function settings_instagram_save()
	{
		$this->isAdmin();

		$fs_instagram_post_in_type = FS_post('fs_instagram_post_in_type' , 0 , 'int' , [1,2,3]);
		$fs_instagram_story_link = FS_post('fs_instagram_story_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_instagram_story_hashtag = FS_post('fs_instagram_story_hashtag' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_instagram_story_hashtag_name = FS_post('fs_instagram_story_hashtag_name' , '' , 'string');
		$fs_instagram_story_hashtag_position = FS_post('fs_instagram_story_hashtag_position' , 'top' , 'string' , ['top' , 'bottom']);

		if( $fs_instagram_story_hashtag && empty($fs_instagram_story_hashtag_name) )
		{
			FSresponse(false , 'Plase type the hashtag');
		}

		$fs_post_text_message_instagram = FS_post('fs_post_text_message_instagram' , '' , 'string');

		update_option('fs_post_text_message_instagram' , $fs_post_text_message_instagram);

		update_option('fs_instagram_post_in_type' , $fs_instagram_post_in_type);
		update_option('fs_instagram_story_link' , (string)$fs_instagram_story_link);
		update_option('fs_instagram_story_hashtag' , (string)$fs_instagram_story_hashtag);

		update_option('fs_instagram_story_hashtag_name' , $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_name : '');
		update_option('fs_instagram_story_hashtag_position' , $fs_instagram_story_hashtag ? $fs_instagram_story_hashtag_position : '');

		FSresponse(true);
	}

	public function settings_vk_save()
	{
		$this->isAdmin();

		$fs_vk_load_admin_communities = FS_post('fs_vk_load_admin_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_vk_load_members_communities = FS_post('fs_vk_load_members_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_vk_upload_image = FS_post('fs_vk_upload_image' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$fs_vk_max_communities_limit = FS_post('fs_vk_max_communities_limit' , '50' , 'num');

		if( $fs_vk_max_communities_limit > 1000 )
			$fs_vk_max_communities_limit = 1000;


		$fs_post_text_message_vk = FS_post('fs_post_text_message_vk' , '' , 'string');

		update_option('fs_post_text_message_vk' , $fs_post_text_message_vk);

		update_option('fs_vk_load_admin_communities' , (string)$fs_vk_load_admin_communities);
		update_option('fs_vk_load_members_communities' , (string)$fs_vk_load_members_communities);

		update_option('fs_vk_max_communities_limit' , $fs_vk_max_communities_limit);
		update_option('fs_vk_upload_image' , $fs_vk_upload_image);

		FSresponse(true);
	}

	public function settings_twitter_save()
	{
		$this->isAdmin();

		$fs_post_text_message_twitter = FS_post('fs_post_text_message_twitter' , '' , 'string');
		$fs_twitter_auto_cut_tweets = FS_post('fs_twitter_auto_cut_tweets' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_twitter_posting_type = FS_post('fs_twitter_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('fs_post_text_message_twitter' , $fs_post_text_message_twitter);
		update_option('fs_twitter_auto_cut_tweets' , $fs_twitter_auto_cut_tweets);
		update_option('fs_twitter_posting_type' , $fs_twitter_posting_type);

		FSresponse(true);
	}

	public function settings_linkedin_save()
	{
		$this->isAdmin();

		$fs_post_text_message_linkedin = FS_post('fs_post_text_message_linkedin' , '' , 'string');

		update_option('fs_post_text_message_linkedin' , $fs_post_text_message_linkedin);

		FSresponse(true);
	}

	public function settings_pinterest_save()
	{
		$this->isAdmin();

		$fs_post_text_message_pinterest = FS_post('fs_post_text_message_pinterest' , '' , 'string');

		update_option('fs_post_text_message_pinterest' , $fs_post_text_message_pinterest);

		FSresponse(true);
	}

	public function settings_google_b_save()
	{
		$this->isAdmin();

		$fs_post_text_message_google_b	= FS_post('fs_post_text_message_google_b' , '' , 'string');
		$fs_google_b_share_as_product	= FS_post('fs_google_b_share_as_product' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_google_b_button_type		= FS_post('fs_google_b_button_type' , 'LEARN_MORE' , 'string', ['BOOK', 'ORDER', 'SHOP', 'SIGN_UP', '-']);

		update_option('fs_post_text_message_google_b' , $fs_post_text_message_google_b);
		update_option('fs_google_b_share_as_product' , $fs_google_b_share_as_product);
		update_option('fs_google_b_button_type' , $fs_google_b_button_type);

		FSresponse(true);
	}

	public function settings_tumblr_save()
	{
		$this->isAdmin();

		$fs_post_text_message_tumblr = FS_post('fs_post_text_message_tumblr' , '' , 'string');

		update_option('fs_post_text_message_tumblr' , $fs_post_text_message_tumblr);

		FSresponse(true);
	}

	public function settings_reddit_save()
	{
		$this->isAdmin();

		$fs_post_text_message_reddit = FS_post('fs_post_text_message_reddit' , '' , 'string');

		update_option('fs_post_text_message_reddit' , $fs_post_text_message_reddit);

		FSresponse(true);
	}

	public function settings_ok_save()
	{
		$this->isAdmin();

		$fs_post_text_message_ok = FS_post('fs_post_text_message_ok' , '' , 'string');
		$fs_ok_posting_type = FS_post('fs_ok_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('fs_post_text_message_ok' , $fs_post_text_message_ok);
		update_option('fs_ok_posting_type' , $fs_ok_posting_type);

		FSresponse(true);
	}

	public function settings_telegram_save()
	{
		$this->isAdmin();

		$fs_post_text_message_telegram	= FS_post('fs_post_text_message_telegram' , '' , 'string');
		$fs_telegram_type_of_sharing	= FS_post('fs_telegram_type_of_sharing' , '1' , 'int', [ '1', '2', '3', '4' ]);

		update_option('fs_post_text_message_telegram' , $fs_post_text_message_telegram);
		update_option('fs_telegram_type_of_sharing' , $fs_telegram_type_of_sharing);

		FSresponse(true);
	}

	public function settings_medium_save()
	{
		$this->isAdmin();

		$fs_post_text_message_medium = FS_post('fs_post_text_message_medium' , '' , 'string');

		update_option('fs_post_text_message_medium' , $fs_post_text_message_medium);

		FSresponse(true);
	}

}