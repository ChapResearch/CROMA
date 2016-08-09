<?php

/*
  ---- media/getIncomingMedia.php ----
  used to process attachments sent to the special CROMA email

  - Contents -
  getSubject() - parses the full text of the email to find the subject
  getSentDate() - parses the text of the email to find when it was sent
  getSender() - returns the UID of the user who sent the email
  getIncomingMedia() - add media for the attachments deposited by Postfix
*/

function getSubject($full_contents)
{
  preg_match("/(?<=Subject: ).*/",$full_contents, $pieces);
  if(!empty($pieces)){
    return substr($pieces[0], 0, 50); // make sure the title fits into the DB
  }
  return "";
}

function getSentDate($full_contents)
{
  preg_match("/(?<=Date: ).*/",$full_contents, $pieces);
  if(!empty($pieces)){
    return $pieces[0];
  }
  return "";
}

function getSender($full_contents)
{
  preg_match("/From:.+<.+>/", $full_contents, $pieces); // gets the From: [name] <email>
  preg_match("/[^(<|>)]+@[^(<|>)]+/", $pieces[0], $pieces2); // gets the email from within < >
  $UID = dbSearchUserByEmail($pieces2[0]);
  return $UID;
}

/* getIncomingMedia() - manually adds media entries into the database for each email sent to pics@chapresearch.com (or its equivalent). This function navigates to the folder used by the getMedia.sh script called by Postfix upon receiving an email. It then parses the emails for info such as sender and date and uploads the appropriate attachments.
 */
function getIncomingMedia()
{
  $media = array();

  if (TYPE == 'release'){
    $basepath = '/home/pics/imageimport/';
  } else if (TYPE == 'test'){
    $basepath = '/home/test-pics/imageimport/';
  }

  $dir = new DirectoryIterator($basepath);

  // loop through all messages
  foreach ($dir as $fileinfo) {
    
    // if this is a valid file
    if(!$fileinfo->isDot()){

      $dir_name = $fileinfo->getFilename();
      $sub_dir = new DirectoryIterator($basepath.$dir_name);
      $full_contents = file_get_contents($basepath . $dir_name . "/message");
      $UID = getSender($full_contents); // set up the UID for the message

      if($UID != false){

	$media['UID'] = $UID;
	$title = getSubject($full_contents); // set up the title for the pictures
	$media['title'] = $title;

	// process sent date
	$sentDate = getSentDate($full_contents);
	$sentDate = strtotime($sentDate);
	$media['dateEntered'] = dbDatePHP2SQL($sentDate);

	// cut the body of the email down to size (and add "...")
	$media['description'] = chopString(file_get_contents("$basepath$dir_name/part1"), MAX_DESCRIPTION_CHAR - 3, true);

	// loop through all attachments
      	foreach ($sub_dir as $fileinfo2){ 

	  // if this is a valid file
	  if(!$fileinfo2->isDot()){
	    $fileName = $fileinfo2->getFilename();
	    $filepath = $basepath . $dir_name . '/' . $fileName;
	    
	    // if this is an image
	    if($fileName != 'part1' && $fileName != 'part2' && $fileName != 'message'){

	      // save the image to drupal
	      $contents = file_get_contents($filepath);
	      $file = file_save_data($contents, 'public://'.$fileName,FILE_EXISTS_REPLACE);
	      $media['FID'] = $file->fid;
	      dbAddMedia($media); // add the final media entry
	    }
	    unlink($filepath); // delete the file
	  }
	} // end of attachment processing
	rmdir($basepath . $dir_name); // delete the directory
      } // end of single message processing
    }
  } // end of big foreach
}

?>