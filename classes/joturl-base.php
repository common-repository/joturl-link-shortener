<?php

class JotUrlBase {

	const COLUMN_OPTIONS_ID = 'joturl_options';

	const META_INFO_KEY = 'joturl_link_info';

	static function init() {

	}

	static function uninstall() {

	}

	static function stripSlashes( $r ) {
		if ( is_string( $r ) ) {
			return trim( stripslashes( $r ) );
		} else if ( is_array( $r ) ) {
			$tmp = array();

			foreach ( $r as $k => $v ) {
				$tmp[ $k ] = self::stripSlashes( $v );
			}

			return $tmp;
		}

		return $r;
	}

	static function getParam( $arr, $key, $default = '' ) {
		if ( is_array( $arr ) && array_key_exists( $key, $arr ) ) {
			return self::stripSlashes( $arr[ $key ] );
		}

		return $default;
	}

	static function getVar( $key, $default = '' ) {
		return self::getParam( $_GET, $key, $default );
	}

	static function getRequest( $key, $default = '' ) {
		return self::getParam( $_REQUEST, $key, $default );
	}

	function unparse_url( $parsed_url ) {
		$parsed_url = array_filter(
			$parsed_url,
			function ( $var ) {
				return is_string( $var ) && trim( $var ) != '';
			}
		);
		$scheme = isset( $parsed_url['scheme'] ) ? ( $parsed_url['scheme'] == '//' ? '//' : $parsed_url['scheme'] . '://' ) : '';
		$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$port = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
		$user = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
		$pass = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
		$pass = ( $user || $pass ) ? "$pass@" : '';
		$path = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
		$query = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
		$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';

		return "$scheme$user$pass$host$port$path$query$fragment";
	}

	static function getIcon( $name ) {
		$classes = array( 'joturl-icon', 'joturlwp_icon_' . $name );

		return '<i class="' . esc_attr( implode( ' ', $classes ) ) . '"></i>';
	}

	static function convertAttrs( $attrs ) {
		$result = array();

		foreach ( $attrs as $k => $v ) {
			$result[] = $k . '="' . esc_attr( $v ) . '"';
		}

		$result = implode( ' ', $result );

		return ! empty( $result ) ? ' ' . $result : '';
	}

	static function getImageIcon( $icon, $attrs = array() ) {
		if ( ! empty( $icon ) ) {
			return str_replace( '<svg ', '<svg class="joturl-icon" ', $icon );
		}

		return '';
	}

	static function getLink( $icon, $attrs = array() ) {
		if ( ! empty( $icon ) ) {
			$attrs = array_merge( array(
				'href'    => '#',
				'class'   => 'joturl-link',
				'onclick' => 'return false',
				'onfocus' => 'this.blur()',
			), $attrs );

			return '<a' . self::convertAttrs( $attrs ) . '>' . $icon . '</a>';
		}

		return '';
	}

	static function addColumns( $cols, $after = '' ) {
		$tmp = array();

		$added = false;
		foreach ( $cols as $k => $v ) {
			$tmp[ $k ] = $v;
			if ( $k == $after ) {
				$added = true;
				$tmp[ self::COLUMN_OPTIONS_ID ] = __( 'JotUrl', 'joturl-link-shortener' );
			}
		}

		if ( ! $added ) {
			$tmp[ self::COLUMN_OPTIONS_ID ] = __( 'JotUrl', 'joturl-link-shortener' );
		}

		return $tmp;
	}

	static function updateLinkInfo( $post_id, $info ) {
		if ( empty( $info ) ) {
			return delete_post_meta( $post_id, self::META_INFO_KEY );
		}

		update_post_meta( $post_id, self::META_INFO_KEY, $info );

		return true;
	}

	static function getLinkInfo( $post_id ) {
		$joturl_link_info = get_post_meta( $post_id, self::META_INFO_KEY, array() );

		return self::getParam( $joturl_link_info, 0, array() );
	}

