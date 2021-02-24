<?php

return [
	
	'curlConnectionTimeOut' => 1200, // The number of seconds to wait cURL while trying to connect
	'curlTimeOut'           => 1200, // The maximum number of seconds to allow cURL functions to execute
	'idPerPage'             => 10,   // Number of pages for slice Array for scraping 
	'threads'               => 10,   // Count of threads for scraping 
	'itemsPerPage'          => 50,   // Number of items per listing list page, default 50

	'minNightlyPrice'       => 0,    // minNightlyPrice parameters for JSON array
	'maxNightlyPrice'       => 1000,  // maxNightlyPrice parameters for JSON array

	'incrementNightlyPrice' => 100,  // The step by which the minNightlyPrice increases 

	'isUseProxy'           => false,  // do you need to use proxy list (file proxy.php)

	'useragent' => [ 
		'Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
	],
	
	'dbName' => 'vrbo',
	'dbUser' => 'root',
	'dbPassword' => 'password',
	'dbHost' => 'localhost',


	'dbOption' => [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
			PDO::ATTR_TIMEOUT => 600, // in seconds
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
	],

];