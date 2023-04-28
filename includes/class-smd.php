<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.saadamin.com
 * @since      1.0.0
 *
 * @package    Smd
 * @subpackage Smd/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Smd
 * @subpackage Smd/includes
 * @author     Saad Amin <saadvi@gmail.com>
 */
class Smd {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Smd_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SMD_VERSION' ) ) {
			$this->version = SMD_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'smd';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Smd_Loader. Orchestrates the hooks of the plugin.
	 * - Smd_i18n. Defines internationalization functionality.
	 * - Smd_Admin. Defines all hooks for the admin area.
	 * - Smd_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smd-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-smd-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-smd-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-smd-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api.php';



		$this->loader = new Smd_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smd_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smd_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		//defined('DOING_PHPUNIT')" has been added for phpunit test
// $b=$_SERVER;
// $g=$_REQUEST;
	$categoryPageParameters = $_REQUEST['taxonomy'] =="category" || isset( $_GET['taxonomy'] )  || $_REQUEST['screen'] =="edit-category" || $_REQUEST['action'] =="query-attachments";
	$otherPagesParameters=basename($_SERVER['PHP_SELF']) === 'post.php' || basename($_SERVER['PHP_SELF']) === 'upload.php' || strpos($_SERVER['HTTP_REFERER'], 'upload.php') !== false || strpos($_SERVER['SCRIPT_NAME'], 'upload.php') !== false || $_REQUEST['action'] =="query-attachments";
		if (defined('DOING_PHPUNIT') || $categoryPageParameters || $otherPagesParameters) {

			$plugin_admin = new Smd_Admin( $this->get_plugin_name(), $this->get_version() );
			// Load the scripts and styles.
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

			if ($categoryPageParameters && !$otherPagesParameters)  {
				//Only load cmb2 for taxonomy pages
				require_once  __DIR__. '/cmb2/init.php';
				add_action( 'cmb2_admin_init', array($plugin_admin,'cmb2_add_metabox') );
			}else{
				// The user is currently on the media library page or editing a post or page or attachment.
				add_filter('attachment_fields_to_edit', array($plugin_admin,'add_image_details_link'), 10, 2);
				add_filter('manage_media_columns', array($plugin_admin,'add_image_linked_object_column'));
				add_action('manage_media_custom_column', array($plugin_admin,'image_linked_object_column_content'), 10, 2);

				//Prevent delete used images from backend only.
				add_action('pre_delete_attachment', array($plugin_admin,'check_image_before_deletion'), 10, 2);
			}
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Smd_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Smd_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
