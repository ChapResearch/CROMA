<?php

include_once ("/var/www-croma/database/croma_dbFunctions.php");
function viewTeamOutreach()
{
  $data = dbGetOutreachesForTeam("1");
  $showAll = false;
  $markup = 'Team Outreaches';
  $markup .='<table id="t01">';
  //  dpm($data);
  $markup .='<div align="right"><button type="button" onClick=""> All Outreaches</button></div>';  
  $markup .='<tr><td>Name</td><td>Description</td><td>Type</td><td>Hours</td>';
  
  foreach($data as $outreach)  {
      $OID = $outreach["OID"];
      //      dpm($OID); 

      if($outreach["status"] == "isOutreach")
	{
	  $status = "Outreach";
	  $markup .= '<tr><td style = "vertical-align: middle;"><a href="http://croma.chapresearch.com/?q=viewOutreach&OID=' .$outreach["OID"] . '"</a>'; 

	  $markup .= $outreach["name"] . '</td>';
	  $markup .='<td style = "vertical-align: middle;">' . $outreach["description"] . '</td>';
	  $markup .='<td style = "vertical-align: middle;">' . $status . '</td>';
	  $markup .='<td style = "vertical-align: middle;">' . dbGetHoursForOutreach($OID);
	  $markup .='</tr>';

	}

      elseif ($outreach["status"] == "isIdea" && $showAll == true)
	{
	  $status = "isIdea";
	  $markup .= '<tr><td style = "vertical-align: middle;"><a href="http://croma.chapresearch.com/?q=viewOutreach&OID=' .$outreach["OID"] . '"</a>'; 

	  $markup .= $outreach["name"] . '</td>';
	  $markup .='<td style = "vertical-align: middle;">' . $outreach["description"] . '</td>';
	  $markup .='<td style = "vertical-align: middle;">' . $status . '</td>';
	  $markup .='<td style = "vertical-align: middle;">' . dbGetHoursForOutreach($OID);
	  $markup .='</tr>';
	}
    }
      

  $markup .='</table>';
  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

?>