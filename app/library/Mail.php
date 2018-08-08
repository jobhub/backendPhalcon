<?php


class Mail extends Phalcon\Mvc\User\Component
{

    protected $_transport;

    public function getTemplate($name, $params){}
    public function send($to, $subject, $name, $params){}
}