(function ($) {
  Drupal.behaviors.addRow = {
    attach: function (context, settings) {
	$('.add-row-bttn').click(
		function() {
		    var nextRow = $(this).parent().parent().next();
		    if (nextRow.length !== 0){ // check if this is the last allowed row
			$(this).hide();
			nextRow.show();
		    } else {
			alert('You have reached the maximum number of rows!');
		    }
		}
	);
    }
  };
}(jQuery));
