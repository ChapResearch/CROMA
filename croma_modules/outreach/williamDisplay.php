<?php

function viewUpcomingOutreach() 
{ 
  $markup = '
<table id="01">
<tr><th colspan="2" style="text-align: center"><b>MY EVENTS</b></th></tr>';
  $outreaches = dbGetApprovedOutreachForUser("1");
  
  foreach($outreaches as $e) {
    $markup .= '
<tr>
<td style="width: 30%">
<table>
<tr><td><b>' . $e['TID'] . '</b></td></tr>' .
      //<tr><td><img src="' . $e['team-logo'] . '" height="64" width="64"></td></tr>
'<tr><td><button type="button">Contribute</button></td></tr>
</table>
</td>
<td style="width: 70%">
<table>
<tr><td><b>' . '<a href="http://croma.chapresearch.com/?q=viewOutreach&OID=' . $e['OID'] .  ' "  target="_blank">' . $e['name'] . '</a>' .  '</b></td></tr>
<tr><td>' . $e['logDate'] . '</td></tr>
<tr><td>' . $e['address'] . '</td></tr>
<tr><td>' . $e['description'] . '</td></tr>
</table>
</td>
</tr>
';
  }
  
  $markup .= '</table>';

  $retArray = array();
  $retArray['#markup'] = $markup;
  return $retArray;
}

function viewOutreachEvent($OID) {
  $outreach = dbGetOutreach($OID);
  $markup = '<table>';
  $markup .= '<tr><td>Name: ' . $outreach['name'] . '</td>';
  $markup .= '<td>Date: ' . $outreach['date'] . '</td></tr>';
  $markup .= '<tr colspan="2"><td>Description:' . $outreach['description'] . '</td></tr>';
  $markup .= '<tr colspan="2"><td><b>Location<b></td></tr>';
  $markup .= '<tr colspan="2"><td>Address: ' . $outreach['city'] . '</td></tr>';
  $markup .= '<tr><td>Contact: ' . $outreach['co_firstName'] . ' ' . $outreach['co_lastName'] . '</td>';
  $markup .= '<td>Email: ' . $outreach['co_email'] . '</td></tr>';

  if($outreach['status'] == 'completed') {
    $markup .= '<tr><td>People Reached: ' . $outreach['peopleImpacted'] . '</td>';
    $markup .= '<td>Total Hours: ' . dbGetHoursForOutreach($OID) . '</td></tr>';
  }
}

?>