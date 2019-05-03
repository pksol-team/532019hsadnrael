<?php
/**
 * Downloads Foofle-Fonts locally and generates the @font-face CSS for them.
 * The main reasons for this is the GDPR & performance.
 *
 * @package Avada
 * @since 5.5.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Downloads Foofle-Fonts locally and generates the @font-face CSS for them.
 *
 * @since 5.5.2
 */
class Fusion_GFonts_Downloader {

	/**
	 * The font-family.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $family;

	/**
	 * The path where files for this font are stored.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $folder_path;

	/**
	 * The URL where files for this font are stored.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var string
	 */
	private $folder_url;

	/**
	 * The font-family arguments from the google-fonts API.
	 *
	 * @access private
	 * @since 5.5.2
	 * @var array
	 */
	private $font;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $family The font-family we're dealing with.
	 */
	public function __construct( $family ) {
		$this->family      = $family;
		$this->folder_path = $this->get_root_path() . '/' . sanitize_key( $this->family );
		$this->folder_url  = $this->get_root_url() . '/' . sanitize_key( $this->family );
		$this->font        = $this->get_font_family();
	}

	/**
	 * Gets the @font-face CSS for all variants this font-family contains.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param array $variants The variants we want to get.
	 * @return string
	 */
	public function get_fontface_css( $variants = array() ) {
		if ( ! $this->font ) {
			return;
		}
		$font_face = '';

		// If $variants is empty then use all variants available.
		if ( empty( $variants ) ) {
			$variants = $this->font['variants'];
		}

		// Download files.
		$this->download_font_family( $variants );

		// Create the @font-face CSS.
		foreach ( $variants as $variant ) {
			$font_face .= $this->get_variant_fontface_css( $variant );
		}
		return $font_face;
	}

	/**
	 * Get the @font-face CSS for a specific variant in this font-family.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $variant The variant.
	 * @return string
	 */
	public function get_variant_fontface_css( $variant ) {
		$font_face = "@font-face{font-family:'{$this->family}';";

		// Get the font-style.
		$font_style = ( false !== strpos( $variant, 'italic' ) ) ? 'italic' : 'normal';
		$font_face .= "font-style:{$font_style};";

		// Get the font-weight.
		$font_weight = '400';
		$font_weight = str_replace( 'italic', '', $variant );
		$font_weight = ( ! $font_weight || 'regular' === $font_weight ) ? '400' : $font_weight;
		$font_face  .= "font-weight:{$font_weight};";

		// Get the font-names.
		$font_name_0 = $this->get_local_font_name( $variant, false );
		$font_name_1 = $this->get_local_font_name( $variant, true );
		$font_face  .= "src:local('{$font_name_0}'),";
		if ( $font_name_0 !== $font_name_1 ) {
			$font_face .= "local('{$font_name_1}'),";
		}

		// Get the font-url.
		$font_url = $this->get_variant_local_url( $variant );

		// Get the font-format.
		$font_format = ( strpos( $font_url, '.woff2' ) ) ? 'woff2' : 'truetype';
		$font_format = ( strpos( $font_url, '.woff' ) && ! strpos( $font_url, '.woff2' ) ) ? 'woff' : $font_format;
		$font_face  .= "url({$font_url}) format('{$font_format}');}";

		return $font_face;
	}

	/**
	 * Gets the local URL for a variant.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $variant The variant.
	 * @return string         The URL.
	 */
	public function get_variant_local_url( $variant ) {
		$local_urls = $this->get_font_files_urls_local();

		if ( empty( $local_urls ) ) {
			return;
		}

		// Return the specific variant if we can find it.
		if ( isset( $local_urls[ $variant ] ) ) {
			return $local_urls[ $variant ];
		}

		// Return regular if the one we want could not be found.
		if ( isset( $local_urls['regular'] ) ) {
			return $local_urls['regular'];
		}

		// Return the first available if all else failed.
		$vals = array_values( $local_urls );
		return $vals[0];
	}

