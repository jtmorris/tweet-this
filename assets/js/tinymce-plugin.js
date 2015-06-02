/**
 * Adds a button to the TinyMCE toolbar which opens a jQuery dialog box created elsewhere.
 */
console.log("Tweet This :: TinyMCE Plugin File Loaded");
	
(function($) {
	$(document).ready(function() {
		tinymce.create('tinymce.plugins.tweetthis', {
			init: function(ed, url) {
				console.log("Tweet This :: TinyMCE Plugin Init");
				//	Now, create a button that opens the dialog.
				ed.addButton('tweetthis_button', {
					title: 'Add Tweet This Box',
					image: url + '/../images/tinymce-button.png',
					onclick: function() {
						$('#TT-shortcode-creator-dialog').dialog("open");
					}
				});
			}
		});
		tinymce.PluginManager.add('tweetthis', tinymce.plugins.tweetthis);
	});
})(jQuery);
