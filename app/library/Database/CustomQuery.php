<?php

namespace App\Libs\Database;

use App\Libs\SupportClass;

class CustomQuery
{
    private $where;

    private $columns;

    private $from;

    private $bind;

    private $columns_map;

    private $id;

    private $order;

    private $limit;

    private $offset;

    private $not_deleted;

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return mixed
     */
    public function getNotDeleted()
    {
        return $this->not_deleted;
    }

    /**
     * @param mixed $not_deleted
     */
    public function setNotDeleted($not_deleted)
    {
        $this->not_deleted = $not_deleted;
    }

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

    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    public function addWhere($where, $bind=null){
        $this->where .= ' and '.$where;
        if($this->bind==null)
            $this->bind = $bind;
        else if($bind!=null && is_array($bind))
            $this->bind = array_merge($this->bind,$bind);

        return $this;
    }

    public function addDeleted($deleted){
        $this->where .= ' and deleted = :deleted';
        if($this->bind==null)
            $this->bind = ['deleted'=>SupportClass::convertBooleanToString($deleted)];
        else
            $this->bind = array_merge($this->bind,['deleted'=>SupportClass::convertBooleanToString($deleted)]);

        return $this;
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

    public function __construct(array $query = null, $not_deleted = null)
    {
        if($query!=null) {
            $this->setWhere($query['where']);
            $this->setId($query['id']);
            $this->setBind($query['bind']);
            $this->setColumns($query['columns']);
            $this->setColumnsMap($query['columns_map']);
            $this->setFrom($query['from']);
            $this->setOrder($query['order']);
            $this->setLimit($query['limit']);
            $this->setOffset($query['offset']);
        }
    }

    public function getQueryInArray(){
        return [
            'from'=>$this->getFrom(),
            'where'=>$this->getWhere(),
            'id'=>$this->getId(),
            'bind'=>$this->getBind(),
            'columns'=>$this->getColumns(),
            'columns_map'=>$this->getColumnsMap(),
            'order'=>$this->getOrder()
        ];
    }

    public function getCopy(){
        return new CustomQuery($this->getQueryInArray());
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

        if(!is_null($this->getLimit()))
            $sql_query .= ' limit ' . $this->getLimit();

        if(!is_null($this->getOffset()))
            $sql_query .= ' offset ' . $this->getOffset();

        return $sql_query;
    }
}