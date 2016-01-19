<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of notifications.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/croma_modules/media/williamDisplay.php");
include_once("/var/www-croma/croma_modules/media/rachelDisplay.php");
//include_once("/var/www-croma/database/croma_dbFunctions.php");

$otherBlocks = array(array("id" => "incoming_media_view", "title" => "CROMA - Incoming Media", "content" => "viewIncomingMedia"),
		     array("id" => "viewMedia", "title" => "CROMA - View Media", "content" => "viewMedia"),
		     array("id" => "mediaImportPath", "title" => "CROMA - Import Media", "content" => "getIncomingMedia")
		     ); 

$formBlocks = array(array("id" => "uploadMedia", "title" => "CROMA - Upload Media", "form" => "uploadMedia"),
		    array("id" => "assignMedia", "title" => "CROMA - Assign Media", "form" => "assignMedia")
		    );


global $mediaBlockInfo;
global $mediaBlockViewFns;

blockLoadForms($mediaBlockInfo, $mediaBlockViewFns, $formBlocks);
blockLoadOther($mediaBlockInfo,$mediaBlockViewFns,$otherBlocks);  

?>