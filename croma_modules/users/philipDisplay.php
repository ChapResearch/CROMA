<?php


/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of a user.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");

// allows a user to view their personal information from the users table in the database

function viewUser(){
  global $user;
  $params = drupal_get_query_parameters();

  if (isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  $profile = dbGetUserProfile($UID);  
  $emailsVsUsers = dbGetEmailsForUser($UID);
  $markup = '<div align="left">' . "<br><h2><b>" . $profile['firstName'] . ' ' . $profile['lastName'] . "</b></h2></div>";
  $profilePic = file_load($profile["FID"]);
  $url = file_create_url($profilePic->uri);
  $markup .= '<div align="right"><img src="' .$url .'" style="width:125px;height:125px;">';
  
  
  $markup .='<div align="right"><a href= "http://croma.chapresearch.com/?q=profileForm';
  $markup .='&UID='. $UID . '">';
  $markup .='<button type="button">Edit User</button></a></div>';  
  $markup .= '<table>';

  $numberOfHours = dbGetUserHours($UID);
  $markup .= '<tr><td colspan="3"><b>Position:</b> ' . $profile['position'] . '</td>';
  $markup .= '<td colspan="3"><b>Number Of Hours:</b> ' . $numberOfHours . '</td></tr>';
  $markup .= '<tr><td colspan="3"><b>Email:</b> ' . $user->mail . '</td>';
  $markup .= '<td colspan="3"><b>Phone:</b> ' . $profile['phone'] . '</td></tr>';
  $markup .= '<tr><td colspan="3"><b>Grade:</b> ' . $profile['grade'] . '</td>';
  $markup .= '<td colspan="3"><b>Gender:</b> ' . $profile['gender'] . '</td></tr>';
  $markup .= '<tr><td colspan="6"><b>Bio:</b> ' . $profile['bio'] . '</td></tr>';  

  $markup .= '</table>';
  
  return array("#markup"=>$markup);

}

?> 