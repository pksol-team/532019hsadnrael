<?php
/*
* Modified from the Tiny Compress Images - WordPress plugin.
*/

if ( ! defined( '\Tinify\VERSION' ) ) {
	/* Load vendored client if it is not yet loaded. */
	require_once dirname( __FILE__ ) . '/Tinify/Tinify/Exception.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify/ResultMeta.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify/Result.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify/Source.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify/Client.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify-Exception.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify-Compress.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify-Image.php';
	require_once dirname( __FILE__ ) . '/Tinify/Tinify-Image-Size.php';
}


if (!class_exists('Tiny_Compress_Client')) {

class Tiny_Compress_Client extends Tiny_Compress {
	private $last_error_code = 0;
	private $last_message = '';
	private $proxy;

	protected function __construct( $api_key, $after_compress_callback ) {
		parent::__construct( $after_compress_callback );

		$this->proxy = new WP_HTTP_Proxy();

		\Tinify\setAppIdentifier( self::identifier() );
		\Tinify\setKey( $api_key );
	}

	public function can_create_key() {
		return true;
	}

	public function get_compression_count() {
		return \Tinify\getCompressionCount();
	}

	public function get_key() {
		return \Tinify\getKey();
	}

	public function limit_reached() {
		return 429 == $this->last_error_code;
	}

	protected function validate() {
		try {
			$this->last_error_code = 0;
			$this->set_request_options( \Tinify\Tinify::getClient() );

			\Tinify\Tinify::getClient()->request( 'post', '/shrink' );
			return true;

		} catch (\Tinify\Exception $err) {
			$this->last_error_code = $err->status;

			if ( 429 == $err->status || 400 == $err->status ) {
				return true;
			}

			throw new Tiny_Exception(
				$err->getMessage(),
				get_class( $err ),
				$err->status
			);
		}
	}

	protected function compress( $input, $resize_opts, $preserve_opts ) {
		try {
			$this->last_error_code = 0;
			$this->set_request_options( \Tinify\Tinify::getClient() );

			$source = \Tinify\fromBuffer( $input );

			if ( $resize_opts ) {
				$source = $source->resize( $resize_opts );
			}

			if ( $preserve_opts ) {
				$source = $source->preserve( $preserve_opts );
			}

			$result = $source->result();

			$meta = array(
				'input' => array(
					'size' => strlen( $input ),
					'type' => $result->mediaType(),
				),
				'output' => array(
					'size' => $result->size(),
					'type' => $result->mediaType(),
					'width' => $result->width(),
					'height' => $result->height(),
					'ratio' => round( $result->size() / strlen( $input ), 4 ),
				),
			);

			$buffer = $result->toBuffer();
			return array( $buffer, $meta );

		} catch (\Tinify\Exception $err) {
			$this->last_error_code = $err->status;

			throw new Tiny_Exception(
				$err->getMessage(),
				get_class( $err ),
				$err->status
			);
		}
	}

	public function create_key( $email, $options ) {
		try {
			$this->last_error_code = 0;
			$this->set_request_options(
				\Tinify\Tinify::getClient( \Tinify\Tinify::ANONYMOUS )
			);

			\Tinify\createKey( $email, $options );
		} catch (\Tinify\Exception $err) {
			$this->last_error_code = $err->status;

			throw new Tiny_Exception(
				$err->getMessage(),
				get_class( $err ),
				$err->status
			);
		}
	}

	private function set_request_options( $client ) {
		/* The client does not let us override cURL properties yet, so we have
           to use a reflection property. */
		$property = new ReflectionProperty( $client, 'options' );
		$property->setAccessible( true );
		$options = $property->getValue( $client );

		if ( $this->proxy->is_enabled() && $this->proxy->send_through_proxy( $url ) ) {
			$options[ CURLOPT_PROXYTYPE ] = CURLPROXY_HTTP;
			$options[ CURLOPT_PROXY ] = $this->proxy->host();
			$options[ CURLOPT_PROXYPORT ] = $this->proxy->port();

			if ( $this->proxy->use_authentication() ) {
				$options[ CURLOPT_PROXYAUTH ] = CURLAUTH_ANY;
				$options[ CURLOPT_PROXYUSERPWD ] = $this->proxy->authentication();
			}
		}
	}

	public static function get_intermediate_size( $size ) {
		/* Inspired by
		http://codex.wordpress.org/Function_Reference/get_intermediate_image_sizes */
		global $_wp_additional_image_sizes;

		$width  = get_option( $size . '_size_w' );
		$height = get_option( $size . '_size_h' );

		/* Note: dimensions might be 0 to indicate no limit. */
		if ( $width || $height ) {
			return array( $width, $height );
		}

		if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
			$sizes = $_wp_additional_image_sizes[ $size ];
			return array(
				isset( $sizes['width'] ) ? $sizes['width'] : null,
				isset( $sizes['height'] ) ? $sizes['height'] : null,
			);
		}
		return array( null, null );
	}
}

