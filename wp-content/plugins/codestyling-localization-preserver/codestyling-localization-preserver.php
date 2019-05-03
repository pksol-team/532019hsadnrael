<?php
/*
Plugin Name: CodeStyling Localization Preserver
Plugin URI: http://www.sowmedia.nl
Description: Keep your own translations safe, even when updating your plugins/themes. This add-on for CodeStyling Localization (CSL) preserves translations made with CSL by forcing it to merge, store, copy and read translations to and from the WP_LANG_DIR (wp-content/languages).
Version: 1.0.6
Author: Sowmedia.nl (Steve Lock)
License: GPLv2 or later

License:
==============================================================================
Copyright 2014 Sowmedia.nl (e-mail: info@sowmedia.nl)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
==============================================================================


Requirements:
==============================================================================
This plugin requires WordPress >= 3.5 and the CodeStyling Localisation plugin
==============================================================================

*/

if ( ! class_exists( 'CodeStyling_Localization_Preserver' ) ) {

	class CodeStyling_Localization_Preserver {

		private $debug = false;

		function __construct() {

			add_filter('load_textdomain', array($this, 'clp_load_textdomain_from_core'), 10, 2); // force WP to look for translations in WP_LANG_DIR

			// register_activation_hook(__FILE__, array($this, 'clp_merge_all_translations')); // Merge all translation files on plugin activation
																							   // This function is implemented for advanced users only; uncomment above line if you'd
																							   // like to try it. This only works on servers with enough performance.

		    //insert hooks
		    if( is_admin() ) {
				
				add_action('wp_ajax_csp_po_launch_editor', array($this, 'clp_merge_specific_translation'), 0 ); //merge translations before editing po file
                add_action('wp_ajax_csp_po_generate_mo_file', array($this, 'clp_perserve_translations')); //backup mo files after CSL generates mo file.
            }

			add_action('init', array($this, 'clp_load_textdomain') ); //load our own translations

		}


		/**
		 * Force WP to load translations from WP_LANG_DIR first
		 *
		 * @since 0.0.1
		 */
		function clp_load_textdomain_from_core($domain, $mofile) {

			if (strpos($mofile, WP_LANG_DIR) === false) { //only apply filter if we're not already loading from WP_LANG_DIR (important: this also prevents loops)
				$mo_plugin_file = trailingslashit(WP_LANG_DIR).trailingslashit('plugins').$domain.'-'.get_locale().'.mo';
				if(file_exists($mo_plugin_file)) load_textdomain($domain, $mo_plugin_file);
				else {
					$mo_theme_file = trailingslashit(WP_LANG_DIR).trailingslashit('themes').$domain.'-'.get_locale().'.mo';
					if(file_exists($mo_theme_file)) load_textdomain($domain, $mo_theme_file);
				}
    		}
		}


		/**
		 * Load translations
		 *
		 * @since 0.0.1
		 */
		function clp_load_textdomain() {

			load_plugin_textdomain('codestyling-localization-preserver', FALSE, trailingslashit(trailingslashit(dirname(plugin_basename(__FILE__))).'languages'));

		}

		
		/**
		 * Perform security checks
		 *
		 * @since 0.0.1
		 */
		function clp_check_security() {

			if($this->debug) header('Status: 404 Not Found'); //forces CSL to output ajax errors
			if($this->debug) header('HTTP/1.1 404 Not Found');

			csp_po_check_security();
		}


		/**
		 * Merge new translation into original translation
		 *
		 * @since 0.0.1
		 */
		function clp_merge_translation_files($originalTranslation, $newTranslation, $textdomain) {

			$this->clp_check_security();

			// check if translations exist and differ
			if ((file_exists($originalTranslation) && file_exists($newTranslation)) && (hash_file('md5', $newTranslation) != hash_file('md5', $originalTranslation))) {	

				require_once(trailingslashit(WP_CONTENT_DIR) . 'plugins/codestyling-localization/includes/locale-definitions.php');
				require_once(trailingslashit(WP_CONTENT_DIR) . 'plugins/codestyling-localization/includes/class-filesystem-translationfile.php');
			
				$translationFile = new CspFileSystem_TranslationFile();

				if (substr($originalTranslation,-2) == 'mo' && substr($newTranslation,-2) == 'mo') {

					//load original
					$translationFile->read_mofile($originalTranslation, $csp_l10n_plurals, false, $textdomain);

					//merge and write
					$translationFile->read_mofile($newTranslation);
					$translationFile->write_mofile($originalTranslation, $textdomain);

				} elseif (substr($originalTranslation,-2) == 'po' && substr($newTranslation,-2) == 'po') { 

					//load original
					$translationFile->read_pofile($originalTranslation);

					//merge and write
					$translationFile->read_pofile($newTranslation);
					$translationFile->write_pofile($originalTranslation, true, $textdomain);
				}
			}
		}


		/**
		 * Walk through plugins and merge their translations with those in WP_LANG_DIR (wp-content/languages) if they both exist but differ
		 *
		 * @since 0.0.1
		 */
		function clp_merge_specific_translation() {

			$this->clp_check_security();
			
			$po_file = $_POST['file'];
			$mo_file = substr($po_file,0,-2)."mo";
			$type = $_POST['type'];
			$textdomain = $_POST['textdomain'];

			$po_basename = basename($po_file);
			$mo_basename = basename($mo_file);

			//only plugins and themes for now
			if($type != 'plugins' && $type != 'themes') return;

			//add textdomain if basename is only a locale
			if (strpos($po_basename, $textdomain) === false) $po_basename = $textdomain."-".$po_basename;
			if (strpos($mo_basename, $textdomain) === false) $mo_basename = $textdomain."-".$mo_basename;

			// Paths to the real translation files, in wp-content/languages/xxx
			$mo_core_path = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $mo_basename;
			$po_core_path = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $po_basename;

			// Paths to translations directory (wp-content/plugins/) 
			$mo_path = trailingslashit($_POST['basepath']) . $mo_file;
			$po_path = trailingslashit($_POST['basepath']) . $po_file;

			// merge
			$this->clp_merge_translation_files($mo_path, $mo_core_path, $_POST['textdomain']);
			$this->clp_merge_translation_files($po_path, $po_core_path, $_POST['textdomain']);

		}


		/**
		 * Walk through plugins/themes and copy (not merge!) translations to WP_LANG_DIR (wp-content/languages) if the're not there yet
		 *
		 * @since 0.0.1
		 */
		function clp_merge_all_translations() {
            
			//check if CSL is alive and active
			if(!function_exists('csp_po_collect_by_type')) $this->clp_throw_csl_missing_error();

			$this->clp_check_security();

			//get all available PO files using CSL's own detector
			$translations = csp_po_collect_by_type(null);

			foreach($translations as $translation) {

				foreach($translation['languages'] as $locale => $language) {

					// Get vars
					$textdomain = $translation['textdomain']['identifier'];
					$translationBase = trailingslashit($translation['base_path']).$translation['base_file'];
					$type = $translation['type'];

					if($type != 'plugins' && $type != 'themes') continue; //only plugins and themes for now

					if(!is_dir((trailingslashit(WP_LANG_DIR)) . trailingslashit($type))) mkdir((trailingslashit(WP_LANG_DIR)) . trailingslashit($type));

					if (is_array($language['mo'])) {
						$mofile = $translationBase. $locale . '.mo'; 
						$core_mofile = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $textdomain . '-' . $locale . '.mo';
						if(!file_exists($core_mofile)) copy($mofile, $core_mofile);
					}

					if (is_array($language['po'])) {
						$pofile = $translationBase. $locale . '.po';
						$core_pofile = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $textdomain . '-' . $locale . '.po';
						if(!file_exists($core_pofile)) copy($pofile, $core_pofile);
					}
				}
			}
		}


		/**
		 * Inform user if CSL hasn't been found
		 *
		 * @since 0.0.1
		 */
		function clp_throw_csl_missing_error() {

			deactivate_plugins( plugin_basename( __FILE__ ) );  //Deactivate ourself

			$message = __( 'Sorry! In order to use the CodeStyling Localization Preserver plugin, you need to install and activate CodeStyling Localization first.', 'codestyling-localization-preserver', 'codestyling-localization-preserver' );

			wp_die( $message, __( 'CodeStyling Localization Preserver', 'codestyling-localization-preserver' ), array( 'back_link' => true ) );

		}



		/**
		 * Write translations back to WP_LANG_DIR (wp-content/languages) when CSL generates mo-file
		 *
		 * @since 0.0.1
		 */
		function clp_perserve_translations() {

			$this->clp_check_security();
					
			$pofile = (string) $_POST['pofile'];
			$textdomain = (string) $_POST['textdomain'];
			$type = (string) $_POST['type'];

			$this->write_translation_to_core($pofile, $textdomain, $type);

		}

		/**
		 * Write translations back to WP_LANG_DIR (wp-content/languages)
		 *
		 * @since 0.0.1
		 */
		function write_translation_to_core($pofile, $textdomain, $type) {

			$this->clp_check_security();

			require_once(trailingslashit(WP_CONTENT_DIR) . 'plugins/codestyling-localization/includes/class-filesystem-translationfile.php');

			$translationFile = new CspFileSystem_TranslationFile();
			if (!$translationFile->read_pofile($pofile)) return false;
			
			//lets detected, what we are about to be writing:
			$mofile = substr($pofile,0,-2).'mo';

			$wp_dir = str_replace("\\","/",WP_LANG_DIR);
			$pl_dir = str_replace("\\","/",WP_PLUGIN_DIR);
			$plm_dir = str_replace("\\","/",WPMU_PLUGIN_DIR);
			$parts = pathinfo($mofile);
			
			preg_match("/([a-z][a-z]_[A-Z][A-Z])$/", $parts['filename'], $h);
			$fallback_textdomain = str_replace('-' . $h[1] . '.mo', "", $parts["basename"]);
			$mo = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $fallback_textdomain . '-' . $h[1] . '.mo';
			$po = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $fallback_textdomain . '-' . $h[1] . '.po';
			
			//dirname|basename|extension
			if (preg_match("|^".$wp_dir."|", $mofile)) return false; //we are WordPress itself, not useful for us
				
			elseif (preg_match("|^".$pl_dir."|", $mofile) || preg_match("|^".$plm_dir."|", $mofile)) { //we are a normal or wpmu plugin
			
				if ((strpos($parts['basename'], $textdomain) === false) && strlen($textdomain) > 0 && ($textdomain != 'default')) {
					preg_match("/([a-z][a-z]_[A-Z][A-Z]\.mo)$/", $parts['basename'], $h);
					if (!empty($textdomain)) {
						$mo = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $textdomain . '-' . $h[1] . '.mo';
						$po = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $textdomain . '-' . $h[1] . '.po';
					}
				}

			} else { 

				//it's a theme
				$mo = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $textdomain . '-' . $h[1] . '.mo';
				$po = trailingslashit( WP_LANG_DIR ) . trailingslashit($type) . $textdomain . '-' . $h[1] . '.po';

			}
			
			$textdomain = strlen($textdomain) < 1 ? $fallback_textdomain : $textdomain;
			if ($translationFile->is_illegal_empty_mofile($textdomain)) return false;
			
			$success = $translationFile->write_mofile($mo,$textdomain);
			$translationFile->write_pofile($po, true, $textdomain);
			
			if(!$success){
				print __("CodeStyling Localization Preserver plugin could not write to", 'codestyling-localization-preserver', 'codestyling-localization-preserver') . ": " . trailingslashit( WP_LANG_DIR ) . trailingslashit($type);
				header('Status: 404 Not Found'); 
				header('HTTP/1.1 404 Not Found');
				die();
			}
		}
	}
}

/* Initialize the class */
$CodeStyling_Localization_Preserver = new CodeStyling_Localization_Preserver();