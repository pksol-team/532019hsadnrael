<?php

/**
 * Widget in admin section.
 *
 */
defined( 'ABSPATH' ) || exit;
?>
<p id="gr_title">
	<label for="<?php echo gr()->int_widget->get_field_id( 'title' ); ?>">
		<?php _e( 'Title' ); ?>:
	</label>
	<input class="widefat"
	       id="<?php echo gr()->int_widget->get_field_id( 'title' ); ?>"
	       name="<?php echo gr()->int_widget->get_field_name( 'title' ); ?>"
	       type="text" value="<?php echo $title; ?>"/>
</p>

<p>
<div class="gr-loading-select">
	<img src="images/loading.gif"/>
</div>

<div class="gr_webform_select" style="display: none;">
	<label for="<?php echo gr()->int_widget->get_field_id( 'select' ); ?>">
		<?php _e( 'Web Form' ); ?>:
	</label>
	<select
		name="<?php echo gr()->int_widget->get_field_name( 'select' ); ?>"
		id="<?php echo gr()->int_widget->get_field_id( 'select' ); ?>"
		style="max-width: 278px;"
		class="widefa"
		onchange="setVariants(jQuery(this));"
	>
		<optgroup label="<?php _e( 'New Forms' ); ?>" id="gr-optgroup-new"></optgroup>
		<optgroup label="<?php _e( 'Old Web Forms' ); ?>" id="gr-optgroup-old"></optgroup>
	</select>
</div>

<div class="gr-loading">
	<img src="images/loading.gif"/>
</div>

<div class="grvariants" style="display: none;">
	<label style="padding-right: 19px;" for="<?php echo gr()->int_widget->get_field_id( 'variants' ); ?>">
		<?php _e( 'Variant' ); ?>:
	</label>
	<select
		name="<?php echo gr()->int_widget->get_field_name( 'variants' ); ?>"
		id="<?php echo gr()->int_widget->get_field_id( 'variants' ); ?>"
		style="max-width: 278px;" class="widefa grvariants_select"
	>
	</select>
</div>
</p>

<p id="gr_css_style">
	<input
		id="<?php echo gr()->int_widget->get_field_id( 'style' ); ?>"
		name="<?php echo gr()->int_widget->get_field_name( 'style' ); ?>"
		type="checkbox"
		value="1" <?php checked( '1', $style ); ?> />

	<label for="<?php echo gr()->int_widget->get_field_id( 'style' ); ?>">
		<?php _e( 'Use Wordpress CSS styles (Old Web Forms)', 'Gr_Integration' ); ?>
	</label>
</p>

<p id="gr_center">
	<input
		id="<?php echo gr()->int_widget->get_field_id( 'center' ); ?>"
		name="<?php echo gr()->int_widget->get_field_name( 'center' ); ?>"
		type="checkbox"
		value="1" <?php checked( '1', $center ); ?> />

	<label for="<?php echo gr()->int_widget->get_field_id( 'center' ); ?>">
		<?php _e( 'Center Webform', 'Gr_Integration' ); ?>
	</label>

	<label for="<?php echo gr()->int_widget->get_field_id( 'center_margin' ); ?>">
		(<?php _e( 'Margin', 'Gr_Integration' ); ?>:
		<input
			id="<?php echo gr()->int_widget->get_field_id( 'center_margin' ); ?>"
			name="<?php echo gr()->int_widget->get_field_name( 'center_margin' ); ?>"
			type="text"
			value="<?php echo ! empty( $center_margin ) ? $center_margin : '200'; ?>"
			size="4"/>px)
	</label>
</p>

<p id="gr_version">
	<input
		id="<?php echo gr()->int_widget->get_field_id( 'version' ); ?>"
		name="<?php echo gr()->int_widget->get_field_name( 'version' ); ?>"
		type="hidden" value="old" size="4"/>
</p>