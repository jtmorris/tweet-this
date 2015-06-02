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



			//	Global settings applicable to all display modes
			add_settings_section(
				'tweet_this_global',		//	Section ID
				'Global Settings',					//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_global' ),	//	Content callback
				TT_FILENAME					//	The page
			);

			//	Settings applicable only to box display mode.
			add_settings_section(
				'tweet_this_box',		//	Section ID
				'Box Display Mode Settings',					//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_box' ),	//	Content callback
				TT_FILENAME					//	The page
			);

			//	Settings applicable only to button link display mode.
			add_settings_section(
				'tweet_this_button_link',		//	Section ID
				'Button Link Display Mode Settings',					//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_button_link' ),	//	Content callback
				TT_FILENAME					//	The page
			);

			//	Settings applicable only to the shortcode creator dialog box.
			add_settings_section(
				'tweet_this_scc_dialog',		//	Section ID
				'Shortcode Creator Dialog Settings',					//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_scc_dialog' ),	//	Content callback
				TT_FILENAME					//	The page
			);

			//	Advanced settings / Miscellaneous section
			add_settings_section(
				'tweet_this_advanced',		//	Section ID
				'Advanced Settings',					//	Section Heading
				array( 'TT_Settings',
					'section_content_helper_advanced' ),	//	Content callback
				TT_FILENAME					//	The page
			);


			//////////////////////////////
			/// Global Settings Fields ///
			//////////////////////////////
			//	Default Twitter Handles
			add_settings_field(
				'tt_default_twitter_handles',			//	Setting ID
				'Default Twitter Handles', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_textbox' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'default_twitter_handles',

					//	Help text displayed below field
					'help_text'=>'Comma separated list of "via" Twitter handles you want added to your tweets (leave blank for none). <br />Example: <span class="tt_admin_example">@jt_morris, @DTELinux, @CraigyFerg</span>'
				)
			);

			//	Default Hidden Hash Tags
			add_settings_field(
				'tt_default_hidden_hashtags',		//	Setting ID
				'Default Hidden Hashtags', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_textbox' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'default_hidden_hashtags',

					//	Help text displayed below field
					'help_text'=>'Any hashtags you want added to your message when tweeted, but not displayed in your Tweet This boxes (leave blank for none). <br />Example: <span class="tt_admin_example">#hashtags #rule</span>'
				)
			);

			//	Default Hidden URLs
			add_settings_field(
				'tt_default_hidden_urls',		//	Setting ID
				'Default Hidden URLs', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_textbox' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'default_hidden_urls',

					//	Help text displayed below field
					'help_text'=>'Any URLs you want added to your message when tweeted, but not displayed in your Tweet This boxes (leave blank for none). <br />Example: <span class="tt_admin_example">http://cs.johnmorris.me http://eng.johnmorris.me</span>'
				)
			);

			//	Twitter Icon
			add_settings_field(
				'tt_twitter_icon',			//	Setting ID
				'Twitter Icon',				//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME	,				//	The page
				'tweet_this_global',	//	Settings Section ID
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

			//	Use Shortlink
			add_settings_field(
				'tt_use_shortlink',			//	Setting ID
				'Use Shortlink?', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID
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

			//	Disable URLs in Tweet
			add_settings_field(
				'tt_disable_url',			//	Setting ID
				'Disable URLs', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID
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


			//	Hide Byline
			add_settings_field(
				'tt_hide_promotional_byline',		//	Setting ID
				'Hide Promotional Byline?',	//	Setting Title

				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID

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


			//	Display mode
			$box_text = 'Box' . ' (<em><a style="cursor: pointer; text-decoration: underline;" onclick="window.open(\'' . TT_ROOT_URL . 'assets/images/box.jpg\', \'popup\', \'width=491,height=492,scrollbars=no,toolbar=no,menubar=no\')">Click to See Sample</a></em>)';
			$blink_text = 'Button Link' . ' (<em><a style="cursor: pointer;  text-decoration: underline;" onclick="window.open(\'' . TT_ROOT_URL . 'assets/images/button_link.jpg\', \'popup\', \'width=530,height=380,scrollbars=no,toolbar=no,menubar=no\')">Click to See Sample</a></em>)';
			add_settings_field(
				'tt_display_mode',			//	Setting ID
				'Display Mode', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_global',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'display_mode',

					//	Array of arrays of label=>value pairs for desired buttons
					'buttons'=>array( array($box_text, 'box'), array($blink_text, 'button_link') ),

					'help_text'=>'Choose the method for displaying your tweetable content.  Default mode is "Box."',

					'default'=>'box'
				)
			);




			/////////////////////////
			/// Box Mode Settings ///
			/////////////////////////
			//	Theme
			add_settings_field(
				'tt_theme',							//	Setting ID
				'Choose Theme',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_box',					//	Settings Section ID
				array(
					'name' => 'base_theme',

					'buttons' => TT_Tools::get_themes_as_radio_array(),

					'help_text' => 'Choose the base theme for "Tweet This" boxes. NOTE: The images are not representative of actual size!',

					'default' => 'light'
				)
			);




			/////////////////////////////
			/// Button Link Settings ///
			////////////////////////////
			//	Include Twitter icon?
			add_settings_field(
				'tt_simple_link_include_icon',			//	Setting ID
				'Include Twitter Icon in Link?', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_button_link',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'simple_link_include_icon',

					//	Array of arrays of label=>value pairs for desired buttons
					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text'=>'Choose yes to include the Twitter icon alongside your link.',

					'default'=>true
				)
			);



			/////////////////////////////////////////
			/// Shortcode Creator Dialog Settings ///
			/////////////////////////////////////////
			//	Disable Preview?
			add_settings_field(
				'tt_disable_preview',							//	Setting ID
				'Disable Preview?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_scc_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_preview',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the preview of your tweet in the shortcode creator?',

					'default' => false
				)
			);			

			//	Disable Character Counter
			add_settings_field(
				'tt_disable_char_count',							//	Setting ID
				'Disable Character Counter?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_scc_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_char_count',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the "characters left" counter?',

					'default' => false
				)
			);

			//	Disable Post URL Options
			add_settings_field(
				'tt_disable_post_url',							//	Setting ID
				'Disable Post URL Options?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_scc_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_post_url',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the post URL options in the shortcode creator?',

					'default' => false
				)
			);

			//	Disable Twitter Handles Options
			add_settings_field(
				'tt_disable_handles',							//	Setting ID
				'Disable Twitter Handle Options?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_scc_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_handles',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the Twitter handle/user options in the shortcode creator?',

					'default' => false
				)
			);

			//	Disable Post URL Options
			add_settings_field(
				'tt_disable_post_url',							//	Setting ID
				'Disable Post URL Options?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_scc_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_post_url',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the post URL options in the shortcode creator?',

					'default' => false
				)
			);

			//	Disable Hidden Content Options
			add_settings_field(
				'tt_disable_hidden',							//	Setting ID
				'Disable Hidden Content Options?',						//	Setting Title
				array( 'TT_Settings',
					'field_helper_radio' ),			//	Content Callback
				TT_FILENAME,
				'tweet_this_scc_dialog',					//	Settings Section ID
				array(
					'name' => 'disable_hidden',

					'buttons'=>array( array('Yes', true), array('No', false) ),

					'help_text' => 'Disable the options for hidden content in the shortcode creator?',

					'default' => false
				)
			);



			///////////////////////
			/// Advanced / Misc ///
			///////////////////////
			//	Button Text
			add_settings_field(
				'tt_button_text_override',			//	Setting ID
				'Override Button Text', 			//	Setting Title
				array( 'TT_Settings',
					'field_helper_textbox' ),	//	Content Callback
				TT_FILENAME,				//	The page
				'tweet_this_advanced',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					//	tt_plugin_options['<THE VALUE SPECIFIED HERE']
					'name'=>'button_text_override',

					//	Help text displayed below field
					'help_text'=>'Override the "Tweet This" text in the call-to-action button/link. Leave blank to use default.'
				)
			);

			//	Icon Alt Tag
			add_settings_field(
				'tt_icon_alt_text',		//	Setting ID
				'Icon Alt Tag Value',	//	Setting Title
				array( 'TT_Settings', 
					'field_helper_textbox' ),	//	Content Callback
				TT_FILENAME,			//	The page
				'tweet_this_advanced',	//	Settings Section ID
				//	Arguments for callback
				array(
					//	Name in options array:
					'name' => 'icon_alt_text',
					'help_text'=>'The value of the alt attribute of the Twitter icon &lt;img&gt; tag. Default is none.'
				)
			);

			//	Custom CSS
			add_settings_field(
				'tt_css_override',					//	Setting ID
				'Override CSS',						//	Setting Title

				array( 'TT_Settings',
					'field_helper_textarea' ),		//	Content Callback
				TT_FILENAME,						//	The page
				'tweet_this_advanced',				//	Settings Section ID

				//	Arguments for callback
				array(
					'name'=>'css_override',

					'help_text'=>'Override default CSS rules for "Tweet This".',

					'style'=>'min-width: 500px; min-height: 300px;'
				)
			);
		}
			public static function validation_helper( $input ) {
				return $input;
			}
			public static function section_content_helper_global() {

			}
			public static function section_content_helper_box() {
				?>
				<p>
					These settings only apply to the "Box" display mode.  Other
					display modes ignore these settings.
				</p>
				<?php
			}
			public static function section_content_helper_button_link() {
				?>
				<p>
					These settings only apply to the "Button Link" display mode.  Other
					display modes will ignore these settings.
				</p>
				<?php
			}
			public static function section_content_helper_advanced() {

			}
			public static function section_content_helper_scc_dialog() {
				?>
				<p>
					Customize the Tweet This Shortcode Creator dialog box. (<em><a style="cursor: pointer;  text-decoration: underline;" onclick="window.open('<?php echo TT_ROOT_URL . 'assets/images/scc.jpg' ?>', 'popup', 'width=646,height=461,scrollbars=no,toolbar=no,menubar=no')">Click here</a></em> to see a picture of it in its default configuration).
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
					<h3>Tutorials, Tips, &amp; Ideas</h3>
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
						<li>
							<a target="_blank" href="http://tweetthis.jtmorris.net/posts/including-graphics-and-images-in-a-tweet/">
								Including Graphics and Images in a Tweet
							</a>
							: Gives a few tips for including graphics inside Tweet This content.
						</li>
					</ul>
				</div>
				<div class='TT_sidebar_box'>
					<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
					<!-- Tweet This Dashboard -->
					<ins class="adsbygoogle"
					     style="display:inline-block;width:336px;height:280px"
					     data-ad-client="ca-pub-4623469134243566"
					     data-ad-slot="9769490937"></ins>
					<script>
					(adsbygoogle = window.adsbygoogle || []).push({});
					</script>
				</div>
				<div class='TT_sidebar_box'>
					<h3>Get Help / Report a Bug</h3>
					<p>
						If you're encountering a problem, have a question, or would like to suggest an improvement, be sure to let me know!
					</p>
					<ul>
						<li>Open a thread on the <a target="_blank" href="http://wordpress.org/support/plugin/tweetthis">plugin support page</a>.</li>
						<li><a target="_blank" href="http://tweetthis.jtmorris.net/contact/">Contact the developer</a> privately.</li>
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
						<li>
							<a target="_blank" href="http://bit.ly/longer-login">
								Longer Login ("Remember Me" Extension)
							</a>
							: <em>Longer Login</em> allows customizing the length of 
							WordPress' "Remember Me" length. No more automatic logouts 
							every few days!
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
