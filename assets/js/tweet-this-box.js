(function($) {
	$(document).ready(function() {
		//	Open Tweet This links in popup instead of new browser window
		$('a.TT_tweet_link').unbind("click").click(function(event) {
			event.preventDefault();

			//	Received bug report regarding double popups opening. The unbind("click")
			//	and this event.stopPropagation() are here to make sure this is only
			//	fired ONCE!
			event.stopPropagation();

			var url = $(this).attr('href');
			var windowName = "Tweet This";
			var windowSize = "width=500,height=450";

			window.open(url, windowName, windowSize);
		});
	});
}(jQuery))
