<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Action.php';

abstract class Ucd_Controller_Abstract_Admin
    extends Ucd_Controller_Abstract_Action
{
    protected $_layoutScript = 'admin.phtml';

    public function init()
    {
        parent::init();

        //Init layout
        $this->_view->layout = Ucd_Application::getService('View_Layout');
    }

    protected function _redirect($action=NULL, $controller=NULL, $parameters=NULL)
    {
        $this->_redirectUrl(
            $this->_view->adminUrl($action, $controller, $parameters));
    }

    protected function _redirectUrl($url)
    {
        $this->_viewScript = 'redirect.phtml';
        $this->_view->url = $url;
    }

    protected function _addMessage($message, $class='updated')
    {
        $messages = is_array($this->_view->layout->messages)
            ? $this->_view->layout->messages
            : array();

        $messages[] = array(
            'class' => $class,
            'text' => $message,
        );

        $this->_view->layout->messages = $messages;
    }
}