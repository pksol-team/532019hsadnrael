<?php
require_once UCD_APPLICATION_PATH . '/models/AbstractModel.php';

class Ucd_Model_PostMeta extends Ucd_Model_AbstractModel
{
    const PREFIX = Ucd_WpPlugin::PREFIX;

    protected $_postId;

    public function getValue($name, $default=NULL)
    {
        $this->_checkPostId();

        $result = get_post_meta($this->_postId, self::PREFIX . '_' . $name, TRUE);
        if ('' == $result) {
            if (!is_null($default)) {
                $result = $default;
            } else {
                $result = Ucd_Application::getConfig()->postMetaDefaults->$name;
                if ($result instanceof UcdZend_Config) {
                    $result = $result->toArray();
                }
            }
        }

        return $result;
    }

    public function setValue($name, $value)
    {
        $this->_checkPostId();

        update_post_meta($this->_postId, self::PREFIX . '_' . $name, $value);

        return $this;
    }

    public function unsetValue($name)
    {
        $this->_checkPostId();
        delete_post_meta($this->_postId, self::PREFIX . '_' . $name);

        return $this;
    }

    public function setPostId($value)
    {
        $this->_postId = $value;
        return $this;
    }

    public function getPostId()
    {
        return $this->_postId;
    }

    protected function _checkPostId()
    {
        if (!$this->_postId) {
            throw new Exception('Post ID not specified');
        }
    }

    //
    // Not a singleton to allow multiple instances connected to different posts
    // to coexist
    //

    static public function isSingleton()
    {
        return FALSE;
    }
}