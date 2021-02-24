<?php


class mainScraping
{
    /**
     * Base scraper class.
     *
     * 
     */	

	protected $idPerPage = 10;
	protected $threads = 10;
	protected $itemsPerPage = 50;
	protected $minNightlyPrice = 0;
	protected $maxNightlyPrice = 600;
	protected $incrementNightlyPrice = 100;
	protected $isUseProxy = false;
	protected $queryLocation = 'tempe-arizona-united-states-of-america';
	protected $page = 1;
	protected $url = '';
	protected $urlPost = 'https://www.booking_site.com/serp/g';
	protected $curlReferer = 'https://www.booking_site.com';
	protected $arrCurlProxy = [];
	protected $arrConfig = [];
	protected $db;
	
	public $arrLogs = [];	
	
    public function __construct()
    {
		
		
		$this->arrConfig             = require_once(dirname(__FILE__) . DS . 'config.php');
		$this->arrCurlProxy          = require_once(dirname(__FILE__) . DS . 'proxy.php');
		$this->idPerPage             = $this->arrConfig['idPerPage'];
		$this->threads               = $this->arrConfig['threads'];
		$this->itemsPerPage          = $this->arrConfig['itemsPerPage'];
		$this->minNightlyPrice       = $this->arrConfig['minNightlyPrice'];
		$this->maxNightlyPrice       = $this->arrConfig['maxNightlyPrice'];
		$this->incrementNightlyPrice = $this->arrConfig['incrementNightlyPrice'];
		$this->isUseProxy            = $this->arrConfig['isUseProxy'];
	
	}
	
    public function connectDB()
    {	
	
		$this->db = new PDO(
			'mysql:host=' . $this->arrConfig['dbHost'] . ';' . 
			'dbname=' . $this->arrConfig['dbName'], 
			$this->arrConfig['dbUser'], 
			$this->arrConfig['dbPassword'], 
			$this->arrConfig['dbOption']
		);
				
	
	}		
	
    public function getListingPage($arrId2ParsAll = [])
    {	
		
		if(is_array($arrId2ParsAll) && count($arrId2ParsAll) > 0){
			$i = 0;  
			for($i=0; $i < count($arrId2ParsAll);$i += $this->idPerPage){  
				$arrId2ParsSliced[] = array_slice($arrId2ParsAll, $i, $this->idPerPage);  
			}		

			foreach($arrId2ParsSliced as $key => $arrId2Pars) {

				$arrDataParsed = $this->sendCurlGetMulti($arrId2Pars);

				foreach($arrDataParsed as $id2Pars => $arrData) {
					if(
					
						$arrData && 
						is_array($arrData) && 
						count($arrData) > 0 &&
						array_key_exists('arrData', $arrData) &&
						array_key_exists('listingReducer', $arrData['arrData'])
					){
						
						$this->saveListingPageData($arrData['arrData']);
						
						$this->arrLogs[$this->queryLocation]['totalCountPropertyReal']++;
			
						
					}				
					else{
						$this->saveLogs([
							'type' => 'errors',
							'data' => [
								'Error listing page scraping: No data',
								'Locations ' . $this->queryLocation,
								'page #' . $this->page,
							]
						]);							
						
					}
				}
			}
		}
		return true;
	}	
	

