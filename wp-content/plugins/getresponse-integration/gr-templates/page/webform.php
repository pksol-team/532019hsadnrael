<?php

defined( 'ABSPATH' ) || exit;

$css   = ( 'off' === $params['css'] ) ? htmlspecialchars( '&css=1' ) : "";
$style = 'margin-left: auto; margin-right: auto; width:' . $params['center_margin'] . 'px;';
?>

<?php if ( 'on' === $params['center'] ) : ?>
	<div style=" <?php echo $style ?>">
<?php endif ?>
	<script type="text/javascript" src="<?php echo $params['url'] . $css . $variant ?>'"></script>


<?php if ( 'on' === $params['center'] ) : ?>
	</div>
<?php endif ?>
