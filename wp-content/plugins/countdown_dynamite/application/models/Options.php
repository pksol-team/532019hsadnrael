<?php
require_once UCD_APPLICATION_PATH . '/models/AbstractModel.php';

class Ucd_Model_Options extends Ucd_Model_AbstractModel
{
    const PREFIX = Ucd_WpPlugin::PREFIX;

    public function getValue($name, $default=NULL)
    {
        if (is_null($default)) {
            $default = Ucd_Application::getConfig()->optionDefaults->$name;
            if ($default instanceof UcdZend_Config) {
                $default = $default->toArray();
            }
        }

        return get_option(self::PREFIX . '_' . $name, $default);
    }

    public function setValue($name, $value)
    {
        update_option(self::PREFIX . '_' . $name, $value);

        return $this;
    }

    public function unsetValue($name)
    {
        return delete_option(self::PREFIX . '_' . $name);

        return $this;
    }
}
