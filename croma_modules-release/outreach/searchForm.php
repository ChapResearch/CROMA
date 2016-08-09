<?php

/*
  ---- searchForm.php ----
  Display code for the outreach search block.
  Uses search "algorithm" in dbSearchOutreach() of croma_dbFunctions.php to generate search results.

  - Contents -
  toggleShowAdvanced() - allows display of "advanced" search fields
  showAdvanced_callback() - returns all "advanced" search fields
  searchFormSidebar() - displays search block in sidebar of "outreach list" pages 
*/

// toggleShowAdvanced() - allows display of "advanced" search fields
function toggleShowTypeSection($form, &$form_state) {
  /*  if (isset($form_state['showAdvanced'])){
    $form_state['showAdvanced'] = !$form_state['showAdvanced'];
  } else {
    $form_state['showAdvanced'] = true;
  }
  $_SESSION['showAdvanced'] = $form_state['showAdvanced'];
  */
  if (isset($_SESSION['showTypeSection'])){
    $_SESSION['showTypeSection'] = !$_SESSION['showTypeSection'];
  } else {
    $_SESSION['showTypeSection'] = true;
  }
  $form_state['rebuild'] = true;
}

// showAdvanced_callback() - returns all "advanced" search fields
function showTypeSection_callback($form, $form_state) {
  return $form['fields']['type'];
}

function toggleShowPeopleSection($form, &$form_state) {
  if (isset($_SESSION['showPeopleSection'])){
    $_SESSION['showPeopleSection'] = !$_SESSION['showPeopleSection'];
  } else {
    $_SESSION['showPeopleSection'] = true;
  }
  $form_state['rebuild'] = true;
}

// showAdvanced_callback() - returns all "advanced" search fields
function showPeopleSection_callback($form, $form_state) {
  return $form['fields']['people'];
}

function toggleShowLocationSection($form, &$form_state) {
  if (isset($_SESSION['showLocationSection'])){
    $_SESSION['showLocationSection'] = !$_SESSION['showLocationSection'];
  } else {
    $_SESSION['showLocationSection'] = true;
  }
  $form_state['rebuild'] = true;
}

// showAdvanced_callback() - returns all "advanced" search fields
function showLocationSection_callback($form, $form_state) {
  return $form['fields']['location'];
}

function toggleShowTimeSection($form, &$form_state) {
  if (isset($_SESSION['showTimeSection'])){
    $_SESSION['showTimeSection'] = !$_SESSION['showTimeSection'];
  } else {
    $_SESSION['showTimeSection'] = true;
  }
  $form_state['rebuild'] = true;
}

// showAdvanced_callback() - returns all "advanced" search fields
function showTimeSection_callback($form, $form_state) {
  return $form['fields']['time'];
}

