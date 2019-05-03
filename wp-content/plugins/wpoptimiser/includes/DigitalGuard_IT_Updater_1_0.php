<?php
/**
 * Created by DigitalGuard.it
 *
 * Copyright (c) 2016 Copyright Holder All Rights Reserved.
 */

if ( !class_exists('DigitalGuard_IT_Updater_1_0') ) {

/**
 * A custom plugin update checker.
 *
 */
class DigitalGuard_IT_Updater_1_0 {
  public $apiKey = '';
	public $metadataUrl = ''; //The URL of the plugin's metadata file.
	public $pluginFile = '';  //Plugin filename relative to the plugins directory.
	public $slug = '';        //Plugin slug.
	public $checkPeriod = 12; //How often to check for updates (in hours).
	public $optionName = '';  //Where to store the update info.
	public $enableLicenseCheck = false;     //Enable/disable automatic update checks.

	private $cronHook = null;

	protected static $updateServerUrl = 'http://digitalguard.it/wp-update-server/';
  protected static $filterPrefix = 'digitalguard_it_update_';
	/**
	 * Class constructor.
	 *
	 * @param string $pluginFile Fully qualified path to the main plugin file.
	 * @param string $slug The plugin's 'slug'. Most likely the name of the folder in which the plugin resides.
	 * @param string $optionNamePrefix Prefic for the option name of where to store info about update checks. The slug is used as well to determin the option name as '$optionNamePrefix-$slug'.
	 * @param boolean $enableLicenseCheck Enable/Disable license checking
	 */
	public function __construct($apiKey, $pluginFile, $slug, $optionNamePrefix, $enableLicenseCheck = false){
    $this->apiKey = $apiKey;
		$this->metadataUrl = self::$updateServerUrl.(!$enableLicenseCheck ? 'nolic.php' : '').'?action=get_metadata&slug='.$slug;
		$this->pluginFile = plugin_basename($pluginFile);
		$this->slug = $slug;
		$this->optionName = $optionNamePrefix.'-'.$this->slug;
		$this->enableLicenseCheck = $enableLicenseCheck;

		$this->installHooks();
	}

   /**
    * Install the hooks required to run regular update checks and inject update info
    * into WP data structures.
    *
    * @return void
    */
	protected function installHooks(){
		//Override requests for plugin information
		add_filter('plugins_api', array($this, 'injectInfo'), 20, 3);

		//Insert our update info into the update array maintained by WP
		add_filter('site_transient_update_plugins', array($this,'injectUpdate')); //WP 3.0+
		add_filter('transient_update_plugins', array($this,'injectUpdate')); //WP 2.8+

		add_filter('plugin_row_meta', array($this, 'addCheckForUpdatesLink'), 10, 4);
		add_action('admin_init', array($this, 'handleManualCheck'));
		add_action('admin_notices', array($this, 'displayManualCheckResult'));

		//Set up the periodic update checks
		$this->cronHook = 'check_plugin_updates-' . $this->slug;
		if ( $this->checkPeriod > 0 ){

			//Trigger the check via Cron
			add_filter('cron_schedules', array($this, '_addCustomSchedule'));
			if ( !wp_next_scheduled($this->cronHook) && !defined('WP_INSTALLING') ) {
				$scheduleName = 'every' . $this->checkPeriod . 'hours';
				wp_schedule_event(time(), $scheduleName, $this->cronHook);
			}
			add_action($this->cronHook, array($this, 'checkForUpdates'));

			register_deactivation_hook($this->pluginFile, array($this, '_removeUpdaterCron'));

			//In case Cron is disabled or unreliable, we also manually trigger
			//the periodic checks while the user is browsing the Dashboard.
			add_action( 'admin_init', array($this, 'maybeCheckForUpdates') );

		} else {
			//Periodic checks are disabled.
			wp_clear_scheduled_hook($this->cronHook);
		}
	}

	/**
	 * Add our custom schedule to the array of Cron schedules used by WP.
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function _addCustomSchedule($schedules){
		if ( $this->checkPeriod && ($this->checkPeriod > 0) ){
			$scheduleName = 'every' . $this->checkPeriod . 'hours';
			$schedules[$scheduleName] = array(
				'interval' => $this->checkPeriod * 3600,
				'display' => sprintf('Every %d hours', $this->checkPeriod),
			);
		}
		return $schedules;
	}

	/**
	 * Remove the scheduled cron event that the library uses to check for updates.
	 *
	 * @return void
	 */
	public function _removeUpdaterCron(){
		wp_clear_scheduled_hook($this->cronHook);
	}

	/**
	 * Get the name of the update checker's WP-cron hook. Mostly useful for debugging.
	 *
	 * @return string
	 */
	public function getCronHookName() {
		return $this->cronHook;
	}

	/**
	 * Retrieve plugin info from the configured API endpoint.
	 *
	 * @uses wp_remote_get()
	 *
	 * @param array $queryArgs Additional query arguments to append to the request. Optional.
	 * @return PluginInfo
	 */
	public function requestInfo($queryArgs = array()){
		//Query args to append to the URL. Plugins can add their own by using a filter callback (see addQueryArgFilter()).
		$installedVersion = $this->getInstalledVersion();
		$queryArgs['api_key'] = $this->apiKey;
		$queryArgs['installed_version'] = ($installedVersion !== null) ? $installedVersion : '';
		if ( $this->enableLicenseCheck )
   		$queryArgs = apply_filters(self::$filterPrefix.'info_query_args-'.$this->slug, $queryArgs);

		//Various options for the wp_remote_get() call. Plugins can filter these, too.
		$options = array(
			'timeout' => 10, //seconds
			'headers' => array(
				'Accept' => 'application/json'
			),
		);
		$options = apply_filters(self::$filterPrefix.'info_options-'.$this->slug, $options);

		//The plugin info should be at 'http://your-api.com/url/here/$slug/info.json'
		$url = $this->metadataUrl;
		if ( !empty($queryArgs) ){
			$url = add_query_arg($queryArgs, $url);
		}

		$result = wp_remote_get(
			$url,
			$options
		);

		//Try to parse the response
		$pluginInfo = null;
		if ( !is_wp_error($result) && isset($result['response']['code']) && ($result['response']['code'] == 200) && !empty($result['body']) ){
			$pluginInfo = DigitalGuardItPluginInfo::fromJson($result['body']);
		}

		$pluginInfo = apply_filters(self::$filterPrefix.'info_result-'.$this->slug, $pluginInfo, $result);
		return $pluginInfo;
	}

	/**
	 * Retrieve the latest update (if any) from the configured API endpoint.
	 *
	 * @uses PluginUpdateChecker::requestInfo()
	 *
	 * @return PluginUpdate An instance of PluginUpdate, or NULL when no updates are available.
	 */
	public function requestUpdate(){
		//For the sake of simplicity, this function just calls requestInfo()
		//and transforms the result accordingly.
		$pluginInfo = $this->requestInfo(array('checking_for_updates' => '1'));
		if ( $pluginInfo == null ){
			return null;
		}
		return DigitalGuardItPluginUpdate::fromPluginInfo($pluginInfo);
	}

	/**
	 * Get the currently installed version of the plugin.
	 *
	 * @return string Version number.
	 */
	public function getInstalledVersion(){
		if ( !function_exists('get_plugins') ){
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		$allPlugins = get_plugins();

		if ( array_key_exists($this->pluginFile, $allPlugins) && array_key_exists('Version', $allPlugins[$this->pluginFile]) ){
			return $allPlugins[$this->pluginFile]['Version'];
		} else {
			//This can happen if the filename is wrong or the plugin is installed in mu-plugins.
			return null;
		}
	}

	/**
	 * Check for plugin updates.
	 * The results are stored in the DB option specified in $optionName.
	 *
	 * @return PluginUpdate|null
	 */
	public function checkForUpdates(){
		$installedVersion = $this->getInstalledVersion();

		//Fail silently if we can't find the plugin or read its header.
		if ( $installedVersion === null ) {
			return null;
		}

		$state = $this->getUpdateState();
		if ( empty($state) ){
			$state = new StdClass;
			$state->lastCheck = 0;
			$state->checkedVersion = '';
			$state->update = null;
		}

		$state->lastCheck = time();
		$state->checkedVersion = $installedVersion;
		$this->setUpdateState($state); //Save before checking in case something goes wrong

		$state->update = $this->requestUpdate();
		$this->setUpdateState($state);

		return $this->getUpdate();
	}

	/**
	 * Check for updates only if the configured check interval has already elapsed.
	 *
	 * @return void
	 */
	public function maybeCheckForUpdates(){
		if ( empty($this->checkPeriod) ){
			return;
		}
		$state = $this->getUpdateState();

		$shouldCheck =
			empty($state) ||
			!isset($state->lastCheck) ||
			( (time() - $state->lastCheck) >= $this->checkPeriod*3600 );

		if ( $shouldCheck ){
			$this->checkForUpdates();
		}
	}

	/**
	 * Load the update checker state from the DB.
	 *
	 * @return StdClass|null
	 */
	public function getUpdateState() {
		$state = get_site_option($this->optionName);
		if ( !empty($state) && isset($state->update) && is_object($state->update) ){
			$state->update = DigitalGuardItPluginUpdate::fromObject($state->update);
		}
		return $state;
	}


	/**
	 * Persist the update checker state to the DB.
	 *
	 * @param StdClass $state
	 * @return void
	 */
	private function setUpdateState($state) {
		if ( isset($state->update) && is_object($state->update) && method_exists($state->update, 'toStdClass') ) {
			$update = $state->update; /** @var PluginUpdate $update */
			$state->update = $update->toStdClass();
		}
		update_site_option($this->optionName, $state);
	}

	/**
	 * Reset update checker state - i.e. last check time, cached update data and so on.
	 *
	 * Call this when your plugin is being uninstalled, or if you want to
	 * clear the update cache.
	 */
	public function resetUpdateState() {
		delete_site_option($this->optionName);
	}

	/**
	 * Intercept plugins_api() calls that request information about our plugin and
	 * use the configured API endpoint to satisfy them.
	 *
	 * @see plugins_api()
	 *
	 * @param mixed $result
	 * @param string $action
	 * @param array|object $args
	 * @return mixed
	 */
	public function injectInfo($result, $action = null, $args = null){
    	$relevant = ($action == 'plugin_information') && isset($args->slug) && ($args->slug == $this->slug);
		if ( !$relevant ){
			return $result;
		}

		$pluginInfo = $this->requestInfo();
		$pluginInfo = apply_filters(self::$filterPrefix.'inject_info-' . $this->slug, $pluginInfo);
		if ($pluginInfo){
			return $pluginInfo->toWpFormat();
		}

		return $result;
	}

	/**
	 * Insert the latest update (if any) into the update list maintained by WP.
	 *
	 * @param StdClass $updates Update list.
	 * @return StdClass Modified update list.
	 */
	public function injectUpdate($updates){
		//Is there an update to insert?
		$update = $this->getUpdate();
		if ( !empty($update) ) {
			//Let plugins filter the update info before it's passed on to WordPress.
			$update = apply_filters(self::$filterPrefix.'inject_update-' . $this->slug, $update);
			if ( !is_object($updates) ) {
				$updates = new StdClass();
				$updates->response = array();
			}
			$updates->response[$this->pluginFile] = $update->toWpFormat();
		} else if ( isset($updates, $updates->response) ) {
			unset($updates->response[$this->pluginFile]);
		}

		return $updates;
	}

	/**
	 * Get the details of the currently available update, if any.
	 *
	 * If no updates are available, or if the last known update version is below or equal
	 * to the currently installed version, this method will return NULL.
	 *
	 * Uses cached update data. To retrieve update information straight from
	 * the metadata URL, call requestUpdate() instead.
	 *
	 * @return PluginUpdate|null
	 */
	public function getUpdate() {
		$state = $this->getUpdateState(); /** @var StdClass $state */

		//Is there an update available insert?
		if ( !empty($state) && isset($state->update) && !empty($state->update) ){
			$update = $state->update;
			//Check if the update is actually newer than the currently installed version.
			$installedVersion = $this->getInstalledVersion();
			if ( ($installedVersion !== null) && version_compare($update->version, $installedVersion, '>') ){
				return $update;
			}
		}
		return null;
	}

	/**
	 * Add a "Check for updates" link to the plugin row in the "Plugins" page. By default,
	 * the new link will appear after the "Visit plugin site" link.
	 *
	 * You can change the link text by using the "puc_manual_check_link-$slug" filter.
	 * Returning an empty string from the filter will disable the link.
	 *
	 * @param array $pluginMeta Array of meta links.
	 * @param string $pluginFile
	 * @param array|null $pluginData Currently ignored.
	 * @param string|null $status Currently ignored/
	 * @return array
	 */
	public function addCheckForUpdatesLink($pluginMeta, $pluginFile, $pluginData = null, $status = null) {
		if ( $pluginFile == $this->pluginFile && current_user_can('update_plugins') ) {
			$linkText = apply_filters(self::$filterPrefix.'check_link-' . $this->slug, 'Check for updates');
			$linkUrl = wp_nonce_url(
				add_query_arg('digitalguard_it_check_for_updates', $this->slug, admin_url('plugins.php')),
				'digitalguard_it_check_for_updates'
			);
			if ( !empty($linkText) ) {
				$pluginMeta[] = sprintf('<a href="%s">%s</a>', esc_attr($linkUrl), $linkText);
			}
		}
		return $pluginMeta;
	}

	/**
	 * Check for updates when the user clicks the "Check for updates" link.
	 * @see self::addCheckForUpdatesLink()
	 *
	 * @return void
	 */
	public function handleManualCheck() {
		$shouldCheck =
			   isset($_GET['digitalguard_it_check_for_updates'])
			&& $_GET['digitalguard_it_check_for_updates'] == $this->slug
			&& current_user_can('update_plugins')
			&& check_admin_referer('digitalguard_it_check_for_updates');

		if ( $shouldCheck ) {
			$update = $this->checkForUpdates();
			$status = ($update === null) ? 'no_update' : 'update_available';
			wp_redirect(add_query_arg('digitalguard_it_update_check_result', $status, admin_url('plugins.php')));
		}
	}

	/**
	 * Display the results of a manual update check.
	 * @see self::handleManualCheck()
	 *
	 * You can change the result message by using the "puc_manual_check_message-$slug" filter.
	 */
	public function displayManualCheckResult() {
		if ( isset($_GET['digitalguard_it_update_check_result']) ) {
			$status = strval($_GET['digitalguard_it_update_check_result']);
			if ( $status == 'no_update' ) {
				$message = 'This plugin is up to date.';
			} else if ( $status == 'update_available' ) {
				$message = 'A new version of this plugin is available.';
			} else {
				$message = sprintf('Unknown update checker status "%s"', htmlentities($status));
			}
			printf(
				'<div class="updated"><p>%s</p></div>',
				apply_filters(self::$filterPrefix.'check_message-' . $this->slug, $message, $status)
			);
		}
	}

	/**
	 * Register a callback for filtering query arguments.
	 *
	 * The callback function should take one argument - an associative array of query arguments.
	 * It should return a modified array of query arguments.
	 *
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function addQueryArgFilter($callback){
		add_filter(self::$filterPrefix.'info_query_args-'.$this->slug, $callback);
	}

	/**
	 * Register a callback for filtering arguments passed to wp_remote_get().
	 *
	 * The callback function should take one argument - an associative array of arguments -
	 * and return a modified array or arguments. See the WP documentation on wp_remote_get()
	 * for details on what arguments are available and how they work.
	 *
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function addHttpRequestArgFilter($callback){
		add_filter(self::$filterPrefix.'info_options-'.$this->slug, $callback);
	}

	/**
	 * Register a callback for filtering the plugin info retrieved from the external API.
	 *
	 * The callback function should take two arguments. If the plugin info was retrieved
	 * successfully, the first argument passed will be an instance of  PluginInfo. Otherwise,
	 * it will be NULL. The second argument will be the corresponding return value of
	 * wp_remote_get (see WP docs for details).
	 *
	 * The callback function should return a new or modified instance of PluginInfo or NULL.
	 *
	 * @uses add_filter() This method is a convenience wrapper for add_filter().
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function addResultFilter($callback){
		add_filter(self::$filterPrefix.'info_result-'.$this->slug, $callback, 10, 2);
	}
}

}
if ( !class_exists('DigitalGuardItPluginInfo') ) {

/**
 * A container class for holding and transforming various plugin metadata.
 *
 */
class DigitalGuardItPluginInfo {
	//Most fields map directly to the contents of the plugin's info.json file.
	//See the relevant docs for a description of their meaning.
	public $name;
	public $slug;
	public $version;
	public $homepage;
	public $sections;
	public $download_url;

	public $author;
	public $author_homepage;

	public $requires;
	public $tested;
	public $upgrade_notice;

	public $rating;
	public $num_ratings;
	public $downloaded;
	public $last_updated;

	public $id = 0; //The native WP.org API returns numeric plugin IDs, but they're not used for anything.

	/**
	 * Create a new instance of PluginInfo from JSON-encoded plugin info
	 * returned by an external update API.
	 *
	 * @param string $json Valid JSON string representing plugin info.
	 * @param bool $triggerErrors
	 * @return PluginInfo|null New instance of PluginInfo, or NULL on error.
	 */
	public static function fromJson($json, $triggerErrors = false){
		/** @var StdClass $apiResponse */
		$apiResponse = json_decode($json);
		if ( empty($apiResponse) || !is_object($apiResponse) ){
			if ( $triggerErrors ) {
				trigger_error(
					"Failed to parse plugin metadata. Try validating your .json file with http://jsonlint.com/",
					E_USER_NOTICE
				);
			}
			return null;
		}

		//Very, very basic validation.
		$valid = isset($apiResponse->name) && !empty($apiResponse->name) && isset($apiResponse->version) && !empty($apiResponse->version);
		if ( !$valid ){
			if ( $triggerErrors ) {
				trigger_error(
					"The plugin metadata file does not contain the required 'name' and/or 'version' keys.",
					E_USER_NOTICE
				);
			}
			return null;
		}

		$info = new self();
		foreach(get_object_vars($apiResponse) as $key => $value){
			$info->$key = $value;
		}

		return $info;
	}

	/**
	 * Transform plugin info into the format used by the native WordPress.org API
	 *
	 * @return object
	 */
	public function toWpFormat(){
		$info = new StdClass;

		//The custom update API is built so that many fields have the same name and format
		//as those returned by the native WordPress.org API. These can be assigned directly.
		$sameFormat = array(
			'name', 'slug', 'version', 'requires', 'tested', 'rating', 'upgrade_notice',
			'num_ratings', 'downloaded', 'homepage', 'last_updated',
		);
		foreach($sameFormat as $field){
			if ( isset($this->$field) ) {
				$info->$field = $this->$field;
			} else {
				$info->$field = null;
			}
		}

		//Other fields need to be renamed and/or transformed.
		$info->download_link = $this->download_url;

		if ( !empty($this->author_homepage) ){
			$info->author = sprintf('<a href="%s">%s</a>', $this->author_homepage, $this->author);
		} else {
			$info->author = $this->author;
		}

		if ( is_object($this->sections) ){
			$info->sections = get_object_vars($this->sections);
		} elseif ( is_array($this->sections) ) {
			$info->sections = $this->sections;
		} else {
			$info->sections = array('description' => '');
		}

		return $info;
	}
}

}

if ( !class_exists('DigitalGuardItPluginUpdate') ) {

/**
 * A simple container class for holding information about an available update.
 *
 */
class DigitalGuardItPluginUpdate {
	public $id = 0;
	public $slug;
	public $version;
	public $homepage;
	public $download_url;
	public $upgrade_notice;
	private static $fields = array('id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice');

	/**
	 * Create a new instance of PluginUpdate from its JSON-encoded representation.
	 *
	 * @param string $json
	 * @param bool $triggerErrors
	 * @return PluginUpdate|null
	 */
	public static function fromJson($json, $triggerErrors = false){
		//Since update-related information is simply a subset of the full plugin info,
		//we can parse the update JSON as if it was a plugin info string, then copy over
		//the parts that we care about.
		$pluginInfo = DigitalGuardItPluginInfo::fromJson($json, $triggerErrors);
		if ( $pluginInfo != null ) {
			return self::fromPluginInfo($pluginInfo);
		} else {
			return null;
		}
	}

	/**
	 * Create a new instance of PluginUpdate based on an instance of PluginInfo.
	 * Basically, this just copies a subset of fields from one object to another.
	 *
	 * @param PluginInfo $info
	 * @return PluginUpdate
	 */
	public static function fromPluginInfo($info){
		return self::fromObject($info);
	}

	/**
	 * Create a new instance of PluginUpdate by copying the necessary fields from
	 * another object.
	 *
	 * @param StdClass|PluginInfo|PluginUpdate $object The source object.
	 * @return PluginUpdate The new copy.
	 */
	public static function fromObject($object) {
		$update = new self();
		foreach(self::$fields as $field){
			$update->$field = $object->$field;
		}
		return $update;
	}

	/**
	 * Create an instance of StdClass that can later be converted back to
	 * a PluginUpdate. Useful for serialization and caching, as it avoids
	 * the "incomplete object" problem if the cached value is loaded before
	 * this class.
	 *
	 * @return StdClass
	 */
	public function toStdClass() {
		$object = new StdClass();
		foreach(self::$fields as $field){
			$object->$field = $this->$field;
		}
		return $object;
	}


	/**
	 * Transform the update into the format used by WordPress native plugin API.
	 *
	 * @return object
	 */
	public function toWpFormat(){
		$update = new StdClass;

		$update->id = $this->id;
		$update->slug = $this->slug;
		$update->new_version = $this->version;
		$update->url = $this->homepage;
		$update->package = $this->download_url;
		if ( !empty($this->upgrade_notice) ){
			$update->upgrade_notice = $this->upgrade_notice;
		}

		return $update;
	}
}

}

?>