<?php

/* db_createOutreachTable() - creates the table of outreach events, which includes information entered at all stages of an outreach event (planning, during and post-mortem).
*/ 
function db_createOutreachTable($con)
{

  $sql="CREATE TABLE outreach
                  (
                    OID                INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,                   name               CHAR(50)      NOT NULL,
                    UID                INT           , # the owner of the outreach
                    description        VARCHAR(999)  ,
                    logDate            DATETIME      NOT NULL, # note that the time(s) of the event are separate
                    type               CHAR(20)      ,
                    TID                INT           NOT NULL,
                    status             CHAR(20)      , # can be isIdea, isOutreach, doingWriteUp or locked
                    testimonial        VARCHAR(999)  ,
                    totalAttendence    INT           ,
                    peopleImpacted     INT           ,
                    co_organization    CHAR(20)      ,
                    co_position        CHAR(20)      ,
                    co_firstName       CHAR(30)      ,
                    co_lastName        CHAR(30)      ,
                    co_email           CHAR(30)      ,
                    co_phoneNumber     CHAR(30)      ,
                    city               CHAR(25)      ,
                    state              CHAR(25)      ,
                    address            CHAR(30)      ,
                    country            CHAR(25)      ,
                    cancelled          BOOLEAN       DEFAULT 0,
                    picture            INT           ,   
                    isPublic           BOOLEAN       DEFAULT 0,
                    writeUp            VARCHAR(999)  ,
                    isWriteUpApproved  BOOLEAN       DEFAULT 0,
                    writeUpUID         INT           ,
                    isWriteUpSubmitted BOOLEAN       DEFAULT 0
                )";
   if(!execute($con,$sql,"Table 'outreach' create")) {
       return (false);
     }
     return (true);
}

/* db_createMediaTable() - creates the table of media, which includes information of assigning pictures to outreach events. 
*/

function db_createMediaTable($con)
{

  $sql="CREATE TABLE media
                  (
                    MID               INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,  
                    FID               INT           ,
                    OID               INT           , # associated outreach
                    UID               INT           , # user who uploaded image
                    title             CHAR(50)      ,
                    description       VARCHAR(500)  ,
                    link              VARCHAR(1000) , # usually not necessary
                    isPicture         BOOLEAN       ,
                    dateEntered       DATETIME      , # when the picture was entered into the database
                    isApproved        BOOLEAN  
                  )";
  if(!execute($con,$sql,"Table 'media' create")) {
       return (false);
     }
     return (true);
}

/* db_createOutreachTagsTable() - creates the table listing each team's possible tags for outreach
*/
function db_createOutreachTagsTable($con)
{

  $sql="CREATE TABLE outreachTags
                  (
                    OTID              INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,  
                    TID               INT           NOT NULL,
                    tagName           CHAR(50)      NOT NULL
                  )";
  if(!execute($con,$sql,"Table 'outreachTags' create")) {
       return (false);
     }
     return (true);
}  

/* db_createTagsVsOutreachTable() - creates the table linking tags with outreach
*/
function db_createTagsVsOutreachTable($con)
{

  $sql="CREATE TABLE tagsVsOutreach
                  (
                    TOID              INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,  
                    OID               INT           NOT NULL, # identifies the outreach
                    OTID              INT           NOT NULL  # identifies the tag
                  )";
  if(!execute($con,$sql,"Table 'tagsVsOutreach' create")) {
       return (false);
     }
     return (true);
}  

/* db_createHourCountingTable() - creates the table to count hours, which includes information for individual users.
*/

function db_createHourCountingTable($con)
{

  $sql="CREATE TABLE hourCounting
                 (
                   HID               INT            NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                   UID               INT            ,
                   OID               INT            NOT NULL,
                   numberOfHours     INT            NOT NULL,
                   description       VARCHAR(100)   ,
                   isApproved        BOOLEAN        DEFAULT 0, # whether the hours should be counted
                   type              CHAR(30)       # can be prep, writeUp, old etc.                        
                 )"; 
  if(!execute($con,$sql,"Table 'hourCounting' create")) {
       return (false);
    }
     return (true);
}

/*  db_createUsersVsOutreachTable - manages relations between users and outreaches
 */

function db_createUsersVsOutreachTable($con)
{

  $sql="CREATE TABLE usersVsOutreach
                (
                  UOID               INT            NOT NULL PRIMARY KEY AUTO_INCREMENT,
                  UID                INT            NOT NULL,
                  OID                INT            NOT NULL,
                  type               CHAR(20)       #can be prep, atEvent, writeUp
                )";
  if(!execute($con, $sql,"Table 'usersVsOutreach' create")) {
        return (false);
      }
      return (true);
}       

/* db_createProfilesTable() - creates the profile table, which includes all users registered with CROMA (including various profile information).
*/  

function db_createProfilesTable($con)
{

  $sql="CREATE TABLE profiles
                  (
                    PID            INT           NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                    UID            INT           , # key to Drupal user
                    firstName      CHAR(20)      NOT NULL,
                    lastName       CHAR(20)      NOT NULL,
                    FID            INT           , # key for profile picture
                    bio            VARCHAR(2000) ,
                    position       CHAR(25)      , # ex: programmer
                    phone          CHAR(30)      ,
                    grade          TINYINT       ,
                    gender         CHAR(10)      , 
                    type           CHAR(10)        # ex: student, mentor, alumni
                  )";
  
     if(!execute($con,$sql,"Table 'profiles' created")) {
       return (false);
     }
     return (true);
}

