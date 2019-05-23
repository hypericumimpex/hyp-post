<?php
defined('ABSPATH') or exit();

class AdminMenuClass
{
	private $menus = ['account' , 'nodes' , 'settings' , 'app' , 'posts' , 'insights' , 'schedule' , 'schedule' , 'share'];

	public function __construct()
	{
		add_action('init' , function()
		{
			$this->getNotifications();
			$res1 = $this->checkLicense();
			if( false === $res1 )
			{
				return;
			}

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

	public function app_disable()
	{
		require_once VIEWS_DIR . "app_disable.php";
	}

	public function app_update()
	{
		require_once VIEWS_DIR . "app_update.php";
	}

	public function getNotifications()
	{
		$lastTime = get_option('fs_license_last_checked_time', 0);

		if( time() - $lastTime < 10 * 60 * 60 )
			return;

		$fsPurchaseKey = get_option('fs_poster_plugin_purchase_key' , '');

		$checkPurchaseCodeURL = FS_API_URL . "api.php?act=get_notifications&purchase_code=" . $fsPurchaseKey . "&domain=" . site_url();
		$result2 = file_get_contents($checkPurchaseCodeURL);
		$result = json_decode($result2 , true);

		if( $result['action'] == 'empty' )
		{
			update_option('fs_plugin_alert', '');
			update_option('fs_plugin_disabled', '0');
		}
		else if( $result['action'] == 'warning' && !empty( $result['message'] ) )
		{
			update_option('fs_plugin_alert', $result['message']);
			update_option('fs_plugin_disabled', '0');
		}
		else if( $result['action'] == 'disable' )
		{
			if( !empty( $result['message'] ) )
			{
				update_option('fs_plugin_alert', $result['message']);
			}
			update_option('fs_plugin_disabled', '1');
		}
		else if( $result['action'] == 'error' )
		{
			if( !empty( $result['message'] ) )
			{
				update_option('fs_plugin_alert', $result['message']);
			}
			update_option('fs_plugin_disabled', '2');
		}

		if( !empty( $result['remove_license'] ) )
		{
			delete_option('fs_poster_plugin_installed');
			delete_option('fs_poster_plugin_purchase_key');
		}

		update_option('fs_license_last_checked_time', time());
	}

	public function checkLicense()
	{
		$alert = get_option('fs_plugin_alert');
		$disabled = get_option('fs_plugin_disabled', '0');

		if( $disabled == '1' )
		{
			add_action( 'admin_menu', function()
			{
				add_menu_page(
					'FS Poster (!)',
					'FS Poster (!)',
					'read',
					'fs-poster',
					array( $this, 'app_disable' ),
					'dashicons-share',
					90
				);
			});

			return false;
		}
		else if( $disabled == '2' )
		{
			if( !empty( $alert ) )
				print $alert;

			exit();
		}

		if( !empty( $alert ) )
		{
			add_action( 'admin_notices', function() use( $alert )
			{
				?>
				<div class="notice notice-error">
					<p><?php _e( $alert, 'fs-poster' ); ?></p>
				</div>
				<?php
			});
		}

		return true;
	}

}

$my_settings_page = new AdminMenuClass();