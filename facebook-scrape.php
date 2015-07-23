<?php
/**
 * Facebook Scrape - A WordPress Plugin.
 *
 * Automatically update the Facebook graph information when a post, page or a custom post type is updated. 
 *
 * @package   Facebook_Scrape
 * @author    Pulpmedia <simon@pulpmedia.at>
 * @license   GPL-2.0+
 * @link      http://pulpmedia.at
 * @copyright 2015 Pulpmedia
 *
 * @wordpress-plugin
 * Plugin Name:       Facebook Scrape
 * Plugin URI:        http://pulpmedia.at
 * Description:       Automatically update the Facebook graph information when a post, page or a custom post type is updated.
 * Version:           1.0.0
 * Author:            Pulpmedia
 * Author URI:        http://www.pulpmedia.at/leistungen/webdevelopment/
 * Text Domain:       facebook-scrape
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>   TODO
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-facebook-scrape.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-facebook-scrape-admin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-facebook-scrape-admin-options.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-facebook-scrape-admin-metabox.php' );

// init
add_action( 'plugins_loaded', array( 'PM_Facebook_Scrape', 'get_instance' ));
add_action( 'plugins_loaded', array( 'PM_Facebook_Scrape_Admin', 'get_instance' ));
add_action( 'plugins_loaded', array( 'PM_Facebook_Scrape_Admin_Options', 'get_instance' ));
new PM_Facebook_Scrape_Admin_Metabox();

// activation hook
register_activation_hook( __FILE__, array( 'PM_Facebook_Scrape_Admin_Options', 'install' ) );