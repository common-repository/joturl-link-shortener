<?php

include_once __DIR__ . '/joturl-base.php';

class JotUrlConfiguration extends JotUrlBase {

	const JOTURL_OPTIONS = 'joturl_global_options';

	static function init() {
		parent::init();
	}

	static function getDefaultOptions() {
		return array(
			'public_key'  => '',
			'private_key' => '',
		);
	}

	static $options = array();

	static function getOption( $n ) {
		if ( array_key_exists( $n, self::$options ) ) {
			return self::$options[ $n ];
		}

		return ( self::$options[ $n ] = get_option( $n ) );
	}

	static function updateOption( $n, $v ) {
		self::$options[ $n ] = $v;

		return update_option( $n, $v );
	}

	static function setConfiguration() {
		$params = array();

		if ( func_num_args() == 2 ) {
			$params[ func_get_arg( 0 ) ] = func_get_arg( 1 );
		} else if ( func_num_args() == 1 ) {
			$params = func_get_arg( 0 );
		}

		if ( ! empty( $params ) && is_array( $params ) ) {
			$joturl_options = self::getOption( self::JOTURL_OPTIONS );
			if ( ! is_array( $joturl_options ) ) {
				$joturl_options = array();
			}
			$joturl_options = array_merge( self::getDefaultOptions(), $joturl_options, $params );

			self::updateOption( self::JOTURL_OPTIONS, $joturl_options );
		}
	}

	static function resetConfiguration() {
		self::updateOption( self::JOTURL_OPTIONS, null );
	}

	static function getConfiguration( $key = '' ) {
		$joturl_options = self::getOption( self::JOTURL_OPTIONS );
		if ( ! is_array( $joturl_options ) ) {
			$joturl_options = array();
		}

		$joturl_options = array_merge( self::getDefaultOptions(), $joturl_options );
		if ( ! empty( $key ) ) {
			if ( is_string( $key ) ) {
				if ( array_key_exists( $key, $joturl_options ) ) {
					return $joturl_options[ $key ];
				}

				return '';
			}

			$tmp = array();

			$keys = array_values( array_unique( array_filter( (array) $key ) ) );
			foreach ( $keys as $key ) {
				if ( array_key_exists( $key, $joturl_options ) ) {
					$tmp[ $key ] = $joturl_options[ $key ];
				}
			}

			return $tmp;
		}

		return $joturl_options;
	}

	static function is_key( $k ) {
		$k = preg_replace( '/[^a-f0-9]/ui', '', $k );

		return strlen( $k ) == 32;
	}

	static function isConfigured() {
		return ( is_email( self::getConfiguration( 'user_email' ) ) && self::is_key( self::getConfiguration( 'public_key' ) ) && self::is_key( self::getConfiguration( 'private_key' ) ) );
	}

	static function uninstall() {
		parent::uninstall();

		delete_option( self::JOTURL_OPTIONS );
	}
}