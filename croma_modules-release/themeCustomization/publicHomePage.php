<?php

/*
  ---- themeCustomization/publicHomePage.php ----

  functions to display various data on the home page shown when not logged in

  - Contents -
  publicHomePageStatistics() - generates a table of overall CROMA stats
  publicHomePageCRLogo() - generates code to display the Chap Research logo
  publicHomePageFRCLogo() - generates code to display the FRC 2468 logo
  publicHomePageInfo() - generates code to display the image showing general CROMA info
*/

function publicHomePageStatistics()
{
  // getting the jquery script from a host
  $markup = "<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.js\"></script>";
  $markup .= "<script src=\"numberCounting.js\"></script>";

  // opening table
  $markup .=  '<table id="publicHomePageStatistics" style="width:1200px">';

  // displaying number of hours logged through CROMA in total
  $markup .=  "<tr style='font-size:15pt; font-family: \"Open Sans\", sans-serif;'><td class=\"title\" style=\"text-align:center\"><h3><b>Hours Logged</b></h3></td>";
  // displaying number of outreaches logged through CROMA in total
  $markup .=  "<td class=\"title\" style=\"text-align:center\"><h3><b>Outreaches Created</b></h3></td>";
  // displaying number of teams that have registered through CROMA in total
  $markup .=  "<td class=\"title\" style=\"text-align:center\"><h3><b>Teams Registered</b></h3></td></tr>";

  // displaying number of hours logged through CROMA in total
  $markup .=  "<tr style='color:#8E2115; font-size:30pt; font-family: \"Open Sans\", sans-serif;'><td class=\"number\" style=\"text-align:center\"><h3><div class=\"countUp\">";
  $markup .= dbGetTotalHours() . "</div></h3></td>";

  // displaying number of outreaches logged through CROMA in total
  $markup .=  "<td class=\"number\" style=\"text-align:center\"><h3><div class=\"countUp\">";
  $markup .= dbGetNumTotalOutreach() . "</div></h3></td>";  

  // displaying number of teams that have registered through CROMA in total
  $markup .=  "<td class=\"number\" style=\"text-align:center\"><h3><div class=\"countUp\">";
  $markup .= dbGetNumTotalTeams() . "</div></h3></td></tr>";

  $markup .= "</table>";

  // closing array and returning it to display the data above for the user
  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

function publicHomePageCRLogo()
{
  // displaying the Chap Research logo (logo/photo is hyperlinked)
  $markup = '<table style="width:172px"><tr><td><a href="http://chapresearch.com" target="_blank"><img src="/images/homePage/CRLogoNoText.png" style="width:172px; height:75px;"></a></td></tr></table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

function publicHomePageFRCLogo()
{
  // displaying the FRC 2468 Team Appreciate logo (logo/photo is hyperlinked)
  $markup = '<table style="float:right;"><tr><td style="text-align:right"><a href="http://frc2468.org" target="_blank"><img src="/images/homePage/2468HomePageLogo.png" id="CROMAOverview" style="width:160px; height:75px;"></a></td></tr></table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

function publicHomePageCROMALogo()
{
  $markup = '<table><tr><td>';

  // displaying the CROMA logo
  $markup .= '<center><a href="http://CROMA.ChapResearch.com" target="_blank"><img src="/images/homePage/CROMALogo.jpg" style="width:360px; height:75px;"></a></center></td></tr></table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;
}

// displays the capabilities of CROMA for a user, team and outreach
function publicHomePageInfo()
{
  $markup = '<table style="width:800px" id="publicHomePageInfo">';
  $markup .= '<tr><td><img src="/images/homePage/homePageInfo.jpg">';
  $markup .= '</td></tr></table>';

  $retArray = array();
  $retArray['#markup'] = $markup;

  return $retArray;

}

?>