<?php
/**
 * This file contains the handlers for all AJAX requests. 
 * IT MUST ONLY OUTPUT JSON STRINGS FOR RECOGNITION DURING AJAX CALLS
 */
require_once ( TT_ROOT_PATH . 'includes/share-handler.php' );
if ( !class_exists( 'TT_Ajax_Actions' ) ) {
	class TT_Ajax_Actions {
		/**
		 * Determines which AJAX handler function needs to be called based on
		 * the tt_action POST parameter passed in the AJAX call.
		 */
		public static function navigate() {
			switch( $_POST['tt_action'] ) {
				case 'get_default_post_url':
					self::get_default_post_url();
					break;
				case 'get_default_twitter_handles':
					self::get_default_twitter_handles();
					break;
			}	//	end switch

			exit;
		}	//	end function navigate( ...

		

		/////////////////////////
		/// Handler Functions ///
		/////////////////////////

		//	All functions should output a simple text string.
		protected static function get_default_post_url() {
			//	There will only be a post URL if the post is saved (draft, published,
			//	et cetera). In this case, there should be an ID URL parameter that we'll
			//	need.  If not, we have no URL to generate, so we should simply
			//	return a placeholder.
			if (array_key_exists('post', $_GET)) {
				$SH = new TT_Share_Handler('');

				echo $SH->generate_post_url($_GET['post']);	
			}
			else {
				echo "<NO URL YET>";
			}			
		}

		protected static function get_default_twitter_handles() {
			$SH = new TT_Share_Handler('');

			echo $SH->generate_twitter_handles_for_url(false);
		}
	}	//	end class
}	//	end if ( !class_exists( ...


