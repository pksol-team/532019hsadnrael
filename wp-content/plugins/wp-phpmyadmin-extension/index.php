<?php
/*
 * Plugin Name:		WP phpMyAdmin
 * Description:		The famous database browser & manager (for MySQL & MariaDB) - use it inside WordPress Dashboard without an extra hassle.
 * Text Domain:		wp-phpmyadmin-extension
 * Domain Path:		/languages
 * Version:			2.3
 * WordPress URI:	https://wordpress.org/plugins/wp-phpmyadmin-extension/
 * Plugin URI:		https://puvox.software/wordpress/
 * Contributors: 	puvoxsoftware,tazotodua
 * Author:			Puvox.software
 * Author URI:		https://puvox.software/
 * Donate Link:		https://paypal.me/puvox
 * License:			GPL-3.0
 * License URI:		https://www.gnu.org/licenses/gpl-3.0.html
 
 * @copyright:		2019 Puvox.software
*/

namespace WpPhpMyAdminExtension
{
  if (!defined('ABSPATH')) exit;
  $lib_final=dirname(__DIR__)."/".($name='default_library_puvox.php');
  if( file_exists($lib_start=__DIR__."/$name") && !defined("_puvox_machine_") ) { rename($lib_start, $lib_final); } require_once($lib_final);


  class PluginClass
  {
	use \default_methods__Puvox_Software;

	public function declare_settings()
	{
		$this->initial_static_options = array
		(
			'has_pro_version'	=>0, 
			'show_opts'			=>true, 
			'show_rating_message'=>true, 
			'display_tabs'		=>false,
			'required_role'		=>'install_plugins', 
			'default_managed'	=>'network',			// network, singlesite
			'menu_button_level'	=>'mainmenu', 
			"menu_icon"			=>$this->plugin_URL.'/assets/media/menu_icon.png" style="width:30px;',   
			'menu_button_name'	=>'WP-phpMyAdmin' 
		);

		$this->initial_user_options	= array
		(	
			'randomCookieName'		=> "pma_".$this->randomString(16), 
			'randomCookieValue'		=> "pma_".$this->randomString(16), 
			'RandomFolderSuffix'	=> "_".$this->randomString(23), 
			'manual_pma_login_url'	=> '',
			'require_ip'			=> true,
			'hide_phma_errors'		=> false,
			'strip_slashes'			=> false,
			'use_https'				=> true,
			'is_localhost'			=> $this->is_localhost
		); 
	}
 
	//by default, this will is disabled per requirements. Users can enable only for themselves.
	private $pma_installed_using_ajax = false;
	private $pma_installed_using_download = false;
	
	public function __construct_my()
	{
		add_action('init', array($this, 'setup_files'), 2 ); 
	}

	// ============================================================================================================== //
	// ============================================================================================================== //

	public function setup_files(){
		
		$this->pma_name_randomed	= "phpMyAdmin". $this->opts['RandomFolderSuffix']; 
		
		$this->lib_relpath			= '/lib';    
		$this->lib_absPath			= __DIR__ . $this->lib_relpath;       
		//$this->pma_relpath			= $this->lib_relpath . '/phpMyAdmin';     
		//$this->pma_relpath_randomed	= $this->lib_relpath . '/'. $this->pma_name_randomed;
		$this->pma_relpath			= $this->lib_relpath . '/'. $this->pma_name_randomed;
		$this->pma_abspath			= __DIR__ . $this->pma_relpath;     
		 
		$this->pma_mainpage_relpath_from_plugin	= $this->pma_relpath.'/index.php' ;
		$this->pma_mainpage_relpath_from_plugin_URL = $this->pma_relpath.'/index.php' ;
		$this->pma_mainpage_url		= $this->plugin_URL	. substr($this->pma_mainpage_relpath_from_plugin,1);
		$this->pma_mainpage_path	= $this->pma_abspath	. '/index.php';
		
		$this->pma_zipPath			= $this->lib_absPath	. '/phpMyAdmin.zip'; 
		$this->pma_sessionfile		= $this->pma_abspath	. '/_session_temp.php'; 
		$this->pma_sessionAllowfile	= $this->pma_abspath	. '/_session_temp_allow.php'; 
		$this->path_to_def_config	= __DIR__ . '/default_config.php';
		$this->path_to_pma_config	= $this->pma_abspath	. '/config.inc.php';
		$this->path_to_pma_common_2	= __DIR__ . '/default_common_inc_code.php';
		$this->path_to_pma_common	= $this->pma_abspath	. '/libraries/common.inc.php';
		//deleted targets //details: https://goo.gl/tCWdEv
		$this->pma_delete_dirs		= array('/vendor/tecnickcom/tcpdf', '/locale', '/theme/original', '/doc', '/setup', '/examples', '/install', '/js/vendor/openlayers' );	
		$this->pma_create_files		= array('/vendor/tecnickcom/tcpdf/tcpdf.php');	
		$this->conflict_file_1		= $this->pma_abspath . '/vendor/phpmyadmin/motranslator/src/functions.php';

		$this->if_redirect_to_pma();
	}
		
