<?php

/* dbGetTeamHours() - returns the total number of hours students have logged on the team. For example, if 3 students attended an event and put in 4 hours each, the total is 12 hours.
 */
function dbGetTeamHours($TID)
{
  $con = dbConnect();
  if($con == null) {
    return(null);
  }

  $sql = "SELECT hourCounting.numberOfHours, usersVsTeams.TID FROM hourCounting ";
  $sql .= "INNER JOIN usersVsTeams ON hourCounting.UID = usersVsTeams.UID ";
  $sql .= "WHERE usersVsTeams.TID = $TID";

  $result = mysqli_query($con,$sql);
  if (!$result) {
    dbErrorMsg("Error during sql select in dbSelect($table)" . mysqli_error($con));
    return false; 
  }

  $total = 0;
  while ($row = mysqli_fetch_assoc($result)){
    $total += $row["numberOfHours"];
  }

  dbClose($con);

  return $total;
}
?>