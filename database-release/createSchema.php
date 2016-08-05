<?php
  //
  // createSchema.php
  //
  //	This file can be used from a blank start to create the schema for
  //	the entire database.  The only things that have to be configured are
  //	the host, username, and password.
  //
  //	NOTE that this file is run by itself, and doesn't make use of any
  //	other functions in this directory - just the configuration.
  //
  //    GOOD INFORMATION FOR TESTS:
  //    ---------------------------
  //    - to start from scratch, "drop" the croma database
  //            login to mysql    --->    $ mysql -u croma -p <RETURN>
  //            drop the database --->      drop database cromaTest; <RETURN>
  //            leave             --->      quit; <RETURN>
  //    - to look at what you've done
  //            login to mysql    --->    $ mysql -u croma -p <RETURN>
  //            change to croma   --->      use cromaTest; <RETURN>
  //            lists tables      --->      show tables; <RETURN>
  //            list table schema --->      describe users; <RETURN>  (or whatever table name instead)
  //            

include_once("/var/www-croma/database/config.php");
include_once("/var/www-croma/database/allTables.php");

//
// execute() - a little function for convenience of running sql statements.
//		It takes the $statement, the $connection, and a $prompt that
//		is used to tell the user what went wrong, or right.
//
function execute($connection,$statement,$prompt)
{
     if (mysqli_query($connection,$statement)) {
	  echo "$prompt successful\n";
	  return(true);
     } else {
	  echo "Error during $prompt: " . mysqli_error($connection) . "\n";
	  return(false);
     }
}

function createSchema()
{
     // we want to know when we use variables that aren't defined

     error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

     // First, create a connection to the database with the given config information

     $con = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD);
     if (mysqli_connect_errno()) {
	  echo "Failed to connect to MySQL: " . mysqli_connect_error() . "\n";
	  return;
     }

     // set database for use for table creation

     $sql="USE " . DB_DATABASE;
     if(!mysqli_query($con,$sql)) {
       dbErrorMsg("Error selecting datbase". DB_DATABASE . mysqli_error($con));
       return;
     }

     db_createOutreachTable($con);
     db_createProfilesTable($con);
     db_createTeamsTable($con);
     db_createUsersVsTeamsTable($con);
     db_createEmailsVsUsersTable($con);
     db_createMediaTable($con);
     db_createHourCountingTable($con);
     db_createTimesVsOutreachTable($con);
     db_createUsersVsOutreachTable($con);
     db_createNotificationsTable($con);
     db_createOutreachTagsTable($con);
     db_createTagsVsOutreachTable($con);
     db_createPermissionsTable($con);
     db_createPermissionsVsRolesTable($con);
     db_createUsersVsRolesTable($con);
     db_createRolesTable($con);
     db_createOldHoursVsTeamsTable($con);
     // finally close the connection
     mysqli_close($con);
}

createSchema();
?>