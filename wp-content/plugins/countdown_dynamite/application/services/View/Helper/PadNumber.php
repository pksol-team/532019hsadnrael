<?php
require_once UCD_APPLICATION_PATH . '/services/View/Helper/AbstractHelper.php';

class Ucd_Service_View_Helper_PadNumber
    extends Ucd_Service_View_Helper_AbstractHelper
{

    public function __invoke($number, $padTo, $padChar='0')
    {
        $number = (string) (int) $number;
        $len = strlen($number);
        if ($len < $padTo) {
            $number = str_repeat($padChar, $padTo - $len) . $number;
        }

        return $number;
    }
}