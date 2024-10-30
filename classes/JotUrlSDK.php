<?php


class JotUrlSDK {

	#base URL for endpoints
	const BaseURL = 'https://joturl.com/a/i1/';

	const SESSION_KEY = 'iu_session_id';

	/**
	 * @var string username to be used in the login
	 */
	private $username = '';

	/**
	 * @var string ID of the login session
	 */
	private $session_id = '';

	/**
	 * @var string private key, https://www.joturl.com/reserved/settings.html#tools-api
	 */
	private $private_key = '';

	/**
	 * @var string public key, https://www.joturl.com/reserved/settings.html#tools-api
	 */
	private $public_key = '';

	private $return_errors = false;

	private $last_error = '';

	/**
	 * Creates an instance of the JotUrl SDK.
	 *
	 * @param string $username    the username used to login into JotURL dashboard
	 * @param string $public_key  public api key, you can find it on https://www.joturl.com/reserved/settings.html#tools-api
	 * @param string $private_key private api key, you can find it on https://www.joturl.com/reserved/settings.html#tools-api
	 */
	function __construct( $username, $public_key, $private_key ) {
		$this->username = $username;
		$this->public_key = $public_key;
		$this->private_key = $private_key;
	}

	function setLastError( $error ) {
		if ( $this->return_errors ) {
			$this->last_error = $error;
		} else {
			throw new Exception( $error );
		}
	}

	function getLastError() {
		return $this->last_error;
	}

	/**
	 * Call and get results from the URL.
	 *
	 * @param string $url URL to be called
	 *
	 * @return bool|array associative array containing the result of the call
	 */
	function call( $url, $postParameters = array(), $retry = true ) {
		try {
			$json = $this->download( $url, $postParameters );
			if ( ! empty( $json ) ) {
				$obj = json_decode( $json, true );

				if ( is_array( $obj ) ) {
					$status = $this->getParam( $obj, 'status', array() );
					$code = $this->getParam( $status, 'code', '' );
					if ( $code != '200' ) {
						$this->setLastError( $this->getParam( $status, 'error' ) . ' (CODE=' . $this->getParam( $status, 'code' ) . ' - STATUS=' . $this->getParam( $status, 'text' ) . ')' );

						return false;
					}

					return $this->getParam( $obj, 'result', array() );
				}
			}

			if ( $retry ) {
				#maybe we are calling APIs too fastly
				sleep( 5 );
				if ( $this->login() ) {
					return $this->call( $url, $postParameters, false );
				}

				$this->setLastError( 'malformed response (mr2)' );

				return false;
			}
		}
		catch ( Throwable $t ) {
			$this->setLastError( $t->getMessage() );
		}

		return false;
	}

	private function download( $url, $postParameters = array(), $args = array() ) {
		$heads = array();
		$heads[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
		$heads[] = 'Accept-Encoding: gzip,deflate';
		$heads[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
		$heads[] = 'Keep-Alive: 115';
		$heads[] = 'Connection: keep-alive';

		if ( isset( $args['headers'] ) ) {
			for ( $i = 0; $i < count( $args['headers'] ); $i ++ ) {
				$ap = $args['headers'][ $i ];
				if ( array_search( $ap, $heads ) === false ) {
					$heads[] = $ap;
				}
			}
			unset( $args['headers'] );
		}

		$args = array_merge(
			array(
				'timeout'     => '30',
				'user-agent'  => 'JotUrl SDK 1.1.WP (https://www.joturl.com/)',
				'cookies'     => '',
				'redirection' => 5,
				'headers'     => $heads,
			), $args
		);

		if ( ! empty( $postParameters ) && is_array( $postParameters ) ) {
			$args['method'] = 'POST';
			$args['body'] = $postParameters;
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			$this->setLastError( $response->get_error_message() . ' (' . $response->get_error_code() . ')' );
			$body = '';
		} else {
			$body = $response['body'];
		}

		return $body;
	}

	/**
	 * Helper that returns the value corresponding to a $key in the associative array $arr.
	 *
	 * @param string $arr an array with keys to check
	 * @param string $key value to check
	 * @param mixed  $def default value to be returned if $key does not exists in $arr
	 *
	 * @return mixed the value of $key in $arr, $def otherwise
	 */
	function getParam( $arr, $key, $def = '' ) {
		return is_array( $arr ) && array_key_exists( $key, $arr ) ? $arr[ $key ] : $def;
	}

	/**
	 * Login into the API interface to call endpoints.
	 *
	 * @return bool true if login is successful, false otherwise
	 */
	function login( $force = false ) {
		if ( ! $force ) {
			$iu_session_id = get_option( self::SESSION_KEY );
			if ( ! empty( $iu_session_id ) ) {
				$this->session_id = $iu_session_id;

				return true;
			}
		}

		#create the login password with PUBLIC_API_KEY
		$password = $this->getHash( $this->public_key );

		#try to login
		$url = $this->buildURL( 'users/login', array( 'username' => $this->username, 'password' => $password ) );
		$result = $this->call( $url, null, false );

		if ( $result !== false ) {
			$session_id = $this->getParam( $result, 'session_id', '' );
			if ( ! empty( $session_id ) ) {
				$this->session_id = $session_id;
				update_option( self::SESSION_KEY, $session_id );

				return true;
			}
		}

		return false;
	}

	private function getHash( $value, $time = null ) {
		return hash_hmac( 'sha256', $value . ':' . ( $time ?: gmdate( "Y-m-d\TH:i\Z", time() ) ), $this->private_key );
	}

	/**
	 * Given an endpoint and parameters (optional) returns the URL to be called.
	 *
	 * @param string $endpoint   endpoint to be called
	 * @param array  $parameters associative array [param => value]
	 *
	 * @return string the URL to be called
	 */
	function buildURL( $endpoint, $parameters = array() ) {
		if ( ! is_array( $parameters ) ) {
			$parameters = array();
		}

		$parameters = http_build_query( array_merge( $parameters, $this->getSecurityParameters() ) );

		return JotUrlSDK::BaseURL . $endpoint . ( ! empty( $parameters ) ? '?' . $parameters : '' );
	}

	/**
	 * @return array security parameters to be used in every call to API endpoints
	 */
	function getSecurityParameters() {
		$parameters = array();

		if ( $this->session_id != '' ) {
			$parameters['_sid'] = $this->session_id;
			$parameters['_h'] = $this->getHash( $this->session_id );
		}

		return $parameters;
	}

	function returnErrors( $bool ) {
		$this->return_errors = $bool;
	}

	static function uninstall() {
		delete_option( self::SESSION_KEY );
	}
}