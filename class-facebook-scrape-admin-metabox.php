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
 * PM_Facebook_Scrape_Admin_Metabox class. 
 *
 * Metabox Class, adds a metabox to the post edit screen
 * provides an option to exclude a single post from scraping
 * displays the time of the latest scrape
 *
 * @package PM_Facebook_Scrape_Admin_Metabox
 * @author  Pulpmedia <simon@pulpmedia.at>
 */
class PM_Facebook_Scrape_Admin_Metabox {

  /**
   * Unique action name for the nonce
   *
   * @since    1.0.0
   *
   * @var      string
   */
  const NONCE_ACTION = 'pm_fb_scrape_metabox_save';

  /**
   * Unique name for the nonce
   *
   * @since    1.0.0
   *
   * @var      string
   */
  const NONCE_NAME = 'pm_fb_scrape_metabox_nonce';

  /**
   * Holds an array with all included post types
   *
   * @since    1.0.0
   *
   * @var      array
   */
  private $post_types = array(); 

  /**
   * Hook into actions.
   */
  public function __construct() {
    $this->post_types = PM_Facebook_Scrape_Admin_Options::get_post_types();
    add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ));
    add_action( 'save_post', array( $this, 'save' ));
  }

  /**
   * Adds the metabox container.
   *
   * @since    1.0.0
   */
  public function add_meta_box( $post_type ) {
    
    $post_type_object = get_post_type_object( $post_type ); 
    $capability = $post_type_object->cap->edit_posts;
    if ( in_array( $post_type, $this->post_types ) && current_user_can( $capability )) {
      add_meta_box(
        'pm_facebook_scrape_metabox'
        ,__( 'Facebook Scrape', PM_Facebook_Scrape::SLUG )
        ,array( $this, 'render_meta_box_content' )
        ,$post_type
        ,'side'
        ,'low'
      );
    }
  }

  /**
   * Save the meta when the post is saved.
   *
   * @since    1.0.0
   */
  public function save( $post_ID ) {
  
    // Check if our nonce is set.
    if ( ! isset( $_REQUEST[self::NONCE_NAME] ))
      return $post_ID;

    $nonce = $_REQUEST[self::NONCE_NAME];

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ))
      return $post_ID;

    // If this is an autosave, our form has not been submitted,
    // so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_ID;

    // Check the user's permissions.
    if ( ! current_user_can( 'edit_post', $post_ID ) )
      return $post_ID;
    
    // Update the post meta
    // set a flag if this post should be excluded
    if ( isset( $_REQUEST[PM_Facebook_Scrape::META_KEY_EXCLUDED] )) {  
      update_post_meta( $post_ID, PM_Facebook_Scrape::META_KEY_EXCLUDED, '1' );
    } else {
      delete_post_meta( $post_ID, PM_Facebook_Scrape::META_KEY_EXCLUDED );
    }
  }

  /**
   * Render the metabox content
   *
   * @since    1.0.0
   */
  public function render_meta_box_content( $post ) {
              
    $is_excluded = get_post_meta( $post->ID, PM_Facebook_Scrape::META_KEY_EXCLUDED, true );
    $date = get_post_meta( $post->ID, PM_Facebook_Scrape::META_KEY_LAST_SCRAPED, true );
    $share_url = add_query_arg( 'u', urlencode( get_permalink( $post->ID )), 'https://www.facebook.com/sharer.php' );
    
    // Add a nonce field 
    wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
    ?>
    <p>
      <label for="fb-scrape-exclude-checkbox">
        <input type="checkbox" name="<?php echo esc_attr( PM_Facebook_Scrape::META_KEY_EXCLUDED );?>" id="fb-scrape-exclude-checkbox" value="1" <?php checked( $is_excluded, true ); ?> />
        <?php _e( 'Do not scrape this post', PM_Facebook_Scrape::SLUG );?>
      </label>
    </p>

    <?php if( $date ) : ?> 
    <p>
      <strong><?php _e( 'last scraped:', PM_Facebook_Scrape::SLUG );?></strong>
      <?php echo get_date_from_gmt( $date, get_option( 'date_format' )); ?>
      <?php echo get_date_from_gmt( $date, get_option( 'time_format' )); ?>
    </p>   
    <p>
    <a href="<?php echo esc_url( $share_url );?>" target="_blank"><?php _e( 'See the share dialog', PM_Facebook_Scrape::SLUG );?></a>
    </p> 
    <?php endif;
  }
}