	private function getPMA_FolderName(){
		$x = glob($this->lib_absPath.'/phpMyAdmin*',  GLOB_ONLYDIR);  return (!empty($x) ? $x[0] : "");
	}

	private function download_install_pma()
	{
		if( !is_dir($this->pma_abspath) )
		{
			$this->locker	= fopen( WP_CONTENT_DIR."/lock.txt","w+"); //__DIR__.'/readme.txt';
			if (flock($this->locker,LOCK_EX) && !is_dir($this->pma_abspath) )  //check again after LOCK 
			{
				$this->try_increase_exec_time(120);
				//rename files
				$dir =  $this->getPMA_FolderName();
				if(!empty($dir) && !rename($dir, $this->pma_abspath )) {
					$this->rmdir_recursive($dir);  exit(__('Failure: can\'t rename <code>'.$dir.'</code> to <code>'.$this->pma_abspath.'</code>. Either do it manually from FTP, or try completely re-install the plugin.', 'wp-phpmyadmin-extension') );
					usleep(500000);
				}
				
				// delete extra directories
				foreach($this->pma_delete_dirs as $eachDir){
					$fullPath = $this->pma_abspath.'/'.$eachDir.'/';
					if( is_dir($fullPath) ){
						$this->rmdir_recursive($fullPath);
					}
				}
				// create extra directories & files
				foreach($this->pma_create_files as $eachFile){
					$this->file_put_contents($this->pma_abspath.'/'.$eachFile,""); 
				}


				// create config
				if(is_admin()){
					if( $this->is_localhost != $this->opts['is_localhost']){
						$force =  true;
						$this->opts['is_localhost']=$this->is_localhost;
						$this->update_opts();
					}
					// MY NOTE: config.inc.php should alwyas be in pma folder 
					// include_once( dirname( dirname(dirname( dirname(__DIR__) ) ) ).'/wp_phpmyadmin_config.inc.php' );
					if(!file_exists($this->path_to_pma_config) || $force){
						$content = file_get_contents($this->path_to_def_config);
						$content = str_replace( '___HOSTADR___', 			'\''.DB_HOST.'\'', 							$content);
						$content = str_replace( '___ALLOWNOPASS___',		($this->is_localhost ? "true" : "false"),	$content);
						$content = str_replace( '___BLOWFISHSECRET___',	'\''. addslashes($this->create_blowfish_secret()).'\'',	$content);
						$content = str_replace( '___LANG___',				'\''.$this->static_settings['lang'].'\'',				$content);  
						$content = str_replace( '___RESTRICTORCOOKIENAME___',	'\''.$this->opts['randomCookieName'].'\'',	$content);  
						$content = str_replace( '___RESTRICTORCOOKIEVALUE___',	'\''.$this->opts['randomCookieValue'].'\'',	$content);  
						$content = str_replace( '___DBARRAY___',			'array(\''.DB_NAME.'\')',	$content);  
						$content = str_replace( '___PmaAbsoluteUri___',		"'".str_replace( $this->domain,'', $this->OneSlash($this->plugin_URL . $this->pma_relpath))."/'",	$content);
						//$content = str_replace( '___RELATIVEPATHTOFOLDER___',	'\'/plugins/wp-phpmyadmin/'.$this->pma_dirname.'\'',	$content);  
						file_put_contents($this->path_to_pma_config, $content);
					}
				}
				
				//add content into common.inc
				$common_inc_content_new  = $this->str_replace_first( '<?php', '<?php'.file_get_contents($this->path_to_pma_common_2) , file_get_contents($this->path_to_pma_common) );
				// other fixes to make global scope correctly
				$common_inc_content_new = preg_replace( '/\$GLOBALS\[\'PMA_Config\']\-\>enableBc\(\)\;/',  '$0'.("\r\n\r\n". 'if(defined("ABSPATH")) {'. "\r\n".'    if(empty($cfg) && !empty($GLOBALS[\'cfg\'])) $cfg = $GLOBALS[\'cfg\'];'. "\r\n". '	if(empty($default_server) && !empty($GLOBALS[\'default_server\'])) $default_server = $GLOBALS[\'default_server\']; '. "\r\n}"),   $common_inc_content_new );
				$common_inc_content_new = preg_replace('/\$auth_plugin\-\>authSetUser\(\)\;/', 'if(defined("ABSPATH")) $GLOBALS["cfg"]=$cfg; ' ."\r\n\r\n".'$0', $common_inc_content_new );
							// in url:showLoginForm:_-->   <input name="token"== $_SESSION[' PMA_token '];
				$common_inc_content_new = preg_replace('/\$token_mismatch \= true\;/', '$temp_approve_file = dirname(__DIR__)."/_session_temp_allow.php";'."\r\n".'if(file_exists($temp_approve_file)){ unlink($temp_approve_file);	$_POST["set_session"] = session_id(); $_POST["token"] = $_SESSION[" PMA_token "];  }' ."\r\n\r\n".'$0', $common_inc_content_new );
				file_put_contents( $this->path_to_pma_common, $common_inc_content_new);
				
				// rename conflicting function named __(    //old method: https://pastebin.com/raw/v652Ef1A
				$file = $this->pma_abspath .'/vendor/phpmyadmin/motranslator/src/functions.php';
				file_put_contents( $file,   str_replace('function __(', 'function __RENAMED(', file_get_contents($file) ) );
				

				// create htaccess to hide it
				$create_redirect = 
					function(){
						file_put_contents( $this->lib_absPath .'/.htaccess',
							'<IfModule mod_rewrite.c>'."\r\n".
							'Options -Indexes'."\r\n".
							'</IfModule>'
							//'RewriteEngine on'."\r\n".
							//'RewriteRule !^'.$this->pma_name_randomed.'($|/) http://example.com/good_bye [L,R=301]'."\r\n".
						);
						file_put_contents( $this->lib_absPath .'/index.html', " ");
					};
				$create_redirect();

				flock($this->locker,LOCK_UN);
			} //end locker
		}
	}
	

	
	//from PMA
	// same as:  public function generateRandom($length)

