<?php


/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");

function addHours()  
{  
  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Enter All Outreach Data Below'),
    );

    $form['fields']['name']=array(
        '#prefix'=>'<table><tr><td colspan="3" style="text-align:left;width:100%">',
        '#type'=>'textfield',
        '#title'=>t('Outreach Name:'),
	'#suffix'=>'</td></tr>'
     );

    $form['fields']['numberOfHours']=array(
        '#prefix'=>'<tr><td style="text-align:left;width:25%">',
        '#type'=>'textfield',
        '#title'=>t('Number Of Hours:'),
	'#suffix'=>'</td>'

     );

    $form['fields']['type']=array(
        '#prefix'=>'<td style="text-align:left;width;20%">',
        '#type'=>'radios',
        '#title'=>t('Type:'),
	'#options'=>array('prep'=>'Preparation','atEvent'=>'At Event','writeUp'=>'Write-Up'),
	'#suffix'=>'</td>'

     );

    $form['fields']['description']=array(
        '#prefix'=>'<td style="text-align:left;width:50%">',
        '#type'=>'textarea',
        '#title'=>t('Description:'),
	'#suffix'=>'</td></tr></table>'

     );

    $form['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit')
	);
    
    return $form;

}

function addHours_validate($form, $form_state)
{
  if(empty($form_state['values']['name']))
    form_set_error('name','Name cannot be empty');
  
  if(empty($form_state['values']['numberOfHours']))
    form_set_error('numberOfHours','Number of hours cannot be empty');

  if(empty($form_state['values']['type']))
    form_set_error('type','Type cannot be empty');
}

function addHours_submit($form, $form_state)
{
  $UID = 1;
  $OID = 1;
  dpm($form_state);
  $fields = array("numberOfHours","description","type");
  $row = getFields($fields,$form_state['values']);
  $row["UID"] = 1;
  $row["OID"] = 1;

  $HID = dbLogHours($row);
  if ($HID != false){
    drupal_set_message("Error");
  } else {
    drupal_set_message("Form has been submitted");
  }
}

?>