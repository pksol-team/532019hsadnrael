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
 * @version    $Id: Regex.php 22697 2010-07-26 21:14:47Z alexander $
 */

/**
 * @see UcdZend_Validate_Abstract
 */
require_once 'UcdZend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    UcdZend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class UcdZend_Validate_Regex extends UcdZend_Validate_Abstract
{
    const INVALID   = 'regexInvalid';
    const NOT_MATCH = 'regexNotMatch';
    const ERROROUS  = 'regexErrorous';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Invalid type given, value should be string, integer or float",
        self::NOT_MATCH => "'%value%' does not match against pattern '%pattern%'",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'pattern' => '_pattern'
    );

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern;

    /**
     * Sets validator options
     *
     * @param  string|UcdZend_Config $pattern
     * @throws UcdZend_Validate_Exception On missing 'pattern' parameter
     * @return void
     */
    public function __construct($pattern)
    {
        if ($pattern instanceof UcdZend_Config) {
            $pattern = $pattern->toArray();
        }

        if (is_array($pattern)) {
            if (array_key_exists('pattern', $pattern)) {
                $pattern = $pattern['pattern'];
            } else {
                require_once 'UcdZend/Validate/Exception.php';
                throw new UcdZend_Validate_Exception("Missing option 'pattern'");
            }
        }

        $this->setPattern($pattern);
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * Sets the pattern option
     *
     * @param  string $pattern
     * @throws UcdZend_Validate_Exception if there is a fatal error in pattern matching
     * @return UcdZend_Validate_Regex Provides a fluent interface
     */
    public function setPattern($pattern)
    {
        $this->_pattern = (string) $pattern;
        $status         = @preg_match($this->_pattern, "Test");

        if (false === $status) {
            require_once 'UcdZend/Validate/Exception.php';
            throw new UcdZend_Validate_Exception("Internal error while using the pattern '$this->_pattern'");
        }

        return $this;
    }

    /**
     * Defined by UcdZend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
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

        $this->_setValue($value);

        $status = @preg_match($this->_pattern, $value);
        if (false === $status) {
            $this->_error(self::ERROROUS);
            return false;
        }

        if (!$status) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}
