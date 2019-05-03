<?php
require_once UCD_APPLICATION_PATH . '/forms/PersistentAbstract.php';

require_once 'UcdPumka/Filter/StripSlashes.php';
require_once 'UcdPumka/Filter/UriNormalize.php';
require_once 'UcdPumka/Validate/Url.php';

abstract class Ucd_Form_BaseAbstract extends Ucd_Form_PersistentAnstract
{
    protected $_optionDependentFields = array(
        'expiration_type' => array(
            'date' => array(
                'expiration_date',
                'expiration_date_timezone_base',
                'expiration_date_timezone_offset_hours',
                'expiration_date_timezone_offset_minutes',
            ),
            // 'duration' fields are added in init()
        ),
        'action_type' => array(
            'content' => array(
                // 'action_content',     // no longer required per request
            ),
            'redirect' => array(
                'action_redirect_url',
            ),
        ),
        'restart' => array(
            '1' => array(),
        )
    );

    protected $_counterElements;

    public function init()
    {
        $cfg = Ucd_Application::getConfig()->counter;

        $this->_counterElements = $cfg->elements->toArray();

        //
        // Fields
        //

        $this->addElement('radio', 'expiration_type', array(
            'required' => TRUE,
            'multiOptions' => array(
                'date' => 'on this exact date',
                'duration' => 'after this time duration',
                'once' => 'after visitor sees the content once only',
            ),
            'label' => 'Expire Countdown',
        ));

        $this->addElement('hidden', 'expiration_timestamp', array(
            'label' => 'Expiration Date',
            '-persistent-load' => FALSE,
            '-help' => FALSE,
        ));

        $this->addElement('text', 'expiration_date', array(
            'filters' => array(
                'StripNewlines',
                'StringTrim',
                'StringToLower',
            ),
            'validators' => array(
                array('Date', TRUE, array(
                    'format' =>  'yyyy-MM-dd hh:mm',
                )),
            ),
            'label' => 'Expiration Date',
            '-help' => FALSE,
        ));

        $this->addElement('select', 'expiration_date_timezone_base', array(
            'multiOptions' => array(
                'America/New_York' => 'EST (EDT)',
                'UTC' => 'UTC (GMT)',
            ),
            'label' => 'Your Timezone',
            '-help' => FALSE,
        ));

        $this->addElement('select', 'expiration_date_timezone_offset_hours', array(
            'multiOptions' => array(
                '-12' => '-12',
                '-11' => '-11',
                '-10' => '-10',
                '-09' => '-09',
                '-08' => '-08',
                '-07' => '-07',
                '-06' => '-06',
                '-05' => '-05',
                '-04' => '-04',
                '-03' => '-03',
                '-02' => '-02',
                '-01' => '-01',
                '00' => ' 00',
                '01' => '+01',
                '02' => '+02',
                '03' => '+03',
                '04' => '+04',
                '05' => '+05',
                '06' => '+06',
                '07' => '+07',
                '08' => '+08',
                '09' => '+09',
                '10' => '+10',
                '11' => '+11',
                '12' => '+12',
            ),
            'label' => 'Your Timezone - Hours Offset',
            '-help' => FALSE,
        ));

        $this->addElement('select', 'expiration_date_timezone_offset_minutes', array(
            'multiOptions' => array(
                '-45' => '-45',
                '-30' => '-30',
                '-15' => '-15',
                '00' => ' 00',
                '15' => '+15',
                '30' => '+30',
                '45' => '+45',
            ),
            '-multiOptionsBasic' => array(
                '00' => '00',
                '15' => '15',
                '30' => '30',
                '45' => '45',
            ),
            'label' => 'Your Timezone - Minutes Offset',
            '-help' => FALSE,
        ));

        $this->addElement('hidden', 'expiration_duration', array(
            'label' => 'Expiration Duration',
            '-persistent-load' => FALSE,
            '-help' => FALSE,
        ));

        foreach ($this->getCounterElements() as $key) {
            $name = "expiration_duration_{$key}";
            $this->addElement('text', $name, array(
                'filters' => array(
                    'StripNewlines',
                    'StringTrim',
                    'StringToLower',
                    'Digits',
                    'Int',
                ),
                'validators' => array(
                    array('Between', TRUE, array(
                        'min' => 0,
                        'max' => $this->getCounterElementProperty($key, 'limit'),
                    ))
                ),
                'label' => "Expiration Duration - {$this->getCounterElementProperty($key, 'label')}",
                '-help' => FALSE,
            ));

            $this->_optionDependentFields['expiration_type']['duration'][] = $name;
        }

        $this->addElement('checkbox', 'restart', array(
            'label' => 'Restart Countdown',
        ));

        $this->addElement('hidden', 'restart_duration', array(
            'label' => 'Restart Duration',
            '-persistent-load' => FALSE,
            '-help' => FALSE,
        ));

        foreach ($this->getCounterElements() as $key) {
            $name = "restart_duration_{$key}";
            $this->addElement('text', $name, array(
                'filters' => array(
                    'StripNewlines',
                    'StringTrim',
                    'StringToLower',
                    'Digits',
                    'Int',
                ),
                'validators' => array(
                    array('Between', TRUE, array(
                        'min' => 0,
                        'max' => $this->getCounterElementProperty($key, 'limit'),
                    ))
                ),
                'label' => "Restart Duration - {$this->getCounterElementProperty($key, 'label')}",
                '-help' => FALSE,
            ));

            $this->_optionDependentFields['restart']['1'][] = $name;
        }

        $this->addElement('radio', 'action_type', array(
            'required' => TRUE,
            'multiOptions' => array(
                'content' => 'show this content below countdown',
                'redirect' => 'redirect to this URL',
            ),
            'label' => 'Action After Expiration',
        ));

        $this->addElement('textarea', 'action_content', array(
            'label' => 'Content',
            'filters' => array(
                new UcdPumka_Filter_StripSlashes,
            ),
            '-help' => FALSE,
        ));

        $this->addElement('checkbox', 'action_keep_visible', array(
            'label' => 'After the deadline expires, keep the countdown timer visible',
        ));

        $this->addElement('text', 'action_redirect_url', array(
            'filters' => array(
                'StripNewlines',
                'StringTrim',
                new UcdPumka_Filter_UriNormalize,
            ),
            'validators' => array(
                new UcdPumka_Validate_Url,
            ),
            'label' => 'Redirect URL',
            '-help' => FALSE,
        ));

        $this->addElement('textarea', 'extra_content_above', array(
            'label' => 'Above Countdown',
            'filters' => array(
                new UcdPumka_Filter_StripSlashes,
            ),
            '-help' => FALSE,
        ));

        $this->addElement('textarea', 'extra_content', array(
            'label' => 'Below Countdown',
            'filters' => array(
                new UcdPumka_Filter_StripSlashes,
            ),
            '-help' => FALSE,
        ));

        $options = array();
        foreach ($cfg->fonts as $key=>$info) {
            $options[$key] = (string) $info->label;
        }
        $this->addElement('select', 'appearance_font_family', array(
            'multiOptions' => $options,
            'label' => 'Font Face',
            '-empty-label' => '(regular text font)',
            '-help' => FALSE,
        ));

        $options = array();
        foreach ($cfg->sizes as $key=>$info) {
            $options[$key] = (string) $info->label;
        }
        $this->addElement('select', 'appearance_size', array(
            'required' => TRUE,
            'multiOptions' => $options,
            'label' => 'Size',
            '-help' => FALSE,
        ));

        $this->addElement('text', 'appearance_color', array(
            'filters' => array(
                'StripNewlines',
                'StringTrim',
                'StringToLower',
            ),
            'validators' => array(
                array('Regex', TRUE, array(
                    'pattern' => '~^#[a-fA-F0-9]{6}$~',
                    'messages' => array('regexNotMatch' => '%value% is not a valid HTML color.'),
                )),
            ),
            'label' => 'Color',
            '-action' => 'forecolor',
            '-default' => '#000000',
            '-empty-label' => 'Regular text color',
            '-help' => FALSE,
        ));

        $this->addElement('text', 'appearance_background_color', array(
            'filters' => array(
                'StripNewlines',
                'StringTrim',
                'StringToLower',
            ),
            'validators' => array(
                array('Regex', TRUE, array(
                    'pattern' => '~^#[a-fA-F0-9]{6}$~',
                    'messages' => array('regexNotMatch' => '%value% is not a valid HTML color.'),
                )),
            ),
            'label' => 'Background Color',
            '-action' => 'backcolor',
            '-default' => '#ffffff',
            '-empty-label' => 'Transparent',
            '-help' => FALSE,
        ));

        $this->addElement('checkbox', 'appearance_font_bold', array(
            'label' => 'Bold',
            '-action' => 'bold',
            '-help' => FALSE,
        ));

        $this->addElement('checkbox', 'appearance_font_italic', array(
            'label' => 'Italic',
            '-action' => 'italic',
            '-help' => FALSE,
        ));

        $this->addElement('multiCheckbox', 'elements_visible', array(
            'multiOptions' => $this->getCounterElementPropertyList('label'),
            'required' => TRUE,
            'validators' => array(
                array('NotEmpty', TRUE, array(
                    'messages' => array('isEmpty' => 'At least one label must be selected.'),
                )),
            ),
            'label' => 'Visibility of Labels',
            '-help' => FALSE,
        ));

        foreach ($this->getCounterElements() as $key) {
            $name = "element_label_{$key}";
            $this->addElement('text', $name, array(
                'required' => TRUE,
                'filters' => array(
                    'StripNewlines',
                    'StringTrim',
                ),
                'label' => "Label Name - {$this->getCounterElementProperty($key, 'label')}",
                '-help' => FALSE,
            ));
        }
    }

