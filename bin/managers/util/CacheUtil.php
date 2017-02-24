<?php

/**
 * cache帮助类
 *
 * @classname: FileUtil
 * @author   11942518@qq.com | quenteen
 * @date     2016-6-28
 */
class CacheUtil extends Manager
{

    public static function get($key)
    {
        return Yii::app()->cache->get($key);
    }

    public static function set($key, $val, $expire = 604800)
    {
        Yii::app()->cache->set($key, $val, $expire);
    }

    public static function del($key)
    {
        Yii::app()->cache->delete($key);
    }

    public static function getX($key)
    {
        if (YII_DEBUG) {
            return self::get($key);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);

            $result = $redis->get($key);
            return $result;
        }
    }

    public static function setX($key, $val, $expire = 604800)
    {
        if (YII_DEBUG) {
            return self::set($key, $val, $expire);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);

            $result = $redis->setex($key, $expire, $val);

            return $result;
        }
    }

    public static function delX($key)
    {
        if (YII_DEBUG) {
            self::del($key);
        } else {
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);

            $result = $redis->del($key);

            return $result;
        }
    }

}