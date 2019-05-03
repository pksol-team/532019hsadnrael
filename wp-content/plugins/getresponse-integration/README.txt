=== GetResponse for WordPress ===
Contributors: GetResponse
Tags: getresponse, getresponse360, email, newsletter, signup, marketing, plugin, widget, mailing list, subscriber, contacts, subscribe form, woocommerce, buddypress
Requires at least: 3.3.0
Tested up to: 5.0.0
Stable tag: 5.3.7
Requires PHP: 5.6

Take advantage of your visitors' commentators' and customers' interest. Invite them to subscribe and nurture their engagement. Track site visits and pass ecommerce data to **GetResponse** to keep your list growing and ensure you have the contact information and ecommerce data to plan successful marketing campaigns.

== Description ==

#### GetResponse for WordPress

**GetResponse for Wordpress** lets you add site visitors to your contact list, update contact information, and add your GetResponse landing pages to your Wordpress site as a page. Use it to track site visits and pass ecommerce data to **GetResponse**.  The plugin helps grow your contact list and ensures you have the contact information and ecommerce data to plan successful marketing campaigns.

**The plugin features include**:

### List-building options

Encourage people to sign up using all the list-building options and built-in integrations with **Contact Form 7**, **BuddyPress** and **Ninja Forms**:

- add **GetResponse** forms anywhere on your site and to your blog posts,
- add an opt-in box to comments and registration forms to add site visitors to your list,
- add an opt-in box to **Contact Form 7**, **Ninja Forms** and **BuddyPress** forms.

Add GetResponse landing pages on your WordPress site to:

- easily feature new webinar, signup, promotion, download, or sales-focused pages,
- use popup, exit popup, and fixed bar forms on the pages to get more signups,
- run A/B tests to ensure your page design is optimized for conversions,
- easily publish landing pages under your WordPress domain as a subpage,
- add contacts without any 3rd party integrations directly after they sign up from one of your landing pages.

### WooCommerce - add contacts and collect ecommerce data
The built-in integration with **WooCommerce** lets you add customers to your contact list and send ecommerce data to **GetResponse**. Here's what you can do with it:

- grow your list by adding customers at checkout,
- export customer list to **GetResponse** (this option allows you to export custom fields and purchase history),
- collect information about customer spending habits, products, purchases made, and shopping carts.

### Web Event tracking

**GetResponse for WordPress** lets you track visits to your site. Create workflows based on URLs visited. Use advanced search options to identify people who recently visited your site. You can create custom filters and plan your mailings.

