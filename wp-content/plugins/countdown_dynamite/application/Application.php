<?php
define('UCD_APPLICATION_PATH', dirname(__FILE__));
define('UCD_LIBRARY_PATH', dirname(dirname(__FILE__)) . '/library');

// Define application environment
if (!defined('UCD_APPLICATION_ENV')) {
    if (getenv('UCD_APPLICATION_ENV')) {
        define('UCD_APPLICATION_ENV', getenv('UCD_APPLICATION_ENV'));
    } else if (getenv('REDIRECT_UCD_APPLICATION_ENV')) {
        define('UCD_APPLICATION_ENV', getenv('REDIRECT_UCD_APPLICATION_ENV'));
    }
    else {
        define('UCD_APPLICATION_ENV', 'production');
    }

}

abstract class Ucd_Application
{
    static protected $_namespaces = array(
        'controller' => array(
            'namespace' => 'Controller',
            'folder'    => 'controllers',
        ),
        'model' => array(
            'namespace' => 'Model',
            'folder'    => 'models',
        ),
        'service' => array(
            'namespace' => 'Service',
            'folder'    => 'services',
        ),
        'form' => array(
            'namespace' => 'Form',
            'folder'    => 'forms',
        ),
    );

    static protected $_singletons = array();
    static protected $_config;

    static public function dispatch($controller='index', $action='index',
        $params = NULL)
    {
        if (empty($controller)) {
            $controller = 'index';
        } else {
            $controller = strtolower($controller);
        }
        if (empty($action)) {
            $action = 'index';
        } else {
            $action = strtolower($action);
        }

        $controllerClass = self::camelize($controller) . 'Controller';
        $controllerObject = self::factory($controllerClass, 'controller');

        $controllerObject->setParams((array) $params);

        $controllerObject->setViewScript($controller . '/' . $action . '.phtml');

        $result = $controllerObject->dispatch(self::camelize($action, FALSE));

        $viewScript = $controllerObject->getViewScript();
        if (!empty($viewScript)) {
            /**
            * @var Ssc_WpModule_View_Engine
            */
            $view = $controllerObject->getView();

            if (is_array($result)) {
                $view->assign($result);
            }

            if (isset($view->layout)
                && ($layoutScript = $controllerObject->getLayoutScript())
            ) {
                $view->layout->content = $view->render($viewScript);
                $view->layout->renderDirect($layoutScript);
            } else {
                $view->renderDirect($viewScript);
            }
        }

        return $result;
    }

    static public function getModel($name, $module = '')
    {
        return self::factory($name, 'model', $module);
    }

    static public function getService($name, $module = '')
    {
        return self::factory($name, 'service', $module);
    }

    static public function getController($name, $module = '')
    {
        return self::factory($name, 'controller', $module);
    }

    static public function getForm($name, $module = '')
    {
        return self::factory($name, 'form', $module);
    }

    static public function factory($name, $type, $module = '')
    {
        $info = self::$_namespaces[$type];
        $class = $info['namespace'] . '_' . $name;
        if ('' != $module) {
            $class = self::camelize($module) . '_' . $class;
        }
        $class = ucfirst(Ucd_WpPlugin::PREFIX) . '_' . $class;

        $path =  '/' . $info['folder'] . '/' . str_replace('_', '/', $name) . '.php';
        if ('' != $module) {
            $path = '/modules/' . $module . $path;
        }
        $path = UCD_APPLICATION_PATH . $path;

        require_once $path;

        $singleton = method_exists($class, 'isSingleton')
            && call_user_func(array($class, 'isSingleton'));
        if ($singleton && isset(self::$_singletons[$class])) {
            return self::$_singletons[$class];
        }

        $result = new $class;

        if ($singleton) {
            self::$_singletons[$class] = $result;
        }

        return $result;
    }

    static public function camelize($string, $first=TRUE)
    {
        $string = ucwords(str_replace('-', ' ', $string));
        $string = str_replace(' ', '', $string);
        if (!$first) {
            $string = strtolower($string[0]) . substr($string, 1);
        }

        return $string;
    }

    static public function getConfig()
    {
        if (!self::$_config) {
            require_once UCD_LIBRARY_PATH . '/UcdZend/Config/Ini.php';
            self::$_config = new UcdZend_Config_Ini(UCD_APPLICATION_PATH . '/configs/application.ini', UCD_APPLICATION_ENV);
        }

        return self::$_config;
    }

    /**
    * Library factory
    */
    static public function loadLibrary($class)
    {
        require_once UCD_LIBRARY_PATH . '/' . str_replace('_', '/', $class) . '.php';
        $result = new $class;

        return $result;
    }
}