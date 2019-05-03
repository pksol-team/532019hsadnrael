<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Generic.php';

abstract class Ucd_Controller_Abstract_Filter
    extends Ucd_Controller_Abstract_Generic
{
    // Do not support view scripts
    public function setViewScript($value)
    {
        //$this->_viewScript = $value;
        return $this;
    }
}