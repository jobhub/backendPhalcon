<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 13.08.2018
 * Time: 15:29
 */

namespace App\Libs;

use Phalcon\Http\Response;
use App\Services\ServiceExtendedException;
use Phalcon\DI\FactoryDefault as DI;

class SupportClass
{

    const COMMON_PAGE_SIZE = 10;

    public static function checkInteger($var)
    {
        return ((string)(int)$var == $var);
    }

    public static function checkPositiveInteger($var)
    {
        return ((string)(int)$var == $var);
    }

    /*public static function checkDouble($var){
        return ((string)(double)$var == $var);
    }*/

    public static function pullRegions($filename, $db = null)
    {
        if ($db == null)
            $db = Phalcon\DI::getDefault()->getDb();

        $content = file_get_contents($filename);
        //$content = trim($content);
        $str = str_replace("\n", '', $content);
        $str = str_replace('osmId', '"osmId"', $str);
        $str = str_replace('name', '"name"', $str);
        $str = str_replace("'", '"', $str);
        $regions = json_decode($str, true);
        //$res = json_decode($str,true);

        $db->begin();
        foreach ($regions as $region) {
            $regionObj = Regions::findFirstByRegionid($region['osmId']);
            if (!$regionObj) {
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
                return ['result' => false, 'errors' => $errors];
            }
        }
        $db->commit();
        return ['result' => true];
    }

    public static function transformControllerName($controllerName)
    {
        $new_controller = array();
        for ($i = 0; $i < strlen($controllerName); $i++) {
            $lowercase = strtolower($controllerName[$i]);
            if (ord($controllerName[$i]) <= 90 && $i > 0) {
                $new_controller[] = '_';
            }
            $new_controller[] = $lowercase;
        }
        $str = implode('', $new_controller);
        return implode('', $new_controller);
    }

    public static function writeMessageInLogFile($message)
    {
        $file = fopen(BASE_PATH . '/public/logs.txt', 'a');
        fwrite($file, 'Дата: ' . date('Y-m-d H:i:s') . ' - ' . $message . "\r\n");
        fflush($file);
        fclose($file);
    }

    /**
     * Optimized algorithm from http://www.codexworld.com
     *
     * @param float $latitudeFrom
     * @param float $longitudeFrom
     * @param float $latitudeTo
     * @param float $longitudeTo
     *
     * @return float [m]
     */
    public static function codexworldGetDistanceOpt($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo)
    {
        $rad = M_PI / 180;
        //Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin($latitudeFrom * $rad) * sin($latitudeTo * $rad) + cos($latitudeFrom * $rad) * cos($latitudeTo * $rad) * cos($theta * $rad);

        return acos($dist) / $rad * 60 * 1853;
    }

    public static function translateInPhpArrFromPostgreJsonObject($str)
    {
        //$str = json_decode($str);
        if (is_null($str))
            return [];

        /*$str[0] = '[';
        $str[strlen($str) - 1] = ']';*/

        $str = str_replace('"{', '{', $str);
        $str = str_replace('}"', '}', $str);
        //$str = stripslashes($str);

        $str = json_decode($str, true);
        return $str;
    }

    public static function translateInPhpArrFromPostgreArr($str)
    {
        //$str = json_decode($str);
        if (is_null($str))
            return [];

        $str[0] = '[';
        $str[strlen($str) - 1] = ']';

        $str = str_replace('"{', '{', $str);
        $str = str_replace('}"', '}', $str);
        $str = stripslashes($str);

        $str = json_decode($str, true);
        return $str;
    }

    /*public static function getResponseWithErrors($object){
        $response = new Response();
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        $response->setJsonContent(
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ]);

        return $response;
    }*/