    public function proceedAllData()
    {	
		$arrLocation = $this->getLocations();
	
		
		if(is_array($arrLocation) && count($arrLocation) > 0){
			foreach($arrLocation as $_Location){
				
				$arrId2Pars = [];				
				
				$this->queryLocation = $_Location['slug'];
				
				$this->arrLogs[$this->queryLocation] = 
				[
					'totalCountProperty'     => 0,
					'totalCountPropertyReal' => 0,
					'insertProperty'         => 0,
					'updateProperty'         => 0,
				];					
				
				$this->saveLogs([
					'type' => 'logs',
					'data' => [
						'Start scraping Locations ' . $this->queryLocation,
					]
				]);					

				
				$arrId2Pars = $this->sendCurlPostMulti();
				

		
	
				$this->saveLogs([
					'type' => 'logs',
					'data' => [
						'Finish scraping Locations pages, for locations: ' . $this->queryLocation,
					]
				]);		
		
				if(is_array($arrId2Pars) && count($arrId2Pars) > 0){
					
					$this->arrLogs[$this->queryLocation]['totalCountProperty'] = count($arrId2Pars);
					
					$this->saveLogs([
						'type' => 'logs',
						'data' => [
							'Start scraping Listings. Location: ' . $this->queryLocation,
							'Location Total Listings Count: ' . count($arrId2Pars),
						]
					]);						
					
					
					$this->getListingPage($arrId2Pars);
					
				}
			}
				
			
		}
		
		$output = $this->getImplodeArray($this->arrLogs);
		
		$this->saveLogs([
			'type' => 'logs',
			'data' => [
				'Summary statistics  ' . $output,
			]
		]);


		return true;
	}

	public function getLocations()
	{
		$arrLocation = [];
		
		$this->connectDB();
		
		$req = $this->db->prepare('SELECT `pl`.`slug` FROM `property_locations` as `pl` WHERE `pl`.`active` = 1'); 		
		
		try {
			
			$req->execute();
			$arrLocation = $req->fetchAll(PDO::FETCH_ASSOC);
			
		} catch (PDOException $e) {

			$this->saveLogs([
				'type' => 'errors',
				'data' => [
					'Error Get List from  to `property_locations` table.',
					'Errors: ' . $this->getImplodeArray($reqListing->errorInfo()),
				]
			]);	

		}		
		
		return $arrLocation;		
		
	}

