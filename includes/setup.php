<?php
/**
 * This file contains a static-esque class with all necessary setup, enqueue,
 * register, and other related WordPress items.
 */

require_once( TT_ROOT_PATH . "includes/ajax-actions.php" );
require_once( TT_ROOT_PATH . "includes/settings.php" );
require_once( TT_ROOT_PATH . "includes/share-handler.php" );

if ( !class_exists( 'TT_Setup' ) ) {
	class TT_Setup {
		protected static $version = '1.4.0';

		/**
		 * Registers and enqueues all CSS and JavaScript.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function enqueue() {
			//	Enqueue admin CSS
			add_action( 'admin_enqueue_scripts',
				array('TT_Setup', 'enqueue_helper_admin_css') );

			//	Enqueue public CSS
			add_action( 'wp_enqueue_scripts',
				array( 'TT_Setup', 'enqueue_helper_public_css' ) );

			//	Enqueue admin JS
			add_action( 'admin_enqueue_scripts',
				array( 'TT_Setup', 'enqueue_helper_admin_js' ) );

			//	Enqueue public facing JS
			add_action( 'wp_enqueue_scripts',
				array( 'TT_Setup', 'enqueue_helper_public_js' ) );

			//	Add AJAX listeners
			add_action( 'wp_ajax_tt_ajax',
				array( 'TT_Ajax_Actions', 'navigate' ) );
		}
			public static function enqueue_helper_admin_css() {
				wp_register_style( 'tt-admin-css',
					TT_ROOT_URL . 'assets/css/admin.css' );
				wp_register_style( 'tt-shortcode-creator-css',
					TT_ROOT_URL . 'assets/css/tinymce-dialog.css' );

				wp_enqueue_style( 'tt-admin-css' );
				wp_enqueue_style( 'tt-shortcode-creator-css' );


				//	jQuery UI theme.
				//	To place nice with other plugins, let's only load this when we're on
				//	Ad Blocking Detector's page in the admin.
				if( is_admin() && $_GET['page'] == 'tweet-this' ) {
					wp_enqueue_style('tt-admin-jquery-ui-css',
	                	TT_ROOT_URL . 'assets/css/jquery/cupertino-theme/jquery-ui.min.css',
	                	false
	               	 );
				}
			}
			public static function enqueue_helper_public_css() {
				$options = get_option( 'tt_plugin_options' );

				wp_register_style( 'tt-public-css-main',
					TT_ROOT_URL . 'assets/css/public-main.css' );
				wp_register_style( 'tt-public-css-theme',
					TT_ROOT_URL . 'assets/css/themes/' . $options['base_theme'] . '.css' );

				wp_enqueue_style( 'tt-public-css-main' );
				wp_enqueue_style( 'tt-public-css-theme' );
			}
			public static function enqueue_helper_admin_js() {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-dialog' );
				wp_enqueue_script( 'jquery-ui-accordion' );


				//	We need to be in the admin, editing a post or page for the dialog box to be
				//	initialized, so we don't want to add the dialog JS unless the same
				//	conditions apply.  So are we?
				global $current_screen;
				$type = $current_screen->post_type;

				if ( is_admin() && ($type == 'post' || $type == 'page') ) {
					wp_enqueue_script( 'tt-shortcode-creator-js',
						TT_ROOT_URL . 'assets/js/tinymce-dialog.js' );
				}
			}
			public static function enqueue_helper_public_js() {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'tt-tweet-this-box-js',
					TT_ROOT_URL . 'assets/js/tweet-this-box.js' );
			}

		/**
		 * Registers and defines all WordPress hooks and filters. (e.g. activation /
		 * deactivation)
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function hooks() {
			//	Activation
			register_activation_hook( TT_PLUGIN_FILE,
				array( 'TT_Setup', 'hooks_helper_activation' ) );

			//	Deactivation
			register_deactivation_hook( TT_PLUGIN_FILE,
				array( 'TT_Setup', 'hooks_helper_deactivation' ) );

			//	Uninstall
			register_uninstall_hook( TT_PLUGIN_FILE,
				array( 'TT_Setup', 'hooks_helper_uninstall' ) );

			//	Admin Init
			add_action( 'admin_init',
				array( 'TT_Setup', 'hooks_helper_admin_init' ) );

			//	Admin Heading
			add_action( 'admin_head',
				array( 'TT_Setup', 'hooks_helper_admin_header' ) );

			//	Admin Footer
			add_action( 'admin_footer',
				array( 'TT_Setup', 'hooks_helper_admin_footer' ) );

			//	TinyMCE Plugin
			add_filter( 'mce_external_plugins',
				array( 'TT_Setup', 'hooks_helper_tinymce_plugin' ) );

			//	Shortcode Creator Button
			$current_options = get_option( 'tt_plugin_options' );
			is_array( $current_options ) && array_key_exists( 'button_location', $current_options ) ? $bl = $current_options['button_location'] : $bl = 'row1';
			
			if( $bl == 'media' ) {	//	Media Button Row
				add_action( 'media_buttons', 
					array( 'TT_Setup', 'hooks_helper_media_button' ), 101 );
			}
			else if ( $bl == 'row2' ) {	//	Advanced Editor Row (Row #2)
				add_filter( 'mce_buttons_2',
					array( 'TT_Setup', 'hooks_helper_tinymce_button' ) );
			}
			else {	//	Normal Editor Row
				add_filter( 'mce_buttons',
					array( 'TT_Setup', 'hooks_helper_tinymce_button' ) );
			}
		}
			public static function hooks_helper_activation() {

			}
			public static function hooks_helper_deactivation() {

			}
			public static function hooks_helper_uninstall() {
				//	Clear the options
				delete_option( 'tt_plugin_options' );
				delete_option( 'tt_current_version' );
			}
			public static function hooks_helper_admin_init() {
				TT_Settings::define_settings();
			}
			public static function hooks_helper_admin_header() {
				//	Output the information for the dialog box and setup the dialog box.

				//	We need to be in the admin, editing a post or page for the plugin to be
				//	initialized, so we don't want to add the jQuery dialog unless the same
				//	conditions apply.  So are we?
				global $current_screen;
				$type = $current_screen->post_type;

				if ( is_admin() && ($type == 'post' || $type == 'page') ) {
					?>
					<script type='text/javascript'>
						<?php // Get the URL to the AJAX handler file and save it ?>
						var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";
						
						jQuery(document).ready(function() {
							<?php // Output the dialog boxes data as a JavaScript variable 

							//	Strategy Cribbed From Here: http://stackoverflow.com/a/4935684/2523144
							//	
							//	Encode the PHP variable as JSON.  Use native JavaScript to parse the JSON
							//	if the necessary fuction is available.  If not, use the slower jQuery
							//	version.
							$json = json_encode( TT_Tools::get_tinymce_dialog_settings() );
							?>
							window.TT_Data = <?php echo $json; ?>;


							<?php // Turn the dialog box code included above into a jQuery dialog ?>						
							jQuery('#TT-shortcode-creator-dialog').dialog({
								autoOpen: false,
								modal: true,
								height: 600,
								width: 650
							});

							jQuery('#tt-dialog-launcher').click(function(event) {
								event.preventDefault();
								jQuery('#TT-shortcode-creator-dialog').dialog("open");
							});
						});						
					</script>
					<?php
				}
			}
			public static function hooks_helper_admin_footer() {
				//	Output the dialog box code.

				//	We need to be in the admin, editing a post or page for the plugin to be
				//	initialized, so we don't want to add the jQuery dialog unless the same
				//	conditions apply.  So are we?
				global $current_screen;
				$type = $current_screen->post_type;

				if ( is_admin() && ($type == 'post' || $type == 'page') ) {
					include( TT_ROOT_PATH . '/includes/tinymce-dialog.html' );
				}
			}			
			public static function hooks_helper_tinymce_plugin( $plugin_array ) {
				//	We need to be in the admin, editing a post or page to include
				//	this plugin.  So are we?
				global $current_screen;
				$type = $current_screen->post_type;

				if ( is_admin() && ($type == 'post' || $type == 'page') ) {
					//	Yes, we are in the admin editing a post or page
					$plugin_array['tweetthis'] = TT_ROOT_URL . 'assets/js/tinymce-plugin.js';
				}
				return $plugin_array;
			}
			public static function hooks_helper_tinymce_button( $buttons ) {
				//	We need to be in the admin, editing a post or page to include
				//	this button.  So are we?
				global $current_screen;
				$type = $current_screen->post_type;

				if ( is_admin() && ($type == 'post' || $type == 'page') ) {
					//	Yes, we are in the admin editing a post or page
					array_push( $buttons, 'tweetthis_button' );
				}
				return $buttons;
			}
			public static function hooks_helper_media_button() {
				?>
				<a href="#" id="tt-dialog-launcher" class="button">
					<img src="<?php echo TT_ROOT_URL; ?>assets/images/tinymce-button.png" alt="Launch Tweet This Shortcode Creator" style="height: 18px;" />
					Add Tweet This Shotcode
				</a>
				<?php
			}


		/**
		 * Registers and defines all WordPress admin menus.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		protected static function menus() {
			add_action( 'admin_menu',
				array( 'TT_Setup', 'menus_helper' ) );
		}
			public static function menus_helper() {
				add_options_page(
					'Tweet This - Dashboard',	//	Title tag value
					'Tweet This',		//	Menu Text
					'administrator',			//	Required privileges/capability
					'tweet-this',				//	Menu Slug
					array( 'TT_Settings',
						'output' )	 			// Content Function
				);
			}

		/**
		 * Registers and defines the WordPress shortcodes.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_action calls: http://goo.gl/pZnUYV
		 */
		public static function shortcodes() {
			add_shortcode( 'tweetthis',
				array( 'TT_Setup', 'shortcodes_helper' ) );
			add_shortcode( 'tweet-this',
				array( 'TT_Setup', 'shortcodes_helper' ) );
		}
			public static function shortcodes_helper( $atts, $enc_content = null ) {
				extract( shortcode_atts( array(
					'text' => '',
					'url' => false,
					'twitter_handles' => false,
					'hidden_hashtags' => false,
					'hidden_urls' => false,
					'display_mode' => false,
					'remove_twitter_handles' => false,
					'remove_url' => false,
					'remove_hidden_hashtags' => false,
					'remove_hidden_urls' => false
				 ), $atts ) );

				//	Is this an enclosing or self-closing shortcode?
				//	http://codex.wordpress.org/Shortcode_API#Enclosing_vs_self-closing_shortcodes
				if ( !is_null( $enc_content ) && $enc_content != '' ) {
					//	Enclosing shortcode. We want to use the text inside
					//	the shortcode instead of in the text attribute.
					$text = $enc_content;
				}


				$Share = new TT_Share_Handler( $text, $url,
					$twitter_handles, $hidden_hashtags, $hidden_urls, $remove_twitter_handles, $remove_url,
					$remove_hidden_hashtags, $remove_hidden_urls );

				$options = get_option('tt_plugin_options');

				//	What display mode are we using?
				if ( $display_mode === false ) {	//	None provided in shortcode
					if( !$options || !array_key_exists( 'display_mode', $options ) ) {
						$display_mode = 'box';
					}
					else {
						$display_mode = $options['display_mode'];
					}
				}


				switch( $display_mode ) {
					case 'button_link':
						//	Do we include the icon?
						if( !array_key_exists( 'simple_link_include_icon', $options ) ||
							$options['simple_link_include_icon'] ) {
								return $Share->display_link();
						}
						else {
							return $Share->display_link( false );
						}
					default:
						return $Share->display_box();
				}
			}


		/**
		 * Adds links under entry in plugins listing.
		 * @todo If minimum PHP version for WordPress hits 5.3.0, switch to
		 * anonymous functions in add_filter call: http://goo.gl/pZnUYV
		 */
		public static function plugin_list_links( ) {
			$plugin_file = TT_SUBDIR_AND_FILE;

			add_filter( "plugin_action_links_{$plugin_file}",
				array( 'TT_Setup', 'plugin_list_links_helper' ) );
		}
			public static function plugin_list_links_helper( $old_links ) {
				$new_links = array(
					//	Settings
					'<a href="' . admin_url( 'options-general.php?page=tweet-this' ) .'">Settings</a>',
					'<a href="http://tweetthis.jtmorris.net/posts/using-tweet-this/" target="_blank">Getting Started</a>'
				);

				return array_merge( $new_links, $old_links );
			}


		/**
		 * Checks to see if plugin has been updated and runs any necessary
		 * upgrade code.
		 */
		public static function upgrade() {
			//	Does the stored plugin version equal the current version?
			//	If so, then we shouldn't need to do anything.
			//	If not, then we have to run through any upgrade processes.
			if ( get_site_option( 'tt_current_version') != self::$version ) {
				//	Run upgrade stuff
				$current_options = get_option( 'tt_plugin_options' );
				$options = array(
					'default_twitter_handles' => '',
					'default_hidden_hashtags' => '',
					'default_hidden_urls' => '',
					'hide_promotional_byline' => false,
					'use_shortlink' => false,
					'css_override' => '',
					'twitter_icon' => 'bird1',
					'base_theme' => 'light',
					'icon_alt_text' => '',
					'button_location' => 'row1'
				);


				//	Only use the default options for options that
				//	aren't already set.
				if ( gettype( $current_options ) == 'array' ) {
					$options = $current_options + $options;
				}


				//	Now store the options
				update_site_option( 'tt_plugin_options', $options );

				//	And we update the option in the database to reflect new
				//	db version
				update_site_option( 'tt_current_version', self::$version );
			}
		}

		public static function initialize() {
			//	Upgrade function runs every time plugin loads. It determines
			//	what, if anything needs to be done.
			self::upgrade();

			self::menus();
			self::hooks();
			self::enqueue();
			self::shortcodes();
			self::plugin_list_links();
		}
	}	//	end class
}	//	end if( !class_exists( ...
