<?php

/*
  ---- users/userStats.php ----

  used for viewing user statistics.

  - Contents -
  viewUserStats() - displays statistics for a user including number of hours logged and events created.
*/

function viewUserStats()
{
  global $user;
  $params = drupal_get_query_parameters();

  if (isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }
  
  $currentTeam = getCurrentTeam();
  $TID = $currentTeam['TID'];
  $teamNumber = $currentTeam['number'];

  $numOfOutreachesForUser = dbGetNumOutreachForUser($UID);

  $markup = "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.js\"></script>";
  $markup .= '<script src="numberCounting.js"></script>';
  $form['script']=array('#markup'=> $markup);

  // create page header and table
  $form['mainHeader']=array('#markup'=>'<h1>My Dashboard</h1>');
  $form['table']=array('#markup'=>'<table><tr><td>');

  // displays users total stats

  $markup = '<table id="myTotalStats"><tr><td colspan="2" style="text-align:center">';
  $markup .= '<div class="help tooltip2">';

  $markup .= '<h2><b>My Total Stats</b></h2>';
  $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
  $markup .= 'These are your total numbers of hours and outreaches.';
  $markup .= '</span></div>';

  $markup .= '</td></tr>';
  $markup .= '<tr><td style="text-align:center"><a href="?q=viewHours&UID=' . $UID. '"><b>HOURS</b></a></td>';
  $markup .= '<td style="text-align:center"><a href="?q=outreach"><b>OUTREACHES</b></a></td></tr>';
  $markup .= '<tr style="font-size:48pt; font-family: "Open Sans", sans-serif;"><td style="text-align:center"><b class="countUp">' . dbGetUserHours($UID) . '</b></td>';
  $markup .= '<td style="text-align:center"><b class="countUp">' .   $numOfOutreachesForUser;
  $markup .= '</b></td></tr></table></td>';

  $form['myStatsTable']=array('#markup'=>$markup)
;

  // if user has more than one team, displays stats for user on current team

  if (count(dbGetTeamsForUser($UID)) > 1){

    // dropdown allows user to switch teams
    $form['TID']=array(
		       '#prefix'=> '<td><table id="myStatsOnTeamNumber"><tr><td id ="myStatsOnMultTeams1" style="text-align:right; padding:0px"><div class="help tooltip2"><h2><b>My Stats On</b></h2><span id="helptext"; class="helptext tooltiptext2">These are your total numbers of hours and outreaches for your currently active team.</span></div></td><td id="myStatsOnMultTeams2"> ',
		       '#type'=>'select',
		       '#attributes' => array('onChange' => 'document.getElementById("viewuserstats").submit();'),
		       '#chosen'=>true,
		       '#options'=>dbGetTeamsListForUser($UID),
		       '#default_value'=>$TID,
		       '#suffix'=>'</td></tr>'
		       );

    $markup = '<tr><td style="text-align:center"><a href="?q=userHoursForTeam&UID='. $UID .'&TID='. $TID .'"><b>HOURS</b></a></td>';
    $markup .= '<td style="text-align:center"><a href="?q=userEventsForTeam&UID='. $UID .'&TID='. $TID .'"><b>OUTREACHES</b></a></td></tr>';
    $markup .= '<tr style="font-size:48pt; font-family:"Open Sans", sans-serif;"><td style="text-align:center"><b class="countUp">'. dbGetUserHoursForTeam($UID,$TID) .'</b></td>';
    $markup .= '<td style="text-align:center"><b class="countUp">'. dbGetNumOutreachesForUserForTeam($UID,$TID) .'</b></td></tr></table></td></tr></table>';

    $form['teamStatsTable']=array('#markup'=>$markup);

    $form['submit'] = array(
			    '#type' => 'submit',
			    '#value' => 'Update',
			    // the submit itself is necessary, but now it can be hidden
			    '#attributes' => array(
						   'style' => array('display: none;'),
						   ),
			    );
  } else {    // if user does not have more than one team, displays what team the user is on
    $markup = '<td><table id="myStatsOnTeamNumber"><tr><td colspan="2" style="text-align:center">';
    $markup .= '<div class="help tooltip2">';
    $markup .= '<h2>My Team</h2>';
    $markup .= '<span id="helptext"; class="helptext tooltiptext2">';
    $markup .= 'This is the team number and CROMA permission for your user.';
    $markup .= '</span></div>';
    $markup .= '</td></tr>';
    $markup .= '<tr style="font-size:48pt; font-family:"Open Sans", sans-serif;"><td style="text-align:center"><b>' . $currentTeam['number'] . '</b></td></tr>';
    $role = dbGetRoleForTeam($UID, $TID)== ''? 'Team Member': dbGetRoleForTeam($UID, $TID);
    $markup .= '<tr><td style="text-align:center"><b>CROMA Role: </b>' . $role . '</td></tr>';
    $markup .= '</table></td></tr></table>';
    $form['TID'] = array('#markup'=>$markup);
  }
  return $form;

}

function viewUserStats_submit($form, &$form_state)
{
  setCurrentTeam($form_state['values']['TID']);
  drupal_set_message('Team changed!');
  drupal_goto('myDashboard');
}