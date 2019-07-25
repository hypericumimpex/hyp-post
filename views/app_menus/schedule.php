<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = FS_get('view' , 'list' , 'string' , ['list', 'calendar']);

switch ($view)
{
	case 'list':
		require_once FS_VIEWS_DIR . '/app_menus/schedule/list_view.php';
		break;
	case 'calendar':
		require_once FS_VIEWS_DIR . '/app_menus/schedule/calendar_view.php';
		break;
}

