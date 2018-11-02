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

		$checkApp = wpFetch('apps' , $id);
		if( !$checkApp )
		{
			response(false , esc_html__('App not found!' , 'fs-poster'));
		}
		else if( $checkApp['user_id'] != get_current_user_id() )
		{
			response(false , esc_html__('You do not have a permission to delete this app!' , 'fs-poster'));
		}
		else if( $checkApp['is_standart'] > 0 )
		{
			response(false , esc_html__('You can not delete this app!' , 'fs-poster'));
		}

		wpDB()->delete(wpTable('apps') , ['id' => $id]);

		response(true);
	}

	public function add_new_app()
	{
		$data = [];
		$data['app_id'] = _post('app_id' , '' , 'string');
		$data['app_key'] = _post('app_key' , '' , 'string');
		$data['app_secret'] = _post('app_secret' , '' , 'string');
		$driver = _post('driver' , '' , 'string');

		$appSupports = [
			'fb'        =>  ['app_id' , 'app_key'],
			'twitter'   =>  ['app_key' , 'app_secret'],
			'linkedin'  =>  ['app_id' , 'app_key'],
			'vk'        =>  ['app_id' , 'app_key'],
			'pinterest' =>  ['app_id' , 'app_key'],
			'reddit'    =>  ['app_id' , 'app_key'],
			'tumblr'    =>  ['app_key' , 'app_secret']
		];
		$appSupports = [
			'fb'        =>  ['app_id' , 'app_key'],
			'twitter'   =>  ['app_key' , 'app_secret'],
			'linkedin'  =>  ['app_id' , 'app_secret'],
			'vk'        =>  ['app_id' , 'app_secret'],
			'pinterest' =>  ['app_id' , 'app_secret'],
			'reddit'    =>  ['app_id' , 'app_secret'],
			'tumblr'    =>  ['app_key' , 'app_secret']
		];

		if( !isset($appSupports[$driver]) )
		{
			response(false);
		}

		$checkParams = [];
		$checkParams['user_id'] = get_current_user_id();
		$checkParams['driver'] = $driver;
		foreach( $appSupports[$driver] AS $field1 )
		{
			if( empty($data[$field1]) )
			{
				response(false , $field1. ' ' . esc_html__('field is empty!' , 'fs-poster'));
			}

			$checkParams[$field1] = $data[$field1];
		}

		$checkAppIdExist = wpFetch('apps' , $checkParams);
		if( $checkAppIdExist )
		{
			response(false , ['error_msg'=>esc_html__('This app has already been added.' , 'fs-poster')]);
		}

		if( $driver == 'fb' )
		{
			require_once LIB_DIR . "fb/FacebookLib.php";
			$validateApp = FacebookLib::validateAppSecret( $data['app_id'] , $data['app_key'] );

			if( !$validateApp )
			{
				response(false , ['error_msg' => esc_html__('App ID or Secret is invalid!' , 'fs-poster')]);
			}
			else if( ($checkResult = checkPermission( $validateApp['permissions'] )) !== true )
			{
				response(false , ['error_msg' => $checkResult]);
			}
		}

		$checkParams['is_public'] = 0;
		$checkParams['is_standart'] = 0;
		$checkParams['name'] = isset($validateApp['name']) ? $validateApp['name'] : (empty($data['app_id']) ? $data['app_key'] : $data['app_id']);

		wpDB()->insert(wpTable('apps') , $checkParams);

		response(true, ['id' => wpDB()->insert_id , 'name' => esc_html($checkParams['name'])]);
	}
}