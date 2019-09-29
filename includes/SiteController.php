<?php
defined('ABSPATH') or exit();

class SiteController
{
	public function __construct( )
	{
		add_action( 'wp', array( $this, 'constructFns' ) );
	}

	public function constructFns()
	{
		$this->postCounter();

		$this->FBRedirect();
		$this->FBCallback();

		$this->twitterRedirect();
		$this->twitterCallback();

		$this->linkedinRedirect();
		$this->linkedinCallback();

		$this->pinterestRedirect();
		$this->pinterestCallback();

		$this->redditRedirect();
		$this->redditCallback();

		$this->tumblrRedirect();
		$this->tumblrCallback();

		$this->okRedirect();
		$this->okCallback();

		$this->mediumRedirect();
		$this->mediumCallback();

		$this->standartFSApp();
	}

	public function postCounter()
	{
		if( is_single() || is_page() )
		{
			global $post;
			if( isset( $post->ID ) && isset($_GET['feed_id']) && is_numeric($_GET['feed_id']) && $_GET['feed_id'] > 0 )
			{
				$post_id = $post->ID;
				$feed_id = (int)$_GET['feed_id'];

				FSwpDB()->query( FSwpDB()->prepare("UPDATE " . FSwpTable('feeds') . " SET visit_count=visit_count+1 WHERE id=%d AND post_id=%d", [$feed_id , $post_id] ) );
			}
		}
	}

	public function FBCallback()
	{
		if( isset($_GET['fb_callback']) && $_GET['fb_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "fb/FacebookLib.php";

			FacebookLib::getAccessToken();
		}
	}

	public function FBRedirect()
	{
		if( isset($_GET['fb_app_redirect']) && is_numeric($_GET['fb_app_redirect']) && $_GET['fb_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['fb_app_redirect'];
			require_once FS_LIB_DIR . "fb/FacebookLib.php";

			$link = FacebookLib::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function twitterCallback()
	{
		if( isset($_GET['twitter_callback']) && $_GET['twitter_callback'] == '1'
			&& isset($_GET['oauth_verifier']) && is_string($_GET['oauth_verifier']) && !empty($_GET['oauth_verifier'])
			&& isset($_GET['oauth_token']) && is_string($_GET['oauth_token']) && !empty($_GET['oauth_token'])
		)
		{
			do_action('FSregisterSession');

			if( !isset($_SESSION['save_app_id']) || !isset($_SESSION['oauth_token']) || !isset($_SESSION['oauth_token_secret']) || $_GET['oauth_token'] != $_SESSION['oauth_token'] )
			{
				unset($_SESSION['save_app_id']);
				unset($_SESSION['oauth_token']);
				unset($_SESSION['oauth_token_secret']);
				print 'Error!1';
				exit();
			}

			$appId = (int)$_SESSION['save_app_id'];

			$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'twitter']);

			$proxy = '';
			if( isset($_SESSION['fs_proxy_save']) )
			{
				$proxy = is_string($_SESSION['fs_proxy_save']) ? $_SESSION['fs_proxy_save'] : '';

				unset($_SESSION['fs_proxy_save']);
			}

			require_once FS_LIB_DIR . 'twitter/autoload.php';
			require_once FS_LIB_DIR . 'twitter/TwitterLib.php';

			$connection = new Abraham\TwitterOAuth\TwitterOAuth($appInf['app_key'], $appInf['app_secret'], $_SESSION['oauth_token'], $_SESSION['oauth_token_secret'] , $proxy);
			$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_GET['oauth_verifier']));

			unset($_SESSION['save_app_id']);
			unset($_SESSION['oauth_token']);
			unset($_SESSION['oauth_token_secret']);

			if( !( isset($access_token['oauth_token']) && isset($access_token['oauth_token_secret']) ) )
			{
				print 'Error access token!';
				exit();
			}

			TwitterLib::authorizeUser($appInf , $access_token['oauth_token'] , $access_token['oauth_token_secret'] , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
	}

	public function twitterRedirect()
	{
		if( isset($_GET['twitter_app_redirect']) && is_numeric($_GET['twitter_app_redirect']) && $_GET['twitter_app_redirect'] > 0 )
		{
			do_action('FSregisterSession');
			$appId = (int)$_GET['twitter_app_redirect'];

			$appInf = FSwpFetch('apps' , ['id' => $appId , 'driver' => 'twitter']);
			if( !$appInf )
			{
				print 'Error!';
				exit();
			}

			$_SESSION['save_app_id'] = $appId;
			$_SESSION['fs_proxy_save'] = FS_get('proxy' , '' , 'string');

			require_once __DIR__ . '/../lib/twitter/autoload.php';

			try
			{
				$connection = new Abraham\TwitterOAuth\TwitterOAuth($appInf['app_key'], $appInf['app_secret'] , null ,null , $_SESSION['fs_proxy_save']);
				$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => site_url() . '/?twitter_callback=1'));
			}
			catch (Exception $e)
			{
				print $e->getMessage();
				exit();
			}

			$_SESSION['oauth_token'] = $request_token['oauth_token'];
			$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
			$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
			header('Location:' . $url);
			exit();
		}
	}

