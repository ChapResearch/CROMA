<?php

include_once("dbFunctions.php");

/* -------------------------- croma_dbFunctions.php ----------------------------------------------
    file contains all CROMA-specific database functions. It relies on the file dbFunctions.php
   for low-level access to the mySQL database (and its ability to operate outside of Drupal). The
   file is roughly organized into sections based on which table the function is associated with.
*\

/* -------------------------------------- OUTREACH ---------------------------------------------- */

// note that this function is currently not used
function dbExportTeamOutreach($TID)
{
  $sql = 'SELECT outreach.* ';
  $sql .= 'FROM outreach WHERE TID = :TID ';
  $sql .= "INTO OUTFILE '/tmp/outreach.csv' ";
  $sql .= "FIELDS TERMINATED BY ',' ENCLOSED BY '\"' ";
  $sql .= "LINES TERMINATED BY '\n'";

  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
}

/* dbIsOutreachCancelled() - checks if the outreach is cancelled
 */
function dbIsOutreachCancelled($OID)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE OID = :OID and cancelled=1;";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if (!empty($result)) {
    return true;
  }
  return false;
}

/* dbSetOutreachToPublic() - sets an outreach to public
*/
function dbSetOutreachToPublic($OID)
{
  return dbUpdate("outreach", array("isPublic"=>'1'), "OID", $OID);
}

/* dbSetOutreachToPrivate() - sets an outreach to private
*/
function dbSetOutreachToPrivate($OID)
{
  return dbUpdate("outreach", array("isPublic"=>'0'), "OID", $OID);
}

/* dbAddUserAsOwnerOfOutreach() - assigns the user as the owner of the outreach
*/
function dbAddUserAsOwnerOfOutreach($UID, $OID)
{
  return dbUpdate("outreach", array("UID"=>$UID), "OID", $OID);
}

/* dbAssignUserToOutreach() - assigns user to outreach.
 */
function dbAssignUserToOutreach($UID, $OID, $type)
{
  return dbGenericInsert(array("UID"=>$UID,"OID"=>$OID, "type"=>$type), "usersVsOutreach");
} 

/* dbGetUIDsForOutreach() - return a list of names and UIDs for all people associated with an outreach event. Used for creating a dropdown or checkbox list.
 */
function dbGetUIDsForOutreach($OID)
{
  $sql = "select UIDs.UID, "; // return the UID
  $sql .= "CONCAT(profiles.firstName,' ',profiles.lastName) as name "; // return the first and last name
  $sql .= 'from (select outreach.UID from outreach where OID = :OID '; // get the UID for the owner of the outreach
  $sql .= 'union select UID from usersVsOutreach where OID = :OID) UIDs '; // get UIDs for people signed up
  $sql .= 'inner join profiles on UIDs.UID = profiles.UID'; // inner join with profiles to get the name

  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    $retArr = array();
    foreach($result as $row){
      if ($row['UID'] != null){
      $retArr[$row['UID']] = $row['name'];
      }
    }
    return $retArr;
  }
  return false;
}

/* dbAssignUserToOutreach() - assigns user to outreach. Will replace duplicates that have the exact same information!
 */
function dbRemoveCommitmentFromOutreach($UID, $OID, $type)
{
  return dbRemoveEntries(array("UID"=>$UID,"OID"=>$OID, "type"=>$type), "usersVsOutreach");
} 

/* dbGetCancelledOutreach() - gets the cancelled outreach for a team
 */
function dbGetCancelledOutreach($TID)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE TID = :TID and cancelled=1;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetOutreachListForTeam() - generates a list of OID's pointing to names (in order to populate a dropdown
 */
function dbGetOutreachListForTeam($TID)
{
  $sql = "SELECT OID, name FROM outreach ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    $retArr = array();
    foreach($result as $row){
      $retArr[$row['OID']] = $row['name'];
    }
    return $retArr;
  }
  return false;
}

/* dbGetOutreachTagsForTeam() - gets all the outreach tags for a team
 */
function dbGetOutreachTagsForTeam($TID)
{
  $sql = "SELECT OTID, tagName FROM outreachTags ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    $retArr = array();
    foreach($result as $row){
      $retArr[$row['OTID']] = $row['tagName'];
    }
    return $retArr;
  }
  return false;
}

/* dbGetTagName() - gets an outreach tag's name based on OTTID
 */
function dbGetTagName($OTID)
{
  $sql = "SELECT tagName FROM outreachTags ";
  $sql .= "WHERE OTID = :OTID;";
  $proxyFields = array(":OTID" => $OTID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['tagName'];
  }
  return false;
}

/* dbCreateOutreachTagForTeam() - creates new outreach tag for a team
 */
function dbCreateOutreachTagForTeam($tagName, $TID)
{
  $row = array('TID' => $TID, 'tagName' => $tagName);
  return dbGenericInsert($row, 'outreachTags');
}

/* dbUpdateOutreachTag() - updates outreach tag for a team
 */
function dbUpdateOutreachTag($OTID, $tagName)
{
  return dbUpdate('outreachTags', array('tagName'=>$tagName), 'OTID', $OTID);
}

/* dbDeleteOutreachTag() - deletes outreach tag for a team
 */
function dbDeleteOutreachTag($OTID)
{
  return dbRemoveEntry('outreachTags', 'OTID', $OTID);
}

function dbAddTagToOutreach($OTID, $OID)
{
  return dbGenericInsert(array('OTID'=>$OTID, 'OID'=>$OID), 'tagsVsOutreach');
}

function dbRemoveTagFromOutreach($OTID, $OID)
{
  return dbRemoveEntry('tagsVsOutreach', 'OTID', $OTID, 'OID', $OID);
}

/* dbGetOutreachMatchingTags() - returns an array of the OID and names of outreaches matching ALLL of the tags given in the array. Note that the input array must be an array of tag names. This function does not check that the given tags are all from the given team. Passing count means the function will only return the number of matching outreaches.
 */
function dbGetOutreachMatchingTags($tags, $TID, $count = true)
{
  $sql = 'SELECT ';
  if ($count){
    $sql .= 'COUNT(*)';
  } else {
    $sql .= 'outreach.OID, name';
  }
  $sql .= ' FROM outreach ';
  $sql .= 'INNER JOIN tagsVsOutreach ON outreach.OID = tagsVsOutreach.OID ';
  $sql .= 'INNER JOIN outreachTags ON outreachTags.OTID = tagsVsOutreach.OTID ';
  $sql .= 'WHERE outreach.TID = :TID ';
  $proxyFields = array(':TID' => $TID);

  foreach($tags as $index => $tagName){
    $sql .= "AND outreachTags.tagName = :tagName$index ";
    $proxyFields[":tagName$index"] = $tagName;
  }

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);  

  if ($result){
    if ($count){
      return isset($result[0]['COUNT(*)'])?$result[0]['COUNT(*)']:0;
    }
    return $result;
  }
}

/* dbGetTagsForOutreach() - returns an array of the OTID and tag names for the outreach given by $OID. The $OTID_only bool allows the user to generate a list of just OTID's, which is necessary for setting the default for a select HTML element, for example.
 */
function dbGetTagsForOutreach($OID, $OTID_only = false)
{
  $sql = 'SELECT outreachTags.OTID, tagName FROM outreachTags ';
  $sql .= 'INNER JOIN tagsVsOutreach ON outreachTags.OTID = tagsVsOutreach.OTID ';
  $sql .= 'INNER JOIN outreach ON outreach.OID = tagsVsOutreach.OID ';
  $sql .= 'WHERE outreach.OID = :OID ';

  $proxyFields = array(':OID' => $OID);

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);  

  if ($result){
    $retArr = array();
    foreach($result as $row){
      if (!$OTID_only){
      $retArr[$row['OTID']] = $row['tagName'];
      } else {
	$retArr[] = $row['OTID'];
      }
    }
    return $retArr;
  }
}

/* dbGetLockedOutreachForTeam() - gets the locked outreach for a team
 */
function dbGetLockedOutreachForTeam($TID)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE TID = :TID and status='locked';";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetIdeasForTeam() - gets the ideas for a team
 */
