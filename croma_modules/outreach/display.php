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
include_once("/var/www-croma/croma_modules/outreach/williamDisplay.php");
include_once("/var/www-croma/croma_modules/outreach/rachelDisplay.php");
include_once("/var/www-croma/croma_modules/outreach/parkerDisplay.php");

$formBlocks = array(
    array("id" => "outreach_input", "title" => "CROMA - Outreach Form", "form" => "outreachForm")
);

$otherBlocks = array(array("id" => "outreach_view", "title" => "CROMA - View Outreach", "content" => "viewUpcomingOutreach"),
		     array("id" => "viewTeamOutreach", "title" => "CROMA - View Team Outreach", "content" => "viewTeamOutreach"),
		     array("id" => "ideas_view", "title" => "CROMA - View Outreach Ideas", "content" => "viewOutreachIdeas")); 

global $outreachBlockInfo;
global $outreachBlockViewFns;

blockLoadForms($outreachBlockInfo,$outreachBlockViewFns,$formBlocks);  
blockLoadOther($outreachBlockInfo,$outreachBlockViewFns,$otherBlocks);  

