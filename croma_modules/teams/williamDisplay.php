<?php

/*

---- teams/williamDisplay.php ----

Used for display and creation/editing of teams.

- Contents -
teamForm() - Displays the form used to create/edit a team.
viewTeam() - Displays the information for the team with which the user is currently associated.

*/

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/croma_modules/helperFunctions.inc");
include_once("/var/www-croma/database/croma_dbFunctions.php");

// teamForm() - Displays the form used to create/edit a team.

function teamForm()  
{
  $params = drupal_get_query_parameters();
  $new = true;

  if(isset($params["TID"])){
    $oldTeam = dbGetTeam($params["TID"]);
    $new = false;
  }
  
  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Create A New Team'),
    );

    $form['fields']['name']=array(
        '#prefix'=>'<table><tr><td colspan="6" style="text-align:center">',
	'#default_value'=>$new ? '' : $oldTeam['name'],
	'#type'=>'textfield',
        '#title'=>t('Team Name'),
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['number']=array(
       '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
       '#default_value'=>$new ? '' : $oldTeam['number'],
       '#type'=>'textfield',
       '#title'=>t('Team Number'),
       '#suffix'=>'</td>'
      );

    $form['fields']['type']=array(
        '#prefix'=>'<td colspan="3" style="text-align:center">',
	'#default_value'=>$new ? '' : $oldTeam['type'],
        '#type'=>'select',
        '#title'=>t('Type of Team'),
	'#options'=>array('FRC'=>'FIRST Robotics Competition','FTC'=>'FIRST Technology Challenge','FLL'=>'FIRST Lego League','Other'=>'Other'),
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['city']=array(
        '#prefix'=>'<tr><td colspan="2" style="text-align:center">',
	'#default_value'=>$new ? '' : $oldTeam['city'],
	'#type'=>'textfield',
        '#title'=>t('City'),
	'#suffix'=>'</td>'
      );
    
    $form['fields']['state']=array(
        '#prefix'=>'<td colspan="2" style="text-align:center">',
	'#default_value'=>$new ? '' : $oldTeam['state'],
        '#type'=>'select',
        '#title'=>t('State'),
        '#options'=>states_list(),
	'#default_value'=>'Other',
	'#suffix'=>'</td>'
      );

    $form['fields']['country']=array(
        '#prefix'=>'<td colspan="2" style="text-align:center">',
	'#default_value'=>$new ? '' : $oldTeam['country'],
	'#type'=>'select',
        '#title'=>t('Country'),
        '#options'=>countries_list(),
	'#default_value'=>'United States',
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['picture']=array(
	 '#markup'=>'<tr><td colspan="6" style="text-align:center">'
     );

    $form['fields']['FID']=array(
        '#type'=>'managed_file',
        '#title'=>t('Team Logo'),
        '#upload_location' => 'public://',
        '#upload_validators' => array(
            'file_validate_extensions' => array('gif png jpg jpeg'),
            'file_validate_size' => array(50000*1024)),         // 500k limit currently
	'#default_value'=>$new?'':$oldTeam['FID'],	
	);


   $form['fields']['footer']=array(
   '#markup'=>'</td></tr></table>');

   $form['fields']['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit')
      );
    
    return $form;
}

// teamForm_validate() - Validates the teamForm.

function teamForm_validate($form, $form_state)
{
  $params = drupal_get_query_parameters();

  if(empty($form_state['values']['name']))
     form_set_error('name','Name cannot be empty');

  if(!is_numeric($form_state['values']['number']))
     form_set_error('number','Team number invalid');

  if(empty($form_state['values']['type']))
     form_set_error('type','Type cannot be empty');

  if(empty($form_state['values']['city']))
     form_set_error('city','City cannot be empty');

  if (!isset($params['TID'])){ // creating new team
    $allTeams = dbSelectAllTeams();

    foreach($allTeams as $team) { // check that the team doesn't exist
      if($team['number'] == $form_state['values']['number']) {
	form_set_error('number','Team already exists! Contact CROMA for assistance.');
      }

      if($team['name'] == $form_state['values']['name']) {
	form_set_error('name','Team already exists! Contact CROMA for assistance.');
      }
    }
  }
}

// teamForm_submit() - Submits the teamForm.

function teamForm_submit($form, $form_state)
{
  $params = drupal_get_query_parameters();

  $new = !isset($params['TID']); // determine if adding or editing

  $names = array('name', 'number', 'type', 'city', 'state', 'country', 'FID');
  $row = getFields($names, $form_state['values']);

  if($row['FID'] != null) { // TODO - should delete old picture
    $f = file_load($form_state['values']['FID']);
    $f->status = FILE_STATUS_PERMANENT;
    file_save($f);
    file_usage_add($f, 'CROMA - teams', 'pictures', $f->fid); // tells Drupal we're using the file
  }

  if($new) { // team doesn't exist
    $TID = dbCreateTeam($row);
  } else {
    $TID = dbUpdateTeam($params['TID'],$row);
  }

  if($TID != false) {
    if($new) {
      drupal_set_message('Your team has been created!');
      drupal_goto('profileForm'); // TODO - fix for other cases
    } else {
      drupal_set_message('Your team has been updated!');
      drupal_goto('viewTeam', array('query'=>array('TID'=>$params['TID'])));
    }
  } else {
    drupal_set_message('There is a problem. Please try again.');
  }

}

// viewTeam() - Displays the information for the team with which the user is currently associated.

function viewTeam() {
  global $user;
  $UID = $user->uid;

  $params = drupal_get_query_parameters();
  $array = array();
  $markup = '';
  
  if(isset($params['TID'])) {
    $TID = $params['TID'];
    $team = dbGetTeam($TID);

    if($team['FID'] = !null){
      $teamPic = file_load($team['FID']);
      $markup .= '<img src="' . file_create_url($teamPic->uri) . '" style="width:75px; height:100px;"><td>';
    }

    $markup .= '<div align="right"><a href= "http://croma.chapresearch.com/?q=teamForm';
    $markup .= '&TID=' . $team['TID'] . '">';
    $markup .= '<button type="button">Edit</button></a></div>';
    $markup .= '<table><tr><td style="width: 50%"><b>Name: </b>' . $team['name'] . '</td>';
    $markup .= '<td style="width: 50%"><b>Number: </b>' . $team['number'] . '</td></tr>';
    $markup .= '<tr><td style="width: 50%"><b>Type: </b>' . $team['type'] . '</td>';
    $markup .= '<td style="width: 50%"><b>Number of Members: </b>' . dbGetNumPplForTeam($team['TID']);
    $markup .= '<span style="float:right"><a href="http://croma.chapresearch.com/?q=showUsersForTeam';
    $markup .= '&TID=' . $team['TID'] . '">View All Members</a></span></td></tr>';
    $markup .= '<tr><td colspan="2"><b>City: </b>' . $team['city'] . '</td></tr>';
    $markup .= '<tr><td colspan="2"><b>State: </b>' . $team['state'] . '</td></tr>';
    $markup .= '<tr><td colspan="2"><b>Country: </b>' . $team['country'] . '</td></tr>';
    $markup .= "<tr><td style=\"width: 50%\"><b><a href=\"?q=viewTeamOutreach&TID=$TID\">Outreach Events: </a></b>";
    $markup .= dbGetNumOutreachForTeam($team['TID']);
    $markup .= '</td>';
    $markup .= '<td style="width: 50%"><b>Total Number of Hours: </b>' . dbGetHoursForTeam($TID) . '</td></tr>';
    $markup .= '</table>';
  } else {
    $teams = dbGetTeamsForUser($UID);

    foreach($teams as $team) {
      $markup .= '<a href="http://croma.chapresearch.com/?q=viewTeam&TID=';
      $markup .= $team['TID'];
      $markup .= '">' . $team['name'] . '</a><br>';
    }
  }

  $array['#markup'] = $markup;
  return $array;
}

?>
