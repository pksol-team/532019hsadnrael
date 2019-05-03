<?php
					// If uninstall not called from WordPress, then exit
					if ( ! defined( "WP_UNINSTALL_PLUGIN" ) ) {
						exit;
					}

					$lib = dirname(__DIR__)."/default_library_puvox.php";
					if(file_exists($lib)){
						//@unlink($lib);
					}