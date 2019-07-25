<?php

require_once __DIR__ . '/../vendor/autoload.php';

class GoogleMyBusiness
{

	private $at;
	private $cookies;
	private $client;


	public function __construct( $sid, $hsid, $ssid , $proxy = '' )
	{
		$this->cookies = [
			["Name" => "SID", "Value" => $sid, "Domain" => ".google.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" =>	true],
			["Name" => "HSID", "Value" => $hsid, "Domain" => ".google.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" => false],
			["Name" => "SSID", "Value" => $ssid, "Domain" => ".google.com", "Path" => "/","Max-Age" => null,"Expires" => null,"Secure" => true,"Discard" => false,"HttpOnly" => false]
		];

		$cookieJar		= new \GuzzleHttp\Cookie\CookieJar(false , $this->cookies);

		$this->client	= new \GuzzleHttp\Client([
			'cookies' 			=>	$cookieJar,
			'allow_redirects'	=>	[ 'max' => 20 ],
			'proxy'				=>	empty($proxy) ? null : $proxy,
			'verify'			=>	false,
			'http_errors'		=>	false,
			'headers'			=>	['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0']
		]);
	}

	public function getUserInfo()
	{
		try
		{
			$myInfo = (string)$this->client->request('GET' , 'https://aboutme.google.com')->getBody();
		}
		catch( Exception $e )
		{
			$myInfo = '';
		}

		preg_match('/window\.IJ_values \= (\[.*?\])\;/mi' , $myInfo , $matches);
		if( isset($matches[1]) )
		{
			$jsonInf = json_decode(str_replace(['\x', "'" , ',]' ] , ['' , '"' , ']'] , $matches[1]) , true);

			preg_match( '/url\((https?\:\/\/.+googleusercontent\.com.+)\)/Ui' , $myInfo, $profilePhoto );

			$userInfo = [
				'id'			=>	isset($jsonInf[46]) ? $jsonInf[46] : null,
				'name'			=>	isset($jsonInf[34]) ? $jsonInf[34] : null,
				'email'			=>	isset($jsonInf[36]) ? $jsonInf[36] : null,
				'gender'		=>	isset($jsonInf[37]) && $jsonInf[37]=='male' ? 1 : 2,
				'profile_image'	=>	isset($profilePhoto[1]) ? $profilePhoto[1] : null
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

	public function sendPost( $postTo , $text , $link = null, $linkButton = 'LEARN_MORE', $imageURL = '', $productName = null, $productPrice = null, $productCurrency = null )
	{
		if( !$this->getAT() )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! Google MyBusiness session expired! Please remove and add your account again.'
			];
		}

		$postType = !is_null( $productName ) ? 1 : 4;

		$productArr = is_null( $productName ) ? null : [ $productName ,[ $productCurrency, $productPrice, null ],[ $productCurrency, $productPrice, null ]];

		$fReqParam = [
			[
				[
					"h6IfIc",
					json_encode(
						[
							$postTo,
							[
								null,
								$text,
								null,
								null,
								( !empty($link) && $linkButton != '-' ? [ null, $link, $linkButton, $linkButton ] : null ),
								[],
								null,
								null,
								null,
								null,
								null,
								null,
								null,
								$this->uploadPhoto( $postTo, $imageURL ),
								$postType,
								null,
								$productArr
							]
						]
					),
					null,
					"generic"
				]
			]
		];

		try
		{
			$post = (string)$this->client->request('POST' , 'https://business.google.com/_/GeoMerchantFrontendUi/data/batchexecute' , [
				'form_params'	=> [ 'f.req' => json_encode( $fReqParam ) , 'at' => $this->getAT() ]  ,
				'headers'		=> [ 'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8' ]
			])->getBody();
		}
		catch( Exception $e )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! ' . $e->getMessage()
			];
		}

		preg_match('/localPosts\/([0-9]+)/' , $post , $postId);

		if( !isset($postId[1]) )
		{
			return [
				'status'		=> 'error',
				'error_msg'		=> 'Error! Can\'t share the post!'
			];
		}

		return [
			'status'    => 'ok',
			//'post_url'  => $fullUrl,
			'id'   		=> $postId[1]
		];
	}

	public function uploadPhoto( $location, $imageUrl = null )
	{
		if( empty( $imageUrl ) )
		{
			return null;
		}

		$fReqParam = [
			[
				[
					"iWixD",
					json_encode(
						[
							$location,
							$imageUrl,
							[ null, $imageUrl, null, null, 1 ]
						]
					),
					null,
					"generic"
				]
			]
		];

		try
		{
			$uploadData = (string)$this->client->request('POST' , 'https://business.google.com/_/GeoMerchantFrontendUi/data/batchexecute' , [
				'form_params'	=> [ 'f.req' => json_encode( $fReqParam ) , 'at' => $this->getAT() ]  ,
				'headers'		=> [ 'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8' ]
			])->getBody();
		}
		catch( Exception $e )
		{
			$uploadData = '';
		}

		$uploadData = str_replace( [ ")]}'", "\n", '\n' ], '', $uploadData );
		$uploadData = json_decode( $uploadData );
		$uploadData = isset( $uploadData[0][2] ) ? $uploadData[0][2] : "[]";

		$uploadData = json_decode( $uploadData );

		$uploadData = isset( $uploadData[2] ) ? [ $uploadData[2] ] : null;

		return $uploadData;
	}

	public function getMyLocations( )
	{
		try
		{
			$locationsStr = (string)$this->client->request('GET', 'https://business.google.com/locations')->getBody();
		}
		catch( Exception $e )
		{
			$locationsStr = '';
		}

		preg_match_all('/\<a.+href\=[\'\"]\.?\/dashboard\/.\/([0-9]+)\/?[\"\']\>\<span.*\>(.+)\<\/span\>.+\<span.*\>(.+)\<\/span\>\<\/a\>/Ui' , $locationsStr , $locations);

		$locationsArr = [];

		foreach($locations[1] AS $arrKey => $cId)
		{
			if( !isset($locations[2][$arrKey]) || !isset($locations[3][$arrKey]) )
				continue;

			$title = strip_tags($locations[2][$arrKey]);
			$categ = strip_tags($locations[3][$arrKey]);

			$locationsArr[] = [
				'id'        =>  $cId ,
				'name'      =>  $title,
				'category'	=>	$categ
			];
		}

		return $locationsArr;
	}

	private function getAT()
	{
		if( is_null( $this->at ) )
		{
			try
			{
				$plusMainPage = (string)$this->client->request('GET' , 'https://business.google.com/locations')->getBody();
			}
			catch( Exception $e )
			{
				$plusMainPage = '';
			}

			preg_match('/\"SNlM0e\":\"([^\"]+)/' , $plusMainPage , $at);
			$this->at = isset( $at[1] ) ? $at[1] : null;
		}

		return $this->at;
	}

}
