<?php

trait FSPNodes
{
	public function settings_node_activity_change()
	{
		$id = _post('id' , '0' , 'num');
		$checked = _post('checked' , -1 , 'num' , ['0','1']);
		$filter_type = _post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = _post('categories' , [], 'array');

		if( !($id > 0 && $checked > -1) )
		{
			response(false );
		}

		$categoriesArr = [];
		foreach($categories AS $categId)
		{
			if(is_numeric($categId) && $categId > 0)
			{
				$categoriesArr[] = (int)$categId;
			}
		}
		$categoriesArr = implode(',' , $categoriesArr);

		if( (!empty($categoriesArr) && empty($filter_type)) || (empty($categoriesArr) && !empty($filter_type)) )
		{
			response(false , 'Please select categories and filter type!');
		}

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) ? 'no' : $filter_type;

		$checkAccount = wpDB()->get_row("SELECT * FROM " . wpTable('account_nodes') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			response(false , 'Community not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			response(false , 'Community not found or you do not have a permission for this community!');
		}

		if( $checked )
		{
			$checkIfIsActive = wpFetch('account_node_status' , [
				'node_id'		=>	$id,
				'user_id'		=>	get_current_user_id()
			]);

			if( !$checkIfIsActive )
			{
				wpDB()->insert(wpTable('account_node_status') , [
					'node_id'		=>	$id,
					'user_id'		=>	get_current_user_id(),
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				]);
			}
			else
			{
				wpDB()->update(wpTable('account_node_status') , [
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				] , ['id' => $checkIfIsActive['id']]);
			}
		}
		else
		{
			wpDB()->delete(wpTable('account_node_status') , [
				'node_id'		=>	$id,
				'user_id'		=>	get_current_user_id()
			]);
		}

		response(true);
	}

	public function settings_node_make_public()
	{
		if(!( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0 ))
		{
			response(false);
		}

		$id = (int)$_POST['id'];

		$getNodeInf = wpFetch('account_nodes' , $id);

		if( !$getNodeInf )
		{
			response(false , 'Community not found!');
		}

		if( $getNodeInf['user_id'] != get_current_user_id() )
		{
			response(false, 'This is not one of you added comunity. Therefore you do not have a permission for make public/private this community.');
		}

		$newStatus = (int)(!$getNodeInf['is_public']);

		wpDB()->update(wpTable('account_nodes') , [ 'is_public' => $newStatus ] , [ 'id' => $id ]);

		response(true);
	}

	public function settings_node_delete()
	{
		if(!( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0 ))
		{
			response(false);
		}

		$id = (int)$_POST['id'];

		$checkAccount = wpDB()->get_row("SELECT * FROM " . wpTable('account_nodes') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			response(false , 'Community not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			response(false , 'You do not have a permission for deleting this community!');
		}

		wpDB()->delete(wpTable('account_nodes'), ['id' => $id]);
		wpDB()->delete(wpTable('account_node_status'), ['node_id' => $id]);

		response(true);
	}
}