<?php
require_once UCD_APPLICATION_PATH . '/models/AbstractModel.php';

abstract class Ucd_Model_Data_ObjectAbstract extends Ucd_Model_AbstractModel
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';

    protected $_tableName;
    protected $_table;

    protected $_idField = 'id';

    protected $_values = array();

    protected $_datetimeFields = array();
    protected $_dateFields = array();
    protected $_utcTime = FALSE;

    protected $_modified = FALSE;

    protected static $_underscoreCache = array();

    public function __construct()
    {
        $this->_table = Ucd_WpPlugin::getTablePrefix() . $this->_tableName;
    }

    public function getValues()
    {
        return $this->_values;
    }

    public function getValue($key)
    {
        return $this->hasValue($key) ? $this->_values[$key] : NULL;
    }

    public function hasValue($key)
    {
        return array_key_exists($key, $this->_values);
    }

    public function setValue($key, $value)
    {
        $this->_values[$key] = $value;
        $this->_modified = TRUE;

        return $this;
    }

    public function setValues(array $values)
    {
        $this->_values = $values;
        $this->_modified = TRUE;

        return $this;
    }

    public function unsetValue($key)
    {
        unset($this->_values[$key]);
        $this->_modified = TRUE;

        return $this;
    }

    public function clearValues()
    {
        $this->_values = array();
        $this->_modified = FALSE;

        return $this;
    }

    public function __call($method, $args)
    {
        $key = $this->_underscore(substr($method, 3));

        switch (substr($method, 0, 3)) {
            case 'get' :
                return $this->getValue($key);

            case 'set' :
                return $this->setValue($key, $args[0]);

            case 'uns' :
                return $this->unsetValue($key);

            case 'has' :
                return $this->hasValue($key);
        }

        throw new Exception("Invalid method '{$method}'");
    }

    public function getId()
    {
        return $this->getValue($this->_idField);
    }

    public function setId($value)
    {
        return $this->setValue($this->_idField, $value);
    }

    public function load($id)
    {
        global $wpdb;

        return $this->loadData($wpdb->get_row(
            "SELECT * FROM `{$this->_table}`
            WHERE `id` = {$id}",
            ARRAY_A
        ));
    }

    public function loadByField($field, $value)
    {
        global $wpdb;

        return $this->loadData($wpdb->get_row(
            "SELECT * FROM `{$this->_table}` WHERE `{$field}`='{$value}'", ARRAY_A
        ));
    }

    public function loadByFields($fields, $any=FALSE)
    {
        global $wpdb;

        foreach ($fields as $name=>$val) {
            $where[] =  "`{$name}` = '" . esc_sql($val) . "'";
        }
        $where = implode($any? ' OR ' : ' AND ', $where);

        $row = $wpdb->get_row(
            "SELECT * FROM `{$this->_table}` WHERE {$where}", ARRAY_A
        );
        $this->loadData($row? $row : $fields);

        return $this;
    }

    public function loadData($data)
    {
        $this->_values = $this->_formatLoadedData($data);
        $this->_modified = FALSE;

        return $this;
    }

    protected function _formatLoadedData($data)
    {
        foreach ($this->_datetimeFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->_strToTime($data[$field]);
            }
        }
        foreach ($this->_dateFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->_strToTime($data[$field]);
            }
        }

        return $data;
    }

    protected function _strToTime($value)
    {
        if ($this->_utcTime) {
            $value .= ' UTC';
        }

        return strtotime($value);
    }

    public function save()
    {
        global $wpdb;

        $data = $this->_formatSavedData($this->_values);

        if ($id = $this->getId()) {
            $wpdb->update(
                $this->_table,
                $data,
                array('id' => $id)
            );
        } else {
            if ($wpdb->insert($this->_table, $data)) {
                $this->setId($wpdb->insert_id);
            } else {
                throw new Exception("Cannot insert record to '{$this->_table}' table: {$wpdb->last_error}");
            }
        }

        $this->_modified = FALSE;

        return $this;
    }

    protected function _formatSavedData($data)
    {
        foreach ($this->_datetimeFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->_utcTime
                    ? gmdate(self::DATETIME_FORMAT, $data[$field])
                    : date(self::DATETIME_FORMAT, $data[$field]);
            }
        }
        foreach ($this->_dateFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->_utcTime
                    ? gmdate(self::DATE_FORMAT, $data[$field])
                    : date(self::DATE_FORMAT, $data[$field]);
            }
        }

        return $data;
    }

    /*
     * Return Id row containing field $name==$val
     */
    public function lookup($field, $value, $fieldToReturn=NULL)
    {
        global $wpdb;

        if (is_null($fieldToReturn)) {
            $fieldToReturn = $this->_idField;
        }

        $result = $wpdb->get_var(
            "SELECT `{$fieldToReturn}` FROM `{$this->_table}`
            WHERE `{$field}` = '" . esc_sql($value) . "}' LIMIT 1"
        );

        return $result;
    }

    public function delete()
    {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM `{$this->_table}`
            WHERE `id` = {$this->getId()}"
        );

        return $this;
    }

    public function isModified()
    {
        return $this->_modified;
    }

    public function findMaxId()
    {
        global $wpdb;

        $id = $wpdb->get_var(
            "SELECT `id` FROM `{$this->_table}`
            ORDER BY `id` DESC LIMIT 1"
        );

        return $id;
    }

    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    protected function _camelize($name)
    {
        return uc_words($name, '');
    }

    public function getTable()
    {
        return $this->_table;
    }

    public function getTableName()
    {
        return $this->_tableName;
    }

    public function loadList(array $list, $ignoreNotFound=FALSE)
    {
        $result = array();
        foreach ($list as $id) {
            $item = clone $this;
            $item->load($id);
            if ($item->getId()) {
                $result[$id] = $item;
            } else {
                if (!$ignoreNotFound) {
                    throw new Exception("ID '{$id}' not found");
                }
            }
        }

        return $result;
    }

    //
    // Not singleton
    //

    static public function isSingleton()
    {
        return FALSE;
    }
}