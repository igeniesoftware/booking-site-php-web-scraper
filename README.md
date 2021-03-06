#Booking Site PHP WebScraper (v 1.02)
=====================================

The script allows you to receive data from the listings of the booking_site and write them to the MySQL database. 
The parsing process takes place in two stages, the first is the collection of listings identifiers, the second is the receipt of the extended data of these listings. 
At the first stage, the script goes through the list of listings and collects their IDs (`$unitNumber`) into an array. The data is sent using the POST method to the “https://www.booking_site.com/serp/g” endpoint. To send data, a JSON array of variables stored in the *postFields.php* configuration file is used. To get more ID listings, the method of changing the `$minNightlyPrice` variable of the POST request configuration parameters is used. For each location, in a loop with `$incrementNightlyPrice` ($100 by default), the `$minNightlyPrice` variable is incremented and a POST is sent for all 20 pages in the location. All POST requests are sent by multi cURL. 
At the second stage, the array of ID listings is sent to the parser, which get the data with a GET request, to the endponit 'https://www.booking_site.com/$id2Pars?noDates=true', where `$id2Pars` is the listing ID. The entire ID array is slice into portions by `$idPerPage` variable, by default 10 pieces. Listing pages are scraped by multi cURL. The data obtained as a result of scraping is written to the database. New data is added, existing data are updated. Also, data is added to the MySQL tables `property_availability_history` and `property_rate_history`. The scraping process, general statistics for each locations, as well parsing and writing errors to the database is logged by saveLogs() method.

##Project files.
 ** start.php -  startup script
 ** config.php - array configuration settings 
 ** proxy.php  - proxy lists (array of "ip: port" strings)
 ** postFields.php - array of settings for POST request to get a list of pages
 ** mainScraping.php - main class with all functionality

##Class mainScraping. 
When the class is initialized, the configuration and proxy settings are received from config.php file and set up to the `$arrConfig`, `$arrCurlProxy` variables. The main class variables are also initialized, the values are taken from the appropriate configuration values.

##ConnectDB() method. 
Connects to MySQL database. 

##The proceedAllData() method 
Is the main control method, that performs the entire scraping process in two stages, getting an ID array (sendCurlPostMulti() method) and getting listing data (getListingPage() method). Each scraping process, obtaining an array of IDs, take place for each locations. Locations are getting using the getLocations() method.

##GetLocations() method. 
Getting active locations from the table `property_locations`, for which the parameter `active` equal to 1.

##SendCurlPostMulti() method. 
Method for getting an array of ID listings multi cURL. An array of 20 pages is prepared with changing the `$minNightlyPrice` variable of the POST request configuration parameters. Parameters are get from file postFields.php. Next, the array of settings is sliced into an array with the number of `$idPerPage` variables per page, 10 by default. The scraped data is parsed by the parsPostResponse() method and the savePageDataId() child method and returned IDs arrays to the parent proceedAllData() method.

##GetListingPage() method. 
The method scrapes the listings data over the entire ID array received in the sendCurlPostMulti() method. The ID array is sliced into an array with the size `$idPerPage` ID per page, 10 by default. Next, the sendCurlGetMulti() method receives data. The data is parsed by the parsGetResponse() method and checked for the presence of the main element with key "listingReducer" and, if it exists, is written to the database using the saveListingPageData() method.

##SaveListingPageData() method. 
The method prepares data for insertion into the `property` table, if the record already exists, it updates the data. After that, data are writes to the `property_availability_history` and `property_rate_history` tables.

##SavePageDataId() method. 
The method for adding a new value to the `$arrId2Pars` array ID listings `$arrData`. Called from the sendCurlPostMulti() method.

##BindArrayValue() method. 
Service method of binding parameters and values for writing to the database.

##The parsGetResponse() method. 
Method for parsing HTML data from listing pages.

##ParsPostResponse() method.
Service method of checking the received data for errors and parsing an array of listings. If there are no errors, it writes id listings using the savePageDataId() method.

##SendCurlGetMulti() method.
A method to fetch data for individual listing pages. The `$useragent` and `$proxy` variables are taken from the appropriated class variables, which are taken from the configuration files.

##SaveLogs() method.
The method writes logs. The getImplodeArray() method is used to format the input array variables `$arrParam` into a string.

##GetImplodeArray() method 
Service method for formatting an associative multidimensional array `$aRR` into a string for writing to a log. Called in the parent saveLogs() method.