    public static function getResponseWithErrors($object)
    {
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        return
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ];
    }

    public static function getArrayWithErrors($object)
    {
        $errors = [];
        foreach ($object->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        return $errors;
    }

    public static function getErrorsWithException($object, $code, $msg)
    {
        $errors = SupportClass::getArrayWithErrors($object);
        if (count($errors) > 0)
            throw new ServiceExtendedException($msg,
                $code, null, null, $errors);
        else {
            throw new ServiceExtendedException($msg,
                $code);
        }
    }

    public static function getResponseWithErrorsFromArray($errors)
    {
        $response = new Response();
        $response->setJsonContent(
            [
                "errors" => $errors,
                "status" => STATUS_WRONG
            ]);

        return $response;
    }

    /**
     * Convert PHP array to postgres array
     *
     * @param $set
     * @return string
     */
    public static function to_pg_array($set)
    {
        settype($set, 'array'); // can be called with a scalar or array
        $result = array();
        foreach ($set as $t) {
            if (is_array($t)) {
                $result[] = self::to_pg_array($t);
            } else {
                $t = str_replace('"', '\\"', $t); // escape double quote
                if (!is_numeric($t)) // quote only non-numeric values
                    $t = '"' . $t . '"';
                $result[] = $t;
            }
        }
        return '{' . implode(",", $result) . '}'; // format
    }

    /**
     * Convert Postgres array to php
     *
     * @param $s
     * @param int $start
     * @param null $end
     * @return array|null
     */
    public static function to_php_array($s, $start = 0, &$end = null)
    {
        if (empty($s) || $s[0] != '{') return null;
        $return = array();
        $string = false;
        $quote = '';
        $len = strlen($s);
        $v = '';
        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $s[$i];

            if (!$string && $ch == '}') {
                if ($v !== '' || !empty($return)) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            } elseif (!$string && $ch == '{') {
                $v = to_php_array($s, $i, $i);
            } elseif (!$string && $ch == ',') {
                $return[] = $v;
                $v = '';
            } elseif (!$string && ($ch == '"' || $ch == "'")) {
                $string = true;
                $quote = $ch;
            } elseif ($string && $ch == $quote && $s[$i - 1] == "\\") {
                $v = substr($v, 0, -1) . $ch;
            } elseif ($string && $ch == $quote && $s[$i - 1] != "\\") {
                $string = false;
            } else {
                $v .= $ch;
            }
        }

        return $return;
    }

    /**
     * Remove an element from an array.
     *
     * @param string|int $element
     * @param array $array
     */
    public static function deleteElement($element, &$array)
    {
        $index = array_search($element, $array);
        if ($index !== false) {
            unset($array[$index]);
        }
    }

    public static function getCertainColumnsFromArray(array $data, array $columns)
    {
        $toRet = [];
        foreach ($columns as $info)
            $toRet[$info] = $data[$info];
        return $toRet;
    }

    /**
     * Save file by url
     * @param $URL
     * @param $PATH
     *
     * @return $file
     */
    public static function downloadFile ($URL, $PATH) {
        $ReadFile = fopen ($URL, "rb");
        if ($ReadFile) {
            $WriteFile = fopen ($PATH, "wb");
            if ($WriteFile){
                while(!feof($ReadFile)) {
                    fwrite($WriteFile, fread($ReadFile, 4096));
                }
            }
            fclose($ReadFile);
            return $WriteFile;
        }
        return null;
    }

    public static function executeWithPagination($sqlRequest, $params, $page = 1, $page_size = self::COMMON_PAGE_SIZE){

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        if(is_string($sqlRequest)){
            $db = DI::getDefault()->getDb();
            $sqlRequestReplaced = self::str_replace_once('select','select count(*) OVER() AS total_count_pagination, ',strtolower($sqlRequest));
            $sqlRequestReplaced.='
                    LIMIT :limit 
                    OFFSET :offset';

            $query = $db->prepare($sqlRequestReplaced);
            $params_2 = [];
            foreach ($params as $key=>$data){
                $params_2[strtolower($key)] = $data;
            }

            $params_2['limit'] = $page_size;
            $params_2['offset'] = $offset;

            $query->execute($params_2);

            $results = $query->fetchAll(\PDO::FETCH_ASSOC);

            if(count($results)>0) {
                $final_results = [];
                foreach ($results as $result) {
                    $final_result = [];
                    foreach ($result as $key => $data) {
                        if ($key != 'total_count_pagination') {
                            $final_result[$key] = $data;
                        }
                    }
                    $final_results[] = $final_result;
                }

                return ['pagination'=>['total'=>$results[0]['total_count_pagination']],'data'=>$final_results];
            } else{

                //$sqlRequestReplaced = str_replace(["\r","\n"],' ',strtolower($sqlRequest));
                $sqlRequestReplaced = strtolower($sqlRequest);
                $sqlRequestReplaced = preg_replace(
                    "#select.*?from#",'select count(*) AS total_count_pagination from',
                    $sqlRequestReplaced,1,$count);

                $sqlRequestReplaced = preg_replace(
                    "#order by.*#",'',
                    $sqlRequestReplaced,1,$count);

                $query = $db->prepare($sqlRequestReplaced);

                $params_2 = [];
                foreach ($params as $key=>$data){
                    $params_2[strtolower($key)] = $data;
                }

                $query->execute($params_2);

                $results = $query->fetchAll(\PDO::FETCH_ASSOC);

                return ['data'=>[],'pagination'=>['total'=>$results[0]['total_count_pagination']]];
            }
        } elseif(is_object($sqlRequest) && get_class($sqlRequest) == 'Phalcon\Mvc\Model\Query\Builder'){


            $sqlGotRequest = $sqlRequest;
            $sqlRequest->limit($page_size)
                       ->offset($offset);

            $data = $sqlRequest->getQuery()->execute();

            $count = $sqlGotRequest->columns('count(*) as count')
                ->limit(null)
                ->offset(null)
                ->orderBy(null)
                ->getQuery()->execute();


            return ['data'=>$data->toArray(),'pagination'=>['total'=>$count[0]->toArray()['count']]];
        }
    }


    public static function str_replace_once($search, $replace, $text)
    {
        $pos = strpos($text, $search);
        return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }
}