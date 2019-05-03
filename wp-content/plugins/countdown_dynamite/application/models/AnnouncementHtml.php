<?php
require_once UCD_APPLICATION_PATH . '/models/AbstractModel.php';

class Ucd_Model_AnnouncementHtml extends Ucd_Model_AbstractModel
{
    const CACHE_TIMEOUT = 3600;
    const REQUEST_TIMEOUT = 20;
    const OPTION_NAME = 'announcement_cache';

    public function get()
    {
        if ('' == Ucd_WpPlugin::PLUGIN_BASE_URL) {
            return '';
        }

        $cache = Ucd_Application::getModel('Options')->getValue(self::OPTION_NAME);
        if (!is_array($cache)
            || !isset($cache['timestamp'])
            || !isset($cache['data'])
            || (self::CACHE_TIMEOUT < (time() - $cache['timestamp']))
        ) {
            if(!class_exists('WP_Http')) {
                require_once ABSPATH . WPINC. '/class-http.php';
            }

            $http = new WP_Http;

            $result = $http->request(Ucd_WpPlugin::PLUGIN_BASE_URL . 'announcement.html', array(
                'timeout' => self::REQUEST_TIMEOUT,
            ));
            if (is_wp_error($result)) {
                $result = '<!-- Error downloading announcement HTML: '
                    . esc_html($result->get_error_message())
                    . ' -->';
            } else if (200 != $result['response']['code']) {
                $result = '<!-- Announcement request failed: '
                    . $result['response']['code']
                    . ' -->';
            } else {
                $result = $result['body'];
            }

            $cache = array(
                'data' => $result,
                'timestamp' => time(),
            );

            Ucd_Application::getModel('Options')
                ->setValue(self::OPTION_NAME, $cache);
        } else {
            $result = $cache['data'];
        }

        return $result;
    }
}