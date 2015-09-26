<?php
/**
 * This file contains the handlers for all AJAX requests.
 * IT MUST ONLY OUTPUT JSON STRINGS FOR RECOGNITION DURING AJAX CALLS
 */

//	Use WordPress' AJAX handler. The action should be tt_ajax, and the AJAX call
//	should POST a variable tt_action with the actions available in the navigate()
//	function.

add_action( 'wp_ajax_tt_ajax', array( 'TT_Ajax_Actions', 'navigate' ) );

if ( !class_exists( 'TT_Ajax_Actions' ) ) {
	class TT_Ajax_Actions {



		/**
		 * Determines which AJAX handler function needs to be called based on
		 * the tt_action POST parameter passed in the AJAX call.
		 */
		public static function navigate() {
			ob_start();
			if( !empty( $_POST['tt_action'] )  ) {
				$action = $_POST['tt_action'];
			}
			else if( !empty( $_GET['tt_action'] ) ) {
				$action = $_GET['tt_action'];
			}
			else {
				$action = '';
			}
			ob_end_clean();

			switch( $action ) {
				case 'get_tinymce_dialog_params':
					self::get_tinymce_dialog_params();
					break;
				case 'get_tweet_content':
					self::get_tweet_content();
					break;
			}	//	end switch

			wp_die();
		}	//	end function navigate( ...



		/////////////////////////
		/// Handler Functions ///
		/////////////////////////
		//	Since this crap is done via AJAX, any "requiring" or "including" has to be
		//	done when the AJAX is called.  Meaning it can't be done at the top of the page,
		//	it has to be done in the actual function called... Here.  Don't believe me?
		//	Try moving this to the top of the file and running the plugin.  It will
		//	die in stupendous glory.
		//
		//	In addition, because this is an AJAX handler page called from a TinyMCE dialog
		//	box, there's some missing WordPress contextual information that can cause
		//	unexpected output when we include/require certain files.
		//	Therefore, the use of an output buffer is highly recommended, and necessary
		//	in certain cases.  Buffer any output during includes/requires, then erase
		//	the buffer, then output what you want to output.

		protected static function get_tinymce_dialog_params() {
			ob_start();
			require( dirname(__FILE__) . '/tools.php' );
			ob_end_clean();

			$sets = TT_Tools::get_tinymce_dialog_settings();

			echo json_encode( $sets );
		}

		protected static function get_tweet_content() {
			ob_start();
			require( dirname(__FILE__) . '/share-handler.php' );
			ob_end_clean();

			$p = $_POST;
			$defaults = array( 'text'=>'', 'custom_url'=>'', 'custom_twitter_handles'=>'',
				'custom_hidden_hashtags'=>'', 'custom_hidden_urls'=>'', 'remove_twitter_handles'=>'',
				'remove_url'=>'', 'remove_hidden_hashtags'=>'', 'remove_hidden_urls'=>'' );

			//	Merge passed values with defaults
			$p = array_merge( $defaults, $p );

			//	Convert empty values to boolean false to play along nicely with TT_Share_Handler
			$p = TT_Tools::empty_to_false( $p );


			$SH = new TT_Share_Handler( $p['text'], $p['custom_url'], $p['custom_twitter_handles'],
				$p['custom_hidden_hashtags'], $p['custom_hidden_urls'], $p['remove_twitter_handles'],
				$p['remove_url'], $p['remove_hidden_hashtags'], $p['remove_hidden_urls'], $p['post_id'] );

			$data = $SH->generate_actual_text();
			//	Generate JSON response
			$json = json_encode( array(
				'status' => true,
				'data'   => $data
			) );

			echo $json;
		}
	}	//	end class
}	//	end if ( !class_exists( ...