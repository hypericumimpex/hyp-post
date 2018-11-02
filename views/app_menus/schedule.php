<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$view = _get('view' , 'calendar' , 'string' , ['list', 'calendar']);

switch ($view)
{
	case 'list':
		require_once VIEWS_DIR . '/app_menus/schedule/list_view.php';
		break;
	case 'calendar':
		require_once VIEWS_DIR . '/app_menus/schedule/calendar_view.php';
		break;
}

