<?php

class NotDeletedModel extends \Phalcon\Mvc\Model
{
    public function delete($delete = false, $data = null, $whiteList = null)
    {
        if (!$delete) {
            $this->setDeleted(true);
            return $this->save();
        } else {
            $result = parent::delete($data, $whiteList);
            return $result;
        }
    }

    public function restore()
    {
        $this->setDeleted(false);
        return $this->save();
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true';
                else
                    $conditions = 'deleted != true';

            }

            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true';
                else
                    $conditions = 'deleted != true';

            }
            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }

        return parent::findFirst($parameters);
    }
}