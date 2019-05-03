<?php

/**
 * Provides the markup for a select field
 *
 * @package    WPOptimiser
 * @subpackage WPOptimiser/admin/partials
 */

if ( ! empty( $atts['label'] ) ) {

	?><label for="<?php echo esc_attr( $atts['id'] ); ?>"><?php esc_html_e( $atts['label'], 'employees' ); ?>: </label><?php

}
?><select
	aria-label="<?php esc_attr( _e( $atts['aria']) ); ?>"
	class="<?php echo esc_attr( $atts['class'] ); ?>"
	id="<?php echo esc_attr( $atts['id'] ); ?>"
	name="<?php echo esc_attr( $atts['name'] ); ?>"><?php

if ( ! empty( $atts['blank'] ) ) {

	?><option value><?php esc_html_e( $atts['blank']); ?></option><?php

}

foreach ( $atts['selections'] as $selection ) {

	if ( is_array( $selection ) ) {

		$label = $selection['label'];
		$value = $selection['value'];

	} else {

		$label = strtolower( $selection );
		$value = strtolower( $selection );

	}

	?><option
		value="<?php echo esc_attr( $value ); ?>" <?php
		selected( $atts['value'], $value ); ?>><?php

		esc_html_e( $label);

	?></option><?php

} // foreach

?></select>
<?php
if ( ! empty( $atts['suffix'] ) ) {
	?><span class="field-suffix"><?php esc_html_e( $atts['suffix']); ?></span><?php
}
?>
<p class="description"><?php esc_html_e( $atts['description']); ?></p>