class WPOptimiser_Tiny_Image {
	const META_KEY = 'tiny_compress_images';
	const ORIGINAL = 0;

	private $settings;
	private $id;
	private $name;
	private $wp_metadata;
	private $sizes = array();
	private $statistics = array();
  private $compressor = null;

	public function __construct($compressor, $settings, $id, $wp_metadata = null, $tiny_metadata = null ) {
    $this->compressor = $compressor;
		$this->settings = $settings;
		$this->id = $id;
		$this->wp_metadata = $wp_metadata;
		$this->parse_wp_metadata();
		$this->parse_tiny_metadata( $tiny_metadata );
		$this->detect_duplicates();
	}

	private function parse_wp_metadata() {
		if ( ! is_array( $this->wp_metadata ) ) {
			$this->wp_metadata = wp_get_attachment_metadata( $this->id );
		}
		if ( ! is_array( $this->wp_metadata ) ) {
			return;
		}
		$path_info = pathinfo( $this->wp_metadata['file'] );
		$this->name = $path_info['basename'];

		$upload_dir = wp_upload_dir();
		$path_prefix = $upload_dir['basedir'] . '/';
		if ( isset( $path_info['dirname'] ) ) {
			$path_prefix .= $path_info['dirname'] . '/';
		}

		$filename = $path_prefix . $this->name;
		$this->sizes[ self::ORIGINAL ] = new Tiny_Image_Size( $filename );

		if ( isset( $this->wp_metadata['sizes'] ) && is_array( $this->wp_metadata['sizes'] ) ) {
			foreach ( $this->wp_metadata['sizes'] as $size_name => $info ) {
				$this->sizes[ $size_name ] = new Tiny_Image_Size( $path_prefix . $info['file'] );
			}
		}
	}

	private function detect_duplicates() {
		$filenames = array();

		if ( is_array( $this->wp_metadata )
			&& isset( $this->wp_metadata['sizes'] )
			&& is_array( $this->wp_metadata['sizes'] ) ) {

			$active_sizes = $this->get_sizes();
			$active_tinify_sizes = $this->settings['compimg'];

			foreach ( $this->wp_metadata['sizes'] as $size_name => $size ) {
				if ( $this->sizes[ $size_name ]->has_been_compressed()
					&& array_key_exists( $size_name, $active_sizes ) ) {
					$filenames = $this->duplicate_check( $filenames, $size['file'], $size_name );
				}
			}
			foreach ( $this->wp_metadata['sizes'] as $size_name => $size ) {
				if ( in_array( $size_name, $active_tinify_sizes, true ) ) {
					$filenames = $this->duplicate_check( $filenames, $size['file'], $size_name );
				}
			}
			foreach ( $this->wp_metadata['sizes'] as $size_name => $size ) {
				if ( array_key_exists( $size_name, $active_sizes ) ) {
					$filenames = $this->duplicate_check( $filenames, $size['file'], $size_name );
				}
			}
			foreach ( $this->wp_metadata['sizes'] as $size_name => $size ) {
				$filenames = $this->duplicate_check( $filenames, $size['file'], $size_name );
			}
		}
	}

	private function duplicate_check( $filenames, $file, $size_name ) {
		if ( isset( $filenames[ $file ] ) ) {
			if ( $filenames[ $file ] != $size_name ) {
				$this->sizes[ $size_name ]->mark_duplicate( $filenames[ $file ] );
			}
		} else {
			$filenames[ $file ] = $size_name;
		}
		return $filenames;
	}

	private function parse_tiny_metadata( $tiny_metadata ) {
		if ( is_null( $tiny_metadata ) ) {
			$tiny_metadata = get_post_meta( $this->id, self::META_KEY, true );
		}
		if ( $tiny_metadata ) {
			foreach ( $tiny_metadata as $size => $meta ) {
				if ( ! isset( $this->sizes[ $size ] ) ) {
					if ( self::is_retina( $size ) && Tiny_Settings::wr2x_active() ) {
						$retina_path = wr2x_get_retina(
							$this->sizes[ rtrim( $size, '_wr2x' ) ]->filename
						);
						$this->sizes[ $size ] = new Tiny_Image_Size( $retina_path );
					} else {
						$this->sizes[ $size ] = new Tiny_Image_Size();
					}
				}
				$this->sizes[ $size ]->meta = $meta;
			}
		}
	}

