<?php

include_once("/var/www-croma/croma_modules/helperFunctions.inc");

function outreachForm()
{
  $params = drupal_get_query_parameters();

  $new = true;

  if(isset($params["OID"])){
    $data = dbGetOutreach($params["OID"]);
    $new = false;
  }

  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Enter outreach data below'),
        '#description'=>t('These are all madatory')
    );

    $form['fields']['name']=array(
        '#prefix'=>'<table><tr><td colspan="3" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('Outreach Name'),
	'#default_value'=>$new?'':$data['name'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['peopleImpacted']=array(
        '#prefix'=>'<tr><td>',
        '#type'=>'textfield',
        '#title'=>t('Number of People Impacted:'),
	'#default_value'=>$new?NULL:$data['peopleImpacted'],
	'#suffix'=>'</td>'
      );

    $form['fields']['TID']=array(
       '#prefix'=>'<td>',
       '#type'=>'textfield',
       '#title'=>t('TID:'),
       '#default_value'=>$new?'':$data['TID'],
       '#suffix'=>'</td>'
      );

    $form['fields']['peopleReached']=array(
        '#prefix'=>'<td>',
        '#type'=>'textfield',
        '#title'=>t('Number of People Reached:'),
	'#default_value'=>$new?NULL:$data['peopleReached'],
	'#suffix'=>'</td>'
      );

       $form['fields']['startTime']=array(
        '#prefix'=>'<tr><td>',
        '#type'=>'date',
        '#title'=>t('Start Time:'),
	'#default_value'=>$new?'':$data['startTime'],
	'#suffix'=>'</td>'
      );

   $form['fields']['endTime']=array(
        '#prefix'=>'<td>',
        '#type'=>'date',
        '#title'=>t('End Time:'),
	'#default_value'=>$new?'':$data['endTime'],
	'#suffix'=>'</td>'
      );

    $form['fields']['description']=array(
        '#prefix'=>'<tr><td colspan="3">',
        '#type'=>'textarea',
        '#title'=>t('Description:'),
	'#default_value'=>$new?'':$data['description'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['address']=array(
        '#prefix'=>'<tr><td>',
	'#type'=>'textfield',
        '#title'=>t('Address:'),
	'#default_value'=>$new?'':$data['address'],
	'#suffix'=>'</td>'
      );

    $form['fields']['state']=array(
        '#prefix'=>'<td>',
        '#type'=>'select',
        '#title'=>t('State:'),
        '#options'=>states_list(),
	'#default_value'=>$new?'':$data['state'],
	'#suffix'=>'</td>'
      );

    $form['fields']['country']=array(
        '#prefix'=>'<td>',
	'#type'=>'select',
        '#title'=>t('Country:'),
        '#options'=>countries_list(),
	'#default_value'=>'United States',
	'#default_value'=>$new?'':$data['country'],
	'#suffix'=>'</td>'
      );

    $form['fields']['type']=array(
        '#prefix'=>'<tr><td>',
        '#type'=>'textfield',
        '#title'=>t('Type:'),
	'#default_value'=>$new?'':$data['type'],
	'#suffix'=>'</td>'
      );

    $form['fields']['status']=array(
        '#prefix'=>'<td>',
        '#type'=>'radios',
	'#default_value'=>'isOutreach',
	'#options'=> array('isIdea'=>'Idea', 'isOutreach'=>'Outreach', 'doingWriteUp'=>'Write-Up', 'locked'=>'Locked'),
        '#title'=>t('Status:'),
	'#default_value'=>$new?'':$data['status'],
	'#suffix'=>'</td></tr>'
      );

    $form['fields']['cancelled']=array(
       '#prefix'=>'<tr><td>',
       '#title'=>t('Cancel'),
       '#type'=>'checkbox',
       '#default_value'=>$new?'':$data['cancelled'],
       '#suffix'=>'</td></table>'
      );

    $form['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit')
	);
    
    return $form;
}

function outreachForm_validate($form, $form_state)
{
  if(empty($form_state['values']['name'])){
     form_set_error('name','Name cannot be empty');
  }
}

function outreachForm_submit($form, $form_state)
{
  $UID = 1;

  $params = drupal_get_query_parameters();

  $fields = array("name", "peopleImpacted", "TID", "peopleReached", "description", "address", "state", "country", "type", "status", "cancelled");
  $outreachData = getFields($fields, $form_state['values']);

  if (isset($params["OID"])){ // updating existing event
    $result = dbUpdateOutreach($params["OID"], $outreachData);
    if ($result){
      drupal_set_message("Outreach updated!!!");
    } else {
      drupal_set_message("Outreach not updated!");
    }
    // TODO - update times
  } else { // adding new event
    $outreachData['logDate'] = time();
    $OID = dbCreateOutreach($outreachData);
    
    if ($OID != false){
      dbAddUserAsOwnerOfOutreach($UID, $OID);
      $timeFields = array("startTime", "endTime");
      $timeData = getFields($timeFields, $form_state['values']);
      $timeData['OID'] = $OID;
      dbCreateTimesVsOutreach($timeData);
      drupal_set_message("Outreach created!");
    } else {
      form_set_error("Outreach not created successfully");
    }
  }
}

?>