<?php
include_once("drupalCompatibility.php");
include_once("croma_dbFunctions.php");

function populateStaticData()
{
  /* --- create permissions (returning UPID's) --- */

  // editing details about the team
  $editTeam             =        dbAddPermission(array('name' => 'editTeam'));

  // moving ideas to the 'outreach' phase
  $approveIdeas         =        dbAddPermission(array('name' => 'approveIdeas'));

  // approving hours so they count for hour totals
  $approveHours         =        dbAddPermission(array('name' => 'approveHours'));

  // approve the documentation of an outreach (to be displayed to public)
  $approveWriteUps       =        dbAddPermission(array('name' => 'approveWriteUps'));

  // edit any of the team's outreach
  $editAnyOutreach      =        dbAddPermission(array('name' => 'editAnyOutreach'));

  // add/remove/edit any of the team's outreach tags
  $manageOutreachTags   =       dbAddPermission(array('name' => 'manageOutreachTags'));

  // create/edit any hours records
  $editAnyHours         =        dbAddPermission(array('name' => 'editAnyHours'));

  // add/remove/edit any team members
  $manageTeamMembers    =        dbAddPermission(array('name' => 'manageTeamMembers'));

  // promote/demote moderators
  $manageModerators     =        dbAddPermission(array('name' => 'manageModerators'));

  // promote/demote admins
  $manageAdmins        =        dbAddPermission(array('name' => 'createAdmins'));

  // delete the team (permanently!)
  $deleteTeam           =        dbAddPermission(array('name' => 'deleteTeam'));

  // transfer ownership of the team
  $manageTeamOwners     =        dbAddPermission(array('name' => 'manageTeamOwners')); 


  /* --- create roles (returning RID's) --- */

  // manages/approves outreach content
  $moderator = dbAddRole(array('name'=> 'moderator', 'displayName' => 'Moderator'));

  // manages users and overrides moderator as necessary
  $teamAdmin = dbAddRole(array('name'=> 'teamAdmin', 'displayName' => 'Team Admin'));

  // ideally doesn't have to do anything, but can do everything
  $teamOwner = dbAddRole(array('name'=> 'teamOwner', 'displayName' => 'Team Owner'));


  /* --- link permissions to roles --- */

  dbAddPermissionToRole($editTeam,              $teamAdmin);
  dbAddPermissionToRole($editTeam,              $teamOwner);

  dbAddPermissionToRole($approveIdeas,          $teamAdmin);
  dbAddPermissionToRole($approveIdeas,          $teamOwner);
  dbAddPermissionToRole($approveIdeas,          $moderator);

  dbAddPermissionToRole($approveHours,          $moderator);
  dbAddPermissionToRole($approveHours,          $teamAdmin);
  dbAddPermissionToRole($approveHours,          $teamOwner);

  dbAddPermissionToRole($approveWriteUps,          $moderator);
  dbAddPermissionToRole($approveWriteUps,          $teamAdmin);
  dbAddPermissionToRole($approveWriteUps,          $teamOwner);

  dbAddPermissionToRole($manageOutreachTags,    $moderator);
  dbAddPermissionToRole($manageOutreachTags,    $teamAdmin);
  dbAddPermissionToRole($manageOutreachTags,    $teamOwner);

  dbAddPermissionToRole($editAnyOutreach,       $teamAdmin);
  dbAddPermissionToRole($editAnyOutreach,       $teamOwner);

  dbAddPermissionToRole($editAnyHours,          $teamAdmin);
  dbAddPermissionToRole($editAnyHours,          $teamOwner);

  dbAddPermissionToRole($manageTeamMembers,     $teamAdmin);
  dbAddPermissionToRole($manageTeamMembers,     $teamOwner);

  dbAddPermissionToRole($manageModerators,      $teamAdmin);
  dbAddPermissionToRole($manageModerators,      $teamOwner);

  dbAddPermissionToRole($manageAdmins,          $teamOwner);
  dbAddPermissionToRole($manageTeamOwners,      $teamOwner);
  dbAddPermissionToRole($deleteTeam,            $teamOwner);
}

populateStaticData();

?>
