<?php
/**
 * Provide a admin area view for the lazy load settings
 *
 * This file is used to markup the admin-facing aspects of the plugin settings page.
 *
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin/partials
 */

global $wp_version;
?>
<div class="wrap">
<div class="branding clearfix">
 <div class="logo">
  <img class="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wpopti-small-logo.png';?>">
  <span class="help-link"><a href="https://cybertactics.net/products" target="_blanl">Other Products</a> | <a href="https://cybertactics.net" target="_blanl">Blog</a></span>
 </div>
 <h1 class="page-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>
<?php if ( version_compare( $wp_version, '4.5', '<' ) ) {
  echo '<div class="notice notice-error">This Plugin requires at least version 4.5 of WordPress. Please update your WordPress to the latest version in order to avoid any compatibility issues.</div>';
}
?>
<?php if(!empty($errors)) { ?>
<div class="notice notice-error"><?php echo $errors;?></div>
<?php } ?>
<form method="post" action="">
<input type="hidden" name="action" value="wpoptimiser_save_settings">
<?php if($this->licensing->is_licensed()) { ?>
<div>
<?php } else { ?>
<div class="dgit_error">
<p>First you need to activate WP Optimiser by entering your JVZoo transaction ID in the license key box below. This transaction ID was emailed to you and you can also find it in your <a href="https://customer.jvzoo.com/portal/index" target="_blank">JVZoo customer portal</a> under your WP Optimiser download details. Your JVZoo transaction ID will look like this: AP-12345678912345678 When you have entered your transaction ID below, click the "Save Changes" button to activate you license.</p>
<?php } ?>
 <table class="form-table">
  <tbody>
  <tr>
      <th scope="row">License Key</th>
      <td>
        <div class="dgit_group">
          <input type="password" class="dgit_key regular-text" name="wpoptimiser-settings-options[license_key]" placeholder="Enter license key" value="<?php echo $this->licensing->get_license_key();?>">
          <?php if($this->licensing->has_error()) { ?>
           <div class="dgit_error msg">
            <?php echo $this->licensing->get_error(); ?>
           </div>
        <?php } else {
            $licenseData = $this->licensing->get_license_data();
            if(!empty($licenseData)) {
                echo '<div class="dgit_status msg"><span>License Status:</span> '.ucfirst(strtolower($licenseData->status)).' <span><br>Product:</span> '.$licenseData->product_name;
                if($this->licensing->has_license_message()) { echo '<br>'.$this->licensing->get_license_message(); }
                echo '</div>';
            }
          } ?>
       </div>
      </td>
    </tr>
   <tr>
    <th scope="row" colspan="2"><h3>Optimize Database Settings</h3></td>
   <tr>
    <th scope="row"><?php _e( 'How many weeks data to retain'); ?></td>
    <td>
      <select name="wpoptimiser-settings-options[retain_interval]">
       <option value="0">None</option>
<?php for($i=1; $i <6; $i++) { ?>
       <option value="<?php echo $i;?>" <?php selected( $i, $this->gen_settings['retain_interval']);?>><?php echo $i;?> Week</option>
<?php } ?>
      </select>
      <p class="description"><?php _e( 'When performing database optimization this option will set how many weeks post/page revision data to retain. Data older than this will be removed. Please note the optimiser will not remove any active data, just database clutter. (Our recommendation is 2 weeks)'); ?></p>
    </td>
   </tr>
  </tbody>
 </table>
 <p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
</form>
</div>
