<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Ajax.php';

class Ucd_Controller_AdminAjaxController
    extends Ucd_Controller_Abstract_Ajax
{
    public function clearHistoryAction()
    {
        Ucd_Application::getModel('Data_Visitor')->clearHistory(
            Ucd_Application::getService('Countdown')->getVisitorInfo()
        );

        return array();
    }
}