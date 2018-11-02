<?php

require_once __DIR__ . '/../vendor/autoload.php';

class GooglePlus
{
	private $_token;
	private $email;
	private $password;
	private $at;
	private $_capcha;
	private $inputs;
	private $cookies;
	private $client;


	public function __construct( $email , $password , $proxy , $cookies = null )
	{
		fsDebug();
		$this->email    = $email;
		$this->password = $password;
		$this->_token   = md5($email.':'.$password);

		if( is_null($cookies) )
		{
			$this->loadUser();
		}
		else
		{
			$this->cookies = $cookies;
		}

		$cookieJar		= new \GuzzleHttp\Cookie\CookieJar(false , $this->cookies);



		$this->client	= new \GuzzleHttp\Client([
			'cookies' 			=>	$cookieJar,
			'allow_redirects'	=>	[ 'max' => 20 ],
			'proxy'				=>	empty($proxy) ? null : $proxy,
			'verify'			=>	false,
			'http_errors'		=>	false,
			'headers'			=>	['User-Agent' => 'NokiaN70/ 5.0741.4.0.1 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1']
		]);
	}

	public function saveData()
	{
		if( !is_null( $this->email ) )
		{
			$checkIfexist = wpFetch('account_sessions' , ['driver' => 'google' , 'username' => $this->email]);
			if( $checkIfexist )
			{
				wpDB()->update(wpTable('account_sessions') , [
					'cookies'	=> json_encode($this->getCookie()),
					'settings'	=>	empty($this->inputs) ? null : json_encode($this->inputs)
				] , ['id' => $checkIfexist['id']]);
			}
			else
			{
				wpDB()->insert(wpTable('account_sessions'), [
					'cookies'	=>	json_encode($this->getCookie()),
					'settings'	=>	empty($this->inputs) ? null : json_encode($this->inputs),
					'username'	=>	$this->email,
					'driver'	=>	'google'
				]);
			}
		}
	}

