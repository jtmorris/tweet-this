(function($) {
	var logMessages = [];
		
	//	Prep variables so closure scope is correct
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

	var sections;
	var inputs;
	var form;
	var preview;
	var preview_heading;
	var characters;
	var placeholder_url_warning;
	var accordionSections;

	var previewTimeout = false;


	$(window).load(function() {	//	Only execute the following after the entire page has loaded.
		//	Vars for readability
		sections = {
			'general': $('#TT_section_general'),
			'handles': $('#TT_section_handles'),
			'post_url': $('#TT_section_post_url'),
			'hidden': $('#TT_section_hidden')
		};
		inputs = {
			'text': $('#TT_tinymce_dialog_text'),
			'display_mode': $('#TT_tinymce_dialog_display_mode'),
			'url': $('#TT_tinymce_dialog_url_override'),
			'twitter_handles': $('#TT_tinymce_dialog_twitter_handle_override'),
			'hidden_hashtags': $('#TT_tinymce_dialog_hidden_hashtags_override'),
			'hidden_urls': $('#TT_tinymce_dialog_hidden_urls_override'),
			'remove_twitter_handles': $('#TT_tinymce_dialog_remove_twitter_handles'),
			'remove_url': $('#TT_tinymce_dialog_remove_url'),
			'remove_hidden_hashtags': $('#TT_tinymce_dialog_remove_hidden_hashtags'),
			'remove_hidden_urls': $('#TT_tinymce_dialog_remove_hidden_urls')
		};
		form = $('#TT-shortcode-creator-dialog form');
		preview = $('#TT_tinymce_tweet_preview');
		preview_heading = $('#TT_preview_heading');
		characters = $('#TT_tinymce_character_count');
		placeholder_url_warning = $('#TT_tinymce_preview_warning_placeholder_url');
		accordionSections = $('.TT_accordion');


		//	Possibly asynchronous, so provide callback
		var recurseDepth = 0;
		get_dialog_data(function setupShortcodeCreator(args) {
			//	If we're not on an editor page, or editor isn't available tinyMCE.activeEditor variable won't be defined, and the
			//	code below will throw an Uncaught ReferenceError.  So wrap it in a try/catch.
			try {
				logToConsole("Searching for current TinyMCE editor...")
				editor = tinyMCE.activeEditor;

				//	If editor is null, it may have not loaded completely yet... Let's try
				//	sleeping for a few seconds, then trying again. I know this is a terrible
				//	idea. But this is a potential fix for the most obscure bug on the planet
				//	that I cannot think of any better way to fix.  I hate it, but this must
				//	be done.  Don't remove this unless you really know what you're doing.
				//	
				//	Don't let this go on forever
				if(!editor) {
					if(recurseDepth < 5) {
						setTimeout(function() {
							recurseDepth++;
							logToConsole("No editor found. Retrying after delay. Retry attempt #" + recurseDepth);
							setupShortcodeCreator(args);
						}, 1000);

						return;
					}
					else {
						//	Couldn't get TinyMCE editor... throw an exception
						throw "No editor found. Tried 5 times over 5 seconds.";
					}
				}

				logToConsole("TinyMCE editor found.")
			} catch(e) {
				//	If we're here, then there's no tinyMCE and the rest of this function
				//	is pointless.  Let's, for safety, output this info to the console and then die.
				logToConsole("Loading dialog box without access to TinyMCE editor.");
				logToConsole("Page: " + window.location.href);
			}

			//	Get passed arguments
			post_id = args['post_id'];
			post_url = args['post_url'];
			post_url_is_placeholder = args['post_url_is_placeholder'];
			default_twitter_handles = args['default_twitter_handles'];
			default_hidden_hashtags = args['default_hidden_hashtags'];
			default_hidden_urls = args['default_hidden_urls'];
			
			insert_sc_behavior = args['insert_shortcode_behavior'];
			disable_preview = args['disable_preview'];
			disable_handles = args['disable_handles'];
			disable_post_url = args['disable_post_url'];
			disable_hidden = args['disable_hidden'];
			disable_char_count = args['disable_char_count'];

			


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


			//	Setup initial form state
			resetDialog();

			//	On dialog close, reset the form state
			$('#TT-shortcode-creator-dialog').bind('dialogclose', function(e) {
				resetDialog();
			})


			//	On text change, update preview
			inputs['text'].on('input keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});
			});

			//	On URL change, update preview and hide any URL warnings
			inputs['url'].on('input keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});

				if( inputs['url'].val() === post_url && post_url_is_placeholder ) {
					placeholder_url_warning.show();
				}
				else {
					placeholder_url_warning.hide();
				}
			});

			//	On Twitter handles change, update preview
			inputs['twitter_handles'].on('input keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});
			});

			//	On hidden hashtags change, update preview
			inputs['hidden_hashtags'].on('input keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});
			});

			//	On hidden URLS change, update preview
			inputs['hidden_urls'].on('input keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});
			});

			//	On remove checkbox changes, update preview
			inputs['remove_twitter_handles'].on('change keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});

				//	Also, disable the override fields for good measure
				if( inputs['remove_twitter_handles'].prop('checked') ) {
					inputs['twitter_handles'].prop('disabled', true);
				}
				else {
					inputs['twitter_handles'].prop('disabled', false);
				}
			});
			inputs['remove_url'].on('change keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});

				//	Also, disable the override fields for good measure
				if( inputs['remove_url'].prop('checked') ) {
					inputs['url'].prop('disabled', true);
				}
				else {
					inputs['url'].prop('disabled', false);
				}
			});
			inputs['remove_hidden_hashtags'].on('change keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});

				//	Also, disable the hidden hashtags box for good measure
				if( inputs['remove_hidden_hashtags'].prop('checked') ) {
					inputs['hidden_hashtags'].prop('disabled', true);
				}
				else {
					inputs['hidden_hashtags'].prop('disabled', false);
				}
			});
			inputs['remove_hidden_urls'].on('change keyup paste', function() {
				tt_preview_string(function(text) {
					preview.val(text).change();
				});

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
				var text = auto_url_shortener_length_equiv($(this).val());
				//var text = $(this).val();

				var count = text.length;
				if (count > 140) {
					var wrap = '<span style="color: red; font-size: 1.3em; ">';
					var warning = '';

					//	Too long, truncate the preview
					tt_truncate_preview();
				}
				else if (count >= 130) {
					var wrap = '<span style="color: red;">';
					var warning = '';

					//	Nearing too long, truncate the preview so URLs are shortened
					tt_truncate_preview();
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
			form.submit(function(event) {
				event.preventDefault();


				var shortcode = '[tweetthis';

				//	Do we have any handle, hashtag, or URL overrides?
				//	If so, add on parameters for them.
				if( get_clean_twitter_handles() ) {
					shortcode += ' twitter_handles="' + (get_clean_twitter_handles()) + '"';
				}
				if( get_clean_hidden_hashtags(true) ) {
					shortcode += ' hidden_hashtags="' + (get_clean_hidden_hashtags()) + '"';
				}
				if( get_clean_url() ) {
					shortcode += ' url="' + (get_clean_url()) + '"';
				}
				if( get_clean_hidden_urls(true) ) {
					shortcode += ' hidden_urls="' + (get_clean_hidden_urls()) + '"';
				}
				if( get_clean_display_mode() !== false ) {
					shortcode += ' display_mode="' + (get_clean_display_mode()) + '"';
				}

				//	Are we supposed to remove anything from this particular tweet?
				var remove_twits           = inputs['remove_twitter_handles'].prop('checked');
				var remove_url             = inputs['remove_url'].prop('checked');
				var remove_hidden_hashtags = inputs['remove_hidden_hashtags'].prop('checked');
				var remove_hidden_urls     = inputs['remove_hidden_urls'].prop('checked');

				if( remove_twits ) {
					shortcode += ' remove_twitter_handles="true"';
				}
				if( remove_url ) {
					shortcode += ' remove_url="true"';
				}
				if( remove_hidden_hashtags ) {
					shortcode += ' remove_hidden_hashtags="true"';
				}
				if( remove_hidden_urls ) {
					shortcode += ' remove_hidden_urls="true"';
				}



				shortcode += ']' + (get_clean_text()) + '[/tweetthis]';

				
				//	Insert the shortcode into the editor. 
				//	Close shortcode creator dialog
				$('#TT-shortcode-creator-dialog').dialog("close");				

				//	Manual Mode:
				if( insert_sc_behavior == 'manual' ) {
					logToConsole("Insert Shortcode Behavior set to manual.");
					//	Set dialog content to shortcode
					var html = "<p>Copy the shortcode below to your clipboard, and paste into your WordPress editor.</p><span style='font-size: 1.3em;'><strong>Created Shortcode:</strong> <input type='text' id='TT_manual_shortcode_field' style='width: 350px;' /></span>";

					var secondaryDialog = $("<div />").html(html).dialog({
						modal: true,
						title: 'Copy & Paste Your Shortcode...',
						closeText: "Close",
						width: 650,
						buttons: [
							{
								text: "Close",
								icons: {primary: 'ui-icon-closethick'},
								click: function() {
									$(this).dialog("destroy");
								}
							}
						],
						open: function() {
							//	Fix Random jQuery UI CSS Scoping Issues	//
							$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="tt-jqui" />');
						}
					});
					secondaryDialog.parent('.ui-dialog').addClass('tt-jqui');	//	jQuery UI theme scope

					$("#TT_manual_shortcode_field").val(shortcode);
				}
				else {
					//  Automatic Mode:  This shouldn't but still does fail
					//	for every one in a billion users (I may be exaggerating).  It is a problem
					//	that has plauged me since the dawn of this plugin.  Sometimes the TinyMCE
					//	editor is not accessible.  So, in those cases, let's provide a fallback.
					logToConsole("Insert Shortcode Behavior set to automatic.");
					
					try {
						editor.selection.setContent(shortcode);
						logToConsole("Successfully inserted shortcode: " + shortcode);
					} catch(e) {
						//	Yep... editor wasn't found right... damnit!
						//	The fallback is going to be replacing the dialog content with a 
						//	copy & paste the shortcode style thingy.
						logToConsole("Failed to insert shortcode. JavaScript Exception: " + e.message);
						var html = "<div id='TT_critical_error_wrapper'>";
							html += "<h2>Lucky you! You've stumbled upon a rare problem.</h2>";
							
							html += "<p>In a very small number of cases, WordPress doesn't provide crucial data to facilitate automatic insertion of your shortcode.";
							html += "This problem has plagued <em>Tweet This</em> from the beginning. Every fix, and there have been many, solves it for some users. But it invariably reappears for someone else.</p>";
							html += "<p>This problem is being actively researched by Tweet This' developer. It will hopefully be resolved soon.</p>";
							html += "<p>If you are willing, please send the developer an email with the contents of the DEBUG INFO textbox below. More information is always helpful in tracking down issues. His email address is <a href='mailto:johntylermorris@jtmorris.net?subject=Tweet This Bug Report -- TinyMCE%20activeEditor%20NULL%20Bug'>johntylermorris@jtmorris.net</a>.</p>"
							
							html += "<br /><p><strong>In the meantime</strong>, you can <strong>manually copy the shortcode</strong> in the text field below, close this dialog box, and <strong>paste the shortcode into your editor</strong>. Sincerest apologies for the inconvenience.</p>"

							html += "<br /><span style='font-size: 1.3em;'><strong>Created Shortcode:</strong> <input type='text' id='TT_manual_shortcode_field' style='width: 350px;' /></span><br /><br />";

							html += "<br /><br /><strong>DEBUG INFO:</strong><br />"
							html += "<textarea id='TT_critical_error_dbginfo' style='width: 550px'>";
							html += "Tweet This Error Report\n================================\n\n";
							html += "Reported Error:\n----------\ntinyMCE.activeEditor is null. Repeated access attempts over the course of 5 seconds yielded the same null value.";
							html += "\n\n\nUser's Web Browser:\n----------\n" + navigator.userAgent;

							html += "\n\n\nOutput Console Data:\n----------\n";

							$.each(logMessages, function(index, val) {
								html += index+1 + ' :: ' + val + '\n\n';
							});

						var secondaryDialog = $("<div />").html(html).dialog({
							modal: true,
							closeText: "Close Shortcode Creator",
							width: 650,
							buttons: [
								{
									text: "Close Shortcode Creator",
									icons: {primary: 'ui-icon-closethick'},
									click: function() {
										$(this).dialog("destroy");
									}
								}
							],
							open: function() {
								//	Fix Random jQuery UI CSS Scoping Issues	//
								$('div.ui-widget-overlay, div.ui-dialog').wrap('<div class="tt-jqui" />');
							}
						});
						secondaryDialog.parent('.ui-dialog').addClass('tt-jqui');	//	jQuery UI theme scope

						$("#TT_manual_shortcode_field").val(shortcode);
						
						//	Highlight text on focus. This can cause some errors in edge cases, so enclose
						//	it in a try/catch block
						try {
							var dbgbox = document.getElementById('TT_critical_error_dbginfo');
							dbgbox.onfocus = function() {
								dbgbox.select();

								dbgbox.onmouseup = function() {
									//	Prevent further mouseup intervention
									dbgbox.onmouseup = null;
									return false;
								};
							}
						} catch(e) {}						
					}
				}

				//	Highlight text on focus. This can cause some errors in edge cases, so enclose
				//	it in a try/catch block
				try {
					var sfbox = document.getElementById('TT_manual_shortcode_field');
					sfbox.select();
					sfbox.onfocus = function() {
						sfbox.select();

						sfbox.onmouseup = function() {
							//	Prevent further mouseup intervention
							sfbox.onmouseup = null;
							return false;
						};
					}
				} catch(e) {}
			});
		})
	});


	/***********************************************
	* Data Cleanup/Management Function Definitions *
	***********************************************/
	/**
	 * Generates and inserts into preview a string representation of what the user's Tweet This box
	 * will tweet out.
	 */
	function tt_preview_string( funcToRun ) {
		//	Reset our AJAX timeout countdown by clearing the old one, then 
		//	creating a new one.
		if( previewTimeout ) {
			clearTimeout( previewTimeout );
		}

		$("#TT_tinymce_tweet_preview").addClass("loading");
		previewTimeout = setTimeout(function() {
			tt_preview_string_helper( funcToRun );
		}, 1500);
	}
		function tt_preview_string_helper( funcToRun ) {
			var data = {};
			data.text = get_clean_text();
			data.post_id = post_id;

			//	Are we supposed to remove anything from the tweet?
			data.remove_twitter_handles = inputs['remove_twitter_handles'].prop('checked');
			data.remove_url             = inputs['remove_url'].prop('checked');
			data.remove_hidden_hashtags = inputs['remove_hidden_hashtags'].prop('checked');
			data.remove_hidden_urls     = inputs['remove_hidden_urls'].prop('checked');

			//	Was Twitter handles, default hashtags, or the URL overriden?
			//	If so, we want those.  If not, we want the defaults.
			data.custom_twitter_handles = default_twitter_handles;
			data.custom_hidden_hashtags = default_hidden_hashtags;
			data.custom_hidden_urls     = default_hidden_urls;
			data.custom_url             = '';

			if( get_clean_twitter_handles() || data.remove_twitter_handles ) {
				data.custom_twitter_handles = get_clean_twitter_handles();
			}
			if( get_clean_url() || data.remove_url ) {
				data.custom_url = get_clean_url();
			}
			if( get_clean_hidden_hashtags() || data.remove_hidden_hashtags ) {
				data.custom_hidden_hashtags = get_clean_hidden_hashtags();
			}
			if( get_clean_hidden_urls() || data.remove_hidden_urls ) {
				data.custom_hidden_urls = get_clean_hidden_urls();
			}

			//	Define actions
			data.action = 'tt_ajax';
			data.tt_action = 'get_tweet_content';

			//	Use AJAX to generate text as it will be generated in the final product
			$.ajax({
				type: "post",
				url: ajaxurl,
				dataType: 'json',
				//dataType: 'text',	//	Uncomment this and look at JS Console output to debug PHP errors
				data: data,
				success: function(retval, status) {
					logToConsole("Tweet preview content retrieved successfully.");
					logToConsole("AJAX data: " + JSON.stringify(retval));
					funcToRun(retval.data);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					logToConsole("Error getting preview content using AJAX.");
					logToConsole("AJAX error message: " + errorThrown);
				},
				complete: function() {
					//	Remove loading GIF
					$("#TT_tinymce_tweet_preview").removeClass("loading");
				}
			});
		}

	/**
	 * Truncates the text in the preview textbox to 140 characters in length.
	 */
	function tt_truncate_preview() {
		var text = preview.val();

		if( text.length > 130 ) {
			//	We're nearing a full tweet. Let's shorten the URLs visually now.
			//	Shorten URLS
			text = auto_url_shortener_length_equiv(text);
			logToConsole( text );
		}

		//	Truncate
		preview.val(text.substr(0,140));	//	First 140 characters
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

	function get_clean_display_mode() {
		dm = inputs['display_mode'].val();

		if( dm == 'box' || dm == 'button_link' ) {
			return dm;
		}
		else {
			return false;
		}
	}

	/**
	 * Replaces detected links with 22 characters.  Twitter automatically shortens links
	 * using t.co, and the resultant link is 22 characters.  For the character counter, 
	 * we need to figure that in.
	 *
	 * @param    {string}   text   The text to search for URLs in.
	 *
	 * @return   {string}          The text with all detected URLs replaced with 22 characters.
	 */
	function auto_url_shortener_length_equiv(text) {
		var matches = text.match(/\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))/gi);
		if( matches ) {
			for( var index in matches ) {
				var m = matches[index];
				text = text.replace(m, m.substr(0,18) + ' ...');
			}
		}

		return text;
	}


	/****************************************************************
	* Dialog Data Population/Setting/Resetting Function Definitions *
	*****************************************************************
	*
	* TinyMCE, the basis for the WordPress WYSIWYG editor, is a major pain-in-the-ass.
	* One of its bigger pain-in-the-ass-osities is an intermittently functional ability
	* to pass parameters to a dialog box like this one.  99.9% of the time, it works as
	* described in the manual, and ad nauseum on the Internet 
	* (http://johnmorris.me/computers/software/how-to-create-a-tinymce-editor-dialog-window-in-a-wordpress-plugin/),
	* the rest of the time that fails.  
	*
	* So, in an update done in April, 2015, the TinyMCE created dialog was scrapped in favor
	* of a jQuery dialog box.  The goal being to reduce the dealings with TinyMCE to the
	* bare minimum possible.  But, this requries some work to populate and reset the dialog
	* box each time.  Which is the purpose of the following functions.
	****************************************/


	function get_dialog_data(callback_func) {
		//	Method #1:  Use the global variable declared and stored within WordPress:
		//	/includes/setup.php: TT_Setup::hooks_helper_admin_header()
		if(typeof TT_Data != 'undefined') {
			if(typeof TT_Data != 'object') {
				//	It's unparsed JSON?
				TT_Data = JSON && JSON.parse(TT_Data) || $.parseJSON(TT_Data);
			}

			logToConsole("Shortcode creator dialog box data successfully retrieved directly.");
			callback_func(TT_Data);
			return TT_Data;
		}
		else {
			logToConsole("Error retrieving dialog box data directly.  Trying fallback AJAX method.")
		}

		//	ARGH! *face reddens*  That should have worked.
		//	Calm down... *chants* goosfraba, goosfraba, goosfraba

		//	Method #2:  Enter desparation mode.  Screw this, use AJAX to get it.  This is
		//	a holdover from this plugin's TinyMCE dialog days, where AJAX was sometime the only way
		//	to get the parameters.  Now, because we don't have any screwy iframes and separations
		//	between the WordPress page and the dialog page, this shouldn't be necessary.  It
		//	is here simply as a last ditch fallback and because I don't want to remove the 
		//	beautiful coding that went into making it work.  All the blood and sweat for nothing?
		//	Hell no, leave it in!
			
		//	Be nice and throw in a loading GIF that is removed upon AJAX completion
		//	http://stackoverflow.com/a/1964871/2523144
		$("#TT-shortcode_creator-dialog").addClass("loading");
		$.ajax({
			type: "post",
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'tt_ajax',
				tt_action: 'get_tinymce_dialog_params'
			},
			success: function(data, status) {
				//	TODO: Remove the re-JSON-ing of the data... PHP turns it to JSON, 
				//	jQuery turns it to a JavaScript var, then I turn it back into JSON.  For shame!
				logToConsole("Shortcode creator dialog box data successfully retrieved using AJAX");
				logToConsole("AJAX's received data: " + JSON.stringify(data));
				callback_func(data);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				logToConsole("Error getting shortcode creator dialog box data using AJAX.");
				logToConsole("AJAX error message: " + errorThrown);
				logToConsole("Unrecoverable error.  Reporting problem to user and dying.");

				informUser(
					"Could not retrieve necessary dialog box arguments.",
					"<p>You have just stumbled upon a critical bug! For some reason, this shortcode creator isn't getting the data it is supposed to.  Please contact me, the plugin's developer, and send me the contents in the textbox below.  Hopefully, with that information I'll have this fixed in short order!</p><p>You can find several methods for contacting me on the plugin's website: <a href='http://tweetthis.jtmorris.net/contact/' target='_blank'>http://tweetthis.jtmorris.net/contact/</a>.",
					logMessages
				);
			},
			complete: function() {
				//	Remove loading GIF
				$("#TT-shortcode_creator-dialog").removeClass("loading");
			}
		});

		return null;
	}

	function resetDialog() {
		//	Clear out any old gunk.
		//	http://stackoverflow.com/a/8937323/2523144
		form[0].reset();

		//	Disable any form field disabling
		$.each(inputs, function(index, value) {
			value.prop('disabled', false);
		});

		//	Load defaults into preview
		tt_preview_string(function(text) {
			preview.val(text);
		});

		//	Ensure all change listeners know we changed the preview
		preview.trigger('change');

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

		$("#TT-shortcode-creator-dialog").html(html);

		//	Highlight text on focus. This can cause some errors in edge cases, so enclose
		//	it in a try/catch block
		try {
			var dbgbox = document.getElementById('TT_critical_error_dbginfo');
			dbgbox.onfocus = function() {
				dbgbox.select();

				dbgbox.onmouseup = function() {
					//	Prevent further mouseup intervention
					dbgbox.onmouseup = null;
					return false;
				};
			}
		} catch(e) {}
	}

	function logToConsole(message) {
		//	Save a copy for debugging purposes.
		logMessages.push(message);

		//	Write to console.
		console.log("Tweet This :: " + message);
	}
})(jQuery);