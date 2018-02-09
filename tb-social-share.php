<?php
/**
 * Plugin Name: TB Social Share
 * Plugin URI: http://www.themesbros.com/
 * Description: Social sharers for articles.
 * Author: ThemesBros
 * AuthorURI: http://www.themesbros.com/
 * Version: 1.0.0
 * Domain Path: /languages
 * Text Domain: tb-social-share
 *
 * @package    	TB Social Share
 * @author 		ThemesBros.com
 * @copyright   Copyright (c) 2011-2017, ThemesBros
*/

/* If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Plugin loader class.
 *
 * @since 1.0.0
 */
class TB_Social_Share {

	/**
	 * Plugin status (enabled or disabled).
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $status = false;

	/**
	 * Plugin directory.
	 *
	 * @since  	1.0.0
	 * @access  protected
	 * @var 	string
	 */
	protected $plugin_dir;

	/**
	 * Plugin URL.
	 *
	 * @since  	1.0.0
	 * @access  protected
	 * @var 	string
	 */
	protected $plugin_url;

	/**
	 * Sets up needed actions/filters for the plugin to initialize.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->plugin_url = plugin_dir_url( __FILE__ );

		$options      = get_option( 'tbss_settings' );
		$this->status = isset( $options['status'] ) ? $options['status'] : '';

		add_action( 'plugins_loaded', array( $this, 'i18n'     ), 3 );
		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );

		if ( $this->status ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue'    ) 		  );
		}

	}

	/**
	 * Loads the translation files.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function i18n() {
		load_plugin_textdomain( 'tb-social-share', false, $this->plugin_dir . 'languages' );
	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function includes() {
		require_once plugin_dir_path( __FILE__ ) . '/inc/options.php';
		require_once plugin_dir_path( __FILE__ ) . '/inc/functions.php';
	}

	/**
	 * Load styles and scripts needed by the plugin.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function enqueue() {

		# Load scripts only on singular pages.
		if ( ! is_single() ) {
			return;
		}

		# Use minified files if SCRIPT_DEBUG is off.
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		# Register & load Font Awesome.
		wp_register_style( 'font-awesome', $this->plugin_url . "assets/css/font-awesome{$suffix}.css" );
		wp_enqueue_style( 'font-awesome' );

		# Register & load plugin style.
		wp_register_style( 'tb-social-share', $this->plugin_url . "assets/css/style{$suffix}.css" );
		wp_enqueue_style( 'tb-social-share' );

	}

}

new TB_Social_Share;