<?php
defined('ABSPATH') or exit();

class AdminMenuClass
{
	private $menus = ['account' , 'nodes' , 'settings' , 'app' , 'posts' , 'insights' , 'schedule' , 'schedule' , 'share'];

	public function __construct()
	{
		add_action('init' , function()
		{
			$plgnVer = get_option('fs_poster_plugin_installed' , '0');

			$hideFSPosterForRoles = explode('|' , get_option('fs_hide_menu_for' , ''));

			$userInf = wp_get_current_user();
			$userRoles = (array)$userInf->roles;

			if( !in_array('administrator' , $userRoles) )
			{
				foreach ($userRoles AS $roleId)
				{
					if( in_array( $roleId , $hideFSPosterForRoles ) )
					{
						return;
					}
				}
			}

			if( empty( $plgnVer ) )
			{
				add_action( 'admin_menu', function()
				{
					add_menu_page(
						'FS Poster',
						'FS Poster',
						'read',
						'fs-poster',
						array( $this, 'app_install' ),
						'dashicons-share',
						90
					);
				});
				return;
			}
			else if( $plgnVer != getVersion() )
			{
				$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');
				if( $fsPurchaseKey != '' )
				{
					$result = AjaxClass::updatePlugin( $fsPurchaseKey );
					if( $result[0] == false )
					{
						add_action( 'admin_menu', function()
						{
							add_menu_page(
								'FS Poster',
								'FS Poster',
								'read',
								'fs-poster',
								array( $this, 'app_update' ),
								'dashicons-share',
								90
							);
						});

						return;
					}
				}
				else
				{
					add_action( 'admin_menu', function()
					{
						add_menu_page(
							'FS Poster',
							'FS Poster',
							'read',
							'fs-poster',
							array( $this, 'app_update' ),
							'dashicons-share',
							90
						);
					});

					return;
				}

			}

			add_action( 'admin_menu', function()
			{
				add_menu_page(
					'FS Poster',
					'FS Poster',
					'read',
					'fs-poster',
					array( $this, 'app_base' ),
					'dashicons-share',
					90
				);

				add_submenu_page( 'fs-poster', esc_html__('Accounts' , 'fs-poster'), esc_html__('Accounts' , 'fs-poster'),
					'read', 'fs-poster' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Nodes' , 'fs-poster'), esc_html__('Nodes' , 'fs-poster'),
					'read', 'fs-poster-nodes' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Schedule' , 'fs-poster'), esc_html__('Schedule' , 'fs-poster'),
					'read', 'fs-poster-schedule' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Share' , 'fs-poster'), esc_html__('Share' , 'fs-poster'),
					'read', 'fs-poster-share' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Posts' , 'fs-poster'), esc_html__('Posts' , 'fs-poster'),
					'read', 'fs-poster-posts' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Insights' , 'fs-poster'), esc_html__('Insights' , 'fs-poster'),
					'read', 'fs-poster-insights' , array( $this, 'app_base' ));

				add_submenu_page( 'fs-poster', esc_html__('Apps' , 'fs-poster'), esc_html__('Apps' , 'fs-poster'),
					'read', 'fs-poster-app' , array( $this, 'app_base' ));

				if( current_user_can('administrator') )
				{
					add_submenu_page( 'fs-poster', esc_html__('Settings' , 'fs-poster'), esc_html__('Settings' , 'fs-poster'),
						'read', 'fs-poster-settings' , array( $this, 'app_base' ));
				}

			} );
		});


	}

	public function app_base()
	{
		$menuKey = _get('page' , reset($this->menus) , 'string');
		$menuKey = str_replace('fs-poster-' , '' , $menuKey);
		if( !in_array($menuKey , $this->menus) )
		{
			$menuKey = reset($this->menus);
		}

		require_once VIEWS_DIR . "app_base.php";
	}

	public function app_install()
	{
		require_once VIEWS_DIR . "app_install.php";
	}

	public function app_update()
	{
		require_once VIEWS_DIR . "app_update.php";
	}
}

$my_settings_page = new AdminMenuClass();