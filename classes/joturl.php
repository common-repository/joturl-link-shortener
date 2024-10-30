<?php

include_once __DIR__ . '/JotUrlSDK.php';
include_once __DIR__ . '/joturl-ajax.php';

class JotUrl extends JotUrlAjax {

	static function enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		wp_enqueue_style( $handle, JOTURL_PLUGIN_URL . $src, $deps, $ver ?: (string) filemtime( JOTURL_PLUGIN_FOLDER . $src ), $media );
	}

	static function enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		wp_enqueue_script( $handle, JOTURL_PLUGIN_URL . $src, $deps, $ver ?: (string) filemtime( JOTURL_PLUGIN_FOLDER . $src ), $in_footer );
	}

	static function admin_add_scripts_and_styles() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

		self::enqueue_style( 'joturl-admin-styles', '/css/admin.css' );
		self::enqueue_style( 'joturl-admin-overlays', '/css/overlays.css' );
		self::enqueue_style( 'joturl-admin-icons', '/css/style.css' );

		self::enqueue_script( 'joturl-admin-javascript', '/js/admin.js', array( 'jquery', 'wp-i18n' ), false, true );
		self::enqueue_script( 'jquery-cookie', '/addons/jquery.cookie/jquery.cookie.js', array( 'jquery' ) );

		self::enqueue_style( 'joturl-admin-tagit-styles', '/addons/tagit/css/jquery.tagit.css' );
		self::enqueue_style( 'joturl-admin-tagit-styles-ui', '/addons/tagit/css/tagit.ui-joturl.css' );
		self::enqueue_script( 'joturl-admin-tagit', '/addons/tagit/js/tag-it.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' ) );

		self::enqueue_style( 'joturl-admin-select2-style', '/addons/select2/css/select2.min.css' );
		self::enqueue_style( 'joturl-admin-select2-theme', '/addons/select2/css/select2-joturl.min.css' );

		self::enqueue_script( 'joturl-admin-select2', '/addons/select2/js/select2.full.custom.js', array( 'jquery' ), false, true );
		wp_add_inline_script( 'joturl-admin-select2', 'jQuery.fn.select2.defaults.set("theme","joturl");', 'after' );
		$loc = strtolower( self::getParam( explode( '_', get_locale() ), 0, 'en' ) );
		if ( ! file_exists( JOTURL_PLUGIN_FOLDER . '/addons/select2/js/i18n/' . $loc . '.js' ) ) {
			$loc = 'en';
		}
		self::enqueue_script( 'joturl-admin-select2', '/addons/select2/js/i18n/' . $loc . '.js', array( 'joturl-admin-select2' ), array(), true );

		$templates = array(
			'overlay' => file_get_contents( JOTURL_PLUGIN_FOLDER . '/templates/overlay.html' ),
		);

		foreach ( $templates as $k => $t ) {
			$t = str_replace( array(
				"\r",
				"\n",
			), '', $t );
			$t = preg_replace( '/>\s+/ui', '>', $t );
			$t = preg_replace( '/\s+</ui', '<', $t );

			$templates[ $k ] = $t;
		}

		wp_add_inline_script( 'joturl-admin-javascript', 'window.JotUrlTemplates=' . json_encode( $templates ) . ';', 'before' );
	}

	static function addCustomMenu() {
		add_menu_page(
			__( 'JotUrl - Settings', 'joturl-link-shortener' ),
			'JotUrl',
			'administrator',
			'joturl-options',
			array(
				__CLASS__,
				'showPage',
			),
			'dashicons-joturl'
		);
	}

	static function load_textdomain() {
		load_plugin_textdomain( 'joturl-link-shortener', false, JOTURL_PLUGIN_FOLDER . '/languages' );
	}

	static function init() {
		parent::init();

		add_action( 'admin_menu', array( __CLASS__, 'addCustomMenu' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_add_scripts_and_styles' ) );

		if ( self::isConfigured() ) {
			// for posts
			add_filter( 'manage_posts_columns', array( 'JotUrl', 'addColumns' ) );
			add_action( 'manage_posts_custom_column', array( 'JotUrl', 'setColumnValues' ), 10, 2 );

			// for pages
			add_filter( 'manage_pages_columns', array( 'JotUrl', 'addColumns' ) );
			add_action( 'manage_pages_custom_column', array( 'JotUrl', 'setColumnValues' ), 10, 2 );
		}

		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
	}
}