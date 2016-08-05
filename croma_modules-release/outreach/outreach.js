(function ($) {
/*  Drupal.behaviors.contact = {
    attach: function (context, settings) {
	$('.contact-link-block').click(
		function() { if($(this).find('a').length) { window.location = $(this).find('a').attr('href'); } }
	);
    }
  };*/
// old code to play with automatically adding new tags when no search results are found
/*    Drupal.behaviors.contact = {
	attach: function (context, settings) {
	    $("#states_field").chosen({
	    no_results_text: '<b><a href="google.com">Click to add tag: </a></b>'});
	    $("#states_field").bind("chosen:no_results",function(event){
		var newOption = $("#states_field_chosen .search-field input").val();
		$('.chosen-results').append($('<li>', {
		    class: 'active-result',
		    value: newOption,
		    text: newOption
		}));
		alert(newOption);
		$("#states_field").trigger("chosen:updated");
	    });
	}
    };*/
}(jQuery));

