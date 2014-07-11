(function($) {
	$(document).ready(function() {
		//	Open Tweet This links in popup instead of new browser window
		$('a.TT_tweet_link').click(function(event) {
			event.preventDefault();
			
			var url = $(this).attr('href');
			var windowName = "Tweet This";
			var windowSize = "width=500,height=450";

			window.open(url, windowName, windowSize);
		});
	});
}(jQuery))