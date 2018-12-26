<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 20.12.2018
 * Time: 9:52
 */

//namespace App\Libs;

class PseudoSession
{
    private $data;

    public function set($name,$data){
        SupportClass::writeMessageInLogFile('Запись в сессию свойства: '.$name);
        $this->data[$name] = $data;
    }

    public function get($name){
        SupportClass::writeMessageInLogFile('Получение из сессии свойства: '.$name);
        return $this->data[$name];
    }
}