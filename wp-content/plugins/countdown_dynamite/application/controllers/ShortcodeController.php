<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Filter.php';

class Ucd_Controller_ShortcodeController
    extends Ucd_Controller_Abstract_Filter
{
    public function indexAction()
    {
        $postId = $this->hasParam('post_id')
            ? $this->getParam('post_id')
            : get_the_ID();
        $postId = (int) $postId;

        if ($postId <= 0) {
            // Post ID not specified
            return;
        }

        $widget = Ucd_Application::getService('Countdown')->getWidget($postId);
        if (!$widget) {
            // Post not found or widget disabled
            return;
        }

        $view = $this->getView();
        $view->assign(array(
            'postId' => $postId,
            'widget' => $widget,
            'allowRedirect' => is_singular() || $this->hasParam('post_id'),
        ));

        return $view->render('shortcode/index.phtml');
    }
}