	/**
	 * Get the name of the font-family.
	 * This is used by @font-face in case the user already has the font downloaded locally.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param string $variant The variant.
	 * @param bool   $compact Whether we want the compact formatting or not.
	 * @return string
	 */
	public function get_local_font_name( $variant, $compact = false ) {
		$variant_names = array(
			'100'       => 'Thin',
			'100i'      => 'Thin Italic',
			'100italic' => 'Thin Italic',
			'200'       => 'Extra-Light',
			'200i'      => 'Extra-Light Italic',
			'200italic' => 'Extra-Light Italic',
			'300'       => 'Light',
			'300i'      => 'Light Italic',
			'300italic' => 'Light Italic',
			'400'       => 'Regular',
			'regular'   => 'Regular',
			'400i'      => 'Regular Italic',
			'italic'    => 'Italic',
			'400italic' => 'Regular Italic',
			'500'       => 'Medium',
			'500i'      => 'Medium Italic',
			'500italic' => 'Medium Italic',
			'600'       => 'Semi-Bold',
			'600i'      => 'Semi-Bold Italic',
			'600italic' => 'Semi-Bold Italic',
			'700'       => 'Bold',
			'700i'      => 'Bold Italic',
			'700italic' => 'Bold Italic',
			'800'       => 'Extra-Bold',
			'800i'      => 'Extra-Bold Italic',
			'800italic' => 'Extra-Bold Italic',
			'900'       => 'Black',
			'900i'      => 'Black Italic',
			'900italic' => 'Black Italic',
		);

		$variant = (string) $variant;
		if ( $compact ) {
			if ( isset( $variant_names[ $variant ] ) ) {
				return str_replace( array( ' ', '-' ), '', $this->family ) . '-' . str_replace( array( ' ', '-' ), '', $variant_names[ $variant ] );
			}
			return str_replace( array( ' ', '-' ), '', $this->family );
		}

		if ( isset( $variant_names[ $variant ] ) ) {
			return $this->family . ' ' . $variant_names[ $variant ];
		}
		return $this->family;
	}

	/**
	 * Get an array of font-files.
	 * Only contains the filenames.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files() {
		$files       = array();
		$remote_urls = $this->get_font_files_urls_remote();
		foreach ( $remote_urls as $key => $url ) {
			$files[ $key ] = $this->get_filename_from_url( $url );
		}
		return $files;
	}

	/**
	 * Get the remote URLs for the font-files.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files_urls_remote() {
		if ( isset( $this->font['files'] ) ) {
			return $this->font['files'];
		}
		return array();
	}

	/**
	 * Get an array of local file URLs.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files_urls_local() {
		$urls  = array();
		$files = $this->get_font_files();
		foreach ( $files as $key => $file ) {
			$urls[ $key ] = $this->folder_url . '/' . $file;
		}
		return $urls;
	}

	/**
	 * Get an array of local file paths.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_files_paths() {
		$paths = array();
		$files = $this->get_font_files();
		foreach ( $files as $key => $file ) {
			$paths[ $key ] = $this->folder_path . '/' . $file;
		}
		return $paths;
	}

	/**
	 * Returns the $wp_filesystem global.
	 *
	 * @access private
	 * @since 5.5.2
	 * @return WP_Filesystem
	 */
	private function filesystem() {
		return Fusion_Helper::init_filesystem();
	}

	/**
	 * Downloads a font-file and saves it locally.
	 *
	 * @access private
	 * @since 5.5.2
	 * @param string $url The URL of the file we want to get.
	 * @return bool
	 */
	private function download_font_file( $url ) {
		$contents = $this->get_remote_url_contents( $url );
		$path     = $this->folder_path . '/' . $this->get_filename_from_url( $url );

		// If the folder doesn't exist, create it.
		if ( ! file_exists( $this->folder_path ) ) {
			$this->filesystem()->mkdir( $this->folder_path, FS_CHMOD_DIR );
		}
		// If the file exists no reason to do anything.
		if ( file_exists( $path ) ) {
			return true;
		}

		// Write file.
		return $this->filesystem()->put_contents( $path, $contents, FS_CHMOD_FILE );
	}

