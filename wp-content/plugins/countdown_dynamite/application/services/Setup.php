<?php
require_once UCD_APPLICATION_PATH . '/services/AbstractService.php';

class Ucd_Service_Setup extends Ucd_Service_AbstractService
{
    public function install()
    {
        global $wpdb;

        $options = Ucd_Application::getModel('Options');
        $version = $options->getValue('version');

        $tablePrefix = $wpdb->prefix . Ucd_WpPlugin::PREFIX . '_';

        // If not installed
        if ('' == $version) {
            // Set default timezone according to GMT offset
            $offset = get_option('gmt_offset');
            $options->setValue('expiration_date_timezone_base', 'UTC')
                ->setValue('expiration_date_timezone_offset_hours', (int) $offset)
                ->setValue('expiration_date_timezone_offset_minutes', 60 * ($offset - (int) $offset));

            $options->setValue('visitor_last_cleanup', time());
        }

        // Create visitor table
        $this->_query("CREATE TABLE IF NOT EXISTS `{$tablePrefix}visitor` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) NOT NULL,
            `cookie_value` varchar(13) NOT NULL,
            `ip_address` varchar(39) NOT NULL,
            `first_visit` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `post_id` (`post_id`),
            KEY `cookie_value` (`cookie_value`),
            KEY `ip_address` (`ip_address`),
            KEY `first_visit` (`first_visit`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // End of setup. Write installed version
        $options->setValue('version', Ucd_WpPlugin::VERSION);
    }

    protected function _query($sql)
    {
        global $wpdb;
        $wpdb->last_error = '';

        $wpdb->query($sql);

        if ('' != $wpdb->last_error) {
            throw new Exception("Database scheme update failed: {$wpdb->last_error} (SQL: {$sql})");
        }
    }
}