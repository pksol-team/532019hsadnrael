=== CodeStyling Localization Preserver ===
Author: Sowmedia.nl (Steve Lock)
Contributors: sowmedia, stevelock
Donate link: http://www.sowmedia.nl/donate
Tags: preserve, translation, update, theme, plugin, codestyling, localization
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: 1.0.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This add-on for CodeStyling Localization preserves translations by forcing it to merge, store, copy and read translations to and from the WP_LANG_DIR.

== Description ==

Keep your own translations safe, even when updating your plugins/themes. This add-on for CodeStyling Localization (CSL) preserves translations made with CSL by forcing it to merge, store, copy and read translations to and from the WP_LANG_DIR (wp-content/languages).

== Installation ==

1. Make sure you have [CodeStyling Localization](http://wordpress.org/plugins/codestyling-localization/) installed prior to installing this plugin
1. Unzip and upload `codestyling-localization-preserver` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it! Your translations are being preserved as you translate from now on.

== Frequently Asked Questions ==

= Where are the settings for this plugin? =

There aren't any, neat huh? This plugin works on-the-go, as long as you have CodeStyling Localization enabled.

= Are translations I adapted prior to installing this plugin, being preserved as well?  =

Not by default. Since this plugin can't keep track of what happened in the past, it cannot determine which translations should be preserved, and which are just the original. However, there are two ways to perserve the translations you've made in the past:
1. Look up the translations you've adapted in the past in CodeStyling Localization. For each translation click 'Edit' and then 'Generate MO-file'. Your translation is being perserved from now on.
2. A second option is to just preserve all (!) translation files of all your plugins and themes. This is still an experimental solution, which only works if your server has enough performance capacity. It's adviced for advanced users only. To preserve all translations, edit the main plugin file (codestyling-localization-preserver.php) and uncomment line 47. Save the file, deactivate CodeStyling Localization Preserver and then re-active the plugin. Next, wait while all translation files are being preserved. If the plugin activates without error, perservation has been succesful.

== Screenshots ==

There aren't any.

== Changelog ==
= 1.0.6 =
* Fixed error in catching textdomain names

= 1.0.5 =
* Removed old updater

= 1.0.4 =
* Fixed error in title description

= 1.0.3 =
* Commented (experimental) hook added for users who'd like to enable (experimental)

= 1.0.2 =
* Put activation hook outside admin, so plugin can be installed and activated remotely

= 1.0.1 =
* Fix when subdir doesn't exist yet in WP_LANG_DIR

= 1.0 =
* First release

== Upgrade Notice ==

= 1.0.3 =
Experimental feature added. No immediate action is required.