	private function create_blowfish_secret(){
		$blowfishSecret = '';
		$random_func = (function_exists('openssl_random_pseudo_bytes')) ? 'openssl_random_pseudo_bytes' : 'phpseclib\\Crypt\\Random::string';
		while (strlen($blowfishSecret) < 32) {
			$byte = $random_func(1);
			// We want only ASCII chars
			if (ord($byte) > 32 && ord($byte) < 127) {
				$blowfishSecret .= $byte;
			}
		}
		return $blowfishSecret;
	}



	// ======
	public function create_session_var($force=false){ 
		$new_content = '<?php $sess_vars = array("time"=>'.time().', "name"=>"wp_pma_'.$this->randomString(14).'",  "value"=>"wp_pma_'.$this->randomString(23).'",  "require_ip"=>'.($this->opts['require_ip']? 'true':'false').', "ip"=>"'.$this->ip.'", "strip_slashes"=>'. ($this->opts['strip_slashes']? 'true':'false') .');'; 

		//
		if($force || !file_exists($this->pma_sessionfile)){
			file_put_contents($this->pma_sessionfile, $new_content);
		}
		else{
			include($this->pma_sessionfile);
			//don't reset if login happens in last 30 seconds again.
			if( $sess_vars["time"] + 30 < time() ){
				file_put_contents($this->pma_sessionfile, $new_content);
			}
		} 
	} 
	
