<?php
include('/var/www-croma/database/croma_dbFunctions.php');
include('/var/www-croma/database/drupalCompatibility.php');

if (isset($_POST['teamNumber'])){
  $teamNumber = $_POST['teamNumber'];
} else {
  echo 'errorTID';
  return;
}

$TID = dbGetTeamTIDByNumber($teamNumber);
$data['numHours'] = dbGetHoursForTeam($TID);
$data['teamName'] = dbGetTeamName($TID);
$data['numOutreaches'] = dbGetNumOutreachForTeam($TID);

$json = json_encode($data);
echo $json;

?>