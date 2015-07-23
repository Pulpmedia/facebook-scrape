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
 * PM_Facebook_Scrape_Admin_Options class. 
 *
 * Admin Options Class
 *
 * @package PM_Facebook_Scrape_Admin_Options
 * @author  Pulpmedia <simon@pulpmedia.at>
 */
class PM_Facebook_Scrape_Admin_Options {

	/**
	 * The name of the option field for this plugin.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	const OPTION_NAME = 'pm_fb_scrape';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the options
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
    
    // init options
    add_action( 'admin_init', array( $this, 'action_init_settings' ));
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
	 * Returns all posttypes saved in the options.
	 *
	 * @since     1.0.0 
	 * @access public
	 * @static
	 *
	 * @return    array
	 */
	public static function get_post_types() {
  	
    $options = get_option( self::OPTION_NAME );
    return isset( $options['included_post_types']) ? $options['included_post_types'] : array(); 	
	}
	
	/**
	 * Registers all Settings, Setting Section, and Setting Fields
	 * 
	 * @since     1.0.0
	 *
	 */
	public function action_init_settings() {
  	
    register_setting( self::OPTION_NAME, self::OPTION_NAME, array( $this, 'options_validate')); 
    add_settings_section( PM_Facebook_Scrape::SLUG . '_included_posts', __( ' Activate scraping for post types', PM_Facebook_Scrape::SLUG ) , array( $this, 'section_included_posts_output' ), PM_Facebook_Scrape::SLUG );
    
    // find all public post types
    $post_types = get_post_types( array( 'public' => true ) , 'object' ); 
    unset( $post_types['attachment'] );  
    
    // add a setting field for each post type
    foreach ($post_types as $post_type) {      
      add_settings_field( PM_Facebook_Scrape::SLUG . '_included_post_' . $post_type->name, $post_type->label, array( $this, 'select_post_type_input_tag' ), PM_Facebook_Scrape::SLUG, PM_Facebook_Scrape::SLUG . '_included_posts', array( 'post_type' => $post_type ));  	
    }   
	}
  
	/**
	 * Outputs description-text for Include Posts Section
	 * 
	 * @since     1.0.0
	 *
	 */
  public function section_included_posts_output() {
    
    echo '<p>' . __( 'Enable scarping Facebook Graph information for certain post types. Posts of the selected post types are re-scraped by facebook each time the post is updated. Specific posts can be excluded from being scraped within the Edit Post Screen.', PM_Facebook_Scrape::SLUG ) . '</p>';
    $options = get_option( self::OPTION_NAME );
  }
	/**
	 * Outputs description-text for Include Posts Section
	 * 
	 * @since     1.0.0
	 *
	 */
  public function select_post_type_input_tag( $args ) {
  
    $post_type = $args['post_type'];
    $options = get_option( self::OPTION_NAME );
    $option_name = self::OPTION_NAME;
    $post_type_name = $post_type->name;
    $value = ( isset( $options['included_post_types'] ) && FALSE !== array_search( $post_type_name, $options['included_post_types'] ) ) ? 1 : 0; 
    $field_name = $option_name . '[included_post_types][]';
    
    echo "<input id='plugin_text_string' name='{$field_name}' " . checked( $value, true, false  ) . " size='40' type='checkbox' value='{$post_type_name}' />";
  } 
  
	/**
	 * Validates the option values before saving them
	 * 
	 * @since     1.0.0
	 *
	 */
  public function options_validate( $input ) {
    
    // init a whitelist
    $validate_input = array();           
    $post_types =  isset( $input['included_post_types']) ? $input['included_post_types'] : array();
    foreach( $post_types as $post_type ) {
      $validate_input['included_post_types'][] = $post_type;
    }
    return $validate_input;
  }
	/**
	 * Activation Hook
	 * 
	 * adds the post_types post and page to the included post_types
	 * 
	 * @since     1.0.0
	 *
	 */  
  public static function install() {
    
    // permission check
  	if ( ! current_user_can( 'activate_plugins' ) )
  		return;
  		
  	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
  	check_admin_referer( "activate-plugin_{$plugin}" );

    $options = get_option( self::OPTION_NAME );
    if( ! isset( $options['included_post_types'] )) {
      $options['included_post_types'] = array(
        'post',
        'page'
      );
      update_option( self::OPTION_NAME, $options );
    }
  }
}