<?php
require_once 'UcdZend/Validate/Abstract.php';
require_once 'UcdZend/Uri.php';

class UcdPumka_Validate_Url extends UcdZend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL => "'%value%' is not a valid URL.",
    );

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);

        if (!UcdZend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return FALSE;
        }

        return TRUE;
    }
}