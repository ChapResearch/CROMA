<?php

/*

 ---- users/usersSearch.php ----

  - Contents-
  usersSearch() - used for searching of users on a given team
  usersSearchHeader() - used to create a header for users search
*/

// used for searching users on a given team
function usersSearch($form, &$form_state)
{
  global $user;
  
  if(isset($params['TID'])){
    $TID = $params['TID'];
    $team = dbGetTeam($TID);
  } else {
    $team = getCurrentTeam();
    $TID = $team['TID'];
  }

  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page.', 'error');
    drupal_goto('myDashboard');
  }

  $form = array();
  
  // displays what a user can search
  $form['fields']['nameContains'] = array(
    '#type' => 'textfield',
    '#placeholder' => 'Name or Email'
					  );

  // submit button
  $form['fields']['submit'] = array(
    '#prefix' => '<div align="left" style="float:left">',
    '#type' => 'submit',
    '#value' => t('Search'),
    '#suffix' => '</div>'
				    );

  // button to view all team members on current team
  $form['fields']['button'] = array(
    '#markup' => '<div align="right" style="float:right"><a href="?q=showUsersForTeam"><button type="button">View All Team Members</button></a></div>'
				    );

  return $form;
}

function usersSearch_validate($form, $form_state)
{
  if(empty($form_state['values']['nameContains'])) {
    form_set_error('nameContains', 'Must search for something.');
  }

  if(is_numeric($form_state['values']['nameContains'])) {
    form_set_error('nameContains', 'No numbers.');
  }

  if(strlen($form_state['values']['nameContains']) > 50) {
    form_set_error('nameContains', 'Query must be less than 50 characters.');
  }
}

function usersSearch_submit($form, $form_state)
{
  $names = array('nameContains');
  $row = getFields($names, $form_state['values']);
  $row = stripTags($row, '');
  drupal_goto('showUsersForTeam', array('query' => array('query' => $row['nameContains'])));
  return;
}

// used to create a header for users search
function usersSearchHeader()
{
  global $user;
  $params = drupal_get_query_parameters();

  if(isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  // create table and header
  $markup = '<table><tr><div class="help tooltip2"><h2>Team Members</h2><span id="helptext"; class="helptext tooltiptext2">Click here to search for a team member or manage your teams permissions.</tr></span></div><th>Search For A Team Member</th></table>';

  return $markup;
}

?>