    public function saveListingPageData($arrData = [])
    {

			$propertyId = 0;

			$this->connectDB();
					
			$MYSQL_DUPLICATE_CODES = [1062, 23000];
		
			$reqListing = $this->db->prepare('
				INSERT INTO `property` (
			`property_code`, `last_updated`,  `property_name`, `property_images`, `property_bedrooms`, `property_bathrooms`, `property_reviews`, `property_amenities`, 
			`external_url`, `thumbnail_url`, `home_type`, `nightly_average`, `bedrooms_number`, `bathrooms_number`, `half_bathrooms_number`, `max_occupants`, `max_adults`,
			`latitude`, `longitude`, `city`, `state`, `country`, `zip_code`, `headline`, `description`, `square_feet`, `cancellation_policy`, 
			`reviews_average`, `reviews_number`, `owner_name`, `owner_description`, `owner_is_premier_partner`) 
			VALUE (

					:property_code, NOW(), :property_name, :property_images, :property_bedrooms, :property_bathrooms, :property_reviews, :property_amenities, 
					:external_url, :thumbnail_url, :home_type, :nightly_average, :bedrooms_number, :bathrooms_number, :half_bathrooms_number, :max_occupants, :max_adults,
					:latitude, :longitude, :city, :state, :country, :zip_code, :headline, :description, :square_feet, :cancellation_policy, 
					:reviews_average, :reviews_number, :owner_name, :owner_description, :owner_is_premier_partner					
			)
			'); 

			$dataForSaveListing = [
				'property_code'            => trim($arrData['listingReducer']['unitNumber']),	
				'property_name'            => trim($arrData['listingReducer']['headline']),
				'property_images'          => json_encode($arrData['listingReducer']['images']),
				'property_bedrooms'        => json_encode($arrData['listingReducer']['spaces']['bedrooms']),
				'property_bathrooms'       => json_encode($arrData['listingReducer']['spaces']['bathrooms']),
				'property_reviews'         => json_encode($arrData['reviewsReducer']['reviews']),
				'property_amenities'       => json_encode($arrData['listingReducer']['allFeaturedAmenitiesRanked']),
				'external_url'             => !empty($arrData['listingReducer']['detailPageUrl']) ? trim($arrData['listingReducer']['detailPageUrl']) : '',
				'thumbnail_url'            => !empty($arrData['listingReducer']['carousel']['thumbnailUrl']) ? $arrData['listingReducer']['carousel']['thumbnailUrl'] : '',
				'home_type'                => $arrData['listingReducer']['propertyType'],
				'nightly_average'          => $arrData['nightly_average'],
				'bedrooms_number'          => $arrData['listingReducer']['bedrooms'],
				'bathrooms_number'         => $arrData['listingReducer']['bathrooms']['full'] ,
				'half_bathrooms_number'    => $arrData['listingReducer']['bathrooms']['half'],
				'max_occupants'            => !empty($arrData['listingReducer']['houseRules']['maxOccupancy']['guests']) ? $arrData['listingReducer']['houseRules']['maxOccupancy']['guests'] : 0 ,
				'max_adults'               => !empty($arrData['listingReducer']['houseRules']['maxOccupancy']['adults']) ? $arrData['listingReducer']['houseRules']['maxOccupancy']['adults'] : 0 ,
				'latitude'                 => $arrData['listingReducer']['geoCode']['latitude'],
				'longitude'                => $arrData['listingReducer']['geoCode']['longitude'],
				'city'                     => $arrData['listingReducer']['address']['city'],
				'state'                    => $arrData['listingReducer']['address']['stateProvince'],
				'country'                  => $arrData['listingReducer']['address']['country'],
				'zip_code'                 => $arrData['listingReducer']['address']['postalCode'],
				'headline'                 => $arrData['listingReducer']['headline'],
				'description'              => $arrData['listingReducer']['description'],
				'square_feet'              => !empty($arrData['listingReducer']['spaces']['spacesSummary']['area']['areaValue']) ? $arrData['listingReducer']['spaces']['spacesSummary']['area']['areaValue'] : 0,
				'cancellation_policy'      => json_encode($arrData['listingReducer']['cancellationPolicy']),
				'reviews_average'          => $arrData['reviewsReducer']['averageRating'],
				'reviews_number'           => $arrData['reviewsReducer']['reviewCount'],
				'owner_name'               => $arrData['listingReducer']['contact']['name'],
				'owner_description'        => $arrData['listingReducer']['ownersListingProfile']['aboutYou'],
				'owner_is_premier_partner' => $arrData['owner_is_premier_partner'],
				
			];		
	
			$reqListing = $this->bindArrayValue($reqListing, $dataForSaveListing);
			
			try {
				
				$rezultInsert = $reqListing->execute(array_values($dataForSaveListing));
				$propertyId = $this->db->lastInsertId();
				
				$this->arrLogs[$this->queryLocation]['insertProperty']++;
				
			} catch (PDOException $e) {
			   if (in_array($e->getCode(), $MYSQL_DUPLICATE_CODES)) {
			
					$reqListing = $this->db->prepare("
					UPDATE `property` SET  `last_updated` = NOW(), `property_name` = :property_name, `property_images` = :property_images, `property_bedrooms` = :property_bedrooms,
						`property_bathrooms` = :property_bathrooms, `property_reviews` = :property_reviews, `property_amenities` = :property_amenities, 
						`external_url` = :external_url, `thumbnail_url` = :thumbnail_url, `home_type` = :home_type, `nightly_average` = :nightly_average, 
						`bedrooms_number` = :bedrooms_number, `bathrooms_number` = :bathrooms_number, `half_bathrooms_number` = :half_bathrooms_number, 
						`max_occupants` = :max_occupants, `max_adults` = :max_adults, `latitude` = :latitude, `longitude` = :longitude,
						`city` = :city, `state` = :state, `country` = :country, `zip_code` = :zip_code, 
						`headline` = :headline, `description` = :description, `square_feet` = :square_feet, 
						`cancellation_policy` = :cancellation_policy, `reviews_average` = :reviews_average, `reviews_number` = :reviews_number, 
						`owner_name` = :owner_name,  `owner_description` = :owner_description, `owner_is_premier_partner` = :owner_is_premier_partner
						WHERE `property_code` = :property_code
					"); 
				 
					$reqListing = $this->bindArrayValue($reqListing, $dataForSaveListing);
					$rezultUpdate = $reqListing->execute($dataForSaveListing);
					
					$reqGetListing = $this->db->prepare(
						"SELECT `id` FROM `property` WHERE `property_code` = :property_code"
					); 

					$reqGetListing = $this->bindArrayValue($reqGetListing, ['property_code' => $dataForSaveListing['property_code']]);
						
					$rezultSelect = $reqGetListing->execute(['property_code' => $dataForSaveListing['property_code']]);	
					$arrId = $reqGetListing->fetch(PDO::FETCH_ASSOC);
					$propertyId = $arrId['id'];
					
					$this->arrLogs[$this->queryLocation]['updateProperty']++;
					
			   } else {
					$this->saveLogs([
						'type' => 'errors',
						'data' => [
							'Error Insert to `property` table. Location: ' . $this->queryLocation,
							'Error property_code: ' . trim($arrData['listingReducer']['unitNumber']),	
							'Error property_url: ' . $arrData['url2Pars'],
							'Errors: ' . $this->getImplodeArray($reqListing->errorInfo()),
						]
					]);						


			   }
			}

			if($propertyId > 0){

				$req = $this->db->prepare('
					INSERT INTO `property_rate_history` (`property_id`, `entry_date`, `property_rates`) 
				VALUE (:property_id, NOW(), :property_rates)
				'); 		

				$dataForSave = [
					'property_id'            => $propertyId,	
					'property_rates'         => json_encode($arrData['listingReducer']['rateSummary'])				
				];		
				
				$req = $this->bindArrayValue($req, $dataForSave);
				
				try {
					
					$rezult2 = $req->execute(array_values($dataForSave));
					
				} catch (PDOException $e) {
				   if (in_array($e->getCode(), $MYSQL_DUPLICATE_CODES)) {

					$this->saveLogs([
						'type' => 'errors',
						'data' => [
							'Error Insert DUPLICATE property_rate_history Data. Location: ' . $this->queryLocation,
							'Error property_code: ' . $arrData['listingReducer']['unitNumber'],
							'Error property_url: ' . $arrData['url2Pars'],
							'Errors: ' . $this->getImplodeArray($req->errorInfo()),
						]
					]);	

				   } else {
				  
					$this->saveLogs([
						'type' => 'errors',
						'data' => [
							'Error Insert property_rate_history Data. Location: ' . $this->queryLocation,
							'Error property_code: ' . $arrData['listingReducer']['unitNumber'],
							'Error property_url: ' . $arrData['url2Pars'],							
							'Errors: ' . $this->getImplodeArray($req->errorInfo()),
						]
					]);						  
					  
				   }
				}
				
				$req = $this->db->prepare('
					INSERT INTO `property_availability_history` (`property_id`, `entry_date`, `start_date`, `end_date`, `availability`) 
				VALUE (:property_id, NOW(), :start_date, :end_date, :availability)
				'); 		

				$dataForSave = [
					'property_id'         => $propertyId,	
					'start_date'          => $arrData['listingReducer']['availabilityCalendar']['availability']['dateRange']['beginDate'],	
					'end_date'            => $arrData['listingReducer']['availabilityCalendar']['availability']['dateRange']['endDate'],	
					'availability'        => $arrData['listingReducer']['availabilityCalendar']['availability']['unitAvailabilityConfiguration']['availability']				
				];		
				
				$req = $this->bindArrayValue($req, $dataForSave);
				
				try {
					
					$rezult3 = $req->execute(array_values($dataForSave));
					
				} catch (PDOException $e) {
				   if (in_array($e->getCode(), $MYSQL_DUPLICATE_CODES)) {

					$this->saveLogs([
						'type' => 'errors',
						'data' => [
							'Error Insert DUPLICATE property_availability_history Data. Location: ' . $this->queryLocation,
							'Error property_code: ' . $arrData['listingReducer']['unitNumber'],
							'Error property_url: ' . $arrData['url2Pars'],							
							'Errors: ' . $this->getImplodeArray($req->errorInfo()),
						]
					]);	
					
				   } else {

					$this->saveLogs([
						'type' => 'errors',
						'data' => [
							'Error Insert property_availability_history Data. Location: ' . $this->queryLocation,
							'Error property_code: ' . $arrData['listingReducer']['unitNumber'],
							'Error property_url: ' . $arrData['url2Pars'],
							'Errors: ' . $this->getImplodeArray($req->errorInfo()),
						]
					]);							  
					  
					  
				   }
				}
			}

	}	
	
    public function savePageDataId($arrData = [], $arrId2Pars = [])
    {
		foreach($arrData as $key => $_Data ){
			$arrId2Pars[$_Data['propertyId']] = $_Data['propertyId'];
		}
		
		return $arrId2Pars;
	}	

	
	public function bindArrayValue($req, $array, $typeArray = false, $key2 = false, $keyField = '')
	{
		if(is_object($req) && ($req instanceof PDOStatement))
		{
			foreach($array as $key => $value)
			{
				if($typeArray)
					$req->bindValue(":$key",$value,$typeArray[$key]);
				else
				{
					if(is_int($value))
						$param = PDO::PARAM_INT;
					elseif(is_bool($value))
						$param = PDO::PARAM_BOOL;
					elseif(is_null($value))
						$param = PDO::PARAM_NULL;
					elseif(is_string($value))
						$param = PDO::PARAM_STR;
					else
	//                  $param = FALSE;
						$param = PDO::PARAM_STR;
					   
					if($param)
						$req->bindValue(':' . $key, $value, $param);
					   
					if($key2 && $keyField != $key)
						$req->bindValue(':' . $key . '2', $value, $param);						
				}
			}
		}
		
		return $req;
	}
		
		
	public function parsGetResponse($arrData = ''){
		$arrParsed = [];
		$ArM = [];
		if(	!empty($arrData)){
			
			$tmp_ArM = [];
			
			$arrData = preg_replace("/\t/i","",$arrData);	  
			$arrData = preg_replace("/\r/i","",$arrData);	  
			$arrData = preg_replace("/\n/i","",$arrData);	 

			preg_match("/\<script\>\s\s\s\s\s\s\s\swindow\.\_\_INITIAL\_STATE\_\_\s\=\s(.*)\;\s\s\s\s\s\s\s\swindow\.\_\_SITE_CONTEXT\_\_\s\=\s/", $arrData, $tmp_ArM);		
			
			if(is_array($tmp_ArM) && count($tmp_ArM) > 1 && !empty($tmp_ArM[1])){
				$ArM = json_decode($tmp_ArM[1], true);	
				if(array_key_exists('listingReducer', $ArM) && array_key_exists('reviewsReducer', $ArM)){
					$arrParsed['listingReducer']             = $ArM['listingReducer'];	
					$arrParsed['reviewsReducer']             = $ArM['reviewsReducer'];	
					$arrParsed['owner_is_premier_partner']   = 0;
					if(
						array_key_exists('badges', $ArM['listingReducer']) &&  
						array_key_exists('rankedBadges', $ArM['listingReducer']['badges'])
					){
						foreach($ArM['listingReducer']['badges']['rankedBadges'] as $rankedBadges){
							if($rankedBadges['name'] == 'Premier Host') $arrParsed['owner_is_premier_partner'] = 1;
						}
						
					}
				}
			}
			
			$arrParsed['nightly_average'] = 0;
			
			$ArM = [];
			preg_match("/window\.analyticsdatalayer\s=\s(.*)\;\s\s\s\s\s\s\s\swindow\.analyticsdatalayer\.proctor/", $arrData, $tmp_ArM);
			
			if(is_array($tmp_ArM) && count($tmp_ArM) > 1 && !empty($tmp_ArM[1])){
				$ArM = json_decode($tmp_ArM[1], true);	
				if(array_key_exists('displayprice', $ArM)){
					$arrParsed['nightly_average'] = $ArM['displayprice'];	
				}
			}

			
		}

		return $arrParsed;
	}

			
	
    public function parsPostResponse($arrData = [])
    {	
		

		
		if(
			is_array($arrData) &&
			count($arrData) > 0 &&
			!array_key_exists('errors',  $arrData) &&
			 isset($arrData['data']) &&
			 array_key_exists('results', $arrData['data'])
		){
			$arrId2Pars = $this->savePageDataId($arrData['data']['results']['listings']);
			return $arrId2Pars;
			
		}
		else{
			
			return false;
			
			
		}
	
		
	}
		
	
	public function sendCurlGetMulti($arrId2Pars = [])
	{
	
		$arrRez = [];
	
		$mh = curl_multi_init(); 
		unset($ch);  
		foreach ($arrId2Pars as $i => $id2Pars){
					
			$useragent = $this->arrConfig['useragent'][array_rand($this->arrConfig['useragent'])];  
			$proxy = $this->arrCurlProxy[array_rand($this->arrCurlProxy)];
			$url = 'https://www.booking_site.com/' . $id2Pars . '?noDates=true';

			$ch[$i] = curl_init();
			curl_setopt($ch[$i], CURLOPT_URL, $url); 
			curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);   
			curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, false);						
			curl_setopt($ch[$i], CURLOPT_REFERER, $this->curlReferer); 
			curl_setopt($ch[$i], CURLOPT_HEADER, 0);           
			curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 0);  
			curl_setopt($ch[$i], CURLOPT_ENCODING, "");       
			curl_setopt($ch[$i], CURLOPT_USERAGENT, $useragent);  
			curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, $this->arrConfig['curlConnectionTimeOut']);
			curl_setopt($ch[$i], CURLOPT_TIMEOUT, $this->arrConfig['curlTimeOut']);
			if($this->isUseProxy)	curl_setopt($ch[$i], CURLOPT_PROXY, $proxy);
			curl_setopt($ch[$i], CURLOPT_MAXREDIRS, 2);				
			curl_multi_add_handle ($mh, $ch[$i]);  
		}
		
		do { 
			$n = curl_multi_exec($mh, $active); usleep(100); 
		} 
		while ($active);   
		
		foreach ($arrId2Pars as $i => $id2Pars){  
			$url = 'https://www.booking_site.com/' . $id2Pars . '?noDates=true';
			$content = '';
			$content = curl_multi_getcontent($ch[$i]);

			$header = curl_getinfo($ch[$i]);
			$error = curl_error($ch[$i]);

			$arrRez[$id2Pars]['header']  = $header;
			$arrRez[$id2Pars]['arrData'] = $this->parsGetResponse($content);
			$arrRez[$id2Pars]['arrData']['url2Pars'] = $url;
			curl_close($ch[$i]);  
		}  
	
		
		curl_multi_close($mh); 
		return $arrRez;
	}		
	
