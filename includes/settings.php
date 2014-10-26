<?php
/**
 * This file contains a static-esque class with all the settings page
 * content and handlers.
 */

require_once( TT_ROOT_PATH . "includes/tools.php" );

if ( !class_exists( 'TT_Settings' ) ) {
	class TT_Settings {
		//	Setup all the preliminary junk for using the Settings API
		//	Good resource link here: http://goo.gl/rg7gZC
		public static function define_settings() {
			register_setting(
				'tweet_this_options',	//	Option Group Name
				'tt_plugin_options',	//	Option Name in DB
				array ('TT_Settings', 'validation_helper')	//	Validation func
			);

			add_settings_section(
				'tweet_this_general',		//	Section ID
				'General',					//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_general' ),	//	Content callback
				TT_FILENAME					//	The page
			);

			add_settings_section(
				'tweet_this_url',		//	Section ID
				'URL Settings',					//	Heading for section
				array( 'TT_Settings',
					'section_content_helper_url'),	//	Callback to output section content
				TT_FILENAME						//	The page
			);

			add_settings_section(
				'tweet_this_dialog',			//	Section ID
				'Shortcode Creator Settings',			//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_dialog' ),	//	Callback to output section content
				TT_FILENAME						//	The page
			);

			add_settings_section(
				'tweet_this_theme',			//	Section ID
				'Theme Settings',			//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_advanced' ),	//	Callback to output section content
				TT_FILENAME						//	The page
			);


			add_settings_field(
				'tt_default_twitter_handles',			//	Setting ID
				'Default Twitter Handles', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_textbox' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_general',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'default_twitter_handles',

					//	Help text displayed below field
					'help_text'=>'Comma separated list of "via" Twitter handles you want added to your tweets (leave blank for none). <br />Example: <span class="tt_admin_example">@jt_morris, @DTELinux, @CraigyFerg</span>'
				)
			);


			add_settings_field(
				'tt_twitter_icon',			//	Setting ID
				'Twitter Icon',				//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME	,				//	The page
				'tweet_this_theme',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'twitter_icon',

					'buttons' => TT_Tools::get_twitter_images_as_radio_array(),

					'help_text' => 'Choose an icon to display next to the Tweet This link.',

					'default' => 'bird1'
				)
			);

			add_settings_field(
				'tt_hide_promotional_byline',		//	Setting ID
				'Hide Promotional Byline?',	//	Setting Title

				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_general',	//	Settings Section ID

				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'hide_promotional_byline',
					'id'=> 'tt_byline_removal',

					//	Array of label=>value pairs for desired buttons
					'buttons'=>array( array('Yes', true), array('No', false) ),

					'default'=>false,

					//	Help text displayed below field
					'help_text'=>'Choose "Yes" to remove the "Powered by Tweet This" byline.'
				)
			);

			add_settings_field(
				'tt_use_shortlink',			//	Setting ID
				'Use Shortlink?', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_url',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'use_shortlink',

					//	Array of arrays of label=>value pairs for desired buttons
					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text'=>'Your Shortlink Format: <span class="tt_admin_example">' . wp_get_shortlink( TT_Tools::get_id_of_last_post() ) . '</span><br /><a href="http://tweetthis.jtmorris.net/posts/beginners-guide-to-wordpress-shortlinks/" target="_blank">Read this article</a> for tips on customizing your shortlinks.'
				)
			);

			add_settings_field(
				'tt_disable_url',			//	Setting ID
				'Disable URLs', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_url',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'disable_url',

					//	Array of arrays of label=>value pairs for desired buttons
					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text'=>'Choose yes to remove your URL from tweets by default.',

					'default'=>false
				)
			);

			add_settings_field(
				'tt_disable_preview',							//	Setting ID
				'Disable Preview?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_preview',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the preview of your tweet in the shortcode creator?',

					'default' => false
				)
			);


			add_settings_field(
				'tt_disable_advanced',							//	Setting ID
				'Disable Advanced Options?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_advanced',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the advanced options in the shortcode creator?',

					'default' => false
				)
			);


			add_settings_field(
				'tt_disable_char_count',							//	Setting ID
				'Disable Character Counter?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_char_count',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the characters left counter?',

					'default' => false
				)
			);


			add_settings_field(
				'tt_theme',							//	Setting ID
				'Choose Theme',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_theme',					//	Settings Section ID
				array(
					'name' => 'base_theme',

					'buttons' => TT_Tools::get_themes_as_radio_array(),

					'help_text' => 'Choose the base theme for "Tweet This" boxes. NOTE: The images are not representative of actual size!',

					'default' => 'light'
				)
			);

			add_settings_field(
				'tt_css_override',					//	Setting ID
				'Override CSS',						//	Setting Title

				array( 'TT_Settings',
					'field_helper_textarea' ),		//	Content Callback
				TT_FILENAME,						//	The page
				'tweet_this_theme',				//	Settings Section ID

				//	Arguments for callback
				array(
					'name'=>'css_override',

					'help_text'=>'Override default CSS rules for "Tweet This" box.',

					'style'=>'min-width: 500px; min-height: 300px;'
				)
			);
		}
			public static function validation_helper( $input ) {
				return $input;
			}
			public static function section_content_helper_general() {

			}
			public static function section_content_helper_url() {
				?>
				<p>
					Customize the URL automatically generated for each tweet box.
				</p>
				<?php
			}
			public static function section_content_helper_shortlinks() {
				?>
				<p>
					If the default shortlinks created by WordPress aren't satisfactory,
					and you don't use a shortlink generating plugin like
					Jetpack
					or WP Bitly, the settings below might help.
				</p>
				<p>
					If your shortlink system relies on the WordPress permalink
					or the post ID, this can work.  Enter your shortlink domain,
					and the permalink structure below.
				</p>
				<p>
					<a href='' target='_blank'>Read this article</a>
					for more information!
				</p>
				<?php
			}
			public static function section_content_helper_advanced() {

			}
			public static function section_content_helper_dialog() {
				?>
				<p>
					Customize the Tweet This Shortcode Creator dialog box.
				</p>
				<?php
			}
			public static function field_helper_radio( $args ) {
				//	Get currently stored options
				$options = get_option( 'tt_plugin_options' );

				//	Parse $args for readability
				$name = $args['name'];
				$buttons = $args['buttons'];

				//	These $args are optional, so make sure they exist!
				array_key_exists('help_text', $args) ?
					$help_text = $args['help_text'] : $help_text = '';
				array_key_exists('default_value', $args) ?
					$default_value = $args['default_value'] : $default_value = '';
				array_key_exists('style', $args) ?
					$style = $args['style'] : $style = '';
				array_key_exists('id', $args) ?
					$id = $args['id'] : $id = '';


				//	Is there already a stored value in the database?
				$stored_value = $options[$name];

				//	If $stored_value is empty, use the default value
				if ( empty( $stored_value ) && $stored_value !== false ) {
					$stored_value = $default_value;
				}


				//	Now, loop through buttons and output them
				echo "<div class='tt_radio_button_wrap' id='$id'>";
				foreach ( $buttons as $button ) {
					$label = $button[0];
					$value = $button[1];

					//	Is this button the currently set setting?
					$checked = ($stored_value == $value) ? 'checked="checked" ' : '';

					//	Output the field
					echo "<label><input " . $checked . " value='$value' name='tt_plugin_options[$name]' type='radio' style='$style' /> $label</label><br />";
				}
				echo "</div>";

				//	And now the help text
				echo "<p class='tt_admin_help_text'>$help_text</p>";
			}
			public static function field_helper_textbox( $args ) {
				//	Get currently stored options
				$options = get_option( 'tt_plugin_options' );

				//	Parse $args for readability
				$name = $args['name'];

				//	These $args are optional, so make sure they exist!
				array_key_exists('help_text', $args) ?
					$help_text = $args['help_text'] : $help_text = '';
				array_key_exists('default_value', $args) ?
					$default_value = $args['default_value'] : $default_value = '';
				array_key_exists('style', $args) ?
					$style = $args['style'] : $style = '';
				array_key_exists('id', $args) ?
					$id = $args['id'] : $id = '';


				//	Is there already a stored value in the database?
				$stored_value = $options[$name];

				//	If $stored_value is empty, use the default value
				if ( empty( $options[$name] ) && $stored_value !== false ) {
					$stored_value = $default_value;
				}

				//	Now output the text field
				echo "<input id='$id' name='tt_plugin_options[$name]' type='text' value='$stored_value' style='$style' />";

				//	And now the help text
				echo "<p class='tt_admin_help_text'>$help_text</p>";
			}
			public static function field_helper_textarea( $args ) {
				//	Get currently stored options
				$options = get_option( 'tt_plugin_options' );

				//	Parse $args for readability
				$name = $args['name'];

				//	These $args are optional, so make sure they exist!
				array_key_exists('help_text', $args) ?
					$help_text = $args['help_text'] : $help_text = '';
				array_key_exists('default_value', $args) ?
					$default_value = $args['default_value'] : $default_value = '';
				array_key_exists('style', $args) ?
					$style = $args['style'] : $style = '';
				array_key_exists('id', $args) ?
					$id = $args['id'] : $id = '';


				//	Is there already a stored value in the database?
				$stored_value = $options[$name];

				//	If $stored_value is empty, use the default value
				if ( empty( $options[$name] ) && $stored_value !== false ) {
					$stored_value = $default_value;
				}

				//	Now output the text field
				echo "<textarea id='$id' name='tt_plugin_options[$name]' style='$style'>$stored_value</textarea>";

				//	And now the help text
				echo "<p class='tt_admin_help_text'>$help_text</p>";
			}


		//	The function to call on the page where you want the settings
		//	section displayed.  It outputs the content using the Settings API
		public static function output() {
			?>
			<div class="wrap">
				<div id="TT_content">
					<div class="icon32" id="icon-options-general"><br /></div>
					<h2>Tweet This - Settings</h2>
					<form action="options.php" method="post">
						<?php settings_fields( 'tweet_this_options' ); ?>
						<?php do_settings_sections( TT_FILENAME ); ?>
						<p class="submit">
						<input name="Submit" type="submit" class="button-primary"
							value="<?php esc_attr_e('Save Changes'); ?>" />
						</p>
					</form>
				</div>

				<div id="TT_hide_byline_dialog" title="Are you sure?" style="display: none;">
					<h3>When You Save Your Settings, The Byline Will Be Disabled</h3>
					<img src="<?php echo TT_ROOT_URL; ?>assets/images/light-switch.png" alt="" style="margin: 0 0 15px 15px; float: right;" />
					<p>
						I completely understand.  As a WordPress user myself,
						I have turned off my share of promotions and bylines
						when given the opportunity.
					</p><br />

					<p>
						However, <span style="font-weight: bold; ">please consider leaving the byline.</span>
					</p>
					<p>
						Just like you are driving traffic to your
						site with Tweetable quotes, this plugin gains users with
						the byline exposure.
					</p>
					<p>
						If it must go, then instead consider
						<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/tweetthis">leaving a postive review</a> for this plugin,
						<a target="_blank" href="http://tweetthis.jtmorris.net/contact/">offering advice</a> for improving it,
						or donating to the developer using the options below.
					</p><br />

					<table class='TT_donations'>
						<tr>
							<!--Flattr-->
							<td>
								<script id='fb5x80k'>(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//api.flattr.com/button/view/?uid=jtmorris&title=Tweet%20This&url=http%3A%2F%2Ftweethis.jtmorris.net';f.title='Flattr';f.height=62;f.width=55;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('fb5x80k');</script>
							</td>
							<!--PayPal-->
							<td>
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
									<input type="hidden" name="cmd" value="_donations">
									<input type="hidden" name="business" value="D6D4QT7PT5M9A">
									<input type="hidden" name="lc" value="US">
									<input type="hidden" name="item_name" value="John Morris">
									<input type="hidden" name="item_number" value="WP Plugin - Tweet This">
									<input type="hidden" name="currency_code" value="USD">
									<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>

							</td>
						</tr>
					</table><br />

					<p>
						Your help and consideration will ensure the ongoing development of this plugin.
					</p>

					<p>
						Thank you,<br /><br />John Morris<br /><span style='color: #777; '>"Tweet This" Plugin Developer<br /><a href='http://jtmorris.net' target='_blank' style='color: #777; '><em>http://jtmorris.net</em></a></span>
					</p>
				</div>
			</div>
			<?php

			self::sidebar();
			self::js();
		}

		protected static function sidebar() {
			?>
			<div id='TT_sidebar'>
				<div class='TT_sidebar_box TT_highlight'>
					<h3>Support This Plugin</h3>
					<p>
						Is this plugin useful for you?  If so, please help
						support its ongoing development and improvement
						with a donation.
					</p>
					<table class='TT_donations'>
						<tr>
							<!--Flattr-->
							<td>
								<script id='fb5x80j'>(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//api.flattr.com/button/view/?uid=jtmorris&title=Tweet%20This&url=http%3A%2F%2Ftweethis.jtmorris.net';f.title='Flattr';f.height=62;f.width=55;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('fb5x80j');</script>
							</td>
							<!--PayPal-->
							<td>
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
									<input type="hidden" name="cmd" value="_donations">
									<input type="hidden" name="business" value="D6D4QT7PT5M9A">
									<input type="hidden" name="lc" value="US">
									<input type="hidden" name="item_name" value="John Morris">
									<input type="hidden" name="item_number" value="WP Plugin - Tweet This">
									<input type="hidden" name="currency_code" value="USD">
									<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>

							</td>
						</tr>
					</table>

					<br />

					<p>
						Or, if you are short on funds, there are other ways you can help out:
					</p>
					<ul>
						<li>Leave a positive review on the plugin's <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/tweetthis">WordPress listing</a></li>
						<li>Vote "Works" on the plugin's <a target="_blank" href="http://wordpress.org/plugins/tweetthis/#compatibility">WordPress listing</a></li>
						<li><a target="_blank" href="http://twitter.com/home?status=I%20love%20this%20WordPress%20plugin!%20http://wordpress.org/plugins/tweetthis/">Share your thoughts on Twitter</a> and other social sites</li>
						<li>Improve this plugin on <a target="_blank" href='https://github.com/jtmorris/tweet-this'>GitHub</a></li>
					</ul>
				</div>

				<div class='TT_sidebar_box'>
					<h3>Plugin Tips, Guides, and More</h3>
					<p>
						If you're having any problems making Tweet This work,
						or you aren't sure how to use it, check out some of the
						following articles.
					</p>
					<ul>
						<li>
							<a target="_blank" href="http://tweetthis.jtmorris.net/posts/using-tweet-this/">
								Using Tweet This For the First Time
							</a>
							: A basic introduction to Tweet This. Covers how it
							works, how to use it, and introduces the most
							important settings.
						</li>
						<li>
							<a target="_blank" href="http://tweetthis.jtmorris.net/posts/tweet-settings/">
								Tweet This Settings
							</a>
							: Explains in greater detail the settings availble
							for Tweet This.
						</li>
					</ul>
				</div>

				<div class='TT_sidebar_box'>
					<h3>Get Help / Report a Bug</h3>
					<p>
						If you're encountering a problem, have a question, or would like to suggest an improvement, be sure to let me know!
					</p>
					<ul>
						<li>Open a thread on the <a target="_blank" href="http://wordpress.org/support/plugin/tweetthis">plugin support page</a>.</li>
						<li><a target="_blank" href="http://tweetthis.jtmorris.net/contact/">Contact the developer</a> privately.</li>
						<li>Open an <a target="_blank" href="https://github.com/jtmorris/tweet-this/issues">"issue" on GitHub</a>.</li>
					</ul>
				</div>
				<div class='TT_sidebar_box'>
					<h3>Other Plugins By This Developer</h3>
					<p>
						If you love this plugin, check out some of the others by
						the same developer!
					</p>
					<ul>
						<li>
							<a target="_blank" href="http://bit.ly/ad-blocking-detector">
								Ad Blocking Detector
							</a>
							: Tired of missed profits because of pesky ad
							blocker browser extensions, add-ons, and plugins? Fight
							back with <em>Ad Blocking Detector</em> today!
						</li>
					</ul>
				</div>
			</div>
			<?php
		}

		protected static function js() {
			?>
			<script type='text/javascript'>
				(function($) {
					$(document).ready(function() {
						//	Beg users disabling byline
						$('#tt_byline_removal input:radio[value=1]').click(function() {
							$("#TT_hide_byline_dialog").dialog({
								modal: true,
								buttons: {
									Close: function() {
										$(this).dialog("close");
									}
								},
								width: 550,
								position: {
									my: "top", at: "top", of: "#TT_content"
								}
							});
						});
					});	//	end $(document.ready(function() {
				}(jQuery))
			</script>
			<?php
		}
	}	//	end class TT_Setup
}	//	end if( !class_exists( ...
