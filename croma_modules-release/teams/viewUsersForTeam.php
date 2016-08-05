<?php

/*
  ---- teams/viewUsersForTeam.php ----
  used to view users associated with a certain team and allows the team owner or admin to change the role or kick another user on that team for that team

  - Contents -
  viewUsersForTeam()- Displays the users on a specific team (and if admin change the role or kick a user on that team for that team)
  viewUsersForTeam_submit()- Submits viewUsersForTeam (if comfirm button is pressed on viewUsersForTeam)
*/

// viewUsersForTeam()- Displays the users on a specific team (and if admin change the role or kick a user on that team for that team)
function viewUsersForTeam($form, &$form_state)
{
  global $user;
  $params = drupal_get_query_parameters();

  if (isset($params['TID'])){
    $TID = $params['TID'];
  } else {
    $TID = getCurrentTeam()['TID'];
  }

  if (teamIsIneligible($TID) || !isMyTeam($TID)) {
    drupal_set_message('You do not have permission to access that page!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }

  $form_state['TID'] = $TID;

  $canManageTeamMembers = hasPermissionForTeam('manageTeamMembers', $TID);
  $canManageTeamOwners = hasPermissionForTeam('manageTeamOwners', $TID);

  // create page header, table, and pending users/view all button

  $markup = '<table><tr><td colspan="3">';

  $markup .= "<h1>View All Members on Team ".dbGetTeamNumber($TID)."</h1></td>";

  $markup .= '<td colspan="3" style="text-align:right">';

  if ($canManageTeamMembers){
    if (!empty(dbGetUsersAwaitingApproval($TID))){
      $markup .= '<a href="?q=viewUsersToBeApproved&TID='.$TID;
      $markup .= '"><button type="button">View Pending Users</button></a>';
    } else {
      $markup .= '<button type="button" disabled>No Pending Users</button>';
    }
    $markup .= '<a href="?q=addTeamMember&TID='.$TID;
    $markup .= '&destination='.current_path();
    $markup .= '"><button type="button">Add User</button></a>';
  }

  if (isset($params['type'])){
    $markup .= '<a href="?q=showUsersForTeam&TID='.$TID;
    $markup .= '"><button type="button">View All</button></a>';
  }    

  $markup .= '</td></tr></table>';

  if (isset($params['query'])) {
    $persons = dbSearchUsersFromTeam($TID, $params['query']);
  } else {
    $type = isset($params['type'])?$params['type']:'';
    // filter by type (student vs mentor vs alumni)
    $persons = dbGetUsersFromTeam($TID, $type); 
  }

  if (empty($persons)) {
    drupal_set_message('No users found!', 'error');
    drupal_goto($_SERVER['HTTP_REFERER']);
  }
  
  // sets up the table to display name, role, and grade of every user on the certain team
  $markup .= '<table class="infoTable"><th>Name</th>';
  $markup .= '<th>Email</td></th>';
  $markup .= '<th>Team Role</td></th>';
  $markup .= '<th>CROMA Role</th>';

  // if user is an admin, they see a new column where they can change the role of other team members
  if ($canManageTeamMembers){
    $markup .= '<th>Admin Functions</th>';
  }else {
    $markup .= '<th></th>';
  }

  $form['tableHeader']=array(
			     '#markup' => $markup
			     );
  $i = 0;
  foreach($persons as $person) {
    $form_state["UID-$i"]=$person['UID'];
    $markup = '<tr><td><a href="?q=viewUser&UID=' . $person["UID"] . ' ">';

    // hyperlinks the name so every name is linked to its user profile
    $markup .= $person["firstName"] . " " . $person["lastName"]. '</a></td>'; 
    $form["name-$i"]=array('#markup'=> $markup);

    $email = dbGetUserPrimaryEmail($person['UID']);
    $markup = "<td><a href=\"mailto:$email\" target=\"_top\">$email</a></td>";

    $form["email-$i"]=array('#markup'=> $markup);

    $markup = '<td>' . ucfirst(dbGetUserProfile($person['UID'])['type']) . '</td>';
    $form["isStudent-$i"]=array('#markup'=> $markup);

    $RID = dbGetRIDForTeam($person['UID'], $TID);
    $teamOwnerRID = dbGetRID('teamOwner');
    $personIsTeamOwner = ($RID == $teamOwnerRID);

    // allow current user to change roles (but not change the role of the team owner)
    if($canManageTeamMembers && !$personIsTeamOwner){
      
      // if the person in question doesn't have a role
      if(!$RID){ 
	$RID = 0;
      }

      $roles = dbGetAllRoles();
      $roles[0] = 'Team Member';

      // if current user can't create team owners
      if(!$canManageTeamOwners){
	unset($roles[$teamOwnerRID]);
      }

      // make sure the roles are still in order
      ksort($roles);

      $form["RID-$i"]=array(
			    '#prefix' => '<td class="roleSelector">',
			    '#type' => 'select',
			    '#default_value' => $RID,
			    '#options' => $roles,
			    '#suffix' => '</td>',
			    '#ajax' => array(
					     'event' => 'change',
					     'callback' => 'callback',
					     'wrapper' => 'confirm-div',
					     'method' => 'replace',
					     ),
			    );
    } else {       // if the current user can't change the role
      if($RID == 0){
	$role = 'Member';
      } else {
	$role = dbGetRoleName($RID);
      }


      $form["role-$i"]=array(
			     '#prefix' => '<td>',
			     '#markup' => $role,
			     '#suffix' => '</td>'
			     );
    }      

    // if the person in question is the current user
    if($person['UID'] == $user->uid){
      // if the person is the team owner -- transfer ownership
      if($personIsTeamOwner){
	$markup = "<td><a href=\"?q=transferTeamOwnership&TID=$TID\">";
	$markup .= "<button type=\"button\">Transfer Ownership</button></a></td>";
      } else { 	// allow user to leave team
	$markup = "<td><a href=\"?q=leaveTeam/$TID\">";
	$markup .= "<button type=\"button\">Leave Team</button></a></td>";
      }	  
      // if the current user can remove users
    } else if($canManageTeamMembers && !$personIsTeamOwner){
      $markup = "<td><a href=\"?q=kickUserFromTeam/{$person['UID']}/$TID\">";
      $markup .= "<button type=\"button\" onclick=\"if(!confirm('Are you sure you want to remove this user from your team?')){return false;}\">Kick User</button></a></td>";
    } else {      // or just some random person
      $markup = '<td></td>';
    }

      $form["adminFunctions-$i"]=array(
				       '#markup'=> $markup,
				       );


    $form["rowFooter-$i"]=array(
				'#markup'=>'</tr>',
				);
    $i++;
  } // end of foreach
  $form_state['numUsers'] = $i;

  $form['tableFooter']=array('#markup'=>'</table>');

  if($canManageTeamMembers){
    $form['buttons']=array(
			   '#prefix'=>'<div id="confirm-div" style="visibility:hidden">',
			   '#suffix'=>'</div>'
			   );

    $form['buttons']['confirm']=array(
				      '#type'=>'submit',
				      '#value'=>'Confirm',
				      );
  }

  return $form;
}

// viewUsersForTeam_submit()- Submits viewUsersForTeam (if comfirm button is pressed on viewUsersForTeam)
function viewUsersForTeam_submit($form, $form_state)
{
  $TID = $form_state['TID'];

  $roleChanged = false;

  for($i = 0; $i < $form_state['numUsers']; $i++){
    if(!isset($form_state['values']["RID-$i"])){
      continue;
    }
    $UID = $form_state["UID-$i"];
    $newRID = $form_state['values']["RID-$i"];
    $oldRID = $form["RID-$i"]['#default_value'];
    // check if the RID changed
    if ($newRID != $oldRID){
      // adding new role
      if($oldRID == 0){
	dbGiveUserRID($UID, $TID, $newRID);
      } else if ($newRID != 0){
	$result = dbUpdateUserRole($UID, $TID, $newRID);
      } else {
	dbRemoveAllUserRoles($UID, $TID);
      }
      $userName = dbGetUserName($UID);
      drupal_set_message("$userName's role has been updated!");
      $roleChanged = true;
      $notification = array(
			    'UID' => $UID,
			    'TID' => $TID,
			    'dateCreated' => date(DEFAULT_TIME_FORMAT, time()),
			    'dateTargeted' => date(DEFAULT_TIME_FORMAT, time()),
			    );

      // check if the user no longer has a role
      if (dbGetRoleName($newRID) == false){
	$notification['message'] = "You are no longer a " . strtolower(dbGetRoleName($oldRID));
      } else {
	$notification['message'] = 'You are now a ' . strtolower(dbGetRoleName($newRID));
      }

      $notification['message'] .= ' on team '. dbGetTeamName($TID) . '!';
      dbAddNotification($notification);
    }
  } 
  
  if(!$roleChanged){
    drupal_set_message('No changes were made!', 'error');
  }
}

function callback(&$form, $form_state)
{ 
  $form['buttons']['#prefix']='<div id="confirm-div" style="text-align:right">';

  return $form['buttons'];
}

?>