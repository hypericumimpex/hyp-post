<?php defined('MODAL') or exit();?>
<?php

$accountsList = FSwpDB()->get_results(
	FSwpDB()->prepare('SELECT *, \'account\' AS node_type, \'account\' AS category FROM ' . FSwpTable('accounts') . " WHERE (user_id=%d OR is_public=1) AND driver<>'tumblr' ORDER BY driver", [get_current_user_id()])
	, ARRAY_A
);


$pagesList = FSwpDB()->get_results(
	FSwpDB()->prepare('SELECT * FROM ' . FSwpTable('account_nodes') . " WHERE (user_id=%d OR is_public=1) ORDER BY node_type", [get_current_user_id()])
	, ARRAY_A
);

$nodesAll = array_merge($accountsList , $pagesList);

$nodesAllByKey = [];
$nodesAllSorted = [ '-' => [] ];

foreach ( $nodesAll AS &$nodeInf )
{
	$nodesAllByKey[ $nodeInf['node_type'] . ':'.(int)$nodeInf['id'] ] = $nodeInf;
}

foreach ( $nodesAll AS $nodeInf2 )
{
	if( isset( $nodeInf2['account_id'] ) && isset( $nodesAllByKey[ 'account:' . $nodeInf2['account_id'] ] ) )
	{
		$nodesAllSorted[ 'account:' . $nodeInf2['account_id'] ][] = $nodeInf2['node_type'] . ':'.(int)$nodeInf2['id'];
	}
	else
	{
		$nodesAllSorted[ '-' ][] = $nodeInf2['node_type'] . ':'.(int)$nodeInf2['id'];
	}
}

?>

<style>
	#proModal<?=$mn?> .modal-content
	{
		background: transparent !important;

		width: 365px;
		height: 500px;
	}
	#proModal<?=$mn?> .nodes_container
	{
		display: flex;
		flex-direction: column;
		width: 365px;
		height: 500px;
		background: #FBFBFD;

		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		border-radius: 5px;
		overflow: hidden;
	}
	#proModal<?=$mn?> .nodes_list
	{
		padding: 8px 0;
		overflow: auto;
		height: 100%;
	}
	#proModal<?=$mn?> .node_toolbar
	{
		padding: 10px 20px;
	}
	#proModal<?=$mn?> .node_toolbar
	{
		display: flex;
		align-items: stretch;
	}

	#proModal<?=$mn?> .search_input
	{
		position: relative;
		flex-shrink: 1;
		width: 100%;
	}
	#proModal<?=$mn?> .search_input>i
	{
		position: absolute;
		top: 3px;
		bottom: 0;
		margin: auto;
		height: 15px;
		right: 15px;
		color: #CCC;
	}
	#proModal<?=$mn?> .search_input>input
	{
		width: 100%;
		height: 35px;
		padding-left: 20px;
		color: #999;
		font-weight: 600;

		border: 0 !important;
		outline: none !important;

		-webkit-box-shadow: 0 0 3px 0 #DDD !important;
		-moz-box-shadow: 0 0 3px 0 #DDD !important;
		box-shadow: 0 0 3px 0 #DDD !important;

		-webkit-border-radius: 50px;
		-moz-border-radius: 50px;
		border-radius: 50px;
	}

	#proModal<?=$mn?> .search_input>input::placeholder
	{
		color: #CCC;
	}

	#proModal<?=$mn?> .node_div
	{
		position: relative;
		margin: 0 20px 10px;
		padding: 13px;
		border-bottom: 1px solid #EEE;
		background: #FFF;
		-webkit-box-shadow: 0px 0px 5px 0 #DDD;
		-moz-box-shadow: 0px 0px 5px 0 #DDD;
		box-shadow: 0px 0px 5px 0 #DDD;
		-webkit-border-radius: 4px;
		-moz-border-radius: 4px;
		border-radius: 4px;
		cursor: pointer;
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
	}
	#proModal<?=$mn?> .node_div:hover
	{
		background: #f5f9fa;
	}
	#proModal<?=$mn?> .node_div>div
	{
		float: left;
	}
	#proModal<?=$mn?> .node_div:after
	{
		content: " ";
		clear: both;
		display: block;
	}
	#proModal<?=$mn?> .node_img>img
	{
		-webkit-border-radius: 50% !important;
		-moz-border-radius: 50% !important;
		border-radius: 50% !important;
		width: 40px;
		height: 40px;
	}
	#proModal<?=$mn?> .node_label
	{
		padding-left: 10px;
	}
	#proModal<?=$mn?> .node_label_title
	{
		color: #888;
		font-size: 14px;
		font-weight: 600;
		white-space: nowrap;
		overflow: hidden;
		max-width: 180px;
	}
	#proModal<?=$mn?> .node_category
	{
		font-weight: 500;
		color: #95a5a6;
		padding-top: 5px;
		white-space: nowrap;
		overflow: hidden;
		max-width: 180px;
	}

	#proModal<?=$mn?> .node_box_label
	{
		text-align: center;
		padding-top: 10px;
		position: relative;
		height: 37px;
	}
	#proModal<?=$mn?> .node_box_label>div
	{
		position: absolute;
		width: 120px;
		height: 22px;
		color: #FFF;
		margin: auto;
		left: 0;
		right: 0;
		background: #94A0B2;
		-webkit-border-radius: 15px;
		-moz-border-radius: 15px;
		border-radius: 15px;
		font-weight: 600;
		font-size: 14px;
		line-height: 20px;
		border: 5px solid #FBFBFD;
	}
	#proModal<?=$mn?> .node_box_label:before
	{
		content: '';
		width: calc(100% - 60px);
		height: 1px;
		border-top: 1px solid #94A0B2;
		top: 16px;
		bottom: 0px;
		left: 0px;
		margin: 10px 30px;
		position: absolute;
	}

	#proModal<?=$mn?> .node_div.sub_node_d
	{
		margin-left: 40px;
		position: relative;
	}

	#proModal<?=$mn?> .node_div.sub_node_d:before
	{
		position: absolute;
		top: 36px;
		left: -18px;
		content: ' ';
		width: 17px;
		height: 1px;
		border-top: 1px solid #EEE;
	}