    public function isValid($data)
    {
        $result = parent::isValid($data);

        $durationCfg = Ucd_Application::getConfig()->counter->duration;

        if ($result) {
            // Calculate and check expiration timestamp
            $val = $this->_calculateExpirationTimestamp();
            if (!is_null($val)) {
                $now = time();
                // Verify that not a date in past
                if ($now >= $val) {
                    $this->getElement('expiration_date')->addError('Cannot set a date in the past.');
                    $result = FALSE;
                    $val = NULL;
                // Verify that not a way in future
                } else if (($val - $now) > $durationCfg->max->value) {
                    $this->getElement('expiration_date')->addError("Cannot set a date beyond {$durationCfg->max->label} from now.");
                    $result = FALSE;
                    $val = NULL;
                }
            }
            $this->setValue('expiration_timestamp', $val);
        }

        if ($result) {
            // Calculate and check expiration duration
            $durations = $this->_calculateDurations();
            foreach ($durations as $name=>$val) {
                if (!is_null($val)) {
                    // Minimum limit
                    if ($val < $durationCfg->min->value) {
                        $this->getElement($name)->addError('Duration is too short');
                        $result = FALSE;
                        $val = NULL;
                    // Maximum limit
                    } else if ($val > $durationCfg->max->value) {
                        $this->getElement($name)->addError("Duration cannot exceed {$durationCfg->max->label}.");
                        $result = FALSE;
                        $val = NULL;
                    }
                }
                $this->setValue($name, $val);
            }
        }

        return $result;
    }

