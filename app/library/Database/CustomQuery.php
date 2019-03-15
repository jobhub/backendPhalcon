<?php

namespace App\Libs\Database;

class CustomQuery
{
    private $where;

    private $columns;

    private $from;

    private $bind;

    private $columns_map;

    private $id;

    private $order;

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param mixed $where
     */
    public function setWhere($where)
    {
        $this->where = $where;
    }

    public function addWhere($where, $bind=null){
        $this->where .= ' and '.$where;
        if($this->bind==null)
            $this->bind = $bind;
        else if($bind!=null && is_array($bind))
            $this->bind = array_merge($this->bind,$bind);
    }

    /**
     * @return mixed
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return mixed
     */
    public function getBind()
    {
        return $this->bind;
    }

    /**
     * @param mixed $bind
     */
    public function setBind($bind)
    {
        $this->bind = $bind;
    }

    /**
     * @return mixed
     */
    public function getColumnsMap()
    {
        return $this->columns_map;
    }

    /**
     * @param mixed $columns_map
     */
    public function setColumnsMap($columns_map)
    {
        $this->columns_map = $columns_map;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function __construct(array $query = null)
    {
        if($query!=null) {
            $this->setWhere($query['where']);
            $this->setId($query['id']);
            $this->setBind($query['bind']);
            $this->setColumns($query['columns']);
            $this->setColumnsMap($query['columns_map']);
            $this->setFrom($query['from']);
            $this->setOrder($query['order']);
        }
    }

    public function formSql(): string
    {
        if (!is_null($this->getColumns()))
            $sql_query = 'SELECT ' . $this->getColumns();
        else
            $sql_query = 'SELECT *';

        $sql_query .= ' FROM ' . $this->getFrom();

        if (!empty($this->getWhere()))
            $sql_query .= ' where ' . $this->getWhere();

        if (!is_null($this->getOrder()))
            $sql_query .= ' order by ' . $this->getOrder();

        return $sql_query;
    }
}