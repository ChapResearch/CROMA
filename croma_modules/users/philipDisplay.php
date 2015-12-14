<?php


/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

include_once("/var/www-croma/croma_modules/blockSupport.php");
include_once("/var/www-croma/database/croma_dbFunctions.php");

function addUser()  
{  
  $form = array();

    $form['fields']=array(
        '#type'=>'fieldset',
        '#title'=>t('Enter user information  below'),
    );

    $form['fields']['firstName']=array(
        '#prefix'=>'<table><tr><td colspan="2" style="text-align:center">',
        '#type'=>'textfield',
        '#title'=>t('First Name'),
	'#suffix'=>'</td>');

    $form['fields']['lastName']=array(
	 '#prefix'=>'<td colspan="2" style="text-align:center">',
	 '#type'=>'textfield',
	 '#title'=>t('Last Name'),
        '#suffix'=>'</td></tr>');

    $form['fields']['bio']=array(
        '#prefix'=>'<tr><td colspan="4" style="text-align:center>',
        '#type'=>'textarea',
        '#title'=>t('A short bio of yourself'),
	'#suffix'=>'</td></tr>');

    $form['fields']['position']=array(
        '#prefix'=>'<tr><td colspan="1" style="text-align:center">',
	'#type'=>'textfield',
        '#title'=>t('Position On The Team'),
	'#suffix'=>'</td>');

    $form['fields']['phone']=array(
      '#prefix'=>'<td colspan="1" style="text-align:center">',
      '#type'=>'textfield',
      '#title'=>t('Your Phone Number'),
      '#suffix'=>'</td>');


    $form['fields']['email']=array(
     '#prefix'=>'<td colspan="2" style="text-align:center">',
     '#type'=>'textfield',
     '#title'=>t('Enter Your Primary Email'),
     '#suffix'=>'</td></tr>');


    $form['fields']['grade']=array(
       '#prefix'=>'<tr><td colspan="1" style="text-align:center">',
       '#type'=>'select',
       '#title'=>t('Grade'),
       '#options'=>array('1'=>'1st','2'=>'2nd', '3'=>'3rd','4'=>'4th','5'=>'5th','6'=>'6th','7'=>'7th','8'=>'8th','9'=>'9th','10'=>'10th','11'=>'11th','12'=>'12th'),
       '#suffix'=>'</td>');

    $form['fields']['gender']=array(
        '#prefix'=>'<td colspan="1" style="text-align:center">',
        '#type'=>'radios',
	'#options'=> array('M'=>'Male', 'F'=>'Female', 'O'=>'Other'),
        '#title'=>t('Gender'),
	'#suffix'=>'</td>');

    $teams = dbSelectAllTeams();
    $names = array();
    foreach($teams as $team)
      {
	$names[$team["TID"]] = $team["name"];
      }

   $form['fields']['TID']=array(
  '#prefix'=>'<td colspan="2" style="text-align:center">',
  '#type'=>'select',
  '#title'=>t('Team'),
  '#options'=>$names
   );

   $form['text']=array(
   '#markup'=>"Don't see your team? Create it <a target=\"blank\" href=\"http://croma.chapresearch.com/?q=addTeam\">here!</a>",
   '#suffix'=>'</td></tr><tr><td colspan="3" style="text-align:center">',
    );

   $form['fields']['FID']=array(
  '#title'=>t('Picture'),
  '#type'=>'managed_file',
  '#upload_location' => 'public://',
  '#upload_validators' => array(
	    'file_validate_extensions' => array('gif png jpg jpeg'),
            'file_validate_size' => array(50000*1024),         // 500k limit currently
   ),
  '#size' => 48,
   );

   $form['footer']=array('#markup'=>'</td></tr></table>');

   $form['submit']=array(
        '#type'=>'submit',
        '#value'=>t('Submit'));

    
    return $form;

}


function addUser_validate($form, $form_state)
{
  if(empty($form_state['values']['firstName']))
     form_set_error('firstName','First name cannot be empty');

  if(empty($form_state['values']['lastName']))
    form_set_error('lastName','Last name cannot be empty');
  
  if(!empty($form_state['values']['phone'])) {
    if(!is_numeric($form_state['values']['phone'])) {
	form_set_error('phone','That is not a a valid phone number!');
    }
  }
}

function addUser_submit($form, $form_state)
{
  $fields = array("firstName", "lastName", "bio", "position", "phone", "grade", "gender", "FID");
  $userData = getFields($fields, $form_state['values']);

  $f = file_load($form_state['values']['FID']);
  file_save($f);
  dpm($f->fid);
  //  $url = file_create_url($f->uri);  

  dpm($form_state['values']);

  $UID = dbCreateUser($userData);
  
  dpm($UID, "UID");

  if ($UID != false){
    //    dbAssignEmailToUser($UID, $form_state['values']['email']);
    dbAssignUserToTeam($UID, $form_state['values']['TID']);
  }
  drupal_set_message("Form has been submitted");
}

function viewUser(){
  $params = drupal_get_query_parameters();
  $UID = $params["UID"];

  $markup ='<div align="right"><a href= "http://croma.chapresearch.com/?q=addUser';
  $markup .='&UID='. $UID;
  $markup .='">';
  $markup .='<button type="button">Edit</button></a></div>';  
  $markup .= '<table>';
  $user = dbGetUser($UID);
  $markup .= '<tr><td colspan="1">';
  dpm($user);
  $profilePic = file_load($user["FID"]);
  $url = file_create_url($profilePic->uri);
  $markup .= "<img src='$url' style='width:75px;height:100px;'> ".'</td>';
  $markup .= '<td colspan="3"><b>Name:</b> ' . $user['firstName'] . ' ' . $user['lastName'] . '</td></tr>';
  $markup .= '<tr><td colspan="4"><b>Bio:</b> ' . $user['bio'] . '</td></tr>';
  $markup .= '<tr><td colspan="2"><b>Position:</b> ' . $user['position'] . '</td>';
  $markup .= '<td colspan="2"><b>Phone:</b> ' . $user['phone'] . '</td></tr>';
  $markup .= '<tr><td colspan="2"><b>Grade:</b> ' . $user['grade'] . '</td>';
  $markup .= '<td colspan="2"><b>Gender:</b> ' . $user['gender'] . '</td></tr>';

  $numberOfHours = dbGetUserHours($UID);
  $markup .= '<tr><td colspan="1"><b>Number Of Hours:</b> ' . $numberOfHours . '</td></tr>';
  $markup .= '</table>';

  return array("#markup"=>$markup);

}

?>