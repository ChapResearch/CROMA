<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of notifications.
*/   
include_once(MODULES_FOLDER."/blockSupport.php");
include_once(MODULES_FOLDER."/media/viewMedia.php");
include_once(MODULES_FOLDER."/media/mediaForms.php");

$otherBlocks = array(array("id" => "incoming_media_view", "title" => "CROMA - Incoming Media", "content" => "viewIncomingMedia"),
		     array("id" => "viewMedia", "title" => "CROMA - View Media", "content" => "viewMedia"),
		     array("id" => "viewPastUserMedia", "title" => "CROMA - View Past User Media", "content" => "viewPastUserMedia"),
		     array("id" => "mediaImportPath", "title" => "CROMA - Import Media", "content" => "getIncomingMedia")
		     ); 

$formBlocks = array(array("id" => "uploadMedia", "title" => "CROMA - Upload Media", "form" => "uploadMedia"),
		    array("id" => "mediaForm", "title" => "CROMA - Media Form", "form" => "mediaForm")
		    );


global $mediaBlockInfo;
global $mediaBlockViewFns;

blockLoadForms($mediaBlockInfo, $mediaBlockViewFns, $formBlocks);
blockLoadOther($mediaBlockInfo,$mediaBlockViewFns,$otherBlocks);  

?>