	public function if_redirect_to_pma()
	{ 
		if( isset($_GET['goto_wp_phpmyadmin']) ){ 
			if(current_user_can('install_plugins') && current_user_can('manage_options'))
			{ 
				if(isset($_GET['hosting_pma'])){
					$m_url	= $this->opts['manual_pma_login_url'];
					if( stripos( $m_url , '/index.php') === false){
						$m_url .=  ! $this->endsWith($m_url, '/')  ?   '/index.php' : 'index.php';
					}
					$chosen_server_url = $m_url;
				}
				else{
					$this->disable_cache(true);
					// when chosen installed pma-url, then use protection (which we cant use with hosting-url)
					// p.s. SESSIONS DOESNT WORK for some reasons, probably WP resets then in 'shutdown' and start... SO WE USE COOKIES... 
					$this->create_session_var();
					include($this->pma_sessionfile);
					file_put_contents($this->pma_sessionAllowfile, $this->ip);

					if(empty($_COOKIE[$sess_vars["name"]]) || $_COOKIE[$sess_vars["name"]] != $sess_vars["value"] ){
						$this->set_cookie( $sess_vars["name"], $sess_vars["value"], 3*60*60);
						$this->php_redirect();
					}
					$chosen_server_url = $this->pma_mainpage_url;
				}

				if(isset($_GET['automatic_login']))
				{
					$this->opts['ssl_error_shown']=1; $this->update_opts();
					if ( $this->opts['use_https'] ) $chosen_server_url = str_replace('http://','https://', $chosen_server_url);
					?>
					<form style="display:none;" method="post" action="<?php echo $chosen_server_url;?>"  name="login_form" >
						<input type="hidden" name="pma_username" value="<?php echo DB_USER;?>" />
						<input type="hidden" name="pma_password" value="<?php echo DB_PASSWORD;?>" />
						<input type="hidden" name="server" value="1">
						<input type="hidden" name="target" value="index.php">
						<input type="hidden" name="set_session" value="will_be_replaced" />
						<input type="hidden" name="token" value="will_be_replaced">
						<input value="Go" type="submit" id="input_go" />
					</form>
					<script>
					document.forms["login_form"].submit();
					</script>
					<?php
				}
				else {
					$this->php_redirect($chosen_server_url);
				}
				?>
				<?php
				exit;
			} 
		}
		return;
	}



