<?php

include_once("dbFunctions.php");

/* dbAddUserAsOwnerOfOutreach() - assigns the user as the owner of the outreach
*/
function dbAddUserAsOwnerOfOutreach($UID, $OID)
{
  dbGenericInsert(array("UID"=>$UID,"OID"=>$OID, "isOwner"=>true), "usersVsOutreach");
}

/* dbAssignUserToOutreach() - assigns user to outreach
 */
function dbAssignUserToOutreach($UID, $OID)
{
  dbGenericInsert(array("UID"=>$UID,"OID"=>$OID), "usersVsOutreach");
} 

/* dbDeleteUser() - deletes user from CROMA database
 */ 

function dbDeleteUser($UID)
{
  dbRemoveEntry("profiles", "UID", $UID);
}

/* dbSelectAllTeams() - when registering for a team, search for a team by viewing existing teams
 */

function dbSelectAllTeams()
{ 
  return dbSimpleSelect("teams","isActive",true);
}

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

/* dbGetNotificationsForUser () - returns all the notifications for a particular user
 */

function dbGetNotificationsForUser($UID)
{
  $sql ="SELECT media.link, notifications.* FROM notifications ";
  $sql .="INNER JOIN teams on notifications.TID = teams.TID ";
  $sql .="INNER JOIN media on teams.MID = media.MID;";

  return (db_query($sql, array())->fetchAll(PDO::FETCH_ASSOC));
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

/* dbAddMedia () - adds a notification
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

// TODO - view all events (sort, filter, search)

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

/* dbCreateUser() - adds the given $row to the "users" table. Note that all validation of $row must be done before this function! It must be an associative array of the proper key/value pairs.
 */
function dbCreateUser($row)
{
  return dbGenericInsert($row, "profiles");
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


//Takes the given row and updates the corresponding outreach
function dbUpdateOutreach($OID,$row)
{
  return dbUpdate("outreach", $row, "OID", $OID);
}

//Returns all approved events for a user, including events from multiple teams
function dbGetApprovedOutreachForUser($UID)
{
  $sql="SELECT * FROM outreach ";
  //  $sql.="INNER JOIN usersVsOutreach, usersVsTeams ";
  $sql.="INNER JOIN usersVsOutreach ";
  //  $sql .="ON outreach.OID = usersVsOutreach.OID AND users.UID = usersVsTeams.UID";
  $sql .="ON outreach.OID = usersVsOutreach.OID ";
  $sql.="WHERE usersVsOutreach.UID = :UID AND outreach.status = :status;";

  $proxyFields = array(":UID" => $UID, ":status" => "isOutreach");

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

//Gets user hours from hours database
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

/* dbCreateTeam() - creates function to create a team when passes info like UIDs, Team Name, etc... 
 */

function dbCreateTeam($row)
{
  dbGenericInsert($row,"teams");
}

/* dbLogHours() - creates function to log hours given the user, outreach event, number of hours etc...
 */
function dbLogHours($row)
{
  dbGenericInsert($row,"hourCounting");
}

//Adds times to the Outreach
function dbAddTimesToOutreach($row)
{
  dbGenericInsert($row,"timesVsOutreach");
}

//Changes the isActive boolean in the table to false
function dbDeactivateTeam($TID)
{
  dbUpdate("teams", array("isActive" => false), "TID", $TID);
}

//Changes the isActive boolean in the table to true
function dbReactivateTeam($TID)
{
  dbUpdate("teams", array("isActive" => true), "TID", $TID);
}

//Gets the hours spent on an outreach
function dbGetHoursForOutreach($OID)
{
  $rows = dbSimpleSelect("hourCounting", "OID", $OID);
  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

//NOTICE, NEED TO CHANGE dbRemoveEntry so it can accept 2 variables
//Kicks a user from a team
function dbKickUserFromTeam($UID,$TID)
{
  $sql = "DELETE FROM usersVsTeams ";
  $sql .= "WHERE UID = :UID AND TID = :TID;";

  $proxyFields = array(":UID" => $UID, ":TID" => $TID);
  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

//Gets the users info on whether they signed up for pre, post or the outreach
function dbGetUserSignUpInfo($UID,$OID)
{
  return dbSimpleSelect("usersVsOutreach", "UID", $UID, "OID", $OID);
}

//Gets how many hours a user has put into an outreach
function dbGetHoursForUsersFromOutreach($UID,$OID)
{
  $rows = dbSimpleSelect("hourCounting", "UID", $UID, "OID", $OID);
  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }
  return $total;
}

/* dbAssignUserToTeam() -  adds a user into a team or changes the isApproved value based on what is already in the table. if the user is already in the table and isApproved is true then nothing else happens. If isApproved is not rue then it will add them on. if there is no user found in this table for that team it will add them
 */
function dbAssignUserToTeam($UID, $TID)
{
  $sql = "SELECT COUNT(*), usersVsTeams.isApproved FROM usersVsTeams ";
  $sql .= "WHERE UID = :UID AND TID = :TID;";

  $proxyFields = array(":UID" => $UID, ":TID" => $TID);

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    $result = $result[0]; // deal with the nested arrays
    if($result["COUNT(*)"] == 1)
      {
	if($result["isApproved"] == false)
	  {
	    dbUpdate("usersVsTeams", array("isApproved" => true), "UID", $UID, "TID", $TID);
	  }
	else
	  {
	    echo "This user has already been added to this team\n";
	  }
      }
    else
      {
	    dbGenericInsert(array("UID" => $UID, "TID" => $TID, "isApproved" => true), "usersVsTeams");
      }
  }

}

function dbGetUser($UID)
{
  $userArray = dbSimpleSelect("profiles", "UID", $UID);
  return $userArray[0];
}

function dbGetTeam($TID)
{
  $teamArray = dbSimpleSelect("teams", "TID", $TID);
  return $teamArray[0];
}

// TODO - ??
function dbGetUserEmailForNotifications()
{
  $sql ="SELECT * FROM notifications";
  $sql .=" INNER JOIN users";
  $sql .= " ON notifications.UID = users.UID;";
}

/* dbGetIncomingMediaForUser() - return all of a user's media that hasn't been assigned to an outreach event yet. 
*/
function dbGetIncomingMediaForUser($UID)
{
  return dbSimpleSelect("media", "UID", $UID, "OID", NULL);
}

function dbGetOutreachesForTeam($TID)
{
  return dbSimpleSelect("outreach","TID", $TID);
}

function dbGetUsersFromTeam($TID)
{
  $proxyFields = array(":TID"=>$TID);

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN profiles ON profiles.UID = usersVsTeams.UID ";
  $sql .= "WHERE usersVsTeams.TID = :TID";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));

}
?>