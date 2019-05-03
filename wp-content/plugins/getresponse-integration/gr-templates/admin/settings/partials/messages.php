<?php

defined( 'ABSPATH' ) || exit;

/**
 * Display API Key error message.
 */
?>

<?php
    $errorMessages = gr()->getErrorMessages();
    if (!empty($errorMessages)): ?>
    <div class="gr_messages">
	<?php foreach($errorMessages as $message) : ?>
		<div class="error">
			<p><?php _e($message, 'Gr_Integration') ?></p>
		</div>
	<?php endforeach ?>
    </div>
<?php endif ?>

<?php
    $successMessages = gr()->getSuccessMessages();
    if (!empty($successMessages)): ?>
    <div class="gr_messages">
	<?php foreach ($successMessages as $message) : ?>
		<div class="notice notice-success">
			<p><?php _e($message, 'Gr_Integration') ?></p>
		</div>
	<?php endforeach ?>
    </div>
<?php endif ?>

