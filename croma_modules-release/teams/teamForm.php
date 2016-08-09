<?php

/*
  ---- teams/teamForm.php ----
  used for display and creation/editing of teams

  - Contents -
  teamForm() - Displays the form used to create/edit a team.
  viewTeam() - Displays the information for the team with which the user is currently associated.
*/

include_once(MODULES_FOLDER."/pictures/pictureFunctions.php");

// teamForm() - Displays the form used to create/edit a team.

function teamForm($form, &$form_state)  
{
  global $user;
  $UID = $user->uid; 
 
  $params = drupal_get_query_parameters();
  $new = true;

  if (isset($params["TID"])){ // getting the team ID if it's set in the URL's parameters
    $oldTeam = dbGetTeam($params["TID"]);
    $new = false;
  }
  
  $form = array();


  if ($new){ // if user is creating a completely new team
    $form['fields']=array(
			  '#type'=>'fieldset',
			  '#title'=>t('Create A New Team'),
			  );
  } else { // if user is editing a team
    $form['fields']=array(
			  '#type'=>'fieldset',
			  '#title'=>t('Edit: ' .  $oldTeam['name']),
			  );
  }
  
  if (!$new){ // if user wants to cancel any changes they made
    $form['fields']['back']=array(
				  '#prefix'=>'<left>',
				  '#limit_validation_errors' => array(),
				  '#submit'=>array('backToTeam'),
				  '#type' => 'submit',
				  '#value' => '⇦ Cancel Changes',
				  '#attributes' => array(
							 'OnSubmit' =>'if(!confirm("Back?")){return false;}'),
				  '#suffix'=>'</left>'
				  );
  }

  // setting the team name
  $form['fields']['name']=array( 
				'#prefix'=>'<table><tr><td colspan="3" style="text-align:center">',
				'#default_value'=>$new ? '' : $oldTeam['name'],
				'#type'=>'textfield',
				'#disabled'=> $new ? false : true,
				'#title'=>t('Team Name'),
				'#suffix'=>'</td>'
				);

  // setting the team number
  $form['fields']['number']=array(
				    '#prefix'=>'<td colspan="3" style="text-align:center">',
				    '#default_value'=>$new ? '' : $oldTeam['number'],
				    '#type'=>'textfield',
				    '#disabled'=> $new ? false : true,
				    '#title'=>t('Team Number'),
				    '#suffix'=>'</td></tr>'
				    );

  // setting the type of robotics team (FLL vs FTC vs FRC etc.)
  $form['fields']['type']=array( 
				'#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				'#default_value'=>$new ? '' : $oldTeam['type'],
				'#type'=>'select',
				'#title'=>t('Type of Team'),
				'#options'=>array('FRC'=>'FIRST Robotics Competition','FTC'=>'FIRST Technology Challenge','FLL'=>'FIRST Lego League','Other'=>'Other'),
				'#chosen'=>true,
				'#suffix'=>'</td>'
				);

  // setting the home city
  $form['fields']['city']=array(
				'#prefix'=>'<td colspan="3" style="text-align:center">',
				'#default_value'=>$new ? '' : $oldTeam['city'],
				'#type'=>'textfield',
				'#title'=>t('City'),
				'#suffix'=>'</td></tr>'
				);

  // setting the home state
  $form['fields']['state']=array( 
				 '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
				 '#default_value'=>$new ? '' : $oldTeam['state'],
				 '#type'=>'select',
				 '#title'=>t('State'),
				 '#options'=>states_list(),
				 '#chosen'=>true,
				 '#suffix'=>'</td>'
				 );

  // setting the home country of the team
  $form['fields']['country']=array( 
				   '#prefix'=>'<td colspan="3" style="text-align:center">',
				   '#default_value'=>$new ? '' : $oldTeam['country'],
				   '#type'=>'select',
				   '#title'=>t('Country'),
				   '#options'=>countries_list(),
				   '#default_value'=>'United States',
				   '#chosen'=>true,
				   '#suffix'=>'</td></tr>'
				   );

  // rookie year of team
  $form['fields']['rookieYear'] = array(
					'#prefix'=>'<tr><td colspan="6" style="text-align:center">',
					'#type'=>'textfield',
					'#title'=>t('Rookie Year'),
					'#default_value'=>$new?NULL:$oldTeam['rookieYear'],
					'#suffix'=>'</td></tr><tr>'
					);

    if (!$new){
      // if the team is not new and you want to delete permanently, then you can do it via this button
      $form['fields']['delete']=array( 
				    '#prefix'=>'<td colspan="3" style="text-align:left">',
				    '#type'=>'submit',
				    '#value'=>'Delete Team',
				    '#limit_validation_errors' => array(),
				    '#attributes' => array('onclick' => 'if(!confirm("Are you sure you want to delete this team PERMANENTLY?")){return false;}'),
				    '#submit'=>array('deleteTeam'),
				    '#suffix'=>'</td>'
				    );

      // submitting the info which the user just updated
      $form['fields']['submit']=array( 
				  '#prefix'=>'<td colspan="3" style="text-align:right">',				  
				  '#type'=>'submit',
				  '#value'=>t('Save'),
				  '#suffix'=>'</td>'
				  );
    } else {
  
      // submitting the info which the user just inputted (with a different colspan)
      $form['fields']['submit']=array( 
				  '#prefix'=>'<td colspan="6" style="text-align:right">',				  
				  '#type'=>'submit',
				  '#value'=>t('Save'),
				  '#suffix'=>'</td>'
				  );
    }

    $form['fields']['footer']=array(
				  '#markup'=>'</tr></table>');

    // checking proper permissions for user
    if (!$new && !hasPermissionForTeam('editTeam', $params['TID'])){ 
      drupal_set_message("You don't have permission to edit {$oldTeam['name']}.", 'error');
      drupal_goto('viewTeam', array('query'=>array('TID'=>$params['TID'])));
    }
    return $form;
}

