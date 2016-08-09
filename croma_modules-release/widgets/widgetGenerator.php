<?php

/*
  ---- widgets/widgetGenerator.php ----

  used to present various widget options to the user (showing the HTML for each)

  - Contents -
  widgetGenerator() - page showing widget possibilities (with description and preview)
*/

function widgetGenerator()
{
  $markup = '';
  
  $teamNumber = getCurrentTeam()['number'];

  $markup .= '<table><tr><td colspan="2" style="width:50%"><div class="help tooltip2"><h2>Create Widgets</h2><span id="helptext"; class="helptext tooltiptext2">Widgets provide information that teams can display on their own webpages.</span></div></td><td style="text-align:right">';

  // allow users to navigate to page to manage outreach visibilities (for outreach directory widget)
  $markup .= '<a href= "?q=outreachVisibilities">';
  $markup .= '<div class="help tooltip1"><button type="button">Outreach Visibilities</button><span id="helptext"; class="helptext tooltiptext1">Click here to manage your teams public and private outreaches.</span></div></a></td></tr></table>';  

  $markup .= '<table><tr><th colspan="4"></th></tr><tr><td><h2>1</h2></td>';

  // show 'static statistics' widget; this is the image generated to have the correct numbers on it
  $markup .= '<td colspan="2"><table><tr><td colspan="3"><h4><b>Static Statistics</b></h4>This is the quick and simple way of displaying up-to-date data on your teams outreach. Just display the image at the URL below, and we will dynamically generate it to show your latest statistics.</td></tr>';
  $markup .= '<tr><td><b>Preview:</b><br><img src="http://croma.chapresearch.com'.PORT;
  $markup .='/basicStatsImage.php?teamNumber='.$teamNumber.'"></td>';
  $markup .= "<td><b>Your Custom HTML:</b><br>&lt;img src=\"http://croma.chapresearch.com".PORT;
  $markup .= "/basicStatsImage.php?teamNumber=$teamNumber\"&gt;</td></tr></table></td>";

  $markup .= '</tr><tr style="border-top:1pt solid black;"><td><h2>2</h2></td>';

  // show 'fancy statistics' widget; this is the version that uses an iframe to having scrolling numbers
  $markup .= "<td colspan=\"2\"><table><tr><td colspan=\"3\"><h4><b>Fancy Statistics Widget</b></h4>Just paste in the HTML below, and your statistics will count up automatically. However, note that for a small percentage of users this widget may not work out of the box (depending on website configurations). The \"basic\" back-up option is above in case you don't feel like fiddling with it.</td></tr>";
  $markup .= '<tr><td style="width:400px"><b>Preview:</b><br><iframe style="overflow:hidden; border:none; width:400px; height:200px" scrolling="no" ';
  $markup .= 'src="http://croma.chapresearch.com'.PORT.'/fancyScrollingStats.html?teamNumber='.$teamNumber.'"></iframe></td>';
  $markup .= "<td><b>Your Custom HTML:</b><br>&lt;iframe style=\"overflow:hidden; border:none; width: 400px; height: 200px\" scrolling=\"no\" src=\"http://croma.chapresearch.com".PORT."/fancyScrollingStats.html?teamNumber=$teamNumber\"&gt;</iframe\></td></tr></table></td>";

  $markup .= '</tr></table>';

  return array('#markup'=>$markup);
}

?>