	public function opts_page_output()
	{  
		$this->settings_page_part("start");
		?> 
		<style>
		p.submit { text-align:center; }
		.settingsTitle{display:none;}
		.myplugin {padding:10px;}
		.myplugin #install_pma:disabled{opacity:0.3;}
		.myplugin .enterb{font-size:1.5em; padding:10px; } 
		#mainsubmit-button{display:none; background:green;}
		.myplugin .sample_disabled{opacity:0.3;}
		body .ui-tooltip{background:pink;}
		td:nth-child(3) { width: 280px; }
		.myplugin .comingsoon{ opacity:0.4; }
		.warning_ssl_img{ text-align:center; }
		.warning_ssl_img img{ filter: sepia(0.6) contrast(1.1); }
		.installed_logins .manual_login {display:none;}
		.red_warning {color:orange;}
		</style>

		<?php
		//moved here, because no longer ajax
		if (!$this->pma_installed_using_ajax) $this->download_install_pma();
		?>

		<?php if ($this->active_tab=="Options") 
		{ 
			//if form updated
			if(isset($_POST["_wpnonce"]) && check_admin_referer("nonce_".$this->plugin_slug) ) 
			{ 
				$this->opts['manual_pma_login_url']	= sanitize_text_field($_POST[ $this->plugin_slug ]['manual_pma_login_url']);
				
				$this->opts['use_https']		= isset($_POST[ $this->plugin_slug ]['use_https']); 
				$this->opts['strip_slashes']	= isset($_POST[ $this->plugin_slug ]['strip_slashes']); 
				$this->opts['require_ip']		= isset($_POST[ $this->plugin_slug ]['require_ip']); 
				$this->opts['hide_phma_errors']	= isset($_POST[ $this->plugin_slug ]['hide_phma_errors']); 
				$this->update_opts(); 
				
				//reflect changes immediately
				$this->replace_in_file($this->path_to_pma_config, '/\$cfg\[\WSendErrorReports\W\]\s+=\s+\W(.*?)\W/', '$cfg["SendErrorReports"] = \''. ($this->opts['hide_phma_errors'] ? 'never':'ask') .'\'');
				$this->create_session_var(true);
			}


			$this->ssl_notice_msg = __("This is one-time message! <br/><br/> Seems that your site doesn\\'t use HTTPS. Automatic login (at this moment) works only for HTTPS. To use this feature, then you should bypass the SSL warning on the next page, like shown on this screenshot: ", "wp-phpmyadmin-extension") ."<br/><div class=\\'warning_ssl_img\\'><img src=\\'".$this->plugin_URL."/assets/media/example_warning.png\\' /></div>".__("If the next page doesn\\'t work at all, then uncheck the HTTPS checkbox on this page.", "wp-phpmyadmin-extension");

			$url_to_open =  trailingslashit(admin_url()).'?rand='.rand(1,99999999).'&goto_wp_phpmyadmin=1';
			?> 
		
			<form class="mainForm" method="post" action="">
			
			<table class="form-table">
				<tr class="installed_logins">
					<td><h3><?php _e("phpMyAdmin in your wp", 'wp-phpmyadmin-extension');?></h3></td>
					<td>
					<?php _e('', 'wp-phpmyadmin-extension');?>
					<?php if ($this->pma_installed_using_ajax) { ?> <button id="install_pma" <?php if (is_dir($this->pma_abspath)) echo "disabled";?>><?php echo ( !is_dir($this->pma_abspath) ? __("install",'wp-phpmyadmin-extension') :  __("installed",'wp-phpmyadmin-extension') );?></button> <?php } ?>
					</td>
					<td>

<!--
					<p class="submit manual_login"><a class="<?php if (!is_dir($this->pma_abspath)) echo "sample_disabled";?> button button-primary type_auto enterb enter_manual" target="_blank"  href="<?php echo $url_to_open;?>&manual_login"><?php _e("Login Manually", 'wp-phpmyadmin-extension');?></a></p>

					<p class="submit automatic_login"><a class="<?php if (!is_dir($this->pma_abspath)) echo "sample_disabled";?> button button-primary type_auto enterb enter_automatic" target="_blank" href="<?php echo $url_to_open;?>&automatic_login"  id="installed_automatic" ><?php _e("Login Automatically", 'wp-phpmyadmin-extension');?></a></p>
-->
					<p class="submit automatic_login"><a class="<?php if (!is_dir($this->pma_abspath)) echo "sample_disabled";?> button button-primary type_auto enterb enter_automatic" target="_blank" href="<?php echo $url_to_open;?>&automatic_login" id="installed_automatic" onclick="show_ssl_wanring1(event, this);"><?php _e("Enter phpMyAdmin", 'wp-phpmyadmin-extension');?></a></p>

					</td>
				</tr> 

				<tr class="hostinged_logins">
					<?php $pma_url = $this->domain.'/phpmyadmin/'; ?>
					<td><h3><?php _e("phpMyAdmin on hosting:", 'wp-phpmyadmin-extension');?></h3></td>
					<td>
					<?php _e('If above method doesn\'t work for you, at first report us. In the meanwhile, you can use an alternative - some hostings might already have phpMyAdmin setup for customers. If so, just paste the phpMyAdmin login page url here:', 'wp-phpmyadmin-extension');?>
					<input type="text" class="regular-text" id="manual_pma_login_url" data-onchange-save="true"  data-onchange-hide=".type_manual" name="<?php echo $this->plugin_slug;?>[manual_pma_login_url]" value="<?php echo $this->opts['manual_pma_login_url'];?>" placeholder="" />  
					<br/><?php _e('( That url might be <code><a href="'.$pma_url.'" target="_blank">'.$pma_url.'</a></code> or <code>https://xyz123.yourhosting.com/phpmyadmin/</code>. You can find out that url in your hosting\'s Control-Panel &gt; <b>phpMyAdmin</b> and you will be redirected to "login" url. Then paste that base url here.)', 'wp-phpmyadmin-extension');?>
					</td>
					<td>
					<p class="submit"><a class="button button-primary type_manual enterb enter_manual" target="_blank"  href="<?php if(empty($this->opts['manual_pma_login_url'])) echo "javascript:alert('url is empty');void(0);"; else if(stripos($this->opts['manual_pma_login_url'], '//')===false) echo "javascript:alert('incorrect url format');void(0);"; else echo $url_to_open.'&hosting_pma&manual_login';?>"><?php _e("Login Manually", 'wp-phpmyadmin-extension');?></a></p>
					<p class="submit"><a class="button button-primary type_manual enterb enter_automatic comingsoon" target="_blank"  href="<?php if(empty($this->opts['manual_pma_login_url'])) echo "javascript:alert('url is empty');void(0);"; else if(stripos($this->opts['manual_pma_login_url'], '//')===false) echo "javascript:alert('incorrect url format');void(0);"; else echo $url_to_open.'&hosting_pma&automatic_url';?>" id="hosting_automatic" ><?php _e("Login Automatically", 'wp-phpmyadmin-extension');?></a></p>
					</td>
				</tr>
				
				<tr>
					<td></td>
					<td></td>
					<td>
						<b><p class="description"><?php _e("Credentials:", 'wp-phpmyadmin-extension');?></p></b> <?php _e('DB Username', 'wp-phpmyadmin-extension');?>: <input type="text" value="<?php echo DB_USER;?>" class="noinput"/>
						<br/><?php _e('DB Password', 'wp-phpmyadmin-extension');?>: <b><?php _e('Get from wp-config.php', 'wp-phpmyadmin-extension');?></b>
						<br/>
					</td>
				</tr>
				<?php if ( !is_ssl() ) { ?>
				<tr>
					<td class="xred_warning"><?php _e("Use HTTPS (so, auto-login will work)", 'wp-phpmyadmin-extension');?> <?php echo $this->question_mark($this->ssl_notice_msg, 2);?></td> 
					<td><input type="checkbox" name="<?php echo $this->plugin_slug;?>[use_https]" <?php checked($this->opts['use_https']);?> data-onchange-save="true" /></td>
					<td></td>
				</tr>
				<?php } ?>
				<tr>
					<td><?php _e("Restrict access only to current IP (<code>$this->ip</code>) to login into PMA <br/>(in rare cases, if you have continiously changing dynamic IP address, then you will need to uncheck IP restriction)", 'wp-phpmyadmin-extension');?></td> 
					<td><input type="checkbox" name="<?php echo $this->plugin_slug;?>[require_ip]" <?php checked($this->opts['require_ip']);?>/> </td>
					<td></td>
				</tr>
				<tr>
					<td><?php _e('Hide errors in PMA <br/>(if you face error popup-boxes in phpMyAdmin frequently, you can hide them)', 'wp-phpmyadmin-extension');?></td> 
					<td><input type="checkbox" name="<?php echo $this->plugin_slug;?>[hide_phma_errors]" <?php checked($this->opts['hide_phma_errors']);?>/> </td>
					<td></td>
				</tr>
				<tr>
					<td><?php _e('Strip slashes in PMA <br/>(if you see that when you update a textfield in phpMyAdmin, and extra backslash <code>\\</code> is added in front of <code>\\</code> or <code>\'</code> or <code>"</code> characters, then check this) :', 'wp-phpmyadmin-extension');?></td> 
					<td><input type="checkbox" name="<?php echo $this->plugin_slug;?>[strip_slashes]" <?php checked($this->opts['strip_slashes']);?>/> </td>
					<td></td>
				</tr>
			</table>
			
			<?php submit_button(  false, 'button-primary save', '', true,  $attrib= array( 'id' => 'save_button') ); ?> 
			<?php wp_nonce_field( "nonce_".$this->plugin_slug);  ?>
			</form>

			<script> 
			// warning tooltips
			shown_error_once = 0;
			function show_ssl_wanring1(e, elem){
				lastEl = jQuery(elem); //jQuery(this) 
				if(location.protocol != 'https:')
				{
					if (shown_error_once || <?php echo ( array_key_exists('ssl_error_shown', $this->opts) ? "true" : "false")?>) return;
					shown_error_once = 1;
					jQuery('<div><?php echo $this->ssl_notice_msg;?></div>').dialog({
						modal:true,
						width:700,
						close: function(){ document.getElementById(lastEl.attr("id")).click(); }
					});
					e.preventDefault();
				}
			}



			jQuery(function(){ 
				jQuery(".comingsoon").attr("title", "Coming soon").on("click", function(e){e.preventDefault();}); 
				
				jQuery(".myplugin").tooltip({ show: { effect: "blind", duration: 800 } }); 
				
				/*
				jQuery("#install_pma").on("click", function(e){
					var targetEl= this;
					e.preventDefault();
					ttLibrary.backend_call
					(
						{
							'act': 'install_pma' 
						},
						function(response)
						{
							ttLibrary.message(response);
						}
					);
					window.setTimeout( function(){ ttLibrary.message("<?php _e('Please don\'t refresh the page untill it refreshes automatically  (might need up to 30 seconds). After that, you can open it.', 'wp-phpmyadmin-extension');?>"); }, 2000);
					window.setTimeout( function(){ jQuery('.ttDialog').dialog('close'); }, 7000);
				});
				*/

			});
			
			//submitFirstLogin(document.getElementById("wp_phpmyadmin_frame"));
			function submitFirstLogin(iframeElement){ 
				iframeElement.onload = function() { 			
				};	
			}
			</script>

		<?php 
		} 

		$this->settings_page_part("end");
	} 
	

