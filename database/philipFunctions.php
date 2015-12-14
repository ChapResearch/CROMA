<?php
  
include_once("dbFunctions.php");

/* dbAssignUserToOutreach () - assigns user to outreach
 */

function dbAssignUserToOutreach($UID, $OID)
{
  dbGenericInsert(array("UID"=>$UID,"OID"=>$OID), "usersVsOutreach");
} 

/* dbDeleteUser () - deletes user from CROMA database
 */ 

function dbDeleteUser($UID)
{
  dbRemoveEntry("users", "UID", $UID);
}

/* dbSelectAllTeams () - when registering for a team, search for a team by viewing existing teams
 */

function dbSelectAllTeams()
{ 
  return dbSimpleSelect("teams","isActive",true);
}

/* dbReturnAllTeamsForUser () - returns all the teams associated with a user
 */

function dbReturnAllTeamsForUser($UID)
{
  $con = dbConnect();
  if($con == null) {
    return(null);
  }

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN teams ON teams.TID = usersVsTeams.TID ";
  $sql .= "WHERE usersVsTeams.UID = $UID";

  $result = mysqli_query($con,$sql);
  if (!$result) {
    dbErrorMsg("Error during sql select in dbReturnAllteamsForUser()" . mysqli_error($con));
    return false; 
  }

  $returnArray = array();
  while ($row = mysqli_fetch_assoc($result)){
    $returnArray[] = $row;
  }

  dbClose($con);

  return $returnArray;
}


/* dbGetNotificationsForUser () - returns all the notifications for a particular user
 */

function dbGetNotificationsForUser($UID)
{
  $con = dbConnect();
  if($con == null) {
    return(null);
  }
  
  $sql ="SELECT media.link, notifications.* FROM notifications ";
  $sql .="INNER JOIN teams on notifications.TID = teams.TID ";
  $sql .="INNER JOIN media on teams.MID = media.MID;";
  echo $sql;  
  $result = mysqli_query($con,$sql);
  if (!$result) {
    dbErrorMsg("Error during sql select in dbGetNotificationsForUser" . mysqli_error($con));
    return false; 
  }

  $returnArray = array();
  while ($row = mysqli_fetch_assoc($result)){
    $returnArray[] = $row;
  }

  dbClose($con);
  return $returnArray;
}


/* dbHideOutreach () - hides outreaches so they are not visible to the team
 */

function dbHideOutreach($OID)
{
  dbUpdate("outreach", array("cancelled"=>true),"OID",$OID);
}

/* dbGetNumPplSignedUpForEvent () - calculates the number of people signed up for an event
 */

function dbGetNumPplSignedUpForEvent($UID,$OID)
{
  return dbSimpleSelect("usersVsOutreach","UID", $UID, "OID", $OID);
}

/* dbGetPendingUsers () - approves users pending approval to team
 */

function dbGetPendingUsers($TID)
{
  return dbSimpleSelect("usersVsTeams","TID", $TID, "isApproved", false);
}

/* dbAddMedia () - adds media to an outreach
 */

function dbAddNotification($row)
{ 
  dbGenericInsert($row, "notifications");
}

/* dbAddMedia () - adds media to an outreach
 */

function dbAddMedia ($row)
{ 
  dbGenericInsert($row, "media");
}

/* dbAddEmails () - adds emails for a user
 */

function dbAddEmails($UID, $emails)
{
  foreach($emails as $email){
    dbGenericInsert(array("UID" => $UID, "email" => $email), "emailsVsUsers");
  }
}

// view all events (sort, filter, search)

/* dbCreateOutreach() - adds the given outreach to the "outreach" table
 */

function dbCreateOutreach($row)
{
  dbGenericInsert($row, "outreach");
}

/* dbCreateUser() - adds the given $row to the "users" table. Note that all validation of $row must be done before this function! It must be an associative array of the proper key/value pairs.
 */
function dbCreateUser($row)
{
  dbGenericInsert($row, "users");
  // TODO - this should deal with emails and team assignment too!
}

/* dbApproveUser() - approve user with the given UID to a team
 */

function dbApproveUser($UID, $TID)
{
  dbUpdate("usersVsTeams", array("isApproved"=>true),"UID", $UID, "TID", $TID);
}
/* dbApproveEvent() - approve event  with the given OID 
 */

function dbApproveEvent($OID)
{
  dbUpdate("outreach", array("status"=>"isOutreach"), "OID", $OID);
}

/* dbGetOutreachIdeas ()-  user can get outreach ideas, returns all outreach events that are not approved
 */

function dbGetOutreachIdeas($TID)
{ 
  return dbSimpleSelect("outreach","status","isIdea","TID",$TID);
}

?>