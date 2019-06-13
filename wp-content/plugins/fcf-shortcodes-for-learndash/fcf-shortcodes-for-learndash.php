<?php
/*
Plugin Name: FCF Shortcodes For LearnDash
Plugin URI: https://www.discoverelearninguk.com
Description: Turn Flexible Checkout Fields into shortcodes that can be used in LearnDash certificates and other content areas
Author: Discover eLearning Ltd
Version: 0.1
Author URI: https://www.discoverelearninguk.com
License: GPL2
*/

add_shortcode('user_meta', 'user_meta_shortcode_handler');
function user_meta_shortcode_handler($atts,$content=null){

	$user_id = get_current_user_id();
	$rut = get_user_meta($user_id, 'rut', true);

	return 'RUT '.$rut;

}

add_shortcode('user_id', 'user_id_handler');
function user_id_handler() {

	$user_id = get_current_user_id();
	return $user_id;

}

add_shortcode('user_profile_image', 'user_profile_image_handler');
function user_profile_image_handler() {
	$user_id = get_current_user_id();
	$uploaded_avatar = get_user_meta($user_id, 'user_profile_img', true);

	return '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="'.$uploaded_avatar.'">';
}

add_shortcode('first_name_user', 'first_name_user_handler');
function first_name_user_handler() {

	return strtoupper(wp_get_current_user()->user_firstname);

}

add_shortcode('last_name_user', 'last_name_user_handler');
function last_name_user_handler() {

	return strtoupper(wp_get_current_user()->user_lastname);

}

add_filter( 'init', function( $template ) {
    if ( isset( $_GET['virtual_image'] ) ) {

		$user_id = get_current_user_id();
		$uploaded_avatar = get_user_meta($user_id, 'user_profile_img', true);
		
		$image = image_generate(get_avatar_url($user_id));

		//$image = image_generate($uploaded_avatar);

        die;
    }
} );


