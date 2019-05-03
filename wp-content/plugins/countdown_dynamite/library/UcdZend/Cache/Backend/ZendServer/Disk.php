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
 * @package    UcdZend_Cache
 * @subpackage UcdZend_Cache_Backend
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Disk.php 24593 2012-01-05 20:35:02Z matthew $
 */


/** @see UcdZend_Cache_Backend_Interface */
require_once 'UcdZend/Cache/Backend/Interface.php';

/** @see UcdZend_Cache_Backend_ZendServer */
require_once 'UcdZend/Cache/Backend/ZendServer.php';


/**
 * @package    UcdZend_Cache
 * @subpackage UcdZend_Cache_Backend
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class UcdZend_Cache_Backend_ZendServer_Disk extends UcdZend_Cache_Backend_ZendServer implements UcdZend_Cache_Backend_Interface
{
    /**
     * Constructor
     *
     * @param  array $options associative array of options
     * @throws UcdZend_Cache_Exception
     */
    public function __construct(array $options = array())
    {
        if (!function_exists('UcdZend_disk_cache_store')) {
            UcdZend_Cache::throwException('UcdZend_Cache_ZendServer_Disk backend has to be used within Zend Server environment.');
        }
        parent::__construct($options);
    }

    /**
     * Store data
     *
     * @param mixed  $data        Object to store
     * @param string $id          Cache id
     * @param int    $timeToLive  Time to live in seconds
     * @return boolean true if no problem
     */
    protected function _store($data, $id, $timeToLive)
    {
        if (UcdZend_disk_cache_store($this->_options['namespace'] . '::' . $id,
                                  $data,
                                  $timeToLive) === false) {
            $this->_log('Store operation failed.');
            return false;
        }
        return true;
    }

    /**
     * Fetch data
     *
     * @param string $id          Cache id
     */
    protected function _fetch($id)
    {
        return UcdZend_disk_cache_fetch($this->_options['namespace'] . '::' . $id);
    }

    /**
     * Unset data
     *
     * @param string $id          Cache id
     * @return boolean true if no problem
     */
    protected function _unset($id)
    {
        return UcdZend_disk_cache_delete($this->_options['namespace'] . '::' . $id);
    }

    /**
     * Clear cache
     */
    protected function _clear()
    {
        UcdZend_disk_cache_clear($this->_options['namespace']);
    }
}
