<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 13.08.2018
 * Time: 15:29
 */

class SupportClass
{
    public static function checkInteger($var){
        return ((string)(int)$var == $var);
    }

    public static function checkPositiveInteger($var){
        return ((string)(int)$var == $var);
    }
}