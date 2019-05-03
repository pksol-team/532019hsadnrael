<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Generic.php';

abstract class Ucd_Controller_Abstract_Ajax
    extends Ucd_Controller_Abstract_Generic
{
    public function dispatch($action)
    {
        try {
            $result = parent::dispatch($action);
        } catch (Exception $e) {
            $this->postDispatch($action, $e);
            $this->_handleError($e);
        }

        if (!is_null($result)) {
            // Output returned value as JSON
            header('Content-Type: application/json', TRUE);
            exit(json_encode($result));
        }

        return $result;
    }

    protected function _handleError(Exception $e)
    {
        $message = $e->getMessage();

        // All handled exceptions must be of Ucd_Controller_Exception class.
        // Other exception class means this is unhandled exception
        if (!($e  instanceof Ucd_Controller_Exception)) {
            header('X-Ajax-Error-Unhandled: 1');
        } else {
            header('X-Ajax-Error: 1');
        }

        header('HTTP/1.0 500 Internal Server Error', TRUE, 500);
        header('Content-Type: text/plain');

        exit($message);
    }
}