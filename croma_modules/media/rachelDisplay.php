<?php

function uploadMedia($form, &$form_state) {
  $form = array();

  $form['pictures'] = array(
			'#type' => 'file',
			'#name' => 'files[]',
			'#title' => t('Upload Media: Hold [Ctrl] in file selection window to upload multiple media.'),
			'#description' => t('JPG\'s, GIF\'s, and PNG\'s only, 10MB Max Size'),
			'#attributes' => array('multiple' => 'multiple'),
			);

  $form['submit'] = array(
			  '#type' => 'submit',
			  '#value' => t('Upload'),
			  );

  return $form;
}

function uploadMedia_validate($form, &$form_state) {
  //Save multiple files
  $num_files = count($_FILES['files']['name']);
  for ($i = 0; $i < $num_files; $i++) {
    $file = file_save_upload($i, array(
				       'file_validate_is_image' => array(),
				       'file_validate_extensions' => array('png gif jpg jpeg'),
				       ));
    if ($file) {
      if ($file = file_move($file, 'public://')) {
	$form_state['values']['file'][$i] = $file;
      }
      else {
	form_set_error('file', t('Failed to write the uploaded file the site\'s file folder.'));
      }
    }
    else {
      form_set_error('file', t('No file was uploaded.'));
    }   
  }
}

function uploadMedia_submit($form, &$form_state) 
{
  global $user;

  $params = drupal_get_query_parameters();
  $OID = $params['OID'];

  foreach($form_state['values']['file'] as $picture){
    $media = array();
    $media['FID'] = $picture->fid;
    $media['UID'] = $user->uid;
    $media['OID'] = $OID;
    dbAddMedia($media);
    file_usage_add($picture, 'CROMA - media', 'pictures', $picture->fid); // tells Drupal we're using the file
  }

  drupal_set_message(t('Upload successful'));
  drupal_goto('viewMedia', array('query'=>array('OID'=>$OID)));
}

function viewMedia()
{
  $params = drupal_get_query_parameters();
  $OID = $params['OID'];

  $markup = '';
  
  $outreach = dbGetOutreach($OID);

  $markup .= "<h3>Viewing media for \"{$outreach['name']}\" Outreach...</h3>";

  $markup .= '<br><div align="right">';

  $markup .= "<button><a href=\"?q=viewOutreach&OID=$OID\">Back to View Outreach</a></button></div><br><br></div>";

  $media = dbGetMediaForOutreach($OID);

  foreach ($media as $m){
    $file = file_load($m['FID']);
    $url = file_create_url($file->uri);
    $markup .= '<table><tr><td><img src="' . $url . '" width="100px" height="100px"></td>';
    $markup .= '<br><td>Title: '. $m['title'] .'</td>';
    $markup .= '<br><td>Description: ' . $m['description'] . '</td></tr></table>';
  }

  $markup .= "<br><button><a href=\"?q=uploadMedia&OID=$OID\">Upload Media</a></button><br><br>";

  return array('#markup'=>$markup);
}

function getAttachment($full_contents)
{
  $pieces = preg_split('/--.+--/',$full_contents);
  $pieces2 = preg_split('/X-Attachment-Id:.+[^-\s]/',$pieces[1]);
  $media_encoded = $pieces2[1];

  $pic = base64_decode($media_encoded);
  $file = file_save_data($pic);
  return $file->fid;
}

function getSubject($full_contents)
{
  $pieces = explode('Subject: ', $full_contents);
  $pieces2 = explode('From: ',$pieces[1]);
  return $pieces2[0];
}

function getBody($full_contents)
{
  $int =  strpos($full_contents, 'Content-Type: text/plain');
  $pieces = explode('Content-Type: text/plain; charset=UTF-8', $full_contents, 3);
  $pieces2 = explode('--', $pieces[1], 2);

  return trim($pieces2[0]);
}

function getIncomingMedia()
{
  global $user;

  $basepath = '/home/pics/imageimport/';

  $dir = new DirectoryIterator($basepath);
  foreach ($dir as $fileinfo) {
    if(!$fileinfo->isDot()){
      $dir_name = $fileinfo->getFilename();
      $filepath = $basepath . $dir_name . '/message';
      $contents = file_get_contents($filepath);
  
      $media = array();
      $media['FID'] = getAttachment($contents);
      $media['UID'] = $user->uid; // TODO - fix this!!!!!!!!!!!!!
      $media['title'] = getSubject($contents);
      $media['description'] = getBody($contents);
      //      $media['date'] = getDate($contents);
      dbAddMedia($media);
  
      unlink($filepath);
      rmdir($basepath . $dir_name);
    }
  }
}

?>