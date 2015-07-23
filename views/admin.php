<?php
/**
 * The Admin User Interface
 *
 * @package   Facebook_Scrape
 * @author    Pulpmedia <simon@pulpmedia.at>
 * @license   GPL-2.0+
 * @link      http://pulpmedia.at
 * @copyright 2015 Pulpmedia
 */
 
?>

<div class="wrap">
  <?php screen_icon(); ?>
  <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
  <p><?php _e( 'The option page for Facebook Scrape plugin.', PM_Facebook_Scrape::SLUG );?></p>
  <form action="options.php" method="post">
  <?php settings_fields( PM_Facebook_Scrape_Admin_Options::OPTION_NAME ); ?>
  <?php do_settings_sections( PM_Facebook_Scrape::SLUG ); ?>
  <?php submit_button(
    __( 'Save Changes', PM_Facebook_Scrape::SLUG ),
    'primary',
    'submit'
  );?>
  </form>
</div>