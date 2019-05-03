<?php
/**
 * @see AzpZend_Filter_Interface
 */
require_once 'UcdZend/Filter/Interface.php';

class UcdPumka_Filter_UriNormalize implements UcdZend_Filter_Interface
{
    public function filter($value)
    {
        if ('' == $value) {
            return '';
        }

        $scheme = @parse_url($value, PHP_URL_SCHEME);
        if (is_null($scheme)) {
            $value = 'http://' . $value;
        }

        return $value;
    }
}