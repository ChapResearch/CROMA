<?php
include_once("dbFunctions.php");

//Takes the given row and updates the corresponding outreach
function dbUpdateOutreach($OID,$row)
{
  dbUpdate("outreach", $row, "OID", $OID);
}

//Returns all approved events for a user, including events from multiple teams
function dbGetApprovedOutreachForUser($UID)
{
  $con = dbConnect();
  if($con == null) {
    return(null);
  }

  $sql="SELECT * FROM outreach ";
  //  $sql.="INNER JOIN usersVsOutreach, usersVsTeams ";
  $sql.="INNER JOIN usersVsOutreach ";
  //  $sql .="ON outreach.OID = usersVsOutreach.OID AND users.UID = usersVsTeams.UID";
  $sql .="ON outreach.OID = usersVsOutreach.OID ";
  $sql.="WHERE usersVsOutreach.UID = $UID AND outreach.status = \"isOutreach\";";
  echo $sql;
  $result = mysqli_query($con,$sql);

  if (!$result) {
    dbErrorMsg("Error during sql getApprovedOutreachForUser" . mysqli_error($con));
    return false; 
  } else {
    $returnArray = array();
    while ($row = mysqli_fetch_assoc($result)) {
      $returnArray[] = $row;
    }
  }
  return $returnArray;
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
  $hadError = false;

  $con = dbConnect();
  if($con == null) {
    return(null);
  }


  $sql = "SELECT * FROM users WHERE ";

  if(ctype_space($searchString)){
      $firstAndLast = "firstname lastname";
      $firstAndLast = explode(" ", $searchString);
      $sql .= "(firstName = \"$firstAndLast[0]\" OR lastName = \"firstAndLast[1]\")";
  }

  else {
      $sql .= "(firstName = \"$searchString\" OR lastName = \"$searchString\")";
  }

  echo $sql;
  echo "\n";

  $result = mysqli_query($con,$sql);
  if (!$result) {
    dbErrorMsg("Error during sql select in dbSelect($table)" . mysqli_error($con));
    return false; 
  } else {
    $returnArray = array();
    while ($row = mysqli_fetch_assoc($result)) {
      $returnArray[] = $row;
    }
  }

  dbClose($con);

  if($hadError) {
    return(null);
  } else {
    return($returnArray);
  }

  return $result;
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
  $con = dbConnect();
  if($con == null) {
    return(null);
  }
  $sql = "DELETE FROM usersVsTeams ";
  $sql .= "WHERE UID = \"$UID\" AND TID = \"$TID\";";
  $result = mysqli_query($con,$sql);

  if (!$result) {
    dbErrorMsg("Error during sql select in dbKickUserFromTeam" . mysqli_error($con));
    return false; 
  }  

  dbClose($con);
  return true;
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

//dbAssignUserToTeam adds a user into a team or changes the isApproved value based on what is already in the table. if the user is already in the table and isApproved is true then nothing else happens. If isApproved is not rue then it will add them on. if there is no user found in this table for that team it will add them
function dbAssignUserToTeam($UID, $TID)
{
  $con = dbConnect();
  if($con == null) {
    return(null);
  }
  $sql = "SELECT COUNT(*), usersVsTeams.isApproved FROM usersVsTeams ";
  $sql .= "WHERE UID = \"$UID\" AND TID = \"$TID\";";
  $result = mysqli_query($con,$sql);
  if (!$result) {
    dbErrorMsg("Error during sql select in dbSelect($table)" . mysqli_error($con));
    return false; 
  } else {
    $row = mysqli_fetch_assoc($result);
    if($row["COUNT(*)"] == 1)
      {
	if($row["isApproved"] == false)
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

function dbGetUserEmailForNotifications()
{
  $con = dbConnect();
  if($con == null) {
    return(null);
  }

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

?>