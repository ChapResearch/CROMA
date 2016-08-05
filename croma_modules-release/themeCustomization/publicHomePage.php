<?php

include_once(DATABASE_FOLDER.'/croma_dbFunctions.php');

function publicHomePageStatistics()
{

  // getting the jquery script from a host
  $markup = "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.js\"></script>";
  $markup .= "<script src=\"numberCounting.js\"></script>";

  // opening table
  $markup .=  '<table id="publicHomePageStatistics" style="width:300px">';

  // displaying number of hours logged through CROMA in total
  $markup .=  "<tr><td class=\"title\" style=\"text-align:center\"><h3>Hours Logged Through CROMA</h3></td>";
  $markup .=  "<td class=\"number\" style=\"text-align:center\"><h3><div class=\"countUp\">";
  $markup .= dbGetTotalHours() . "</div></h3></td></tr>";
  
  // displaying number of outreaches logged through CROMA in total
  $markup .=  "<tr><td class=\"title\" style=\"text-align:center\"><h3>Outreaches Created Through CROMA</h3></td>";
  $markup .=  "<td class=\"number\" style=\"text-align:center\"><h3><div class=\"countUp\">";
  $markup .= dbGetNumTotalOutreach() . "</div></h3></td></tr>";
  
  // displaying number of teams that have registered through CROMA in total
  $markup .=  "<tr><td class=\"title\" style=\"text-align:center\"><h3>Teams Registered Through CROMA</h3></td>";
  $markup .=  "<td class=\"number\" style=\"text-align:center\"><h3><div class=\"countUp\">";
  $markup .= dbGetNumTotalTeams() . "</div></h3></td></tr>";

  // closing the table
  $markup .= "</table>";

  // closing array and returning it to display the data above for the user
  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

function publicHomePageLogos()
{

  // displaying the Chap Research and FRC 2468 Team Appreciate logos (logos/photos are hyperlinked)
  $markup = '<table style="width:370px"><tr><td><a href="http://chapresearch.com" target="_blank"><img src="/images/homePage/CRLogo.png" style="width:200px; height:75px;"></a></td>';
  $markup .= '<td><a href="http://frc2468.org" target="_blank"><img src="/images/homePage/2468HomePageLogo.jpg" id="CROMAOverview" style="width:200px; height:75px;"></a></td></tr></table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

function publicHomePageCROMALogoAndInfo()
{

  $markup = '<table style="width:700px"><tr><td>';

  // displaying the CROMA logo
  $markup .= '<center><a href="http://CROMA.ChapResearch.com" target="_blank"><img src="/images/homePage/CROMALogo.png" style="width:390px; height:87px;"></a></center>';

  $markup .= '</td></tr>';

  $markup .= '<tr><td>';

  $markup .= '<img src="/images/homePage/CROMASummary.jpg" style="width:774px; height:671px;">'; // displaying the CROMA informational "sheet"

  $markup .= '</td></tr></table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

?>