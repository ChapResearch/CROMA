<?php

/*
  ---- teams/teamOutreachSettings.php ----

  used for the header on teamOutreachSettings

  - Contents -
  outreachSettingsHeader() - title of teamOutreachSettings
*/

function outreachSettingsHeader()
{
  $team = getCurrentTeam();
  $TID = $team['TID'];

  // if the team does not have permission to access team outreach settings
  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  // create header with team number
  $markup = "<h1>Team {$team['number']} Outreach Settings</h1>";

  return $markup;
}