// searchFormSidebar() - displays search block in sidebar of "outreach list" pages 
function searchFormSidebar($form, &$form_state)
{
  global $user;
  $params = drupal_get_query_parameters();

  $form_state['TID'] = getCurrentTeam()['TID'];

  if (isset($_SESSION['showAdvanced'])){
    $form_state['showAdvanced'] = $_SESSION['showAdvanced'];
  } else {
    $form_state['showAdvanced'] = $_SESSION['showAdvanced'] = true;
  }

  // where the current instance of the form is located (e.g. allTeamOutreach)
  $form['fields']['source'] = array(
				    '#type' => 'hidden',
				    '#value' => isset($params['source']) ? $params['source'] : ''
				    );

  $form['fields']['markupOne']=array('#markup'=>'<div id="searchFormOnSidebar" style="padding:24px 0px 0px 0px"><h2>Search All Outreach</h2>');


  $form['fields']['nameHeader']=array(
				      '#markup'=>'<table><tr><td style="padding:0px 5px 0px 0px"><b>Name:</b></td><td style="padding:0px">'
				      );

  $form['fields']['name']=array(
				'#type'=>'textfield',
				'#default_value'=> fillFromSession('name', array()),
				);

  $form['fields']['nameFooter']=array('#markup'=>'</td></tr></table>');
 
  $form['fields']['statusHeader']=array(
					//				  '#markup'=>'<table><tr><td>',
				  );

  $form['fields']['status']=array('#type'=>'checkboxes',
				  '#prefix'=>'<table><tr><td style="vertical-align:top">',
				  /*
				  '#type'=>'select',
				  '#chosen'=> true,
				  '#multiple'=> true,
				  '#attributes'=>array('style'=>'width:200px'),*/
				  '#title'=>'<b>Status:</b>',
				  '#options'=> array('isIdea'=>'Idea', 'isOutreach'=>'Outreach', 'doingWriteUp'=>'Write-Up', 'locked'=>'Locked'),
				  '#default_value'=> fillFromSession('status', array()),
				  '#suffix'=>'</td>'
				  );

  $teams = dbGetTeamsForUser($user->uid);

  // if user is on multiple teams, allow him to select teams for searching
  if (!empty($teams)) {
    $teamOptions = array();
    
    foreach($teams as $team) {
      $teamOptions[$team['TID']] = $team['number'];
    }

    $defaultTeam = array(getCurrentTeam()['TID']);
    $form['fields']['teams']=array(
				   '#prefix'=>'<td style="vertical-align:top">',
				   '#type'=>'checkboxes',
				   '#title'=>'<b>Team:</b>',
				   '#options'=> $teamOptions,
				   '#default_value'=> fillFromSession('teams', $defaultTeam),
				   '#suffix'=>'</td></tr></table>',
				  );
  }

  $form['fields']['typeBttn']=array(
				      '#type'=>'submit',
				      '#submit'=>array('toggleShowTypeSection'),
				      '#value'=>'Type',
				      '#limit_validation_errors' => array(),
				      '#ajax'=>array(
						     'callback'=>'showTypeSection_callback',
						     'wrapper'=>'type-div'
						     ),
				      );
  
  $form['fields']['type']=array(
				'#prefix'=>'<div id="type-div">',
				'#suffix'=>'</div>'
				);

  // merge various data from all the teams of the given user  
  $userTIDs = dbGetTIDsforUser($user->uid);
  $tags = array();
  $allUsers = array();
  foreach ($userTIDs as $TID){
    $teamTags = dbGetOutreachTagsForTeam($TID); 
    $users = dbGetUsersListFromTeam($TID);
    if (!empty($teamTags)){
      $tags = array_replace($tags, $teamTags);
    }
    if (!empty($users)){
      $allUsers = array_replace($users, $allUsers);
    }
  }

  // toggle display of the "Type" section
  if (isset($_SESSION['showTypeSection']) && $_SESSION['showTypeSection']){

    if (!empty($tags)){
      $form['fields']['type']['tags']=array(
					    '#prefix'=>'<tr><td colspan="3" style="text-align:center">',
					    '#type'=>'select',
					    '#title'=>t('Tags:'),
					    '#attributes'=>array('style'=>'width:200px'),
					    '#chosen'=>true,
					    '#multiple'=>true,
					    '#options'=>$tags,
					    '#default_value'=>fillFromSession('tags', array()),
					    '#suffix'=>'</td>'
					    );
    }

    $form['fields']['type']['cancelled']=array(
					       '#type'=>'checkbox',
					       '#title'=>t("Cancelled"),
					       '#default_value'=>fillFromSession('cancelled'),
					       );

  }

  $form['fields']['peopleBttn']=array(
				      '#type'=>'submit',
				      '#submit'=>array('toggleShowPeopleSection'),
				      '#value'=>'People',
				      '#limit_validation_errors' => array(),
				      '#ajax'=>array(
						     'callback'=>'showPeopleSection_callback',
						     'wrapper'=>'people-div'
						     ),
				      );

  $form['fields']['people']=array(
				  '#prefix'=>'<div id="people-div">',
				  '#suffix'=>'</div>'
				  );

  if (isset($_SESSION['showPeopleSection']) && $_SESSION['showPeopleSection']){

    $form['fields']['people']['ownerLabel']=array(
						  '#prefix'=>'<table style="margin:0px"><tr><td style="padding:0px">',
						  '#markup'=>'<b>Owner:</b>',
						  '#suffix'=>'</td>'
						  );

    $form['fields']['people']['owner']=array(
					     '#prefix'=>'<td style="padding:0px">',
					     '#type'=>'select',
					     '#options'=>$allUsers,
					     '#default_value'=>fillFromSession('owner'),
					     '#attributes'=>array('style'=>'width:200px'),
					     '#chosen'=>true,
					     '#multiple'=>true,
					     '#suffix'=>'</td></tr></table>',
					     );

    $form['fields']['people']['signedUpLabel']=array(
						     '#prefix'=>'<table style="margin:0px"><tr><td style="padding:0px">',
						     '#markup'=>'<b>Signed Up:</b>',
						     '#suffix'=>'</td>'
						     );

    $form['fields']['people']['signedUp']=array(
						'#prefix'=>'<td style="padding:0px">',
						'#type'=>'select',
						'#options'=>$allUsers,
						'#default_value'=>fillFromSession('signedUp'),
						'#attributes'=>array('style'=>'width:200px'),
						'#chosen'=>true,
						'#multiple'=>true,
						'#suffix'=>'</td></tr></table>',
						);

    $form['fields']['people']['co_organizationLabel']=array(
							    '#prefix'=>'<table style="margin:0px"><tr><td style="padding:0px">',
							    '#markup'=>'<b>Organization:</b>',
							    '#suffix'=>'</td>'
							    );

    $form['fields']['people']['co_organization']=array(
						       '#prefix'=>'<td style="padding:0px">',
						       '#type'=>'textfield',
						       '#default_value'=>fillFromSession('co_organization'),
						       '#size'=>20,
						       '#suffix'=>'</td></tr></table>',
						       ); 
  }

  $form['fields']['locationBttn']=array(
					'#type'=>'submit',
					'#submit'=>array('toggleShowLocationSection'),
					'#value'=>'Location',
					'#limit_validation_errors' => array(),
					'#ajax'=>array(
						       'callback'=>'showLocationSection_callback',
						       'wrapper'=>'location-div'
						       ),
					);

  $form['fields']['location']=array(
				    '#prefix'=>'<div id="location-div">',
				    '#suffix'=>'</div>'
				    );

  if (isset($_SESSION['showLocationSection']) && $_SESSION['showLocationSection']){
    

    $form['fields']['location']['cityLabel']=array(
						   '#prefix'=>'<table style="margin:0px"><tr><td style="padding:0px">',
						   '#markup'=>'<b>City:</b>',
						   '#suffix'=>'</td>'
						   );

    $form['fields']['location']['city']=array(
					      '#prefix'=>'<td style="padding:0px">',
					      '#type'=>'textfield',
					      '#size'=>25,
					      '#default_value'=>fillFromSession('city'),
					      '#suffix'=>'</td></tr></table>',
					      );

    $form['fields']['location']['stateLabel']=array(
						    '#prefix'=>'<table style="margin:0px"><tr><td style="padding:0px">',
						    '#markup'=>'<b>State:</b>',
						    '#suffix'=>'</td>'
						    );

    $form['fields']['location']['state']=array(
					       '#prefix'=>'<td style="padding:0px">',
					       '#type'=>'select',
					       '#options'=>states_list(),
					       '#default_value'=>fillFromSession('state'),
					       '#chosen'=>true,
					       '#suffix'=>'</td></tr></table>',
					       );

    $form['fields']['location']['countryLabel']=array(
						      '#prefix'=>'<table style="margin:0px"><tr><td style="padding:0px">',
						      '#markup'=>'<b>Country:</b>',
						      '#suffix'=>'</td>'
						      );

    $form['fields']['location']['country']=array(
						 '#prefix'=>'<td style="padding:0px">',
						 '#type'=>'select',
						 '#options'=>countries_list(),
						 '#default_value'=>fillFromSession('country'),
						 '#attributes'=>array('width'=>'200px'),
						 '#chosen'=>true,
						 '#suffix'=>'</td></tr></table>',
						 );

  }  
  // if user would like to see advanced fields, show them
//  if (isset($form_state['showAdvanced']) && $form_state['showAdvanced']){
  //  if (isset($_SESSION['showAdvanced']) && $_SESSION['showAdvanced']){


    $form['fields']['timeBttn']=array(
					'#type'=>'submit',
					'#submit'=>array('toggleShowTimeSection'),
					'#value'=>'Time',
					'#limit_validation_errors' => array(),
					'#ajax'=>array(
						       'callback'=>'showTimeSection_callback',
						       'wrapper'=>'time-div'
						       ),
					);


    $form['fields']['time']=array(
				  '#prefix'=>'<div id="time-div">',
				  '#suffix'=>'</div>'
				  );

    if (isset($_SESSION['showTimeSection']) && $_SESSION['showTimeSection']){

      $form['fields']['time']['timeConstraints'] = array(
							 '#markup' => '<table style="table-layout:fixed"><tr></tr>'
							 );

      $form['fields']['time']['within5Years']=array(
						    '#prefix'=>'<tr style="white-space:nowrap"><td style="padding:0px;display:inline-block">Within 5 Years</td><td style="padding:5px;display:inline-block">',
						    '#type'=>'checkbox',
						    '#default_value'=>fillFromSession('within5Years'),
						    '#suffix'=>'</td></tr>',
						    );

      $form['fields']['time']['dateSelection3']=array(
						      '#markup'=>'<tr style="white-space:nowrap"><td style="padding:5px; display:inline-block">Within</td><td style="padding:5px; display:inline-block">',
						      );


      // search outreaches within some time of specified date
      $distanceOptions = array('1 day'=>'1 day', '1 week'=>'1 week', '1 month'=>'1 month', '1 year'=>'1 year');
      $form['fields']['time']['dateDistance']=array(
						    '#type'=>'select',
						    '#options'=>$distanceOptions,
						    '#default_value'=>fillFromSession('dateDistance'),
						    //						      '#attributes'=>array('width'=>'100px'),
						    '#chosen'=>true,
						    );


      $form['fields']['time']['dateSelection4']=array(
						      '#markup'=>'</td><td style="padding:5px; display:inline-block">of</td><td style="padding:5px;display:inline-block; width:100px">',
						      );

      // specify date of outreach for use with dateDistance
      $form['fields']['time']['date'] = array(
					      '#type' => 'date_popup', 
					      '#date_format' => SHORT_TIME_FORMAT,
					      '#date_label_position' => 'within', 
					      '#date_increment' => 1,
					      '#date_year_range' => '-20:+20',
					      '#datepicker_options' => array(),
					      //						'#attributes'=>array('width'=>'200px'),
					      '#default_value'=>isset($_SESSION['searchParams']['date']['center'])?$_SESSION['searchParams']['date']['center']:''
					      );

      $form['fields']['time']['dateSelection5']=array(
						      '#markup'=>'</td></tr><tr style="white-space:nowrap"><td style="padding:0px; display:inline-block">Events In Year</td><td style="display:inline-block">'
						      );
      $years = array('select' => '-Select-');
    
      for ($i = date("Y"); $i >= 1992; $i--) {
	$years[(string)$i] = $i;
      }

      $form['fields']['time']['year']=array(
					    '#type'=>'select',
					    '#options'=>$years,
					    '#chosen'=>true,
					    '#default_value'=>fillFromSession('year'),
					    );

      $form['fields']['time']['dateSelection6'] = array(
							'#markup' => '</td></tr></table>'
							);
    }

  $form['fields']['submit']=array(
				  //'#prefix'=>'<div align="right" style="text-align:right">',
				  '#type'=>'submit',
				  '#value'=>'Search',
				  //'#suffix'=>'</div>'
			);

  return $form;
}

