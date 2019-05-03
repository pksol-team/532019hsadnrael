<?php
/**
 * Provide a admin area view for the plugin image optimization
 *
 * This file is used to markup the admin-facing aspects of the plugin image optimization settings page.
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
 <h1 class="page-title">Optimize Images - Automatic Optimization Of Images Uploaded to Media Library</h1>
</div>
<?php if(!empty($message)) { ?>
<?php echo $message;?>
<?php } ?>
<?php

  if (is_plugin_active( 'tiny-compress-images/tiny-compress-images.php' ) ) {
    echo '<div class="error">';
    echo "<p>You will need to deactivate the 'Compress JPEG & PNG images' Plugin by TinyPNG before you can access this section.</p>";
    echo '</div>';
    echo '</div>';
    return;
  }

  $errors = get_option($this->plugin_name. '-optimizeimgs-acclimit');
  if(!empty($errors)) {
    echo '<div class="error">';
    echo "<p>$errors</p>";
    echo '</div>';
  }
$image_sizes = get_intermediate_image_sizes();
$optimize_all_imgs_settings = array();
$optimize_all_imgs_settings['compimg'][0] = 'Y';
foreach ( $image_sizes as $size ) {
 if ( '_tiny_wpoptimiser' === $size ) continue;
 $optimize_all_imgs_settings['compimg'][$size] = 'Y';
}

$stats = Tiny_Image::get_optimization_statistics($compressor, $optimize_all_imgs_settings );
$compcount = get_option($this->plugin_name. '-optimizeimgs-compcount');
$compcount = $compcount == false ? 0 : $compcount;
$estimated_costs = Tiny_Compress::estimate_cost($stats['available-unoptimized-sizes'], $compcount);
$compimg_settings = $this->optimizeimgs_settings['compimg'];
$active_tinify_sizes = array_count_values($compimg_settings);
$compression_count = $compressor->get_compression_count();

if(!isset($active_tinify_sizes['Y'])) $active_tinify_sizes['Y'] = 0;
?>
<form method="post" action="">
<input type="hidden" name="action" value="wpoptimiser_save_optimizeimgs_setup">
 <table class="wp-list-table widefat">
  <tbody>
   <tr class="info">
    <td colspan="2">
      <p class="info"><?php echo __('Once active, this feature will automatically optimize the images that are uploaded to the media library or set as the featured image on a post. With each uploaded image WordPress will automatically create multiple resized versions that are required by your theme which will also be optimized. We use the popular image optimization service TinyPNG which applies the best possible optimization.');?></p>
    </td>
   </tr>
   <tr class="panel">
    <td>
     <h3><?php esc_html_e( 'Media Library JPEG/PNG Images') ?></h3>
     <div class="infobox infobox-blue">
       <div class="infobox-icon">
					<i class="ace-icon dashicons dashicons-upload"></i>
			 </div>
      <div class="infobox-data">
       <span class="infobox-text">Images In Library</span>
        <span class="infobox-data-number"><?php echo number_format($stats['optimized-image-sizes']+$stats['available-unoptimized-sizes'], 0); ?></span>
      </div>
     </div>
     <div class="infobox infobox-green">
       <div class="infobox-icon">
         <i class="ace-icon dashicons dashicons-tagcloud"></i>
      </div>
      <div class="infobox-data">
       <span class="infobox-text">Images Optimized</span>
        <span class="infobox-data-number"><?php echo number_format($stats['optimized-image-sizes'], 0); ?></span>
      </div>
     </div>
     <?php if(!empty($this->optimizeimgs_settings['tinypngkey'])) { ?>
      <div class="infobox infobox-green2">
        <div class="infobox-icon">
          <i class="ace-icon dashicons dashicons-admin-tools"></i>
       </div>
       <div class="infobox-data">
        <span class="infobox-text">TinyPng API Usage<br>This Month</span>
         <span class="infobox-data-number"><?php echo number_format($compression_count, 0); ?> / 500</span>
       </div>
      </div>
    <?php } ?>
    <div class="clearfix marginTop10"></div>
    <?php
    if ( 0 == $stats['available-unoptimized-sizes'] ) {
      echo '<div class="stats-info"><p>Great Work! Your entire library is optimized!</p></div>';
    }
    if ( $estimated_costs > 0 ) {
      printf( '<div class="stats-info"><p>If you wish to optimize more than %d %s a month and you are still on a free account %s.</p></div>', $compressor::MONTHLY_FREE_COMPRESSIONS, 'image sizes', '<a href="https://tinypng.com/dashboard/developers" target="_blank">upgrade here</a>');
    }
    if(empty($this->optimizeimgs_settings['tinypngkey'])) {
      echo '<div class="stats-error"><p>You need to enter your TinyPng API key before we can get to optimizing your images.</p></div>';
    }
    if ( 0 == $stats['optimized-image-sizes'] + $stats['available-unoptimized-sizes'] ) {
     $percentage_of_files = 0;
    } else {
     $percentage_of_files = round( $stats['optimized-image-sizes'] / ( $stats['optimized-image-sizes'] + $stats['available-unoptimized-sizes'] ) * 100, 2 );
    }
    if ( $stats['available-unoptimized-sizes'] > 0 ) {
      echo '<div class="stats-info"><p>';
      echo number_format($percentage_of_files, 0) . '% of the images in the library are optimized. Use the bulk optimization section to optimize the remainder of your images.';
      echo '</p></div>';
    }
    ?>
</td>
<td>
  <div class="infobox infobox-blue2 infobox-big">
    <h3><?php esc_html_e( 'Image Optimization&nbsp;Savings') ?></h3>
		<div class="infobox-progress">
			<div class="easypiechart" data-percent="<?php echo $stats['display-percentage'];?>"><?php echo $stats['display-percentage'];?>%</div>
    </div>
		<div class="infobox-data">
			<div class="infobox-text"><?php echo ( $stats['unoptimized-library-size'] ? size_format( $stats['unoptimized-library-size'], 2 ) : '-'); ?> unoptimized size</div>
			<div class="infobox-text"><?php echo ($stats['optimized-library-size'] ? size_format( $stats['optimized-library-size'], 2 ) : '-') ?> optimized size</div>
		</div>
	</div>
</td>
</tr>
</tbody>
</table>
<table class="form-table img-optimize">
 <tbody>
   <tr>
    <th scope="row"><?php _e( 'Activate Image Optimization'); ?></th>
    <td>
      <label>
       <input type="checkbox" class="ace ace-switch ace-switch-4 btn-rotate" name="wpoptimiser-optimizeimgs-options[active]" value="Y" <?php checked( 'Y', $this->optimizeimgs_settings['active']); ?>/>
       <span class="lbl"></span>
      </label>
     <p class="description"><?php _e( 'Once active all new images uploaded to your site will be automatically optimized.'); ?></p>
    </td>
   </tr>
   <tr>
    <th scope="row"><?php _e( 'TinyPng API Key'); ?></th>
    <td>
     <input type="text" class="regular-text" name="wpoptimiser-optimizeimgs-options[tinypngkey]" value="<?php echo $this->optimizeimgs_settings['tinypngkey']; ?>"/>
     <p class="description"><?php _e( 'You can find your key or create a free account at the TinyPng <a href="https://tinypng.com/developers" target="_blank">Developer Dashboard</a>'); ?></p>
    </td>
   </tr>
   <tr>
    <th scope="row"></th>
    <td>
     <input type="hidden" name="wpoptimiser-optimizeimgs-options[compimg][0]" value="Y">
<?php
     foreach ( $image_sizes as $size ) {
 			if ( '_tiny_wpoptimiser' === $size ) continue;
 			list($width, $height) = $compressor->get_intermediate_size( $size );
 			if ( $width || $height ) {
        if(!isset( $this->optimizeimgs_settings['compimg'][$size]))  $this->optimizeimgs_settings['compimg'][$size] = '';
?>
        <input type="hidden" name="wpoptimiser-optimizeimgs-options[compimg][<?php echo $size;?>]" value="Y">
<?php
 			}
 		}
?>
    </td>
   </tr>
  </tbody>
 </table>
 <p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
 <p>Please note when you upload images to WordPress each image will automatically create multiple variants depending on your theme requirements for thumbnails & gallery Images.</p>
</form>
</div>
