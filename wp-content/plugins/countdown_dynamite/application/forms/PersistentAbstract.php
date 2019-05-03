<?php
require_once 'UcdZend/Form.php';

abstract class Ucd_Form_PersistentAnstract extends UcdZend_Form
{
    protected $_persistence;

    public function load()
    {
        $data = array();
        foreach ($this->getElements() as $name=>$elem) {
            if (FALSE === $elem->getAttrib('-persistent')
                || FALSE === $elem->getAttrib('-persistent-load')
            ) {
                continue;
            }

            $data[$name] = $this->_loadValue($name);
        }

        $this->onLoad($data);

        $this->setValues($data);

        return $this;
    }

    public function save()
    {
        $data = array();
        foreach ($this->getElements() as $name=>$elem) {
            if (FALSE === $elem->getAttrib('-persistent')
                || FALSE === $elem->getAttrib('-persistent-save')
            ) {
                continue;
            }

            $data[$name] = $elem->getValue();
        }

        $this->onSave($data);

        foreach ($data as $name=>$value) {
            $this->_saveValue($name, $value);
        }

        return $this;
    }

    protected function _loadValue($name)
    {
        return $this->_persistence->getValue($name);
    }

    protected function _saveValue($name, $value)
    {
        return $this->_persistence->setValue($name, $value);
    }

    public function setDefaults(array $data)
    {
        parent::setDefaults($data);

        $this->onUpdate();

        return $this;
    }

    public function isValid($data)
    {
        foreach ($this->getElements() as $key=>$elem) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = NULL;
            }
        }

        $this->setDefaults($data);

        $this->beforeValidate();

        return parent::isValid($this->getValues());
    }

    public function setValue($name, $value)
    {
        return $this->setDefault($name, $value);
    }

    public function setValues($data)
    {
        return $this->setDefaults($data);
    }

    //
    // Events
    //

    public function onUpdate()
    {
        // Extensions
    }

    public function onLoad(&$data)
    {
        // Extensions
    }

    public function onSave(&$data)
    {
        // Extensions
    }

    public function beforeValidate()
    {

    }
}