	public function login( )
	{
		$inputs = $this->getInputs( );
		if( $inputs === true )
		{
			return ['status' => true];
		}

		$result = (string)$this->client->request('POST' , 'https://accounts.google.com/ServiceLoginAuth' , ['form_params' => $inputs ] )->getBody();
		// check capcha
		preg_match( '/class=\"captcha-img\".+img src\=\"(.+)\"/Uis' , $result , $capchaCheck );

		if( isset( $capchaCheck[1] ) )
		{
			$inputs = $this->getInputs( $result );

			$this->inputs = $inputs;
			$capchaURL = trim( str_replace('&amp;' , '&' , $capchaCheck[1]) );
		}

		preg_match('/class\=\"error-msg\".+\>(.+)\<\/span\>/Uis' , $result , $errorMessage);

		if( isset( $errorMessage[1] ) )
		{
			$return = [
				'status'    =>  'error',
				'message'   =>  trim( strip_tags($errorMessage[1]) )
			];

			if( isset( $capchaURL ) )
			{
				$return['capcha_url'] = $capchaURL;
			}

			return $return;
		}

		if( isset( $capchaURL ) )
		{
			return [
				'status'        =>  'capcha',
				'capcha_url'    =>  $capchaURL
			];
		}

		// check for errors!
		preg_match( '/\<div class=\"yTvy3\".*\>(.+)\<\//Uis' , $result , $checkIfError );
		if( isset( $checkIfError[1] ) )
		{
			return [
				'status'    =>  'error',
				'message'   =>  trim( strip_tags($checkIfError[1]) )
			];
		}

		preg_match( '/\<div class=\"H9T9of\".*\>(.+)\<\//Uis' , $result , $checkIfError );
		if( isset( $checkIfError[1] ) )
		{
			return [
				'status'    =>  'error',
				'message'   =>  trim( strip_tags($checkIfError[1]) )
			];
		}


		// check for 2 factor auth...
		if( preg_match( '/\<form.+id=\"challenge\"/Uis' , $result ) )
		{
			preg_match('/\<form.+id=\"challenge\".+\<div.+>(.+\<b.+\>.+\<\/b\>)\<\/div\>/Uis' , $result , $getText);

			$caption = isset($getText[1]) ? strip_tags( $getText[1] ) : '';

			$inputs = $this->parseInputs( $result , 'challenge' );
			$inputs['TrustDevice'] = 'on';
			$this->inputs = $inputs;

			return [
				'status'    =>  'two_factor_auth',
				'message'   =>  strip_tags($caption)
			];
		}

		/*if( preg_match( '/\<ol.+id=\"challengePickerList\"/Uis' , $result ) )
		{
			preg_match('/\<ol.+id=\"challengePickerList\".*\>(.+)<\/ol\>/Uis' , $result , $getOlContainer);

			preg_match_all('/\<form.+\>.+\<\/form\>/Uis' , $getOlContainer[1] , $getForms);

			$allowedMethods = [];
			foreach($getForms[0] AS $formInner)
			{
				preg_match('/\<form.+action\=\"(.+)\"/Uis' , $formInner , $formAction);
				$formAction = $formAction[1];

				$allowedMethods[] = [
					'action'	=>	$formAction,
					'message'	=>	trim( strip_tags( str_replace('>' , '>.' , $formInner) ) )
				];
			}
		}*/

		// /signin/challenge/kpe/4
		if( preg_match( '/\<form.+action=\"(\/signin\/challenge\/[^\"]+)\"/Uis' , $result , $getChallengeURL ) && isset($getChallengeURL[1]) )
		{
			$inputs2 = $this->parseInputs( $result , '\/signin\/challenge\/' , 'action' );
			$challengeURL = 'https://accounts.google.com' . $getChallengeURL[1];

			$challengeId = isset($inputs2['challengeId']) ? $inputs2['challengeId'] : '';

			if( $this->fintVisibleInputType( $result , '\/signin\/challenge\/' , 'action' ) )
			{
				$inputs['form_action'] = $getChallengeURL[1];
				$this->inputs = $inputs2;

				preg_match( '/\<form.+action=\"\/signin\/challenge\/[^\"]+.*\>(.+)\<\/form\>/Uis' , $result , $getMessage );

				return [
					'status'    	=>  'challenge',
					'challenge_id'	=>	$challengeId,
					'message'		=>  isset($getMessage[1]) ? trim( strip_tags($getMessage[1]) ) : 'Challenge required!'
				];
			}
			else
			{
				$result2 = (string)$this->client->request('POST' , $challengeURL , ['form_params' => $inputs2 ] )->getBody();

				if( preg_match( '/\<form.+id\=\"challenge\".+action=\"(\/signin\/challenge\/[^\"]+)\"/Uis' , $result2 , $getChallengeURL2 ) && isset($getChallengeURL2[1]) )
				{
					$inputs3 = $this->parseInputs( $result2 , 'challenge');
					$inputs3['form_action'] = $getChallengeURL2[1];
					$this->inputs = $inputs3;

					preg_match( '/\<form.+id\=\"challenge\".+action=\"\/signin\/challenge\/[^\"]+.*\>(.+)\<\/form\>/Uis' , $result2 , $getMessage );

					return [
						'status'    	=>  'challenge',
						'challenge_id'	=>	$challengeId,
						'message'		=>  isset($getMessage[1]) ? trim( strip_tags($getMessage[1]) ) : 'Challenge required!'
					];
				}
				else
				{
					return [
						'status'    =>  'error',
						'message'   =>  'Challenge error (n=002)! Please use proxy from your real country and try to login again!'
					];
				}
			}

		}

		return ['status' => 'ok'];
	}

