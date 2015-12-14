<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");
include_once("/var/www-croma/croma_modules/outreach/philipDisplay.php");

$formBlocks = array(
    array("id" => "hours_input", "title" => "CROMA - Log Hours", "form" => "addHours")
);

//$otherBlocks = array(array("id" => "hourLogging_view", "title" => "CROMA - View HourLogging", "content" => "viewUpcomingHourLogging"),
//		array("id" => "ideas_view", "title" => "CROMA - View HourLogging Ideas", "content" => "viewHourLoggingIdeas")); 

global $hourLoggingBlockInfo;
global $hourLoggingBlockViewFns;

blockLoadForms($hourLoggingBlockInfo,$hourLoggingBlockViewFns,$formBlocks);  
//blockLoadOther($hourLoggingBlockInfo,$hourLoggingBlockViewFns,$otherBlocks);  

