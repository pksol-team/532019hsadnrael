<?php
/**
 * @see AzpZend_Filter_Interface
 */
require_once 'UcdZend/Filter/Interface.php';

class UcdPumka_Filter_StripSlashes implements UcdZend_Filter_Interface
{
    public function filter($value)
    {
        if (is_null($value)) {
            return NULL;
        }

        return stripslashes($value);
    }
}