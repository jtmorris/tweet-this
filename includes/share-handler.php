<?php
/**
 * This file contains a static-esque class with all necessary functions and data for
 * sharing of content.
 */


if ( !class_exists( 'TT_Share_Handler' ) ) {
	class TT_Share_Handler {
		protected $my_text = null;
		protected $my_url = null;
		protected $my_url_is_placeholder = false;
		protected $my_twitter_handles = null;

		/**
		 * Share Handler constructor
		 * @param string  $text                   The text to tweet.
		 * @param boolean $custom_url             A URL to override the auto-generated one.
		 * @param boolean $custom_twitter_handles A list of Twitter handles to override the default ones with.
		 */
		public function __construct( $text='', $custom_url=false, $custom_twitter_handles=false ) {
			$this->my_text = $text;

			if ( $custom_url ) {
				$this->my_url = $custom_url;
			}

			if ( $custom_twitter_handles ) {
				$this->my_twitter_handles = $custom_twitter_handles;
			}
		}


		public function display() {
			$url = $this->generate_share_url();

			$options = get_option('tt_plugin_options');
			$retval = '';

			//	Any CSS overrides?
			if ( !empty($options['css_override']) ) {
				$retval .= '<style type="text/css">' . $options['css_override'] . '</style>';
			}
			$retval .= '<div class="TT_wrapper">';
				$retval .= '<div class="TT_text">';
					$retval .= '<a class="TT_tweet_link" href="' . $url . '" target="_blank">';
						$retval .= $this->generate_text();
					$retval .= '</a>';
				$retval .= '</div>';

				$retval .= '<div class="TT_footer">';
					$retval .= '<div class="TT_byline">';
						if ( $options['hide_promotional_byline'] != true ) {
							$retval .= 'Powered By the <em><a href="http://wordpress.org/plugins/tweet-this/" target="_blank">Tweet This</a></em> Plugin';
						}
					$retval .= '</div>';

					$retval .= '<div class="TT_tweet_link_wrapper">';
						$retval .= '<a class="TT_tweet_link" href="' . $url . '" target="_blank">';
							$retval .= '<img src="' . TT_ROOT_URL . 'assets/images/twitter-icons/' . $options['twitter_icon'] . '.png" />';
							$retval .= 'Tweet This';
						$retval .= '</a>';
					$retval .= '</div>';
					$retval .= '<div style="clear: both; "></div>';
				$retval .= '</div>';
			$retval .= '</div>';


			return $retval;
		}


		protected function generate_share_url() {
			//	We need to generate the URL
			$url = 'http://twitter.com/intent/tweet?text=';
			$url .= urlencode(
				trim(	//	There's a chance there's a trailing space if no URL or Twitter
						//	handles are input.  Remove that.
					html_entity_decode(	//	WordPress encodes special characters in posts. Undo that.
						$this->generate_text() . ' ' .
						$this->generate_post_url() . ' ' .
						$this->generate_twitter_handles_for_url(),
						ENT_QUOTES,	//	Both single and double quotes
						"UTF-8"	//	Specify UTF-8 for older versions of PHP
					)
				)
			);


			$url = trim( $url );

			return $url;
		}

		protected function generate_text() {
			return $this->my_text;
		}

		public function generate_twitter_handles_for_url( $include_via = true ) {
			//	Do we need to get the Twitter handles from the database?
			if( is_null( $this->my_twitter_handles ) ) {
				//	Yes
				$options = get_option('tt_plugin_options');

				$this->my_twitter_handles = $options['default_twitter_handles'];
			}

			if ( !empty($this->my_twitter_handles) ) {
				if ( $include_via ) {
					return "via " . $this->my_twitter_handles;
				}
				else {
					return $this->my_twitter_handles;
				}
			}

			return '';
		}

		public function generate_post_url( $id = 0, $placeholder_flag = false ) {	//	$id = 0 means current post to get_permalink function
			//	Do we need to generate the URL? If one is already provided, we don't.
			if( is_null( $this->my_url ) ) {
				//	Yes, we need ot generate a URL

				//	Okay then, do we need to construct a shortlink
				$options = get_option('tt_plugin_options');
				if ( $options['use_shortlink'] ) {
					//	Yes, we need a shortlink.

					//	Try getting one
					$short = wp_get_shortlink( $id );

					if ( empty( $short ) ) {	//	No shortlink for whatever reason
						//	Just get the permalink I guess...
						$short = get_permalink( $id );
					}


					//	Okay. Now, if the post is not yet published, the shortlink
					//	will be the default WordPress shortlink (the domain and
					//	post id: http://domain.com/?p=123). Most shortlink
					//	customization plugins, like JetPack's WP.me module and
					//	WP Bitly don't generate shortlinks until after the post
					//	is published.
					//
					//	This means that the shortlink we just worked out butts
					//	of to get... might not be correct.  The shortlink
					//	might not have been constructed yet, and WordPress just
					//	doesn't know that.
					//
					//	If this is the case, then we want to return a placeholder
					//	URL that has similar/same length.
					//
					//	Fortunately, there's a function for that which determines
					//	whether the shortlink is accurate or not... sort of.
					if ( TT_Tools::is_shortlink_accurate( $short ) ) {
						//	YAY! It's accurate... probably.  So, use $short
						$this->my_url = $short;
						$this->my_url_is_placeholder = false;
					}
					else {
						//	Damn... now we need a placeholder...
						//	Fortunately, there's a function for that...
						$this->my_url = TT_Tools::placeholder_shortlink();
						$this->my_url_is_placeholder = true;
					}
				}
				else {
					//	No, no shortlink, just use normal URL
					$this->my_url = get_permalink( $id );
					$this->my_url_is_placeholder = false;
				}
			}

			if ( $placeholder_flag ) {
				return array( 'shortlink'=>$this->my_url,
					'is_placeholder'=>$this->my_url_is_placeholder );
			}

			return $this->my_url;
		}
	}	//	end class
}	//	end if( !class_exists( ...
