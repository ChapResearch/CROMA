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
function toggleShowAdvanced($form, &$form_state) {
  if (isset($form_state['showAdvanced'])){
    $form_state['showAdvanced'] = !$form_state['showAdvanced'];
  } else {
    $form_state['showAdvanced'] = true;
  }
  $_SESSION['showAdvanced'] = $form_state['showAdvanced'];
  $form_state['rebuild'] = true;
}

// showAdvanced_callback() - returns all "advanced" search fields
function showAdvanced_callback($form, $form_state) {
  return $form['fields']['advanced'];
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

  dpm($_SESSION);

  // where the current instance of the form is located (e.g. allTeamOutreach)
  $form['fields']['source'] = array(
				    '#type' => 'hidden',
				    '#value' => isset($params['source']) ? $params['source'] : ''
				    );

  $form['fields']['markupOne']=array('#markup'=>'<div id="searchFormOnSidebar" style="padding:24px 0px 0px 0px"><h2>Search All Outreach</h2>');


  $form['fields']['name']=array(
				'#type'=>'textfield',
				'#title'=>t('Outreach Name:'),
				'#default_value'=> fillFromSession('name', array())
				);
 
  $form['fields']['status']=array(
				  '#type'=>'checkboxes',
				  '#title'=>t('Status:'),
				  '#options'=> array('isIdea'=>'Idea', 'isOutreach'=>'Outreach', 'doingWriteUp'=>'Write-Up', 'locked'=>'Locked'),
				  '#default_value'=> fillFromSession('status', array())
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
				   '#type'=>'checkboxes',
				   '#title'=>t('Team:'),
				   '#options'=> $teamOptions,
				   '#default_value'=> fillFromSession('teams', $defaultTeam)
				  );
  }

  // toggle advanced fields
  $form['fields']['showAdvancedBttn']=array(
					    '#type'=>'submit',
					    '#submit'=>array('toggleShowAdvanced'),
					    '#value'=>'Advanced',
					    '#limit_validation_errors' => array(),
					    '#ajax'=>array(
							   'callback'=>'showAdvanced_callback',
							   'wrapper'=>'advanced-div'
							   ),
					    );

  $form['fields']['advanced']=array(
				   '#prefix'=>'<div id="advanced-div">',
				   '#suffix'=>'</div>',
				    );
  
  // if user would like to see advanced fields, show them
  if (isset($form_state['showAdvanced']) && $form_state['showAdvanced']){
  //  if (isset($_SESSION['showAdvanced']) && $_SESSION['showAdvanced']){
    
    $userTIDs = dbGetTIDsforUser($user->uid);
    $tags = array();
    foreach ($userTIDs as $TID){
      $teamTags = dbGetOutreachTagsForTeam($TID); 
      if (!empty($teamTags)){
	$tags = array_replace($tags, $teamTags);
      }
    }

    if (!empty($tags)){
      $form['fields']['advanced']['tags']=array(
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

    $form['fields']['advanced']['country']=array(
				   '#type'=>'select',
				   '#title'=>t('Country:'),
				   '#options'=>countries_list(),
				   '#default_value'=>fillFromSession('country'),
				   '#chosen'=>true,
				   );

    $form['fields']['advanced']['state']=array(
				   '#type'=>'select',
				   '#title'=>t('State:'),
				   '#options'=>states_list(),
				   '#default_value'=>fillFromSession('state'),
				   '#chosen'=>true,
				   );
    
    $form['fields']['advanced']['city']=array(
						 '#type'=>'textfield',
						 '#title'=>t('City:'),
						 '#default_value'=>fillFromSession('city'),
						 );

    $form['fields']['advanced']['co_organization']=array(
					   '#type'=>'textfield',
					   '#title'=>t("Host Organization:"),
					   '#default_value'=>fillFromSession('co_organization'),
					   ); 

    $form['fields']['advanced']['cancelled']=array(
						   '#type'=>'checkbox',
						   '#title'=>t("Cancelled"),
						   '#default_value'=>fillFromSession('cancelled'),
						   );

    $form['fields']['advanced']['timeConstraints'] = array(
					       '#markup' => '<table style="table-layout:fixed"><tr><th colspan="4">Select a Time Constraint</th></tr>'
					       );

    $form['fields']['advanced']['within5Years']=array(
							'#prefix'=>'<tr style="white-space:nowrap"><td style="padding:0px;display:inline-block">Within 5 Years</td><td style="padding:5px;display:inline-block">',
							'#type'=>'checkbox',
							'#default_value'=>fillFromSession('within5Years'),
							'#suffix'=>'</td></tr>',
						       );

    $form['fields']['advanced']['dateSelection3']=array(
						       '#markup'=>'<tr style="white-space:nowrap"><td style="padding:5px; display:inline-block">Within</td><td style="padding:5px; display:inline-block">',
						       );


    // search outreaches within some time of specified date
    $distanceOptions = array('1 day'=>'1 day', '1 week'=>'1 week', '1 month'=>'1 month', '1 year'=>'1 year');
    $form['fields']['advanced']['dateDistance']=array(
						      '#type'=>'select',
						      '#options'=>$distanceOptions,
						      '#default_value'=>fillFromSession('dateDistance'),
						      //						      '#attributes'=>array('width'=>'100px'),
						      '#chosen'=>true,
						      );


    $form['fields']['advanced']['dateSelection4']=array(
						       '#markup'=>'</td><td style="padding:5px; display:inline-block">of</td><td style="padding:5px;display:inline-block; width:100px">',
						       );

    // specify date of outreach for use with dateDistance
    $form['fields']['advanced']['date'] = array(
						'#type' => 'date_popup', 
						'#date_format' => SHORT_TIME_FORMAT,
						'#date_label_position' => 'within', 
						'#date_increment' => 1,
						'#date_year_range' => '-20:+20',
						'#datepicker_options' => array(),
						//						'#attributes'=>array('width'=>'200px'),
						'#default_value'=>isset($_SESSION['searchParams']['date']['center'])?$_SESSION['searchParams']['date']['center']:''
						);

    $form['fields']['advanced']['dateSelection5']=array(
						       '#markup'=>'</td></tr><tr style="white-space:nowrap"><td style="padding:0px; display:inline-block">Events In Year</td><td style="display:inline-block">'
						       );
    $years = array('select' => '-Select-');
    
    for ($i = date("Y"); $i >= 1992; $i--) {
      $years[(string)$i] = $i;
    }

    $form['fields']['advanced']['year']=array(
					      '#type'=>'select',
					      '#options'=>$years,
					      '#chosen'=>true,
					      '#default_value'=>fillFromSession('year'),
					      );

    $form['fields']['advanced']['dateSelection6'] = array(
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
    form_set_error('', 'You need to fill out at least one field!');
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

  // queryMsg accumulates filled-in search fields to display as string when search exits
  // searchParams is an array containing search data to be passed into generateSearchSQL()
  $queryMsg = '<u>Search Query</u><br>';
  if (!empty($form_state['values']['status'])) {
    $queryMsg .= 'Status: ';
    foreach($form_state['values']['status'] as $status => $value){
      if ($value != '0'){
	$searchParams['status'][] = $value;
	$queryMsg .= $value . ', ';
      }
    }
  }
  if (!empty($form_state['values']['teams'])) {
    $queryMsg .= 'Team(s): ';
    foreach($form_state['values']['teams'] as $team => $value){
      if ($value != '0'){
	$searchParams['teams'][] = $value;
	$queryMsg .= dbGetTeamName($value) . ', ';
      }
    }
  }
  if (!empty($form_state['values']['tags'])) {
    $queryMsg .= 'Tag(s): ';
    foreach($form_state['values']['tags'] as $tag => $value){
      if ($value != '0'){
	$searchParams['tags'][] = $value;
	$queryMsg .= dbGetTagName($value) . ', ';
      }
    }
  }
  if (!empty($searchData['name'])){
    $queryMsg .= 'Name: ';
    $searchParams['name'] = array('value'=>$searchData['name'], 'matchType'=>'fuzzy');
    $queryMsg .= $searchData['name'] . '. ';
  }
  if (!empty($searchData['country']) && $searchData['country'] != '[none]'){
    $queryMsg .= 'Country: ';
    $searchParams['country'] = array('value'=>$searchData['country'], 'matchType'=>'exact');
    $queryMsg .= $searchData['country'] . '. ';
  }
  if (!empty($searchData['state']) && $searchData['state'] != '[none]'){
    $queryMsg .= 'State: ';
    $searchParams['state'] = array('value'=>$searchData['state'], 'matchType'=>'exact');
    $queryMsg .= $searchData['state'] . '. ';
  }
  if (!empty($searchData['city'])){
    $queryMsg .= 'City: ';
    $searchParams['city'] = array('value'=>$searchData['city'], 'matchType'=>'fuzzy');
    $queryMsg .= $searchData['city'] . '. ';
  }
  if (!empty($searchData['co_organization'])){
    $queryMsg .= 'Organization: ';
    $searchParams['co_organization'] = array('value'=>$searchData['co_organization'], 'matchType'=>'fuzzy');
    $queryMsg .= $searchData['co_organization'] . '. ';
  }
  if (!empty($searchData['cancelled'])){
    $searchParams['cancelled'] = array('value'=>$searchData['cancelled'], 'matchType'=>'exact');
  } else {
    $searchParams['cancelled'] = array('value'=>false, 'matchType'=>'exact');
  }
  if (!empty($searchData['within5Years'])){
    $queryMsg .= 'Within 5 Years. ';
    $searchParams['within5Years'] = true;
  }
  if (!empty($searchData['date'])){
    $queryMsg .= 'Start Date: ';
    $date = strtotime($searchData['date']);
    $start = strtotime('-'.$searchData['dateDistance'], $date);
    $queryMsg .= date(SHORT_TIME_FORMAT, $start) . '; End Date: ';
    $end = strtotime('+'.$searchData['dateDistance'], $date);
    $queryMsg .= date(SHORT_TIME_FORMAT, $end) . '. ';
    $searchParams['date'] = array('start'=>dbDatePHP2SQL($start), 'center'=>date(DEFAULT_TIME_FORMAT, $date), 'end'=>dbDatePHP2SQL($end));
  }
  if (!empty($searchData['year']) && $searchData['year'] != 'select') {
    $queryMsg .= 'Year: ';
    $searchParams['year'] = array('year' => $searchData['year']);
    $queryMsg .= $searchData['year'] . '. ';
  }
  $proxyFields = array();
  //  drupal_set_message($queryMsg);

  // what page is the search query coming from
  if ($searchData['source'] == 'allTeamOutreach') {
    $searchParams['TID'] = array('value'=>getCurrentTeam()['TID'], 'matchType'=>'exact');
    $_SESSION['searchSQL'] = generateSearchSQL($searchParams, $proxyFields);
    $_SESSION['proxyFields'] = $proxyFields;
    drupal_goto('allTeamOutreach', array('query'=>array('query'=>'search', 'source'=>'allTeamOutreach')));
  } else { // default to searching user outreach
    $_SESSION['searchParams'] = $searchParams;
    $_SESSION['showAdvanced'] = isset($form_state['showAdvanced'])?$form_state['showAdvanced']: false;
    $_SESSION['searchSQL'] = generateSearchSQL($searchParams, $proxyFields);
    $_SESSION['proxyFields'] = $proxyFields;
    drupal_goto('outreach', array('query'=>array('query'=>'search')));
  }
  $form_state['test'] = $form_state['values'];
}

?>