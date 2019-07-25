<?php

trait FSPNodes
{

	public function settings_node_activity_change()
	{
		$id = FS_post('id' , '0' , 'num');
		$checked = FS_post('checked' , -1 , 'num' , ['0','1']);
		$filter_type = FS_post('filter_type' , '' , 'string' , ['in' , 'ex']);
		$categories = FS_post('categories' , [], 'array');

		if( !($id > 0 && $checked > -1) )
		{
			FSresponse(false );
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
			FSresponse(false , 'Please select categories and filter type!');
		}

		$categoriesArr = empty($categoriesArr) ? null : $categoriesArr;
		$filter_type = empty($filter_type) ? 'no' : $filter_type;

		$checkAccount = FSwpDB()->get_row("SELECT * FROM " . FSwpTable('account_nodes') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			FSresponse(false , 'Community not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() && $checkAccount['is_public'] != 1 )
		{
			FSresponse(false , 'Community not found or you do not have a permission for this community!');
		}

		if( $checked )
		{
			$checkIfIsActive = FSwpFetch('account_node_status' , [
				'node_id'		=>	$id,
				'user_id'		=>	get_current_user_id()
			]);

			if( !$checkIfIsActive )
			{
				FSwpDB()->insert(FSwpTable('account_node_status') , [
					'node_id'		=>	$id,
					'user_id'		=>	get_current_user_id(),
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				]);
			}
			else
			{
				FSwpDB()->update(FSwpTable('account_node_status') , [
					'filter_type'	=>	$filter_type,
					'categories'	=>	$categoriesArr
				] , ['id' => $checkIfIsActive['id']]);
			}
		}
		else
		{
			FSwpDB()->delete(FSwpTable('account_node_status') , [
				'node_id'		=>	$id,
				'user_id'		=>	get_current_user_id()
			]);
		}

		FSresponse(true);
	}

	public function settings_node_make_public()
	{
		if(!( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0 ))
		{
			FSresponse(false);
		}

		$id = (int)$_POST['id'];

		$getNodeInf = FSwpFetch('account_nodes' , $id);

		if( !$getNodeInf )
		{
			FSresponse(false , 'Community not found!');
		}

		if( $getNodeInf['user_id'] != get_current_user_id() )
		{
			FSresponse(false, 'This is not one of you added comunity. Therefore you do not have a permission for make public/private this community.');
		}

		$newStatus = (int)(!$getNodeInf['is_public']);

		FSwpDB()->update(FSwpTable('account_nodes') , [ 'is_public' => $newStatus ] , [ 'id' => $id ]);

		FSresponse(true);
	}

	public function settings_node_delete()
	{
		if(!( isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0 ))
		{
			FSresponse(false);
		}

		$id = (int)$_POST['id'];

		$checkAccount = FSwpDB()->get_row("SELECT * FROM " . FSwpTable('account_nodes') . " WHERE id='" . $id . "'" , ARRAY_A);

		if( !$checkAccount )
		{
			FSresponse(false , 'Community not found!');
		}

		if( $checkAccount['user_id'] != get_current_user_id() )
		{
			FSresponse(false , 'You do not have a permission for deleting this community!');
		}

		FSwpDB()->delete(FSwpTable('account_nodes'), ['id' => $id]);
		FSwpDB()->delete(FSwpTable('account_node_status'), ['node_id' => $id]);

		FSresponse(true);
	}
}