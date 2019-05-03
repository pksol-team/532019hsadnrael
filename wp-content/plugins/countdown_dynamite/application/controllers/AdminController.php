<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Admin.php';

class Ucd_Controller_AdminController
    extends Ucd_Controller_Abstract_Admin
{
    public function indexAction()
    {
        $form = Ucd_Application::getForm('Main')->load();

        if ($_POST) {
            if (!$form->isValid($_POST)) {
                foreach ($form->getElements() as $element) {
                    foreach ($element->getMessages() as $message) {
                        $this->_addMessage(
                            $this->_view->escape("{$element->getLabel()}: {$message}"),
                            'error');
                    }
                }
            } else {
                $form->save();
            }
        }

        $this->_view->layout->announcement = Ucd_Application::getModel('AnnouncementHtml')->get();

        return array(
            'form' => $form,
        );
    }
}