	/*
	
	public function backend_call($act)
	{
		if ($this->pma_installed_using_ajax)
		{
			if($act=="install_pma")
			{
				$this->download_install_pma();
				exit(__('Installation complete.<script>location=location;</script>','wp-phpmyadmin-extension') );
			}
		}
	}

	REMOVED:
	//======================== Download & install ========================
	if($this->pma_installed_using_ajax)
	{
		// download latest pma
		if ($this->pma_installed_using_download) if(!file_exists($this->pma_zipPath)) $this->getPmaZip(); 		// getPmaZip -----> removed: https://pastebin.com/raw/r8jLN1P7
			
		// remove if previous unpack was partial.
		$dir =  $this->getPMA_FolderName();  
		if( !empty($dir) && is_dir($dir) ) { 
			if( !$this->rmdir_recursive($dir) )
				exit(__("Failure: can't remove the old $dir folder, click OK to re-try (If you will see this message again, do it manually from FTP).", 'wp-phpmyadmin-extension'));
		}

		// unzip
		$this->unzip($this->pma_zipPath, $this->lib_absPath);
		usleep(500000);
	}
	//remove file
	if ($this->pma_installed_using_download) unlink($this->pma_zipPath);
	// =================================================
	*/



	



  } // End Of Class

  $GLOBALS[__NAMESPACE__] = new PluginClass();

} // End Of NameSpace
?>