<?php

trait FSPSharePanel
{

	public function manual_share_save()
	{
		$id			= _post('id' , '0' , 'num');
		$link		= _post('link' , '' , 'string');
		$message	= _post('message' , '' , 'string');
		$image		= _post('image' , '0' , 'num');
		$tmp		= _post('tmp' , '0' , 'num', ['0', '1']);

		$sqlData = [
			'post_type'			=>	'fs_post' . ( $tmp ? '_tmp' : '' ),
			'post_content'		=>	$message,
			'post_status'		=>	'publish',
			'comment_status'	=>	'closed',
			'ping_status'		=>	'closed'
		];

		if( $id > 0 )
		{
			$sqlData['ID'] = $id;

			wp_insert_post( $sqlData );
		}
		else
		{
			$id = wp_insert_post( $sqlData );
		}

		add_post_meta($id, '_fs_link', $link);

		if( $image > 0 )
		{
			add_post_meta($id, '_thumbnail_id', $image);
		}

		response(true , ['id'		=>	$id]);
	}

	public function manual_share_delete()
	{
		$id	= _post('id' , '0' , 'num');

		if( !($id > 0) )
			response(false);

		$currentUserId = (int)get_current_user_id();

		$checkPost = wpDB()->get_row('SELECT * FROM ' . wpDB()->base_prefix . "posts WHERE post_type='fs_post' AND post_author='{$currentUserId}' AND ID='{$id}'", ARRAY_A);

		if( !$checkPost )
			response(false, 'Post not found!');

		delete_post_meta($id, '_fs_link');
		delete_post_meta($id, '_thumbnail_id');
		wp_delete_post($id);

		response(true , ['id'		=>	$id]);
	}

	public function check_post_is_published()
	{
		$id			= _post('id' , '0' , 'num');

		$postStatus = get_post_status( $id );

		response(true, [
			'post_status' => $postStatus=='publish' ? true : false
		]);
	}

}