	public function sendCurlPostMulti()
	{
	
		$arrPages2Pars = [];
		$arrId2Pars    = [];


		for($page = 1; $page <= 20; $page++){
		
			for($j = $this->minNightlyPrice; $j < $this->maxNightlyPrice; $j += $this->incrementNightlyPrice){
				
				$arrPages2Pars[] = [
					'page' => $page,
					'minNightlyPrice' => $j,
					'maxNightlyPrice' => $this->maxNightlyPrice,
				];
				
			}					
		}
		

						
		for($k = 0; $k < count($arrPages2Pars);$k += $this->idPerPage){  
			$arrPages2ParsSliced[] = array_slice($arrPages2Pars, $k, $this->idPerPage);  
		}	

		foreach ($arrPages2ParsSliced as $j => $arrP2Pars){
		
			$mh = curl_multi_init(); 
			unset($ch);		
			
			
			foreach ($arrP2Pars as $i => $pages2Pars){
				
				$queryLocation   = $this->queryLocation; 
				$page            = $pages2Pars['page']; 
				$minNightlyPrice = $pages2Pars['minNightlyPrice']; 
				$maxNightlyPrice = $pages2Pars['maxNightlyPrice']; 
				
		
					
				$useragent = $this->arrConfig['useragent'][array_rand($this->arrConfig['useragent'])];  
				$proxy = $this->arrCurlProxy[array_rand($this->arrCurlProxy)];  
				
				$data = include(dirname(__FILE__) . DS . 'postFields.php');
				
				$payload = json_encode($data);



				$ch[$i] = curl_init();
				curl_setopt($ch[$i], CURLOPT_URL, $this->urlPost); 
				curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);   
				curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, false);						
				curl_setopt($ch[$i], CURLOPT_REFERER, $this->curlReferer); 
				curl_setopt($ch[$i], CURLOPT_HEADER, 0);           
				curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 0);  
				curl_setopt($ch[$i], CURLOPT_USERAGENT, $useragent);  
				curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, $this->arrConfig['curlConnectionTimeOut']);
				curl_setopt($ch[$i], CURLOPT_TIMEOUT, $this->arrConfig['curlTimeOut']);
				if($this->isUseProxy)	curl_setopt($ch[$i], CURLOPT_PROXY, $proxy);
				curl_setopt($ch[$i], CURLOPT_POST, 1);
				curl_setopt($ch[$i], CURLOPT_POSTFIELDS, $payload);
				curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
				curl_multi_add_handle ($mh, $ch[$i]);  
			}
			
			do { 
				$n = curl_multi_exec($mh, $active); usleep(100); 
			} 
			
			while ($active);   
			
			foreach ($arrP2Pars as $i => $pages2Pars){  

				$content = '';
				$content = curl_multi_getcontent($ch[$i]);
				curl_close($ch[$i]);  
			
				$arrData = $this->parsPostResponse(json_decode($content, true));
				
				if($arrData && is_array($arrData) && count($arrData) > 0) {
					foreach($arrData as $_Data){
						$arrId2Pars[$_Data] = $_Data;		
					}
				
				}
					
			}  
			
			curl_multi_close($mh); 
		}  
	

		return $arrId2Pars;
	}	
	
	
	
	public function saveLogs($arrParam = [])
	{
		$arrParam['data'] = array_merge(['time' => date("Y-m-d H:i:s")], $arrParam['data']);
		
		$output = $this->getImplodeArray($arrParam['data']);

		if($arrParam['type'] == 'errors'){
			file_put_contents(dirname(__FILE__) .DS. 'logs' . DS . "_errors_logs.txt", $output."\n", FILE_APPEND);
		}
		else{
			file_put_contents(dirname(__FILE__) .DS. 'logs' . DS . "_parsing_logs.txt", $output."\n", FILE_APPEND);
		}
	}	
	
	public function getImplodeArray($aRR = [])
	{
		
		$resStr = '';
		
		if (is_array($aRR) && (count($aRR) > 0)) {
			foreach ($aRR as $k1 => $v1) {
				if (is_array($v1) && (count($v1) > 0)) {
					$resStr .= ' | `' . $k1 . '` => [';
					foreach ($v1 as $k2 => $v2) {
						if (is_array($v2) && (count($v2) > 0)) {
							$resStr .= ' | `' . $k2 . '` => [';
							foreach ($v2 as $k3 => $v3) {
									$resStr .= '`' . $k3 . '` => ' . $v3 . ' | ' ;
							}
							$resStr .= '] ';
						}
						else {

							$resStr .= ' | `' . $k2 . '` => ' . $v2;
						}
					}
					$resStr .= '] ';
				}
				else {
					$resStr .= ' | `' . $k1 . '` => ' . $v1;
				}
			}
		}
		else{
			$resStr .= ' | ' . $aRR;
			
			
		}

		return $resStr;		
		
	}
	
}
