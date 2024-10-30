<?php

include_once __DIR__ . '/joturl-configuration.php';

class JotUrlPages extends JotUrlConfiguration {

	static $pages = array( 'joturl-options' );

	static function init() {
		parent::init();
	}

	static function getPageFile( $page ) {
		if ( in_array( $page, self::$pages ) ) {
			return JOTURL_PLUGIN_FOLDER . 'pages/' . $page . '.php';
		}

		return '';
	}

	static function showPage() {
		$page = self::getVar( 'page' );

		$fn = self::getPageFile( $page );
		if ( ! empty( $fn ) ) {
			if ( ! file_exists( $fn ) ) {
				$fn = self::getPageFile( 'joturl-options' );
			}

			include $fn;
		}
	}
}