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
 * PM_Facebook_Scrape class. 
 *
 * Main Class
 *
 * @package   PM_Facebook_Scrape
 * @author    Pulpmedia <simon@pulpmedia.at>
 */
class PM_Facebook_Scrape {

  /**
   * Plugin version, used for cache-busting of style and script file references.
   *
   * @since   1.0.0
   *
   * @var     string
   */
  const VERSION = '1.0.0';

  /**
   *
   * Unique identifier.
   *
   * The variable name is used as the text domain when internationalizing strings
   * of text. 
   *
   * @since    1.0.0
   *
   * @var      string
   */
  const SLUG = 'facebook-scrape';

  /**
   *
   * Post Meta Key.
   *
   * Used to flag posts that are excluded from scraping. 
   *
   * @since    1.0.0
   *
   * @var      string
   */
  const META_KEY_EXCLUDED = '_pm_fb_scrape_excluded';
  
  /**
   *
   * Post Meta Key.
   *
   * Used to save the date of the latest scrape in mysql format, gmt. 
   *
   * @since    1.0.0
   *
   * @var      string
   */
  const META_KEY_LAST_SCRAPED = '_pm_fb_scrape_last';

  /**
   * Instance of this class.
   *
   * @since    1.0.0
   *
   * @var      object
   */
  protected static $instance = null;

  /**
   * Initialize the plugin
   *
   * @since     1.0.0
   */
  private function __construct() {

    // Load plugin text domain
    add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

    // hook into the publish post action, sends a http request to fb
    $post_types = PM_Facebook_Scrape_Admin_Options::get_post_types();
    if( ! empty( $post_types )) {
      foreach ( $post_types as $post_type ) {
        add_action ( 'publish_' . $post_type,  array( $this, 'action_do_scrape' ), 10, 2);    
      }
    }
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
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain() {

    $domain = self::SLUG;
    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

    load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
    load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
  }

  /**
   * Action Hook
   *
   * Fires when a post is set to published or when a published post is updated
   *
   * @since    1.0.0
   */
  public function action_do_scrape( $ID, $post ) {
    
    if( $this->is_post_included( $ID ))
      $this->do_scrape( $ID );
  }

  /**
   * Checks if a post should be scraped
   * 
   * @since    1.0.0
   * @access private
   *
   * @return    bool
   */
  private function is_post_included( $post_ID ) {
    
    if( ! absint( $post_ID ))
      return false;  
    
    // check if a form is submitted ( edit post screen )
    if ( isset( $_REQUEST[PM_Facebook_Scrape_Admin_Metabox::NONCE_NAME] )) {
    
      // exclude when checkbox is checked       
      if ( $_REQUEST && isset( $_REQUEST[self::META_KEY_EXCLUDED] ))
        return false;               
    } else {

      // exclude when an exclude meta field is saved for this post  
      if( get_post_meta( $post_ID, self::META_KEY_EXCLUDED, true ))
        return false;   
    }
    
    return true;
  }
  
  /**
   * Sends an HTTP Request to Facbook Graph Api and tries to scrape the post
   * 
   * @since    1.0.0
   * @access private
   *
   * @return    bool
   */
  private function do_scrape( $post_ID  ) {
    
    $post_permalink = get_permalink( $post_ID );  
    $base_url = 'https://graph.facebook.com/';

    $args = array(
      'timeout'     => 20,
      'redirection' => 5,
      'httpversion' => '1.0',
      'user-agent'  => 'WordPress/; ' . get_bloginfo( 'url' ),
      'blocking'    => true,
      'headers'     => array(),
      'cookies'     => array(),
      'compress'    => false,
      'decompress'  => true,
      'sslverify'   => true,
      'stream'      => false,
      'filename'    => null,
      'body'        => array(
        'id'  =>  $post_permalink ,
        'scrape' => 'true'
      )
    );  
            
    $response = wp_remote_post( $base_url, $args ); 
    
    // TODO better check successful scrape
    // use headers
    echo '<pre>'; print_r($response); echo '</pre>';
    if( wp_remote_retrieve_response_code( $response ) != 200) {
      wp_die('sdf');
      return false;
    } 
    
    update_post_meta( $post_ID, self::META_KEY_LAST_SCRAPED, current_time( 'mysql', true ));  
          
    return true; 
  }
}