function dbGetIdeasForTeam($TID)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE TID = :TID and status='isIdea';";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetWriteUpsForTeam() - gets the write ups for a team
 */
function dbGetWriteUpsForTeam($TID, $submitted = null)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE TID = :TID and status='doingWriteUp' ";
  if (isset($submitted)){
    $sql .= 'AND outreach.isWriteUpSubmitted = :isWriteUpSubmitted';
    $proxyFields[':isWriteUpSubmitted']=$submitted;
  }

  $proxyFields[":TID"] = $TID;
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetPrivateOutreachForTeam() - gets the private outreach for a team
 */
function dbGetPrivateOutreachForTeam($TID)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE TID = :TID AND isPublic=0 AND status='locked';";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetPublicOutreachForTeam() - gets the private outreach for a team
 */
function dbGetPublicOutreachForTeam($TID)
{
  $sql = "SELECT * FROM outreach ";
  $sql .= "WHERE TID = :TID AND isPublic=1 AND status='locked';";
  $proxyfields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetUserSignUpUOID() - returns the ID of the record for the user's sign up for an outreach. Returning false indicates the user is not signed up for the event.
 */
function dbGetUserSignUpUOID($UID, $OID)
{
  $sql = "SELECT UOID FROM usersVsOutreach ";
  $sql .= "WHERE UID = :UID ";
  $sql .= "AND OID = :OID ";
  $proxyFields = array(":UID" => $UID, ":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  if ($result){
    $UOID = $result["0"]["UOID"];
  }
  return isset($UOID)?$UOID:false;
}



/* dbIsUserSignedUp() - checks if a user is signed up for a certain outreach
 */
function dbIsUserSignedUp($UID, $OID)
{
  $sql = "SELECT UID, OID FROM usersVsOutreach ";
  $sql .= "WHERE UID = :UID ";
  $sql .= "AND OID = :OID ";
  $proxyFields = array(":UID" => $UID, ":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  
  return !empty($result);
}

/* dbRemoveUserFromOutreach() - removes a user's association from an outreach
 */
function dbRemoveUserFromOutreach($UID, $OID){
  $sql = "DELETE FROM usersVsOutreach ";
  $sql .= "WHERE UID = :UID AND OID = :OID;";

  $proxyFields = array(":UID" => $UID, ":OID" => $OID);
  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

function dbApproveIdea($OID)
{
  return dbUpdate("outreach", array("status"=>'isOutreach'),"OID",$OID);
}

function dbRejectIdea($OID)
{
  return dbRemoveEntry("outreach","OID",$OID);
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

/* dbDuplicateOutreach() - duplicates the given outreach event (and increments the name)
*/
function dbDuplicateOutreach($OID)
{
  $row = dbGetOutreach($OID);
  $row['name'] = $row['name'] . ' copy';
  $row['logDate'] = dbDatePHP2SQL(time());
  unset($row['OID']);

  $newOID = dbGenericInsert($row, 'outreach');
  if ($newOID == 0){
    dbErrorMsg("Outreach not created!");
    return false;
  } else {
    return $newOID;
  }
}

/* dbDuplicateOutreachTimes() - duplicates the times associated with the given outreach event and associates them with the new outreach event. Returns whether the function was successful.
 */
function dbDuplicateOutreachTimes($oldOID, $newOID)
{
  $times = dbGetTimesForOutreach($oldOID);
  $worked = true;
  foreach($times as &$time){
    unset($time['TOID']);
    $time['OID'] = $newOID;
    if (!dbGenericInsert($time, 'timesVsOutreach')){
      $worked = false;
    }
  }
  return $worked;
}

/* dbGetOutreachThumbnail() - return the FID for the outreach thumbnail
 */
function dbGetOutreachThumbnail($OID)
{
  $outreach = dbSimpleSelect('outreach', 'OID', $OID);
  return $outreach[0]['FID'];
}

/* dbGetPplSignedUpForEvent() - gets the list of people signed up for an event
 */
function dbGetPplSignedUpForEvent($OID)
{
  $sql="SELECT DISTINCT UID FROM usersVsOutreach WHERE OID = :OID";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }

  return false;
}

/* dbGetNumPplSignedUpForEvent() - calculates the number of people signed up for an event
 */
function dbGetNumPplSignedUpForEvent($OID)
{
  $sql="SELECT COUNT(DISTINCT UID) FROM usersVsOutreach WHERE OID = :OID";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['COUNT(DISTINCT UID)'];
  }

  return false;
}

/* dbGetOutreachIdeas() -  returns all outreach events that are still ideas
 */
function dbGetOutreachIdeas($UID, $limit = null)
{ 
  return dbSelect("outreach",array("status"=>"isIdea","UID"=>$UID), array('logDate'=>'DESC'), $limit);
}

/* dbGetOutreachWriteUp() - returns all outreaches in the "write-up" phase
 */
function dbGetOutreachWriteUp($TID, $limit = null)
{ 
  return dbSelect("outreach",array("status"=>"writeUp","TID"=>$TID), array(), $limit);
}
 
/* dbGetOutreach()-  returns the outreach for the given OID
 */
function dbGetOutreach($OID)
{ 
  $array = dbSimpleSelect("outreach","OID",$OID);
  if (!empty($array)){
    return $array[0]; // deal with nested arrays
  }
  return false;
}

/* dbUpdateOutreach() - takes the given row and updates the corresponding outreach
 */
function dbUpdateOutreach($OID,$row)
{
  return dbUpdate("outreach", $row, "OID", $OID);
}

function dbUpdateTimesForOutreach($TOID,$row)
{
  return dbUpdate("timesVsOutreach", $row, "TOID", $TOID);
}

/* dbGetHoursForOutreach() - calculates the total volunteer hours puting into the outreach given by $OID
 */
function dbGetHoursForOutreach($OID)
{
  $rows = dbSimpleSelect("hourCounting", "OID", $OID, 'isApproved', true);
  $total = 0;
  foreach ($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

function dbGetTotalHours()
{
  $rows = dbSimpleSelect("hourCounting", 'isApproved', true);
  $total = 0;
  foreach ($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}


/* dbApproveEvent() - approve event with the given OID 
 */
function dbApproveEvent($OID)
{
  dbUpdate("outreach", array("status"=>"isOutreach"), "OID", $OID);
}

/* dbGetMediaForOutreach() - returns all media assigned to the given outreach
 */
function dbGetMediaForOutreach($OID)
{
  return dbSimpleSelect("media", "OID", $OID);
}

function dbCancelEvent($OID)
{
  dbUpdate("outreach", array("cancelled"=>1), "OID", $OID);
}

function dbUncancelEvent($OID)
{
  dbUpdate("outreach", array("cancelled"=>0), "OID", $OID);
}

/* dbGetOutreachOwner() - return the UID of the owner of the outreach
 */
function dbGetOutreachOwner($OID)
{
  $sql = "SELECT UID FROM outreach ";
  $sql .= "WHERE OID = :OID";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['UID'];
  }
  return false;
}

/* dbGetOutreachName() - return the name of the outreach
 */
function dbGetOutreachName($OID)
{
  $sql = "SELECT name FROM outreach ";
  $sql .= "WHERE OID = :OID;";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['name'];
  }
  return false;
}

/* dbAddTimesToOutreach() - adds starting and ending day/times to the outreach
 */
function dbAddTimesToOutreach($timeData)
{
  return dbGenericInsert($timeData,"timesVsOutreach");
}

/* dbRemoveTimeFromOutreach() - adds starting and ending day/times to the outreach
 */
function dbRemoveTimeFromOutreach($TOID)
{
  return dbRemoveEntry("timesVsOutreach", 'TOID', $TOID);
}

function dbIsOutreachOver($OID)
{
  $sql = "SELECT MAX(endTime) FROM timesVsOutreach ";
  $sql .= "WHERE OID = :OID;";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result[0]['MAX(endTime)'] != null) {
    return (dbDateSQL2PHP($result[0]['MAX(endTime)']) < time());
  }
  return false;
}

/* dbGetTimesForOutreach() - return all start and end times for the outreach (including the ID of that row, to use when updating)
 */
function dbGetTimesForOutreach($OID)
{
  $sql = "SELECT startTime, endTime, TOID FROM timesVsOutreach ";
  $sql .= "WHERE OID = :OID ORDER BY startTime";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result;
  }
  return false;
}

/* dbGetEarliestTimeForOutreach() - returns the earliest start time for the given outreach
 */
function dbGetEarliestTimeForOutreach($OID)
{
  $sql = "SELECT MIN(startTime) FROM timesVsOutreach ";
  $sql .= "WHERE OID = :OID";

  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['MIN(startTime)'];
  }
  return false;
}

