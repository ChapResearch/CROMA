
/*
  ---- widgets/fancyScrollingStats.js ----

  used to animate the numbers in the 'fancy stats' widget
*/

(function ($) {

    // quick function to pull info from the url
    function getURLParameter(sParam)
    {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split('&');
	for (var i = 0; i < sURLVariables.length; i++) 
	{
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam) 
            {
		return sParameterName[1];
            }
	}
    }

    $(document).ready(function(){
	// get and parse the stats for the team number
	var teamNumber = getURLParameter('teamNumber');
	var data;
	var url = 'http://croma.chapresearch.com:' + window.location.port + '/fancyScrollingStats.php';
	var x = $.post(url, {teamNumber:teamNumber}, function(retVal){
	    data = jQuery.parseJSON(retVal);
	});

	// once that data has been received, animate the numbers
	// note that this function fills in specific HTML elements in fancyScrollingStats.html
	$.when(x).then(function(){
	    $({countNum: 0}).animate({countNum: data.numHours}, {
		duration: 1500,
		easing:'linear',
		step: function() {
		    $('#numHours').text(Math.floor(this.countNum));
		},
		complete: function(){
		    $('#numHours').text(this.countNum);
		}
	    });
	    $({countNum: 0}).animate({countNum: data.numOutreaches}, {
		duration: 1500,
		easing:'linear',
		step: function() {
		    $('#numOutreaches').text(Math.floor(this.countNum));
		},
		complete: function(){
		    $('#numOutreaches').text(this.countNum);
		}
	    });
	    $({countNum: 0}).animate({countNum: data.numTeams}, {
		duration: 1500,
		easing:'linear',
		step: function() {
		    $('#numTeams').text(Math.floor(this.countNum));
		},
		complete: function(){
		    $('#numTeams').text(this.countNum);
		}
	    });
	    $('#teamName').text(data.teamName);
	});
    });

})(jQuery);
