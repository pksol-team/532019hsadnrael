<?php
require_once UCD_APPLICATION_PATH . '/services/AbstractService.php';

abstract class Ucd_Service_View_Helper_AbstractHelper
    extends Ucd_Service_AbstractService
{
    protected $_view;

    public function init()
    {
        // Extensions
    }

    static public function isSingleton()
    {
        return FALSE;
    }

    public function setView(Ucd_Service_View $value)
    {
        $this->_view = $value;

        return $this;
    }
}
