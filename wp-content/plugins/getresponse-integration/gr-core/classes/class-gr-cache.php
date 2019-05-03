<?php

namespace Getresponse\WordPress;

/**
 * Class GrCache
 * @package Getresponse\WordPress
 */
class GrCache
{

    const GROUP = 'getresponse';

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     */
    public function setValue($key, $value, $ttl)
    {
        wp_cache_set($key, $value, self::GROUP, time() + $ttl);
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function getValue($key)
    {
        return wp_cache_get($key, self::GROUP);
    }

    /**
     * @param string $key
     */
    public function deleteKey($key)
    {
        wp_cache_delete($key, self::GROUP);
    }
}
