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
			if( !empty( $_POST['tt_action'] )  ) {
				$action = $_POST['tt_action'];
			}
			else if( !empty( $_GET['tt_action'] ) ) {
				$action = $_GET['tt_action'];
			}
			else {
				$action = '';
			}
			
			switch( $action ) {
				case 'get_tinymce_dialog_params':
					self::get_tinymce_dialog_params();
					break;
			}	//	end switch

			die();
		}	//	end function navigate( ...

		

		/////////////////////////
		/// Handler Functions ///
		/////////////////////////

		protected static function get_tinymce_dialog_params() {
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
			//	the buffer.
			ob_start();
			require( './tools.php' );
			ob_end_clean();

			$sets = TT_Tools::get_tinymce_dialog_settings();

			echo json_encode( $sets );
		}
	}	//	end class	
}	//	end if ( !class_exists( ...