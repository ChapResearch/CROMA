<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of user data
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/users/viewUsers.php");
include_once(MODULES_FOLDER."/users/profileForm.php");
include_once(MODULES_FOLDER."/users/usersSearch.php");
include_once(MODULES_FOLDER."/users/userStats.php");
include_once(MODULES_FOLDER."/users/deleteUser.php");

$formBlocks = array(
		    array("id" => "profileForm", "title" => "CROMA - Create/Edit Profile", "form" => "profileForm"),
		    array("id" => "myDashboardHeader", "title" => "CROMA - My Dashboard Header", "form" => "myDashboardHeader"),
		    array("id" => "userStats", "title" => "CROMA - User Stats", "form" => "viewUserStats"),
		    array("id" => "usersSearch", "title" => "CROMA - Search For Users", "form" => "usersSearch"),
		    array("id" => "deleteUserPage", "title" => "CROMA - Delete User", "form" => "deleteUserPage"),
		    );

$otherBlocks = array(array("id" => "viewUser", "title" => "CROMA - View User", "content" => "viewUser"),
		     array("id" => "viewUserProfileSummary", "title" => "CROMA - User Mini Profile", "content" => "viewUserProfileSummary"),
		     array("id" => "usersSearchHeader", "title" => "CROMA - Users Search Header", "content" => "usersSearchHeader"));


global $usersBlockInfo;
global $usersBlockViewFns;

blockLoadForms($usersBlockInfo,$usersBlockViewFns,$formBlocks);  
blockLoadOther($usersBlockInfo,$usersBlockViewFns,$otherBlocks);  
