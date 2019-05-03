<h2>Perfect Audience Plugin for WordPress</h2>

<div>
<p>Please enter your unique "advertiser ID" numbers below.</p>

<p>Your Perfect Audience Advertiser ID will be found in your Perfect Audience account, under “Manage” → “User Tracking”</p>

<p><a href="http://perfectaudience.com/users/sign_in">Click here</a> to log in to your Perfect Audience account.</p>

</div>

<form action="options.php" method="POST">
	
	<?php settings_fields('perfect_setting') ?>
	<?php do_settings_sections( 'wp_perfectaudience' ) ?>
	
	<input type="submit" value="Update Perfect Audience Settings" />
</form>