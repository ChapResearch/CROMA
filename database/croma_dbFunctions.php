<?php

include_once("dbFunctions.php");

/* -------------------------- croma_dbFunctions.php ----------------------------------------------
   This file contains all CROMA-specific database functions. It relies on the file dbFunctions.php
   for low-level access to the mySQL database (and its ability to operate outside of Drupal). The
   file is roughly organized into sections based on which table the function is associated with.
*/

/* -------------------------------------- OUTREACH ---------------------------------------------- */

/* dbAddUserAsOwnerOfOutreach() - assigns the user as the owner of the outreach
*/
function dbAddUserAsOwnerOfOutreach($UID, $OID)
{
  return dbGenericInsert(array("UID"=>$UID,"OID"=>$OID, "isOwner"=>true), "usersVsOutreach");
}

/* dbAssignUserToOutreach() - assigns user to outreach
 */
function dbAssignUserToOutreach($UID, $OID)
{
  return dbGenericInsert(array("UID"=>$UID,"OID"=>$OID), "usersVsOutreach");
} 

/* dbHideOutreach() - hides outreaches so they are not visible to the team. This is done by setting "cancelled" to true.
 */
function dbHideOutreach($OID)
{
  dbUpdate("outreach", array("cancelled"=>true),"OID",$OID);
}

/* dbCreateOutreach() - adds the given outreach to the "outreach" table and returns the OID of the new outreach.
 */
function dbCreateOutreach($row)
{
  $OID = dbGenericInsert($row, "outreach");
  if ($OID == 0){
    dbErrorMsg("Outreach not created!");
    return false;
  } else {
    return $OID;
  }
}

/* dbGetOutreachThumbnail() - return the FID for the outreach thumbnail
 */
function dbGetOutreachThumbnail($OID)
{
  $outreach = dbSimpleSelect('outreach', 'OID', $OID);
  return $outreach[0]['FID'];
}

/* dbGetNumPplSignedUpForEvent() - calculates the number of people signed up for an event
 */
function dbGetNumPplSignedUpForEvent($UID,$OID)
{
  return dbSimpleSelect("usersVsOutreach","UID", $UID, "OID", $OID);
}

/* dbGetOutreachIdeas()-  user can get outreach ideas, returns all outreach events that are not approved
 */
function dbGetOutreachIdeas($TID)
{ 
  return dbSimpleSelect("outreach","status","isIdea","TID",$TID);
}
 
/* dbGetOutreach()-  returns the outreach for the given OID
 */
function dbGetOutreach($OID)
{ 
  $array = dbSimpleSelect("outreach","OID",$OID);
  return $array[0]; // deal with nested arrays
}

/* dbUpdateOutreach() - takes the given row and updates the corresponding outreach
 */
function dbUpdateOutreach($OID,$row)
{
  return dbUpdate("outreach", $row, "OID", $OID);
}

/* dbGetHoursForOutreach() - calculates the total volunteer hours puting into the outreach given b $OID
 */
function dbGetHoursForOutreach($OID)
{
  $rows = dbSimpleSelect("hourCounting", "OID", $OID);
  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

/* dbApproveEvent() - approve event  with the given OID 
 */
function dbApproveEvent($OID)
{
  dbUpdate("outreach", array("status"=>"isOutreach"), "OID", $OID);
}

/* dbAddTimesToOutreach() - adds starting and ending day/times to the outreach
 */
function dbAddTimesToOutreach($row)
{
  return dbGenericInsert($row,"timesVsOutreach");
}

/* dbGetMediaForOutreach() - returns all media assigned to the given outreach
 */
function dbGetMediaForOutreach($OID)
{
  return dbSimpleSelect("media", "OID", $OID);
}


/* ----------------------------------------- TEAMS ---------------------------------------------------- */

/* dbGetTeamLogo() - return the FID for the team logo
 */
function dbGetTeamLogo($TID)
{
  $team = dbSimpleSelect('teams', 'TID', $TID);
  return $team['FID'];
}

/* dbSelectAllTeams() - when registering for a team, search for a team by viewing existing teams
 */
function dbSelectAllTeams()
{ 
  return dbSimpleSelect("teams","isActive",true);
}

/* dbGetPendingUsers() - approves users pending approval to team
 */
function dbGetPendingUsers($TID)
{
  return dbSimpleSelect("usersVsTeams","TID", $TID, "isApproved", false);
}

/* dbUpdateTeam() - takes the given row and updates the team given by $TID
 */
function dbUpdateTeam($TID,$row)
{
  return dbUpdate("teams", $row, "TID", $TID);
}

/* dbCreateTeam() - creates function to create a team when passes info like UIDs, Team Name, etc... 
 */
function dbCreateTeam($row)
{
  $row['isActive'] = true;
  return dbGenericInsert($row,"teams");
}


/* dbDeactivateTeam() - deactivates the team given by $TID
 */
function dbDeactivateTeam($TID)
{
  dbUpdate("teams", array("isActive" => false), "TID", $TID);
}

/* dbDeactivateTeam() - reactivates the team given by $TID
 */
function dbReactivateTeam($TID)
{
  dbUpdate("teams", array("isActive" => true), "TID", $TID);
}

/* dbGetHoursForTeam() - calculates the total volunteer hours put into a team's outreaches
 */
function dbGetHoursForTeam($TID)
{
  $sql = "SELECT * FROM hourCounting ";
  $sql .= "INNER JOIN outreach ON hourCounting.OID = outreach.OID ";
  $sql .= "INNER JOIN teams ON outreach.TID = teams.TID ";
  $sql .= "WHERE teams.TID = :TID;";

  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields);

  $rows = $result->fetchAll(PDO::FETCH_ASSOC);

  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

/* dbKickUserFromTeam() - removes the user given by $UID from the team given by $TID
 */
function dbKickUserFromTeam($UID,$TID)
{
  $sql = "DELETE FROM usersVsTeams ";
  $sql .= "WHERE UID = :UID AND TID = :TID;";

  $proxyFields = array(":UID" => $UID, ":TID" => $TID);
  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

/* dbGetNumPplForTeam() - returns the number of people on a given team.
 */

function dbGetNumPplForTeam($TID)
{
  $sql = "SELECT COUNT(*), teams.* FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if($result) {
    return $result[0]["COUNT(*)"];
  }

  return -1;
}

/* dbGetNumOutreachForTeam() - returns the number of outreach on a given team.
 */

function dbGetNumOutreachForTeam($TID)
{
  $sql = "SELECT COUNT(*), outreach.* FROM outreach ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if($result) {
    return $result[0]["COUNT(*)"];
  }

  return -1;
}

