<?php
/**
 * This file contains a static-esque class with all necessary functions and data for
 * sharing of content.
 */


if ( !class_exists( 'TT_Share_Handler' ) ) {
	class TT_Share_Handler {
		protected $my_text = null;
		protected $my_url = null;
		protected $my_hidden_hashtags = null;
		protected $my_hidden_urls = null;
		protected $my_url_is_placeholder = false;
		protected $my_twitter_handles = null;

		/**
		 * Share Handler constructor
		 * @param string  $text                   The text to tweet.
		 * @param boolean $custom_url             A URL to override the auto-generated one.
		 * @param boolean $custom_twitter_handles A list of Twitter handles to override the default ones with.
		 * @param boolean $custom_hidden_hashtags A list of hidden hashtags to override the default ones with.
		 * @param boolean $custom_hidden_url      A list of hidden URLs to override the default ones with.
		 * @param boolean $remove_twitter_handles Whether to include Twitter handles or not.
		 * @param boolean $remove_url             Whether to include URL or not.
		 * @param boolean $remove_hidden_hashtags Whether to include hidden hashtags or not.
		 * @param boolean $remove_hidden_urls     Whether to include hidden URLS or not.
		 */
		public function __construct( $text='', $custom_url=false, $custom_twitter_handles=false, 
			$custom_hidden_hashtags=false, $custom_hidden_urls=false, $remove_twitter_handles=false, $remove_url=false,
			$remove_hidden_hashtags=false, $remove_hidden_urls=false ) {

			//	Get values
			$this->my_text = $text;

			if ( $custom_url ) {
				$this->my_url = $custom_url;
			}

			if ( $custom_twitter_handles ) {
				$this->my_twitter_handles = $custom_twitter_handles;
			}

			if ( $custom_hidden_hashtags ) {
				$this->my_hidden_hashtags = $custom_hidden_hashtags;
			}

			if ( $custom_hidden_urls ) {
				$this->my_hidden_urls = $custom_hidden_urls;
			}

			//	If we're removing anything, remove it
			if( $remove_twitter_handles ) {
				$this->my_twitter_handles = '';
			}

			if( $remove_url ) {
				$this->my_url = '';
			}

			if( $remove_hidden_hashtags ) {
				$this->my_hidden_hashtags = '';
			}

			if( $remove_hidden_urls ) {
				$this->my_hidden_urls = '';
			}
		}


		public function display_box() {
			$url = $this->generate_share_url();

			$options = get_option('tt_plugin_options');
			//	Button Text?
			if( array_key_exists( 'button_text_override', $options ) &&
				is_string( $options['button_text_override'] ) &&
				trim( $options['button_text_override'] ) != '' ) {
					
				$btext = $options['button_text_override'];
			}
			else {
				$btext = "Tweet This";
			}


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
							$retval .= '<img src="' . TT_ROOT_URL . 'assets/images/twitter-icons/' . $options['twitter_icon'] . '.png" alt="' . $options['icon_alt_text'] . '" />';
							$retval .= $btext;
						$retval .= '</a>';
					$retval .= '</div>';
					$retval .= '<div style="clear: both; "></div>';
				$retval .= '</div>';
			$retval .= '</div>';


			return $retval;
		}

		public function display_link( $image_too = true ) {
			$url = $this->generate_share_url();

			$options = get_option('tt_plugin_options');
			//	Button Text?
			if( array_key_exists( 'button_text_override', $options ) &&
				is_string( $options['button_text_override'] ) &&
				trim( $options['button_text_override'] ) != '' ) {

				$btext = $options['button_text_override'];
			}
			else {
				$btext = "Tweet This";
			}

			$retval = '';


			$retval .= '<span class="TT_wrapper">';
				$retval .= '<a title="' . htmlentities( $this->generate_text() ) . '" class="TT_tweet_link" href="' . $url . '" target="_blank">';
					if( $image_too ) {
						$retval .= '<img src="' . TT_ROOT_URL . 'assets/images/twitter-icons/' . $options['twitter_icon'] . '.png" />';
					}
					$retval .= $btext;
				$retval .= '</a>';
			$retval .= '</span>';


			return $retval;
		}


		protected function generate_share_url() {
			//	Put all the tweet pieces into an array which we'll implode with a space as glue
			$ttext = array(
				$this->generate_text(),
				$this->generate_hidden_hashtags_for_url(),
				$this->generate_hidden_urls_for_url(),
				$this->generate_post_url(),
				$this->generate_twitter_handles_for_url()
			);
			//	Remove any empty array entries
			$ttext = array_filter( $ttext );
			//	Implode the tweet array into a string.
			$ttext = implode( ' ', $ttext );


			//	Now, generate the URL
			$url = 'http://twitter.com/intent/tweet?text=';
			$url .= urlencode(
				trim(	//	There's a chance there's a trailing space if no URL or Twitter
						//	handles are input.  Remove that.
					html_entity_decode(	//	WordPress encodes special characters in posts. Undo that.
						$ttext,
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

		public function generate_hidden_hashtags_for_url( ) {
			//	Do we need to get the hashtags from the database?
			if( is_null( $this->my_hidden_hashtags ) ) {
				//	Yes
				$options = get_option( 'tt_plugin_options' );

				$this->my_hidden_hashtags = $options['default_hidden_hashtags'];
			}

			if( !empty( $this->my_hidden_hashtags ) ) {
				return $this->my_hidden_hashtags;
			}

			return '';
		}

		public function generate_hidden_urls_for_url(  ) {
			//	Do we need to get the hidden urls from the database?
			if( is_null( $this->my_hidden_urls ) ) {
				//	Yes
				$options = get_option( 'tt_plugin_options' );
				$this->my_hidden_urls = $options['default_hidden_urls'];
			}

			if( !empty( $this->my_hidden_urls ) ) {
				return $this->my_hidden_urls;
			}

			return '';
		}

		public function generate_post_url( $id = 0, $placeholder_flag = false ) {	//	$id = 0 means current post to get_permalink function
			$options = get_option('tt_plugin_options');

			//	Do we need to generate the URL? If URL is disabled, we don't.
			//	If one is already provided, we don't.
			if( $options['disable_url'] ) {
				if( $placeholder_flag ) {
					$this->my_url = "";
					return array('shortlink'=>'', 'is_placeholder'=>false);
				}
				else {
					return "";
				}
			}
			if( is_null( $this->my_url ) ) {
				//	Yes, we need to generate a URL

				//	Okay then, do we need to construct a shortlink

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
