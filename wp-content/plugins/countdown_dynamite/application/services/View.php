<?php
require_once UCD_APPLICATION_PATH . '/services/AbstractService.php';

class Ucd_Service_View extends Ucd_Service_AbstractService
{
    protected $_data = array();
    protected $_helpers = array();

    protected $_scriptFolder = 'scripts';

    /**
    * Executes given template script and returns output
    *
    * @param string $script Script file name
    * @return string Script output
    */
    public function render($script, $module='')
    {
        ob_start();
        $this->_run($script, $module);
        $result = ob_get_clean();

        return $result;
    }

    /**
    * Executes given template script and immediately sends output
    *
    * @param string $script Script file name
    * @return void
    */
    public function renderDirect($script, $module='')
    {
        $this->_run($script, $module);
    }

    /**
    * Renders view script by absolute path
    *
    * @param string $script
    */
    public function renderAbs($script)
    {
        ob_start();
        $this->_executeScript($script);
        $result = ob_get_clean();

        return $result;
    }

    /**
    * Renders view script by absolute path and sends output
    *
    * @param string $script
    */
    public function renderDirectAbs($script)
    {
        $this->_executeScript($script);
    }


    /**
    * Renders given template script in its own variable context and returns output
    *
    * @param string $script Script file name
    * @param array $data Variables to pass to the script
    * @return string Script putput
    */
    public function partial($script, array $data=array(), $module='')
    {
        $oldData = $this->_data;
        $this->_data = $data;

        $result = $this->render($script, $module);

        $this->_data = $oldData;

        return $result;
    }

    /**
    * HTML escape given string
    *
    * @param string $text
    * @return string
    */
    public function escape($text)
    {
        $text = (string) $text;
        if ('' === $text) return '';

        $result = @htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
        if (empty($result)) {
            $result = @htmlspecialchars(utf8_encode($text), ENT_COMPAT, 'UTF-8');
        }

        return $result;
    }

    public function adminUrl($params=NULL, $escape=TRUE)
    {
        if (!$params) {
            $params = array();
        }

        $query = array('page' => Ucd_WpPlugin::SLUG);
        foreach (array('controller', 'action') as $key) {
            if (isset($params[$key])) {
                $query[$key] = $params[$key];
                unset($params[$key]);
            }
        }

        ksort($params);

        foreach ($params as $key=>$value) {
            if (is_null($value)) {
                $query[$key] = $value;
            }
        }

        $result = admin_url('/admin.php?' . http_build_query($query, '', '&'));

        if ($escape) {
            $result = $this->escape($result);
        }

        return $result;
    }

    /**
    * Exectures view script
    *
    * @param string Script path
    */
    protected function _run($script, $module='')
    {
        $path = '' == $module
            ? UCD_APPLICATION_PATH . "/views/{$this->_scriptFolder}/{$script}"
            : UCD_APPLICATION_PATH . "/modules/{$module}/views/{$this->_scriptFolder}/{$script}";

        $this->_executeScript($path);
    }

    protected function _executeScript()
    {
        extract($this->_data);

        require func_get_arg(0);
    }

    function getHelper($name, $module='')
    {
        $name = ucfirst($name);

        $key = $name;
        if ('' != $module) {
            $key = $module . ':' . $name;
        }

        if (!isset($this->_helpers[$key])) {
            $helper =  Ucd_Application::getService(
                'View_Helper_' . $name, $module);
            $helper->setView($this);
            $this->_helpers[$key] = $helper;
        }

        return $this->_helpers[$key];
    }

    public function __call($method, $arguments)
    {
        $helper = $this->getHelper($method);
        if ($arguments) {
            return call_user_func_array(array($helper, '__invoke'), $arguments);
        } else {
            return $helper;
        }
    }

    //
    // Variable storage methods
    //

    public function assign($var, $value=NULL)
    {
        if (is_array($var)) {
            $this->_data = array_merge($this->_data, $var);
        } else {
            $this->_data[$var] = $value;
        }

        return $this;
    }

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    public function __get($key)
    {
        return isset($this->_data[$key])? $this->_data[$key] : NULL;
    }

    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    public function __unset($key)
    {
        unset($this->_data[$key]);
    }
}