</style>

<div class="nodes_container">
	<div class="node_box_label">
		<div><?=esc_html__('SELECT' , 'fs-poster')?></div>
	</div>
	<div class="node_toolbar">
		<div class="search_input">
			<input type="text" placeholder="<?=esc_html__('Search...' , 'fs-poster')?>">
			<i class="fa fa-search"></i>
		</div>
	</div>
	<div class="nodes_list">
		<?php

		function dontShowNodesArr()
		{
			$notShowList = FS_post('dont_show' , [] , 'array');

			$listArr = [];
			foreach ( $notShowList AS $nodeKey )
			{
				$nodeKey = explode(':', $nodeKey);
				$nodeKey = count($nodeKey) > 2 ? $nodeKey[0] . ':' . $nodeKey[1] . ':' . $nodeKey[2] : '';

				if( !empty($nodeKey) )
				{
					$listArr[] = $nodeKey;
				}
			}

			return $listArr;
		}

		function printNodeCart( $node, $isSub = false )
		{
			$val = esc_html($node['driver'].':'.$node['node_type']).':'.(int)$node['id'];
			if( in_array( $val , dontShowNodesArr() ) )
			{
				return;
			}

			$isSub = $isSub ? ' sub_node_d' : ''

			?>
			<div class="node_div<?=$isSub?>" data-id="<?=$val?>">
				<div class="node_img"><img src="<?=FSprofilePic($node);?>" onerror="$(this).attr('src', '<?=plugin_dir_url(__FILE__).'../../images/no-photo.png'?>');"></div>
				<div class="node_label">
					<div class="node_label_title"><?=esc_html($node['name']);?></div>
					<div class="node_category"><i class="far fa-paper-plane"></i> <?=esc_html(ucfirst($node['driver']) ) . ' <i class="fa fa-chevron-right " style="font-size: 10px; color: #CCC;"></i> ' . esc_html( $node['node_type'] );?></div>
				</div>
			</div>
			<?php
		}

		foreach ($nodesAllSorted[ '-' ] AS $nodeKey)
		{
			$node = isset( $nodesAllByKey[ $nodeKey ] ) ? $nodesAllByKey[ $nodeKey ] : [];;

			if( empty($node) )
				continue;

			printNodeCart( $node );

			if( isset( $nodesAllSorted[ $nodeKey ] ) )
			{

				foreach( $nodesAllSorted[ $nodeKey ] AS $nodeSubKey )
				{
					$subNode = isset( $nodesAllByKey[ $nodeSubKey ] ) ? $nodesAllByKey[ $nodeSubKey ] : [];;

					if( empty($subNode) )
						continue;

					printNodeCart( $subNode, true );
				}

			}

		}
		?>
	</div>
</div>

<span class="close" data-modal-close="true">&times;</span>

<script>

	jQuery(document).ready(function()
	{
		$("#proModal<?=$mn?> .node_div").click(function()
		{
			var dataId = $(this).attr('data-id');

			if( typeof addNodeToList == 'function' )
			{
				addNodeToList(dataId , $(this).find('.node_img>img').attr('src') , $(this).find('.node_label_title').text().trim());
			}

			$(this).remove();

			fsCode.toast("<?=esc_html__('Added to list!' , 'fs-poster')?>" , 'success');
		});

		$("#proModal<?=$mn?> .search_input>input").keyup(function()
		{
			var val = $(this).val();

			$(this).closest('.node_toolbar').next('.nodes_list').children('.node_div:not(:contains("' + fsCode.htmlspecialchars(val) + '"))').hide(500);
			$(this).closest('.node_toolbar').next('.nodes_list').children('.node_div:contains("' + fsCode.htmlspecialchars(val) + '")').show(500);
		});

		jQuery.expr[':'].contains = function(a, i, m) {
			return jQuery(a).text().toUpperCase()
				.indexOf(m[3].toUpperCase()) >= 0;
		};
	});

</script>