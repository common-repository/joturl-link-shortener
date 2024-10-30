<?php

include_once __DIR__ . '/JotUrlSDK.php';

include_once __DIR__ . '/joturl-pages.php';

class JotUrlAjax extends JotUrlPages {

	const UNABLE_TO_LOGIN = 'Unable to login to JotUrl';

	const INVALID_CONFIGURATION = 'The plugin is not corretly configured';

	const UNABLE_TO_EXEC_AJAX = 'Unable to exec AJAX request';

	static $ajax_endpoints = array(
		'domains_list',
		'tags_list',
		'projects_list',
		'conversions_list',
		'remarketings_list',
		'ctas_list',
		'utm_templates_list',
		'utm_template_info',
		'parse_url',
		'merge_url_parameters',
		'create_project',
		'create_tl',
		'edit_tl',
		'delete_tl',
		'info_tl',
		'setpostinfo',
		'create_utm_template',
	);

	static $utms = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' );

	static function init() {
		parent::init();

		foreach ( self::$ajax_endpoints as $endpoint ) {
			add_action( 'wp_ajax_' . $endpoint, array( __CLASS__, $endpoint ) );
		}
	}

	static function joturlWrapper( $callback, $retry = true, $force_login = false ) {
		try {
			if ( JotUrl::isConfigured() ) {
				$user_email = JotUrl::getConfiguration( 'user_email' );
				$public_key = JotUrl::getConfiguration( 'public_key' );
				$private_key = JotUrl::getConfiguration( 'private_key' );

				$joturl = new JotUrlSDK( $user_email, $public_key, $private_key );

				if ( $joturl->login( $force_login ) ) {
					if ( is_callable( $callback ) ) {
						$callback( $joturl );
					} else {
						self::emitError( __( self::UNABLE_TO_EXEC_AJAX, 'joturl-link-shortener' ) );
					}
				} else {
					self::emitError( __( self::UNABLE_TO_LOGIN, 'joturl-link-shortener' ) );
				}
			} else {
				self::emitError( __( self::INVALID_CONFIGURATION, 'joturl-link-shortener' ) );
			}
		}
		catch ( Throwable $t ) {
			$msg = $t->getMessage();
			if ( strpos( $msg, 'INVALID session' ) !== false ) {
				if ( $retry ) {
					self::joturlWrapper( $callback, false, true );

					return;
				}
				$msg = __( 'You are not logged in, please navigate to the plugin settings and check your credentials and click on "Set login credentials."', 'joturl-link-shortener' );
			}
			self::emitError( $msg );
		}
	}

	static function getDefaultJSON() {
		return array(
			'results'    => array(),
			'pagination' => array(
				'more' => false,
			),
		);
	}

	static function emitJSON( $a ) {
		if ( array_key_exists( 'results', $a ) ) {
			$a['results'] = array_values( $a['results'] );
		}
		wp_send_json( $a );
	}

	static function emitError( $e ) {
		wp_send_json_error( array( 'error' => $e ) );
	}

