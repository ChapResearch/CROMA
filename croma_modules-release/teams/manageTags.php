<?php

/*
  ---- teams/manageTags.php ----

  used to view and edit the outreach tags for a team

  - Contents -
  addTagRow(), removeTagRow(), modifyTagRows_callback() - manages the AJAX to add/remove extra tag rows
  switchTagEditMode(), checkTagEditMode() - used to switch between viewing and editing
  tagManager() - paint the tag form itself (note that this is static when in "view" mode)
*/

function addTagRow($form, &$form_state) 
{
  $form_state['numNewRows']++;
  $form_state['rebuild'] = true;
}

function removeTagRow($form, &$form_state) 
{
  $form_state['numNewRows']--;
  $form_state['rebuild'] = true;
}

function modifyTagRows_callback($form, $form_state) 
{
  return $form['tags'];
}

// switch between viewing and editing tags
function switchTagEditMode($isEditing = true)
{
  $_SESSION['tagEditMode'] = $isEditing;
}

function checkTagEditMode()
{
  return isset($_SESSION['tagEditMode'])?$_SESSION['tagEditMode']:false;
}

/* tagManager() - paints the tag management window. Note that this is declared as a form, but will simply display existing tag data unless the session variable "tagEditMode" is set. The "edit" and "confirm" buttons are used to switch between the view and edit modes.
 */
