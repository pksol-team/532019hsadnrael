<?php
require_once UCD_APPLICATION_PATH . '/models/AbstractModel.php';

class Ucd_Model_Block_Widget extends Ucd_Model_AbstractModel
{
    protected $_context; // Options or PostMeta model

    protected $_postId;
    protected $_enabled;
    protected $_custom;

    protected $_visitor;

    protected $_expired;

    protected $_elements;

    public function setPostId($postId)
    {
        $this->_postId = $postId;

        if (0 != $postId) {
            $meta = Ucd_Application::getModel('PostMeta')->setPostId($postId);

            $this->_enabled = (bool) $meta->getValue('enabled');
            $this->_custom = (bool) $meta->getValue('custom');
            $this->_context = $this->_custom
                ? $meta
                : Ucd_Application::getModel('Options');
        } else {
            $this->_enabled = TRUE;
            $this->_custom = FALSE;
            $this->_context = Ucd_Application::getModel('Options');
        }

        return $this;
    }

    public function setPreviewPostId($postId=NULL)
    {
        $this->_enabled = TRUE;

        if (!is_null($postId)) {
            $meta = Ucd_Application::getModel('PostMeta')->setPostId($postId);
            $this->_custom = (bool) $meta->getValue('custom');
            $this->_context = $this->_custom
                ? $meta
                : Ucd_Application::getModel('Options');
        } else {
            $this->_custom = FALSE;
            $this->_context = Ucd_Application::getModel('Options');
        }

        return $this;
    }

    public function setVisitorInfo(array $visitorInfo)
    {
        if (is_null($this->_postId)) {
            throw new Exception('setVisitorInfo() requires postId initialized');
        }

        $visitorInfo['post_id'] = $this->_postId;

        $this->_visitor = Ucd_Application::getModel('Data_Visitor')
                ->loadByVisitorInfo($visitorInfo);

        return $this;
    }

    public function isExpired()
    {
        if (is_null($this->_expired)) {
            if ($this->_visitor) {
                $type = $this->getValue('expiration_type');

                $now = Ucd_Application::getService('Countdown')->getTime();

                switch ($type) {
                    case 'date':
                        $this->_expired = $now >= $this->getExpirationTimestamp();
                        break;
                    case 'duration':
                        $this->_expired = !$this->_visitor->isFirstVisit()
                            && $now >= $this->getExpirationTimestamp();

                        // Check for restart
                        if ($this->_expired && $this->getValue('restart')
                            && $now >= $this->getExpirationTimestamp() + $this->getValue('restart_duration')

                        ) {
                            $this->_expired = FALSE;
                            $this->_visitor->start();
                        }

                        break;
                    case 'once':
                        $this->_expired = !$this->_visitor->isFirstVisit()
                            && ($now - $this->_visitor->getFirstVisit()
                                > Ucd_Application::getConfig()->visitor->once->timeout);
                        break;
                }
            } else {
                $this->_expired = FALSE;
            }
        }

        return $this->_expired;
    }

    public function isFirstVisit()
    {
        return $this->_visitor->isFirstVisit();
    }

    public function isInvalid()
    {
        return $this->_custom && $this->getValue('errors');
    }

    //
    // Template helpers
    //

    public function getCssStyles()
    {
        $result = array();
        $cfg = Ucd_Application::getConfig()->counter;

        // Font family
        $value = $this->getValue('appearance_font_family');
        if ('' != $value) {
            $result['font-family'] = (string) $cfg->fonts->$value->family;
        }
        // Color
        $value = $this->getValue('appearance_color');
        if ('' != $value) {
            $result['color'] = $value;
        }
        // Background Color
        $value = $this->getValue('appearance_background_color');
        if ('' != $value) {
            $result['background-color'] = $value;
        }
        // Bold
        $value = $this->getValue('appearance_font_bold');
        if ($value) {
            $result['font-weight'] = 'bold';
        }
        // Italic
        $value = $this->getValue('appearance_font_italic');
        if ($value) {
            $result['font-style'] = 'italic';
        }

        return $result;
    }

    public function getElements()
    {
        if (is_null($this->_elements)) {
            $visible = (array) $this->getValue('elements_visible');

            foreach (Ucd_Application::getConfig()->counter->elements as $key=>$info) {
                $this->_elements[$key] = array(
                    'label' => $this->getValue("element_label_{$key}"),
                    'visible' => in_array($key, $visible),
                );
            }
        }

        return $this->_elements;
    }

    public function getExpirationTimestamp()
    {
        switch ($this->getValue('expiration_type')) {
            case 'date':
                return $this->getValue('expiration_timestamp');
            case 'duration':
                return $this->_visitor->getFirstVisit() + $this->getValue('expiration_duration');
        }

        return Ucd_Application::getService('Countdown')->getTime();
    }

    public function getDefaultOptions()
    {
        return Ucd_Application::getConfig()->counter->widget->options->toArray();
    }

    //
    // Filter
    //

    public function filterContent($content)
    {
        if (!$this->getEnabled()) {
            return $content;
        }

        $above = NULL;
        $below = NULL;
        if (!$this->isExpired()) {
            $above = $this->getValue('extra_content_above');
            $below = $this->getValue('extra_content');
        }

        $actionContent = NULL;
        if ('content' == $this->getValue('action_type')) {
            $actionContent = $this->getValue('action_content');
        }

        if ('' == $above && '' == $below && '' == $actionContent) {
            return $content;
        }

        $replace = $this->_renderExtraContent($above, 'ucd-countdown-content ucd-countdown-extra-content ucd-countdown-extra-content-above')
            . '\\0'
            . $this->_renderExtraContent($actionContent, 'ucd-countdown-content ucd-countdown-action-content' . (
                    !$this->isExpired()? ' ucd-countdown-content-hidden' : ''
                ))
            . $this->_renderExtraContent($below, 'ucd-countdown-content ucd-countdown-extra-content ucd-countdown-extra-content-below');
        
        $result = preg_replace(
            '~<p\b[^>]*>(?!:<p\b)*?\[countdown-dynamite\b.*?</p>~isu',
            $replace,
            $content,
            -1,
            $count
        );

        // Fallback
        if (is_null($content) || !$count ||  $content == $result) {
            $result = preg_replace('~\[countdown-dynamite\b[^\]]*\]~isu',
                $replace, $content);
        }

        return $result;
    }

    protected function _renderExtraContent($content, $class)
    {
        if ('' == $content) {
            return '';
        }

        ob_start();

        ?>
        <div class="<?php echo $class; ?>"
             data-post-id="<?php echo $this->getPostId(); ?>"
        >
            <?php echo do_shortcode($content); ?>
        </div>

        <?php

        $result = ob_get_clean();

        return preg_replace('~\$(\d)~isu', '\\\$$1', $result);
    }

    //
    // Getters
    //

    public function getPostId()
    {
        return $this->_postId;
    }

    public function getEnabled()
    {
        return $this->_enabled;
    }

    public function getCustom()
    {
        return $this->_custom;
    }

    public function getValue($name)
    {
        return $this->_context->getValue($name);
    }

    public function getVisitor()
    {
        return $this->_visitor;
    }

    static public function isSingleton()
    {
        return FALSE;
    }
}