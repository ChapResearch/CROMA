(function ($) {
  Drupal.behaviors.contact = {
    attach: function (context, settings) {
	$('.contact-link-block').click(
		function() { if($(this).find('a').length) { window.location = $(this).find('a').attr('href'); } }
	);
    }
  };
}(jQuery));