// searchFormSidebar_validate() - ensures submitted form contains valid input
function searchFormSidebar_validate($form, $form_state)
{
  $allEmpty = true;

  // ensure that there is at least one field filled in
  while(true){
    if (!empty($form_state['values']['name'])){ // check the name
      $allEmpty = false;
      break;
    }
    foreach($form_state['values']['status'] as $status){ //check statuses
      if ($status != '0'){
	$allEmpty = false;
	break 2;
      }
    }
    foreach($form_state['values']['teams'] as $team){ //check teams
      if ($team != '0'){
	$allEmpty = false;
	break 2;
      }
    }
    if (isset($form_state['showAdvanced'])){
      if ($form_state['values']['country'] != '[none]' || $form_state['values']['state'] != '[none]'
	 || !empty($form_state['values']['city']) || !empty($form_state['co_organization'])
	 || !empty($form_state['values']['within5Years']) || !empty($form_state['values']['date']) 
	 || !empty($form_state['values']['year']) || !empty($form_state['values']['tags'])){
	$allEmpty = false;
	break;
      }
    }
    break;
  }

  if ($allEmpty){
    form_set_error('', 'You need to fill out at least one field.');
  }

  // ensure there are <2 time constraints selected
  $timeConstraints = 0;
  $timeConstraints += !empty($form_state['values']['within5Years']) ? 1 : 0;
  $timeConstraints += isset($form_state['values']['date']) ? 1 : 0;
  $timeConstraints += isset($form_state['values']['year']) && $form_state['values']['year'] != 'select' ? 1 : 0;

  if ($timeConstraints > 1){
    form_set_error('', 'Please pick at most one time constraint.');
  }
}

