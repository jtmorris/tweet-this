(function($) {
	logMessages = [];

	//	Vars for readability
	var sections = {
		'general': $('#TT_section_general'),
		'handles': $('#TT_section_handles'),
		'post_url': $('#TT_section_post_url'),
		'hidden': $('#TT_section_hidden')
	};
	var inputs = {
		'text': $('#TT_tinymce_dialog_text'),
		'url': $('#TT_tinymce_dialog_url_override'),
		'twitter_handles': $('#TT_tinymce_dialog_twitter_handle_override'),
		'hidden_hashtags': $('#TT_tinymce_dialog_hidden_hashtags_override'),
		'hidden_urls': $('#TT_tinymce_dialog_hidden_urls_override'),
		'remove_twitter_handles': $('#TT_tinymce_dialog_remove_twitter_handles'),
		'remove_url': $('#TT_tinymce_dialog_remove_url'),
		'remove_hidden_hashtags': $('#TT_tinymce_dialog_remove_hidden_hashtags'),
		'remove_hidden_urls': $('#TT_tinymce_dialog_remove_hidden_urls')
	};
	var form = $('#TT_tinymce_dialog form');
	var preview = $('#TT_tinymce_tweet_preview');
	var preview_heading = $('#TT_preview_heading');
	var characters = $('#TT_tinymce_character_count');
	var placeholder_url_warning = $('#TT_tinymce_preview_warning_placeholder_url');
	var accordionSections = $('.TT_accordion');


	//	Prep passed argument variables so closure scope is correct
	var post_url;
	var post_url_is_placeholder;
	var default_twitter_handles;
	var default_hidden_hashtags;
	var default_hidden_urls;
	var editor;
	
	var disable_preview;
	var disable_handles;
	var disable_hidden;
	var disable_char_count;


	$(document).ready(function() {
		//	Possibly asynchronous, so provide callback
		get_tinymce_params(function(args) {
			//	Get passed arguments
			post_url = args['post_url'];
			post_url_is_placeholder = args['post_url_is_placeholder'];
			default_twitter_handles = args['default_twitter_handles'];
			default_hidden_hashtags = args['default_hidden_hashtags'];
			default_hidden_urls = args['default_hidden_urls'];
			editor = args['editor'];
			
			disable_preview = args['disable_preview'];
			disable_handles = args['disable_handles'];
			disable_post_url = args['disable_post_url'];
			disable_hidden = args['disable_hidden'];
			disable_char_count = args['disable_char_count'];

			


			//	Load defaults into preview
			preview.val(tt_preview_string());

			//	Display any caveats or warning about the preview
			if ( post_url_is_placeholder && !disable_preview ) {
				placeholder_url_warning.show();
			}



			//	Setup the layout of the dialog
			if( disable_preview ) {
				preview.hide();
				preview_heading.hide();
			}

			if( disable_char_count ) {
				characters.hide();
			}
			
			if( disable_handles ) {
				sections.handles.parent('.TT_accordion').remove();
			}

			if( disable_post_url ) {
				sections.post_url.parent('.TT_accordion').remove();
			}

			if( disable_hidden ) {
				sections.hidden.parent('.TT_accordion').remove();
			}




			//	Accordion the sections
			//	We have multiple accordions for each section because when
			//	submitting the form, we want to be able to expand ALL accordion
			//	sections so that validation errors are visible.  Therefore,
			//	each section is an accordion with only one entry.  It looks
			//	the same, but allows multiple open sections.
			accordionSections.accordion({
				active: false,
				collapsible: true,
				heightStyle: 'content'
			});
			//	Open the general content accordion
			accordionSections.first().accordion("option", "active", 0);



			//	On text change, update preview
			inputs['text'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();
			});

			//	On URL change, update preview and hide any URL warnings
			inputs['url'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();

				if( inputs['url'].val() === post_url && post_url_is_placeholder ) {
					placeholder_url_warning.show();
				}
				else {
					placeholder_url_warning.hide();
				}
			});

			//	On Twitter handles change, update preview
			inputs['twitter_handles'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();
			});

			//	On hidden hashtags change, update preview
			inputs['hidden_hashtags'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();
			});

			//	On hidden URLS change, update preview
			inputs['hidden_urls'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();
			});

			//	On remove checkbox changes, update preview
			inputs['remove_twitter_handles'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();

				//	Also, disable the override fields for good measure
				if( inputs['remove_twitter_handles'].prop('checked') ) {
					inputs['twitter_handles'].prop('disabled', true);
				}
				else {
					inputs['twitter_handles'].prop('disabled', false);
				}
			});
			inputs['remove_url'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();

				//	Also, disable the override fields for good measure
				if( inputs['remove_url'].prop('checked') ) {
					inputs['url'].prop('disabled', true);
				}
				else {
					inputs['url'].prop('disabled', false);
				}
			});
			inputs['remove_hidden_hashtags'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();

				//	Also, disable the hidden hashtags box for good measure
				if( inputs['remove_hidden_hashtags'].prop('checked') ) {
					inputs['hidden_hashtags'].prop('disabled', true);
				}
				else {
					inputs['hidden_hashtags'].prop('disabled', false);
				}
			});
			inputs['remove_hidden_urls'].on('change keyup paste', function() {
				preview.val(tt_preview_string()).change();

				//	Also, disable the hidden hashtags box for good measure
				if( inputs['remove_hidden_urls'].prop('checked') ) {
					inputs['hidden_urls'].prop('disabled', true);
				}
				else {
					inputs['hidden_urls'].prop('disabled', false);
				}
			});


			//	Character counter and preview truncate
			preview.on('change', function() {
				//	Counter
				var count = $(this).val().length;
				if (count > 140) {
					var wrap = '<span style="color: red; font-size: 1.3em; ">';

					//	Too long, truncate the preview
					tt_truncate_preview();
				}
				else if (count >= 130) {
					var wrap = '<span style="color: red;">';
					var warning = '';
				}
				else if (count >= 120) {
					var wrap = '<span style="color: darkred;">';
					var warning = '';
				}
				else {
					var wrap = '<span>'
					var warning = '';
				}
				characters.html(wrap + (140-count) + '</span> characters left ' + warning);
			});



			//	When submit button is clicked, expand all accordions so any
			//	HTML5 validation errors are visible.
			$('input[type="submit"]').click(function() {
				accordionSections.accordion("option", "active", 0);
			});

			//	Insert the shortcode when submitted
			// form.submit(function(event) {
			// 	event.preventDefault();


			// 	var shortcode = '[tweetthis';

			// 	//	Do we have any handle, hashtag, or URL overrides?
			// 	//	If so, add on parameters for them.
			// 	if( get_clean_twitter_handles() ) {
			// 		shortcode += ' twitter_handles="' + (get_clean_twitter_handles()) + '"';
			// 	}
			// 	if( get_clean_hidden_hashtags(true) ) {
			// 		shortcode += ' hidden_hashtags="' + (get_clean_hidden_hashtags()) + '"';
			// 	}
			// 	if( get_clean_url() ) {
			// 		shortcode += ' url="' + (get_clean_url()) + '"';
			// 	}
			// 	if( get_clean_hidden_urls(true) ) {
			// 		shortcode += ' hidden_urls="' + (get_clean_hidden_urls()) + '"';
			// 	}

			// 	//	Are we supposed to remove anything from this particular tweet?
			// 	var remove_twits           = inputs['remove_twitter_handles'].prop('checked');
			// 	var remove_url             = inputs['remove_url'].prop('checked');
			// 	var remove_hidden_hashtags = inputs['remove_hidden_hashtags'].prop('checked');
			// 	var remove_hidden_urls     = inputs['remove_hidden_urls'].prop('checked');

			// 	if( remove_twits ) {
			// 		shortcode += ' remove_twitter_handles="true"';
			// 	}
			// 	if( remove_url ) {
			// 		shortcode += ' remove_url="true"';
			// 	}
			// 	if( remove_hidden_hashtags ) {
			// 		shortcode += ' remove_hidden_hashtags="true"';
			// 	}
			// 	if( remove_hidden_urls ) {
			// 		shortcode += ' remove_hidden_urls="true"';
			// 	}



			// 	shortcode += ']' + (get_clean_text()) + '[/tweetthis]';

			// 	editor.selection.setContent(shortcode);
			// 	editor.windowManager.close();
			// });
		})
	});



	/***********************************************
	* Data Cleanup/Management Function Definitions *
	***********************************************/
	/**
	 * Generates and returns a string representation of what the user's Tweet This box
	 * will tweet out.
	 *
	 * @return   {string}   String representation of the resultant tweet.
	 */
	function tt_preview_string() {
		var text = inputs['text'].val();

		//	Are we supposed to remove anything from the tweet?
		var remove_twits           = inputs['remove_twitter_handles'].prop('checked');
		var remove_url             = inputs['remove_url'].prop('checked');
		var remove_hidden_hashtags = inputs['remove_hidden_hashtags'].prop('checked');
		var remove_hidden_urls     = inputs['remove_hidden_urls'].prop('checked');

		//	Was Twitter handles, default hashtags, or the URL overriden?
		//	If so, we want those.  If not, we want the defaults.
		var twits = default_twitter_handles;
		var hashtags = default_hidden_hashtags;
		var hidden_urls = default_hidden_urls;
		var url = post_url;

		if( get_clean_twitter_handles() || remove_twits ) {
			twits = get_clean_twitter_handles();
		}
		if( get_clean_url() || remove_url ) {
			url = get_clean_url();
		}
		if( get_clean_hidden_hashtags() || remove_hidden_hashtags ) {
			hashtags = get_clean_hidden_hashtags();
		}
		if( get_clean_hidden_urls() || remove_hidden_urls ) {
			hidden_urls = get_clean_hidden_urls();
		}

		var retval = text;
		if(hashtags) {
			retval += ' ' + hashtags;
		}					
		if ( hidden_urls ) {
			retval += ' ' + hidden_urls;
		}
		if ( url ) {
			retval += ' ' + url;
		}
		if (twits) {
			retval +=' via ' + twits;
		}

		return retval;
	}

	/**
	 * Truncates the text in the preview textbox to 140 characters in length.
	 */
	function tt_truncate_preview() {
		preview.val(preview.val().substr(0,140));	//	First 140 characters
	}

	/**
	 * Returns the text portion of the tweet after any and all cleanup and/or sanitization has been run.
	 *
	 * @return   {string}   The text for the tweet.
	 */
	function get_clean_text() {
		return inputs['text'].val();
	}

	/**
	 * Returns the URL for the post after any and all cleanup and/or sanitization has been run.
	 *
	 * @return   {string}    The URL for the post/page.
	 */
	function get_clean_url() {
		if(inputs['remove_url'].prop('checked')) {
			return "";
		}

		return encodeURI( $.trim(inputs['url'].val()) ).
			replace(/"/g, '').	//	remove double quotes
			replace(/'/g, '');	//	remove single quotes;
	}

	/**
	 * Returns the Twitter handles to include after any and all cleanup and/or sanitization has been run.
	 *
	 * @return   {string}    The twitter handles.
	 */
	function get_clean_twitter_handles() {
		if(inputs['remove_twitter_handles'].prop('checked')) {
			return "";
		}

		return $.trim(inputs['twitter_handles'].val()).
			replace(/"/g, '').	//	remove double quotes
			replace(/'/g, '');	//	remove single quotes
	}

	/**
	 * Returns any hashtags to include after any and all cleanup and/or sanitization has been run.
	 *
	 * @param    {boolean}   for_output   As this content is hidden, in some circumstances, 
	 * we only want a value here if content is for outputting.
	 *
	 * @return   {string}                The hashtags.
	 */
	function get_clean_hidden_hashtags(for_output) {					
		if(inputs['remove_hidden_hashtags'].prop('checked')) {
			return "";
		}

		//	Do we have any override?
		if( $.trim(inputs['hidden_hashtags'].val()) ) {
			var cont = inputs['hidden_hashtags'].val();
		}
		//	Do we have any defaults? If so, and this isn't for output, return them.
		else if( !for_output && $.trim(default_hidden_hashtags) ) {
			var cont = default_hidden_hashtags;
		}
		//	Nothing
		else {
			var cont = '';
		}

		return $.trim(cont).
			replace(/"/g, '').	//	remove double quotes
			replace(/'/g, '');	//	remove single quotes;
	}
	/**
	 * Returns any hidden URLS to include after any and all cleanup and/or sanitization has been run.
	 *
	 * @param    {boolean}   for_output   As this content is hidden, in some circumstances, 
	 * we only want a value here if content is for outputting.
	 *
	 * @return   {string}                The URLs.
	 */
	function get_clean_hidden_urls(for_output) {
		if(inputs['remove_hidden_urls'].prop('checked')) {
			return "";
		}

		//	Do we have any override?
		if( $.trim(inputs['hidden_urls'].val()) ) {
			var cont = inputs['hidden_urls'].val();
		}
		//	Do we have any defaults?  If so, and this isn't for output, return them.
		else if( !for_output && $.trim(default_hidden_urls) ) {
			var cont = default_hidden_urls;
		}
		//	Nothing
		else {
			var cont = '';
		}

		return encodeURI( $.trim(cont) ).
			replace(/"/g, '').	//	remove double quotes
			replace(/'/g, '');	//	remove single quotes;
	}


	/****************************************
	* TinyMCE Handling Function Definitions *
	*****************************************
	*
	* TinyMCE, the basis for the WordPress WYSIWYG editor, is a major pain-in-the-ass.
	* One of its bigger pain-in-the-ass-osities is an intermittently functional ability
	* to pass parameters to a dialog box like this one.  99.9% of the time, it works as
	* described in the manual, and ad nauseum on the Internet 
	* (http://johnmorris.me/computers/software/how-to-create-a-tinymce-editor-dialog-window-in-a-wordpress-plugin/),
	* the rest of the time that fails.  Unfortunately, we need passed parameters... so, what 
	* should be a simple one-line piece of code, is instead a lengthy exercise in workarounds 
	* and exception catching.  These functions do that lengthy bullsh... er... stuff so the 
	* main code up top can look pretty.
	****************************************/


	function get_tinymce_params(callback_func) {
		/*
		 * Pain-in-the-ass #1:
		 * --------------------------
		 * According to the TinyMCE documentation, the appropriate method for extracting
		 * parameters/arguments passed to a TinyMCE dialog box is like this:
		 * 		var args = top.tinymce.activeEditor.windowManager.getParams();
		 * 	The only problem is, that fails miserably on occasion.
		 *
		 * In addition, this requires the user's website and the dialog box to be served
		 * from the same EXACT domain and subdomain, or security exceptions will be thrown
		 * by modern web browsers.  Some strange redirection methodologies and God only knows
		 * what settings lead to subtle differences in the domains (e.g. www.domain.com and domain.com)
		 * that can't easily be remedied here.
		 *
		 * So, we have multiple vectors for failure and we need to workaround them all.  In 
		 * addition, it would be very nice to have some method of locating the point of failure
		 * and analyzing what worked and didn't work in case this isn't solved.  This means
		 * exception checking and error reporting out the ass.  
		 * 
		 * So, let's get started.  Begin by cycling through the options.  If one works, run the callback,
		 * return the passed arguments, and get the hell out of here.  Otherwise, report it and
		 * try the next.
		 */
		

		 var args = null;
		 var dbginfo = null;

		 var testMethodNum = 9;	//	Used for debugging and testing. Set this to the method# 
		 						//	you want to test and the methods before it will simulate failure.
		 						//	Set to 0 when not debugging or testing.

		//	Method #1:  Do it the prescribed way
		try {
			if(testMethodNum > 1) {
				throw "Failing for debugging purposes.";
			}
			
			var args = top.tinymce.activeEditor.windowManager.getParams();
			callback_func(args);
			return args;
		} catch (e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = top.tinymce.activeEditor.windowManager.getParams();'.  Trying parent instead of top.");						
		}


		//	Method #2:  Try using parent instead of top... That has fixed problems for some
		try {
			if(testMethodNum > 2) {
				throw "Failing for debugging purposes.";
			}
			
			var args = parent.tinymce.activeEditor.windowManager.getParams();
			logToConsole("Successfully obtained parameters using 'var args = parent.tinymce.activeEditor.windowManager.getParams();'.");
			callback_func(args);
			return args;
		} catch (e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = parent.tinymce.activeEditor.windowManager.getParams();'.  Switching to brute force method.");
		}


		//	Damnit.  This is bullcrap.  THIS ISN'T SUPPOSED TO HAPPEN!  Well okay, let's try getting
		//	dirty and brute force the damn thing.

		//	Method #3:  Use parent to access a two-deep iframe
		try {
			if(testMethodNum > 3) {
				throw "Failing for debugging purposes.";
			}
			
			var args = parent.parent.tinymce.activeEditor.windowManager.getParams();
			logToConsole("Successfully obtained parameters using 'var args = parent.parent.tinymce.activeEditor.windowManager.getParams();'.");
			callback_func(args);
			return args;
		} catch(e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = parent.parent.tinymce.activeEditor.windowManager.getParams();'  Trying brute force method #2")
		}
		

		//	Method #4:  Use parent to access a three-deep iframe		
		try {
			if(testMethodNum > 4) {
				throw "Failing for debugging purposes.";
			}
			
			var args = parent.parent.parent.tinymce.activeEditor.windowManager.getParams();
			logToConsole("Successfully obtained parameters using 'var args = parent.parent.parent.tinymce.activeEditor.windowManager.getParams();'.");
			callback_func(args);
			return args;
		} catch(e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = parent.parent.parent.tinymce.activeEditor.windowManager.getParams();'  Trying brute force method #3")
		}



		//	OMFG!  I hate TinyMCE.  Have I said that lately?  Okay, the prescribed way,
		//	the alternative prescribed way, and two unlikely modifications to the alternative
		//	way failed.  Now, let's cheat.
		//	
		//	Being a smart, but very angry programmer, I had the foresight (actually I went
		//	back and added it later, so it wasn't foresight, but foresight sounds better)
		//	to build in a backdoor that omits the need for everything TinyMCE related.
		//	
		//	All the parameters were written to a variable in the *cringe* global scope in
		//	an object so all we need to do is bust out of this iframe and get the top window.
		//	If we can do that, then access that object and we're golden.  No TinyMCE.
		//	The only way this can go wrong is if somebody overwrites the object or some
		//	annoying security policy, like the cross-domain blocking discussed above, gets
		//	in the way.  But what are the odds of that happening right?

		//	Method #5:  Access global parameter object using top
		try {
			if(testMethodNum > 5) {
				throw "Failing for debugging purposes.";
			}
			
			var args = window.top.tweetthis_tinymce_args;
			logToConsole("Successfully obtained parameters using 'var args = window.top.tweetthis_tinymce_args;'.");
			callback_func(args);
			return args;
		} catch (e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = window.top.tweetthis_tinymce_args;'.  Trying brute force method #4.'")						
		}

		//	Oh come on!

		//	Method #6:  Access global parameter object using parent
		try {
			if(testMethodNum > 6) {
				throw "Failing for debugging purposes.";
			}
			
			var args = window.parent.tweetthis_tinymce_args;
			logToConsole("Successfully obtained parameters using 'var args = window.parent.tweetthis_tinymce_args;'.");
			callback_func(args);
			return args;
		} catch (e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = window.parent.tweetthis_tinymce_args;'.  Trying brute force method #5.")
		}

		//	You can't be serious....

		//	Method #7:  Enter desparation mode... try two deep frames?
		try {
			if(testMethodNum > 7) {
				throw "Failing for debugging purposes.";
			}
			
			var args = window.parent.parent.tweetthis_tinymce_args;
			logToConsole("Successfully obtained parameters using 'var args = window.parent.parent.tweetthis_tinymce_args;'.");
			callback_func(args);
			return args;
		} catch (e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = window.parent.parent.tweetthis_tinymce_args;'.  Trying brute force method #6.")
		}

		//	FUCK!

		//	Method #8:  In for a penny, in for a pound.  Engage hyper-desparation mode.  Three deep frames?
		try {
			if(testMethodNum > 8) {
				throw "Failing for debugging purposes.";
			}
			
			var args = window.parent.parent.parent.tweetthis_tinymce_args;
			logToConsole("Successfully obtained parameters using 'var args = window.parent.parent.parent.tweetthis_tinymce_args;'.");
			callback_func(args);
			return args;
		} catch (e) {
			logToConsole("Error getting parameters passed to dialog with 'var args = window.parent.parent.parent.tweetthis_tinymce_args;'.  Trying AJAX method.")
		}

		//	ARGH! *face reddens*
		//	Calm down... *chants* goosfraba, goosfraba, goosfraba

		//	Method #9:  Enter mega-hyper-desparation mode.  Screw this, use AJAX to get it.
		//	Be nice and throw in a loading GIF that is removed upon AJAX completion
		//	http://stackoverflow.com/a/1964871/2523144
		$("#tt-frame-body").addClass("loading");
		$.ajax({
			type: "post",
			url: '../../../../wp-admin/admin-ajax.php',
			dataType: 'json',
			data: {
				action: 'tt_ajax',
				tt_action: 'get_tinymce_dialog_params'
			},
			success: function(data, status) {
				logToConsole("AJAX Data Retrieved: " + JSON.stringify(data));
				callback_func(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				logToConsole("Error getting parameters using AJAX. Error message: " + errorThrown + ".  Out of ideas.  Reporting problem to user and dying.");

				informUser(
					"Could not retrieve necessary dialog box arguments.",
					"<p>You have just stumbled upon a very rare WordPress editor bug. I have been unable to locate the root cause, but, with your help, I can probably work around it.</p><p>Here's the problem:  Your WordPress editor is not providing data to this shortcode creator <a href='http://www.tinymce.com/wiki.php/Tutorials:Creating_custom_dialogs' target='_blank'>like it is supposed to</a>.  This has happened to a few other plugin users, but the circumstances are slightly different in each case, so I can only react when it occurs, not prevent it.</p><p>Working around this instance of the problem will require a little bit of contextual information (automatically gathered below). Please send me, Tweet This' developer, an <strong>email</strong>, and copy and paste the <strong>data in the text box below</strong> into your message. You can find a contact form for sending an email on <a href='http://tweetthis.jtmorris.net/contact/' target='_blank'>the plugin's website</a>.</p><p>I'm very sorry for the trouble.  As I said, this is a very rare and unpredictable WordPress problem.</p>",
					logMessages
				);
			},
			complete: function() {
				//	Remove loading GIF
				$("#tt-frame-body").removeClass("loading");
			}
		});
	}


	/****************************
	* Tool Function Definitions *
	****************************/
	function informUser(errorMessage, apologyMessage, debuggingInfo) {
		if (typeof debuggingInfo == 'string' || debuggingInfo instanceof String ) {
			//	Turn it into a single entry array of strings to make later easier.
			debuggingInfo = [debuggingInfo];
		}

		var html = "<div id='TT_critical_error_wrapper'>";
			html += "<h2>Lucky you! You've found an unrecoverable error.</h2>";
			html += "<p class='TT_error'><strong>ERROR:</strong> " + errorMessage + "</p>";
			
			html += apologyMessage;

			html += "<textarea id='TT_critical_error_dbginfo'>";
			html += "Tweet This Error Report\n================================\n\n";
			html += "Reported Error:\n----------\n" + errorMessage;
			html += "\n\n\nUser Message:\n----------\n" + apologyMessage;
			html += "\n\n\nUser's Web Browser:\n----------\n" + navigator.userAgent;
			html += "\n\n\n",
			html += "Collected Debugging Information:\n----------\n";

			$.each(debuggingInfo, function(index, val) {
				html += index+1 + ' :: ' + val + '\n\n';
			});

			html += "</textarea>";
		html += "</div>"

		$("body").html(html);

		//	Highlight text on focus
		var dbgbox = document.getElementById('TT_critical_error_dbginfo');
		dbgbox.onfocus = function() {
			dbgbox.select();

			dbgbox.onmouseup = function() {
				//	Prevent further mouseup intervention
				dbgbox.onmouseup = null;
				return false;
			};
		}
	}

	function logToConsole(message) {
		//	Save a copy for debugging purposes.
		logMessages.push(message);

		//	Write to console.
		console.log("Tweet This :: " + message);
	}
})($);