/* db_createTeamsTable() - creates teams that are saved in the CROMA database
*/

function db_createTeamsTable($con)
{

  $sql="CREATE TABLE teams
                  (
                   TID              INT        NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   name             CHAR(50)   NOT NULL,
                   number           CHAR(10)   ,
                   city             CHAR(20)   ,
                   state            CHAR(20)   ,
                   type             CHAR(10)   ,
                   country          CHAR(25)   ,
                   isApproved       BOOLEAN    ,
                   rookieYear       INT        ,
                   FID              INT        , # key to drupal file containing picture
                   UID              INT        ,
                   isActive         BOOLEAN    DEFAULT TRUE
                   )";

     if(!execute($con,$sql,"Table 'teams' create")) {
        return (false);
     }
     return (true);
}

/* dbCreateUsersVsTeams () - manages the relation between users and teams they are associated with
 */

function db_createUsersVsTeamsTable($con)
{
 
  $sql="CREATE TABLE usersVsTeams
                  (
                   UTID             INT         NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UID              INT         NOT NULL,
                   TID              INT         NOT NULL,
                   isApproved       BOOLEAN     DEFAULT FALSE,
                   userEmail        VARCHAR(50) , # the email the user wants to be contacted at for follow-up
                   userMessage      VARCHAR(500), # the custom message entered when applying for the team
                   isDefault        BOOLEAN     # whether this is the default team to operate under
                  )";

     if(!execute($con,$sql,"Table 'usersVsTeams' create")) {
        return (false);
     }
     return (true);
}

/* dbCreateEmailsVsUsersTable () - manages emails associated with a user
 */

function db_createEmailsVsUsersTable($con)
{
  $sql="CREATE TABLE emailsVsUsers
                  (
                   EUID             INT         NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UID              INT         NOT NULL,
                   email            VARCHAR(50) NOT NULL
                  )";

      if(!execute($con,$sql,"Table 'emailsVsUsers' create")) {
       	return (false);
      }
      return (true);
}

/* dbCreateTimesVsOutreachTable () - manages the amount of time for an outreach
 */

function db_createTimesVsOutreachTable($con)
{
  $sql="CREATE TABLE timesVsOutreach
                  (
                   TOID           INT         NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   OID            INT         NOT NULL,
                   startTime      DATETIME    NOT NULL,
                   endTime        DATETIME    
                  )";

  if(!execute($con,$sql,"Table 'timesVsOutreach' create"))
    {
      return (false);
    }
    return (true);
}

/* dbCreateNotifications () - creates notifications for users
 */

function db_createNotificationsTable($con)
{
  $sql="CREATE TABLE notifications
                  (
                   NID            INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UID            INT          ,
                   TID            INT          ,
                   OID            INT          , # optional
                   dateCreated    DATETIME     NOT NULL,
                   dateTargeted   DATETIME     NOT NULL,
                   message        VARCHAR(500) ,
                   bttnTitle      VARCHAR(50)  , # the title of the bttn
                   bttnLink       VARCHAR(100) # link to use (follow to act upon a notification
                  )";

  if(!execute($con,$sql,"Table 'notifications' create"))
    {
      return (false);
    }
    return (true);
}

/* dbCreatePermissions() - creates all the possible permissions users can have
 */

function db_createPermissionsTable($con)
{
  $sql="CREATE TABLE permissions
                  (
                   UPID           INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   name           VARCHAR(30)  # name of permission (aka editTeam)
                  )";

  if(!execute($con,$sql,"Table 'permissions' create"))
    {
      return (false);
    }
    return (true);
}

/* dbCreateRoles() - creates all the possible roles users can have
 */

function db_createRolesTable($con)
{
  $sql="CREATE TABLE roles
                  (
                   RID            INT               NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   name           VARCHAR(30),      # internal name of role (aka teamAdmin)
                   displayName    VARCHAR(30)       # name to be shown to user (aka Team Admin)
                  )";

  if(!execute($con,$sql,"Table 'roles' create"))
    {
      return (false);
    }
    return (true);
}

/* dbcreatePermissionsVsRolesTable - creates the table to link various permissions to each role
 */

function db_createPermissionsVsRolesTable($con)
{
  $sql="CREATE TABLE permissionsVsRoles
                  (
                   PRID            INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UPID            INT          NOT NULL, # user permission
                   RID             INT          NOT NULL # role
                  )";

  if(!execute($con,$sql,"Table 'permissionsVsRoles' create"))
    {
      return (false);
    }
    return (true);
}

/* dbcreateUsersVsRolesTable - creates the table to link various permissions to each role
 */

function db_createUsersVsRolesTable($con)
{
  $sql="CREATE TABLE usersVsRoles
                  (
                   URID            INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UID             INT          NOT NULL, # user
                   TID             INT          NOT NULL, # team
                   RID             INT          NOT NULL # role
                  )";

  if(!execute($con,$sql,"Table 'usersVsRoles' create"))
    {
      return (false);
    }
    return (true);
}

/* dbCreateOldHoursVsTeamsTable() - creates the table to link various permissions to each role
 */

function db_createOldHoursVsTeamsTable($con)
{
  $sql="CREATE TABLE oldHoursVsTeams
                  (
                   HTID            INT          NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   TID             INT          NOT NULL,
                   year            INT          NOT NULL,
                   numberOfHours   INT          NOT NULL
                   )";

  if(!execute($con,$sql,"Table 'oldHoursVsTeams' create"))
    {
      return (false);
    }
    return (true);
}

?>