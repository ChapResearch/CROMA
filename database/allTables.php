<?php

/* db_createOutreachTable() - creates the table of outreach events, which includes information entered at all stages of an outreach event (planning, during and post-mortem).
*/ 
function db_createOutreachTable($con)
{

  $sql="CREATE TABLE outreach
                  (
                    OID               INT           NOT NULL PRIMARY KEY AUTO_INCREMENT,  
                    name              CHAR(50)      NOT NULL,
                    description       VARCHAR(300)  ,
                    logDate           DATETIME      NOT NULL, # note that the time(s) of the event are separate
                    peopleImpacted    INT           ,
                    testimonial       VARCHAR(300)  ,
                    type              CHAR(20)      ,
                    TID               INT           NOT NULL,
                    status            CHAR(20)      , # can be isIdea, isOutreach, doingWriteUp or locked
                    co_organization   CHAR(20)      ,
                    co_position       CHAR(20)      ,
                    co_firstName      CHAR(30)      ,
                    co_lastName       CHAR(30)      ,
                    co_email          CHAR(30)      ,
                    co_phoneNumber    CHAR(20)      ,
                    city              CHAR(25)      ,
                    state             CHAR(25)      ,
                    address           CHAR(30)      ,
                    country           CHAR(25)      ,
                    peopleReached     INT           ,
                    cancelled         BOOLEAN  
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
                    title             CHAR(30)      ,
                    description       CHAR(50)      ,
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

/* db_createHourCountingTable() - creates the table to count hours, which includes information for individual users.
*/

function db_createHourCountingTable($con)
{

  $sql="CREATE TABLE hourCounting
                 (
                   HID               INT            NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                   UID               INT            NOT NULL,
                   OID               INT            NOT NULL,
                   numberOfHours     INT            NOT NULL,
                   description       VARCHAR(100)           ,
                   type              CHAR(30)                                
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
                  type               CHAR(20)       ,#can be prep, atEvent, writeUp
                  isOwner            BOOLEAN        #indicates that the person created the event
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
                    UID            INT           NOT NULL PRIMARY KEY AUTO_INCREMENT, 
                    firstName      CHAR(20)      NOT NULL,
                    lastName       CHAR(20)      NOT NULL,
                    FID            INT           , # key for profile picture
                    bio            VARCHAR(2000) ,
                    position       CHAR(25)      ,
                    phone          CHAR(15)      ,
                    grade          TINYINT       ,
                    gender         CHAR(10)       
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
                   MID              INT        , # key to media table (gets logo or whatever)
                   isActive         BOOLEAN    
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
                   UTID             INT        NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UID              INT        NOT NULL,
                   TID              INT        NOT NULL,
                   isApproved       BOOLEAN       
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
                   EUID             INT        NOT NULL PRIMARY KEY AUTO_INCREMENT,
                   UID              INT        NOT NULL,
                   email            CHAR(20)   NOT NULL
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
                   date           DATETIME     NOT NULL,
                   title          CHAR(50)     ,    
                   message        VARCHAR(200)    
                  )";

  if(!execute($con,$sql,"Table 'notifications' create"))
    {
      return (false);
    }
    return (true);
}

?>