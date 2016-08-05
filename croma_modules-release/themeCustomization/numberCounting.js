(function ($) {
    $(document).ready(function(){
	$(".countUp").each(function(){
	    var data = $(this).text();
	    var temp = $(this);
	    $.when(data).then(function(){
		$({countNum: 0}).animate({countNum: data}, {
		    queue: false,
		    duration: 1000,
		    easing:'linear',
		    step: function() {
			temp.text(Math.floor(this.countNum));
		    },
		    complete: function(){
			temp.text(this.countNum);
		    }
		});
		temp.text(data);
	    });
	});
    });
})(jQuery);
    