// teamForm_validate() - validates the teamForm.

function teamForm_validate($form, $form_state)
{
  $params = drupal_get_query_parameters();
  $currentYear = date("Y");

  // name
  if(empty($form_state['values']['name'])){
    form_set_error('name','Name cannot be empty.');
  }
  if(!empty($form_state['values']['name'])) {
    if(strlen($form_state['values']['name'])>50){
      form_set_error('name',"The name must be fewer than 50 characters.");
    }
  }

  // number
  if(!is_numeric($form_state['values']['number'])){
    form_set_error('number','Team number must only contain numbers.');
  }
  if(!empty($form_state['values']['number'])) {
    if(strlen($form_state['values']['number'])>10){
      form_set_error('number',"The number must be fewer than 10 characters.");
    }
  }
  if(empty($form_state['values']['number'])) {
    form_set_error('number',"Number cannot be empty.");
  }

  // type
  if(empty($form_state['values']['type'])){
    form_set_error('type','Type cannot be empty.');
  }

  // city
  if(empty($form_state['values']['city'])){
    form_set_error('city','City cannot be empty.');
  }
  if(!empty($form_state['values']['city'])) {
    if(strlen($form_state['values']['city'])>20){
      form_set_error('city',"The city  must be fewer than 20 characters.");
    }
  }

  // rookie year
  if(!is_numeric($form_state['values']['rookieYear']) && !empty($form_state['values']['rookieYear'])){
    form_set_error('rookieYear','Rookie year field must be a number.');
  }
  if(is_numeric($form_state['values']['rookieYear']) && !empty($form_state['values']['rookieYear']) && $form_state['values']['rookieYear']<1980){
    form_set_error('rookieYear','Please enter a valid year after 1980.');
  }
  if(is_numeric($form_state['values']['rookieYear']) && !empty($form_state['values']['rookieYear']) && $form_state['values']['rookieYear']>$currentYear){
    form_set_error('rookieYear','You can not enter an year after ' . $currentYear . '.');
  }

  // misc validations
  if (!isset($params['TID'])){ // creating new team
    $allTeams = dbSelectAllTeams();

    foreach($allTeams as $team) { // check that the team doesn't exist
      if($team['number'] == $form_state['values']['number']) {
	form_set_error('number','Team already exists! Contact CROMA staff for assistance.');
      }

      if($team['name'] == $form_state['values']['name']) { //if team exists...
	form_set_error('name','Team already exists! Contact CROMA staff for assistance.');
      }
    }
  }
}

// teamForm_submit() - submits the teamForm.
function teamForm_submit($form, $form_state)
{
  global $user;
  $params = drupal_get_query_parameters();

  $new = !isset($params['TID']); // determine if adding or editing

  $names = array('name', 'number', 'type', 'city', 'state', 'country', 'FID', 'rookieYear');
  $row = getFields($names, $form_state['values']);
  $row = stripTags($row, '');

  if($row['rookieYear'] === ''){
    $row['rookieYear'] = null;
  }

  if($new) { // team doesn't exist yet
    $row['UID'] = $user->uid;
    $TID = dbCreateTeam($row);
  } else {
    $result = dbUpdateTeam($params['TID'],$row);
    if ($result){
      $TID = $params['TID'];
      if(!teamIsIneligible($TID)){
	setCurrentTeam($params['TID'], $row['name']);
      }
    } else {
      drupal_set_message('Error in updating team', 'error');
      return;
    }
  }

  if($TID != false) {
    if($new) { // if team is submitted correctly
      drupal_set_message('Your team form has been submitted. The CROMA team will contact you when your team has been successfully created.');
      dbGiveUserRole($user->uid, $TID, 'teamOwner');
      dbAssignUserToTeam($user->uid, $TID);

      // send email
      $params = array('TID' => $TID, 'name' => $row['name'], 'number' => $row['number'], 'user' => $user->uid, 'userName' => dbGetUserName($user->uid)); 
      drupal_mail('teams', 'teamCreated', 'croma@chapresearch.com', variable_get('language_default'), $params, $from = NULL, $send = TRUE);

      drupal_goto('teamDashboard');
    } else {
      drupal_set_message('Your team has been updated!');
      if(!dbIsTeamApproved($TID)){
	drupal_goto('manageUserTeams');
      }else{
	drupal_goto('viewTeam', array('query'=>array('TID'=>$params['TID'])));
      }
    }
  } else { // if something went wrong...
    drupal_set_message('Error creating team. Please try again.');
  }
}

function backToTeam()
{
  $params = drupal_get_query_parameters();
  $TID = $params['TID'];

  drupal_goto("viewTeam", array('query'=>array('TID'=>$TID)));
}

?>
