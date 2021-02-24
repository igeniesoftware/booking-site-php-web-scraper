<?php

set_time_limit(1800);
ini_set('default_socket_timeout', 600);


define('DS', DIRECTORY_SEPARATOR);

require_once(dirname(__FILE__) . DS . 'mainScraping.php');

print_r("\n".date("Y-m-d H:i:s")." Proceed scraping was started!");

$mainScraping = new mainScraping();

$mainScraping->proceedAllData();

print_r("\n".date("Y-m-d H:i:s")." Proceed scraping was finished!");
