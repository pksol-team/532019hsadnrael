<?php
/*
Plugin Name: Page Restrict
Plugin URI: http://theandystratton.com/pagerestrict
Description: Restrict certain pages to logged in users
Author: Matt Martz & Andy Stratton
Author URI: http://theandystratton.com
Version: 2.2.8

    Page Restrict is released under the GNU Lesser General Public License (LGPL)
	http://www.gnu.org/licenses/lgpl-3.0.txt

*/

// if we are in the admin load the admin functionality
if ( is_admin () )
	require_once( dirname ( __FILE__ ) . '/inc/admin.php' );

// get specific option
function pr_get_opt ( $option ) {
	$pr_options = get_option( 'pr_options' );

	// clean up PHP warning for in_array() later when they have not been saved
	if ( $option == 'posts' || $option == 'pages' ) 
	{
		if ( !isset( $pr_options[ $option] ) || !is_array( $pr_options[ $option ] ) )
		{
			$pr_options[$option] = array();
		}
	}
    return $pr_options[$option];
}

// Add headers to keep browser from caching the pages when user not logged in
// Resolves a problem where users see the login form after logging in and need 
// to refresh to see content
function pr_no_cache_headers () {
	if ( !is_user_logged_in() )
		nocache_headers();
}

// gets standard page content when page/post is restricted.
function pr_get_page_content() {
	$pr_page_content = '
		<p>' . pr_get_opt ( 'message' )  . '</p>';
	if ( pr_get_opt ( 'loginform' ) == true ) :

		$errors = '';
		if ( isset( $_GET['wp-error'] ) )
		{
			$errors = strip_tags( $_GET['wp-error'] );
			$errors = str_ireplace( 'Lost your password?', '<a href="' . wp_lostpassword_url() . '">Lost your password?</a>', $errors );
			$errors = '<div class="pr-message pr-error"><p>' . $errors . '</p></div>';
		}

		$user_login = '';

		if ( !isset( $user_login ) && isset( $_GET['pr-user-login'] ) )
		{
			$user_login = sanitize_user( $_GET['pr-user-login'] );
		}

		$pr_page_content .= '
		<form style="text-align: left;" action="' . wp_login_url() . '" method="post">
		' . $errors . '
			<p>
				<label for="log"><input type="text" name="log" id="log" value="' . esc_html ( stripslashes ( $user_login ) , 1 ) . '" size="22" /> '
				. apply_filters( 'pr_username_label', 'Username' ) . '</label><br />
				<label for="pwd"><input type="password" name="pwd" id="pwd" size="22" /> ' 
				. apply_filters( 'pr_password_label' , 'Password' ) . '</label><br />
				<input type="submit" name="submit" value="Log In" class="button" />
				<label for="rememberme"><input name="rememberme" id="rememberme" type="checkbox" checked="checked" value="forever" /> Remember me</label><br />
			</p>
			<input type="hidden" name="redirect_to" value="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" />
		</form>
		<p>
		';
		
		if ( get_option( 'users_can_register' ) )
			$pr_page_content .= '	<a href="' . wp_registration_url() . '">Register</a> | ';

		$pr_page_content .= '<a href="' . wp_lostpassword_url() . '">Lost your password?</a>
		</p>
		';
		global $post;
		$post->comment_status = 'closed';
	endif;
	return apply_filters( 'pr_page_content', $pr_page_content );
}

// Perform the restriction and if restricted replace the page content with a login form
function pr_page_restrict ( $pr_page_content ) {
	global $post;
	$pr_check = pr_get_opt('method') == 'all';
	$pr_check = $pr_check || (
		( is_array(pr_get_opt('pages')) || is_array(pr_get_opt('posts')) ) 
		&& ( count(pr_get_opt('pages')) + count(pr_get_opt('posts')) > 0 )
	);
	$pr_check = $pr_check || ( pr_get_opt('pr_restrict_home') && is_home() );
	if ( !is_user_logged_in() && $pr_check ) :
		// current post is in either page / post restriction array
		$is_restricted = ( in_array($post->ID, pr_get_opt('pages')) || in_array($post->ID, pr_get_opt('posts')) ) && pr_get_opt ( 'method' ) != 'none';
		// content is restricted OR everything is restricted
		if ( (is_single() || is_page()) && ($is_restricted || pr_get_opt('method') == 'all') ):
			$pr_page_content = pr_get_page_content();
			$pr_page_content = '<div class="page-restrict-output">' . $pr_page_content . '</div>';
		// home page, archives, search
		elseif ( ( in_array($post->ID, pr_get_opt('pages')) || in_array($post->ID, pr_get_opt('posts')) || pr_get_opt('method') == 'all' ) 
				&& ( is_archive() || is_search() || is_home() ) 
		) :
            $pr_page_content = '<p>' . pr_get_opt ( 'message' )  . '</p>';
            $pr_page_content = str_replace('login', '<a href="' . wp_login_url( $_SERVER['REQUEST_URI'] )  . '">login</a>', $pr_page_content);
			$pr_page_content = '<div class="page-restrict-output">' . apply_filters( 'pr_message_content', $pr_page_content ) . '</div>';
		endif;
	endif;
	return $pr_page_content;
}

function pr_comment_restrict ( $pr_comment_array ) {
	global $post;
    if ( !is_user_logged_in()  && is_array ( pr_get_opt ( 'pages' ) ) ) :
		$is_restricted = ( in_array($post->ID, pr_get_opt('pages')) || in_array($post->ID, pr_get_opt('posts')) ) && pr_get_opt ( 'method' ) != 'none';
       	if ( $is_restricted || pr_get_opt('method') == 'all' ):
			$pr_comment_array = array();
		endif;
	endif;
	return $pr_comment_array;
}

// Add Actions
add_action( 'send_headers' , 'pr_no_cache_headers' );

// Add Filters
add_filter ( 'the_content' , 'pr_page_restrict' , 50 );
add_filter ( 'the_excerpt' , 'pr_page_restrict' , 50 );
add_filter ( 'comments_array' , 'pr_comment_restrict' , 50 );

add_action( 'wp_login_failed', 'pr_login_failed', 50, 1 );
function pr_login_failed( $username = null )
{
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

	if ( !empty( $referrer ) && !strstr( $referrer, 'wp-login.php' ) && !strstr( $referrer, 'wp-admin' ) )
	{
		$params = parse_url( $referrer );		
		$redirect = $params['scheme'] . '://' . $params['host'] . $params['path'];

		$query = false;
		
		if ( isset( $params['query'] ) )
			parse_str( $params['query'], $query );

		$query['login'] = 'failed';
		
		if ( is_wp_error( $username ) )
			$query['wp-error'] = $username->get_error_message();

		if ( isset( $username->user_login ) )
			$query['pr-user-login'] = $username->user_login;

		if ( count( $query ) > 0 )
		{
			$redirect .= '?' . http_build_query( $query );
		}

		wp_redirect( $redirect, 303 );
		die;
	}
}

if ( sizeof( $_POST ) )
	add_filter( 'authenticate', 'pr_authenticate', 50, 3 );

function pr_authenticate( $error, $user, $pass )
{
	if ( !empty( $user ) )
		$error->user_login = $_POST['log'];

	if ( is_wp_error( $error ) )
		do_action( 'wp_login_failed', $error );

	return $error;
}