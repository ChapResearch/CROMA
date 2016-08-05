<?php

/*

---- users/usersSearch.php ----

Used for searching of users on a given team.

*/

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
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto('myDashboard');
    return;
  }

  $form = array();
  
  /*  $form['fields'] = array(
    '#type' => 'fieldset',
    '#title' => t('Search for a Team Member')
			  );
  */

  $form['fields']['nameContains'] = array(
    '#type' => 'textfield',
    '#placeholder' => 'Name or Email'
					  );

  $form['fields']['submit'] = array(
    '#prefix' => '<div align="left" style="float:left">',
    '#type' => 'submit',
    '#value' => t('Search'),
    '#suffix' => '</div>'
				    );

  $form['fields']['button'] = array(
    '#markup' => '<div align="right" style="float:right"><a href="?q=showUsersForTeam"><button type="button">View All Team Members</button></a></div>'
				    );

  return $form;
}

function usersSearch_validate($form, $form_state)
{
  if(empty($form_state['values']['nameContains'])) {
    form_set_error('nameContains', 'Must search for something!');
  }

  if(is_numeric($form_state['values']['nameContains'])) {
    form_set_error('nameContains', 'No numbers!');
  }

  if(strlen($form_state['values']['nameContains']) > 50) {
    form_set_error('nameContains', 'Query must be less than 50 characters!');
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


function usersSearchHeader()
{
  global $user;
  $params = drupal_get_query_parameters();


  if(isset($params["UID"]))  {
    $UID = $params["UID"];
  } else {
    $UID = $user->uid;
  }

  $markup = '<table><tr><div class="help tooltip2"><h2>Team Members</h2><span id="helptext"; class="helptext tooltiptext2">Click here to search for a team member or manage your teams permissions.</tr></span></div><th>Search For A Team Member</th></table>';

  return $markup;
}

?>