<?php

/*
Plugin Name: EngagiFire WP
Plugin URI: http://engagifire.com
Description: EngagiFire WP Integration Plugin
Version: 1.0
Author: EngagiFire
Author URI: http://engagifire.com
License: GPL2
*/

define('ENGAGIFIRE_HOST','app.wishloop.com');

add_action( 'admin_menu', 'engagifire_menu' );
add_action( 'admin_init', 'engagifire_register_options');
add_action( 'wp_footer',  'engagifire_script');


// registering engagifire options
function engagifire_register_options() {
	register_setting('engagifire_options', 'engagifire_user_id' );
}

// admin menu
function engagifire_menu() {
	add_options_page( 'EngagiFire Settings', 'Engagifire', 'manage_options', 'engagifire-wp', 'engagifire_options' );
}

// engagifire options page
function engagifire_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	// condition : eq (equals), gt(greater than), lt(less than), df(different de)
	// pre_callback : func to execute on value bfr going any further
	// choices : array of values / first = default
	// type : text, text_simple, text_licensekey, text_licensestatut, single_checkbox, radio, dropdown , textarea
	$lippsi_options =  array(
		'license' => array(
			'label'=> 'Settings',
		                    'desc'=> 'By using this plugin, EngagiFire is easily integrated into your website!',
		                    'fields' => array(
			                    array(
				                    'type'	 	=> 'text',
				                    'name' 		=> 'engagifire_user_id',
				                    'value'     => '',
				                    'label'		=> 'Your User ID',
				                    'desc'      => 'You can get your user ID from your member area > integrations.'
			                    ),
								/*
			                    [
				                    'type'	 	=> 'text_simple',
				                    'name' 		=> 'Useful',
				                    'value'     => '<a href="http://engagifire.com/integrations/" target="_blank">Get your User ID</a> - <a href="http://engagifire.com/support/" target="_blank">Help</a>',
				                    'label'		=> 'Useful Links',
			                    ],
								*/
		                    ),
		)
	);
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			$active = key($lippsi_options);
			// $status = base64_decode( get_option( 'widget_api_endpoint' ));

			foreach ($lippsi_options as $tab_id => $tab_content) {
				if($active == $tab_id)
					$active_class = 'nav-tab-active';
				else
					$active_class = '';

				?>
				<a href="#"
				   id="<?php echo $tab_id; ?>"
				   class="nav-tab <?php echo $active_class; ?>"><?php echo $tab_content['label']; ?></a>
			<?php
			}
			?>
		</h2>
		<form method="post" action="options.php">
			<?php
			settings_fields('engagifire_options');
			// Printing tabs contents
			foreach ($lippsi_options as $tab_id => $tab_content)
			{
				if ($tab_id == $active)
					$display = "display:block;";
				else
					$display = "display:none;";

				?>
				<div class="tab-content <?php echo $tab_id; ?>" style="<?php echo $display; ?>">
					<div class="manage-menus"> <?php echo $tab_content['desc']; ?>	</div>
					<table class="form-table">
						<tbody>
						<?php
						foreach ($tab_content['fields'] as $field)
						{
							engagifire_render_setting_field($field);
						}
						?>
						</tbody>
					</table>
				</div>
			<?php
			} // END FOREACH
			?>

			<?php
			submit_button();
			?>
		</form>

	</div>
	<?php
}

