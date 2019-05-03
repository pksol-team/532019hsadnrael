<?php
require_once UCD_APPLICATION_PATH . '/services/AbstractService.php';

class Ucd_Service_Countdown extends Ucd_Service_AbstractService
{
    /**
    * @var array of Ucd_Model_Block_Widget indexed by post IDs
    */
    protected $_widgets = array();

    protected $_visitorInfo;

    protected $_time;

    public function __construct()
    {
        $cookieName = Ucd_WpPlugin::PREFIX . '_' . Ucd_WpPlugin::COOKIE_BASE_NAME;

        $this->_visitorInfo = array(
            'cookie_value' => isset($_COOKIE[$cookieName])
                ? $_COOKIE[$cookieName] : '',
            'ip_address' => $this->_getClientIpAddress(),
        );

        $this->_time = time();
    }

    protected function _getClientIpAddress()
    {
        $altKeys = array(
            'HTTP_X_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'HTTP_CF_CONNECTING_IP',
        );

        foreach ($altKeys as $key) {
            if (isset($_SERVER[$key])) {
                $result = $_SERVER[$key];
                if ('HTTP_FORWARDED' == $key) {
                    $result = str_replace('for=', '', $result);
                }
                return $result;
            }
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
    * Records user visit and detects if a redirect needed
    *
    * @param int $postId
    */
    public function processPostView($postId)
    {
        $widget = $this->getWidget($postId); // This will record first visit if needed
        if ($widget->isExpired() && 'redirect' == $widget->getValue('action_type')) {
            $url = $widget->getValue('action_redirect_url');
            // Try to redirect using HTTP headers
            if (!headers_sent()) {
                header("Location: {$url}", TRUE, 302);
                exit();
            } else {
                Ucd_WpPlugin::setPendingRedirectUrl($url);
            }
        }

        return $this;
    }

    //
    // Getters and setters
    //

    public function getWidget($postId)
    {
        if (!array_key_exists($postId, $this->_widgets)) {
            $visitorInfo = $this->_visitorInfo;
            $visitorInfo['post_id'] = $postId;

            if (0 == $postId
                || Ucd_Application::getModel('PostMeta')->setPostId($postId)
                        ->getValue('enabled')
            ) {
                $widget = Ucd_Application::getModel('Block_Widget')
                    ->setPostId($postId)
                    ->setVisitorInfo($visitorInfo);
            } else {
                $widget = NULL;
            }

            $this->_widgets[$postId] = $widget;
        }

        return $this->_widgets[$postId];
    }

    public function getVisitorInfo()
    {
        return $this->_visitorInfo;
    }

    public function getTime()
    {
        return $this->_time;
    }
}