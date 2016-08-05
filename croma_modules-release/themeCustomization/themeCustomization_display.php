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
		     array("id" => "publicHomePageLogos", "title" => " CROMA - Public Home Page Logos", "content" => "publicHomePageLogos"),
		     array("id" => "publicHomePageCROMALogoAndInfo", "title" => " CROMA - Public Home Page CROMA Logo and Info", "content" => "publicHomePageCROMALogoAndInfo"),
		     array("id" => "displayHelp", "title" => " CROMA - Display Help", "content" => "displayHelp")
		     );

global $themeCustomizationBlockInfo;
global $themeCustomizationBlockViewFns;

blockLoadOther($themeCustomizationBlockInfo,$themeCustomizationBlockViewFns,$otherBlocks);  

?>