<?php
/**
 * Plugin Name: Storefront Parallax Hero Polylang Integration
 * Plugin URI: https://github.com/decarvalhoaa/storefront-parallax-hero-polylang-integration/
 * Description: Plugin for adding Polylang support to the Storefront Parallax Hero plugin
 * Author: Antonio de Carvalho
 * Author URI: http://https://github.com/decarvalhoaa/
 * Version: 1.0.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the main instance of SPH_Poly_Integration to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object SPH_Poly_Integration
 */
function SPH_Poly_Integration() {
	return SPH_Poly_Integration::instance();
} // End Storefront_Parallax_Hero_Polylang_Integration()

SPH_Poly_Integration();

/**
 * Main SPH_Poly_Integration Class
 *
 * @class SPH_Poly_Integration
 * @version 1.0.0
 * @since 1.0.0
 * @package SPH_Poly_Integration
 * @author Antonio de Carvalho
 */
final class SPH_Poly_Integration {
	/**
	 * SPH_Poly_Integration The single instance of SPH_Poly_Integration.
	 * @var	   object
	 * @access private
	 * @since  1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * The Storefront Parallax Hero strings settings.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $sph_hero_strings;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->token 			= 'sph-poly-integration';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.0';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'sph_poly_setup' ) );
	} // End __construct()

	/**
	 * Main SPH_Poly_Integration Instance
	 *
	 * Ensures only one instance of SPH_Poly_Integration is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see SPH_Poly_Integration()
	 * @return Main SPH_Poly_Integration instance
	 */
	public static function instance () {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	} // End __wakeup()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install()

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	} // End _log_version_number()

	/**
	 * Setup all the things, if Storefront, or a child theme using Storefront that has not disabled the Customizer settings, and Polylang is active
	 * @return void
	 */
	public function sph_poly_setup() {
		$theme = wp_get_theme();

		if ( 'Storefront' == $theme->name || 'storefront' == $theme->template && apply_filters( 'storefront_parallax_hero_enabled', true ) && function_exists( 'pll_the_languages' ) ) {
			$this->sph_hero_strings = array(
				'Heading text' => sanitize_text_field( get_theme_mod( 'sph_hero_heading_text', __( 'Heading Text', 'storefront-parallax-hero' ) ) ),
				'Description text' => wp_kses_post( get_theme_mod( 'sph_hero_text', __( 'Description Text', 'storefront-parallax-hero' ) ) ),
				'Button text' => sanitize_text_field( get_theme_mod( 'sph_hero_button_text', __( 'Go shopping', 'storefront-parallax-hero' ) ) ),
				'Button url' => sanitize_text_field( get_theme_mod( 'sph_hero_button_url', home_url() ) )
			);

			foreach ( $this->sph_hero_strings as $name => $string ) {
				// Register Storefront Parallax Hero strings for translation.
				pll_register_string( $name, $string, 'Storefront Parallax Hero', $name == 'Description text' ? true : false );
			}
		}

		add_filter( 'theme_mod_sph_hero_heading_text', array( $this, 'sph_poly_translate_string' ) );
		add_filter( 'theme_mod_sph_hero_text', array( $this, 'sph_poly_translate_string' ) );
		add_filter( 'theme_mod_sph_hero_button_text', array( $this, 'sph_poly_translate_string' ) );
		add_filter( 'theme_mod_sph_hero_button_url', array( $this, 'sph_poly_translate_string' ) );
	} // End sph_poly_setup()

	/**
	 * Translate the Storefront Parallax Hero string.
	 * @access  public
	 * @since   1.0.0
	 * @return  string The translated string.
	 */
	public function sph_poly_translate_string( $string ) {
		// Only attempt to translate the strings if Polylang is active
		if ( function_exists( 'pll__' ) ) {
			switch ( current_filter() ) {
				case 'theme_mod_sph_hero_heading_text':
				    return pll__( $this->sph_hero_strings['Heading text'] );

				case 'theme_mod_sph_hero_text':
				    return pll__( $this->sph_hero_strings['Description text'] );

				case 'theme_mod_sph_hero_button_text':
				    return pll__( $this->sph_hero_strings['Button text'] );

				case 'theme_mod_sph_hero_button_url':
				    return pll__( $this->sph_hero_strings['Button url'] );

				default:
				    return $string;
			}
		}

		return $string;
	} // End sph_poly_translate_string()
}