/* dbGetEarliestTimeForFutureOutreach() - returns the closest start time for the given outreach (if it is in the future)
 */
function dbGetEarliestTimeForFutureOutreach($OID)
{
  $sql = "SELECT MIN(startTime) FROM timesVsOutreach ";
  $sql .= "WHERE OID = :OID AND startTime >= NOW()";

  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['MIN(startTime)'];
  }
  return false;
}

function dbGetOutreachesForUserForTeam($UID, $TID)
{
  $proxyFields = array(":UID"=>$UID , ":TID"=>$TID);

  $sql ="SELECT DISTINCT outreach.* FROM usersVsOutreach ";
  $sql .= "INNER JOIN outreach ON usersVsOutreach.OID = outreach.OID ";
  $sql .= "WHERE usersVsOutreach.UID = :UID AND outreach.TID = :TID";
  
  $rows = (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
  return $rows;
}

function dbGetOutreachHoursForUserForTeam($UID, $TID)
{
  $proxyFields = array(":UID"=>$UID , ":TID"=>$TID);

  $sql ="SELECT * from hourCounting ";
  $sql .= "INNER JOIN outreach ON hourCounting.OID = outreach.OID ";
  $sql .= "WHERE hourCounting.UID = :UID AND outreach.TID = :TID AND isApproved = true";
  
  $rows = (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
  return $rows;
}
/* ----------------------------------------- TEAMS ---------------------------------------------------- */

function dbRescindTeamApplication($UID, $TID)
{
  return dbRemoveEntry('usersVsTeams', 'UID', $UID, 'TID', $TID);
}

function dbGetTeamApplication($UID, $TID)
{
  return dbSelect('usersVsTeams', array('UID'=>$UID, 'TID'=>$TID), null, null, 'userEmail, userMessage');
}

function dbGetStatusForTeam($TID)
{
  $sql = "SELECT isActive FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  return $result[0]['isActive'];
}

function dbApproveTeam($TID)
{
  dbUpdate('teams', array('isApproved' => 1), 'TID', $TID);
}

function dbRejectTeam($TID)
{
  dbUpdate('teams', array('isApproved' => 0), 'TID', $TID);
}

function dbGetOwnerForTeam($TID)
{
  $sql = "SELECT UID FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['UID'];
  }
  return false;
}

/* dbGetTeamForOutreach() - gets the TID of the team associated with the given outreach (given by OID)
 */
function dbGetTeamForOutreach($OID)
{
  $sql = "SELECT TID FROM outreach ";
  $sql .= "WHERE OID = :OID;";
  $proxyFields = array(":OID" => $OID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['TID'];
  }
  return false;
}

/* dbGetTeamLogo() - return the FID for the team logo
 */
function dbGetTeamLogo($TID)
{
  $sql = "SELECT FID FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['FID'];
  }
  return false;
}

/* dbGetTeamName() - return the name of the team
 */
function dbGetTeamName($TID)
{
  $sql = "SELECT name FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['name'];
  }
  return false;
}

/* dbGetTeamOwner() - return the UID of the owner of the team
 */
function dbGetTeamOwner($TID)
{
  $sql = "SELECT UID FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['UID'];
  }
  return false;
}

function dbGetTeamNumber($TID)
{
  $sql = "SELECT number FROM teams ";
  $sql .= "WHERE TID = :TID;";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['number'];
  }
  return false;
}

/* dbSelectAllTeams() - when registering for a team, search for a team by viewing existing teams
 */
function dbSelectAllTeams()
{ 
  return dbSimpleSelect("teams","isActive",true);
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
  return dbUpdate("teams", array("isActive" => 0), "TID", $TID);
}

/* dbKickAllUsersFromTeam() - removes all users from a given team
 */
function dbKickAllUsersFromTeam($TID)
{
  return dbRemoveEntries(array('TID'=>$TID), 'usersVsTeams');
}

/* dbRemoveAllRolesFromTeam() - removes all role associations from a given team
 */
function dbRemoveAllRolesFromTeam($TID)
{
  return dbRemoveEntries(array('TID'=>$TID), 'usersVsRoles');
}

/* dbReactivateTeam() - reactivates the team given by $TID
 */
function dbReactivateTeam($TID)
{
  dbUpdate("teams", array("isActive" => true), "TID", $TID);
}

/* dbIsTeamApproved() - returns whether the team with the given $TID is approved
 */
function dbIsTeamApproved($TID)
{
  return dbSelect("teams", array('TID'=>$TID), null, 1, 'isApproved')[0]['isApproved'];
}

/* dbGetHoursForTeam() - calculates the total volunteer hours put into a team's outreaches
 */
function dbGetHoursForTeam($TID)
{
  $sql = '(select numberOfHours from hourCounting ';
  $sql .= 'inner join outreach on hourCounting.OID = outreach.OID ';
  $sql .= 'inner join teams on outreach.TID = teams.TID ';
  $sql .= 'where teams.TID = :TID and outreach.cancelled = 0 and outreach.status != :status and hourCounting.isApproved = 1) ';
  $sql .= 'union all (select numberOfHours from oldHoursVsTeams where TID = :TID)';

  $proxyFields = array(":TID" => $TID, ':status' => 'isIdea');
  $result = db_query($sql, $proxyFields);

  $rows = $result->fetchAll(PDO::FETCH_ASSOC);

  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

function dbIsUserApprovedForTeam($UID, $TID)
{
  $sql = 'SELECT * FROM usersVsTeams ';
  $sql .= 'WHERE UID = :UID AND TID = :TID;';
  $proxyFields = array(":UID" => $UID, ":TID" => $TID);
  $result = db_query($sql, $proxyFields);
  $rows = $result->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($rows)){
    return $rows[0]['isApproved'];
  }
  return false;
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

/* dbGetNumStudentsForTeam() - returns the number of people on a given team.
 */
function dbGetNumStudentsForTeam($TID)
{
  $sql = "SELECT COUNT(*) FROM usersVsTeams ";
  $sql .= 'INNER JOIN profiles ON usersVsTeams.UID = profiles.UID ';
  $sql .= "WHERE usersVsTeams.TID = :TID AND usersVsTeams.isApproved = 1 AND profiles.type = 'student';";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]["COUNT(*)"];
  }

  return false;
}

/* dbGetNumMentorsForTeam() - returns the number of people on a given team.
 */
function dbGetNumMentorsForTeam($TID)
{
  $sql = "SELECT COUNT(*) FROM usersVsTeams ";
  $sql .= 'INNER JOIN profiles ON usersVsTeams.UID = profiles.UID ';
  $sql .= "WHERE usersVsTeams.TID = :TID AND usersVsTeams.isApproved = 1 AND profiles.type = 'mentor'";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]["COUNT(*)"];
  }

  return -1;
}

/* dbGetNumOutreachForTeam() - returns the number of outreach on a given team.
 */
function dbGetNumOutreachForTeam($TID)
{
  $sql = "SELECT COUNT(*) FROM outreach ";
  $sql .= "WHERE TID = :TID AND cancelled != 1 AND status != 'isIdea';";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]["COUNT(*)"];
  }

  return 0;
}

/* dbGetNumOutreach() - returns the number of outreach for all teams.
 */
function dbGetNumTotalOutreach()
{
  $sql = "SELECT COUNT(*), outreach.* FROM outreach ";
  $result = db_query($sql, array())->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]["COUNT(*)"];
  }

  return false;
}

