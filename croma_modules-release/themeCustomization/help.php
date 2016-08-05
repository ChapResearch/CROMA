<?php

/*
  ---- themeCustomization/help.php ----

  used to toggle help icon

  - Contents -
  displayHelp() - allows a user to show or hide various tooltips
*/

function displayHelp(){
  $markup = '';

  // javascript to toggle the help icon, calls four tooltip classes
  $javascript = "
              
	       var y;
               var x;
               var i;
               for (y = 1; y<=4; y++) {
                   x = document.getElementsByClassName('tooltiptext' + y);
                   for (i = 0; i < x.length; i++) {
                        if(x[i].style.visibility == 'visible'){
                           x[i].style.visibility = 'hidden';
                        } else {
                           x[i].style.visibility = 'visible';
                           x[i].style.display = 'block';
                        }
                    }
                }
                 ";
  
  // icon image
  $markup .= "<input type=\"image\" class=\"helpIcon\" src=\"/images/icons/smallQuestionMarkWhite.png\" onclick=\"$javascript\">";

  return $markup;
}

?>