<?php
namespace Getresponse\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Class ImageUrl
 * @package Getresponse\WordPress
 */
class ImageUrl extends Url
{

    /**
     * ImageUrl constructor.
     * @param $url
     */
    public function __construct($url)
    {
        parent::__construct($url);
        $this->normalizeFileName();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    private function normalizeFileName()
    {
        if (empty($this->url)) {
            return;
        }

        $this->url = dirname($this->url) . DIRECTORY_SEPARATOR . urlencode(basename($this->url));
    }

}