<?php
require_once UCD_APPLICATION_PATH . '/controllers/Abstract/Ajax.php';

class Ucd_Controller_FrontendAjaxController
    extends Ucd_Controller_Abstract_Ajax
{
    public function indexAction()
    {
        if (!isset($_POST['postId'])) {
            throw new Exception('Parameter "postId" not found.');
        }
        $postId = (int)$_POST['postId'];
        if ($postId <= 0) {
            throw new Exception('Post ID is invalid.');
        }

        if (!isset($_POST['countdownId'])) {
            throw new Exception('Parameter "countdownId" not found.');
        }
        if (!isset($_POST['allowRedirect'])) {
            throw new Exception('Parameter "allowRedirect" not found.');
        }

        $widget = Ucd_Application::getService('Countdown')->getWidget($postId);
        if (!$widget) {
            // Post not found or widget disabled
            exit;
        }

        $this->_view->assign(array(
            'postId' => $postId,
            'widget' => $widget,
            'countdownId' => (int)$_POST['countdownId'],
            'allowRedirect' => (bool)$_POST['allowRedirect'],
        ));
    }

    public function embedAction()
    {
        $postId = $this->getParam('post_id');

        $widget = Ucd_Application::getService('Countdown')->getWidget($postId);
        if (!$widget) {
            header('HTTP/1.0 404 Not Found');
            exit("Countdown for post ID '{$postId}' not found or disabled.");
        }

        $this->_view->assign(array(
            'postId' => $postId,
            'widget' => $widget,
            'countdownId' => '{countdownId}',
            'allowRedirect' => TRUE,
        ));

        nocache_headers();
        header('Content-Type: text/javascript');
    }
}