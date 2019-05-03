<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Metabox.php';
require_once 'UcdPumka/Filter/StripSlashes.php';

class Ucd_Controller_MetaboxController
    extends Ucd_Controller_Abstract_Metabox
{
    public function indexAction()
    {
        $post = $this->getParam(0);
        $postId = $post->ID;

        $form = Ucd_Application::getForm('Metabox')
                ->setPost($post)
                ->load();

        $meta = Ucd_Application::getModel('PostMeta')->setPostId($postId);
        $errors = $meta->getValue('errors');
        if (!is_array($errors)) {
            $errors = array();
        }
        //$meta->unsetValue('errors');

        return array(
            'form' => $form,
            'post' => $post,
            'postId' => $postId,
            'errors' => $errors,
            'defaultValuesInvalid' => $meta->getValue('default-values-invalid'),
        );
    }

    public function savePostAction()
    {
        $this->setViewScript(NULL);

        $postId = $this->getParam(0);
        $data = $this->getParam(1);

        $form = Ucd_Application::getForm('Metabox')
            ->setPost(get_post($postId))
            ->load();

        $errors = array();
        if (!$form->isValid($data)) {
            foreach ($form->getElements() as $element) {
                foreach ($element->getMessages() as $message) {
                    $errors[] = "{$element->getLabel()}: {$message}";
                }
            }
        }

        $form->save();

        $meta = Ucd_Application::getModel('PostMeta')->setPostId($postId);
        if ($errors) {
            $meta->setValue('errors', $errors)
                ->setValue('default-values-invalid', $form->getDefaultValuesInvalid());
        } else {
            $meta->unsetValue('errors')
                ->unsetValue('default-values-invalid');
        }
    }

    public function mediaButtonsAction()
    {
    }
}