// rendering the options page fields
function engagifire_render_setting_field($data)
{
	//$status 	= base64_decode(get_option( 'widget_api_endpoint' )); // status=widget_api_endpoint

	$bfr_label = '<tr valign="top">
					<th scope="row" valign="top">';
	$aftr_label = '</th>
					<td>';
	$aftr_field = '</td>
					</tr>';

	// eq (equals), gt(greater than), lt(less than), df(different de)
	if (isset($data['condition']))
	{
		$condition = explode(':', $data['condition']);
		$id_to_compare = $condition[0];

		$operator = substr($condition[1], 0, 2);
		$value    = str_replace($operator, '', $condition[1]);
		$value    = str_replace('(', '', $value);
		$value    = str_replace(')', '', $value);

		$attrs = 'data-condition="true" data-id_to_compare="'.$id_to_compare.'" data-operator="'.$operator.'" data-value="'.$value.'"';
	}
	else
		$attrs = '';

	// the label
	?>
	<tr valign="top">
		<th scope="row" valign="top">
			<label for="<?php echo $data['name']; ?>"><?php _e($data['label']); ?></label>
		</th>
		<td>
			<?php
			// the field
			$stored_value = esc_attr(get_option($data['name'], @$data['value'] ) );

			// Check if there is a function to execute before showing the value
			if (isset($data['pre_callback']))
			{
				$stored_value = call_user_func($data['pre_callback'], $stored_value);
			}

			switch ($data['type']) {

				case 'text_simple':
					echo '<p '.$attrs.' >'.$data['value'].'</p>';
					break;

				case 'text_licensestatut':
					if (@$status == 'valid')
						echo('<span style="color:green">'.__('Activated').'</span>');
					else if (@$status == 'invalid' || @$status == 'missing' || @$status == 'deactivated' || $status == 'invalid')
						echo ('<span style="color:red">'.__('Deactivated').'</span>');
					else if (@$status == 'expired')
						echo ('<span style="color:red">'.__('Expired. Please Renew to receive security patchs, features updates & technical support.').'</span>');
					else if (@$status == 'revoked' || @$status == 'disabled')
						echo ('<span style="color:red">Revoked. Please contact the support to know why.</span>');
					else if (@$status == 'no_activations_left')
						echo ('<span style="color:red">No Activations Left. You are trying to activate this license more than allowed.</span>');
					else
						echo ('<span style="color:red">No statut.</div>');
					break;

				case 'text':
					echo '<input '.$attrs.' id="'.$data['name'].'" name="'.$data['name'].'" type="text" class="regular-text" value="'.$stored_value.'" />';
					break;

				case 'divider':
					echo '<hr style="margin-top:10px"/>';
					break;

				case 'text_licensekey':

					$status   = base64_decode( get_option( 'widget_api_endpoint' ));

					echo '<input '.$attrs.' id="'.$data['name'].'" name="'.$data['name'].'" type="text" class="regular-text" value="'.$stored_value.'" /> &nbsp;';

					if( $status !== false && ($status == 'valid' || $status == 'expired'))
					{
						echo '<input type="submit" name="deactivate_license" class="button button-secondary" value="'.__('Deactivate').'"/>';
					}
					else
					{
						echo '<input type="submit" name="activate_license" class="button button-secondary" value="'.__('Activate').'"/>';
					}
					break;

				case 'single_checkbox':
					if ($stored_value == key($data['choices']))
					{
						$checked = "checked='checked'";
					}
					else
					{
						$checked = "";
					}

					echo ' <input '.$attrs.' id="'.$data['name'].'" name="'.$data['name'].'" type="checkbox" value="'.key($data['choices']).'"  '.$checked.'/> <label for="'.$data['name'].'">'.$data['choices'][key($data['choices'])].'</label>';
					break;

				case 'radio':
					foreach ($data['choices'] as $key => $value)
					{
						if ($key == $stored_value )
							$checked = 'checked="checked"';
						else
							$checked = '';

						echo '<input '.$attrs.' type="radio" id="'.$data['name'].'_'.$key.'" name="'.$data['name'].'" value="'.$key.'" '.$checked.'/> <label for="'.$data['name'].'_'.$key.'">'.$value.'</label> &nbsp; ';
					}
					break;

				case 'textarea':
					echo "<textarea ".$attrs." name='".$data["name"]."' id='".$data["name"]."' rows=6 cols=40 class='".$data["name"]." form-control'>".$stored_value."</textarea>";
					break;

				case 'dropdown':
					echo '<select '.$attrs.' name="'.$data['name'].'" id="'.$data['name'].'">';
					foreach ($data['choices'] as $key => $value)
					{
						if ($key == $stored_value )
							$checked = 'selected="selected"';
						else
							$checked = '';

						echo ' <option id="'.$data['name'].'_'.$key.'" name="'.$data['name'].'" value="'.$key.'" '.$checked.'/> '.$value.'  </option> ';
					}
					echo '</select>';
					break;
			}
			// the description
			?>
			<br/><p class="description"><?php _e(@$data['desc']); ?></p>
		</td>
	</tr>
<?php
}

function engagifire_script()
{
	$engagifire_user_id = get_option('engagifire_user_id', false);
	if ($engagifire_user_id != false)
	{
		?>
		<script type="text/javascript">
			(function(d, a) {
				var h = d.getElementsByTagName("head")[0], p = d.location.protocol, s;
				s = d.createElement("script");
				s.type = "text/javascript";
				s.charset = "utf-8";
				s.async = true;
				s.defer = true;
				s.src = "//<?php echo ENGAGIFIRE_HOST; ?>/bjs/" + a;
				h.appendChild(s);
			})(document, '<?php echo $engagifire_user_id; ?>');
		</script>
	<?php
	}
}