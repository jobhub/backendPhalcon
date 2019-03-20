<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 20.03.2019
 * Time: 9:53
 */

namespace App\Libs\Database;


class MySQLAdapter
{
    private $host;
    private $user;
    private $password;
    private $port;

    private $DBH;

    public function __construct($config)
    {
        $this->host = $config['host'];
        $this->user = $config['username'];
        $this->password = $config['password'];
        $this->port = $config['port'];
    }

    public function openConnection()
    {
        $this->DBH = new \PDO("mysql:host=$this->host;port=$this->port");
        $this->DBH->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function closeConnection($link)
    {
        $this->DBH = null;
    }

    public function executeQuery(CustomQuery $query)
    {
        $STH = $this->DBH->prepare($query->formSql());

        foreach ($query->getBind() as $param_name => $param) {
            $STH->bindParam($param_name, $param);
        }

        $result = $STH->execute();

        $STH->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $STH->fetchAll();

        $rowCount = $STH->rowCount();

        /*while ($row = $STH->fetch())
            $result[] = $row;*/

        return ['data'=>$result,'pagination'=>['total'=>$rowCount]];
    }
}