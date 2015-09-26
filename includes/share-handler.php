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
		protected $my_post_id = 0;

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
		 * @param int     $post_id                The ID# of the post. Defaults to zero, which to WordPress means 
		 *                                        the current post. When using AJAX, post ID must be specified as there
		 *                                        is no current post context.
		 */
		public function __construct( $text='', $custom_url=false, $custom_twitter_handles=false, 
			$custom_hidden_hashtags=false, $custom_hidden_urls=false, $remove_twitter_handles=false, $remove_url=false,
			$remove_hidden_hashtags=false, $remove_hidden_urls=false, $post_id=0 ) {

			//	Get values
			$this->my_text    = $text;
			$this->my_post_id = $post_id;

			if ( !empty( $custom_url ) ) {
				$this->my_url = $custom_url;
			}

			if ( !empty( $custom_twitter_handles ) ) {
				$this->my_twitter_handles = $custom_twitter_handles;
			}

			if ( !empty( $custom_hidden_hashtags ) ) {
				$this->my_hidden_hashtags = $custom_hidden_hashtags;
			}

			if ( !empty( $custom_hidden_urls ) ) {
				$this->my_hidden_urls = $custom_hidden_urls;
			}

			//	If we're removing anything, remove it
			//	The variable may contain string representation of booleans (e.g. "false"),
			//	so filter the variable.
			if( filter_var( $remove_twitter_handles, FILTER_VALIDATE_BOOLEAN ) ) {
				$this->my_twitter_handles = '';
			}

			if( filter_var( $remove_url, FILTER_VALIDATE_BOOLEAN ) ) {
				$this->my_url = '';
			}

			if( filter_var( $remove_hidden_hashtags, FILTER_VALIDATE_BOOLEAN ) ) {
				$this->my_hidden_hashtags = '';
			}

			if( filter_var( $remove_hidden_urls, FILTER_VALIDATE_BOOLEAN ) ) {
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
						$retval .= $this->generate_visible_text();
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
				$retval .= '<a title="' . htmlentities( $this->generate_visible_text() ) . '" class="TT_tweet_link" href="' . $url . '" target="_blank">';
					if( $image_too ) {
						$retval .= '<img src="' . TT_ROOT_URL . 'assets/images/twitter-icons/' . $options['twitter_icon'] . '.png" />';
					}
					$retval .= $btext;
				$retval .= '</a>';
			$retval .= '</span>';


			return $retval;
		}


		protected function generate_share_url() {
			$ttext = self::generate_actual_text();

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

		public function generate_actual_text() {
			//	Get tweet template
			$options = get_option( 'tt_plugin_options' );

			$template = null;
			if( array_key_exists( 'template', $options ) ) {
				$template = $options['template'];
			}
			if( empty( $template ) ) {	//	Use default value
				$template = '{{{text}}}{{ {hidden_hashtags}}}{{ {hidden_urls}}}{{ {post_url}}}{{ via {twitter_handles}}}';
			}

			//	Substitute values in place of template tags
			$telem_names = array(
				'{text}',
				'{hidden_hashtags}',
				'{hidden_urls}',
				'{post_url}',
				'{twitter_handles}'
			);
			$telem_vals = array(
				$this->generate_visible_text(),
				$this->generate_hidden_hashtags_for_url(),
				$this->generate_hidden_urls_for_url(),
				$this->generate_post_url( $this->my_post_id ),
				$this->generate_twitter_handles_for_url()
			);

			$re = "/[{{].*?.*?}}{2,2}/";
			preg_match_all( $re, $template, $split_t );	//	Split by template tag
			$split_t = $split_t[0];
			//	$split_t should have array like this:
			//		array(
			//			{{{text}}},
			//			{{ {hidden_hashtags}}},
			//			{{ {hidden_urls}}},
			//			{{ {post_url}}},
			//			{{ via {twitter_handles}}}
			//		)
			foreach( $split_t as $tkey=>$t ) {
				foreach( $telem_names as $nkey=>$name ) {
					//	Does $t even contain this name?
					if( strpos( $t, $name ) !== false ) {
						//	Yes, this name is in our $t... process it.
						$value = $telem_vals[$nkey];
						if( !empty( $value ) ) {
							//	Replace template tag with this value
							$split_t[$tkey] = str_replace( $name, $value, $t );
						}
						else {
							//	No value to put in... delete this entry altogether
							unset( $split_t[$tkey] );
						}
					}
				}
			}

			//	Delete template tag openers and closers, and remove altogether if nothing is left
			foreach( $split_t as $i=>$v ) {
				$nv = str_replace( '{{', '', $v );
				$nv = str_replace( '}}', '', $nv );

				if( empty( $nv ) ) {
					//	This thing is empty... delete it
					unset( $split_t[$i] );
				}
				else {
					//	Replace with this new value
					$split_t[$i] = $nv;
				}
			}

			//	Implode the string
			$end_result = implode( '', $split_t );

			return $end_result;
		}

		protected function generate_visible_text() {
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
					return $this->my_twitter_handles;
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
			if( $options['disable_url'] && is_null( $this->my_url ) ) {
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
					echo "HERE";
					//	Yes, we need a shortlink.

					//	Try getting one the default way
					$short = wp_get_shortlink( $id );



					if ( empty( $short ) ) {	//	No shortlink for whatever reason
						//	Try using custom functions provided by popular shortlink pluings
						//	Shortn.it: https://wordpress.org/plugins/shortnit/
						if( function_exists( 'the_full_shortn_url' ) ) {
							$short = get_the_full_shortn_url();
						}

						//	Out of ideas... just use permalink I guess
						else {
							$short = get_permalink( $id );
						}
					}


					//	Okay. Now, if the post is not yet published, the shortlink
					//	will be the default WordPress shortlink (the domain and
					//	post id: http://domain.com/?p=123). Most shortlink
					//	customization plugins, like JetPack's WP.me module and
					//	WP Bitly don't generate shortlinks until after the post
					//	is published.
					//
					//	This means that the shortlink we just worked our butts
					//	off to get... might not be correct.  The shortlink
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
