<?php

function addTagRow($form, &$form_state) 
{
  $form_state['numNewRows']++;
  $form_state['rebuild'] = TRUE;
}

function removeTagRow($form, &$form_state) 
{
  $form_state['numNewRows']--;
  $form_state['rebuild'] = TRUE;
}

function modifyTagRows_callback($form, $form_state) 
{
  return $form['tags'];
}

function switchTagEditMode($isEditing = true)
{
  $_SESSION['tagEditMode'] = $isEditing;
}

function checkTagEditMode()
{
  return isset($_SESSION['tagEditMode'])?$_SESSION['tagEditMode']:false;
}

/* tagManager() - paints the tag management window. Note that this is declared as a form, but will simply display existing tag data without "editTag" in the URL of the page. The "edit" and "confirm" buttons are used to switch between the view and edit modes.
 */
function tagManager($form, &$form_state)
{
  $team = getCurrentTeam();
  $form_state['TID'] = $TID = $team['TID'];

  if(teamIsIneligible($TID)) {
    drupal_set_message('Your team does not have permission to access this page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
    return;
  }

  $currentURL = getCurrentURL();

  // if browser didn't end up here by coming from the current page
  if($currentURL != getAjaxURL() && (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != $currentURL)){
    switchTagEditMode(false);
    if(!empty(drupal_get_query_parameters())){
      drupal_goto(parseURLAlias($_SERVER['QUERY_STRING']));
    }
  }

  $markup = "<h1>Team {$team['number']} Outreach Settings</h1>";

  $editMode = checkTagEditMode();

  //  $tableHeader .= '<button onclick="switchTagEditMode"><img src="/images/icons/editWhite.png" style="max-width:15px; width:auto; height:auto; padding: 1px 0px 1px 0px"></button>';
  $tableHeader = '<table><tr><td><h2>Outreach Tags</h2></td><td><div align="right">';


  $tableHeader2 = '</div></td></tr></table>';
  $tableHeader2 .= '<table class="infoTable"><tr><th>Tag Name</th>';

  //  $tableHeader = '<table><tr><th>Tag Name</th><th>';
  $tags = dbGetOutreachTagsForTeam($TID);

  if(!$editMode){
    $tableHeader2 .= '<th style="text-align:center">Matched Outreaches</th>';
  } else {
    $tableHeader2 .= '<th></th><th></th>';
  }

  $form['tags']=array(
		      '#prefix'=>'<div id="tags-div">',
		      '#suffix'=>'</div>'
		      );

  $form['tags']['tableHeader']=array('#markup'=>$tableHeader);
  
  if(!$editMode && hasPermissionForTeam('manageOutreachTags', $TID)){
    $form['tags']['buttons']=array(
			 //'#prefix'=>'<div align="right">',
			 '#type'=>'image_button',
			 '#src' => '/images/icons/editWhite.png',
			 '#attributes' => array('class' => array('editIcon')),
			 '#limit_validation_errors'=>array(),
			 '#submit'=>array('switchTagEditMode'),
			 //'#suffix'=>'</div>'
			     );
  }
  
  $form['tags']['tableHeader2']=array('#markup'=>$tableHeader2);

  if(!$editMode){ // if in "view" mode (aka not acting as a form)
    $tableContents = '';
    $tableContents .= '</tr>';

    if(!empty($tags)){
      foreach($tags as $OTID => $tagName){
	$tableContents .= '<tr><td><a href="?q=outreach&tag=' . $OTID . '">' . $tagName . '</a></td>';
	$numMatched = dbGetOutreachMatchingTags(array($tagName), $TID, true);
	$tableContents .= "<td style=\"text-align:center\">$numMatched</td></tr>";
      }
    } else {
      $tableContents = '<tr><td colspan="2" style="text-align:center"><em>[None]</em></td></tr>';
    }
    $form['tags']['tableContents']=array('#markup'=>$tableContents);
  } else { // -------------------------------- in "edit" mode
    $i = 0;

    if(!empty($tags)){
      foreach($tags as $OTID => $tagName){
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
					     '#markup'=>"<button onclick=\"$confirmBoxJS\" type=\"button\"><img src=\"/images/icons/trashWhite.png\" style=\"max-width:12px; width:auto; height:auto; margin:0px; padding: 0px 0px 0px 0px\"></button>",
					     '#suffix'=>'</a></td></tr>'
					     );
	$form_state["OTID-$i"] = $OTID;
      } // end of foreach
    } // end of if
    $form_state['numTags'] = $i;

    // create the empty row for creating new tags

    if(empty($form_state['numNewRows'])){
      $form_state['numNewRows'] = 1;
    }

    $x;
    for($x = 1; $x <= $form_state['numNewRows']; $x++){ // have to be sure to not overwrite anything

      $form['tags']["newTagName-$x"]=array(
					'#prefix'=>'<tr><td>',
					'#type'=>'textfield',
					'#maxlength'=>50,
					'#suffix'=>'</td>'
					);

      if($form_state['numNewRows'] > 1 && $x == $form_state['numNewRows']){
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

      if($x == $form_state['numNewRows']){
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

  if($editMode){
    $form['cancel']=array(
			  '#prefix'=>'<table><tr><td style="text-align:left">',
			  '#markup'=>'<a href="?q=teamOutreachSettings&edit"><button type="button">Cancel</button></a>',
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
  if(!isset($form_state['numTags'])){
    return;
  }

  // check if all the fields were left blank (AND no tags exist already)
  $allEmpty = true;
  if($form_state['numTags'] == 0){
    for($x = 1; $x <= $form_state['numNewRows']; $x++){
      if($form_state['values']["newTagName-$x"] != ''){
	$allEmpty = false;
	break;
      }
    }
  } else {// if there are existing tags
    $allEmpty = false;
  }

  if($allEmpty){
    form_set_error('', 'You have not added a tag!');
  }
}

function tagManager_submit($form, $form_state)
{
  $i;
  for($i = 1; $i <= $form_state['numTags']; $i++){
    dbUpdateOutreachTag($form_state["OTID-$i"], $form_state['values']["tagName-$i"]);
  }
  $x;
  for($x = 1; $x <= $form_state['numNewRows']; $x++){
    if($form_state['values']["newTagName-$x"] != ''){
      dbCreateOutreachTagForTeam($form_state['values']["newTagName-$x"], $form_state['TID']);
    }
  }
  switchTagEditMode(false);
}

function deleteTag($OTID)
{
  if(dbDeleteOutreachTag($OTID)) {
    drupal_set_message('Tag deleted successfully!');
    drupal_goto('teamOutreachSettings', array('query'=>array('editTags'=>'true')));
    return;
  } else {
    drupal_set_message('Error deleting tag!', 'error');
    drupal_goto('teamOutreachSettings', array('query'=>array('editTags'=>'true')));
    return;
  }
}