    public function beforeValidate()
    {
        // Require option dependent fields
        foreach ($this->_optionDependentFields as $name=>$options) {
            $value = $this->getValue($name);
            foreach ($options as $option=>$fields) {
                $required = $option == $value;
                foreach($fields as $field) {
                    $this->getElement($field)->setRequired($required);
                }
            }
        }
    }

    public function onUpdate()
    {
        $expirationOnce = 'once' == $this->getValue('expiration_type');
        $actionTypeElem = $this->getElement('action_type');

        $actionTypeElem->setAttrib('disable', $expirationOnce?
            array('content')
            : array());
        if ($expirationOnce) {
            $actionTypeElem->setValue('redirect');
        }
    }

    protected function _calculateExpirationTimestamp()
    {
        if ('date' != $this->getValue('expiration_type')) {
            return NULL;
        }

        // Format date to ISO
        $result = $this->getValue('expiration_date');
        if ('' == $result) {
            return NULL;
        }
        $result = str_replace(' ', 'T', $result) . ':00';

        // Set base timezone
        date_default_timezone_set($this->getValue('expiration_date_timezone_base'));

        // Init date object
        $result = new UcdZend_Date($result, UcdZend_Date::ISO_8601);

        // Apply timezone offset
        $offsetH = $this->getValue('expiration_date_timezone_offset_hours');
        $offsetM = $this->getValue('expiration_date_timezone_offset_minutes');

        $negative = FALSE;
        if ('-' == $offsetM[0]) {
            $offsetM = substr($offsetM, 1);
            $negative = TRUE;
        } else if ('-' == $offsetH[0]) {
            $offsetH = substr($offsetM, 1);
            $negative = TRUE;
        }
        $offset = "{$offsetH}:{$offsetM}:00";

        if ($negative) {
            $result = $result->add($offset, UcdZend_Date::TIMES);
        } else {
            $result = $result->sub($offset, UcdZend_Date::TIMES);
        }

        return $result->get(UcdZend_Date::TIMESTAMP);
    }

    protected function _calculateDurations()
    {
        if ('duration' != $this->getValue('expiration_type')) {
            return array();
        }

        $result = array();

        $result['expiration_duration']
            = $this->_calculateDuration('expiration_duration');

        if ($this->getValue('restart')) {
            $result['restart_duration']
                = $this->_calculateDuration('restart_duration');
        }

        return $result;
    }

    protected function _calculateDuration($name)
    {
        $result = 0;

        foreach ($this->getCounterElements() as $key) {
            $result += $this->getValue("{$name}_{$key}")
                * $this->getCounterElementProperty($key, 'multiplier');
        }

        return $result;
    }

    //
    // Getters & setters
    //

    // Counter elements

    public function getCounterElements()
    {
        return array_keys($this->_counterElements);
    }

    public function getCounterElementProperty($key, $prop)
    {
        return isset($this->_counterElements[$key][$prop])
            ? $this->_counterElements[$key][$prop]
            : NULL;
    }

    public function getCounterElementPropertyList($prop)
    {
        $result = array();

        foreach ($this->getCounterElements() as $key) {
            $result[$key] = $this->getCounterElementProperty($key, $prop);
        }

        return $result;
    }

    /**
    * Returns data for passing to JS code
    */
    public function getUiData()
    {
        return array(
            'counter' => Ucd_Application::getConfig()->counter->toArray(),
        );
    }
}