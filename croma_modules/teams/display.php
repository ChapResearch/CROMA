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
    array("id" => "teamForm", "title" => "CROMA - Add/Edit Team", "form" => "teamForm")
);

$otherBlocks = array(array("id" => "viewTeam", "title" => "CROMA - View Team", "content" => "viewTeam"));

global $teamsBlockInfo;
global $teamsBlockViewFns;

blockLoadForms($teamsBlockInfo,$teamsBlockViewFns,$formBlocks);  
blockLoadOther($teamsBlockInfo,$teamsBlockViewFns,$otherBlocks);  

