<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/outreach/outreachForm.php");
include_once(MODULES_FOLDER."/outreach/viewOutreach.php");
include_once(MODULES_FOLDER."/outreach/signUpForm.php");
include_once(MODULES_FOLDER."/outreach/searchForm.php");
include_once(MODULES_FOLDER."/outreach/writeUpForm.php");
include_once(MODULES_FOLDER."/outreach/viewUserOutreach.php");

$formBlocks = array(
		    array("id" => "outreach_input", "title" => "CROMA - Outreach Form", "form" => "outreachForm"),
		    array("id" => "advancedViewOutreach", "title" => " CROMA - Advanced View Outreach", "form" => "advancedViewOutreach"),
   		    array("id" => "searchFormFull", "title" => " CROMA - Search Outreach - Full", "form" => "searchForm"),
		    array("id" => "searchFormSidebar", "title" => " CROMA - Search Outreach - Sidebar", "form" => "searchFormSidebar"),
		    array("id" => "signUp", "title" => "CROMA - Sign Up For Outreach", "form" => "signUp"),
		    array("id" => "outreachPageHeader", "title" => "CROMA - Outreach Page Header ", "form" => "outreachPageHeader"),
		    array("id" => "writeUpForm", "title" => "CROMA - Write Up Form", "form" => "writeUpForm"),
		    		    );

$otherBlocks = array(array("id" => "outreach_view", "title" => "CROMA - View Upcoming Outreach", "content" => "viewUpcomingOutreach"),
		     array("id" => "ideas_view", "title" => "CROMA - View Outreach Ideas", "content" => "viewOutreachIdeas"),
		     array("id" => "viewOutreach", "title" => "CROMA - View Outreach Event", "content" => "viewOutreachEvent"),
		     array("id" => "viewPeopleForEvent", "title" => "CROMA - View List of People Signed Up For An Event", "content" => "viewPeopleForEvent"),
		     array("id" => "viewCancelledOutreach", "title" => "CROMA - View Cancelled Outreach", "content" => "viewCancelledOutreach"),
		     array("id" => "viewUserUpcomingEvents", "title" => "CROMA - View Upcoming Outreach Events For User", "content" => "viewUserUpcomingEvents"),
		     array("id" => "ideasWaitingApproval", "title" => "CROMA - Ideas Waiting Approval", "content" => "ideasWaitingApproval"),
		     array("id" => "writeUpsWaitingApproval", "title" => "CROMA - Write-Ups Waiting Approval", "content" => "writeUpsWaitingApproval"),
		     array("id" => "hoursWaitingApproval", "title" => "CROMA - Hours Waiting Approval", "content" => "hoursWaitingApproval"),
		     array("id" => "viewUserOutreach", "title" => " CROMA - View User Outreach", "content" => "viewUserOutreach"));


global $outreachBlockInfo;
global $outreachBlockViewFns;

blockLoadForms($outreachBlockInfo,$outreachBlockViewFns,$formBlocks);  
blockLoadOther($outreachBlockInfo,$outreachBlockViewFns,$otherBlocks);  