	public function challenge1stStep( $pin )
	{
		if( !is_array($this->inputs) )
		{
			return [
				'status'    =>  'error',
				'message'   =>  'Error! Two factor authentication not found! Please try again! (Err no: 001)'
			];
		}

		$inputs = $this->inputs;
		$this->inputs = null;

		$inputs['email'] = $pin;

		$requestURL = $inputs['form_action'];
		unset($inputs['form_action']);

		$result = (string)$this->client->request( 'POST' , 'https://accounts.google.com' . $requestURL , ['form_params' => $inputs])->getBody();

		if( strpos($result , 'https://accounts.google.com/Logout') === false )
		{
			return [
				'status'    =>  'error',
				'message'   =>  'Error (01), please try again!'
			];
		}

		return ['status' => 'ok'];
	}

	public function approve2FactorAuth( $pin )
	{
		if( !is_array($this->inputs) )
		{
			return [
				'status'    =>  'error',
				'message'   =>  'Error! Two factor authentication not found! Please try again! (Err no: 001)'
			];
		}

		$inputs = $this->inputs;
		$this->inputs = null;

		$inputs['Pin'] = $pin;
		unset($inputs['SendMethod']);

		$result = (string)$this->client->request( 'POST' , 'https://accounts.google.com/signin/challenge/ipp/3' , ['form_params' => $inputs])->getBody();
		// check for errors!
		preg_match( '/\<div class=\"yTvy3\".*\>(.+)\<\//Uis' , $result , $checkIfError );
		if( isset( $checkIfError[1] ) )
		{
			return [
				'status'    =>  'error',
				'message'   =>  trim( strip_tags($checkIfError[1]) )
			];
		}

		preg_match( '/\<div class=\"H9T9of\".*\>(.+)\<\//Uis' , $result , $checkIfError );
		if( isset( $checkIfError[1] ) )
		{
			return [
				'status'    =>  'error',
				'message'   =>  trim( strip_tags($checkIfError[1]) )
			];
		}

		// check for 2 factor auth...
		if( preg_match( '/\<form.+id=\"challenge\"/Uis' , $result ) )
		{
			preg_match('/\<form.+id=\"challenge\".+\<div.+>(.+\<b.+\>.+\<\/b\>)\<\/div\>/Uis' , $result , $getText);

			$caption = isset($getText[1]) ? strip_tags( $getText[1] ) : '';

			$inputs = $this->parseInputs( $result , 'challenge' );
			$inputs['TrustDevice'] = 'on';
			$this->inputs = $inputs;

			return [
				'status'    =>  'error',
				'message'   =>  strip_tags($caption)
			];
		}

		return ['status' => 'ok'];
	}

	public function setCapcha( $code )
	{
		$this->_capcha = $code;
	}

	public function getUserInfo()
	{
		$plusPage = (string)$this->client->request('GET' , 'https://plus.google.com')->getBody();

		preg_match('/window\.IJ_values \= (\[.*?\])\;/mi' , $plusPage , $matches);

		if( isset($matches[1]) )
		{
			$jsonInf = json_decode(str_replace(['\x', "'" , ',]' ] , ['' , '"' , ']'] , $matches[1]) , true);

			$userInfo = [
				'id'			=>	isset($jsonInf[45]) ? $jsonInf[45] : null,
				'name'			=>	isset($jsonInf[34]) ? $jsonInf[34] : null,
				'email'			=>	isset($jsonInf[36]) ? $jsonInf[36] : null,
				'gender'		=>	isset($jsonInf[37]) && $jsonInf[37]=='male' ? 1 : 2,
				'profile_image'	=>	isset($jsonInf[46]) ? $jsonInf[46] : null
			];

		}
		else
		{
			$userInfo = [
				'id'			=>	null,
				'name'			=>	null,
				'email'			=>	null,
				'gender'		=>	null,
				'profile_image'	=>	null
			];
		}

		return $userInfo;
	}

