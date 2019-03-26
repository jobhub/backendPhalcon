<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use App\Controllers\AbstractController;
use App\Services\ImageService;
use App\Libs\SupportClass;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Tasks extends AccountWithNotDeletedWithCascade
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $task_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $category_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deadline;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date_creation;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $date_start;

    /**
     *
     * @var string
     * @Column(type="string", length=300, nullable=true)
     */
    protected $address;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $marker_id;

    const publicColumns = ['task_id', 'category_id', 'deadline', 'description', 'price', 'name', 'status',
        'date_start','address','marker_id', 'account_id'];

    const DEFAULT_PAGE_SIZE = 10;
    /**
     * Method to set the value of field task_id
     *
     * @param integer $task_id
     * @return $this
     */
    public function setTaskId($task_id)
    {
        $this->task_id = $task_id;

        return $this;
    }

    /**
     * Method to set the value of field category_id
     *
     * @param integer $category_id
     * @return $this
     */
    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field deadline
     *
     * @param string $deadline
     * @return $this
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Method to set the value of field price
     *
     * @param integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Method to set the value of field status
     *
     * @param integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Method to set the value of field date_creation
     *
     * @param string $date_creation
     * @return $this
     */
    public function setDateCreation($date_creation)
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    /**
     * Method to set the value of field date_start
     *
     * @param string $date_start
     * @return $this
     */
    public function setDateStart($date_start)
    {
        $this->date_start = $date_start;

        return $this;
    }

    /**
     * Method to set the value of field address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Method to set the value of field marker_id
     *
     * @param integer $marker_id
     * @return $this
     */
    public function setMarkerId($marker_id)
    {
        $this->marker_id = $marker_id;

        return $this;
    }

    /**
     * Returns the value of field task_id
     *
     * @return integer
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * Returns the value of field category_id
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field deadline
     *
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Returns the value of field price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the value of field status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the value of field date_creation
     *
     * @return string
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Returns the value of field date_start
     *
     * @return string
     */
    public function getDateStart()
    {
        return $this->date_start;
    }

    /**
     * Returns the value of field address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the value of field marker_id
     *
     * @return integer
     */
    public function getMarkerId()
    {
        return $this->marker_id;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'category_id',
            new Callback(
                [
                    "message" => "Такая категория не существует или она не является дочерней",
                    "callback" => function ($task) {
                        $category = Categories::findFirstByCategoryId($task->getCategoryId());

                        if ($category && ($category->getParentId() != null && $category->getParentId() != 0))
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'status',
            new Callback([
                "message" => "Поле статус имеет неверное значение.",
                'callback' => function ($task) {
                    $status = Statuses::findFirstByStatusId($task->getStatus());
                    if (!$status)
                        return false;
                    return true;
                }
            ])
        );

        $validator->add(
            'name',
            new PresenceOf([
                "message" => "Должно быть указано название задания"
            ])
        );

        $validator->add(
            'price',
            new Callback([
                "message" => "Должна быть указана цена",
                'callback' => function ($task) {
                    if ($task->getPrice() == null || !SupportClass::checkDouble($task->getPrice()))
                        return false;
                    return true;
                }
            ])
        );

        $validator->add(
            'date_start',
            new PresenceOf([
                "message" => "Дата начала приема заявок должна быть указана"
            ])
        );

        $validator->add(
            'date_start',
            new Callback(
                [
                    "message" => "Дата начала задания должна быть раньше даты завершения задания",
                    "callback" => function ($task) {
                        if (strtotime($task->getDateStart()) < strtotime($task->getDeadline()))
                            return true;
                        return false;
                    }
                ]
            )
        );

        return $this->validate($validator) && parent::validation();
    }

    public static function findTasksByCompany($companyId)
    {
        $result = self::findByCompany($companyId, get_class(), self::publicColumns);
        return $result->toArray();
    }

    public static function findTasksByCompanyWithPagination($companyId,$page = 1, $page_size = Tasks::DEFAULT_PAGE_SIZE)
    {
        $result = self::findByCompanyWithPagination($companyId, get_class(), self::publicColumns,
            null,null,'m.status!=ANY '.STATUSES_FINISHED.', m.date_creation desc',$page,$page_size);
        $result['data'] = self::handleYourTasksFromArray($result['data']);
        return $result;
    }

    public static function findTasksByUser($userId)
    {
        $result = self::findByUser($userId, get_class(), self::publicColumns);

        return $result->toArray();
    }

    public static function findTasksByUserWithPagination($userId, $page = 1, $page_size = Tasks::DEFAULT_PAGE_SIZE)
    {
        $result = self::findByUserWithPagination($userId, get_class(), self::publicColumns,
            null,null,'m.status!=ANY'.STATUSES_FINISHED.', m.date_creation desc',$page,$page_size);

        $result['data'] = self::handleYourTasksFromArray($result['data']);
        return $result;
    }

    public static function findAcceptingTasksByUser($userId)
    {
        $result = self::findByUser($userId, get_class(), self::publicColumns,
            ['status = :status:'], ['status' => STATUS_ACCEPTING]);

        return self::handleTasksFromArray($result->toArray());
    }

    public static function findAcceptingTasksByCompany($companyId)
    {
        $result = self::findByCompany($companyId, get_class(), self::publicColumns,
            ['status = :status:'], ['status' => STATUS_ACCEPTING]);

        return self::handleTasksFromArray($result->toArray());
    }

    public static function handleTasksFromArray(array $tasks)
    {
        $handledTasks = [];
        if ($tasks != null)
            foreach ($tasks as $task) {
                $handledTasks[] = self::handleTaskFromArray($task);
            }
        return $handledTasks;
    }

    public static function handleYourTasksFromArray(array $tasks)
    {
        $handledTasks = [];
        if ($tasks != null)
            foreach ($tasks as $task) {
                $handledTasks[] = self::handleYourTaskFromArray($task);
            }
        return $handledTasks;
    }

    public static function handleTaskFromArray(array $task, $accountId = null)
    {
        if ($accountId == null) {
            $accountId = AbstractController::getAccountId();
        }

        $taskAll = [];

        $taskAll['name'] = $task['name'];
        $taskAll['description'] = $task['description'];
        $taskAll['task_id'] = $task['task_id'];
        $taskAll['address'] = $task['address'];

        if(!is_null($task['marker_id'])) {
            $marker = MarkersWithCity::findById($task['marker_id']);

            $taskAll['longitude'] = $marker->getLongitude();
            $taskAll['latitude'] = $marker->getLatitude();
            $taskAll['city_id'] = $marker->getCityId();
        }

        $taskAll['category_id'] = $task['category_id'];

        $account = Accounts::findAccountById($task['account_id']);

        if($account) {
            if($account->getCompanyId()!=null)
                $taskAll['publisher_company'] = $account->getUserInformation();
            else
                $taskAll['publisher_user'] = $account->getUserInformation();
        }


        $taskAll['date_start'] = $task['date_start'];
        $taskAll['deadline'] = $task['deadline'];

        $di = DI::getDefault();

        /*$taskAll['images'] = ImagesModel::findAllImages(
            $di->getImageService()->getModelByType(ImageService::TYPE_TASK),
            $taskAll['task_id']);*/

        $taskAll['images'] = $di->getImageService()->getAllImages($taskAll['task_id'],ImageService::TYPE_TASK);

        return $taskAll;
    }

    public static function handleYourTaskFromArray(array $task, $accountId = null)
    {
        if ($accountId == null) {
            $accountId = AbstractController::getAccountId();
        }

        $taskAll = [];

        $taskAll['name'] = $task['name'];
        $taskAll['task_id'] = $task['task_id'];

        $taskAll['category_id'] = $task['category_id'];

        $account = Accounts::findAccountById($task['account_id']);

        if($account) {
            if($account->getCompanyId()!=null)
                $taskAll['publisher_company'] = $account->getUserInformation();
            else
                $taskAll['publisher_user'] = $account->getUserInformation();
        }


        $taskAll['date_start'] = $task['date_start'];
        $taskAll['deadline'] = $task['deadline'];
        $taskAll['status'] = $task['status'];

        $taskAll['offers'] = Offers::findOffersForTask($taskAll['task_id']);

        $di = DI::getDefault();

        $images = $di->getImageService()->getAllImages($taskAll['task_id'],ImageService::TYPE_TASK);

        if(count($images)>0)
            $taskAll['image'] = $images[0]['image_path'];

        return $taskAll;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("tasks");
        $this->hasMany('task_id', 'App\Models\Offers', 'task_id', ['alias' => 'Offers']);
        $this->belongsTo('account_id', 'App\Models\Accounts', 'id', ['alias' => 'Accounts']);
        $this->belongsTo('category_id', 'App\Models\Categories', 'category_id', ['alias' => 'Categories']);
        $this->belongsTo('marker_id', 'App\Models\MarkersWithCity', 'marker_id', ['alias' => 'MarkersWithCity']);
        $this->belongsTo('status', 'App\Models\Statuses', 'status_id', ['alias' => 'Statuses']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tasks';
    }

    public function getSequenceName()
    {
        return "tasks_taskid_seq";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tasks[]|Tasks|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tasks|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findById(int $id, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['task_id = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, 'task_id = :id:',
                'bind' => ['id' => $id]]);
        }
    }


    /*public static function checkUserHavePermission($userId, $taskId, $right = null)
    {
        $task = Tasks::findFirstByTaskid($taskId);
        $user = Users::findFirstByUserid($userId);

        if (!$task)
            return false;

        if ($task->getSubjectType() == 1) {
            //Предложение компании
            $rightCompany = 'Tasks';
            if($right == 'delete')
                $rightCompany = 'deleteTask';
            else if($right == 'get')
                $rightCompany = 'getTasks';
            else if($right == 'edit')
                $rightCompany = 'editTask';
            else if($right == 'getOffers')
                $rightCompany = 'getOffersForTask';

            if (!Companies::checkUserHavePermission($userId, $task->getSubjectId(), $rightCompany)) {
                return false;
            }
            return true;
        } else if ($task->getSubjectType() == 0) {
            if ($task->getSubjectId() != $userId && $user->getRole() != ROLE_MODERATOR) {
                return false;
            }
            return true;
        }
        return false;
    }*/
}