/* dbGetNumOutreachForUser() - returns the number of outreach for a given user. This includes both the outreaches the user has signed up for, as well as the outreaches the user owns.
 */
function dbGetNumOutreachForUser($UID)
{
  $sql = "SELECT COUNT(*) FROM ";
  $sql .= "(SELECT OID FROM usersVsOutreach WHERE UID = :UID ";
  $sql .= "UNION SELECT OID FROM outreach WHERE UID = :UID AND type != 'isIdea') x";

  $proxyFields = array(":UID" => $UID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]["COUNT(*)"];
  }

  return false;
}

/* dbGetRejectedTeams() - returns all teams that have not been approved yet
 */
function dbGetRejectedTeams()
{
  return dbSimpleSelect('teams', "isApproved", 0);
}

/* dbGetTeamsPendingApproval() - returns all teams that have not been approved yet
 */
function dbGetTeamsPendingApproval()
{
  return dbSimpleSelect('teams', "isApproved", NULL);
}

/* dbGetTeamsAppliedFor() - returns the TIDs of all teams the user has applied for
 */
function dbGetTeamsAppliedFor($UID)
{
  $records = dbSimpleSelect('usersVsTeams', "UID", $UID, "isApproved", false);
  $retArr = array();
  foreach($records as $record){
    $retArr[] = $record['TID'];
  }
  return array_unique($retArr);
}

/* dbApplyForTeam() - allow user with the given UID to apply for the team with the given TID. Note that this function does NOT deal with the associated notification!
 */
function dbApplyForTeam($application)
{
  if (in_array($application['TID'], dbGetTeamsForUser($application['UID']))){
    printErrorMsg('You are already on this team!');
    return false;
  }
  
  if (in_array($application['TID'], dbGetTeamsAppliedFor($application['UID']))){
    printErrorMsg('You have already applied for this team!');
    return false;
  }

  return dbGenericInsert($application, 'usersVsTeams');
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
    if ($result["COUNT(*)"] == 1) { // user has previously applied for this team
      if ($result["isApproved"] == false) { // user hasn't been assigned to the team
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
  if (!empty($teamArray)){
    return $teamArray[0]; // deal with nested arrays
  }
  return false;
}

/* dbGetAllTeams() - gets total number of teams registered with CROMA
 */
function dbGetNumTotalTeams()
{
  $sql = "SELECT COUNT(*) FROM teams WHERE isApproved = true AND isActive = true";
  $result = db_query($sql)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]["COUNT(*)"];
  }
  return false;
}

/* dbGetTeamByNumber() - return all data from the team given by the team number
 */
function dbGetTeamByNumber($number)
{
  $teamArray = dbSelect("teams", array("number"=>$number, 'isActive'=>1, 'isApproved'=>1), null, 1);
  if ($teamArray){
    return $teamArray[0]; // deal with nested arrays
  }
  return false;
}

/* dbGetTeamTIDByNumber() - return the TID from the team given by the team number
 */
function dbGetTeamTIDByNumber($number)
{
  $teamArray = dbSelect("teams", array("number"=>$number), null, 1, 'TID');
  if ($teamArray){
    return $teamArray[0]['TID']; // deal with nested arrays
  }
  return false;
}

/* dbGetOutreachesForTeam() - return all outreaches for the team given by $TID
 */
function dbGetOutreachesForTeam($TID, $orderParams = null, $limit = null)
{
  $proxyFields = array(":TID"=>$TID);

  $sql = "SELECT * FROM outreach ";
  $sql .= "INNER JOIN timesVsOutreach ON timesVsOutreach.OID = outreach.OID ";
  $sql .= "WHERE outreach.TID = :TID AND outreach.cancelled != 1 ";
  if ($orderParams == 'upcoming'){
    $sql .= 'AND timesVsOutreach.startTime > NOW() ';
  }
  $sql .= "GROUP BY outreach.OID ";
  
  if ($orderParams == 'upcoming'){
    $sql .= "ORDER BY timesVsOutreach.startTime";
  } else if ($orderParams == 'logDate'){
    $sql .= "ORDER BY outreach.logDate DESC";
  }    

  if ($limit != null){
    $sql .= " LIMIT $limit";
  }

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetUsersFromTeam() - return all users for the team given by $TID
 */
function dbGetUsersFromTeam($TID, $type = '')
{
  $proxyFields = array(":TID"=>$TID);

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN profiles ON profiles.UID = usersVsTeams.UID ";
  $sql .= "WHERE usersVsTeams.TID = :TID AND isApproved = 1 ";
  if ($type != ''){
    $sql .= 'AND type = :type ';
    $proxyFields[':type'] = $type;
  }
  $sql .= "ORDER BY profiles.lastName";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

function dbSearchUsersFromTeam($TID, $query)
{
  $proxyFields = array(':TID' => $TID, ':query' => $query);
  $sql = 'SELECT * FROM usersVsTeams ';
  $sql .= 'INNER JOIN users ON users.UID = usersVsTeams.UID ';
  $sql .= 'INNER JOIN profiles ON profiles.UID = usersVsTeams.UID ';
  $sql .= 'WHERE usersVsTeams.TID = :TID ';
  $sql .= 'AND (profiles.firstName LIKE :query OR profiles.lastName LIKE :query OR users.mail LIKE :query)';
  return db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
}

function dbGetUsersListFromTeam($TID)
{
  $proxyFields = array(":TID"=>$TID);

  $sql = "SELECT profiles.UID, firstName, lastName FROM usersVsTeams ";
  $sql .= "INNER JOIN profiles ON profiles.UID = usersVsTeams.UID ";
  $sql .= "WHERE usersVsTeams.TID = :TID AND isApproved = 1 ORDER BY lastName";

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  $retArr = array();
  if ($result){
    foreach($result as $row){
      $retArr[$row['UID']] = $row['firstName'].' '.$row['lastName'];
    }
    return $retArr;
  }
  return false;
}

/* dbGetUIDsFromTeam() - return all users for the team given by $TID
 */
function dbGetUIDsFromTeam($TID)
{
  $proxyFields = array(":TID"=>$TID);

  $sql = "SELECT profiles.UID FROM usersVsTeams ";
  $sql .= "INNER JOIN profiles ON profiles.UID = usersVsTeams.UID ";
  $sql .= "WHERE usersVsTeams.TID = :TID";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* ----------------------------------------------- USERS ------------------------------------------------------ */

function dbGetDefaultTIDForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT TID FROM usersVsTeams ";
  $sql .= "WHERE isApproved = true AND isDefault = true AND UID = :UID";

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  
  if ($result){
    return $result[0]['TID'];
  }
  return false;
}

function dbSetTeamAsDefaultForUser($UID, $TID)
{
  return dbUpdate('usersVsTeams', array('isDefault'=>true), 'TID', $TID, 'UID', $UID);
}

/* dbRemoveUserFromFutureTeamOutreach() - removes the user from all events the user is signed up for, but hasn't logged hours for.
 */
function dbRemoveUserFromFutureTeamOutreach($UID, $TID)
{
  $proxyFields = array(":UID"=>$UID, ':TID'=>$TID);

  $sql = 'DELETE usersVsOutreach FROM usersVsOutreach ';
  $sql .= 'usersVsOutreach LEFT JOIN hourCounting on usersVsOutreach.OID = hourCounting.OID ';
  $sql .= 'INNER JOIN outreach on hourCounting.OID = outreach.OID ';
  $sql .= 'WHERE usersVsOutreach.UID = :UID AND hourCounting.UID IS NULL AND outreach.TID = :TID';

  return (db_query($sql, $proxyFields) != false);
}

function dbDisableUser($UID)
{
  dbUpdate("users", array("status"=>0),"uid", $UID);
}

/* dbSearchUserByEmail() - returns the UID of the user associated with the given email
 */
function dbSearchUserByEmail($email)
{
  $proxyFields = array(":email"=>$email);

  $sql = "SELECT users.UID FROM users ";
  $sql .= "LEFT JOIN emailsVsUsers ON users.UID = emailsVsUsers.UID ";
  $sql .= "WHERE emailsVsUsers.email = :email ";
  $sql .= "OR users.mail = :email";

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  if (!empty($result)){
    return $result[0]['UID'];
  }
  return false;
}

/* dbGetHoursForUserFromOutreach() - calculates how many hours a user has put into an outreach
 */
function dbGetHoursForUserFromOutreach($UID,$OID)
{
  $rows = dbSelect('hourCounting', array('UID'=>$UID,'OID'=>$OID, 'isApproved'=>true));
  $total = 0;
  foreach($rows as $row){
    $total += $row['numberOfHours'];
  }
  return $total;
}

/* dbGetHours() - returns the hourCounting records given the various filter parameters
 */
function dbGetHours($filterParams)
{
  $table = 'hourCounting INNER JOIN outreach USING (OID)';

  return dbSelect($table, $filterParams, null, null, 'DISTINCT hourCounting.*');
}

/* dbGetOIDForHours() - returns the OID associated with the given hour record
 */
function dbGetOIDForHours($HID)
{
  return dbSelect('hourCounting', array('HID'=>$HID), null, null, 'OID')[0]['OID'];
}

/* dbGetUserName() - returns the full name of the user with the given UID
 */
function dbGetUserName($UID)
{
  $proxyFields = array(":UID"=>$UID);
  $sql = "SELECT firstName, lastName FROM profiles ";
  $sql .= "WHERE UID = :UID";

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  
  if ($result){
    return $result[0]['firstName'] . ' ' . $result[0]['lastName'];
  }

  return false;
}

function dbGetTeamsListForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);
  $sql = "SELECT teams.TID, teams.number FROM usersVsTeams ";
  $sql .= "INNER JOIN teams ON usersVsTeams.TID = teams.TID ";
  $sql .= "WHERE usersVsTeams.UID = :UID AND usersVsTeams.isApproved = 1";

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result){
    $retArr = array();
    foreach($result as $row){
      $retArr[$row['TID']] = $row['number'];
    }
    return $retArr;
  }

  return false;
}

