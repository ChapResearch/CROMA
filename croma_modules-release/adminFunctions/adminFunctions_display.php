<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/adminFunctions/adminPage.php");

$otherBlocks = array(
		     array("id" => "adminPage", "title" => " CROMA - Admin Page", "content" => "adminPage"),
		     );

global $adminFunctionsBlockInfo;
global $adminFunctionsBlockViewFns;

blockLoadOther($adminFunctionsBlockInfo,$adminFunctionsBlockViewFns,$otherBlocks);  

?>