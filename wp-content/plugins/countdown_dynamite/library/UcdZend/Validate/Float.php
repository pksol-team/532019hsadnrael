<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    UcdZend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Float.php 22697 2010-07-26 21:14:47Z alexander $
 */

/**
 * @see UcdZend_Validate_Abstract
 */
require_once 'UcdZend/Validate/Abstract.php';

/**
 * @see UcdZend_Locale_Format
 */
require_once 'UcdZend/Locale/Format.php';

/**
 * @category   Zend
 * @package    UcdZend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class UcdZend_Validate_Float extends UcdZend_Validate_Abstract
{
    const INVALID   = 'floatInvalid';
    const NOT_FLOAT = 'notFloat';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given, value should be float, string, or integer",
        self::NOT_FLOAT => "'%value%' does not appear to be a float",
    );

    protected $_locale;

    /**
     * Constructor for the float validator
     *
     * @param string|UcdZend_Config|UcdZend_Locale $locale
     */
    public function __construct($locale = null)
    {
        if ($locale instanceof UcdZend_Config) {
            $locale = $locale->toArray();
        }

        if (is_array($locale)) {
            if (array_key_exists('locale', $locale)) {
                $locale = $locale['locale'];
            } else {
                $locale = null;
            }
        }

        if (empty($locale)) {
            require_once 'UcdZend/Registry.php';
            if (UcdZend_Registry::isRegistered('UcdZend_Locale')) {
                $locale = UcdZend_Registry::get('UcdZend_Locale');
            }
        }

        $this->setLocale($locale);
    }

    /**
     * Returns the set locale
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * Sets the locale to use
     *
     * @param string|UcdZend_Locale $locale
     */
    public function setLocale($locale = null)
    {
        require_once 'UcdZend/Locale.php';
        $this->_locale = UcdZend_Locale::findLocale($locale);
        return $this;
    }

    /**
     * Defined by UcdZend_Validate_Interface
     *
     * Returns true if and only if $value is a floating-point value
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        if (is_float($value)) {
            return true;
        }

        $this->_setValue($value);
        try {
            if (!UcdZend_Locale_Format::isFloat($value, array('locale' => $this->_locale))) {
                $this->_error(self::NOT_FLOAT);
                return false;
            }
        } catch (UcdZend_Locale_Exception $e) {
            $this->_error(self::NOT_FLOAT);
            return false;
        }

        return true;
    }
}