	public function sendPost( $postTo , $text , $link = null )
	{
		if( is_null( $link ) )
		{
			$linkParam = null;
		}
		else
		{
			$urlImg = $this->getURLImg( $link );
			if( empty($urlImg) )
			{
				$linkParam = null;
			}
			else
			{
				$linkParam = [["94515327" => [ $link , $urlImg ]]];
			}
		}

		$postToArr = [];

		if( $postTo == 'profile' )
		{
			$postToArr = [[[null,null,1],"Public"]];
		}
		else
		{
			$postToArr = [[[null,null,null,[ $postTo[0] ,null, $postTo[1] ]],"",null,null,null,""]];
		}

		$fReqParam = [
			"af.maf",
			[
				[
					"af.add",
					79255737,
					[
						[
							"79255737" => [ [ [] , [] , $postToArr ] , [[[0,$text,null]]] , null , false , null , $linkParam , [] , null , 199 , false , false ]
						]
					]
				]
			]
		];

		$post = (string)$this->client->request('POST' , 'https://plus.google.com/_/PlusAppUi/mutate' , [
			'form_params'	=> [ 'f.req' => json_encode( $fReqParam ) , 'at' => $this->getAT() ]  ,
			'headers'		=> [ 'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8' ]
		])->getBody();

		preg_match('/\"([0-9]+\/posts\/([^\"]+))/' , $post , $postUrl);

		if( !(isset($postUrl[1]) && isset($postUrl[2])) )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error!'
			];
		}

		$fullUrl = $postUrl[1];
		$postId = $postUrl[2];

		return [
			'status'    => 'ok',
			//'post_url'  => $fullUrl,
			'id'   		=> str_replace('/posts/' , ':' , $fullUrl)
		];
	}

	public function getCommunities( )
	{
		$communitiesHTML = (string)$this->client->request('GET', 'https://plus.google.com/communities')->getBody();

		preg_match_all('/\<a href\=[\'\"]\.?\/communities\/([0-9]+)\/?[\"\'](.+)\<\/a\>/Ui' , $communitiesHTML , $communities);

		$communitiesArr = [];

		foreach($communities[1] AS $arrKey => $cId)
		{
			if( !isset($communities[2][$arrKey]) )
				continue;

			preg_match('/\<img.+class\=\"JZUAbb\".+src\=\"(.+)\"/Ui' , $communities[2][$arrKey] , $getImage);
			$image = isset($getImage[1]) ? $getImage[1] : null;

			preg_match('/\<div.+jsname\=\"r4nke\"\>(.+)\<\/div\>/Ui' , $communities[2][$arrKey] , $getTitle);
			$title = isset($getTitle[1]) ? $getTitle[1] : ' - ';

			preg_match('/\<div.+jsname\=\"VdSJob\"\>([0-9\,\.]+)[^0-9\,\.]+\<\/div\>/Ui' , $communities[2][$arrKey] , $getMemmbersCount);
			$memmbersCount = isset($getMemmbersCount[1]) ? str_replace(['.',','] , '' , $getMemmbersCount[1]) : ' - ';

			$communitiesArr[] = [
				'id'        =>  $cId ,
				'name'      =>  $title ,
				'image'     =>  $image ,
				'members'   =>  $memmbersCount,
				'is_owner'  =>  strpos($communities[2][$arrKey] , '<div class="pVtChb">Owner</div>') !== false
			];
		}

		return $communitiesArr;
	}

	public function getCategories( $communitieId )
	{
		$url = 'https://plus.google.com/communities/' . $communitieId;

		$communitieHTML = (string)$this->client->request('GET' , $url)->getBody();

		preg_match_all('/data-categoryid\=\"([a-zA-Z0-9]{8}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{4}\-[a-zA-Z0-9]{12})\".*\>(.+)\<\/div\>/Ui' , $communitieHTML , $categs);

		$categsArr = [];

		foreach($categs[1] AS $arrKey => $cId)
		{
			if( !isset($categs[2][$arrKey]) )
				continue;

			$categsArr[] = [
				'id'        =>  $cId ,
				'name'      =>  $categs[2][$arrKey]
			];
		}

		return $categsArr;
	}

	public function getCookie( )
	{
		return $cookieJar = $this->client->getConfig('cookies')->toArray();
	}


	private function loadUser()
	{
		if( !is_null( $this->email ) )
		{
			$checkIfexist = wpFetch('account_sessions' , ['driver' => 'google' , 'username' => $this->email]);

			$this->cookies	= !empty( $checkIfexist['cookies'] ) ? json_decode($checkIfexist['cookies'] , true) : [];
			$this->inputs	= !empty( $checkIfexist['settings'] ) ? json_decode($checkIfexist['settings'] , true) : null;
		}
	}

	private function getAT()
	{
		if( is_null( $this->at ) )
		{
			$plusMainPage = (string)$this->client->request('GET' , 'https://plus.google.com/')->getBody();

			preg_match('/\"SNlM0e\":\"([^\"]+)/' , $plusMainPage , $at);
			$this->at = $at[1];
		}

		return $this->at;
	}

	private function getURLImg( $url )
	{
		$data = [
			[
				[
					92371866,
					[
						[
							"92371866" =>[ $url ,[[73046798,73047122],[],[]],1,1]
						]
					],
					null,
					null,
					0
				]
			]
		];

		$result = (string)$this->client->request('POST' , 'https://plus.google.com/_/PlusAppUi/data' , [
			'form_params'	=> [ 'f.req' => json_encode( $data ), 'at'    => $this->getAT() ] ,
			'headers'		=> ['Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8']
		] )->getBody();

		$result = str_replace(")]}'" , '' , $result);

		$result = json_decode($result , true);

		return isset($result[0][2]['92371866'][1][0][0]) ? $result[0][2]['92371866'][1][0][0] : null;
	}

	private function getInputs($fromData = null)
	{
		if( !is_null($fromData) )
		{
			$inputs = $this->parseInputs( $fromData , 'gaia_loginform' );

			$inputs['Email']    = $this->email;
			$inputs['Passwd']   = $this->password;
		}
		else if( is_array($this->inputs) && !is_null( $this->_capcha ) )
		{
			$inputs = $this->inputs;
			$inputs['logincaptcha'] = $this->_capcha;

			$this->inputs = null;
		}
		else
		{
			$loginPage = (string)$this->client->request('GET', 'https://accounts.google.com/ServiceLogin?hl=en')->getBody();

			if( strpos($loginPage , 'href="https://accounts.google.com/Logout"') !== false )
			{
				return true;
			}

			$inputs = $this->parseInputs($loginPage , 'gaia_loginform');

			$inputs['Email']    = $this->email;
			$inputs['Passwd']   = $this->password;
		}

		return $inputs;
	}

	private function parseInputs( $HTMLdata , $formName , $attrName = 'id' )
	{
		preg_match('/(<form.*?'.$attrName.'=.?'.$formName.'.*?<\/form>)/is', $HTMLdata, $matches);
		$form = isset($matches[1]) ? $matches[1] : '';

		preg_match_all('/(<input[^>]+>)/is', $form, $matches);

		$inputs = [];

		foreach( $matches[1] AS $input )
		{
			preg_match( '/name=(?:["\'])?([^"\'\s]*)/i' , $input ,$name );
			preg_match( '/value=(?:["\'])?([^"\'\s]*)/i' , $input ,$value );

			if( !empty( $name[1] ) )
			{
				$inputs[ $name[1] ] = isset($value[1]) ? $value[1] : '';
			}
		}

		return $inputs;
	}

	private function fintVisibleInputType( $HTMLdata , $formName , $attrName = 'id' )
	{
		preg_match('/(<form.*?'.$attrName.'=.?'.$formName.'.*?<\/form>)/is', $HTMLdata, $matches);
		$form = isset($matches[1]) ? $matches[1] : '';

		preg_match_all('/(<input[^>]+>)/is', $form, $matches);

		foreach( $matches[1] AS $input )
		{
			preg_match( '/name=(?:["\'])?([^"\'\s]*)/i' , $input ,$name );
			preg_match( '/type=(?:["\'])?([^"\'\s]*)/i' , $input ,$type );

			if( !empty( $name[1] ) && !empty( $type[1] ) && !in_array( strtolower(trim($type[1])) , ['submit' , 'hidden'] ) )
			{
				return true;
			}
		}

		return false;
	}

}
