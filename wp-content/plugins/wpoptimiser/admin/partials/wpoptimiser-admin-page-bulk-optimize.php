<?php
/**
 * Provide a admin area view for the plugin bulk image optimization
 *
 * This file is used to markup the admin-facing aspects of the plugin bulk image optimization process.
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
 <h1 class="page-title">Bulk Optimization of Existing Images In Your Media Library</h1>
</div>
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

if(isset($_GET['step']) && $_GET['step'] == 'done') {
  echo '<div class="stats-info">';
  echo "Bulk Optimization Finished";
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
<form method="post" action="" class="bulk-optimize">
 <input type="hidden" name="wpoptimiser-bulkoptimizeimgs-options[compimg][0]" value="Y">
<?php
 foreach ( $image_sizes as $size ) {
   if ( '_tiny_wpoptimiser' === $size ) continue;
   list($width, $height) = $compressor->get_intermediate_size( $size );
   if ( $width || $height ) {
?>
  <input type="hidden" name="wpoptimiser-bulkoptimizeimgs-options[compimg][<?php echo $size;?>]" value="Y">
<?php
   }
 }
?>
<div id="bulk-optimiz-table">
 <table class="wp-list-table widefat bulk-optimize">
  <tbody>
    <tr class="info">
     <td colspan="2">
       <p class="info"><?php echo __('In this section you can bulk optimize any existing images in your media library. You have a <strong>free monthly optimisation allowance of 500 images</strong><br><br>If your site has more than 500 images you\'ll see an estimate of what these will cost to optimize over your allowance - you can upgrade your account with <a href="https://tinypng.com/developers" target="_blank">Tiny PNG</a> or you can wait till your free allowance renews.');?>
       </p>
     </td>
    </tr>
   <tr class="panel">
    <td>
     <h3><?php esc_html_e( 'Unoptimized Media Library JPEG/PNG  Images') ?></h3>
     <div class="infobox infobox-red uncomp-images">
       <div class="infobox-icon">
         <i class="ace-icon dashicons dashicons-text"></i>
      </div>
      <div class="infobox-data">
       <span class="infobox-text">Unoptimized Images</span>
        <span class="infobox-data-number"><?php echo number_format($stats['available-unoptimized-sizes'], 0); ?></span>
      </div>
     </div>
     <div class="infobox infobox-blue2 est-costs">
       <div class="infobox-icon">
         <i class="ace-icon dashicons dashicons-cart"></i>
      </div>
      <div class="infobox-data">
       <span class="infobox-text">Estimated cost</span>
       <span class="infobox-data-number">$ <?php echo number_format( $estimated_costs, 2 ) ?> USD</span>
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
   <tr>
    <td colspan="2">
      <?php
      if(empty($this->optimizeimgs_settings['tinypngkey'])) {
        echo '<div class="stats-error"><p>You need to enter your TinyPng API key before we can get to optimizing your images.</p></div>';
      }
      if ( 0 == $stats['available-unoptimized-sizes'] ) {
        echo '<div class="stats-info"><p>Great Work! Your entire library is optimized!</p></div>';
      }
    ?>
    </td>
   </tr>
 </tbody>
 </table>
<?php  if ( $stats['available-unoptimized-sizes'] > 0 ) { ?>
 <table class="form-table bulk-optimize">
  <tbody>
   <tr>
    <th scope="row"><?php _e( 'How many images to bulk optimize'); ?></td>
    <td>
      <select name="wpoptimiser-bulkoptimizeimgs-options[count]">
<?php for($i=5; $i <35; $i+=5) { ?>
       <option value="<?php echo $i;?>"><?php echo $i;?></option>
<?php } ?>
      <option value="50">50 (high speed hosts only)</option>
      <option value="100">100 (high speed hosts only)</option>
      </select>
    </td>
   </tr>
    <tr>
      <td colspan="2">
        <p class="dbo-problem">Please note image processing can take some time. We recommend you bulk process your images in maximum batches of 30 on shared & low budget hosting accounts or reduce the batch size if you run into timeout issues.</p>
      </td>
    </tr>
   <tr>
     <td colspan="2">
       <p class="submit"><input type="submit" value="Start Bulk Optimization" class="button button-primary" id="submit" name="submit"></p>
       <p>Please note when you upload images to WordPress each image will automatically create multiple variants depending on your theme requirements for thumbnails & gallery Images.</p>
     </td>
   </tr>
  </tbody>
 </table>
<?php } ?>
</div>
</form>
</div>
