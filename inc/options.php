<?php
/**
 * Plugin options.
 *
 * @package   TB Social Share
 * @version   1.0.0
 * @author    ThemesBros
 * @copyright Copyright (c) 2011 - 2017, ThemesBros
 */

/* If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Plugin admin class.
 *
 * @since 1.0.0
 */
class TB_Social_Share_Admin {

	/**
	 * Sets up needed actions for the admin to initialize.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * List of supported share sites.
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array
	 */
	public function get_site_list() {
		return array(
			'facebook'  => __( 'Facebook', 'tb-social-share' ),
			'twitter'   => __( 'Twitter', 'tb-social-share' ),
			'gplus'     => __( 'Google Plus', 'tb-social-share' ),
			'pinterest' => __( 'Pinterest', 'tb-social-share' ),
			'linkedin'  => __( 'Linkedin', 'tb-social-share' ),
			'reddit'    => __( 'Reddit', 'tb-social-share' ),
			'tumblr'    => __( 'Tumblr', 'tb-social-share' ),
			'vk'        => __( 'Vk', 'tb-social-share' ),
			'email'     => __( 'Email', 'tb-social-share' ),
		);
	}

	/**
	 * Adds link to admin menu under "Settings".
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'options-general.php',
			esc_html__( 'TB Social Share', 'tb-social-share' ),
			esc_html__( 'TB Social Share', 'tb-social-share' ),
			'manage_options',
			'tb_social_share',
			array( $this, 'display_options' )
		);
	}

	/**
	 * Initialize settings API to create plugin options.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function settings_init() {

		register_setting( 'tbss_options', 'tbss_settings', array( $this, 'sanitize_data' ) );

		add_settings_section(
			'tbss_section',
			esc_html__( 'TB Social Share Options', 'tb-social-share' ),
			array( $this, 'settings_section_display' ),
			'tbss_options'
		);

		add_settings_field(
			'status',
			esc_html__( 'Status', 'tb-social-share' ),
			array( $this, 'display_status' ),
			'tbss_options',
			'tbss_section'
		);

		add_settings_field(
			'sites',
			esc_html__( 'Sites', 'tb-social-share' ),
			array( $this, 'display_sites' ),
			'tbss_options',
			'tbss_section'
		);

		add_settings_field(
			'position',
			esc_html__( 'Position', 'tb-social-share' ),
			array( $this, 'display_position' ),
			'tbss_options',
			'tbss_section'
		);

	}

	/**
	 * Adds required scripts for plugin functionality (jQuery, jQuery-ui-sortable).
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue() {

		wp_enqueue_script( 'jquery-ui-sortable' );

		wp_enqueue_script(
			'tbss-admin',
			trailingslashit( plugins_url( '../assets/js/', __FILE__ ) ) . 'admin.js',
			array( 'jquery' ),
			null,
			true
		);

	}

	/**
	 * Callback: displays section info.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function settings_section_display() {
		printf( '<p>%s</p>', esc_html__( 'Customize plugin behaviour.', 'tb-social-share' ) );
	}

	/**
	 * Callback: displays checkbox.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function display_status() {
		$options = get_option( 'tbss_settings' );
		?>
		<label for="status"><input type="checkbox" id="status" name="tbss_settings[status]" <?php isset( $options['status'] ) ? checked( $options['status'], 1 ) : ''; ?>> <?php esc_html_e( 'Check to enable', 'tb-social-share' ); ?></label>
		<?php
	}


	/**
	 * Callback: displays checkboxes with sites.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function display_sites() {

		$options = get_option( 'tbss_settings' );

		$sites = isset( $options['site'] ) ? $this->get_sorted_sites( $options['site'] ) : $this->get_site_list();

		echo '<ul class="sortable">';

		foreach( $sites as $id => $name ) {
			printf(
				'<li><span class="dashicons dashicons-move"></span> <label for="site-%1$s"><input id="site-%1$s" type="checkbox" name="tbss_settings[site][%1$s]"%2$s>%3$s</label></li>',
				esc_attr( $id ),
				! empty( $options['site'][$id] ) ? sprintf( ' %s', checked( 1, 1, false ) ) : '',
				esc_html( $name )
			);
		}

		echo '</ul>';

	}

	/**
	 * Callback: displays select.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function display_position() {
		$options = get_option( 'tbss_settings' );
		?>
		<select name="tbss_settings[position]">
			<option value="custom" <?php selected( $options["position"], 'custom' ); ?>><?php esc_html_e( 'Custom position', 'tb-social-share' ); ?></option>
			<option value="after" <?php selected(  $options["position"],  'after' ); ?>><?php esc_html_e( 'After post',  'tb-social-share' ); ?></option>
			<option value="before" <?php selected( $options["position"], 'before' ); ?>><?php esc_html_e( 'Before post', 'tb-social-share' ); ?></option>
		</select>
		<p><?php esc_html_e( 'If custom position is chosen, you can place function in your theme like this:', 'tb-social-share' ); ?>
		<br>
		<code>
			&lt;?php if ( function_exists( 'tb_social_share_display' ) ) { echo tb_social_share_display(); } ?&gt;
		</code>
		</p>
	<?php
	}

	/**
	 * Callback: displays options page.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function display_options() {
		?>
		<div class="wrap">
			<form action="options.php" method="POST">
				<?php
				settings_fields( 'tbss_options' );
				do_settings_sections( 'tbss_options' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Callback: checks and validates user submitted data.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function sanitize_data( $input ) {
		$output['status']   = isset( $input['status'] ) ? 1 : '';
		$output['position'] = sanitize_text_field( $input['position'] );

		$sites = $this->get_sorted_sites( $input['site'] );

		foreach( $sites as $id => $name ) {
			$output['site'][ sanitize_text_field( $id ) ] = isset( $input['site'][$id] ) ? 1 : '';
		}

		return $output;
	}

	/**
	 * Set's new order of social share sites.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $sites
	 * @return array
	 */
	public function get_sorted_sites( $order ) {
		$site_list = $this->get_site_list();
		$new_order = array();
		foreach( array_keys( $order ) as $key ) {
			$new_order[$key] = $site_list[$key];
		}
		return array_merge( $new_order, $site_list ) ;
}

}

new TB_Social_Share_Admin;