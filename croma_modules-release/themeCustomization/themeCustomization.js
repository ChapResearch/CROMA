// http://codekarate.com/blog/adding-javascript-your-drupal-7-module

(function ($) {
    Drupal.behaviors.themeCustomization = {
	attach: function (context, settings) {
	    $(window).bind('scroll load',
		function resize() {
//		    alert("begin");
		    var container = document.getElementById("content-wrapper");
		    var footer = document.getElementById("footer-wrapper");
		    var header = document.getElementById("menu-bar-wrapper");
		    var page = document.getElementById("page-wrapper");
		    var contentSize = 0;
		    var footHeight = 0;
		    var conHeight = 0;
		    var headHeight = 0;
		    var pageHeight = 0;
		    var whiteSpace = 0;
//		    alert("variables done");
		    // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight

		    if (typeof window.innerWidth != 'undefined') {
			viewportwidth = window.innerWidth,
			viewportheight = window.innerHeight
		    }

		    // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)

		    else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
			viewportwidth = document.documentElement.clientWidth,
			viewportheight = document.documentElement.clientHeight
		    }

		    // older versions of IE

		    else {
			viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
			viewportheight = document.getElementsByTagName('body')[0].clientHeight
		    }

//		    alert("resizing");
		    footHeight = window.getComputedStyle(footer,null).getPropertyValue("height");
		    conHeight = window.getComputedStyle(container,null).getPropertyValue("height");
		    headerHeight = window.getComputedStyle(header,null).getPropertyValue("height");
		    pageHeight = window.getComputedStyle(page,null).getPropertyValue("height");
		    //whiteSpace = pageHeight - (footHeight + conHeight + headerHeight);
		    footHeight = parseInt(footHeight);
		    conHeight = parseInt(conHeight);
		    headerHeight = parseInt(headerHeight);
		    pageHeight = parseInt(pageHeight);
		    whiteSpace = parseInt(viewportheight) - parseInt(pageHeight);
//		    alert("whiteSpace: "+whiteSpace);
		    conHeight = parseInt(conHeight);
//		    alert("foot height: "+footHeight);
//		    alert("con height: "+conHeight);
//		    alert("header height: "+headerHeight);
//		    alert("page height: "+pageHeight);
//		    alert("typeof con: "+typeof conHeight);
//		    alert("view port height: "+viewportheight);
		    if(pageHeight >= viewportheight){
//			alert("greater than");
			contentSize = parseInt(conHeight);
		    } else {
//			alert("less than");
			contentSize = parseInt(conHeight) + Math.abs(parseInt(whiteSpace));
		    }
//		    alert("final con height: "+contentSize);
		    container.style.height = contentSize+"px";
		    footer.style.bottom = "0";
//		    alert("done");
		}
	    ); 
	}
    };

}(jQuery));
