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

    public static function checkDouble($var){
        return ((string)(double)$var == $var);
    }

    public static function pullRegions($filename, $db){
        $content = file_get_contents($filename);
        //$content = trim($content);
        $str = str_replace("\n", '', $content);
        $str = str_replace('osmId', '"osmId"', $str);
        $str = str_replace('name', '"name"', $str);
        $str = str_replace("'", '"', $str);
        $regions = json_decode($str,true);
        //$res = json_decode($str,true);

        $db->begin();
        foreach($regions as $region){
            $regionObj = Regions::findFirstByRegionid($region['osmId']);
            if(!$regionObj) {
                $regionObj = new Regions();
                $regionObj->setRegionId($region['osmId']);
            }
            $regionObj->setRegionName($region['name']);

            if (!$regionObj->save()) {
                $db->rollback();
                $errors = [];
                foreach ($regionObj->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                return ['result' => false,'errors' => $errors];
            }
        }
        $db->commit();
        return ['result' => true];
    }
}