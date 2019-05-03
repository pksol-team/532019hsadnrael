<?php
require_once UCD_APPLICATION_PATH . '/forms/BaseAbstract.php';

class Ucd_Form_Metabox extends Ucd_Form_BaseAbstract
{
    /**
    * Fields that do not override general settings
    *
    * @var mixed
    */
    protected $_localFields = array(
        'custom',
        'enabled'
    );

    protected $_post;
    protected $_postId;

    protected $_defaultValues;

    protected $_defaultValuesInvalid = FALSE;

    public function init()
    {
        parent::init();

        $this->addElement('checkbox', 'custom', array(
            'label' => 'Use custom settings for this post/page'
        ));

        $this->addElement('checkbox', 'enabled', array(
            'label' => 'Enable countdown on this post/page'
        ));

        // Save required as attribute as it could be replaced
        foreach ($this->getElements() as $elem) {
            $elem->setAttrib('-required', $elem->isRequired());
        }
    }

    //
    // Metabox specific methods
    //

    public function load()
    {
        $custom = $this->_persistence->getValue('custom');
        $this->setValue('custom', $custom);
        if ($custom) {
            return parent::load();
        }

        $data = $this->getDefaultValues();
        $data['enabled'] = $this->_persistence->getValue('enabled');
        $this->onLoad($data);
        $this->setValues($data);

        return $this;
    }

    public function save()
    {
        if ($this->getValue('custom')) {
            return parent::save();
        }

        // Default values: save local fields only and erase other fields
        foreach ($this->getElements() as $key=>$elem) {
            if (in_array($key, $this->_localFields)) {
                $this->_persistence->setValue($key, $this->getValue($key));
            } else {
                $this->_persistence->unsetValue($key);
            }
        }

        return $this;
    }

    public function onUpdate()
    {
        if (!$this->getValue('custom')) {
            foreach ($this->getDefaultValues() as $key=>$value) {
                $this->getElement($key)
                    ->setValue($value)
                    ->setAttrib('-disabled', TRUE);
            }
        } else {
            foreach ($this->getElements() as $key=>$elem) {
                if (in_array($key, $this->_localFields)) {
                    continue;
                }
                $elem->setAttrib('-disabled', FALSE);
            }
        }

        parent::onUpdate();
    }

    public function isValid($data)
    {
        $result = parent::isValid($data);

        if (!$result) {
            if (!$this->getValue('enabled')) {
                // Ignore invalid values if not enabled
                $result = TRUE;
            } else if (!$this->getValue('custom')) {
                $this->_defaultValuesInvalid = TRUE;
            }
        }

        return $result;
    }

    //
    // Getters and setters
    //

    public function setPost($post)
    {
        $this->_post = $post;
        $this->setPostId($post->ID);

        $postTypes = Ucd_Application::getModel('PostType')->getList();
        $postType = strtolower($postTypes[$post->post_type]);

        foreach ($this->_localFields as $field) {
            $elem = $this->getElement($field);
            $elem->setLabel(str_replace('post/page', $postType, $elem->getLabel()));
        }

        return $this;
    }

    public function setPostId($value)
    {
        $this->_postId = $value;

        $this->_persistence = ucd_Application::getModel('PostMeta')
            ->setPostId($value);

        return $this;
    }

    public function getPostId()
    {
        return $this->_postId;
    }

    //
    // Getters & setters
    //

    public function getLocalFields()
    {
        return $this->_localFields;
    }

    public function getDefaultValues()
    {
        if (is_null($this->_defaultValues)) {
            $this->_defaultValues = array();

            $options = Ucd_Application::getModel('Options');

            foreach ($this->getElements() as $key=>$elem) {
                if (in_array($key, $this->_localFields)) {
                    continue;
                }

                $this->_defaultValues[$key] = $options->getValue($key);
            }
        }

        return $this->_defaultValues;
    }

    public function getDefaultValuesInvalid()
    {
        return $this->_defaultValuesInvalid;
    }

    /**
    * Returns data for passing to JS code
    */
    public function getUiData()
    {
        $result = parent::getUiData();

        $result['form'] = 'metabox';
        $result['defaultValues'] = $this->getDefaultValues();

        return $result;
    }
}