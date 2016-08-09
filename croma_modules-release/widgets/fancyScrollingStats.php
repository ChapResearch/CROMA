<?php

/*
  ---- widgets/fancyScrollingStats.php ----

  returns JSON-encoded stats data for a team (when called by fancyScrollingStats.js)

*/

// quick check to determine if this is the release version
// normally this would be set through a define in Drupal
if ($_SERVER['SERVER_PORT'] == ':80'){
  $basePath = '/var/www-croma/database-release';
} else {
  $basePath = '/var/www-croma/database';
}

include($basePath.'/croma_dbFunctions.php');
include($basePath.'/drupalCompatibility.php');

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