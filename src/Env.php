<?php
/**
 * @author: Axios
 *
 * @email: axioscros@aliyun.com
 * @blog:  http://hanxv.cn
 * @datetime: 2017/7/11 13:37
 */

namespace PhalApi;

final class Env
{
    public static $file_path = '';

    public static $env_array = [];

    public static $instance = null;

    public static function init()
    {
        if (empty(self::$file_path) && is_file(API_ROOT . '.env')) {
            self::$file_path = API_ROOT . '.env';
        }
        if (empty(self::$env_array)) {
            self::$env_array = is_file(self::$file_path) ? parse_ini_file(self::$file_path, true) : [];
        }
        return self::$env_array;
    }

    public static function path($path)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }
        if (is_file($path)) {
            self::$file_path = $path;
            self::$env_array = parse_ini_file(self::$file_path, true);
        } else {
            throw new Exception($path . ' not found');
        }
        return self::$instance;
    }

    public static function get($index, $default = null)
    {
        self::init();
        if (strpos($index, '.')) {
            $indexArray = explode('.', $index);
            $envData = self::$env_array;
            $tmp = $envData;
            foreach ($indexArray as $i) {
                $tmp = isset($tmp[$i]) ? $tmp[$i] : null;
                if (is_null($tmp)) {
                    return $default;
                }
            }
        } else {
            $tmp = self::$env_array;
            $tmp = isset($tmp[$index]) ? $tmp[$index] : null;
            if (is_null($tmp)) {
                return $default;
            }
        }
        return $tmp;
    }
}
