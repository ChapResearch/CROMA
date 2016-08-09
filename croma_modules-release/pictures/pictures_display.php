<?php
/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of user data
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/users/editThumbnailForm.php");

$formBlocks = array(
		    array("id" => "thumbnailForm", "title" => "CROMA - Thumbnail Form", "form" => "thumbnailForm"));

global $picturesBlockInfo;
global $picturesBlockViewFns;

blockLoadForms($picturesBlockInfo,$picturesBlockViewFns,$formBlocks);  
?>