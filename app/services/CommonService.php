<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\ActivationCodes;

use App\Models\News;
use App\Models\Services;
use App\Models\UsersSocial;
use Phalcon\DI\FactoryDefault as DI;

//Models
use App\Models\Users;
use App\Models\Phones;
use App\Models\PasswordResetCodes;

use App\Libs\SupportClass;
use App\Libs\PHPMailerApp;

/**
 * business and other logic for authentication. Maybe just creation simple objects.
 *
 * Class UsersService
 */
class CommonService extends AbstractService
{
    const ADDED_CODE_NUMBER = 35000;


    const ERROR_UNABLE_SEND_ACTIVATION_CODE_TO_SOCIAL = 1 + self::ADDED_CODE_NUMBER;

    public function getIdFromObject($type, $some_object)
    {
        switch ($type) {
            case self::TYPE_USER:
                $id = $some_object->getUserId();
                break;
            case self::TYPE_NEWS:
                $id = $some_object->getNewsId();
                break;
            case self::TYPE_REVIEW:
                $id = $some_object->getReviewId();
                break;
            case self::TYPE_SERVICE:
                $id = $some_object->getServiceId();
                break;
            case self::TYPE_TEMP:
                $id = $some_object->getId();
                break;
            case self::TYPE_RASTRENIYA:
                $id = $some_object->getId();
                break;
            case self::TYPE_PRODUCT:
                $id = $some_object->getProductId();
                break;
            case self::TYPE_EVENT:
                $id = $some_object->getEventId();
                break;
            case self::TYPE_TASK:
                $id = $some_object->getTaskId();
                break;
            default:
                throw new ServiceException('Invalid type of object', self::ERROR_INVALID_OBJECT_TYPE);
        }
        return $id;
    }

    public function getModel($type)
    {
        switch ($type) {
            case self::TYPE_USER:
                $model = 'Users';
                break;
            case self::TYPE_NEWS:
                $model = 'News';
                break;
            case self::TYPE_SERVICE:
                $model = 'Services';
                break;
            case self::TYPE_PRODUCT:
                $model = 'Products';
                break;
            case self::TYPE_EVENT:
                $model = 'Events';
                break;
            case self::TYPE_RASTRENIYA:
                $model = 'Rastreniya';
                break;
            case self::TYPE_REVIEW:
                $model = 'Reviews';
                break;
            case self::TYPE_COMPANY:
                $model = 'Companies';
                break;
            case self::TYPE_TASK:
                $model = 'Tasks';
                break;
            default:
                throw new ServiceException('Invalid type of object', self::ERROR_INVALID_OBJECT_TYPE);
        }
        return 'App\Models\\' . $model;
    }

    public function getSortCondition($type, $query, $some_object)
    {
        $model = $this->getModel($type);

        $condition['bind'] = $query['bind'];

        $sort_fields = str_replace(['asc'], '', $query['order'], $countAsc);
        $sort_fields = str_replace(['desc'], '', $sort_fields, $countDesc);

        if ($countDesc != 0)
            $more = true;
        else
            $more = false;

        $sort_fields = explode(',', $sort_fields);

        if (isset($query['id'])) {
            $sort_fields[] = $query['id'];
        } else {
            $sort_fields[] = $model::getIdField();
        }

        $sort_condition = '';
        for ($i = 0; $i < count($sort_fields); $i++) {

            $sort_fields[$i] = trim($sort_fields[$i]);
            $sort_fields_params[$i] = str_replace('.', '_', $sort_fields[$i]);

            $and_condition = '';
            for ($j = 0; $j < $i; $j++) {
                if ($and_condition != '')
                    $and_condition .= ' AND ';
                $and_condition .= $sort_fields[$j] . ' = :' . $sort_fields_params[$j] . '_' . $i;

                if (isset($query['columns_map']) && isset($query['columns_map'][$sort_fields[$j]]))
                    $condition['bind'][$sort_fields_params[$j] . '_' . $i] = $some_object[$query['columns_map'][$sort_fields[$j]]];
                else {
                    $condition['bind'][$sort_fields_params[$j] . '_' . $i] = $some_object[$sort_fields[$j]];
                }
            }

            if ($sort_condition != '') {
                $sort_condition .= ' OR (' . $and_condition . ' AND '
                    . $sort_fields[$i] . ' ' . ($more ? '>' : '<') . ' :' . $sort_fields_params[$i] . ')';
            } else {
                $sort_condition .= '(' . $sort_fields[$i] . ' ' . ($more ? '>' : '<') . ' :' . $sort_fields_params[$i] . ')';
            }

            if (isset($query['columns_map']) && isset($query['columns_map'][$sort_fields[$i]]))
                $condition['bind'][$sort_fields_params[$i]] = $some_object[$query['columns_map'][$sort_fields[$i]]];
            else {
                $condition['bind'][$sort_fields_params[$i]] = $some_object[$sort_fields[$i]];
            }
        }

        $condition['from'] = 'FROM ' . $query['from'];

        if (!empty(trim($query['where'])))
            $condition['conditions'] = 'WHERE ' . $query['where'] . ' AND 
                (' . $sort_condition . ')';
        else
            $condition['conditions'] = 'WHERE (' . $sort_condition . ')';


        return $condition;
    }
}
