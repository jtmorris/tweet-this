<?php
/**
 * Plugin Name: Tweet This
 * Plugin URI: http://tweetthis.jtmorris.net
 * Description: Embed "share the given text" boxes in your content quickly and easily.
 * Version: 0.1.0
 * Author: John Morris
 * Author URI: http://jtmorris.net
 * License: GPL2
 */

/*  Copyright 2014 - 2015  John Morris  (email : johntylermorris@jtmorris.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define ( 'TT_ROOT_PATH', plugin_dir_path( __FILE__ ) );
define ( 'TT_ROOT_URL', plugin_dir_url( __FILE__ ) );
define ( 'TT_PLUGIN_FILE', TT_ROOT_PATH . 'tweet-this.php' );
define ( 'TT_SUBDIR_AND_FILE', plugin_basename(__FILE__) );
define ( 'TT_FILENAME', __FILE__ );

require_once ( TT_ROOT_PATH . 'includes/setup.php' );

TT_Setup::initialize();