<?php

/*
Plugin Name: JotUrl Link Shortener
Description: Beta JotUrl plugin for WordPress
Plugin URI: https://www.joturl.com/
Author: JotUrl
Text Domain: joturl-link-shortener
Version: 0.1.5
*/

/*  Copyright 2019  JotUrl

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation using version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ( is_admin() && ! defined( 'JOTURL_PLUGIN_FOLDER' ) && function_exists( 'add_theme_support' ) ) {
	define( 'JOTURL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	define( 'JOTURL_PLUGIN_FOLDER', plugin_dir_path( __FILE__ ) );

	include_once __DIR__ . '/classes/joturl.php';

	JotUrl::init();
}

function joturl_activate() {
	register_uninstall_hook( __FILE__, 'joturl_uninstall' );
}

register_activation_hook( __FILE__, 'joturl_activate' );

function joturl_uninstall() {
	include_once __DIR__ . '/classes/JotUrlSDK.php';
	include_once __DIR__ . '/classes/joturl.php';

	JotUrlSDK::uninstall();
	JotUrl::uninstall();
}