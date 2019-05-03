<?php
require_once UCD_APPLICATION_PATH . '/forms/BaseAbstract.php';

class Ucd_Form_Main extends Ucd_Form_BaseAbstract
{
    public function init()
    {
        parent::init();

        $this->_persistence = Ucd_Application::getModel('Options');

        // Custom field
        $this->addElement('checkbox', 'ajax_mode', array(
            'label' => 'Check this box, only if you are using a caching plugin',
        ));
    }

    /**
    * Returns data for passing to JS code
    */
    public function getUiData()
    {
        $result = parent::getUiData();

        $result['form'] = 'main';

        return $result;
    }
}