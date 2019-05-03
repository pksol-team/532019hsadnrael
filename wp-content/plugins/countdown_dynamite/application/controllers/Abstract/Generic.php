<?php
abstract class Ucd_Controller_Abstract_Generic
{
    protected $_view;
    protected $_viewScript;
    protected $_layoutScript;

    protected $_params = array();

    public final function __construct()
    {
        //Init view engine
        $this->_view = Ucd_Application::getService('View');

        $this->init();
    }

    //
    // Control flow
    //

    /**
    * Throws flow logic exception
    * Suitable to abort execution and flag error
    *
    * @param mixed $message
    */
    protected function _throwError($message)
    {
        require_once UCD_APPLICATION_PATH . '/controllers/Exception.php';
        throw new Ucd_Controller_Exception($message);
    }

    public function dispatch($action)
    {
        $method = "{$action}Action";

        $this->preDispatch($action);
        $result = $this->$method();
        $this->postDispatch($action, $result);

        return $result;
    }

    //
    // Extensions
    //

    public function init()
    {
        // Extensions
    }

    public function preDispatch(&$action)
    {
        // Extensions
    }

    public function postDispatch($action, $result)
    {
        // Extensions
    }

    public function handleError(Exception $exception)
    {
        // Extensions
    }

    public function getViewScriptPath()
    {
        // Override to use custom path
        return UCD_APPLICATION_PATH . '/views/scripts';
    }

    //
    // Getters & setters
    //

    public function getView()
    {
        return $this->_view;
    }

    public function getLayoutScript()
    {
        return $this->_layoutScript;
    }

    public function setViewScript($value)
    {
        $this->_viewScript = $value;
        return $this;
    }

    public function getViewScript()
    {
        return $this->_viewScript;
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getParam($key)
    {
        return $this->hasParam($key)
            ? $this->_params[$key]
            : NULL;
    }

    public function setParams(array $value)
    {
        $this->_params = $value;
    }

    public function hasParam($key)
    {
        return array_key_exists($key, $this->_params);
    }
}