Have questions? Head to our [Help Center](https://www.getresponse.com/help/integrations-and-api#subcat-wordpress-integration) for more information about installing the features in the plugin.

== Installation ==
= Method 1. =

1. Log into your **WordPress** admin panel.
2. Go to the **Plugins** menu and click **Add new**.
3. In the search field, type in **GetResponse for Wordpress** and click **Search plugins**.
4. Once you've found it, click **Instal Now**. When the plugin finishes installing, it will appear in the side menu.
5. To activate the plugin, go to **Plugins>>Installed Plugins**, locate **GetResponse for Wordpress**, and click **Activate**.
6. Connect your **GetResponse** account. Here's how to do it:

	* Go to the **Account** tab in the plugin. 
	* Copy and paste the GetResponse API key you can find in **Profile>>Integrations & API>>API** in your **GetResponse** account. (If you are GetResponse Enterprise client, check the checkbox to confirm you have the **Enterprise package, choose your account type, and enter your GetResponse Enterprise domain). 
	* Click **Connect**.

= Method 2. =

1. Download the GetResponse plugin for your WordPress version.
2. Unzip the downloaded file and extract the code to to your /wp-content/plugins/ folder.
3. To complete installation you should activate the module in the plugins section of your administration panel.

== Frequently Asked Questions ==

= Where can I find my API Key? =
You can find it on your GetResponse account in Profile >> Integrations & API >> API.


== Changelog ==

= v5.3.7 =

* Minor fixes

= v5.3.6 =

* Query string support for landing pages

= v5.3.5 =

* Resolved problem with landing pages display on mobile devices

= v5.3.4 =

* Resolved problem with LPS on mobile

= v5.3.3 =

* Resolved validation problem when customer name is empty

= v5.3.1 =

* Change form snippet for contact form 7

= v5.3.0 =

* New feature - Ninja Forms integration

= v5.2.0 =

* New feature - Landing Pages

= v5.1.1 =

- optimization of cron usage

= v5.1.0 =

**Improvements**

- flash messages based on session
- optimization in api calls

**Fixes**

- support configurable products in export
- overriding name during contact update
- create table gr_variants_map

= v5.0.11 =

Fix - validating plugin activation process

= v5.0.10 =

Add required PHP version and remove depricated methods for Woocommerce.

= v5.0.9 =

Fix - create tables when plugin is activated.

= v5.0.8 =

Fix for WebForms and exported images.

= v5.0.7 =

Fix issue with losing email address on checkout without registration

= v5.0.6 =

Fix issue with unicode in url to products images
Fix issue with adding product to GetResponse

= v5.0.5 =

Set proper field names for Contact Form 7 integration.
Create product if not exists during order/cart exports to GetResponse.

= v5.0.4 =

Bug fixes, improvements

= v5.0.3 =

Bug fixes in CURL connection

= v5.0.2 =

Fix for redeclared dd function

== Changelog ==

= v5.0.1 =

Tested up to Wordpress 4.9.4

= v5.0.0 =

Plugin redesign and new features added:
* Enabling opt-in checkbox on forms in BuddyPress and Contact Form 7
* Enabling tracking site visits
* WooCommerce integration
* Purchase history in exports
* Ecommerce data tracking
* Time-based job scheduler

= v3.2.1 =

Fixed compatibility with PHP 5.3

= v3.2.0 =

Disconnect, when invalid API Key

= v3.1.6 =

Fixed widget display for GetResponse360 accounts.

= v3.1.1 =

Added shortcode on post-new page.

= v3.1.0 =

Provided support for PHP 7.

= v3.0.9 =

Tested up to Wordpress 4.6 (beta version).

= v3.0.8 =

Fixed integration with Saphali Woocommerce Russian.

= v3.0.7 =

Reduction of API requests amount (plugin performance improvement).
Fixed payment webhook on checkout page in WooCommerce integration.

= v3.0.6 =

Fixed Notice: Undefined variable

= v3.0.5 =

Fixed SSL CA certificates issue

= v3.0.4 =

Fixed widget issue (Variants not loading properly).

= v3.0.3 =

* Fixed widget issue (plugin not worked correctly in Customizer).

= v3.0.2 =

* Added support for GetResponse 360 accounts
* Views improvements
* Fix for Wordpress <3.9 - added Web Form Shortcode to TinyMCE editor

= v3.0.1 =

* Changed param name WebformId => formId in API v3 forms method
* Added Check webform/form status on list
* Improved performance on widget list
* Fixed js issues to display variants

= v3.0 =

* Integration is based on new REST API v3.0
* Added support for new Web forms (Forms)

= v2.2.2 =

* Fixed js issue with undefined param

= v2.2.1 =

* Fixed sort method (compatible with php < 5.4)

= v2.2 =

* Added Subscribe via BuddyPress Registration page
* Added Subscribe via WooCommerce Registration page
* Added Convert special characters to HTML entities in Webform url
* Added Shortcode attribute "center" - allows to center Webform easily
* Added Shortcode attribute "center_margin" - allows to edit margin (center position)
* Campaign names and Web Forms are now sorted by name using case-insensitive ordering

= v2.1.4 =

* Fixed bug, hook register_post changed to user_register. Thanks to @Wieckowy.

= v2.1.3 =

* Fixed problem with HTTPS blog

= v2.1.2 =

* Fixed problem with displaying web forms with the same names on drop down list

= v2.1.1 =

* Shortcode updated to TinyMCE 4 (fixed broken visual editor)
* Description, Installation and FAQ updated

= v2.1 =

* Added subscribe via the registration page
* Campaign names and Web Forms are now sorted by name
* Added checking if curl extension is set and curl_init method is callable
* Removed typo and deprecated unused params
* Tested up to: 3.9.1

= v2.0.7 =

* Fixed Class name changed in class-gr-widget-webform

= v2.0.6 =

* Class name changed from GetResponse to GetResponseIntegration, in some cases caused error: Cannot redeclare class GetResponse

= v2.0.5 =

* Tested up to: 3.9
* Shortcode updated to TinyMCE 4

= v2.0.4 =

* Changelog updated
* Screenshot updated
* Default ref custom (ref => wordpress) added to API request

= v2.0.3 =

* Fixed typo, Thanks to @Reza
* Tested up to: 3.8.3

= v2.0.2 =

* Fixed curl error notification
* Trigger error deleted

= v2.0.1 =

* Fixed Strict standards: non-static method
* Fixed empty variables
* Fixed empty campaigns notice
* Register actions moved to constructor
* Tested up to: 3.8.2

= v2.0 =

* Integration is based on API Key;
* Web form ID needs no longer to be copied; now web form is selected from the drop-down menu;
* Customer details can be updated at Checkout page;
* Checkout subscription checkbox now can be ticked by default;
* Comments subscription checkbox now can be ticked by default;
* Shortcode now contains webform url instad of webform id;
* Drop-down menu with webforms has been added to WYSIWYG editor;
* Web forms can now be instantly added into multiple places inside the WordPress page via Widgets;
* Custom fields can be easily mapped via the web form upon subscription;

= v1.3.2 =

* Fixed bugs in getting options. Thanks to @norcross. FAQ updated

= v1.3.1 =

* Added shortcode

= v1.3.0 =

* Added integration with WooCommerce to allow users to subscribe via the checkout page.

= v1.2.1 =

* Fixed code.

= v1.2.0 =

* Note that the web form installed via the old version of the plug-in will still be fully operational, so you do not need to replace it with the new one. If you want to add the new “Subscribe via comment” function, simply delete old plug-in and install new – and use the same web form ID.

= v1.1.1 =

* Fixed integration with new WebForms.

= v1.1 =

* Added possiblity to use Wordpress styles,
* Added integration with new WebForms.

= v1.0 =

* Initial release.