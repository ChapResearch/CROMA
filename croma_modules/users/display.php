<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");
include_once("/var/www-croma/croma_modules/users/philipDisplay.php");
include_once("/var/www-croma/croma_modules/users/shreyDisplay.php");
include_once("/var/www-croma/croma_modules/users/parkerDisplay.php");

$formBlocks = array(array("id" => "profileForm", "title" => "CROMA - Create/Edit Profile", "form" => "profileForm"));

$otherBlocks = array(array("id" => "viewUser", "title" => "CROMA - View User", "content" => "viewUser"),
		     array("id" => "viewUsersInTeam", "title" => "CROMA - View Users For Team", "content" => "showUsersForTeam"));

global $usersBlockInfo;
global $usersBlockViewFns;

blockLoadForms($usersBlockInfo,$usersBlockViewFns,$formBlocks);  
blockLoadOther($usersBlockInfo,$usersBlockViewFns,$otherBlocks);  
