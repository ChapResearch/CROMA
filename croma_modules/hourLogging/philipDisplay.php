<?php


/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying the logging of outreach. 
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/croma_modules/hourLogging/hourLogging.inc");
include_once("/var/www-croma/database/croma_dbFunctions.php");

// allows a user to add hours for an outreach, called from the outreach table in the database


function addHours()  
{  
  $form = array();

  $params = drupal_get_query_parameters();

  if (!isset($params['OID'])){
    drupal_set_message("No outreach selected!", 'error');
  } else {
    $OID = $params['OID'];

    $outreachData = dbGetOutreach($OID);

    $form['header']=array(
	  '#markup'=>"<h5>Logging hours for \"{$outreachData['name']}\" Outreach...</h5>"
	  );

    $form['fields']=array(
	  '#type'=>'fieldset',
	  '#title'=>t('Enter data for the hours put into the outreach'),
	  );

    $form['fields']['tableHeader']=array(
	  '#markup'=>'<table>'
	  );

    dpm($form_state);

    for($i = 0; $i < NUM_HOUR_ROWS; $i++){

      $rowMarkUp = "<tr id=\"row-$i\"";
      if ($i != 0 
	  && empty($form_state['values']["numberOfHours-$i"])
	  && empty($form_state['values']["type-$i"])
	  && empty($form_state['values']["description-$i"])
	  ){
	$rowMarkUp .= ' style="display:none"';
      }
      $rowMarkUp .= '>';
      $form['fields']["rowHeader-$i"]=array(
	    '#markup'=> $rowMarkUp
	    );

      $form['fields']["numberOfHours-$i"]=array(
            '#prefix'=>'<td style="text-align:left;width:25%">',
	    '#type'=>'textfield',
	    '#title'=>t('Number Of Hours:'),
	    '#suffix'=>'</td>'
	    );

      $form['fields']["type-$i"]=array(
	    '#prefix'=>'<td style="text-align:left;width;20%">',
	    '#type'=>'radios',
	    '#title'=>t('Type:'),
	    '#options'=>array('prep'=>'Preparation','atEvent'=>'At Event','writeUp'=>'Write-Up'),
	    '#suffix'=>'</td>'
	    );

      $form['fields']["description-$i"]=array(
	    '#prefix'=>'<td style="text-align:left;width:50%">',
	    '#type'=>'textarea',
	    '#title'=>t('Description:'),
	    '#suffix'=>'</td>'
	     );
      $bttnMarkUp = '<button type="button"';
      $bttnMarkUp .= ' class="add-row-bttn">+</button>';
      $form['fields']["addRowButton-$i"]=array(
	    '#prefix'=>'<td>',
	    '#markup'=>$bttnMarkUp,
	    '#suffix'=>'<td></tr>'
	    );
    }

    $form['fields']['tableFooter']=array(
	  '#markup'=>'</table>'
	  );


    $form['submit']=array(
	  '#type'=>'submit',
	  '#value'=>t('Submit')
	  );
  }
    
  return $form;

}

function addHours_validate($form, $form_state)
{
  if(empty($form_state['values']['numberOfHours']))
    form_set_error('numberOfHours','Number of hours cannot be empty');

  if(empty($form_state['values']['type']))
    form_set_error('type','Type cannot be empty');
}

function addHours_submit($form, $form_state)
{
  global $user;
  $params = drupal_get_query_parameters();
  $OID = $params["OID"];

  $fields = array("numberOfHours","description","type");
  $row = getFields($fields,$form_state['values']);
  $row["UID"] = $user->uid;
  $row["OID"] = $OID;

  $HID = dbLogHours($row);
  if ($HID != false){
    drupal_set_message("Your hours have been logged!");
    drupal_goto("viewOutreach", array('query'=>array("OID"=>$OID)));
  } else {
    drupal_set_message("Error");
  }
}

?>