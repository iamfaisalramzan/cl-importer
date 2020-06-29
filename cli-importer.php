<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.opticommerce.co.uk/
 * @since             1.0.0
 * @package           Cli_Importer
 *
 * @wordpress-plugin
 * Plugin Name:       CL Importer
 * Plugin URI:        #
 * Description:       This plugin will get all products data from CSV file and insert accordingly. Also it communicates with the API to get the database record based on matched CLID and create attributes and variations for that specific product.
 * Version:           1.0.0
 * Author:            OptiCommerce
 * Author URI:        https://www.opticommerce.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cli-importer
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
define( 'CLI_IMPORTER_VERSION', '1.0.0' );

/**
 * Currently plugin dir path.
 */
define('CLI_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cli-importer-activator.php
 */
function activate_cli_importer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cli-importer-activator.php';
	Cli_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cli-importer-deactivator.php
 */
function deactivate_cli_importer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cli-importer-deactivator.php';
	Cli_Importer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cli_importer' );
register_deactivation_hook( __FILE__, 'deactivate_cli_importer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cli-importer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cli_importer() {

	$plugin = new Cli_Importer();
	$plugin->run();

}
run_cli_importer();
