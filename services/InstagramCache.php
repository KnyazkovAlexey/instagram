<?php

namespace app\services;

use InstagramScraper\Instagram;
use Psr\SimpleCache\CacheInterface;
use Yii;

/**
 * Класс для кэширования аутентификационных данных Instagram.
 *
 * @see Instagram::withCredentials
 *
 * Class InstagramCache
 * @package app\services
 */
class InstagramCache implements CacheInterface
{
    /**
     * @see CacheInterface
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Yii::$app->cache->get($key);
    }

    /**
     * @see CacheInterface
     *
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        return Yii::$app->cache->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        return true;
    }

    public function clear()
    {
        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        return [];
    }

    public function setMultiple($values, $ttl = null)
    {
        return true;
    }

    public function deleteMultiple($keys)
    {
        return true;
    }

    public function has($key)
    {
        return false;
    }
}
