<?php
namespace GouuseCore\Helpers;

class SessionHelper
{
    public function __construct()
    {
        session_start();
    }
    
    public static function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    public static function get(string $key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
}
