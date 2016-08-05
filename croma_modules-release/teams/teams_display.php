<?php

/* ---------------------------- display.php ---------------------------------
   This file creates blocks that implement displaying of outreach.
*/   

include_once(MODULES_FOLDER."/blockSupport.php");
include_once(DATABASE_FOLDER."/croma_dbFunctions.php");
include_once(MODULES_FOLDER."/teams/teamForm.php");
include_once(MODULES_FOLDER."/teams/viewTeamInfo.php");
include_once(MODULES_FOLDER."/teams/applyForTeams.php");
include_once(MODULES_FOLDER."/teams/manageTeams.php");
include_once(MODULES_FOLDER."/teams/manageUsersForTeam.php");
include_once(MODULES_FOLDER."/teams/viewUsersForTeam.php");
include_once(MODULES_FOLDER."/teams/userStatsForTeam.php");
include_once(MODULES_FOLDER."/teams/teamOutreachSettings.php");
include_once(MODULES_FOLDER."/teams/manageTags.php");
include_once(MODULES_FOLDER."/teams/publicOutreachForTeams.php");
include_once(MODULES_FOLDER."/teams/deleteTeam.php");
include_once(MODULES_FOLDER."/teams/addTeamMember.php");

$formBlocks = array(
		    array("id" => "teamForm", "title" => "CROMA - Add/Edit Team", "form" => "teamForm"),
		    array("id" => "applyForTeamForm", "title" => "CROMA - Apply for Team", "form" => "applyForTeamForm"),
		    array("id" => "viewUsersForTeam", "title" => "CROMA - View Users For Team", "form" => "viewUsersForTeam"),
		    array("id" => "teamDashboardHeader", "title" => "CROMA - Team Dashboard Header", "form" => "teamDashboardHeader"),
		    array("id" => "teamOutreachHeader", "title" => "CROMA - Team Outreach Header", "form" => "teamOutreachHeader"),
		    array("id" => "transferTeamOwnership", "title" => "CROMA - Transfer Team Ownership", "form" => "transferTeamOwnershipForm"),
		    array("id" => "teamAllOutreachHeader", "title" => "CROMA - Team All Outreach Header", "form" => "teamAllOutreachHeader"),
		    array("id" => "tagManager", "title" => "CROMA - Manage Tags", "form" => "tagManager"),
   		    array("id" => "publicOutreach", "title" => " CROMA - Select Public Outreaches", "form" => "publicOutreach"),
   		    array("id" => "deleteTeam", "title" => " CROMA - Delete Team", "form" => "deleteTeamPage"),
		    array("id" => "teamSummary", "title" => "CROMA - Team Summary", "form" => "teamSummary"),
   		    array("id" => "addTeamMember", "title" => " CROMA - Add Team Member", "form" => "addTeamMember")
		    );

$otherBlocks = array(array("id" => "viewTeam", "title" => "CROMA - View Team", "content" => "viewTeam"),
		     array("id" => "switchTeam", "title" => "CROMA - Switch Your Team", "content" => "switchTeamPage"),
		     array("id" => "recentTeamOutreach", "title" => "CROMA - Recently Added Team Outreach", "content" => "recentTeamOutreach"),
		     array("id" => "switchTeamTab", "title" => "CROMA - Switch Team Tab", "content" => "switchTeamTab"),
		     array("id" => "viewTeamStatistics", "title" => "CROMA - View Team Statistics", "content" => "viewTeamStatistics"),
		     array("id" => "manageUserTeams", "title" => "CROMA - Manage User Teams", "content" => "manageUserTeams"),
		     array("id" => "approveUser", "title" => "CROMA - Approve User", "content" => "approveUser"),
		     array("id" => "viewUsersAwaitingApproval", "title" => "CROMA - View Users Awaiting Approval", "content" => "viewUsersAwaitingApproval"),
		     array("id" => "viewUserHoursForTeam", "title" => "CROMA - User Hours For Team", "content" => "viewUserHoursForTeam"),
		     array("id" => "viewUserEventsForTeam", "title" => "CROMA - User Events For Team", "content" => "viewUserEventsForTeam"),
		     array("id" => "allTeamOutreach", "title" => "CROMA - All Team Outreach", "content" => "allTeamOutreach"),
		     array("id" => "outreachSettingsHeader", "title" => "CROMA - Outreach Settings Header", "content" => "outreachSettingsHeader"),
);		   

global $teamsBlockInfo;
global $teamsBlockViewFns;

blockLoadForms($teamsBlockInfo,$teamsBlockViewFns,$formBlocks);  
blockLoadOther($teamsBlockInfo,$teamsBlockViewFns,$otherBlocks);  

