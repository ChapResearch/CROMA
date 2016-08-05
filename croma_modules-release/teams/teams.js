(function ($) {
  Drupal.behaviors.addRow = {
    attach: function (context, settings) {
	$('.switchTeamBttn').click(
		function() {
		    var TID = $(this).attr("id");
		    alert(TID);
		    $.post("switchTeam.php", {"TID": TID}, function(response){alert(response);});
		}
	);
    }
  };
}(jQuery));