/* dbAssignUserToTeam() -  adds a user into a team or changes the isApproved value based on what is already in the table. if the user is already in the table and isApproved is true then nothing else happens. If isApproved is not true then it will add them on. if there is no user found in this table for that team it will add them
 */
function dbAssignUserToTeam($UID, $TID)
{
  $sql = "SELECT COUNT(*), usersVsTeams.* FROM usersVsTeams ";
  $sql .= "WHERE UID = :UID AND TID = :TID;";

  $proxyFields = array(":UID" => $UID, ":TID" => $TID);

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    $result = $result[0]; // deal with the nested arrays
    if($result["COUNT(*)"] == 1) { // user has previously applied for this team
      if($result["isApproved"] == false) { // user hasn't been assigned to the team
	dbUpdate("usersVsTeams", array("isApproved" => true), "UID", $UID, "TID", $TID);
      }
    } else { // user hasn't applied for this team
      return dbGenericInsert(array("UID" => $UID, "TID" => $TID, "isApproved" => true), "usersVsTeams");
    }
  }

}

/* dbGetTeam() - return all data from the team given by $TID
 */
function dbGetTeam($TID)
{
  $teamArray = dbSelect("teams", array("TID"=>$TID), null, 1);
  return $teamArray[0]; // deal with nested arrays
}

/* dbGetOutreachesForTeam() - return all outreaches for the team given by $TID
 */
function dbGetOutreachesForTeam($TID)
{
  return dbSimpleSelect("outreach","TID", $TID);
}

/* dbGetOutreachesForTeam() - return all users for the team given by $TID
 */
function dbGetUsersFromTeam($TID)
{
  $proxyFields = array(":TID"=>$TID);

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN profiles ON profiles.UID = usersVsTeams.UID ";
  $sql .= "WHERE usersVsTeams.TID = :TID";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* ----------------------------------------------- USERS ------------------------------------------------------ */

/* dbReturnAllTeamsForUser() - returns all the teams associated with a user
 */
function dbReturnAllTeamsForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN teams ON teams.TID = usersVsTeams.TID ";
  $sql .= "WHERE usersVsTeams.UID = :UID";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));

}

/* dbGetHoursForUserFromOutreach() - calculates how many hours a user has put into an outreach
 */
function dbGetHoursForUserFromOutreach($UID,$OID)
{
  $rows = dbSimpleSelect("hourCounting", "UID", $UID, "OID", $OID);
  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }
  return $total;
}

/* dbGetTeamsForUser() - return all teams the user given by $UID is assigned to (or has applied to join)
 */
function dbGetTeamsForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN teams ON teams.TID = usersVsTeams.TID ";
  $sql .= "WHERE usersVsTeams.UID = :UID";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbDeleteUser() - deletes user from CROMA database
 */ 
function dbDeleteUser($UID)
{
  dbRemoveEntry("profiles", "UID", $UID);
}

/* dbGetUserSignUpInfo() - gets the users info on whether they signed up for pre, post or the outreach
 */
