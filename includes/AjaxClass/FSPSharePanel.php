<?php

trait FSPSharePanel
{

	public function manual_share_save()
	{
		$id			= _post('id' , '0' , 'num');
		$link		= _post('link' , '' , 'string');
		$message	= _post('message' , '' , 'string');
		$image		= _post('image' , '0' , 'num');

		$sqlData = [
			'post_type'			=>	'fs_post',
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

	public function check_post_is_published()
	{
		$id			= _post('id' , '0' , 'num');

		$postStatus = get_post_status( $id );

		response(true, [
			'post_status' => $postStatus=='publish' ? true : false
		]);
	}

}