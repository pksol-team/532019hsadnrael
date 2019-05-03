<?php
require_once UCD_APPLICATION_PATH . '/services/View/Helper/AbstractHelper.php';

class Ucd_Service_View_Helper_AddSlashes
    extends Ucd_Service_View_Helper_AbstractHelper
{
    public function start()
    {
        ob_start();
    }

    public function finish()
    {
        $output = ob_get_clean();
        $output = addslashes($output);
        $output = str_replace("\n", '\n', $output);
        $output = str_replace("\r", '', $output);

        echo $output;
    }

    public function __invoke()
    {
        return $this->finish();
    }
}