function image_generate($filename) {
	$image_s = imagecreatefromstring(file_get_contents($filename));
	$width = imagesx($image_s);
	$height = imagesy($image_s);

	$newwidth = 140;
	$newheight = 140;

	$image = imagecreatetruecolor($newwidth, $newheight);
	imagealphablending($image, true);
	imagecopyresampled($image, $image_s, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

	$mask = imagecreatetruecolor($newwidth, $newheight);

	$transparent = imagecolorallocate($mask, 25,0,0);
	imagecolortransparent($mask, $transparent);

	imagefilledellipse($mask, $newwidth/2, $newheight/2, $newwidth, $newheight, $transparent);

	$red = imagecolorallocate($mask, 0, 0, 0);
	imagecopymerge($image, $mask, 0, 0, 0, 0, $newwidth, $newheight, 100);
	imagecolortransparent($image, $red);
	imagefill($image, 0,0,$red);

	header('Content-type: image/png');
	imagepng($image);
	imagepnd($image, 'output.png');
	imagedestroy($image);
}


add_action( 'wp_ajax_get_form', 'get_form' );

function get_form() {

	$user_id = get_current_user_id();

	$profile_img_url = get_user_meta($user_id, 'user_profile_img', true);

	$profile_img = '';
	if($profile_img_url) {
		$profile_img = '<img src="'.$profile_img_url.'" style="width: 200px">';
	}

	$first_name = get_user_meta($user_id, 'first_name', true);
	$last_name = get_user_meta($user_id, 'last_name', true);
	$rut = get_user_meta($user_id, 'rut', true);
	$address = get_user_meta($user_id, 'ct_address', true);
	$phone = get_user_meta($user_id, 'ct_phone', true);

	$congo_img = plugin_dir_url( __FILE__ ) . 'congo.jpeg';

	echo '
		<p>¡Felicidades! Has finalizado tu curso en Braniff Institute Llena los siguientes datos para imprimir tus acreditaciones de manera gratuita o solicitarlas a domicilio desde $22.500:</p>
		<img src="'.$congo_img.'" style="display: block">
		<br>
		<form class="profile_form">

			<input type="hidden" name="action" value="submit_profile_form">

			<label for="first_name">
				Nombre Completo
				<input class="reqed" type="text" name="first_name" value="'.$first_name.'">
			</label>

			<label for="last_name">
				Apellido paterno y materno
				<input class="reqed" type="text" name="last_name" value="'.$last_name.'">
			</label>

			<label for="rut">
				Rut
				<input class="reqed" type="text" name="rut" value="'.$rut.'">
			</label>

			<label for="rut">
				Dirección envío diploma en caso de quererlo a domicilio (verificación con Google maps)
				<input type="text" name="ct_address" value="'.$address.'">
			</label>

			<label for="rut">
				Teléfono:
				<input class="reqed" type="text" name="ct_phone" value="'.$phone.'">
			</label>

			<br>

			<label for="">Subir foto tuya para tu credencial (Obligatorio):</label>

			<div class="profile_image_div">
			'.$profile_img.'
			</div>

			<input class="reqed" type="hidden" name="user_profile_img" value="'.$profile_img_url.'">
			<input type="file" class="inputfile" accept="image/gif, image/jpeg, image/png" />

			<strong style="display: block;">¿Seguro que toda la información esta correcta?</strong>
			
			<input type="submit" value="si, enviar mis datos" style="display: block; margin-top: 11px;">

			<img src="http://www.springsiac.org/wp-content/plugins/embed-bible-passages/images/ajax-loading.gif" class="ajax-loader" style="width: 63px; display: none;">

		</form>

	';


	die();
}



add_action( 'wp_ajax_upload_file', 'upload_file' );
function upload_file() {

	if ( ! function_exists( 'wp_handle_upload' ) ) {
	    require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	$uploadedfile = $_FILES['file'];
	$fileName = $_FILES["file"]["name"];
	$kaboom = explode(".", $fileName); // Split file name into an array using the dot
	$fileExt = end($kaboom); // Now target the last array element to get the file extension


	$upload_overrides = array( 'test_form' => false );
	$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );


	include_once(plugin_dir_path( __FILE__ ) ."ak_php_img_lib_1.0.php");

	$dir = wp_get_upload_dir()['path'];

	$target_file = $dir.DIRECTORY_SEPARATOR.$fileName;


	$resized_file = $dir.DIRECTORY_SEPARATOR."$fileName";
	$wmax = 140;
	$hmax = 140;
	ak_img_resize($target_file, $resized_file, $wmax, $hmax, $fileExt);

	echo $movefile['url'];
	
	wp_die();

}

add_action( 'wp_ajax_submit_profile_form', 'submit_profile_form' );
function submit_profile_form() {

	$data = $_POST;
	$user_id = get_current_user_id();

	update_user_meta($user_id, 'first_name', $data['first_name']);
	update_user_meta($user_id, 'last_name', $data['last_name']);
	update_user_meta($user_id, 'rut', $data['rut']);
	update_user_meta($user_id, 'user_profile_img', $data['user_profile_img']);

	update_user_meta($user_id, 'ct_address', $data['ct_address']);
	update_user_meta($user_id, 'ct_phone', $data['ct_phone']);

	die();

}

add_action( 'wp_ajax_send_email', 'send_email' );
function send_email() {

	$login_user = wp_get_current_user();
	$user_email = $login_user->data->user_email;

	$subject = 'Home Delivery User Data';

	// $to = get_option('admin_email');
	$to = 'nomanaadma@gmail.com';

	$user_data = json_decode(  str_replace('\\', '' , $_POST['user_data']) );

	$first_name = $user_data[1]->value;
	$last_name = $user_data[2]->value;
	$RUT = $user_data[3]->value;
	$address = $user_data[4]->value;
	$phone = $user_data[5]->value;

	$name = $first_name.' '.$last_name;

	$email_data = '
		Nombre Completo: '.$first_name.' <br>
		Apellido paterno y materno: '. $last_name .` <br>
		RUT: `. $RUT .` <br>
		Dirección: `. $address .` <br>
		Teléfono: `. $phone .` <br>
		<h3>Datos del usuario</h3>
	`;

	$email_data .= str_replace('Imprime tu ', '', $_POST['template']);

	$headers = '';
	$headers .= 'From: ' . $name . ' <' . $user_email . '>' . "\r\n";
	$headers .= "Reply-To: " .  $user_email . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
	$message = $email_data;

	$mails = mail($to, $subject, $message, $headers);

	die();

}


