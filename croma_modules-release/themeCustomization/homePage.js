(function ($) {
    $(document).ready(function(){
	var data;
	var x = $.post('homeStats.php', {}, function(retVal){
	    data = jQuery.parseJSON(retVal);
	});
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
