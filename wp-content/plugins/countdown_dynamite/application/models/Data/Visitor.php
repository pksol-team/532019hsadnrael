<?php
require_once UCD_APPLICATION_PATH . '/models/Data/ObjectAbstract.php';

class Ucd_Model_Data_Visitor extends Ucd_Model_Data_ObjectAbstract
{
    protected $_tableName = 'visitor';

    protected $_datetimeFields = array(
        'first_visit',
    );
    protected $_utcTime = TRUE;

    protected $_visitorInfoFields = array(
        'post_id',
        'cookie_value',
        'ip_address',
    );

    protected $_isFirstVisit;

    static protected $_cleanupChecked = FALSE;

    public function __construct()
    {
        parent::__construct();

        if (!self::$_cleanupChecked) {
            $this->cleanup();
            self::$_cleanupChecked = TRUE;
        }
    }

    public function loadByVisitorInfo(array $fields)
    {
        foreach ($this->_visitorInfoFields as $field) {
            if (!isset($fields[$field])) {
                throw new Exception("Required field '{$field}' not found");
            }
        }

        $this->clearValues();

        // First try to find the visit by cookie
        if ('' != $fields['cookie_value']) {
            $this->loadByFields($this->_arrayExtractKeys($fields,
                array('post_id', 'cookie_value')));
        }

        // Then try by IP
        if (!$this->getId()) {
            $this->loadByFields($this->_arrayExtractKeys($fields,
                array('post_id', 'ip_address')));
        }

        // If still not found then this is the first visit
        if (!$this->getId()) {
            $this->setValues($fields);
            $this->start();
        } else {
            $this->_isFirstVisit = FALSE;
        }

        return $this;
    }

    public function start()
    {
        $this->_isFirstVisit = TRUE;
        // Assign current date as first visit and save
        $this->setValue('first_visit',
            Ucd_Application::getService('Countdown')->getTime());

        $this->save();

        return $this;
    }

    /**
    * Retruns given keys from array (cannot find a built-in function)
    *
    * @param array $array Array to extract values from
    * @param array $keys Array of keys to extract
    */
    protected function _arrayExtractKeys(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    //
    // Utilities
    //

    public function cleanup()
    {
        global $wpdb;

        $cfg = Ucd_Application::getConfig()->visitor;

        $now = Ucd_Application::getService('Countdown')->getTime();

        $options = Ucd_Application::getModel('Options');
        if ($options->getValue('visitor_last_cleanup')
            > $now - 86400 * $cfg->cleanupInterval
        ) {
            return;
        }

        $lifetime = (int) $cfg->lifetime;
        $date = gmdate(self::DATETIME_FORMAT, strtotime("-{$lifetime} days"));

        $result = $wpdb->query("DELETE FROM `{$this->_table}` WHERE `first_visit` < '{$date}'");

        $options->setValue('visitor_last_cleanup', $now);

        return $this;
    }

    public function clearHistory($visitorInfo)
    {
        global $wpdb;

        foreach ($visitorInfo as $name=>$val) {
            $where[] =  "`{$name}` = '" . esc_sql($val) . "'";
        }
        $where = implode(' OR ', $where);

        $result = $wpdb->query(
            "DELETE FROM `{$this->_table}` WHERE {$where}");

        return $this;
    }

    //
    // Getters & setters
    //

    public function isFirstVisit()
    {
        return $this->_isFirstVisit;
    }
}