	/**
	 * Get a font-family from the array of google-fonts.
	 *
	 * @access public
	 * @since 5.5.2
	 * @return array
	 */
	public function get_font_family() {

		// Get the fonts array.
		$fonts = $this->get_fonts();

		// Make sure we've got fonts.
		if ( isset( $fonts['items'] ) ) {
			$fonts = $fonts['items'];

			// Loop array of fonts.
			foreach ( $fonts as $font ) {

				// When we find the font, return it.
				if ( isset( $font['family'] ) && $this->family === $font['family'] ) {
					return $font;
				}
			}
		}
	}

	/**
	 * Gets the filename by breaking-down the URL parts.
	 *
	 * @access private
	 * @since 5.5.2
	 * @param string $url The URL.
	 * @return string     The filename.
	 */
	private function get_filename_from_url( $url ) {
		$url_parts   = explode( '/', $url );
		$parts_count = count( $url_parts );
		if ( 1 < $parts_count ) {
			return $url_parts[ count( $url_parts ) - 1 ];
		}
		return $url;
	}

	/**
	 * Get the font defined in the google-fonts API.
	 *
	 * @access private
	 * @since 5.5.2
	 * @return array
	 */
	private function get_fonts() {
		$path = wp_normalize_path( FUSION_LIBRARY_PATH . '/inc/redux/custom-fields/typography/googlefonts-array.php' );
		return include $path;
	}

	/**
	 * Gets the root fonts folder path.
	 * Other paths are built based on this.
	 *
	 * @since 1.5
	 * @access public
	 * @return string
	 */
	public function get_root_path() {
		// Get the upload directory for this site.
		$upload_dir = wp_upload_dir();
		$path       = untrailingslashit( wp_normalize_path( $upload_dir['basedir'] ) ) . '/fusion-gfonts';

		// If the folder doesn't exist, create it.
		if ( ! file_exists( $path ) ) {
			$this->filesystem()->mkdir( $path, FS_CHMOD_DIR );
		}

		// Return the path.
		return apply_filters( 'fusion_gfonts_root_path', $path );
	}

	/**
	 * Gets the root folder url.
	 * Other urls are built based on this.
	 *
	 * @since 1.5
	 * @access public
	 * @return string
	 */
	public function get_root_url() {

		// Get the upload directory for this site.
		$upload_dir = wp_upload_dir();

		// The URL.
		$url = trailingslashit( $upload_dir['baseurl'] );
		// Take care of domain mapping.
		// When using domain mapping we have to make sure that the URL to the file
		// does not include the original domain but instead the mapped domain.
		if ( defined( 'DOMAIN_MAPPING' ) && DOMAIN_MAPPING ) {
			if ( function_exists( 'domain_mapping_siteurl' ) && function_exists( 'get_original_url' ) ) {
				$mapped_domain   = domain_mapping_siteurl( false );
				$original_domain = get_original_url( 'siteurl' );
				$url             = str_replace( $original_domain, $mapped_domain, $url );
			}
		}
		return apply_filters( 'fusion_gfonts_root_url', untrailingslashit( esc_url_raw( $url ) ) . '/fusion-gfonts' );
	}

	/**
	 * Download font-family files.
	 *
	 * @access public
	 * @since 5.5.2
	 * @param array $variants An array of variants to download. Leave empty to download all.
	 * @return void
	 */
	public function download_font_family( $variants = array() ) {
		if ( isset( $this->font['files'] ) ) {
			if ( empty( $variants ) ) {
				$variants = $this->font['variants'];
			}
			foreach ( $this->font['files'] as $variant => $file ) {
				if ( in_array( $variant, $variants ) ) {
					$this->download_font_file( $file );
				}
			}
		}
	}

	/**
	 * Gets the remote URL contents.
	 *
	 * @access private
	 * @since 5.5.2
	 * @param string $url The URL we want to get.
	 * @return string     The contents of the remote URL.
	 */
	public function get_remote_url_contents( $url ) {
		$transient_name = 'fusion_gfonts' . md5( $url );
		$html           = get_transient( $transient_name );
		if ( false === $html ) {
			$response = wp_remote_get( $url );
			if ( is_wp_error( $response ) ) {
				return array();
			}
			$html = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $html ) ) {
				return;
			}
			set_transient( $transient_name, $html, 24 * HOUR_IN_SECONDS );
		}
		return $html;
	}
}
