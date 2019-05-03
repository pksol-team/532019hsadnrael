<?php
/**
 * Created by DigitalGuard.it
 *
 * Copyright (c) 2018 Copyright Holder All Rights Reserved.
 */

if (!class_exists('DigitalGuard_IT_Licensing_2_2')) {

class DigitalGuard_IT_Licensing_2_2 {
	protected $api_key = '';
  protected $slug;
  protected $product_name;
	protected $optionName = '';
  protected $license_data = null;
  protected $domain = '';

	/**
	 * Initialize the class.
	 */
	public function __construct($api_key, $slug = '', $product_name = '', $show_notice = true) {
	   $this->api_key = $api_key;
     $this->slug = $slug;
     $this->product_name = $product_name;
     $this->optionName = $slug.'_DG_licensing';

     $domain = get_bloginfo( 'url' );
     $domain = preg_replace('#^https?://#', '', rtrim($domain,'/'));
     $this->domain = preg_replace('#^(http(s)?://)?w{3}\.(\w+\.\w+)#', '$3', $domain);

	   $this->license_data = json_decode(get_option( $this->optionName, null));

     if($show_notice)
		   add_action( 'admin_notices', array($this,'admin_notices') );
	}

	public function admin_notices(){
		if( $this->is_licensed() ){
			return;
		}
		$message = "{$this->product_name} is unlicensed";

		if( $this->has_error() )
			$message .= ': ' . $this->get_error();

		echo '<div class="error">';
		echo "<p>$message</p>";
		echo '</div>';
	}

	public function validate_license($license = '') {

		$server_url = 'http://digitalguard.it/api/license/activate';

		try{
			$response = wp_remote_post( $server_url, array( 'timeout' => 45, 'sslverify' => false, 'body' => array('key' => $this->api_key, 'slug' => $this->slug, 'domain' => $this->domain, 'license' => $license) ) );

			if ( is_wp_error( $response ) )
				throw new Exception($response->get_error_message());

			$this->license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if( isset($this->license_data->valid) ){
				if( $this->license_data->valid ) {
   		     update_option( $this->optionName, json_encode($this->license_data));
           set_transient( $this->optionName.'_la'.'st'.'ch'.'ec'.'k', $license, 604800 );
           if(!empty($this->license_data->error_message))
              $this->set_error($this->license_data->error_message);
           else
			        $this->set_error(NULL);
				}
        else {
          $this->set_error($this->license_data->error_message);
        }
			}
			else {
					throw new Exception("Unknown Error");
				}
		} catch (Exception $e){
			$this->set_error($e->getMessage());
		}
	}

  public function revoke_license($license = '') {

		$server_url = 'http://digitalguard.it/api/license/revoke';

		try{
			$response = wp_remote_post( $server_url, array( 'timeout' => 45, 'sslverify' => false, 'body' => array('key' => $this->api_key, 'slug' => $this->slug, 'domain' => $this->domain, 'license' => $license) ) );

			if ( is_wp_error( $response ) )
				throw new Exception($result->get_error_message());

			$this->revoke_data = json_decode( wp_remote_retrieve_body( $response ) );

			if( isset($this->revoke_data->valid) ){
				if( $this->revoke_data->valid ) {
			     $this->set_error(NULL);
           $this->clear_license();
				}
        else {
          $this->set_error($this->revoke_data->error_message);
        }
			}
			else {
					throw new Exception("Unknown Error");
				}
		} catch (Exception $e){
			$this->set_error($e->getMessage());
		}
	}

	private function set_error($error) {
		if($error === NULL)
			delete_option( $this->optionName.'_e'.'r'.'ro'.'r');
		else
			update_option( $this->optionName.'_e'.'rr'.'o'.'r', $error );
	}

	function has_error() {
		$error = get_option($this->optionName.'_e'.'rr'.'or');

		return $error === false ? false : true;
	}

	function get_error() {
		return get_option($this->optionName.'_e'.'rr'.'or');
	}

	function clear_error() {
	   delete_option( $this->optionName.'_e'.'r'.'ro'.'r');
	}

	public function has_license_message() {
    if(is_null($this->license_data)) return false;

	 if(empty($this->license_data->license_message)) return false;

   return true;
  }

	public function get_license_message() {
    if(is_null($this->license_data)) return '';

	   return $this->license_data->license_message;
  }

	public function is_licensed() {
    if(is_null($this->license_data)) return false;

	   return $this->license_data->valid == true && $this->license_data->status == 'COMPLETED';
   }

 	public function get_license_key() {
     if(is_null($this->license_data)) return '';

 	   return $this->license_data->license_key;
  }

 	public function get_license_data() {
     if(is_null($this->license_data)) return '';

 	   return $this->license_data;
    }

   public function clear_license() {
      delete_option( $this->optionName );
      delete_transient($this->optionName.'_las'.'t'.'c'.'he'.'ck');
      $this->license_data = null;

      return true;
   }

   function has_feature($check){
      if(is_null($this->license_data)) return;

      if(!$this->is_licensed()) return;

      $features = explode(',', $this->license_data->features);

			foreach( $features as $feature ){
				if( $check === $feature )
					return true;
			}
			return false;
	 }

   public function update() {
      if(is_null($this->license_data)) return;

      if(false === get_transient($this->optionName.'_las'.'t'.'c'.'he'.'ck')) {
         $this->validate_license($this->license_data->license_key);
      }
   }
}
}
?>
