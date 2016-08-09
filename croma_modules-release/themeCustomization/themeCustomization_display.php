<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/themeCustomization/publicHomePage.php");
include_once(MODULES_FOLDER."/themeCustomization/help.php");

$otherBlocks = array(
		     array("id" => "publicHomePageStatistics", "title" => " CROMA - Public Home Page Statistics", "content" => "publicHomePageStatistics"),
		     array("id" => "publicHomePageCRLogo", "title" => " CROMA - Public Home Page CR Logo", "content" => "publicHomePageCRLogo"),
		     array("id" => "publicHomePageFRCLogo", "title" => " CROMA - Public Home Page FRC Logo", "content" => "publicHomePageFRCLogo"),
		     array("id" => "publicHomePageCROMALogo", "title" => " CROMA - Public Home Page CROMA Logo", "content" => "publicHomePageCROMALogo"),
		     array("id" => "publicHomePageInfo", "title" => " CROMA - Public Home Page Info", "content" => "publicHomePageInfo"),
		     array("id" => "displayHelp", "title" => " CROMA - Display Help", "content" => "displayHelp")
		     );

global $themeCustomizationBlockInfo;
global $themeCustomizationBlockViewFns;

blockLoadOther($themeCustomizationBlockInfo,$themeCustomizationBlockViewFns,$otherBlocks);  

?>