<?php
/*
 * Plugin Name: HYP Poster
 * Description: Facebook, Twitter , Instagram, Google+, Linkedin, Reddit, Tumblr, VK, Pinterest Auto Poster Plugin. Post WooCommerce products. Schedule your posts i.e
 * Version: 3.0
 * Author: HYP
 * Author URI: https://github.com/hypericumimpex/hyp-post
 * License: GNU General Public License v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: fs-poster
 */


if ( !defined( 'ABSPATH' ) )
{
	exit;
}

define('FS_ROOT_DIR', __DIR__);

require_once "includes/Config.php";
require_once FS_INCLUDES_DIR . 'CronJob.php';

$fsPosterDisabled = get_option('fs_plugin_disabled', '0');

add_action('FSregisterSession', 'FSregisterSession');

if( $fsPosterDisabled != '1' && $fsPosterDisabled != '2' && get_option('fs_poster_plugin_installed' , '0') )
{
	require_once "includes/PostMetaBox.php";
}

if( is_admin() )
{
	if( $fsPosterDisabled != '1' && $fsPosterDisabled != '2' )
	{
		require_once "includes/AjaxClass.php";
		require_once "includes/ModalClass.php";
	}

	require_once "includes/AdminMenuClass.php";

	add_action('admin_enqueue_scripts' , function()
	{
		wp_register_script('fs-code.js', plugins_url( 'js/fs-code.js', __FILE__ ) , array( 'jquery' ) , '3.0.0');
		wp_enqueue_script( 'fs-code.js' );

		wp_enqueue_style( 'fs-code.css', plugins_url('css/fs-code.css', __FILE__) , [] , '3.0.0' );
		wp_enqueue_style( 'font_aweasome', '//use.fontawesome.com/releases/v5.0.13/css/all.css' );
	});

	$activationKey = get_option('fs_poster_plugin_purchase_key' , '');

	if( !empty($activationKey) )
	{
		require_once FS_LIB_DIR . 'plugin-updates/plugin-update-checker.php';
		$updater = Puc_v4_Factory::buildUpdateChecker(FS_API_URL . 'api.php', __FILE__ , 'fs-poster' );

		$updater->addQueryArgFilter(function( $query ) use( $activationKey )
		{
			$query['act']			= 'check_update';
			$query['domain']		= site_url();
			$query['purchase_code']	= $activationKey;

			return $query;
		});
	}
}
else if( $fsPosterDisabled != '1' && $fsPosterDisabled != '2' )
{
	require_once "includes/SiteController.php";
}

add_action( 'plugins_loaded', function()
{
	load_plugin_textdomain( 'fs-poster', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links)
{
	$newLinks = [
		'<a href="https://support.fs-code.com" target="_blank">' . __('Support', 'fs-poster') . '</a>',
		'<a href="https://www.fs-poster.com/doc/" target="_blank">' . __('Doc', 'fs-poster') . '</a>'
	];

	return array_merge($newLinks, $links);
});

add_action( 'init', function ()
{
	register_post_type( 'fs_post', [
		'labels'		=> [
			'name'			=> __( 'FS Posts' ),
			'singular_name'	=> __( 'FS Post' )
		],
		'public'		=> false,
		'has_archive'	=> true
	]);
	register_post_type( 'fs_post_tmp', [
		'labels'		=> [
			'name'			=> __( 'FS Posts' ),
			'singular_name'	=> __( 'FS Post' )
		],
		'public'		=> false,
		'has_archive'	=> true
	]);
});