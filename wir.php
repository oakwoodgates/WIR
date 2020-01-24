<?php
/**
 * Plugin Name: WIR
 * Plugin URI:  https://github.com
 * Description: Awesome sauce
 * Version:     0.2.0
 * Author:      OakwoodGates [WPGuru4u]
 * Author URI:  https://github.com/oakwoodgates
 * License:     GPLv2
 * Text Domain: wir
 * Domain Path: /languages
 *
 * @link    https://github.com
 *
 * @package WIR
 * @version 0.0.1
 *
 * Built using generator-plugin-wp (https://github.com/WebDevStudios/generator-plugin-wp)
 */

/**
 * Copyright (c) 2017 WPGuru4u (email : wpguru4u@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
//Component slug used in url, can be overridden in bp-custom.php
if ( ! defined( 'WIR_SLUG' ) ) {
	define( 'WIR_SLUG', 'locations' );
}

define( 'WIR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Autoloads files with classes when needed.
 *
 * @since  0.0.1
 * @param  string $class_name Name of the class being requested.
 */
function wir_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'WIR_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'WIR_' ) ) ) );

	// Include our file.
	WIR::include_file( 'includes/class-' . $filename );
}
spl_autoload_register( 'wir_autoload_classes' );

/**
 * Main initiation class.
 *
 * @since  0.0.1
 */
final class WIR {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	const VERSION = '0.2.0';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.0.1
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.0.1
	 */
	protected $activation_errors = array();

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    WIR
	 * @since  0.0.1
	 */
	protected static $single_instance = null;

	/**
	 * Instance of WIR_Location
	 *
	 * @since 0.0.1
	 * @var WIR_Location
	 */
	protected $location;

	/**
	 * Instance of WIR_Core
	 *
	 * @since 0.0.1
	 * @var WIR_Core
	 */
	protected $core;

	/**
	 * Instance of WIR_Screens
	 *
	 * @since 0.0.1
	 * @var WIR_Screens
	 */
	protected $screens;

	/**
	 * Instance of WIR_Clone
	 *
	 * @since 0.0.1
	 * @var WIR_Clone
	 */
	protected $clone;

	/**
	 * Instance of WIR_Favorites
	 *
	 * @since 0.0.1
	 * @var WIR_Favorites
	 */
	protected $favorite_lists;

	/**
	 * Instance of WIR_Favorite_Locations
	 *
	 * @since 0.0.1
	 * @var WIR_Favorite_Locations
	 */
	protected $favorite_locations;

	/**
	 * Instance of WIR_Clone_List
	 *
	 * @since 0.0.1
	 * @var WIR_Clone_List
	 */
	protected $clone_list;

	/**
	 * Instance of WIR_Clone_Location
	 *
	 * @since 0.0.1
	 * @var WIR_Clone_Location
	 */
	protected $clone_location;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.0.1
	 * @return  WIR A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.0.1
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.0.1
	 */
	public function plugin_classes() {

		$this->location = new WIR_Location( $this );
		$this->core 	= new WIR_Core( $this );
		$this->screens 	= new WIR_Screens( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.0.1
	 */
	public function hooks() {
		add_action( 'init', 				array( $this, 'init' ), 0 );
		add_action( 'bp_include',			array( $this, 'load_extension' ) );
		add_action( 'bp_init', 				array( $this, 'favorite_groups_initiate') );
		add_action( 'wp_enqueue_scripts', 	array( $this, 'enqueue' ) );
	}

	function favorite_groups_initiate(){
		//	$favorite_groups =  WIR_Favorites::get_instance();
		if ( bp_is_active( 'groups' ) ) {
			$this->favorite_lists 		= new WIR_Favorite_Lists();
			$this->favorite_locations 	= new WIR_Favorite_Locations();
			$this->clone_list 			= new WIR_Clone_List();
			$this->clone_location 		= new WIR_Clone_Location();
		}
	}

	/**
	 * Load required files
	 */
	public function load_extension() {

		$files = array(
			'includes/functions.php',
			'includes/template-tags.php',
			'includes/hooks.php',
			'includes/permissions.php',
			'includes/template.php',
		);

	//	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		//	$files[] =  'admin/admin.php';
	//	}

		foreach ( $files as $file ) {
			require_once $this->path . $file;
		}
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  0.0.1
	 */
	public function _activate() {
		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.0.1
	 */
	public function _deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  0.0.1
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'wir', false, dirname( $this->basename ) . '/languages/' );

		require( 'vendor/cmb2/init.php' );
		require( 'vendor/cpt-core/CPT_Core.php' );

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	static function enqueue( $hook ) {
		wp_enqueue_script( 'wir_js', self::url( 'assets/wir.js' ), array( 'jquery' ), time(), true );
		wp_enqueue_script( 'maps_js', self::url( 'assets/jquery.geocomplete.js' ), array( 'jquery' ), time(), true );
		wp_localize_script( 'wir_js', 'wir_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'	   => wp_create_nonce( 'wir_a' ),
		) );
		wp_enqueue_style( 'wir_plugin_css', self::url( 'assets/wir.css' ) );
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.0.1
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

		// Deactivate our plugin.
		add_action( 'admin_init', array( $this, 'deactivate_me' ) );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.0.1
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.0.1
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.0.1
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		$default_message = sprintf( __( 'WIR is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'wir' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'location':
			case 'core':
			case 'screens':
			case 'clone':
			case 'favorites':
			case 'favorite_locations':
			case 'clone_list':
			case 'clone_location':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.0.1
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}

	public static function load_template( $template ) {
		include( self::dir( 'templates' ) . '/' . $template . '.php' );
	}
}

/**
 * Grab the WIR object and return it.
 * Wrapper for WIR::get_instance().
 *
 * @since  0.0.1
 * @return WIR  Singleton instance of plugin class.
 */
function wir() {
	return WIR::get_instance();
}

// Kick it off.
add_action( 'plugins_loaded', array( wir(), 'hooks' ) );

// Activation and deactivation.
register_activation_hook( __FILE__, array( wir(), '_activate' ) );
register_deactivation_hook( __FILE__, array( wir(), '_deactivate' ) );
