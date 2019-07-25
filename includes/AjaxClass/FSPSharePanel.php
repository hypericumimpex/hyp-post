<?php

trait FSPSharePanel
{

	public function manual_share_save()
	{
		$id			= FS_post('id' , '0' , 'num');
		$link		= FS_post('link' , '' , 'string');
		$message	= FS_post('message' , '' , 'string');
		$image		= FS_post('image' , '0' , 'num');
		$tmp		= FS_post('tmp' , '0' , 'num', ['0', '1']);

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

			delete_post_meta($id, '_fs_link');
			delete_post_meta($id, '_thumbnail_id');
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
		else
		{
			delete_post_meta( $id, '_thumbnail_id' );
		}

		FSresponse(true , ['id'		=>	$id]);
	}

	public function manual_share_delete()
	{
		$id	= FS_post('id' , '0' , 'num');

		if( !($id > 0) )
			FSresponse(false);

		$currentUserId = (int)get_current_user_id();

		$checkPost = FSwpDB()->get_row('SELECT * FROM ' . FSwpDB()->base_prefix . "posts WHERE post_type='fs_post' AND post_author='{$currentUserId}' AND ID='{$id}'", ARRAY_A);

		if( !$checkPost )
			FSresponse(false, 'Post not found!');

		delete_post_meta($id, '_fs_link');
		delete_post_meta($id, '_thumbnail_id');
		wp_delete_post($id);

		FSresponse(true , ['id'		=>	$id]);
	}

	public function check_post_is_published()
	{
		$id			= FS_post('id' , '0' , 'num');

		$postStatus = get_post_status( $id );

		FSresponse(true, [
			'post_status' => $postStatus=='publish' ? true : false
		]);
	}

}