function tagManager($form, &$form_state)
{
  $team = getCurrentTeam();
  $form_state['TID'] = $TID = $team['TID'];

  if (teamIsIneligible($TID)){
    drupal_set_message('Your team does not have permission to access this page.', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  $currentURL = getCurrentURL();

  // if browser didn't end up here by coming from the current page
  if ($currentURL != getAjaxURL() && (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != $currentURL)){

    switchTagEditMode(false);    // be sure to start with just viewing the tags

    if (!empty(drupal_get_query_parameters())){    // clear any other drupal_query_parameters
      drupal_goto(parseURLAlias($_SERVER['QUERY_STRING']));
    }
  }

  $markup = "<h1>Team {$team['number']} Outreach Settings</h1>";

  $editMode = checkTagEditMode();

  // create the wrapper-div used by AJAX
  $form['tags']=array(
		      '#prefix'=>'<div id="tags-div">',
		      '#suffix'=>'</div>'
		      );

  $tableHeader = '<table><tr><td><div class="help tooltip2"><h2>Outreach Tags</h2><span id="helptext"; class="helptext tooltiptext2">Outreach Tags are used to tag similar outreaches.</span></div></td><td><div align="right">';

  $form['tags']['tableHeader']=array('#markup'=>$tableHeader);
  
  // only show edit button if in "view mode" and the user has proper permissions
  if (!$editMode && hasPermissionForTeam('manageOutreachTags', $TID)){
    $form['tags']['buttons']=array(
				   '#type'=>'image_button',
				   '#src' => '/images/icons/editWhite.png',
				   '#attributes' => array('class' => array('editIcon')),
				   '#limit_validation_errors'=>array(),
				   '#submit'=>array('switchTagEditMode'),
				   );
  }

  // finish off the title and buttons table, then start the table for the tags themselves
  $tableHeader2 = '</div></td></tr></table>';
  $tableHeader2 .= '<table class="infoTable"><tr><th>Tag Name</th>';

  if (!$editMode){
    $tableHeader2 .= '<th style="text-align:center">Matched Outreaches</th>';
  } else {
    $tableHeader2 .= '<th></th><th></th>';
  }
  
  $form['tags']['tableHeader2']=array('#markup'=>$tableHeader2);

  $tags = dbGetOutreachTagsForTeam($TID);

  if (!$editMode){ // if in "view" mode (aka not acting as a form)
    $tableContents = '';
    $tableContents .= '</tr>';

    if (!empty($tags)){
      foreach ($tags as $OTID => $tagName){
	// display the name
	$tableContents .= '<tr><td>' . $tagName . '</td>';
	// show the number of matching outreaches (which can be clicked on to search the outreach form by tags)
	$numMatched = dbGetOutreachMatchingTags(array($tagName), $TID, true); // "true" indicates only a count is returned
	$tableContents .= "<td style=\"text-align:center\"><a href=\"?q=outreach&tag=$OTID\">$numMatched</a></td></tr>";
      }
    } else {
      $tableContents = '<tr><td colspan="2" style="text-align:center"><em>[None]</em></td></tr>';
    }
    $form['tags']['tableContents']=array('#markup'=>$tableContents);
  } else { // -------------------------------- in "edit" mode
    $i = 0;

    if (!empty($tags)){
      foreach ($tags as $OTID => $tagName){
	$i++;
	$form['tags']["tagName-$i"]=array(
					  '#prefix'=>'<tr><td colspan="2">',
					  '#type'=>'textfield',
					  '#maxlength'=>50,
					  '#default_value'=>$tagName,
					  '#suffix'=>'</td>'
					  );


	$numMatching = dbGetOutreachMatchingTags(array(dbGetTagName($OTID)), $TID, true);
	$confirmBoxJS = '';
	if($numMatching > 0){
	  $confirmBoxJS = "if(!confirm('This tag matches $numMatching outreach(es). Are you sure you want to delete it?')){return false;}";
	}

	$form['tags']["deleteBttn-$i"]=array(
					     '#prefix'=>"<td><a href=\"?q=deleteTag/$OTID/$TID\">",
					     '#markup'=>"<button onclick=\"$confirmBoxJS\" type=\"button\"><img src=\"/images/icons/trashWhite.png\" class=\"trashIcon\"></button>",
					     '#suffix'=>'</a></td></tr>'
					     );
	$form_state["OTID-$i"] = $OTID;
      } // end of foreach
    } // end of if
    $form_state['numTags'] = $i;


    // initialize the 'numNewRows' variable
    if (empty($form_state['numNewRows'])){
      $form_state['numNewRows'] = 1;
    }

    $x; // PHP is weird and makes you declare a variable before the loop
    // create the empty row for creating new tags
    for ($x = 1; $x <= $form_state['numNewRows']; $x++){ // have to be sure to not overwrite anything

      // create row to allow entry of a new tag
      $form['tags']["newTagName-$x"]=array(
					'#prefix'=>'<tr><td>',
					'#type'=>'textfield',
					'#maxlength'=>50,
					'#suffix'=>'</td>'
					);

      // if this is the last row (and not the only row), add a "-" button
      if ($form_state['numNewRows'] > 1 && $x == $form_state['numNewRows']){
	$form['tags']["newRemoveBttn-$x"]=array(
						  '#prefix'=>'<td>',
						  '#type'=>'submit',
						  '#submit'=>array('removeTagRow'),
						  '#value'=>'-',
						  '#limit_validation_errors' => array(),
						  '#ajax'=>array(
								 'callback'=>'modifyTagRows_callback',
								 'wrapper'=>'tags-div'
								 ),
						  '#suffix'=>'</td>'
						  );
      } else {
	// add a placeholder instead of the "-" button
	$form['tags']["removeBttnPlaceHolder-$x"]=array('#markup'=>'<td></td>');
      }

      // if this is the last row, add a "+" button
      if ($x == $form_state['numNewRows']){
	$form['tags']["newAddBttn-$x"]=array(
					     '#prefix'=>'<td>',
					     '#type'=>'submit',
					     '#submit'=>array('addTagRow'),
					     '#value'=>'+',
					     '#limit_validation_errors' => array(),
					     '#ajax'=>array(
							    'callback'=>'modifyTagRows_callback',
							    'wrapper'=>'tags-div'
							    ),
					     '#suffix'=>'</td>'
					     );
      } else {
	// add a placeholder instead of the "+" button
	$form['tags']["addBttnPlaceHolder-$x"]=array('#markup'=>'<td></td>');
      }
      $for['tags']["rowFooter-$x"]=array('#markup'=>'</tr>');
    } // end of for loop
  } // end of else (aka edit mode code)
  $form['tags']['tableFooter']=array('#markup'=>'</table>');

  // allow the user to cancel and return to the previous page 
  // (note that the URL for cancel has a random extra parameter to ensure the mode is changed to view)
  if($editMode){
    $form['cancel']=array(
			  '#prefix'=>'<table><tr><td style="text-align:left">',
			  '#markup'=>'<a href="?q=teamOutreachSettings&notEdit"><button type="button">Cancel</button></a>',
			  '#suffix'=>'</td>'
			  );
    $form['submit']=array(
			  '#prefix'=>'<td style="text-align:right">',
			  '#type'=>'submit',
			  '#value'=>'Confirm',
			  '#suffix'=>'</td></tr></table>'
			  );
  }

  return $form;
}

function tagManager_validate($form, $form_state)
{
  // if numTags isn't set, the form wasn't filled
  if (!isset($form_state['numTags'])){
    return;
  }

  // check if all the fields were left blank (AND no tags exist already)
  $allEmpty = true;
  if ($form_state['numTags'] == 0){
    for ($x = 1; $x <= $form_state['numNewRows']; $x++){
      if ($form_state['values']["newTagName-$x"] != ''){
	$allEmpty = false;
	break;
      }
    }
  } else {// if there are existing tags
    $allEmpty = false;
  }

  if ($allEmpty){
    form_set_error('', 'You have not added a tag.');
  }
}

function tagManager_submit($form, $form_state)
{
  $i;
  for ($i = 1; $i <= $form_state['numTags']; $i++){ // update existing tags
    dbUpdateOutreachTag($form_state["OTID-$i"], $form_state['values']["tagName-$i"]);
  }
  $x;
  for ($x = 1; $x <= $form_state['numNewRows']; $x++){ // add the new tags
    if ($form_state['values']["newTagName-$x"] != ''){
      dbCreateOutreachTagForTeam($form_state['values']["newTagName-$x"], $form_state['TID']);
    }
  }
  switchTagEditMode(false); // go back to view mode
}

function deleteTag($OTID)
{
  if (dbDeleteOutreachTag($OTID)){ // if the tag was deleted successfully
    drupal_set_message('Tag deleted successfully.');
    drupal_goto('teamOutreachSettings', array('query'=>array('editTags'=>'true')));
  } else {
    drupal_set_message('Error deleting tag.', 'error');
    drupal_goto('teamOutreachSettings', array('query'=>array('editTags'=>'true')));
  }
}
