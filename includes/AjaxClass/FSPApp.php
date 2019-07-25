<?php

trait FSPApp
{
	public function delete_app()
	{
		if( !(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) )
		{
			exit();
		}
		$id = (int)$_POST['id'];

		$checkApp = FSwpFetch('apps' , $id);
		if( !$checkApp )
		{
			FSresponse(false , esc_html__('App not found!' , 'fs-poster'));
		}
		else if( $checkApp['user_id'] != get_current_user_id() )
		{
			FSresponse(false , esc_html__('You do not have a permission to delete this app!' , 'fs-poster'));
		}
		else if( $checkApp['is_standart'] > 0 )
		{
			FSresponse(false , esc_html__('You can not delete this app!' , 'fs-poster'));
		}

		FSwpDB()->delete(FSwpTable('apps') , ['id' => $id]);

		FSresponse(true);
	}

	public function add_new_app()
	{
		$data = [];
		$data['app_id'] = FS_post('app_id' , '' , 'string');
		$data['app_key'] = FS_post('app_key' , '' , 'string');
		$data['app_secret'] = FS_post('app_secret' , '' , 'string');
		$driver = FS_post('driver' , '' , 'string');

		$appSupports = [
			'fb'        =>  ['app_id' , 'app_key'],
			'twitter'   =>  ['app_key' , 'app_secret'],
			'linkedin'  =>  ['app_id' , 'app_secret'],
			'vk'        =>  ['app_id' , 'app_secret'],
			'pinterest' =>  ['app_id' , 'app_secret'],
			'reddit'    =>  ['app_id' , 'app_secret'],
			'tumblr'    =>  ['app_key' , 'app_secret'],
			'ok'    	=>  ['app_id' , 'app_key' , 'app_secret'],
			'medium'   	=>  ['app_id' , 'app_secret'],
		];

		if( !isset($appSupports[$driver]) )
		{
			FSresponse(false);
		}

		$checkParams = [];
		$checkParams['user_id'] = get_current_user_id();
		$checkParams['driver'] = $driver;
		foreach( $appSupports[$driver] AS $field1 )
		{
			if( empty($data[$field1]) )
			{
				FSresponse(false , $field1. ' ' . esc_html__('field is empty!' , 'fs-poster'));
			}

			$checkParams[$field1] = $data[$field1];
		}

		$checkAppIdExist = FSwpFetch('apps' , $checkParams);
		if( $checkAppIdExist )
		{
			FSresponse(false , ['error_msg'=>esc_html__('This app has already been added.' , 'fs-poster')]);
		}

		if( $driver == 'fb' )
		{
			require_once FS_LIB_DIR . "fb/FacebookLib.php";
			$validateApp = FacebookLib::validateAppSecret( $data['app_id'] , $data['app_key'] );

			if( !$validateApp )
			{
				FSresponse(false , ['error_msg' => esc_html__('App ID or Secret is invalid!' , 'fs-poster')]);
			}
			else if( ($checkResult = FScheckPermission( $validateApp['permissions'] )) !== true )
			{
				FSresponse(false , ['error_msg' => $checkResult]);
			}
		}

		$checkParams['is_public'] = 0;
		$checkParams['is_standart'] = 0;
		$checkParams['name'] = isset($validateApp['name']) ? $validateApp['name'] : (empty($data['app_id']) ? $data['app_key'] : $data['app_id']);

		FSwpDB()->insert(FSwpTable('apps') , $checkParams);

		FSresponse(true, ['id' => FSwpDB()->insert_id , 'name' => esc_html($checkParams['name'])]);
	}
}