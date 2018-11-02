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

		$unique_link = _post('unique_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_on_background = _post('fs_share_on_background' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_share_timer = _post('fs_share_timer' , '0' , 'integer' );

		$url_shortener = _post('url_shortener' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$shortener_service = _post('shortener_service' , 0 , 'string' , ['tinyurl' , 'bitly']);
		$url_short_access_token_bitly = _post('url_short_access_token_bitly' , '' , 'string' );

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

		update_option('unique_link' , $unique_link);
		update_option('fs_share_on_background' , $fs_share_on_background);
		update_option('fs_share_timer' , $fs_share_timer);
		update_option('url_shortener' , $url_shortener);
		update_option('shortener_service' , $shortener_service);
		update_option('url_short_access_token_bitly' , $url_short_access_token_bitly);

		update_option('fs_allowed_post_types' , $newArrPostTypes);
		update_option('fs_hide_menu_for' , $newArrHideForRoles);

		response(true);
	}

	public function settings_facebook_save()
	{
		$this->isAdmin();

		$load_own_pages = _post('load_own_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$load_groups = _post('load_groups' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$load_liked_pages = _post('load_liked_pages' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$max_liked_pages_limit = _post('max_liked_pages_limit' , '50' , 'num');
		$max_groups_limit = _post('max_groups_limit' , '50' , 'num');

		if( $max_liked_pages_limit > 1000 )
			$max_liked_pages_limit = 1000;

		if( $max_groups_limit > 1000 )
			$max_groups_limit = 1000;

		$post_text_message_fb = _post('post_text_message_fb' , '' , 'string');

		update_option('post_text_message_fb' , $post_text_message_fb);

		update_option('load_own_pages' , $load_own_pages);
		update_option('load_groups' , $load_groups);
		update_option('load_liked_pages' , $load_liked_pages);

		update_option('max_liked_pages_limit' , $max_liked_pages_limit);
		update_option('max_groups_limit' , $max_groups_limit);

		response(true);
	}

	public function settings_instagram_save()
	{
		$this->isAdmin();

		$instagram_post_in_type = _post('instagram_post_in_type' , 0 , 'int' , [1,2,3]);
		$instagram_story_link = _post('instagram_story_link' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$instagram_story_hashtag = _post('instagram_story_hashtag' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$instagram_story_hashtag_name = _post('instagram_story_hashtag_name' , '' , 'string');
		$instagram_story_hashtag_position = _post('instagram_story_hashtag_position' , 'top' , 'string' , ['top' , 'bottom']);

		if( $instagram_story_hashtag && empty($instagram_story_hashtag_name) )
		{
			response(false , 'Plase type the hashtag');
		}

		$post_text_message_instagram = _post('post_text_message_instagram' , '' , 'string');

		update_option('post_text_message_instagram' , $post_text_message_instagram);

		update_option('instagram_post_in_type' , $instagram_post_in_type);
		update_option('instagram_story_link' , $instagram_story_link);
		update_option('instagram_story_hashtag' , $instagram_story_hashtag);

		update_option('instagram_story_hashtag_name' , $instagram_story_hashtag ? $instagram_story_hashtag_name : '');
		update_option('instagram_story_hashtag_position' , $instagram_story_hashtag ? $instagram_story_hashtag_position : '');

		response(true);
	}

	public function settings_vk_save()
	{
		$this->isAdmin();

		$vk_load_admin_communities = _post('vk_load_admin_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$vk_load_members_communities = _post('vk_load_members_communities' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;

		$vk_max_communities_limit = _post('vk_max_communities_limit' , '50' , 'num');

		if( $vk_max_communities_limit > 1000 )
			$vk_max_communities_limit = 1000;


		$post_text_message_vk = _post('post_text_message_vk' , '' , 'string');

		update_option('post_text_message_vk' , $post_text_message_vk);

		update_option('vk_load_admin_communities' , $vk_load_admin_communities);
		update_option('vk_load_members_communities' , $vk_load_members_communities);

		update_option('vk_max_communities_limit' , $vk_max_communities_limit);

		response(true);
	}

	public function settings_twitter_save()
	{
		$this->isAdmin();

		$post_text_message_twitter = _post('post_text_message_twitter' , '' , 'string');
		$fs_twitter_auto_cut_tweets = _post('fs_twitter_auto_cut_tweets' , 0 , 'string' , ['on']) === 'on' ? 1 : 0;
		$fs_twitter_posting_type = _post('fs_twitter_posting_type' , '1' , 'num' , ['1', '2', '3'] );

		update_option('post_text_message_twitter' , $post_text_message_twitter);
		update_option('fs_twitter_auto_cut_tweets' , $fs_twitter_auto_cut_tweets);
		update_option('fs_twitter_posting_type' , $fs_twitter_posting_type);

		response(true);
	}

	public function settings_linkedin_save()
	{
		$this->isAdmin();

		$post_text_message_linkedin = _post('post_text_message_linkedin' , '' , 'string');

		update_option('post_text_message_linkedin' , $post_text_message_linkedin);

		response(true);
	}

	public function settings_pinterest_save()
	{
		$this->isAdmin();

		$post_text_message_pinterest = _post('post_text_message_pinterest' , '' , 'string');

		update_option('post_text_message_pinterest' , $post_text_message_pinterest);

		response(true);
	}

	public function settings_tumblr_save()
	{
		$this->isAdmin();

		$post_text_message_tumblr = _post('post_text_message_tumblr' , '' , 'string');

		update_option('post_text_message_tumblr' , $post_text_message_tumblr);

		response(true);
	}

	public function settings_reddit_save()
	{
		$this->isAdmin();

		$post_text_message_reddit = _post('post_text_message_reddit' , '' , 'string');

		update_option('post_text_message_reddit' , $post_text_message_reddit);

		response(true);
	}

	public function settings_google_save()
	{
		$this->isAdmin();

		$post_text_message_google = _post('post_text_message_google' , '' , 'string');

		update_option('post_text_message_google' , $post_text_message_google);

		response(true);
	}


}