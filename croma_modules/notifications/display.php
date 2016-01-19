<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of notifications.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");
include_once("/var/www-croma/croma_modules/notifications/rachelDisplay.php");

$blocks = array(array("id" => "notifications_view", "title" => "CROMA - View Notifications", "content" => "viewNotifications"));

global $notificationsBlockInfo;
global $notificationsBlockViewFns;

blockLoadOther($notificationsBlockInfo,$notificationsBlockViewFns,$blocks);  

?>