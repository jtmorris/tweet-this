<?php
/**
 * This file contains a static-esque class with miscellaneous tools and one-off
 * functions used elsewhere.
 */

if ( !class_exists( 'TT_Tools' ) ) {
	class TT_Tools {
		/**
		 * Parses the ./tweet-this/assets/images/twitter-icons directory for
		 * Twitter logos in PNG format. Then constructs an array of values
		 * for the TT_Settings::define_settings function.
		 * @return array Multi-dimensional array formatted for TT_Settings::field_helper_radio()
		 */
		public static function get_twitter_images_as_radio_array() {
			$dir_base = TT_ROOT_PATH . 'assets/images/twitter-icons/';
			$url_base = TT_ROOT_URL .  'assets/images/twitter-icons/';
			$pattern = $dir_base . '*.png';

			$retarr = array();

			foreach ( glob( $pattern ) as $file ) {
				$filename = basename( $file, '.png' );
				$img_url = $url_base . $filename . '.png';
				$img = "<img title='$filename' class='TT_twitter_icon' src='$img_url' />";
				$retarr[] = array( 
					//	Label
					$img,
					//	Value
					$filename
				);
			}

			return $retarr;
		}

		/**
		 * Parses the ./tweet-this/assets/css/themes directory for
		 * themes. Then constructs an array of values
		 * for the TT_Settings::define_settings function.
		 * @return array Multi-dimensional array formatted for TT_Settings::field_helper_radio()
		 */
		public static function get_themes_as_radio_array() {
			$dir_base = TT_ROOT_PATH . 'assets/css/themes/';
			$url_base = TT_ROOT_URL .  'assets/css/themes/';
			$pattern = $dir_base . '*.css';

			$retarr = array();

			foreach ( glob( $pattern ) as $file ) {
				$filename = basename( $file, '.css' );
				$img_url = $url_base . $filename . '.jpg';
				$img = "<img title='Theme Name: $filename' class='TT_theme_preview' src='$img_url' />";
				$retarr[] = array(
					//	Label
					$img,
					//	Value
					$filename
				);
			}

			return $retarr;
		}


		/**
		 * Returns the ID# of the last post
		 * @return int The ID# of the post
		 */
		public static function get_id_of_last_post() {
			$last = wp_get_recent_posts( array( 'numberposts'=> 1, 
				'post_type' => 'post', 'post_status' => 'publish' ) );

			return $last[0]['ID'];
		}


		/**
		 * Determines with reasonable certainty whether the provided shortlink
		 * is accurate, or if it's just WordPress' default shortlink that hasn't
		 * yet been overriden by shortlink plugins.
		 * @param  string  $short The shortlink URL you want checked for accuracy.
		 * @return boolean        true = accurate shortlink, false = inaccurate shortlink
		 */
		public static function is_shortlink_accurate( $short ) {
			//	What does accurate mean exactly?
			//	================================
			//	Well, that's a great question.  Unfortunately, this isn't a 
			//	precise science.  The only reason we need to check for this
			//	is some shortlink plugins don't generate shortlinks until
			//	the post is published.  And there's no guarantees regarding
			//	how those plugins work or what they do.
			//	
			//	Because this is such a grey area, I'm only going to make two
			//	checks.
			//	
			//	1.)  Is the shortlink domain/hostname the same as the site's
			//	normal domain.  I'm checking this because when plugins postpone
			//	shortlink generation, WordPress steps in and provides it's 
			//	default shortlink with the same domain and the post ID as a 
			//	query argument.  Therefore, I'm going to assume that if the
			//	domains are the same, we have the default WordPress shortlink,
			//	and then all we'll need to know is whether the shortlinks on 
			//	published and active posts use the same domain.  If it's a
			//	different domain, I'm going to assume the shortlink is accurate.
			//	Why would the shortlink have a different domain if it wasn't
			//	generated appropriately?
			//	
			//	2.)  If the shortlink domain is the same as the normal domain,
			//	Then I'll check whether already published posts have shortlinks
			//	with the normal domain as well.  If so, I'll assume the shortlink
			//	is accurate.  If not, I'll assume the shortlink is inaccurate.
			//	
			//	Cross your fingers.

			//	1.) Shortlink and site domain the same?
			$siteurl = get_site_url();
			$shortd = parse_url( $short, PHP_URL_HOST );
			$sited = parse_url( $siteurl, PHP_URL_HOST );

			if ( $shortd != $sited ) {
				//	No, domains are not the same... As stated above, assume
				//	this means the shortlink is accurate.
				//
				//	Seriously, cross your fingers...
				return true;
			}


			//	Okay, the domains are the same, so on to step 2
				
			//	2.) Last published post had shortlink with different domain than site
			$last_shortlink = wp_get_shortlink( TT_Tools::get_id_of_last_post() );
			$lastd = parse_url( $last_shortlink, PHP_URL_HOST );

			if ( $lastd == $sited ) {
				//	Yes, last post had shortlink with the site's domain... As
				//	stated above, assuming this means the shortlink is accurate.
				//	
				//	I really, truly mean it.  Cross your fingers.  This sucks.
				return true;
			}


			//	Well, the last post had a different shortlink domain than this
			//	post AND this post's shortlink domain is the same as the site's.
			//	
			//	Really, I can't think of a reasonable situation where these 
			//	checks don't produce an accurate answer.
			//	
			//	So, return false.  The shortlink is inaccurate.
			return false;	//	FINGER'S CROSSED DAMNIT! AND KEEP THEM THAT WAY!
		}

		/**
		 * Returns a shortlink with the same size and style as other published
		 * posts. Useful for when shortlinks aren't yet generated for in progress
		 * posts.
		 * @return string A placeholder URL of the same size and format as the
		 * shortlink will be for the current post.
		 */
		public static function placeholder_shortlink( ) {
			//	What does a placeholder shortlink mean exactly?
			//	================================================
			//	Well, presumably this function is being called because WordPress
			//	is displaying its default shortlink for unpublished posts because
			//	lots of shortlink plugins don't generate a shortlink until the
			//	post is published.
			//	
			//	And the function caller wants a fake shortlink that looks similar
			//	to what the shorltink will be, and is the same length so the
			//	character count is accurate.
			//	
			//	So, here's how I'm going to do that.  I'm going to get the 
			//	shortlink from the last published post.
			$last_shortlink = wp_get_shortlink( TT_Tools::get_id_of_last_post() );

			if( empty( $last_shortlink ) ) {
				//	Uh... no published posts? Well, put in a default placeholder.
				//	The plugin warns about no published posts elsewhere, and
				//	we need to return something.
				return "http://tweetthis.jtmorris.net/";
			}

			return $last_shortlink;
		}

		/**
		 * Returns all the plugin settings needed for the dialog box as an associative array.
		 *
		 * @return   [array]   Array of plugin settings for dialog box.
		 */
		public static function get_tinymce_dialog_settings() {
			//	Because this function is not necessarily called on the post/page editor screen,
			//	the post/page URL getting function TT_Share_Handler::generate_post_url()
			//	won't always work.  So, if and when it fails, we'll need a placeholder link that
			//	we can get without failure.
			try {
				//	post ID
				global $current_screen, $post;
				$type = $current_screen->post_type;
				if ( $type == 'post' || $type == 'page' ) {
					$id = $post->ID;
				}
				else {
					throw new Exception( 'Unable to retrieve post ID.' );
				}

				//	post URL
				$SH = new TT_Share_Handler();
				$urlarr = $SH->generate_post_url( $id, true );

				if( is_array( $urlarr ) ) {	//	Shortlink w/ qualifying info in array
					$post_url = $urlarr['shortlink'];
					$is_placeholder = $url['is_placeholder'];
				}
				else {	//	Nothing fancy, just the URL as a string
					$post_url = $urlarr;
					$is_placeholder = false;
				}
			}
			catch( Exception $e ) {
				$post_url = TT_Tools::placeholder_shortlink();
				$is_placeholder = false;
			}

			//	default Twitter handles and hidden hashtags
			$options = get_option( 'tt_plugin_options' );
			$twits = $options['default_twitter_handles'];
			$hashtags = $options['default_hidden_hashtags'];
			$hidden_urls = $options['default_hidden_urls'];

			//	dialog customization options
			$hide_preview = $options['disable_preview'];
			$hide_handles = $options['disable_handles'];
			$hide_post_url = $options['disable_post_url'];
			$hide_hidden = $options['disable_hidden'];
			$hide_char_count = $options['disable_char_count'];

			

			return array(
				'post_url' => $post_url,
				'post_url_is_placeholder' => $is_placeholder,
				'default_twitter_handles' => $twits,
				'default_hidden_hashtags' => $hashtags,
				'default_hidden_urls' => $hidden_urls,
				'disable_preview' => $hide_preview,
				'disable_handles' => $hide_handles,
				'disable_post_url' => $hide_post_url,
				'disable_hidden' => $hide_hidden,
				'disable_char_count' => $hide_char_count
			);
		}
	}	//	end class
}	//	end if( !class_exists( ...