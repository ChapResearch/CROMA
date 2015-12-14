<?php


ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/croma_modules/helperFunctions.inc");
include_once("/var/www-croma/database/croma_dbFunctions.php");

function addTeam()  
{  
  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Create A New Team'),
    );

    $form['fields']['name']=array(
        '#prefix'=>'<table><tr><td style="width: 70%">',
	/*'#default_value'=>$new
	  FINISH FOR EACH FORM
	 */
        '#type'=>'textfield',
        '#title'=>t('Team Name'),
	'#suffix'=>'</td>'
      );

    $form['fields']['number']=array(
       '#prefix'=>'<td style="width: 30%">',
       /*'#default_value'=>$new
	 FINISH
       */
       '#type'=>'textfield',
       '#title'=>t('Team Number'),
       '#suffix'=>'</td></tr>'
      );

    $form['fields']['type']=array(
        '#prefix'=>'<tr><td colspan="2">',
	/*#default_value'=>$new
	  FINISH
	*/
        '#type'=>'select',
        '#title'=>t('Type of Team'),
	'#options'=>array('FRC'=>'FIRST Robotics Competition','FTC'=>'FIRST Tech Challenge','FLL'=>'FIRST Lego League','Other'=>'Other'),
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['city']=array(
        '#prefix'=>'<tr><td colspan="2">',
	'#type'=>'textfield',
        '#title'=>t('City'),
	'#suffix'=>'</td></tr>'
      );
    
    $form['fields']['state']=array(
        '#prefix'=>'<tr><td style="width: 50%">',
        '#type'=>'select',
        '#title'=>t('State'),
        '#options'=>states_list(),
	'#default_value'=>'Texas',
	'#suffix'=>'</td>'
      );

    $form['fields']['country']=array(
        '#prefix'=>'<td style="width: 50%">',
	'#type'=>'select',
        '#title'=>t('Country'),
        '#options'=>countries_list(),
	'#default_value'=>'United States',
	'#suffix'=>'</td>'
      );

    $form['fields']['picture']=array(
        '#prefix'=>'<tr><td>',
        '#type'=>'file',
        '#title'=>t('Profile Picture'),
        '#upload_location' => 'public://',
        '#upload_validators' => array(
            'file_validate_extensions' => array('gif png jpg jpeg'),
            'file_validate_size' => array(500*1024)),         // 500k limit currently
	'#suffix'=>'</td></tr></table>'
      );

    $form['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit')
      );
    
    return $form;
}

function addTeam_validate($form, $form_state)
{
  if(empty($form_state['values']['name']))
     form_set_error('name','Name cannot be empty');

  if(!is_numeric($form_state['values']['number']))
     form_set_error('number','Team number invalid');

  if(empty($form_state['values']['type']))
     form_set_error('type','Type cannot be empty');

  if(empty($form_state['values']['city']))
     form_set_error('city','City cannot be empty');

  $allTeams = dbSelectAllTeams();
  dpm($allTeams);

  foreach($allTeams as $team) {
    if($team['number'] == $form_state['values']['number']) {
      form_set_error('number','Team already exists! Contact CROMA for assistance.');
    }

    if($team['name'] == $form_state['values']['name']) {
      form_set_error('name','Team already exists! Contact CROMA for assistance.');
    }
  }
}

function addTeam_submit($form, $form_state)
{
  dpm($form_state);

  $names = array('name', 'number', 'type', 'city', 'state', 'country');
		 
  $row = getFields($names, $form_state['values']);

  $OID = dbCreateTeam($row);

  if ($OID != false) {
    drupal_set_message('Submission successful!');
  } else {
    drupal_set_message('There is a problem. Please try again.');
  }

}

function viewTeam() {
  $TID = 1;
  //  $markup = '<table style="width: 30%">';
  $teams = dbSelectAllTeams();

  foreach($teams as $team) {
    if($team['TID'] == $TID) {
      //      $markup .= '<tr><td style="text-align: center"></td></tr></table>';
      $markup = '<table><tr><td style="width: 50%"><b>Name: </b>' . $team['name'] . '</td>';
      $markup .= '<td style="width: 50%"><b>Number: </b>' . $team['number'] . '</td></tr>';
      $markup .= '<tr><td style="width: 50%"><b>Type: </b>' . $team['type'] . '</td>';
      $markup .= '<td style="width: 50%"><b>Number of Members: </b>' . count(dbGetUsersFromTeam($team['TID'])) . '</td></tr>';
      $markup .= '<tr><td colspan="2"><b>City: </b>' . $team['city'] . '</td></tr>';
      $markup .= '<tr><td colspan="2"><b>State: </b>' . $team['state'] . '</td></tr>';
      $markup .= '<tr><td colspan="2"><b>Country: </b>' . $team['country'] . '</td></tr>';
      $markup .= '<tr><td style="width: 50%"><b>Number of Outreach Events: </b>' . count(dbGetOutreachesForTeam($team['TID'])) . '</td>';

      $outreaches = dbGetOutreachesForTeam($team['TID']);
      $hours = 0;

      foreach($outreaches as $outreach) {
	$hours += dbGetHoursForOutreach($outreach['OID']);
      }

      $markup .= '<td style="width: 50%"><b>Total Number of Hours: </b>' . $hours . '</td></tr>';
    }
  }

  $markup .= '</table>';

  $array = array();
  $array['#markup'] = $markup;
  return $array;
}

?>
  
  