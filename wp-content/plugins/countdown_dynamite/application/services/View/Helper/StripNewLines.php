<?php
require_once UCD_APPLICATION_PATH . '/services/View/Helper/AbstractHelper.php';

class Ucd_Service_View_Helper_StripNewLines
    extends Ucd_Service_View_Helper_AbstractHelper
{
    public function start()
    {
        ob_start();
    }

    public function finish()
    {
        $output = ob_get_clean();
        $output = str_replace(array("\n", "\r"), '', $output);

        echo $output;
    }

    public function __invoke()
    {
        return $this->finish();
    }
}