	static function generic_ajax_list( $endpoint, $fields = array(), $add_params = array(), $merge_results = array(), $processResults = null ) {
		self::joturlWrapper( function ( &$joturl ) use ( $endpoint, $fields, $add_params, $merge_results, $processResults ) {
			$a = array_merge( self::getDefaultJSON(), $merge_results );

			$length = 10;
			$params = array_merge( array(
				'length' => $length,
			), $add_params );
			if ( ! empty( $fields ) ) {
				$params['fields'] = implode( ',', array_map( function ( $v ) {
					return strtolower( trim( $v ) );
				}, array_values( array_unique( array_filter( $fields ) ) ) ) );
			}
			$q = self::getRequest( 'q' );
			if ( ! empty( $q ) ) {
				$params['search'] = $q;
			}
			$page = max( 1, self::getRequest( 'page', 1 ) );
			$params['start'] = $length * ( $page - 1 );

			$url = $joturl->buildURL( $endpoint, $params );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				$count = $joturl->getParam( $result, 'count', 0 );
				if ( $count ) {
					$data = $joturl->getParam( $result, 'data', array() );
					foreach ( $data as $d ) {
						$d['text'] = $d['name'];
						if ( is_callable( $processResults ) ) {
							$d = $processResults( $d );
						}
						$a['results'][] = $d;
					}
					$len = count( $a['results'] );
					$a['pagination']['more'] = ( $len && $count > $params['start'] + $len );
				}

				self::emitJSON( $a );
			}
		} );
	}

	static function domains_list() {
		$params = array();

		$default = self::getRequest( 'default' );
		if ( $default == 1 ) {
			$params['is_default'] = 1;
		}

		self::generic_ajax_list( 'domains/list', array( 'count', 'id', 'name', 'is_default', 'is_owner', 'host' ), $params );
	}

	static function projects_list() {
		$params = array( 'orderby' => 'name', 'sort' => 'asc' );

		$default = self::getRequest( 'default' );
		if ( $default == 1 ) {
			$a['results'][] = array( 'id' => '', 'text' => __( 'Select a conversion', 'joturl-link-shortener' ) );
		}

		self::generic_ajax_list( 'projects/list', array( 'count', 'id', 'name' ), $params );
	}

	static function conversions_list() {
		$a = self::getDefaultJSON();
		$a['results'][] = array( 'id' => '-1', 'text' => '# ' . __( 'no conversion code', 'joturl-link-shortener' ) );

		$default = self::getRequest( 'default' );
		if ( $default == 1 ) {
			$a['pagination']['more'] = true;
			self::emitJSON( $a );
		}

		$params = array( 'orderby' => 'name', 'sort' => 'asc' );

		self::generic_ajax_list( 'conversions/codes/list', array( 'count', 'id', 'name' ), $params, $a );
	}

	static function remarketings_list() {
		$a = self::getDefaultJSON();
		$a['results'][] = array( 'id' => '-1', 'text' => '# ' . __( 'no remarketing pixel', 'joturl-link-shortener' ) );

		$default = self::getRequest( 'default' );
		if ( $default == 1 ) {
			$a['pagination']['more'] = true;
			self::emitJSON( $a );
		}

		$params = array( 'orderby' => 'name', 'sort' => 'asc' );

		self::generic_ajax_list( 'remarketings/list', array( 'count', 'id', 'name', 'code_type' ), $params, $a, function ( $row ) {
			$row['text'] = strtoupper( $row['code_type'] ) . ' - ' . $row['text'];

			return $row;
		} );
	}

	static function ctas_list() {
		$a = self::getDefaultJSON();
		$a['results'][] = array( 'id' => '-1', 'text' => '# ' . __( 'no call to action', 'joturl-link-shortener' ) );

		$default = self::getRequest( 'default' );
		if ( $default == 1 ) {
			$a['pagination']['more'] = true;
			self::emitJSON( $a );
		}

		$params = array( 'orderby' => 'name', 'sort' => 'asc' );

		self::generic_ajax_list( 'ctas/list', array( 'count', 'id', 'name', 'type' ), $params, $a, function ( $row ) {
			$row['text'] = strtoupper( $row['type'] ) . ' - ' . $row['text'];

			return $row;
		} );
	}

	static function utm_templates_list() {
		$a = self::getDefaultJSON();
		$a['results'][] = array( 'id' => '-1', 'text' => '# ' . __( 'custom UTM parameters', 'joturl-link-shortener' ) );

		$default = self::getRequest( 'default' );
		if ( $default == 1 ) {
			$a['pagination']['more'] = true;
			self::emitJSON( $a );
		}

		$params = array( 'orderby' => 'name', 'sort' => 'asc' );

		self::generic_ajax_list( 'utms/list', array_merge( array( 'count', 'id', 'name' ), self::$utms ), $params, $a );
	}

	static function utm_template_info() {
		$id = self::getRequest( 'id' );
		if ( empty( $id ) ) {
			self::emitError( __( 'UTM template ID is required', 'joturl-link-shortener' ) );
		}

		self::joturlWrapper( function ( &$joturl ) use ( $id ) {
			$url = $joturl->buildURL( 'utms/info', array( 'id' => $id, 'fields' => implode( ',', self::$utms ) ) );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				$result = self::getParam( $result, 'data', array() );
				$result = self::getParam( $result, 0, array() );

				self::emitJSON( $result );
			}
		} );
	}

	static function tags_list() {
		$a = self::getDefaultJSON();

		$params = array(
			'orderby'    => 'name',
			'hide_empty' => false,
		);
		$term = self::getRequest( 'term' );
		if ( ! empty( $term ) ) {
			$params['search'] = $term;
		}

		$tags = get_tags( $params );
		foreach ( $tags as $tag ) {
			$a[] = preg_replace( '/[^a-z0-9_\\-]/ui', '', str_replace( ' ', '_', trim( $tag->name ) ) );
		}

		self::emitJSON( $a );
	}

	static function parse_url() {
		$a = array(
			'scheme'   => '',
			'host'     => '',
			'port'     => '',
			'user'     => '',
			'pass'     => '',
			'path'     => '',
			'query'    => '',
			'fragment' => '',
			'params'   => '',
		);

		$u = self::getRequest( 'u' );
		if ( ( $u = filter_var( $u, FILTER_SANITIZE_URL ) ) && filter_var( $u, FILTER_VALIDATE_URL ) !== false ) {
			$a = array_merge( $a, parse_url( $u ) );
			if ( array_key_exists( 'query', $a ) && ! empty( $a['query'] ) ) {
				parse_str( $a['query'], $a['params'] );
			}
		}

		self::emitJSON( $a );
	}

	static function merge_url_parameters() {
		$a = array(
			'url' => '',
		);

		$u = self::getRequest( 'u' );
		if ( ! empty( $u ) ) {
			$p = self::getRequest( 'p' );
			if ( $p && ( $p = @json_decode( $p, true ) ) ) {
				$u = parse_url( $u );
				$u['query'] = http_build_query( $p );
				$a['url'] = self::unparse_url( $u );
			} else {
				self::emitError( json_last_error_msg() );
			}
		} else {
			self::emitError( __( 'empty parameter u', 'joturl-link-shortener' ) );
		}


		self::emitJSON( $a );
	}

	static function create_project() {
		$name = self::getRequest( 'name' );
		if ( ! empty( $name ) ) {
			$notes = self::getRequest( 'notes' );

			self::joturlWrapper( function ( &$joturl ) use ( $name, $notes ) {
				$a = array(
					'id'    => '',
					'name'  => '',
					'notes' => '',
				);

				$url = $joturl->buildURL( 'projects/add', array( 'name' => $name, 'client' => $notes ) );
				if ( ( $result = $joturl->call( $url ) ) !== false ) {
					$a['id'] = self::getParam( $result, 'id' );
					$a['name'] = self::getParam( $result, 'name' );
					$a['notes'] = self::getParam( $result, 'client' );

					self::emitJSON( $a );
				}
			} );
		} else {
			self::emitError( __( 'Please enter a valid project name', 'joturl-link-shortener' ) );
		}
	}

	static function create_utm_template() {
		$name = self::getRequest( 'name' );
		if ( ! empty( $name ) ) {
			$utm_source = self::getRequest( 'utm_source' );
			if ( ! empty( $utm_source ) ) {
				$params = array( 'name' => $name, 'utm_source' => $utm_source );

				foreach ( self::$utms as $k ) {
					$params[ $k ] = self::getRequest( $k );
				}

				self::joturlWrapper( function ( &$joturl ) use ( $params ) {
					$a = array();

					$url = $joturl->buildURL( 'utms/add', $params );
					if ( ( $result = $joturl->call( $url ) ) !== false ) {
						$a['id'] = self::getParam( $result, 'id' );
						$a['name'] = self::getParam( $result, 'name' );

						foreach ( self::$utms as $k ) {
							$a[ $k ] = self::getParam( $result, $k );
						}

						self::emitJSON( $a );
					}
				} );
			} else {
				self::emitError( __( 'Please enter a valid utm_source parameter', 'joturl-link-shortener' ) );
			}
		} else {
			self::emitError( __( 'Please enter a valid UTM template name', 'joturl-link-shortener' ) );
		}
	}

	static function addIDsToTL( &$joturl, $id, $ids, $endpoint_prefix, $is_edit = false ) {
		if ( ! empty( $joturl ) && ! empty( $id ) ) {
			$params = array(
				'url_id' => $id,
			);

			if ( is_array( $ids ) && ! empty( $ids ) ) {
				$method = 'edit';
				$params['ids'] = implode( ',', $ids );
			} else if ( $is_edit ) {
				$method = 'delete';
			} else {
				return true;
			}

			$url = $joturl->buildURL( 'urls/' . $endpoint_prefix . '/' . $method, $params );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function getIDsFromTL( &$joturl, $id, &$result, $endpoint_prefix ) {
		if ( ! empty( $joturl ) ) {
			$params = array(
				'fields' => 'id,name',
				'url_id' => $id,
			);
			if ( $endpoint_prefix == 'remarketings' ) {
				$params['fields'] .= ',code_type';
			}
			$url = $joturl->buildURL( 'urls/' . $endpoint_prefix . '/list', $params );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				$result = self::getParam( $result, 'data', array() );

				$tmp = array();
				foreach ( $result as $r ) {
					if ( array_key_exists( 'name', $r ) ) {
						if ( $endpoint_prefix == 'remarketings' ) {
							$r['text'] = strtoupper( $r['code_type'] ) . ' - ' . $r['name'];
						} else {
							$r['text'] = $r['name'];
						}
					}
					$tmp[] = $r;
				}
				$result = $tmp;

				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function addCTAToTL( &$joturl, $id, $cta_id, $is_edit = false ) {
		if ( ! empty( $joturl ) && ! empty( $id ) ) {
			$params = array(
				'url_id' => $id,
			);

			if ( ! empty( $cta_id ) ) {
				$method = 'edit';
				$params['id'] = $cta_id[0];
			} else if ( $is_edit ) {
				$method = 'delete';
			} else {
				return true;
			}

			$url = $joturl->buildURL( 'urls/ctas/' . $method, $params );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function getCTAFromTL( &$joturl, $id, &$result ) {
		if ( ! empty( $joturl ) ) {
			$url = $joturl->buildURL( 'urls/ctas/info', array(
				'fields' => 'id,name,type',
				'url_id' => $id,
			) );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				if ( is_array( $result ) && array_key_exists( 'type', $result ) && array_key_exists( 'name', $result ) ) {
					$result['text'] = strtoupper( $result['type'] ) . ' - ' . $result['name'];
				} else {
					$result = null;
				}

				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function setParameterFromTL( &$joturl, $id, $queryParameters = array() ) {
		if ( ! empty( $joturl ) ) {
			if ( empty( $queryParameters ) ) {
				$queryParameters = array();
			}
			$p = array(
				'url_id' => $id,
			);
			if ( array_key_exists( 'params', $queryParameters ) ) {
				$p['params'] = $queryParameters['params'];
				unset( $queryParameters['params'] );
				$tmp = array();
				foreach ( $p['params'] as $i ) {
					$k = self::getParam( $i, 'key' );
					if ( ! empty( $k ) ) {
						$tmp[ $k ] = self::getParam( $i, 'value' );
					}
				}
				$p['params'] = $tmp;
				if ( empty( $p['params'] ) ) {
					unset( $p['params'] );
				}
			}
			$p = array_merge( $p, $queryParameters );

			$url = $joturl->buildURL( 'urls/parameters/edit', $p );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function getParameterFromTL( &$joturl, $id, &$result ) {
		if ( ! empty( $joturl ) ) {
			$url = $joturl->buildURL( 'urls/parameters/info', array(
				'url_id' => $id,
			) );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				if ( array_key_exists( 'params', $result ) && ! empty( $result['params'] ) ) {
					$tmp = array();
					foreach ( $result['params'] as $k => $v ) {
						$tmp[] = array( 'key' => $k, 'value' => $v );
					}
					$result['params'] = $tmp;
				}
				$result['utm_template_id'] = self::getParam( $result, 'utm_template_id' ) ?: '-1';
				$result['utm_template_name'] = self::getParam( $result, 'name' ) ?: '# ' . __( 'custom UTM parameters', 'joturl-link-shortener' );
				unset( $result['name'] );

				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function addTagsToTL( &$joturl, $id, $tags ) {
		$tags = trim( $tags );
		if ( ! empty( $joturl ) && ! empty( $tags ) ) {
			$url = $joturl->buildURL( 'urls/tags/edit', array(
				'tags'   => $tags,
				'url_id' => $id,
			) );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				return true;
			} else {
				return $joturl->getLastError();
			}
		}

		return true;
	}

	static function edit_create_tl( $endpoint, $is_edit = false ) {
		$inputs = array(
			'id'          => '',
			'long_url'    => '',
			'alias'       => '',
			'domain_id'   => '',
			'project_id'  => '',
			'notes'       => '',
			'tags'        => '',
			'conversion'  => '',
			'remarketing' => '',
			'cta'         => '',
			'error'       => '',
			'query'       => '',
		);

		foreach ( array_keys( $inputs ) as $key ) {
			$inputs[ $key ] = self::getRequest( $key );
		}

		$inputs['conversion'] = array_values( array_unique( array_filter( explode( ',', trim( $inputs['conversion'] ) ) ) ) );
		$inputs['remarketing'] = array_values( array_unique( array_filter( explode( ',', trim( $inputs['remarketing'] ) ) ) ) );
		$inputs['cta'] = array_slice( array_values( array_unique( array_filter( explode( ',', trim( $inputs['cta'] ) ) ) ) ), 0, 1 );
		if ( is_array( $inputs['tags'] ) ) {
			$inputs['tags'] = preg_replace( '/\s+/ui', ' ', implode( ' ', $inputs['tags'] ) );
		}
		$inputs['tags'] = implode( ' ', array_values( array_unique( array_filter( preg_split( '/[,\s]/ui', trim( $inputs['tags'] ) ) ) ) ) );

		if ( empty( $inputs['long_url'] ) ) {
			self::emitError( __( 'Please enter a valid destination URL', 'joturl-link-shortener' ) );
		}

		if ( $is_edit ) {
			if ( empty( $inputs['id'] ) ) {
				self::emitError( __( 'Tracking link ID is missing', 'joturl-link-shortener' ) );
			}
			unset( $inputs['alias'] );
			unset( $inputs['domain_id'] );
		} else {
			if ( empty( $inputs['alias'] ) ) {
				self::emitError( __( 'Please enter a valid alias', 'joturl-link-shortener' ) );
			}

			if ( empty( $inputs['domain_id'] ) ) {
				self::emitError( __( 'Please select a domain', 'joturl-link-shortener' ) );
			}
		}

		if ( empty( $inputs['project_id'] ) ) {
			self::emitError( __( 'Please select a project', 'joturl-link-shortener' ) );
		}

		if ( ! empty( $inputs['remarketing'] ) && ! empty( $inputs['cta_id'] ) ) {
			self::emitError( __( 'Remarketing pixels and calls to action can not be activated at the same time', 'joturl-link-shortener' ) );
		}

		$extra = array(
			'conversion'  => $inputs['conversion'],
			'remarketing' => $inputs['remarketing'],
			'cta'         => $inputs['cta'],
			'query'       => $inputs['query'],
		);
		unset( $inputs['conversion'] );
		unset( $inputs['remarketing'] );
		unset( $inputs['cta_id'] );
		unset( $inputs['query'] );
		if ( $is_edit ) {
			$extra['tags'] = $inputs['tags'];
			unset( $inputs['tags'] );
		}

		self::joturlWrapper( function ( &$joturl ) use ( $inputs, $extra, $endpoint, $is_edit ) {
			$url = $joturl->buildURL( $endpoint, $inputs );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				$id = self::getParam( $result, 'id' );
				if ( empty( $id ) ) {
					self::emitError( __( 'Returned information cannot be parsed', 'joturl-link-shortener' ) );
				}

				$joturl->returnErrors( true );
				if ( ( $error = self::addIDsToTL( $joturl, $id, $extra['conversion'], 'conversions', $is_edit ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( ( $error = self::addIDsToTL( $joturl, $id, $extra['remarketing'], 'remarketings', $is_edit ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( ( $error = self::addCTAToTL( $joturl, $id, $extra['cta'], $is_edit ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( ( $error = self::setParameterFromTL( $joturl, $id, $extra['query'] ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( $is_edit && array_key_exists( 'tags', $extra ) ) {
					if ( ( $error = self::addTagsToTL( $joturl, $id, $extra['tags'] ) ) !== true ) {
						$result['error'] = $error;
					}
				}
				$joturl->returnErrors( false );

				self::emitJSON( $result );
			}
		} );
	}

	static function create_tl() {
		self::edit_create_tl( 'urls/shorten' );
	}

	static function edit_tl() {
		self::edit_create_tl( 'urls/edit', true );
	}

	static function delete_tl() {
		$id = self::getRequest( 'id' );
		if ( empty( $id ) ) {
			self::emitError( __( 'Tracking link ID is required', 'joturl-link-shortener' ) );
		}

		$post_id = self::getRequest( 'pid' );

		self::joturlWrapper( function ( &$joturl ) use ( $id, $post_id ) {
			$url = $joturl->buildURL( 'urls/delete', array( 'ids' => $id ) );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				if ( ! empty( $post_id ) ) {
					self::setpostinfo( $post_id );
				}
				self::emitJSON( array( 'deleted' => 1 ) );
			}
		} );
	}

	static function setpostinfo( $post_id = null ) {
		$post_id = $post_id ?: self::getRequest( 'pid' );
		if ( empty( $post_id ) ) {
			self::emitError( __( 'Post ID is required', 'joturl-link-shortener' ) );
		}

		$info = self::getRequest( 'info' );

		if ( empty( $info ) || ( $info = @json_decode( $info, true ) ) ) {
			if ( self::updateLinkInfo( $post_id, $info ) ) {
				$html = implode( "\n", self::getColumnValue( $post_id ) );
				if ( empty( $html ) ) {
					self::emitError( __( 'Error while retrieving post information', 'joturl-link-shortener' ) );
				}

				self::emitJSON( array(
					'html' => $html,
				) );
			} else {
				self::emitError( __( 'Error while updating tracking link information', 'joturl-link-shortener' ) );
			}
		} else {
			self::emitError( __( 'Error while decoding tracking link information', 'joturl-link-shortener' ) );
		}

		self::emitError( __( 'Unknown error', 'joturl-link-shortener' ) );
	}

	static function info_tl( $post_id = null ) {
		$post_id = $post_id ?: self::getRequest( 'pid' );
		if ( empty( $post_id ) ) {
			self::emitError( __( 'Post ID is required', 'joturl-link-shortener' ) );
		}

		$info = self::getLinkInfo( $post_id );
		if ( empty( $info ) ) {
			self::emitError( __( 'Post has no linked tracking link', 'joturl-link-shortener' ) );
		}

		$id = self::getParam( $info, 'id' );
		if ( empty( $id ) ) {
			self::emitError( __( 'Unable to find linked tracking link', 'joturl-link-shortener' ) );
		}

		self::joturlWrapper( function ( &$joturl ) use ( $id ) {
			$url = $joturl->buildURL( 'urls/info', array( 'id' => $id, 'fields' => 'id,short_url,long_url,project_id,project_name,tags,notes,domain_host,domain_id,alias' ) );
			if ( ( $result = $joturl->call( $url ) ) !== false ) {
				$result = self::getParam( $result, 'data', array() );
				$result = self::getParam( $result, 0, array() );

				$joturl->returnErrors( true );
				if ( ( $error = self::getIDsFromTL( $joturl, $id, $result['conversion'], 'conversions' ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( ( $error = self::getIDsFromTL( $joturl, $id, $result['remarketing'], 'remarketings' ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( ( $error = self::getCTAFromTL( $joturl, $id, $result['cta'] ) ) !== true ) {
					$result['error'] = $error;
				}
				if ( ( $error = self::getParameterFromTL( $joturl, $id, $result['query'] ) ) !== true ) {
					$result['error'] = $error;
				}
				$joturl->returnErrors( false );

				self::emitJSON( $result );
			}
		} );
	}
}