<?php
include('/var/www-croma/database/croma_dbFunctions.php');
include('/var/www-croma/database/drupalCompatibility.php');

$data['numHours'] = dbGetTotalHours();
$data['numOutreaches'] = dbGetNumTotalOutreach();
$data['numTeams'] = dbGetNumTotalTeams();
$json = json_encode($data);
echo $json;
?>