/* dbGetTIDsForUser() - return an array of TID's the user is associated with
 */
function dbGetTIDsForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);
  $sql = "SELECT TID FROM usersVsTeams ";
  $sql .= "WHERE usersVsTeams.UID = :UID AND isApproved = 1";

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result){
    $retArr = array();
    foreach($result as $row){
      $retArr[] = $row['TID'];
    }
    return $retArr;
  }

  return false;
}

/* dbGetTeamsForUser() - return all teams the user given by $UID is assigned to (or has applied to join)
 */
function dbGetTeamsForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT * FROM usersVsTeams ";
  $sql .= "INNER JOIN teams ON teams.TID = usersVsTeams.TID ";
  $sql .= "WHERE usersVsTeams.UID = :UID AND usersVsTeams.isApproved = true ";
  $sql .= "AND teams.isApproved = true AND teams.isActive = true";

  $result = db_query($sql, $proxyFields);
  if ($result){
    return $result->fetchAll(PDO::FETCH_ASSOC);
  }
  return false;
}

function dbUserAwaitingApprovalForTeam($UID, $TID)
{
  $sql = 'SELECT * FROM usersVsTeams ';
  $sql .= 'WHERE UID = :UID AND TID = :TID';
  $proxyFields = array(':UID'=>$UID, ':TID'=>$TID);
  $rows = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  return $rows[0]['isApproved'];
}

/* dbGetPendingTeams() - return all teams the user with the given UID has applied to
 */