// searchFormSidebar_submit() - updates the outreach list based on search input
function searchFormSidebar_submit($form, &$form_state)
{
  $fields = array('source', 'name', 'country', 'state', 'city', 'co_organization', 'date', 'dateDistance', 'within5Years', 'year', 'cancelled');
  $searchData = getFields($fields, $form_state['values']);
  $searchData = stripTags($searchData, ''); // don't allow any tags

  // searchParams is an array containing search data to be passed into generateSearchSQL()
  if (!empty($form_state['values']['status'])) {
    foreach($form_state['values']['status'] as $status => $value){
      if ($value != '0'){
	$searchParams['status'][] = $value;
      }
    }
  }
  if (!empty($form_state['values']['teams'])) {
    foreach($form_state['values']['teams'] as $team => $value){
      if ($value != '0'){
	$searchParams['teams'][] = $value;
      }
    }
  }
  if (!empty($form_state['values']['tags'])) {
    foreach($form_state['values']['tags'] as $tag => $value){
      if ($value != '0'){
	$searchParams['tags'][] = $value;
      }
    }
  }

  if (!empty($searchData['name'])){
    $searchParams['name'] = array('value'=>$searchData['name'], 'matchType'=>'fuzzy');
  }
  if (!empty($form_state['values']['owner'])){
    $searchParams['owner'] = array('value'=>$form_state['values']['owner'], 'matchType'=>'exact');
  }
  if (!empty($form_state['values']['signedUp'])){
    $searchParams['signedUp'] = array('value'=>$form_state['values']['signedUp'], 'matchType'=>'exact');
  }
  if (!empty($searchData['country']) && $searchData['country'] != '[none]'){
    $searchParams['country'] = array('value'=>$searchData['country'], 'matchType'=>'exact');
  }
  if (!empty($searchData['state']) && $searchData['state'] != '[none]'){
    $searchParams['state'] = array('value'=>$searchData['state'], 'matchType'=>'exact');
  }
  if (!empty($searchData['city'])){
    $searchParams['city'] = array('value'=>$searchData['city'], 'matchType'=>'fuzzy');
  }
  if (!empty($searchData['co_organization'])){
    $searchParams['co_organization'] = array('value'=>$searchData['co_organization'], 'matchType'=>'fuzzy');
  }
  if (!empty($searchData['cancelled'])){
    $searchParams['cancelled'] = array('value'=>$searchData['cancelled'], 'matchType'=>'exact');
  } else {
    $searchParams['cancelled'] = array('value'=>false, 'matchType'=>'exact');
  }
  if (!empty($searchData['within5Years'])){
    $searchParams['within5Years'] = true;
  }
  if (!empty($searchData['date'])){
    $date = strtotime($searchData['date']);
    $start = strtotime('-'.$searchData['dateDistance'], $date);
    $end = strtotime('+'.$searchData['dateDistance'], $date);
    $searchParams['date'] = array('start'=>dbDatePHP2SQL($start), 'center'=>date(DEFAULT_TIME_FORMAT, $date), 'end'=>dbDatePHP2SQL($end));
  }
  if (!empty($searchData['year']) && $searchData['year'] != 'select') {
    $searchParams['year'] = array('year' => $searchData['year']);
  }
  $proxyFields = array();

  $_SESSION['searchParams'] = $searchParams;
  $_SESSION['proxyFields'] = $proxyFields;
  drupal_goto('outreach', array('query'=>array('query'=>'search')));
}

?>