	static function getColumnValue( $post_id ) {
		$result = array();

		$joturl_link_info = self::getLinkInfo( $post_id );
		$id = self::getParam( $joturl_link_info, 'id' );
		if ( empty( $id ) ) {
			$url = get_permalink( $post_id );

			$attrs = array(
				'data-pid' => $post_id,
				'data-url' => $url,
				'class'    => 'joturl-shorten-link',
				'title'    => __( 'shorten URL', 'joturl-link-shortener' ),
			);

			$result[] = self::getLink( self::getImageIcon( self::getIcon( 'shorten' ) ), $attrs );
		} else {
			$short_url = self::getParam( $joturl_link_info, 'short_url' );

			$attrs = array(
				'data-pid' => $post_id,
				'data-id'  => $id,
				'class'    => 'joturl-edit-link',
				'title'    => sprintf( __( "edit tracking link '%s'", 'joturl-link-shortener' ), $short_url ),
			);

			$result[] = self::getLink( self::getImageIcon( self::getIcon( 'edit' ) ), $attrs );

			$info = array(
				'page' => 'options',
				'id'   => $id,
			);
			$attrs = array(
				'data-pid' => $post_id,
				'data-id'  => $id,
				'href'     => 'https://joturl.com/reserved/projects.html#c=' . urlencode( json_encode( $info ) ),
				'target'   => '_blank',
				'onclick'  => 'return true',
				'class'    => 'joturl-edit-on-joturl',
				'title'    => sprintf( __( "edit tracking link '%s' on the JotUrl dashboard", 'joturl-link-shortener' ), $short_url ),
			);

			$result[] = self::getLink( self::getImageIcon( self::getIcon( 'joturl' ) ), $attrs );

			$attrs = array(
				'data-pid' => $post_id,
				'data-id'  => $id,
				'data-url' => $short_url,
				'class'    => 'joturl-copy-link',
				'title'    => sprintf( __( "copy '%s' to clipboard" ), $short_url ),
			);

			$result[] = self::getLink( self::getImageIcon( self::getIcon( 'copy' ) ), $attrs );

			$attrs = array(
				'href'    => $short_url,
				'onclick' => 'return true',
				'target'  => '_blank',
				'title'   => sprintf( __( "navigate to '%s'" ), $short_url ),
			);

			$result[] = self::getLink( self::getImageIcon( self::getIcon( 'eye' ) ), $attrs );

			$attrs = array(
				'data-pid' => $post_id,
				'data-id'  => $id,
				'data-url' => $short_url,
				'class'    => 'joturl-delete-link',
				'title'    => sprintf( __( "delete tracking link '%s'" ), $short_url ),
			);

			$result[] = self::getLink( self::getImageIcon( self::getIcon( 'delete' ) ), $attrs );
		}

		return $result;
	}

	static function setColumnValues( $column_name, $post_id ) {
		if ( is_null( $column_name ) || $column_name == self::COLUMN_OPTIONS_ID ) {
			echo implode( "\n", self::getColumnValue( $post_id ) );
		}
	}

	static $info = array();

	static function addMessage( $key, $msg ) {
		self::$info[ $key ] = $msg;
	}

	static function emitMessages() {
		foreach ( self::$info as $key => $msg ) {
			?>
            <div class="notice notice-info is-dismissible"><p><?php echo htmlentities( $msg ); ?></p></div>
			<?php
		}
		self::$info = array();
	}

	static $errors = array();

	static function addError( $key, $msg ) {
		self::$errors[ $key ] = $msg;
	}

	static function emitErrors() {
		foreach ( self::$errors as $key => $msg ) {
			?>
            <div class="notice notice-error is-dismissible"><p><?php echo htmlentities( $msg ); ?></p></div>
            <script>
                (function ($) {
                    $(document).ready(function () {
                        $('[name="<?php echo $key;?>"]').addClass('error');
                    });
                })(jQuery);
            </script>
			<?php
		}
		self::$errors = array();
	}
}