function dbGetPendingTeams($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT teams.name, teams.number, teams.TID FROM usersVsTeams ";
  $sql .= "INNER JOIN teams ON teams.TID = usersVsTeams.TID ";
  $sql .= "WHERE usersVsTeams.UID = :UID AND usersVsTeams.isApproved = false";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetUnapprovedTeamsForUser() - return all teams the user has created that have not yet been approved
 */
function dbGetUnapprovedTeamsForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT teams.name, teams.number, teams.TID FROM teams ";
  $sql .= "WHERE UID = :UID AND isApproved is null";

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
  $proxyFields = array(":UID"=>$UID, ":OID"=>$OID);

  $sql = "SELECT DISTINCT usersVsOutreach.* FROM usersVsOutreach ";
  $sql .= "WHERE UID = :UID AND OID = :OID";

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetUserSignUpType() - gets the users signup type for a certain outreach(can be for pre, post)
 */
function dbGetUserSignUpType($UID,$OID)
{
   $types = dbSelect("usersVsOutreach", array("UID"=> $UID, "OID" =>$OID),null, null, "type");
   
  if ($types){
    $retArr = array();
    foreach($types as $row){
      $retArr[] = $row['type'];
    }
    return $retArr;
  }
  return false;
}

/* dbGetIncomingMediaForUser() - return all of a user's media that hasn't been assigned to an outreach event yet. 
*/
function dbGetIncomingMediaForUser($UID)
{
  return dbSimpleSelect("media", "UID", $UID, "OID", NULL);
}

function dbGetPastMediaForUser($UID)
{
  $sql = 'SELECT * FROM media WHERE UID = :UID AND OID IS NOT NULL ORDER BY dateEntered DESC';
  $proxyFields = array(':UID' => $UID);
  return db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
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

/* dbUserHasProfile() - returns whether a user has a profile
 */
function dbUserHasProfile($UID)
{
  $userArray = dbSimpleSelect("profiles", "UID", $UID);
  $count = count($userArray);

  if ($count > 1){
    dbErrorMsg('User has more than one profile!');
    return true;
  } else if ($count == 1){
    return true;
  }
  return false;
}

function dbUserMoreThan1Team($UID)
{
  $sql = "SELECT COUNT(*) FROM usersVsTeams ";
  $sql .= "INNER JOIN teams USING (TID) ";
  $sql .= "WHERE usersVsTeams.UID = :UID AND usersVsTeams.isApproved = 1 ";
  $sql .= "AND teams.isActive = 1 AND teams.isApproved = 1";
  $proxyFields = array(":UID" => $UID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  return $result[0]['COUNT(*)'] > 1;
}

/* dbGetSecondaryEmailForUser() - returns the secondary emails for a user. Does NOT include the email address entered into Drupal. Note: currently a max of one secondary can be entered, though the database supports the addition of multiple.
 */
function dbGetSecondaryEmailForUser($UID)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT email FROM emailsVsUsers ";
  $sql .= "WHERE UID = :UID LIMIT 1";

  $data = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($data){
    return $data[0]['email'];
  }
  return false;
}

/* dbCheckSecondaryEmail() - given an email, compare it against all other emails in the database to be sure it doesn't belong to another user. Returns true if the email is unique, false otherwise.
 */
function dbCheckSecondaryEmailForUser($email, $UID)
{
  $proxyFields = array(':email'=>trim($email), ':UID'=>$UID);
  $sql = "SELECT COUNT(*) FROM emailsVsUsers ";
  $sql .= "RIGHT JOIN users ON users.uid = emailsVsUsers.UID ";
  $sql .= "WHERE (emailsVsUsers.email LIKE :email AND emailsVsUsers.UID != :UID)";
  $sql .= " OR users.mail LIKE :email";

  $data = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($data){
    return $data[0]['COUNT(*)'] < 1;
  }
  return false;
}

/* dbAddEmails() - adds emails for a user
 */
function dbAddEmailsToUser($UID, $emails)
{
  foreach($emails as $email){
    $EUID = dbGenericInsert(array("UID" => $UID, "email" => $email), "emailsVsUsers");
    if ($EUID == false){
      return false;
    }
  }
  return true;
}

/* dbRemoveAllEmailsForUser() - removes all emails for a user
 */
function dbRemoveAllEmailsForUser($UID)
{
  return dbRemoveEntries(array('UID'=>$UID), 'emailsVsUsers');
}

/* dbCreateProfile() - adds the given $row to the "profiles" table. Note that all validation of $row must be done before this function! It must be an associative array of the proper key/value pairs.
 */
function dbCreateProfile($row)
{
  return dbGenericInsert($row, "profiles");
}

/* dbGetUsersAwaitingApproval() - get all users not get approved for the team, as well as any data from their application
 */
function dbGetUsersAwaitingApproval($TID)
{
  $sql="SELECT profiles.*, usersVsTeams.* FROM usersVsTeams ";
  $sql.="INNER JOIN profiles ";
  $sql.="ON usersVsTeams.UID = profiles.UID ";
  $sql.="WHERE usersVsTeams.TID = :TID AND usersVsTeams.isApproved = false ";

  $proxyFields = array(":TID" => $TID);

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbApproveUser() - approve user with the given UID to a team
 */
function dbApproveUser($UID, $TID)
{
  dbUpdate("usersVsTeams", array("isApproved"=>true),"UID", $UID, "TID", $TID);
}

/* dbRejectUser() - delete the user's application to the team
 */
function dbRejectUser($UID, $TID)
{
  dbRemoveEntries(array('UID'=>$UID, 'TID'=>$TID), 'usersVsTeams', 1);
}

/* dbGetApprovedOutreachForuser() - returns all approved events for a user, including events from multiple teams
 */
function dbGetApprovedOutreachForUser($UID, $limit = null)
{
  $sql="SELECT DISTINCT outreach.* FROM outreach ";
  $sql.="LEFT JOIN usersVsOutreach ";
  $sql.="ON outreach.OID = usersVsOutreach.OID ";
  $sql.="WHERE (usersVsOutreach.UID = :UID OR outreach.UID = :UID) AND outreach.status = :status ";
  $sql.="AND outreach.cancelled = false ";
  
  if ($limit != null){
    $sql .= "LIMIT $limit";
  }

  $proxyFields = array(":UID" => $UID, ":status" => "isOutreach");

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetUpcomingOutreachForUser() - returns all approved events (with associated times) for a user, including events from multiple teams. Note again that the events must have times associated with them!
 */
function dbGetUpcomingOutreachForUser($UID, $limit = null)
{
  $sql="SELECT DISTINCT outreach.* FROM outreach ";
  $sql.="LEFT JOIN usersVsOutreach ";
  $sql.="ON outreach.OID = usersVsOutreach.OID ";
  $sql.="INNER JOIN timesVsOutreach ON timesVsOutreach.OID = outreach.OID ";
  $sql.="WHERE (usersVsOutreach.UID = :UID OR outreach.UID = :UID) AND outreach.status = :status ";
  $sql.="AND outreach.cancelled = false ";
  
  if ($limit != null){
    $sql .= "LIMIT $limit";
  }

  $proxyFields = array(":UID" => $UID, ":status" => "isOutreach");

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* dbGetUserPrimaryEmail() - returns the primary email for a user. NOTE: this accesses Drupal tables.
 */
function dbGetUserPrimaryEmail($UID)
{
  $sql="SELECT mail FROM users ";
  $sql.="WHERE uid = :UID;";
  $proxyFields = array(":UID" => $UID);

  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result){
    return $result[0]['mail'];
  } else {
    dbErrorMsg("User $UID has no primary email!");
  }

  return false;
}

function generateSearchSQL($searchParams, &$proxyFields)
{
  global $user;
  $UID = $user->uid;
  $sql = "SELECT * FROM outreach ";

  if (isset($searchParams['signedUp'])) {
    $sql .= 'LEFT JOIN usersVsOutreach ON outreach.OID = usersVsOutreach.OID ';
  }

  if (isset($searchParams['tags'])) {
    $sql .= 'INNER JOIN tagsVsOutreach ON outreach.OID = tagsVsOutreach.OID ';
  }

  if (isset($searchParams['date']) || isset($searchParams['within5Years']) || isset($searchParams['year'])){
    $sql .= 'INNER JOIN timesVsOutreach ON outreach.OID = timesVsOutreach.OID ';
  }

  $sql .= 'WHERE ';

  $i = 1;
  $length = count($searchParams);
  foreach($searchParams as $field => $data){
    switch($field){
    case 'status': // special case status (as multiple statuses can be selected)
      $sql .= statusSearch_helper($data, $proxyFields);
      break;
    case 'teams': // another special case
      $sql .= teamSearch_helper($data, $proxyFields);
      break;
    case 'tags': // another special case
      $sql .= tagsSearch_helper($data, $proxyFields);
      break;
    case 'owner': // another special case
      $sql .= ownerSearch_helper($data['value'], $proxyFields);
      break;
    case 'date':
      $sql .= "timesVsOutreach.startTime BETWEEN :startDate AND :endDate ";
      $proxyFields[':startDate'] = $data['start'];
      $proxyFields[':endDate'] = $data['end'];
      break;
    case 'within5Years':
      if (isset($searchParams['date'])){
	continue; // shouldn't ever have both set
      }
      $sql .= "YEAR(timesVsOutreach.startTime) > YEAR(curdate()) - 5";
      break;
    case 'year':
      if (isset($searchParams['date']) || isset($searchParams['within5Years'])) {
	continue; // shouldn't ever have both set
      }
      $sql .= "YEAR(timesVsOutreach.startTime) = :year ";
      $proxyFields[':year'] = $data['year'];
      break;
    case 'signedUp':
      $teams = dbGetTIDsForUser($UID);
      if (!isset($searchParams['teams'])) {
	$sql .= '(';
	$k = 1;
	foreach($teams as $team){
	  $sql .= "outreach.TID = :TID$k OR ";
	  $proxyFields[":TID$k"] = $team;
	  $k++;
	}
	$sql .= 'usersVsOutreach.UID = :UID)';
      } else {
	$sql .= 'usersVsOutreach.UID = :UID';
      }
      $proxyFields[':UID'] = $searchParams['signedUp']['value'];
      break;
    default:
      $sql .= "outreach.$field ";
      if ($data['matchType'] == 'exact'){
	$sql .= "= :$field";
	$proxyFields[":$field"] = $data['value'];
      } else {
	$sql .= "LIKE :$field";
	$proxyFields[":$field"] = '%'.$data['value'].'%';
      }

    }
    if ($i < $length){
      $sql .= " AND ";
    }
    $i++;
  }

  $sql .= ' GROUP BY outreach.OID';
  dpm($sql);
  return $sql;
}

function ownerSearch_helper($owners, &$proxyFields)
{
  $i = 1;
  $length = count($owners);
  $sql = '(';

  foreach($owners as $owner){
    $sql .= "outreach.UID = :owner$i";
    $proxyFields[":owner$i"] = $owner;
    if ($i < $length){
      $sql .= ' OR ';
    }
    $i++;
  }
  $sql .= ')';

  return $sql;
}

function statusSearch_helper($statuses, &$proxyFields)
{
  $i = 1;
  $length = count($statuses);
  $sql = '(';
  foreach($statuses as $status){
    $sql .= "outreach.status = :status$i";
    $proxyFields[":status$i"] = $status;
    if ($i < $length){
      $sql .= ' OR ';
    }
    $i++;
  }
  $sql .= ')';
  return $sql;
}

function teamSearch_helper($teams, &$proxyFields)
{
  $i = 1;
  $length = count($teams);
  $sql = '(';
  foreach($teams as $team){
    $sql .= "outreach.TID = :TID$i";
    $proxyFields[":TID$i"] = $team;
    if ($i < $length){
      $sql .= ' OR ';
    }
    $i++;
  }
  $sql .= ')';
  return $sql;
}

function tagsSearch_helper($tags, &$proxyFields)
{
  $i = 1;
  $length = count($tags);
  $sql = '(';
  foreach($tags as $tag){
    $sql .= "tagsVsOutreach.OTID = :OTID$i";
    $proxyFields[":OTID$i"] = $tag;
    if ($i < $length){
      $sql .= ' OR ';
    }
    $i++;
  }
  $sql .= ')';
  return $sql;
}

/* dbSearchOutreach() - advanced search for outreach
 */
function dbSearchOutreach($sql, $proxyFields)
{
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  if ($result){
    return $result;
  } else {
    printMsg("No results found!");
  }

  return false;
}

/* dbGetUserHours() - calculates total hours a user has volunteered
 */
function dbGetUserHours($UID)
{
  $rows = dbSimpleSelect("hourCounting", "UID", $UID, 'isApproved', true);
  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

/* dbGetAssociatedOutreachForUser() - Returns the list of OID's for outreaches for which the user is either an owner or a participant.
 */
function dbGetAssociatedOutreachForUser($UID)
{
  $proxyFields = array(':UID' => $UID);
  $sql = 'SELECT DISTINCT outreach.OID FROM outreach ';
  $sql .= 'LEFT JOIN usersVsOutreach ON usersVsOutreach.OID = outreach.OID ';
  $sql .= 'WHERE (usersVsOutreach.UID = :UID OR outreach.UID = :UID) AND outreach.cancelled = 0';
  return db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
}


function dbGetAllAssociatedOutreachForUser($UID)
{
  $proxyFields = array(':UID' => $UID);
  $sql = 'SELECT outreach.* FROM outreach ';
  $sql .= 'LEFT JOIN usersVsOutreach ON usersVsOutreach.OID = outreach.OID ';
  $sql .= 'WHERE (usersVsOutreach.UID = :UID OR outreach.UID = :UID OR ';
  $teams = dbGetTIDsForUser($UID);
  $length = count($teams);
  $i = 1;
  foreach($teams as $team){
    $sql .= "outreach.TID = :TID$i";
    $proxyFields[":TID$i"] = $team;
    if ($i < $length){
      $sql .= ' OR ';
    }
    $i++;
  }
  $sql .= ')';
  $outreaches = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  return $outreaches;
}


/* dbGetUserHoursForTeam() - calculates total hours a user has volunteered for a specific team they are assigned to
 */
function dbGetUserHoursForTeam($UID, $TID)
{
  $proxyFields = array(":UID"=>$UID , ":TID"=>$TID);

  $sql ="SELECT * FROM hourCounting ";
  $sql .= "INNER JOIN outreach ON hourCounting.OID = outreach.OID ";
  $sql .= "WHERE hourCounting.UID = :UID AND outreach.TID = :TID and isApproved = true";
  
  $rows = (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));

  $total = 0;
  foreach($rows as $row){
    $total += $row["numberOfHours"];
  }

  return $total;
}

/* dbGetNumOutreachesForUserForTeam() - calculates total number of outreaches a user has volunteered for a specific team they are assigned to
 */
function dbGetNumOutreachesForUserForTeam($UID, $TID)
{
  $proxyFields = array(":UID"=>$UID , ":TID"=>$TID);

  $sql ="SELECT COUNT(DISTINCT usersVsOutreach.OID) FROM usersVsOutreach ";
  $sql .= "INNER JOIN outreach ON usersVsOutreach.OID = outreach.OID ";
  $sql .= "WHERE usersVsOutreach.UID = :UID AND outreach.TID = :TID ";
  $sql .= "AND outreach.status != 'isIdea' AND cancelled = 0";
  
  $result = (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));

  if ($result){
    return $result[0]['COUNT(DISTINCT usersVsOutreach.OID)'];
  }
  return false;
}


/* dbGetOutreachForUser() - returns all outreaches associated with the given user
 */
function dbGetOutreachForUser($UID, $orderParams = null, $limit = null)
{
  $proxyFields = array(":UID"=>$UID);

  $sql = "SELECT * FROM outreach ";
  $sql .= "INNER JOIN timesVsOutreach ON timesVsOutreach.OID = outreach.OID ";
  $sql .= "LEFT JOIN usersVsOutreach ON outreach.OID = usersVsOutreach.OID ";
  $sql .= "WHERE (outreach.UID = :UID OR usersVsOutreach.UID = :UID) AND outreach.cancelled != 1 ";
  if ($orderParams == 'upcoming'){
    $sql .= 'AND timesVsOutreach.startTime > NOW() ';
  }
  $sql .= "GROUP BY outreach.OID ";
  
  if ($orderParams == 'upcoming'){
    $sql .= "ORDER BY timesVsOutreach.startTime";
  }

  if ($limit != null){
    $sql .= " LIMIT $limit";
  }
  dpm($sql);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result){
    dpm($result);
    return $result;
  }
  return false;
}

function dbGetOwnedOutreachForUser($UID)
{
  return dbSimpleSelect("outreach", "UID", $UID);
}
 
/* --------------------------------------------------------- MEDIA -------------------------------------------- */

/* dbAddMedia() - adds media (whether associated with an outreach event or not)
 */
function dbAddMedia ($row)
{ 
  return dbGenericInsert($row, "media");
}

/* dbUpdateMedia() - takes the given row and updates the media given by $MID
 */
function dbUpdateMedia($MID,$row)
{
  return dbUpdate("media", $row, "MID", $MID);
}

/* dbGetMedia() - returns media when given an $MID
 */
function dbGetMedia($MID)
{
  $media = dbSelect("media", array("MID"=>$MID), array(), 1); // returns a single media
  return $media[0]; // deal with nested arrays
}

/* dbDeleteMedia() - removes media from an outreach when given an $MID and $OID
 */
function dbDeleteMedia($MID)
{
  $media = dbGetMedia($MID);
  $FID = $media['FID'];

  $sql = "DELETE FROM media ";
  $sql .= "WHERE MID = :MID LIMIT 1;";

  $proxyFields = array(":MID" => $MID);
  $result = db_query($sql, $proxyFields);

  return ($FID);
}

/* dbGetUserForMedia() - returns the user associated with the given media
 */
function dbGetUserForMedia($MID)
{
  return dbSimpleSelect('media', "MID", $MID)[0]['UID'];
}

function dbGetUserForNotification($NID)
{
  return dbSimpleSelect('notifications', "NID", $NID)[0]['UID'];
}


/* ------------------------------------------------- HOUR-LOGGING --------------------------------------- */

/* dbLogHours() - creates function to log hours given the user, outreach event, number of hours etc...
 */
function dbLogHours($row)
{
  return dbGenericInsert($row,"hourCounting");
}

function dbGetHTID($TID, $year)
{
  $sql = "SELECT HTID FROM oldHoursVsTeams ";
  $sql .= "WHERE TID = :TID ";
  $sql .= "AND year = :year ";
  $proxyFields = array(":TID" => $TID, ":year"=>$year);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result)
    {
  return $result;
    }
  return false;
}

function dbRemoveOldHours($HTID)
{
  return dbRemoveEntry("oldHoursVsTeams", 'HTID', $HTID);
}

function dbGetOffsetHours($TID)
{
  $sql = "SELECT numberOfHours, year, HTID FROM oldHoursVsTeams ";
  $sql .= "WHERE TID = :TID ";
  $proxyFields = array(":TID" => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result)
    {
  return $result;
    }
  return false;
}

/* dbLogHours() - creates function to log hours given the user, outreach event, number of hours etc.
 */
function dbUpdateHours($row, $HID)
{
  return dbUpdate("hourCounting", $row, "HID", $HID);
}


/* dbApproveHours() - creates function to approve an hours record with the given $HID
 */
function dbApproveHours($HID)
{
  return dbUpdate("hourCounting", array('isApproved'=>true), "HID", $HID);
}

/* dbGetUserForHours() - returns the user associated with the given hours record
 */
function dbGetUserForHours($HID)
{
  return dbSimpleSelect('hourCounting', "HID", $HID)[0]['UID'];
}

/* dbDeleteHours() - creates a function to remove an hours record given the HID
 */
function dbDeleteHours($HID)
{
  return dbRemoveEntry("hourCounting", 'HID', $HID);
}

function dbGetHour($HID)
{
  return dbSimpleSelect("hourCounting", 'HID', $HID)[0];
}

function dbAddHourOffset($row)
{
  return dbGenericInsert($row, "oldHoursVsTeams");
}

function dbUpdateOffset($HTID,$row)
{
  return dbUpdate("oldHoursVsTeams", $row, "HTID", $HTID);
}


/* ------------------------------------------------- NOTIFICATIONS --------------------------------------------- */

/* dbAddNotification() - adds a notification
 */
function dbAddNotification($row)
{ 
  return dbGenericInsert($row, "notifications");
}

/* dbDeleteNotification() - deletes a notification
 */
function dbDeleteNotification($NID)
{ 
  return dbRemoveEntry("notifications", 'NID', $NID);
}

/* dbDeleteNotifications() - deletes notifications for the given user
 */
function dbDeleteNotifications($UID, $limit = 0)
{ 
  $sql = "DELETE FROM notifications ";
  $sql .= "WHERE UID = :UID ";
  $sql .= "ORDER BY notifications.dateTargeted DESC ";
  if ($limit != 0){
        $sql .= "LIMIT :limit";
	$proxyFields[':limit'] = $limit;
  }

  $proxyFields = array(":UID" => $UID);
  $result = db_query($sql, $proxyFields);

  return ($result != false);
}

/* dbGetNotificationsForUser() - returns all the notifications for a particular user
 */
function dbGetNotificationsForUser($UID, $limit = null)
{
  $sql ="SELECT teams.FID, notifications.* FROM notifications ";
  $sql .= "LEFT JOIN teams on notifications.TID = teams.TID ";
  $sql .= "WHERE notifications.UID = :UID ";
  $sql .= "AND notifications.dateTargeted <= NOW() ";
  $sql .= "ORDER BY notifications.dateTargeted DESC ";

  if ($limit != null){
    $sql .= "LIMIT $limit";
  }

  $proxyFields = array(":UID" => $UID);

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

function dbGetNumNotificationsForUser($UID)
{
  $sql = 'SELECT COUNT(DISTINCT NID) FROM notifications WHERE UID = :UID';
  $proxyFields = array(":UID" => $UID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result) {
    return $result[0]['COUNT(DISTINCT NID)'];
  }

  return 0;
}

/* dbGetNotificationsForOutreach() - returns all the notifications for a particular outreach
 */
function dbGetNotificationsForOutreach($OID, $limit = null)
{
  $sql ="SELECT outreach.FID, notifications.* FROM notifications ";
  $sql .= "INNER JOIN outreach on outreach.OID = notifications.OID ";
  $sql .= "WHERE notifications.OID = :OID ";
  $sql .= "ORDER BY notifications.dateTargeted ";

  if ($limit != null){
    $sql .= "LIMIT $limit";
  }

  $proxyFields = array(":OID" => $OID);

  return (db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC));
}

/* ------------------------------------------------ ROLES ------------------------------------------------------ */

function dbUserHasPermissionForTeam($UID, $permission, $TID)
{
  $sql = "SELECT COUNT(*) FROM usersVsRoles ";
  $sql .= "INNER JOIN permissionsVsRoles ON usersVsRoles.RID = permissionsVsRoles.RID ";
  $sql .= "INNER JOIN permissions ON permissionsVsRoles.UPID = permissions.UPID ";
  $sql .= "WHERE permissions.name = :permission ";
  $sql .= "AND usersVsRoles.TID = :TID ";
  $sql .= "AND usersVsRoles.UID = :UID ";

  $proxyFields = array(":permission" => $permission, ':TID' => $TID, ':UID' => $UID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  return ($result[0]['COUNT(*)'] != 0);
}

/* dbAddRole() - adds a role
 */
function dbAddRole($row)
{ 
  return dbGenericInsert($row, "roles");
}

function dbGetRID($roleName)
{
  return dbSimpleSelect('roles', 'name', $roleName)[0]['RID'];
}

/* dbAddPermission() - adds a permission
 */
function dbAddPermission($row)
{ 
  return dbGenericInsert($row, "permissions");
}

/* dbAddPermissionToRole() - adds a permission to a role
 */
function dbAddPermissionToRole($UPID, $RID)
{ 
  return dbGenericInsert(array("UPID"=>$UPID, "RID"=>$RID), "permissionsVsRoles");
}

/* dbUpdateUserRole() - updates a user the selected role for the given team
 */
function dbUpdateUserRole($UID, $TID, $RID){
  return dbUpdate('usersVsRoles', array('RID'=>$RID), 'TID', $TID, 'UID', $UID);
}

/* dbGiveUserRole() - gives a user the selected role for the given team
 */
function dbGiveUserRole($UID, $TID, $roleName)
{ 
  $RID = dbSimpleSelect("roles", "name", $roleName)[0]['RID'];
  return dbGiveUserRID($UID, $TID, $RID);
}

/* dbGiveUserRID() - gives a user the selected role for the given team
 */
function dbGiveUserRID($UID, $TID, $RID){
  return dbGenericInsert(array("UID"=>$UID, "TID"=>$TID, 'RID'=>$RID), "usersVsRoles");
}

/* dbRemoveAllUserRoles() - removes all roles from the given user
 */
function dbRemoveAllUserRoles($UID, $TID)
{ 
  return dbRemoveEntries(array('UID'=>$UID, 'TID'=>$TID), 'usersVsRoles');
}

/* dbRemoveUserRole() - removes the given role from the user
 */
function dbRemoveUserRole($UID, $TID, $roleName)
{ 
  $RID = dbSimpleSelect("roles", "name", $roleName)['RID'];
  return dbRemoveEntries(array("UID"=>$UID, 'TID'=>$TID, "RID"=>$RID), "usersVsRoles");
}

function dbSelectTeamMembersByRole($TID, $role)
{
  $sql = "SELECT UID FROM usersVsRoles ";
  $sql .= "INNER JOIN roles ON usersVsRoles.RID = roles.RID ";
  $sql .= "WHERE roles.name = :role ";
  $sql .= "AND usersVsRoles.TID = :TID ";

  $proxyFields = array(":role" => $role, ':TID' => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  $retArr = array();
  foreach($result as $user){
    $retArr[] = $user['UID'];
  }

  return $retArr;
}

/* dbGetRIDForTeam() - returns the RID of the role of the user (given by UID) for the team given by TID.
 */
function dbGetRIDForTeam($UID, $TID)
{
  $sql = "SELECT roles.RID FROM roles ";
  $sql .= "INNER JOIN usersVsRoles ON usersVsRoles.RID = roles.RID ";
  $sql .= "WHERE usersVsRoles.UID = :UID ";
  $sql .= "AND usersVsRoles.TID = :TID ";
  $sql .= "LIMIT 1";

  $proxyFields = array(":UID" => $UID, ':TID' => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  if ($result){
    return $result[0]['RID'];
  }
  return false;
}

/* dbGetRoleForTeam() - returns the name of the role of the user (given by UID) for the team given by TID.
 */
function dbGetRoleForTeam($UID, $TID)
{
  $sql = "SELECT roles.displayName FROM roles ";
  $sql .= "INNER JOIN usersVsRoles ON usersVsRoles.RID = roles.RID ";
  $sql .= "WHERE usersVsRoles.UID = :UID ";
  $sql .= "AND usersVsRoles.TID = :TID ";
  $sql .= "LIMIT 1";

  $proxyFields = array(":UID" => $UID, ':TID' => $TID);
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);
  if ($result){
    return $result[0]['displayName'];
  }
  return false;
}

/* dbGetAllRoles() - gets all possible roles
 */
function dbGetAllRoles()
{
  $sql = "SELECT displayName, RID FROM roles;";

  $proxyFields = array();
  $result = db_query($sql, $proxyFields)->fetchAll(PDO::FETCH_ASSOC);

  if ($result){
    foreach($result as $row){
      $retArr[$row['RID']] = $row['displayName'];
    }
    return $retArr;
  }

  return false;
}

/* dbGetRoleName() - given the $RID, return the display name of a role
 */
function dbGetRoleName($RID)
{
  $result = dbSelect('roles', array('RID'=>$RID), '', '', 'displayName');
  if (!empty($result)){
    return $result[0]['displayName'];
  }
  return false;
}

/* ------------------------------------------------ OTHER ------------------------------------------------------ */

function dbFormatPhoneNumber($phone)
{
  $length = strlen($phone);
  $tempPhone = '';

  if ($length == 10){
    $tempPhone = "(" .substr($phone,0,3). ") " .substr($phone,3,3). "-" .substr($phone,6,4);
  } else if ($length == 7){
    $tempPhone = substr($phone,0,3). "-" .substr($phone,3,7);
  } else {
    $tempPhone = $phone;
  }
  
  return $tempPhone;
}

?>


