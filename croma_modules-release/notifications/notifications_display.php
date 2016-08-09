<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of notifications.
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/notifications/viewNotifications.php");
include_once(MODULES_FOLDER."/notifications/manageNotifications.php");
include_once(MODULES_FOLDER."/notifications/notificationForm.php");
include_once(MODULES_FOLDER."/notifications/notificationMenu.php");

$blocks = array(array("id" => "notifications_view", "title" => "CROMA - View Notifications", "content" => "viewNotifications"),
		array("id" => "manageNotifications", "title" => "CROMA - Manage Notifications", "content" => "manageNotifications"),
		array("id" => "viewAllNotifications", "title" => "CROMA - View All Notifications", "content" => "viewAllNotifications"),
		array("id" => "menuBarNotification", "title" => "CROMA - View Notification Indicator", "content" => "menuBarNotification")
		);

$formBlocks = array(array('id'=>'notificationForm', 'title'=>'CROMA - Notification Form', 'form'=>'notificationForm'));

global $notificationsBlockInfo;
global $notificationsBlockViewFns;

blockLoadOther($notificationsBlockInfo,$notificationsBlockViewFns,$blocks);  
blockLoadForms($notificationsBlockInfo,$notificationsBlockViewFns,$formBlocks);  
?>