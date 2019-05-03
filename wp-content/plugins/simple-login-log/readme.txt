=== Plugin Name ===
Contributors: maxchirkov
Donate link: http://www.ibsteam.net/donate
Tags: login, log, users
Requires at least: 3.0
Tested up to: 4.7.5
Stable tag: 1.1.3

This plugin keeps a log of WordPress user logins. Offers user and date filtering, and export features.

== Description ==

Simple log of user logins. Tracks user name, time of login, IP address and browser user agent.

[Demo Video](http://screenr.com/kfEs "Demo Video")

**Features include:**

1. ability to filter by user name, successful/failed logins, month and year;
2. export into CSV file;
3. log auto-truncation;
4. option to record failed login attempts.

**Translations:**

- Persian [fa_IR] by [MohammadHadi Nasiri](http://taktaweb.ir/)
- German [de_DE] by Philipp Moore
- Russian [ru_RU]
- Ukrainian [ua_UA]
- Chinese [zh_CN] by [Mihuwa](http://www.mihuwa.com/)
- French [fr_FR] by [Mehdi Hamida](http://www.lo-geek.fr/)

* Author: Max Chirkov
* Author URI: [http://simplerealtytheme.com/](http://simplerealtytheme.com/ "Real Estate Themes & Plugins for WordPress")
* Copyright: Released under GNU GENERAL PUBLIC LICENSE

== Installation ==

1. Install and activate like any other basic plugin.
2. If you wish to set log truncation or opt-in to record failed login attempts, go to Settings => General. Scroll down to Simple Login Log.
3. To view login log, go to Users => Login Log. You can export the log to CSV file form the same page.

Screen Options are available at the top of the Login Log page. Click on the *Screen Options* tab to expand the options section. You'll be able to change the number of results per page as well as hide/display table columns.

== Screenshots ==

1. Simple Login Log Settings.
2. Login Log Management Screen.

== Changelog ==

**Version 1.1.3**
- Minor fix.

**Version 1.1.2**

- Fixed: logins were not recorded due to (multiple) agent roles assigned to the same user a longer than 30 characters.
- Fixed: sql injection vulnerability.

**Version 1.1.0**

- WP 4.6 compatibility update

**Version 1.1.0**

- Fixed: some SQL queries were requesting all records, which caused some sites to run out of memory.
- Numerous minor fixes and improvements.
- Added Chinese and French translations.
- New Feature: Delete All link - deletes all log records at once.

**Version 1.0**

- WP 3.8 compatibility update.

**Version 0.9.6**

- Bug Fix: records weren't truncated in multi-site setup.
- Added German, Russian and Ukrainian translations.

**Version 0.9.5**

- Fixed: filtered log results weren't getting exported correctly.
- Improvement: log real IP per [Alexander's recommendation](http://wordpress.org/support/topic/log-real-ip).
- Added Persian translation.

**Version 0.9.4 - Highly Advised!**

- Numerous vulnerability fixes!

**Version 0.9.3**

- Improvement: search by partial user name as well as partial IP address per [Commeuneimage's recommendation](http://wordpress.org/support/topic/plugin-simple-login-log-small-enhancement-suggested-on-search-feature).
- Updated POT file.
- Added uninstall.php to all plugin's data from the database on plugin deletion.

**Version 0.9.2**

- Daily cron job with log truncation didn't work.

**Version 0.9**

- Changed access to the log for users with capability to "list_users".

**Version 0.8**

- Bug Fix: Columns' checkboxes weren't showing in Screen Options in WP 3.3.

**Version 0.7**

- Added user role filter via link. Filter will apply only to newly registered logins, because user roles weren't recorded in versions prior to v.0.6.

**Version 0.6**

- Added new column - User Role.
- Minor PHP warning notices cleanup.

**Version 0.5**

- Bug fix: in_array() warning for hidden columns not returning an array.

**Version 0.4**

- Added option to export filtered log results.
- Added Views filters All/Successful/Failed logins.
- Added Screen Options: number of items per page, output visibility options for table columns.
- Added *sll-output-data* filter, which allows to alter data output in each column of the table.
- Added support for localization.

**Version 0.3**

- Added support for third-party login plugins.
- Added option to log Failed Login Attempts.

== Other Notes ==

= Filters =

** Log Output Within the Table **

*sll-output-data* - filters table row array where array keys are column names and values is the output
For example, we can use this filter to link IP addresses to a geo-location service:
`
<?php
add_filter( 'sll-output-data', 'link_location_by_ip' );
function link_location_by_ip($item){

	//$item is a single row for columns with their values

	$item['ip'] = sprintf('<a target="_blank"  href="http://infosniper.net/index.php?ip_address=%1$s&map_source=3&two_maps=1&overview_map=1&lang=1&map_type=1&zoom_level=11">%1$s</a>', $item['ip']);
	return $item;
}
?>
`

= Translation =

If you would like to contribute, the POT file is available in the *languages* folder. Translation file name convention is *sll-{locale}.mo*, where {locale} is the locale of your language. Fore example, Russian file name would be *sll-ru_RU.po*.