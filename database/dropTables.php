<?php

include_once("/var/www-croma/database/config.php");
include_once("/var/www-croma/database/allTables.php");

function dropTables()
{

  $con = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD);
  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
    return;
  }

  $sql="USE " . DB_DATABASE;
  if(!mysqli_query($con,$sql)) {
    echo("Error selecting datbase". DB_DATABASE . mysqli_error($con));
    return;
  }

  $tables = array("profiles", "teams", "usersVsTeams", "emailsVsUsers", "outreach", "media", "hourCounting", "timesVsOutreach", "usersVsOutreach", "notifications");

  foreach($tables as $table){
    $sql = "DROP TABLE $table;";
    if(!mysqli_query($con,$sql)) {
      echo("Error dropping $table: " . mysqli_error($con) . "\n");
      return;
    }
  }

}

dropTables();

?>