<?php
include_once("drupalCompatibility.php");
include_once("croma_dbFunctions.php");

function populateTestData()
{
  dbCreateTeam(array("name" => "Team Appreciate", "number" => "2468", "type" => "FRC", "city" => "1000 Westbank Austin TX 78746", "state" => "Texas", "country" => "United States", "isActive" => true));
  dbCreateTeam(array("name" => "The Poofs", "number" => "254", "isActive" => true));
  dbCreateTeam(array("name" => "RobotTots", "number" => "118", "isActive" => true));
  dbCreateTeam(array("name" => "Katies Team", "number" => "3310", "isActive" => true));

  dbCreateProfile(array("firstName"=>"Philip", "lastName"=>"Liu", "bio"=>"Philip Liu is a sophomore at Westlake High School. He has been highly involved with STEM programs since a young age and has developed many skills such as leadership, communication, and perseverance. In Chap Research, Philip is currently working on two different projects. The first is an all hardware project called the Chap Measuring Press (CMP) while the second is the Chap Research Outreach Management Application (CROMA). He is a member of FRC 2468 Team Appreciate and works in many sub-systems. In addition to working with the robot design, CAD, and manufacturing, he manages the documentation and portfolio for the outreach team. With all these generous opportunities, he uses his knowledge (so far) to mentor FTC teams. Outside of robotics and school, Philip practices TaeKwonDo (a soon-to-be instructor), dances, and plays football, basketball, and fantasy with his friends. In order to balance his time with a laughing and happy manner, his favorite quote is 'where there is a will, there is a way.'", "position"=>"CROMA Admin", "phone"=>"512-925-2241", "grade"=>"10", "gender"=>"Male"));
  dbCreateProfile(array("firstName"=>"Parker", "lastName"=>"Bergen"));
  dbCreateProfile(array("firstName"=>"Rachel", "lastName"=>"Gardner"));
  dbCreateProfile(array("firstName"=>"Lewis", "lastName"=>"Jones"));

  dbAssignUserToTeam("2","1");
  dbAssignUserToTeam("1","1");
  dbAssignUserToTeam("3","1");

  dbApproveUser("1","1");
  dbApproveUser("3", "2");
  dbApproveUser("2", "1");

  dbCreateOutreach(array("name"=>"CROMA Prep", "status"=>"isOutreach", "logDate" => date("Y-m-d H:i:s", time()),"TID" =>"1", "address" => "my house", "description" => "An event"));
  dbCreateOutreach(array("name"=>"SXSW", "status"=>"isOutreach", "logDate" => time(), "address" => "my house", "description" => "An event"));
  dbCreateOutreach(array("name"=>"Barnes and Nobles", "status"=>"isOutreach", "logDate" => time(), "address" => "my house", "description" => "An event", "TID" => "1"));
  dbCreateOutreach(array("name"=>"CROMA Prep", "status"=>"isOutreach", "TID" => "1"));
  dbCreateOutreach(array("name"=>"NI Week", "status"=>"isIdea", "TID" => "1"));
  dbCreateOutreach(array("name"=>"Robot Fair","description"=>"Promoting STEM education to the community", "type"=>"Ripple Effect", "status"=>"isIdea"));
  dbApproveEvent("1");

  dbAssignUserToOutreach("1","1");
  dbAssignUserToOutreach("1","2");
  dbAssignUserToOutreach("1","3");
  dbAssignUserToOutreach("1","4");
  dbAssignUserToOutreach("2","2");

  dbLogHours(array("UID" => "1", "OID" => "1", "numberOfHours" => "12", "type" => "Pre"));
  dbLogHours(array("UID" => "2", "OID" => "1", "numberOfHours" => "15", "type" => "Post"));

  dbAddMedia(array("OID" => null,"title" => "test", "UID" => "1",  "description" => "fbsjdfjsdg", "dateEntered" => time(), "link" =>"https://pbs.twimg.com/profile_images/447374371917922304/P4BzupWu.jpeg"));
  dbAddMedia(array("OID" => null,"UID" => "1", "description" => "YO HOMEDAWG", "dateEntered" => time(), "link" =>"http://croma.chapresearch.com/sites/default/files/CROMA%20Logo%20v1_0.png"));
  dbAddMedia(array("OID" => "1", "UID" => "1", "description" => "Ni Hao", "dateEntered" => time(), "link" =>"http://chapresearch.com/wp-content/uploads/2015/08/William-e1439428977920.jpg"));


  dbAddMedia(array("OID" => "2", "description" => "Philip likes Chap Research"));
  dbAddMedia(array("OID" => null, "UID" => "1", "title" => "FUNNY"));

  dbAddEmails("1", array("parkerj@lol.com"));
  dbAddEmails("1", array("roar@lol.com"));
  dbAddEmails("2", array("philipj@lol.com"));
  dbAddEmails("2", array("test@lol.com"));

  dbAddTimesToOutreach(array("OID" => "1", "startTime" => "5/25/15", "endTime" => "5/26/15"));
  dbAddTimesToOutreach(array("OID" => "2", "startTime" => "1/25/15", "endTime" => "3/26/15"));
  dbAddTimesToOutreach(array("OID" => "2", "startTime" => "9/2/15", "endTime" => "11/12/15"));
  dbAddTimesToOutreach(array("OID" => "4", "startTime" => "1/3/15", "endTime" => "3/28/15"));

  dbAddNotification(array("TID" => "1", "UID" => "1", "date" => "11/10/15", "title" => "Dell Family Day", "message" => "This event will take place on Saturday, November 28th, at the J.W. Marriott hotel in Downtown Austin. We need all Robowranglers to attend."));
  dbAddNotification(array("TID" => "4", "UID" => "1", "date" => "15/4/15", "title" => "Barnes and Noble", "message" => "This event will take place on Saturday, November 7th at the Barnes and Noble in the Hill Country Galleria. See Roger Newton for details."));
  dbAddNotification(array("TID" => "3", "UID" => "1", "date" => "12/1/15", "title" => "Freescale Marathon", "message" => "Informal event... Show up on December 31st to wish the runners at the finish line a Happy New Year!"));
}

populateTestData();

?>
