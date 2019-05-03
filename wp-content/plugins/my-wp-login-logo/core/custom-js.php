<?php
/* Custom JS for Login Page */

		$custom_logo_fadein = get_option('wp_custom_login_logo_fadein','true');
		$custom_logo_fadetime = get_option('wp_custom_login_logo_fadetime','2500');
		
		if($custom_logo_fadein) {
			echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
			<script type="text/javascript">// <![CDATA[
			jQuery(document).ready(function() { jQuery("#loginform,#nav,#backtoblog").css("display", "none");          jQuery("#loginform,#nav,#backtoblog").fadeIn('.$custom_logo_fadetime.');     
			});
			// ]]></script>';
		}
?>