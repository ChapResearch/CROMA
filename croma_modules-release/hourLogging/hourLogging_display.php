<?php

/* ---------------------------- display.php ---------------------------------

*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/hourLogging/oldHoursForm.php");
include_once(MODULES_FOLDER."/hourLogging/viewHours.php");
include_once(MODULES_FOLDER."/hourLogging/hourForm.php");

$formBlocks = array(array("id" => "hoursInput", "title" => "CROMA - Log Hours", "form" => "hoursForm"),
		    array("id" => "oldHours", "title" => "CROMA - Log Old Hours", "form" => "oldHoursForm"));
$otherBlocks = array(array("id" => "viewHours", "title" => "CROMA - View Hours", "content" => "viewHours"));

global $hourLoggingBlockInfo;
global $hourLoggingBlockViewFns;

blockLoadForms($hourLoggingBlockInfo,$hourLoggingBlockViewFns,$formBlocks);  
blockLoadOther($hourLoggingBlockInfo,$hourLoggingBlockViewFns,$otherBlocks);  

