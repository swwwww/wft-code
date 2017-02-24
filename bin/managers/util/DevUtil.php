<?php
/**
 * Created by PhpStorm.
 * User: MEX
 * Date: 16/7/7
 * Time: 17:01
 */

class DevUtil extends Manager{

    public static function e($s, $is_exit = false)
    {
        echo "<pre>";

        if(is_object($s))
        {
            print_r($s);

            echo 'Function ';
            print_r(get_class_methods($s));
        }
        else
        {
            echo htmlspecialchars(print_r($s, true));
        }

        echo "</pre>";

        if($is_exit)
            exit;
    }
}