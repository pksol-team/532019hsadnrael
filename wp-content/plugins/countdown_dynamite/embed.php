<?php
require_once(dirname(__FILE__) . '/../../../wp-load.php');

if (!class_exists('Ucd_WpPlugin')) {
    header('HTTP/1.0 501 Not Implemented');
    exit("Countdown Dynamite plugin is not active.");
}

Ucd_WpPlugin::getInstance()->dispatch('frontend-ajax', 'embed', array(
    'post_id' => isset($_GET['post_id'])? $_GET['post_id'] : 0,
));