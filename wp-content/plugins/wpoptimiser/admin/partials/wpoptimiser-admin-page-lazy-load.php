<?php
/**
 * Provide a admin area view for the lazy load images settings
 *
 * This file is used to markup the admin-facing aspects of the plugin lazy load images settings page.
 *
 * @since      1.0.0
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin/partials
 */
?>
<div class="wrap">
<div class="branding clearfix">
 <div class="logo">
  <img class="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wpopti-small-logo.png';?>">
  <span class="help-link"><a href="https://cybertactics.net/products" target="_blanl">Other Products</a> | <a href="https://cybertactics.net" target="_blanl">Blog</a></span>
 </div>
 <h1 class="page-title"><?php echo esc_html( get_admin_page_title() ); ?></h1>
</div>
<?php if(!empty($errors)) { ?>
<div class="notice notice-error"><?php echo $errors;?></div>
<?php } ?>
<?php if(!empty($message)) { ?>
<?php echo $message;?>
<?php } ?>
<form method="post" action="">
<input type="hidden" name="action" value="wpoptimiser_save_lazyload_setup">
 <table class="form-table">
  <tbody>
    <tr class="info">
     <td colspan="2">
      <p class="info"><?php echo __('Once active this feature improves page load times by loading your images on demand. It delays loading of images outside of the browsers viewport (visible part of a web page). Images won\'t be loaded until the user scrolls down to them. On long pages and/or pages containing many large images, this will make the page load much faster by reducing server load and saving bandwidth.<br><br>You can also enable/disable Lazy Load Images on an individual post/page. When editing a post/page use the \'WP Optimiser\' box to enable/disable Lazy Load Images. ');?></p>
     </td>
    </tr>
   <tr>
    <th scope="row"><?php _e( 'Activate Lazy Load Images'); ?></th>
    <td>
     <label>
      <input type="checkbox" class="ace ace-switch ace-switch-4 btn-empty" name="wpoptimiser-lazyload-options[active]" value="Y" <?php checked( 'Y', $this->lazyload_settings['active']); ?>/>
      <span class="lbl"></span>
     </label>
    </td>
   </tr>
  </tbody>
 </table>
 <p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
</form>
</div>