	public function get_wp_metadata() {
		return $this->wp_metadata;
	}

	public function file_type_allowed() {
		return in_array( $this->get_mime_type(), array( 'image/jpeg', 'image/png' ) );
	}

	public function get_mime_type() {
		return get_post_mime_type( $this->id );
	}

	public function compress($toProcess) {

		if ( $this->compressor === null || ! $this->file_type_allowed() ) {
			return;
		}

		$success = 0;
		$failed = 0;

		$active_tinify_sizes = $this->settings['compimg'];
		$uncompressed_sizes = $this->filter_image_sizes( 'uncompressed', $active_tinify_sizes );

		foreach ( $uncompressed_sizes as $size_name => $size ) {
			if ( ! $size->is_duplicate() ) {
				$size->add_tiny_meta_start();
				$this->update_tiny_post_meta();
				// $resize = $this->settings->get_resize_options( $size_name );
        $resize = false;
				// $preserve = $this->settings->get_preserve_options( $size_name );
        $preserve = array('copyright', 'creation', 'location');
				try {
					$response = $this->compressor->compress_file( $size->filename, $resize, $preserve );
					$size->add_tiny_meta( $response );
					$success++;
				} catch (Tiny_Exception $e) {
					$size->add_tiny_meta_error( $e );
					$failed++;
				}
				$this->add_wp_metadata( $size_name, $size );
				$this->update_tiny_post_meta();
			}
      if($success >= $toProcess) break;
		}
		return array( 'success' => $success, 'failed' => $failed );
	}

	public function add_wp_metadata( $size_name, $size ) {
		if ( self::is_original( $size_name ) ) {
			if ( isset( $size->meta['output'] ) ) {
				$output = $size->meta['output'];
				if ( isset( $output['width'] ) && isset( $output['height'] ) ) {
					$this->wp_metadata['width'] = $output['width'];
					$this->wp_metadata['height'] = $output['height'];
				}
			}
		}
	}

	public function update_tiny_post_meta() {
		$tiny_metadata = array();
		foreach ( $this->sizes as $size_name => $size ) {
			$tiny_metadata[ $size_name ] = $size->meta;
		}
		update_post_meta( $this->id, self::META_KEY, $tiny_metadata );
	}

	public function filter_image_sizes( $method, $filter_sizes = null ) {
		$selection = array();
		if ( is_null( $filter_sizes ) ) {
			$filter_sizes = array_keys( $this->sizes );
		}

		foreach ( $filter_sizes as $size_name => $active) {
			if ( ! isset( $this->sizes[ $size_name ] ) ) {
				continue;
			}

			$tiny_image_size = $this->sizes[ $size_name ];

			if ( $tiny_image_size->$method() ) {
				$selection[ $size_name ] = $tiny_image_size;
			}
		}
		return $selection;
	}

	public function get_latest_error() {
		$active_tinify_sizes = $this->settings['compimg'];
		$error_message = null;
		$last_timestamp = null;
		foreach ( $this->sizes as $size_name => $size ) {
			if ( in_array( $size_name, $active_tinify_sizes, true ) ) {
				if ( isset( $size->meta['error'] ) && isset( $size->meta['message'] ) ) {
					if ( null === $last_timestamp || $last_timestamp < $size->meta['timestamp'] ) {
						$last_timestamp = $size->meta['timestamp'];
						$error_message = $size->meta['message'];
					}
				}
			}
		}
		return $error_message;
	}

  	public static function is_original( $size ) {
		return self::ORIGINAL === $size;
	}

	public static function is_retina( $size ) {
			return strrpos( $size, 'wr2x' ) === strlen( $size ) - strlen( 'wr2x' );
	}

  public function get_sizes() {
    $sizes = array();
    foreach ( get_intermediate_image_sizes() as $size ) {

    	list($width, $height) = Tiny_Compress_Client::get_intermediate_size( $size );
    	if ( $width || $height ) {

        if(!isset( $this->settings['compimg'][$size]))
          $tinify = false;
        else
          $tinify = $this->settings['compimg'][$size] == 'Y';

    		$sizes[ $size ] = array(
    			'width' => $width,
    			'height' => $height,
    			'tinify' => $tinify,
    		);
    	}
    }

    return $sizes;
  }
}

}
