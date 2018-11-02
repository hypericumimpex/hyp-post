<?php

class FSCurl
{

	public static function getURL( $url , $proxy = '' )
	{
		$headerss = array
		(
			'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'Accept-Language: en-US,fr;q=0.8;q=0.6,en;q=0.4,ar;q=0.2',
			'Accept-Charset: utf-8;q=0.7,*;q=0.7',
			'Accept-Encoding: gzip,deflate',
			'cookie:datr=; locale=en_US; sb=; pl=n; lu=gA; c_user=; xs=; act=; presence='
		);

		$ch = curl_init( $url );

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST , "GET");
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headerss);

		if( !empty( $proxy ) )
		{
			curl_setopt($ch, CURLOPT_PROXY, $proxy);
		}

		$result = curl_exec( $ch );

		$cError = curl_error( $ch );

		if( $cError )
		{
			return json_encode( [
				'error' => [
					'message' => htmlspecialchars( $cError )
				]
			] );
		}

		curl_close( $ch );

		return $result;
	}

    public static function getContents($url , $method = 'GET' ,  $data = [] , $headers = [] , $proxy = '')
	{
		$method = strtoupper($method);

		$c = curl_init();

		$user_agents = [
			"Mozilla/5.0 (Linux; Android 5.0.2; Andromax C46B2G Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/60.0.0.16.76;]",
			"[FBAN/FB4A;FBAV/35.0.0.48.273;FBDM/{density=1.33125,width=800,height=1205};FBLC/en_US;FBCR/;FBPN/com.facebook.katana;FBDV/Nexus 7;FBSV/4.1.1;FBBK/0;]",
			"Mozilla/5.0 (Linux; Android 5.1.1; SM-N9208 Build/LMY47X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.81 Mobile Safari/537.36",
			"Mozilla/5.0 (Linux; U; Android 4.4.2; en-us; Celkon A406 Build/MocorDroid2.3.5) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1"
		];

		$useragent = $user_agents[array_rand($user_agents)];

		if( $method == 'GET' && !empty($data) )
		{
			$url .= (strpos($url , '?') !== false ? '&' : '?') . http_build_query($data);
		}
		$opts = [
			CURLOPT_URL				=> $url ,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_SSL_VERIFYPEER	=> false,
			CURLOPT_USERAGENT		=> $useragent
		];

		if( !empty( $proxy ) )
		{
			$opts[CURLOPT_PROXY] = $proxy;
		}

		if($method == 'POST')
		{
			$opts[CURLOPT_POST]			= true;
			$opts[CURLOPT_POSTFIELDS]	= $data;//json)encode
		}
		else if( $method == 'DELETE' )
		{
			$opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			$opts[CURLOPT_POST]			= true;
			$opts[CURLOPT_POSTFIELDS]	= http_build_query($data);
		}

		if( is_array( $headers ) && !empty( $headers ) ) {

			$headers_arr = array();
			foreach( $headers as $k => $v )
			{
				$headers_arr[] = $k.': '.$v;
			}

			$opts[CURLOPT_HTTPHEADER] = $headers_arr;
		}

		curl_setopt_array($c, $opts);

		$result = curl_exec( $c );

		$cError = curl_error($c);

		if( $cError )
		{
			return json_encode( [
				'error' => [
					'message' => htmlspecialchars( $cError )
				]
			] );
		}

		curl_close( $c );

		return $result;
	}

}