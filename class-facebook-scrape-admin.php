<?php
/**
 * Facebook Scrape.
 *
 * @package   Facebook_Scrape
 * @author    Pulpmedia <simon@pulpmedia.at>
 * @license   GPL-2.0+
 * @link      http://pulpmedia.at
 * @copyright 2015 Pulpmedia
 */

/**
 * PM_Facebook_Scrape_Admin class. 
 *
 * Main Admin Class
 *
 * @package PM_Facebook_Scrape_Admin
 * @author  Pulpmedia <simon@pulpmedia.at>
 */
class PM_Facebook_Scrape_Admin {

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;

  /**
   * Slug of the plugin screen.
   *
   * @since    1.0.0
   *
   * @var      string
   */
  protected $plugin_screen_hook_suffix = null;

  /**
   * Initialize the plugin by loading admin scripts & styles and adding a
   * settings page and menu.
   *
   * @since     1.0.0
   */
  private function __construct() {

    // Add the options page and menu item.
    add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

    // Add an action link pointing to the options page.
    $plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . PM_Facebook_Scrape::SLUG . '.php' );
    add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
  }

  /**
   * Return an instance of this class.
   *
   * @since     1.0.0
   *
   * @return    object    A single instance of this class.
   */
  public static function get_instance() {

    // If the single instance hasn't been set, set it now.
    if ( null == self::$instance ) {
      self::$instance = new self;
    }

    return self::$instance;
  }

  /**
   * Register the administration menu for this plugin into the WordPress Dashboard menu.
   *
   * @since    1.0.0
   */
  public function add_plugin_admin_menu() {

    /*
     * Add a settings page for this plugin to the Settings menu.
     *
     */
    $this->plugin_screen_hook_suffix = add_options_page(
      __( 'Facebook Scrape', PM_Facebook_Scrape::SLUG ),
      __( 'Facebook Scrape', PM_Facebook_Scrape::SLUG ),
      'manage_options',
      PM_Facebook_Scrape::SLUG,
      array( $this, 'display_plugin_admin_page' )
    );
  }

  /**
   * Render the settings page.
   *
   * @since    1.0.0
   */
  public function display_plugin_admin_page() {
    include_once( 'views/admin.php' );
  }

  /**
   * Add settings action link to the plugins page.
   *
   * @since    1.0.0
   */
  public function add_action_links( $links ) {

    return array_merge(
      array(
        'settings' => '<a href="' . admin_url( 'options-general.php?page=' . PM_Facebook_Scrape::SLUG ) . '">' . __( 'Settings', PM_Facebook_Scrape::SLUG ) . '</a>'
      ),
      $links
    );
  }
}