<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.saadamin.com
 * @since             1.0.0
 * @package           Smd
 *
 * @wordpress-plugin
 * Plugin Name:       Safe Media Delete
 * Plugin URI:        https://www.saadamin.com
 * Description:       Develop a WordPress plugin which adds the some features in WP Admin
 * Version:           1.0.0
 * Author:            Saad Amin
 * Author URI:        https://www.saadamin.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smd
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SMD_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-smd-activator.php
 */
function activate_smd() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smd-activator.php';
	Smd_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-smd-deactivator.php
 */
function deactivate_smd() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smd-deactivator.php';
	Smd_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smd' );
register_deactivation_hook( __FILE__, 'deactivate_smd' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-smd.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_smd() {

	$plugin = new Smd();
	$plugin->run();

}
run_smd();
