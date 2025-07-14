<?php

class Request extends LiteFrameCore
{
    public static function get($key, $default = null)
    {
        return self::clean($_GET[$key] ?? $default);
    }

    public static function post($key, $default = null)
    {
        return self::clean($_POST[$key] ?? $default);
    }

    public static function input($key, $default = null)
    {
        return self::clean($_REQUEST[$key] ?? $default);
    }

    public static function all($method = 'REQUEST')
    {
        $data = match (strtoupper($method)) 
        {
            'GET' => $_GET,
            'POST' => $_POST,
            default => $_REQUEST
        };

        return array_map([self::class, 'clean'], $data);
    }

    public static function exists($key, $method = 'REQUEST')
    {
        $source = match (strtoupper($method)) 
        {
            'GET' => $_GET,
            'POST' => $_POST,
            default => $_REQUEST
        };

        return isset($source[$key]);
    }

    protected static function clean($value)
    {
        if (is_array($value)) 
        {
            return array_map([self::class, 'clean'], $value);
        }

        return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
    }
}
