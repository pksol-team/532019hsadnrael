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
 * @package    UcdZend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** UcdZend_Form_Decorator_Abstract */
require_once 'UcdZend/Form/Decorator/Abstract.php';

/**
 * UcdZend_Form_Decorator_FormElements
 *
 * Render all form elements registered with current form
 *
 * Accepts following options:
 * - separator: Separator to use between elements
 *
 * Any other options passed will be used as HTML attributes of the form tag.
 *
 * @category   Zend
 * @package    UcdZend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FormElements.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class UcdZend_Form_Decorator_FormElements extends UcdZend_Form_Decorator_Abstract
{
    /**
     * Merges given two belongsTo (array notation) strings
     *
     * @param  string $baseBelongsTo
     * @param  string $belongsTo
     * @return string
     */
    public function mergeBelongsTo($baseBelongsTo, $belongsTo)
    {
        $endOfArrayName = strpos($belongsTo, '[');

        if ($endOfArrayName === false) {
            return $baseBelongsTo . '[' . $belongsTo . ']';
        }

        $arrayName = substr($belongsTo, 0, $endOfArrayName);

        return $baseBelongsTo . '[' . $arrayName . ']' . substr($belongsTo, $endOfArrayName);
    }

    /**
     * Render form elements
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $form    = $this->getElement();
        if ((!$form instanceof UcdZend_Form) && (!$form instanceof UcdZend_Form_DisplayGroup)) {
            return $content;
        }

        $belongsTo      = ($form instanceof UcdZend_Form) ? $form->getElementsBelongTo() : null;
        $elementContent = '';
        $separator      = $this->getSeparator();
        $translator     = $form->getTranslator();
        $items          = array();
        $view           = $form->getView();
        foreach ($form as $item) {
            $item->setView($view)
                 ->setTranslator($translator);
            if ($item instanceof UcdZend_Form_Element) {
                $item->setBelongsTo($belongsTo);
            } elseif (!empty($belongsTo) && ($item instanceof UcdZend_Form)) {
                if ($item->isArray()) {
                    $name = $this->mergeBelongsTo($belongsTo, $item->getElementsBelongTo());
                    $item->setElementsBelongTo($name, true);
                } else {
                    $item->setElementsBelongTo($belongsTo, true);
                }
            } elseif (!empty($belongsTo) && ($item instanceof UcdZend_Form_DisplayGroup)) {
                foreach ($item as $element) {
                    $element->setBelongsTo($belongsTo);
                }
            }

            $items[] = $item->render();

            if (($item instanceof UcdZend_Form_Element_File)
                || (($item instanceof UcdZend_Form)
                    && (UcdZend_Form::ENCTYPE_MULTIPART == $item->getEnctype()))
                || (($item instanceof UcdZend_Form_DisplayGroup)
                    && (UcdZend_Form::ENCTYPE_MULTIPART == $item->getAttrib('enctype')))
            ) {
                if ($form instanceof UcdZend_Form) {
                    $form->setEnctype(UcdZend_Form::ENCTYPE_MULTIPART);
                } elseif ($form instanceof UcdZend_Form_DisplayGroup) {
                    $form->setAttrib('enctype', UcdZend_Form::ENCTYPE_MULTIPART);
                }
            }
        }
        $elementContent = implode($separator, $items);

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $elementContent . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $elementContent;
        }
    }
}
