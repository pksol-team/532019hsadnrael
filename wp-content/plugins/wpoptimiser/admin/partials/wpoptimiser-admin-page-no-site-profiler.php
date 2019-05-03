<?php
/**
 * Provide a admin area view for cases where the site profiler is not available
 *
 * This file is used to markup the admin-facing aspects when the site profiler is not available.
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
<table class="form-table">
 <tbody>
   <tr class="info">
    <td colspan="2">
     <p class="info">Oops PHP 7 is detected on your server. The Site Profiler feature is not currently supported in PHP 7 - You can ask your host to change your PHP version to PHP 5.6 (most compatible version) or wait for this feature to become available in future.</p>
    </td>
   </tr>
  </tbody>
</table>
</div>
