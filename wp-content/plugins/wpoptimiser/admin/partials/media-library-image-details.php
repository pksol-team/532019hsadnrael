<?php

$available_sizes = array_keys( $tiny_image->get_sizes() );
$active_sizes = $tiny_image->get_sizes();
$compimg_settings = $this->optimizeimgs_settings['compimg'];
$active_tinify_sizes = array_count_values($compimg_settings);
$error = $tiny_image->get_latest_error();
$total = $tiny_image->get_count( array( 'modified', 'missing', 'has_been_compressed', 'compressed' ) );
$active = $tiny_image->get_count( array( 'uncompressed', 'never_compressed' ) );
$image_statistics = $tiny_image->get_statistics();
$available_unoptimized_sizes = $image_statistics['available_unoptimized_sizes'];
$size_before = $image_statistics['initial_total_size'];
$size_after = $image_statistics['optimized_total_size'];

$size_exists = array_fill_keys( $available_sizes, true );
ksort( $size_exists );
$delta = $size_before - $size_after;
?>
<div class="details" >
<span class="icon spinner hidden"></span>
<?php if ( $size_before - $size_after ) {
    $displayPercentage = round( (1 - $size_after / floatval( $size_before )) * 100  );
?>
 <div class="file-comp-stats clearfix">
  <div class="file-size">
   <span style="height:<?php echo $displayPercentage;?>%;"><span class="compressed"></span></span>
  </div>
	<span class="message">
	 <?php printf( '%.0f%%', $displayPercentage); ?><br> Compression
	</span>
 </div>
<?php } ?>
<?php if ( $total['has_been_compressed'] > 0  && 0 == $available_unoptimized_sizes && $delta > 0) { ?>
	<span class="icon dashicons dashicons-yes success"></span> <span class="message">Complete</span>
<?php } else if ( $total['has_been_compressed'] > 0  && 0 == $available_unoptimized_sizes && $delta == 0) { ?>
	<span class="icon dashicons dashicons-yes success"></span> <span class="message">Already Optimized</span>
<?php } else if ( $total['has_been_compressed'] > 0 || (0 == $total['has_been_compressed'] && 0 == $available_unoptimized_sizes) ) { ?>
  <span class="icon dashicons dashicons-yes success"></span>
	<span class="message"><?php printf( wp_kses( _n( '<strong>%d</strong> size optimized', '<strong>%d</strong> sizes optimized', $total['has_been_compressed'] ), array('strong' => array()) ), $total['has_been_compressed'] ) ?></span>
	<br/>
  <?php if ( $available_unoptimized_sizes > 0 ) { ?>
    <span class="icon dashicons dashicons-flag warn"></span>
  	<span class="message"><?php printf( esc_html( _n( '%d size left to optimize', '%d sizes left to optimize', $available_unoptimized_sizes ) ), $available_unoptimized_sizes ) ?></span><br>
<?php if(empty($this->optimizeimgs_settings['tinypngkey'])) { ?>
    <div class="stats-error"><p><span class="icon dashicons dashicons-warning"></span> TinyPng API key Required To Optimize.</p></div>
<?php } else { ?>
    <button type="button" class="wpopti-compress button button-small button-primary" data-id="<?php echo $tiny_image->get_id() ?>">Optimize Now</button>
<?php } ?>
<?php } ?>
<?php }  else if ( $available_unoptimized_sizes > 0 ) { ?>
    <span class="icon dashicons dashicons-flag warn"></span>
  	<span class="message"><?php printf( esc_html( _n( '%d size to optimize', '%d sizes to optimize', $available_unoptimized_sizes ) ), $available_unoptimized_sizes ) ?></span><br>
<?php if(empty($this->optimizeimgs_settings['tinypngkey'])) { ?>
    <div class="stats-error"><p><span class="icon dashicons dashicons-warning"></span> TinyPng API key Required To Optimize.</p></div>
<?php } else { ?>
    <button type="button" class="wpopti-compress button button-small button-primary" data-id="<?php echo $tiny_image->get_id() ?>">Optimize Now</button>
<?php } ?>
  <?php } ?>

<?php if ( $error ) { ?>
  <br><span class="icon dashicons dashicons-no error"></span>
	<span class="message error_message">
		<?php echo 'Latest error: ' . esc_html( $error ) ?>
	</span>
	<br/>
<?php } ?>
</div>
