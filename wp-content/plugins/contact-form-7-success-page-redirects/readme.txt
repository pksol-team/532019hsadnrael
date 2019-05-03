=== Contact Form 7 - Success Page Redirects ===
Contributors: rnevius
Tags: contact form 7, cf7, contact forms 7, contact form, redirect, forms, form redirect, form, success pages, thank you pages, contact form 7 add-on, cf7 redirect, cf7 success, contact form 7 redirect, contact form 7 success
Requires at least: 3.8.2
Tested up to: 4.2
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

An add-on for Contact Form 7 that provides a straightforward method to redirect visitors to success pages or thank you pages.


== Description ==

An add-on for Contact Form 7 (CF7) that provides a straightforward method to redirect visitors to success pages or thank you pages, if their messages are successfully delivered. If no message is sent, or if there is an error with the form, the user will not be redirected.

**NOTE:** This plugin requires Contact Form 7 version 3.9 or later. 


== Installation ==

1. Unzip the downloaded plugin archive.
2. Upload the inner 'contact-form-7-success-page-redirects' directory to the '/wp-content/plugins/' directory on your web server.
3. Activate the plugin through the 'Plugins' menu in WordPress.

Please note that [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) (version 3.9 or later) must be installed and activated in order for this plugin to be functional.


== Frequently Asked Questions ==

= Why doesn't this plugin do *insert feature here*? =

If you have a feature suggestion, I'd love to hear about it. Feel free to leave a message on the "Support" tab of the plugin page, and I'll follow up as soon as possible.

= My forms are no longer submitting, with this plugin installed. Why? =

Please make sure that you have updated to the latest version of Contact Form 7. Success Page Redirects only supports versions 3.9.0 or higher. If everything is up to date and you're still experiencing problems, please post a message on the [Support Page](https://wordpress.org/support/plugin/contact-form-7-success-page-redirects).

= Why are my forms no longer using Ajax, when I install this plugin? =

In order to ensure that all forms are submitted properly, and that users can be redirected to success pages from some of your forms, I have had to disable Contact Form 7's JavaScript for all forms. This means that you won't be able to use Ajax to submit forms. In future updates, I hope to change this functionality, so that Ajax is only disabled for the forms that have a redirect page defined. Please note that disabling CF7's JavaScript does not affect your forms' ability to send messages properly.


== Screenshots ==

1. The plugin will add a "Redirect to:" dropdown that contains all of your existing pages as options. This is set on each of the "Edit Contact Form" pages.


== Changelog ==

= 1.2.0 =
* Adds support for Contact Form 7 version 4.2+

= 1.1.6 =
* Fixes a bug where duplicating a contact form from the form list view wouldn't copy over the redirect field. 

= 1.1.5 =
* Fixes a bug where duplicating a contact form wouldn't copy over the redirect field from the original form edit page. 

= 1.1.4 =
* Upgrade process for verifying that Contact Form 7 is installed and up to date. 

= 1.1.3 =
* CF7 id property is no longer accessible. Switched to the id() method, so that form changes can be properly saved in the admin.

= 1.1.2 =
* Provides clearer admin notices regarding necessary plugin dependencies 

= 1.1.1 =
* Fixes an issue that prevented some forms from submitting and redirecting
* Disables JavaScript for WPCF7 completely

= 1.1.0 =
* Initial version


== Upgrade Notice ==

= 1.1.1 =
This version fixes an issue that prevented some forms from submitting. Please upgrade to ensure all CF7 messages are sent.
