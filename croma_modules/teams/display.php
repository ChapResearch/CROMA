<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");
include_once("/var/www-croma/croma_modules/teams/williamDisplay.php");

$formBlocks = array(
    array("id" => "addTeam", "title" => "CROMA - Add Team", "form" => "addTeam")
);

$otherBlocks = array(array("id" => "viewTeam", "title" => "CROMA - View Team", "content" => "viewTeam"),
		     array("id" => "viewOutreachForTeam", "title" => "CROMA - View Outreach For Team", "content" => "viewTeamOutreach"));

global $teamsBlockInfo;
global $teamsBlockViewFns;

blockLoadForms($teamsBlockInfo,$teamsBlockViewFns,$formBlocks);  
blockLoadOther($teamsBlockInfo,$teamsBlockViewFns,$otherBlocks);  

