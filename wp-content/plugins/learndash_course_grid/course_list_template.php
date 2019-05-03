<?php
/**
 * @package nmbs
 */
$col = empty($shortcode_atts["col"])? 3:intval($shortcode_atts["col"]);
$smcol = $col/1.5;
$col = empty($col)? 1:($col >= 12)? 12:$col;
$smcol = empty($smcol)? 1:($smcol >= 12)? 12:$smcol;
$col = intVal(12/$col);
$smcol = intVal(12/$smcol);

global $post; $post_id = $post->ID;

$options = get_option('sfwd_cpt_options');
$currency = null;
if(!is_null($options)){
	if(isset($options['modules']) && isset($options['modules']['sfwd-courses_options']) && isset($options['modules']['sfwd-courses_options']['sfwd-courses_paypal_currency']))
	$currency = $options['modules']['sfwd-courses_options']['sfwd-courses_paypal_currency'];
}
if(is_null($currency))
	$currency = 'USD';


$course_options = get_post_meta($post_id, "_sfwd-courses", true);
$price = $course_options && isset($course_options['sfwd-courses_course_price']) ? $course_options['sfwd-courses_course_price'] : __('Free');
$short_description = @$course_options['sfwd-courses_course_short_description'];

if($price=='')
	$price .= 'Free';

if(is_numeric($price))
if($currency == "USD")
	$price = '$' . $price;
else
	$price .= ' ' . $currency;

?>
<div class="ld_course_grid col-sm-<?php echo $smcol;?> col-md-<?php echo $col; ?>">
	<article id="post-<?php the_ID(); ?>" <?php post_class('thumbnail course'); ?>>
		
		<?php 
			if($post->post_type == 'sfwd-courses'): ?>
		<div class="price <?php echo !empty($course_options['sfwd-courses_course_price']) ? "price_".$currency : __('Free')?>">
			<?php echo $price ?>
		</div>
		<?php endif;?>
		<?php $linkpermanente =  str_replace("/courses/","/",get_permalink()); ?>
		<?php if(has_post_thumbnail()) :?>
		<a href="<?php echo $linkpermanente; ?>" rel="bookmark">
			<?php the_post_thumbnail('course-thumb'); ?>
		</a>
		<?php else :?>
		<a href="<?php echo $linkpermanente; ?>" rel="bookmark">
			<img src="<?php echo plugins_url( 'no_image.jpg', __FILE__); ?>"/>
		</a>
		<?php endif;?>
		<div class="caption">
			<h3 class="entry-title"><?php the_title(); ?></h3>
			<?php if(!empty($short_description)) { ?>
			<p class="entry-content"><?php echo do_shortcode($short_description); ?></p>
			<?php  } ?>
			<p><a class="btn btn-primary" role="button" href="<?php echo $linkpermanente; ?>" rel="bookmark"><?php _e("See more...", "learndash_course_grid"); ?></a></p>
		</div><!-- .entry-header -->
	</article><!-- #post-## -->
</div>