function dbGetUserSignUpInfo($UID,$OID)
{
  return dbSimpleSelect("usersVsOutreach", "UID", $UID, "OID", $OID);
}

/* dbGetIncomingMediaForUser() - return all of a user's media that hasn't been assigned to an outreach event yet. 
*/
function dbGetIncomingMediaForUser($UID)
{
  return dbSimpleSelect("media", "UID", $UID, "OID", NULL);
}

/* dbGetUserProfile() - returns the profile for the user given by $UID
 */
function dbGetUserProfile($UID)
{
  $userArray = dbSimpleSelect("profiles", "UID", $UID);
  $count = count($userArray);
  switch (true){
  case ($count == 0): dbErrorMsg("No profile for user $UID!"); break;
  case ($count > 1): dbErrorMsg("More than one profile for the user"); break;
  case ($count == 1): return $userArray[0];
  }
  return false; // had an error
}

/* dbGetNotificationsForUser() - returns all the notifications for a particular user
 */
function dbGetNotificationsForUser($UID)
{
  $sql ="SELECT teams.FID, notifications.* FROM notifications ";
  $sql .="INNER JOIN teams on notifications.TID = teams.TID;";

  return (db_query($sql, array())->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetEmailsForUser() - returns all the secondary emails for a user. Does NOT include the email address entered into Drupal.
 */
function dbGetEmailsForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT email FROM emailsVsUsers ";
  $sql .= "WHERE UID = :UID";

  $data = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  $retArray = array();
  foreach ($data as $datum){
    $retArray[] = $datum['email']; // flatten the simple array
  }

  return ($retArray);
}

/* dbAddEmails() - adds emails for a user
 */
function dbAddEmailsToUser($UID, $emails)
{
  foreach($emails as $email){
    $EUID = dbGenericInsert(array("UID" => $UID, "email" => $email), "emailsVsUsers");
    if($EUID == false){
      return false;
    }
  }
  return true;
}

/* dbCreateProfile() - adds the given $row to the "profiles" table. Note that all validation of $row must be done before this function! It must be an associative array of the proper key/value pairs.
 */
function dbCreateProfile($row)
{
  return dbGenericInsert($row, "profiles");
}

/* dbApproveUser() - approve user with the given UID to a team
 */
function dbApproveUser($UID, $TID)
{
  dbUpdate("usersVsTeams", array("isApproved"=>true),"UID", $UID, "TID", $TID);
}


/* dbGetApprovedOutreachForuser() - returns all approved events for a user, including events from multiple teams
 */
function dbGetApprovedOutreachForUser($UID)
{
  $sql="SELECT * FROM outreach ";
  $sql.="INNER JOIN usersVsOutreach ";
  $sql .="ON outreach.OID = usersVsOutreach.OID ";
  $sql.="WHERE usersVsOutreach.UID = :UID AND outreach.status = :status;";

  $proxyFields = array(":UID" => $UID, ":status" => "isOutreach");

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetUserHours() - calculates total hours a user has volunteered
 */
function dbGetUserHours($UID)
{
  $rows = dbSimpleSelect("hourCounting", "UID", $UID);
  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

//TODO(bonus) - revise such that the function accepts an array of "search parameters" that become if the "or" part of the WHERE (you'll need an if statement to check if the parameter is null before you add it to the list!!). I should be able to call the function by using an associative array of any length!
function dbFindUsers($searchString)
{
  $sql = "SELECT * FROM users WHERE ";
  $sql .= "(firstName = :firstName OR lastName = :lastName)";

  if(ctype_space($searchString)){
      $firstAndLast = "firstname lastname";
      $firstAndLast = explode(" ", $searchString);
      $proxyFields = array(":firstName" => $firstAndLast[0], ":lastName" => $firstAndLast[1]);
  } else {
      $proxyFields = array(":firstName" => $searchString, ":lastName" => $searchString);
  }

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}
 
/* --------------------------------------------------------- MEDIA -------------------------------------------- */

/* dbAddMedia() - adds media (whether associated with an outreach event or not)
 */
function dbAddMedia ($row)
{ 
  return dbGenericInsert($row, "media");
}

/* dbUpdateTeam() - takes the given row and updates the media given by $MID
 */
function dbUpdateMedia($MID,$row)
{
  return dbUpdate("media", $row, "MID", $MID);
}

/* ------------------------------------------------- HOUR-LOGGING --------------------------------------- */

/* dbLogHours() - creates function to log hours given the user, outreach event, number of hours etc...
 */
function dbLogHours($row)
{
  return dbGenericInsert($row,"hourCounting");
}

/* ------------------------------------------------- NOTIFICATIONS --------------------------------------------- */

/* dbAddNotification() - adds a notification
 */
function dbAddNotification($row)
{ 
  return dbGenericInsert($row, "notifications");
}


?>