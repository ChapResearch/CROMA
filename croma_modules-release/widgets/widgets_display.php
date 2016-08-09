<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of widgets.
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/widgets/widgetGenerator.php");

$otherBlocks = array(array("id" => "widgetGenerator", "title" => "CROMA - Widget Generator", "content" => "widgetGenerator"));
		     

global $widgetsBlockInfo;
global $widgetsBlockViewFns;

blockLoadOther($widgetsBlockInfo,$widgetsBlockViewFns,$otherBlocks);  

