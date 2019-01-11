<?php

namespace App\Models;

class HiddenSpamModel extends NotDeletedModel
{

    /**
     *
     * @var boolean
     */
    protected $is_hidden;

    /**
     *
     * @var boolean
     */
    protected $is_spam;


    /**
     * Method to set the value of field is_hidden
     *
     * @param string $is_hidden
     * @return $this
     */
    public function setIsHidden($is_hidden)
    {
        $this->is_hidden = $is_hidden;

        return $this;
    }

    /**
     * Method to set the value of field is_spam
     *
     * @param string $is_spam
     * @return $this
     */
    public function setIsSpam($is_spam)
    {
        $this->is_spam = $is_spam;

        return $this;
    }


    /**
     * Returns the value of field is_spam
     *
     * @return boolean
     */
    public function getIsSpam()
    {
        return $this->is_spam;
    }

    /**
     * Returns the value of field is_hidden
     *
     * @return boolean
     */
    public function getIsHidden()
    {
        return $this->is_hidden;
    }

    /**
     * Toggle discussion to spam
     *
     * @param $value boolean
     * @return string
     */
    public function spam($value = null)
    {
        if(!is_null($value))
            // set specified spam value
            $this->setIsSpam($value);
        else{
            // toggle spam
            $this->setIsSpam(!$this->getIsSpam());
        }
        if($this->is_hidden && $this->is_spam)
            $this->setIsHidden(false);
        return $this->update();
    }

    /**
     * Toggle discussion to spam
     *
     * @param $value boolean
     * @return string
     */
    public function hidden($value = null)
    {
        if(!is_null($value))
            // set specified hidden value
            $this->setIsHidden($value);
        else{
            // toggle hidden
            $this->setIsHidden(!$this->getIsHidden());
        }
        if($this->is_spam && $this->is_hidden)
            $this->setIsSpam(false);
        return $this->update();
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function findHidden($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= 'AND deleted != true AND is_hidden = true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true AND is_hidden = true';
                else
                    $conditions = 'deleted != true AND is_hidden = true';
            }

            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }
        $result = parent::find($parameters);

        return $result;
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function findSpam($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true AND is_spam = true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true AND is_spam = true';
                else
                    $conditions = 'deleted != true AND is_spam = true';
            }

            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }
        $result = parent::find($parameters);

        return $result;
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirstEnabled($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true AND is_hidden != true AND is_spam != true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true AND is_hidden != true  AND is_spam != true';
                else
                    $conditions = 'deleted != true AND is_hidden != true  AND is_spam != true';
            }
            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }

        return parent::findFirst($parameters);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function findEnabled($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true AND is_hidden != true AND is_spam != true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true AND is_hidden != true AND is_spam != true';
                else
                    $conditions = 'deleted != true AND is_hidden != true AND is_spam != true';
            }

            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }
        $result = parent::find($parameters);

        return $result;
    }
}