	public function linkedinCallback()
	{
		if( isset($_GET['linkedin_callback']) && $_GET['linkedin_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "linkedin/Linkedin.php";

			Linkedin::getAccessToken();
		}
	}

	public function linkedinRedirect()
	{
		if( isset($_GET['linkedin_app_redirect']) && is_numeric($_GET['linkedin_app_redirect']) && $_GET['linkedin_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['linkedin_app_redirect'];
			require_once FS_LIB_DIR . "linkedin/Linkedin.php";

			$link = Linkedin::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function pinterestCallback()
	{
		if( isset($_GET['pinterest_callback']) && $_GET['pinterest_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "pinterest/Pinterest.php";

			Pinterest::getAccessToken();
		}
	}

	public function pinterestRedirect()
	{
		if( isset($_GET['pinterest_app_redirect']) && is_numeric($_GET['pinterest_app_redirect']) && $_GET['pinterest_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['pinterest_app_redirect'];
			require_once FS_LIB_DIR . "pinterest/Pinterest.php";

			$link = Pinterest::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function redditCallback()
	{
		if( isset($_GET['reddit_callback']) && $_GET['reddit_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "reddit/Reddit.php";

			Reddit::getAccessToken();
		}
	}

	public function redditRedirect()
	{
		if( isset($_GET['reddit_app_redirect']) && is_numeric($_GET['reddit_app_redirect']) && $_GET['reddit_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['reddit_app_redirect'];
			require_once FS_LIB_DIR . "reddit/Reddit.php";

			$link = Reddit::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function tumblrCallback()
	{
		if( isset($_GET['tumblr_callback']) && $_GET['tumblr_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "tumblr/Tumblr.php";

			Tumblr::getAccessToken();
		}
	}

	public function tumblrRedirect()
	{
		if( isset($_GET['tumblr_app_redirect']) && is_numeric($_GET['tumblr_app_redirect']) && $_GET['tumblr_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['tumblr_app_redirect'];
			require_once FS_LIB_DIR . "tumblr/Tumblr.php";

			$link = Tumblr::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function okCallback()
	{
		if( isset($_GET['ok_callback']) && $_GET['ok_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "ok/Odnoklassniki.php";

			Odnoklassniki::getAccessToken();
		}
	}

	public function okRedirect()
	{
		if( isset($_GET['ok_app_redirect']) && is_numeric($_GET['ok_app_redirect']) && $_GET['ok_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['ok_app_redirect'];
			require_once FS_LIB_DIR . "ok/Odnoklassniki.php";

			$link = Odnoklassniki::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}

	public function mediumCallback()
	{
		if( isset($_GET['medium_callback']) && $_GET['medium_callback'] == '1' )
		{
			require_once FS_LIB_DIR . "medium/Medium.php";

			Medium::getAccessToken();
		}
	}

	public function mediumRedirect()
	{
		if( isset($_GET['medium_app_redirect']) && is_numeric($_GET['medium_app_redirect']) && $_GET['medium_app_redirect'] > 0 )
		{
			$appId = (int)$_GET['medium_app_redirect'];
			require_once FS_LIB_DIR . "medium/Medium.php";

			$link = Medium::getLoginURL($appId);
			header('Location: ' . $link);
			exit();
		}
	}


	public function standartFSApp()
	{
		$supportedFSApps	= ['fb', 'twitter' , 'linkedin' , 'pinterest' , 'reddit' , 'tumblr' , 'ok', 'medium'];

		$sn                 = FS_get('sn' , '' , 'string' , $supportedFSApps);
		$callback           = FS_get('fs_app_redirect' , '0' , 'num' , ['1']);
		$proxy              = FS_get('proxy' , '' , 'string');

		if( !$callback || empty($sn) )
			return;

		$appInf = FSwpFetch('apps' , ['driver' => $sn , 'is_standart' => '1']);

		if( $sn == 'fb' )
		{
			$access_token	= FS_get('access_token' , '' , 'string');

			if( empty($access_token) )
				return;

			require_once FS_LIB_DIR . 'fb/FacebookLib.php';

			FacebookLib::authorizeFbUser($appInf['id'] , $access_token , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'twitter' )
		{
			$oauth_token = FS_get('oauth_token' , '' , 'string');
			$oauth_token_secret = FS_get('oauth_token_secret' ,'' , 'string');

			if( empty($oauth_token) || empty($oauth_token_secret) )
				return;

			require_once FS_LIB_DIR . 'twitter/TwitterLib.php';

			TwitterLib::authorizeUser($appInf , $oauth_token , $oauth_token_secret , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'linkedin' )
		{
			$access_token = FS_get('access_token' , '' , 'string');
			$expire_in = FS_get('expire_in' ,'' , 'string');

			if( empty($access_token) || empty($expire_in) )
				return;

			require_once FS_LIB_DIR . 'linkedin/Linkedin.php';

			Linkedin::authorizeLinkedinUser($appInf['id'] , $access_token , $expire_in , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'pinterest' )
		{
			$access_token = FS_get('access_token' , '' , 'string');

			if( empty($access_token) )
				return;

			require_once FS_LIB_DIR . 'pinterest/Pinterest.php';

			Pinterest::authorizePinterestUser($appInf['id'] , $access_token , $proxy);

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'reddit' )
		{
			$access_token = FS_get('access_token' , '' , 'string');
			$refreshToken = FS_get('refresh_token' , '' , 'string');
			$expiresIn = FS_get('expires_in' , '' , 'string');

			if( empty($access_token) || empty($refreshToken) || empty($expiresIn) )
				return;

			require_once FS_LIB_DIR . 'reddit/Reddit.php';

			Reddit::authorizeRedditUser( $appInf['id'] , $access_token , $refreshToken , $expiresIn , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'tumblr' )
		{
			$access_token = FS_get('access_token' , '' , 'string');
			$access_token_secret = FS_get('access_token_secret' , '' , 'string');

			if( empty($access_token) || empty($access_token_secret) )
				return;

			// sync timezone for FS Poster server...
			date_default_timezone_set('Asia/Baku');

			require_once FS_LIB_DIR . 'vendor/autoload.php';
			require_once FS_LIB_DIR . 'tumblr/Tumblr.php';

			Tumblr::authorizeTumblrUser( $appInf['id'] , $appInf['app_key'] , $appInf['app_secret'] , $access_token , $access_token_secret , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'ok' )
		{
			$access_token = FS_get('access_token' , '' , 'string');
			$refreshToken = FS_get('refresh_token' , '' , 'string');
			$expiresIn = FS_get('expires_in' , '' , 'string');

			if( empty($access_token) || empty($refreshToken) || empty($expiresIn) )
				return;

			require_once FS_LIB_DIR . 'ok/OdnoKlassniki.php';

			OdnoKlassniki::authorizeOkUser( $appInf['id'] , $appInf['app_key'] , $appInf['app_secret'] , $access_token , $refreshToken , $expiresIn , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
		else if( $sn == 'medium' )
		{
			$access_token = FS_get('access_token' , '' , 'string');
			$refreshToken = FS_get('refresh_token' , '' , 'string');
			$expiresIn = FS_get('expires_in' , '' , 'string');

			if( empty($access_token) || empty($refreshToken) || empty($expiresIn) )
				return;

			require_once FS_LIB_DIR . 'medium/Medium.php';

			Medium::authorizeMediumUser( $appInf['id'] , $access_token , $refreshToken , $expiresIn , $proxy );

			print esc_html__('Loading...' , 'fs-poster') . ' <script>if( typeof window.opener.compleateOperation == "function" ){ window.opener.compleateOperation(true);window.close();}else{document.write("'.esc_html__('Error! Please try again!' , 'fs-poster').'");} </script>';
			exit();
		}
	}
}

new SiteController();