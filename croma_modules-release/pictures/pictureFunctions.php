<?php

/*
  ---- pictures/pictureFunctions.php ----

  used to handle all aspects of pictures within CROMA

  - Contents -
  generatePictureField() - creates the field to upload an image
  addPicture(), removePicture(), replacePicture() - handles the image in Drupal
  addUsage() - tells Drupal the file is in use
  bulkSavePictures() - saves multiple pictures at once
  generateURL() - creates the URL to view the image
*/

/* generatePictureField() - returns a render array of the picture field. The FID will be filled into the form state for the value of the field within the form API.
 */
function generatePictureField($title, $defaultValue, $prefix = '', $suffix = '')
{
  return array(
	       '#prefix'=>$prefix,
	       '#type'=>'managed_file',
	       '#title'=>$title,
	       '#upload_location' => 'public://',
	       '#upload_validators' => array(
					     'file_validate_extensions' => array('gif png jpg jpeg'),
					     'file_validate_size' => array(50000*1024)),
	       '#default_value'=>$defaultValue,
	       '#suffix'=>$suffix
	       );
}

function addPicture($FID, $moduleName)
{
  $f = file_load($FID);
  $f->status = FILE_STATUS_PERMANENT;
  file_save($f);
  addUsage($f, $moduleName);// tells Drupal we're using the file
}

function removePicture($FID, $moduleName)
{
  $f = file_load($FID);
  if($f != null){
    file_usage_delete($f, "CROMA - $moduleName");
    file_delete($f);
  } else {
    drupal_set_message('File does not exist!', 'error');
  }
}

function replacePicture($newFID, $oldFID, $moduleName)
{
  if($newFID != 0 && $newFID != $oldFID){
    addPicture($newFID, $moduleName);
    if ($oldFID != 0){
      removePicture($oldFID, $moduleName);
    }
  }
}

function addUsage($picture, $moduleName)
{
  file_usage_add($picture, "CROMA - $moduleName", $moduleName, $picture->fid); // tells Drupal we're using the file
}

function bulkSavePictures(&$form_state)
{
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

function generateURL($FID)
{
  $picture = file_load($FID);
  return file_create_url($picture->uri);
}

?>