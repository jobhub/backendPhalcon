<?php

namespace App\Models;

use App\Controllers\AbstractController;
use App\Libs\Database\CustomQuery;
use App\Libs\SphinxClient;
use App\Libs\SupportClass;
use App\Services\ImageService;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Alpha as AlphaValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Alnum as AlnumValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Regex;

use Phalcon\DI\FactoryDefault as DI;

class Events extends AccountWithNotDeletedWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $event_id;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
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
     * @Column(type="string", nullable=false)
     */
    protected $date_publication;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $center_marker_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $radius;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $active;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $binder_id;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $service_type;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $statistics_id;

    const publicColumns = ['event_id', 'name', 'description', 'account_id', 'active', 'radius', 'center_marker_id', 'binder_id','service_type'];

    const shortColumns = ['event_id', 'name', 'description', 'active', 'binder_id','service_type'];

    const DEFAULT_RESULT_PER_PAGE = 10;

    /**
     * @return string
     */
    public function getServiceType(): string
    {
        return $this->service_type;
    }

    /**
     * @param string $service_type
     */
    public function setServiceType(string $service_type)
    {
        $this->service_type = $service_type;
    }


    /**
     * Method to set the value of field event_id
     *
     * @param integer $event_id
     * @return $this
     */
    public function setEventId($event_id)
    {
        $this->event_id = $event_id;

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
     * @return int
     */
    public function getBinderId()
    {
        return $this->binder_id;
    }

    /**
     * @param int $binder_id
     */
    public function setBinderId($binder_id)
    {
        $this->binder_id = $binder_id;
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
     * Method to set the value of field date_publication
     *
     * @param string $date_publication
     * @return $this
     */
    public function setDatePublication($date_publication)
    {
        $this->date_publication = $date_publication;

        return $this;
    }

    /**
     * Method to set the value of field center_marker_id
     *
     * @param integer $center_marker_id
     * @return $this
     */
    public function setCenterMarkerId($center_marker_id)
    {
        $this->center_marker_id = $center_marker_id;

        return $this;
    }

    /**
     * Method to set the value of field radius
     *
     * @param integer $radius
     * @return $this
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;

        return $this;
    }

    /**
     * Method to set the value of field active
     *
     * @param string $active
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Method to set the value of field statistics_id
     *
     * @param integer $statistics_id
     * @return $this
     */
    public function setStatisticsId($statistics_id)
    {
        $this->statistics_id = $statistics_id;

        return $this;
    }

    /**
     * Returns the value of field event_id
     *
     * @return integer
     */
    public function getEventId()
    {
        return $this->event_id;
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
     * Returns the value of field date_publication
     *
     * @return string
     */
    public function getDatePublication()
    {
        return $this->date_publication;
    }

    /**
     * Returns the value of field center_marker_id
     *
     * @return integer
     */
    public function getCenterMarkerId()
    {
        return $this->center_marker_id;
    }

    /**
     * Returns the value of field radius
     *
     * @return integer
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Returns the value of field active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Returns the value of field statistics_id
     *
     * @return integer
     */
    public function getStatisticsId()
    {
        return $this->statistics_id;
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'radius',
            new Callback(
                [
                    "message" => "Не указан радиус для акции",
                    "callback" => function ($event) {
                        if (SupportClass::checkInteger($event->getRadius()))
                            return true;
                        return false;
                    }
                ]
            )
        );

        if($this->getBinderId()!=null)
        $validator->add(
            'binder_id',
            new Callback(
                [
                    "message" => "Ссылочный объект для акции не существует",
                    "callback" => function ($event) {
                    $binder = Binders::getBinderByServiceType($event->getBinderId(),$event->getServiceType());

                    return $binder?true:false;
                    }
                ]
            )
        );

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("events");
        $this->hasMany('event_id', 'App\Models\ImagesEvents', 'object_id', ['alias' => 'ImagesEvents']);
        $this->hasMany('event_id', 'App\Models\TagsEvents', 'object_id', ['alias' => 'TagsEvents']);
        $this->belongsTo('center_marker_id', 'App\Models\Markers', 'marker_id', ['alias' => 'Markers']);
        $this->hasOne('statistics_id', 'App\Models\Statistics', 'statistics_id', ['alias' => 'Statistics']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'events';
    }

    public function getSequenceName()
    {
        return "events_eventid_seq";
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Events[]|Events|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Events|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findById(int $id, array $columns = null)
    {
        if ($columns == null)
            return self::findFirst(['event_id = :id:',
                'bind' => ['id' => $id]]);
        else {
            return self::findFirst(['columns' => $columns, 'event_id = :id:',
                'bind' => ['id' => $id]]);
        }
    }

    /**
     * Function for handle complete information about events.
     *
     * @param array $event
     * @param null $accountId
     * @return array
     */
    public static function handleEventFromArray(array $event, $accountId = null)
    {
        if ($accountId == null) {
            $accountId = AbstractController::getAccountId();
        }

        $eventAll = [];

        $eventAll['name'] = $event['name'];
        $eventAll['description'] = $event['description'];
        $eventAll['event_id'] = $event['event_id'];

        $marker = Markers::findById($event['center_marker_id']);

        $eventAll['center']['longitude'] = $marker->getLongitude();
        $eventAll['center']['latitude'] = $marker->getLatitude();

        $binder = Binders::getBinderByServiceType($event['binder_id'],$event['service_type']);

        if($event['service_type'] == 'product')
            $eventAll['binder_name'] = $binder->getProductName();
        elseif($event['service_type'] == 'service'){
            $eventAll['binder_name'] = $binder->getName();
        }

        $eventAll['radius'] = $event['radius'];

        $eventAll['active'] = $event['active'];

        $account = Accounts::findAccountById($event['account_id']);
        if($account && $account->getCompanyId()!=null) {
            $company = Companies::findCompanyById($account->getCompanyId());
            $eventAll['rating'] = $company->getRatingExecutor();
        }

        $di = DI::getDefault();

        $eventAll['images'] = ImagesModel::findAllImages($di->getImageService()->getModelByType(ImageService::TYPE_EVENT),
            $eventAll['event_id']);

        /*$account = Accounts::findFirstById($event['account_id']);

        if ($account) {
            $eventAll['publisher_company'] = $account->getUserInformation();
        }*/

        $eventAll['statistics'] = Statistics::findById($event['statistics_id'])->toArray();

        $eventAll['binder_id'] = $event['binder_id'];
        $eventAll['service_type'] = $event['service_type'];

        unset($eventAll['statistics']['statistics_id']);
        return $eventAll;
    }

    public static function handleShortEventFromArray(array $event, $accountId = null)
    {
        if ($accountId == null) {
            $accountId = AbstractController::getAccountId();
        }

        $eventAll = [];

        $eventAll['name'] = $event['name'];
        $eventAll['description'] = $event['description'];
        $eventAll['event_id'] = $event['event_id'];
        $eventAll['binder_id'] = $event['binder_id'];
        $eventAll['service_type'] = $event['service_type'];

        $binder = Binders::getBinderByServiceType($event['binder_id'],$event['service_type']);

        if($event['service_type'] == 'product')
            $eventAll['binder_name'] = $binder->getProductName();
        elseif($event['service_type'] == 'service'){
            $eventAll['binder_name'] = $binder->getName();
        }

        $eventAll['service_type'] = $event['service_type'];

        $eventAll['active'] = $event['active'];

        $account = Accounts::findAccountById($event['account_id']);
        if($account && $account->getCompanyId()!=null) {
            $company = Companies::findCompanyById($account->getCompanyId());
            $eventAll['rating'] = $company->getRatingExecutor();
        }

        $di = DI::getDefault();

        $eventAll['image'] = ImagesModel::findAllImages($di->getImageService()->getModelByType(ImageService::TYPE_EVENT),
            $eventAll['event_id'])[0];

        return $eventAll;
    }

    public static function handleEventsFromArray(array $events)
    {
        $handledEvents = [];
        foreach ($events as $event) {
            $handledEvents[] = self::handleEventFromArray($event);
        }
        return $handledEvents;
    }

    public static function handleShortEventsFromArray(array $events)
    {
        $handledEvents = [];
        foreach ($events as $event) {
            $handledEvents[] = self::handleShortEventFromArray($event);
        }
        return $handledEvents;
    }

    public static function handleEventsFromSearch($search_result)
    {
        $handledEvents = [];
        if ($search_result != null)
            foreach ($search_result as $event) {
                $handleEvent = SupportClass::translateInPhpArrFromPostgreJsonObject($event['event']);

                $handledEvents[] = self::handleShortEventFromArray($handleEvent);
            }
        return $handledEvents;
    }

    public static function findEventsByAccount($relatedAccounts, $active = null, $divided_by_active = null,
                                               $page = 1, $page_size = self::DEFAULT_RESULT_PER_PAGE)
    {
        $query = new CustomQuery([
            'from' => 'events',
            'where' => 'events.account_id = ANY(:ids)',
            'bind' => ['ids' => $relatedAccounts],
            'order' => 'date_publication desc'
        ]);

        $query->addDeleted(false);

        if (!is_null($active) && is_bool($active)) {
            $active = SupportClass::convertBooleanToString($active);
            $query->addWhere('active = :active', ['active' => $active]);
        } elseif (!is_null($divided_by_active)) {
            $queryTwo = $query->getCopy();

            $query->addWhere('active = :active', ['active' => 'true']);

            $sql = $query->formSql();

            $active_events = SupportClass::executeWithPagination($sql, $query->getBind(), $page, $page_size);

            $queryTwo->addWhere('active = :active', ['active' => 'false']);

            $disactive_events = SupportClass::executeWithPagination($sql, $queryTwo->getBind(), $page, $page_size);

            $disactive_events['pagination']['total'] += $active_events['pagination']['total'];

            $merged_events = ['data' => array_merge($active_events['data'], $disactive_events['data']),
                'pagination' => $disactive_events['pagination']];

            $merged_events['data'] = self::handleEventsFromArray($merged_events['data']);

            return $merged_events;
        } else{
            $query->addWhere('active = :active', ['active' => 'true']);
        }

        $sql = $query->formSql();

        $events = SupportClass::executeWithPagination($sql, $query->getBind(), $page, $page_size);

        $events['data'] = self::handleShortEventsFromArray($events['data']);
        return $events;
    }

    /**
     * @param $query
     * @param array $filter_array => [
     *                                  [categories], [cities],
     *                                  [companies], price_max, price_min,
     *                                  distance, center=>[longitude, latitude]
     *                               ]
     * @param int $page
     * @param int $page_size
     * @return array
     */
    public static function findEventsWithFilters($query, array $filter_array,
                                                   $page = 1, $page_size = Events::DEFAULT_RESULT_PER_PAGE)
    {
        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        /*require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);

        if ($query == null || trim($query) == '')
            $cl->SetMatchMode(SPH_MATCH_ALL);
        else
            $cl->SetMatchMode(SPH_MATCH_ANY);

        $cl->SetLimits($offset, $page_size, 400);

        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        $cl->SetGeoAnchor('latitude', 'longitude',
            deg2rad($filter_array['center']['latitude']),
            deg2rad($filter_array['center']['longitude']));

        $cl->SetFilterFloatRange("@geodist", 0, 4000, false);

        $cl->AddQuery($query, 'events_with_filters_index');
        $results = $cl->RunQueries();*/

        /*$query = new CustomQuery([
            'from'=>'events',
            'columns'=>'*'
        ]);

        $di = DI::getDefault();
        $link = $di->getMysql()->openConnection();
        $stmt = $link->stmt_init();
        if(
        ($stmt->prepare($query->formSql())===FALSE) or
        ($stmt->bind_param('llq',
                deg2rad($filter_array['center']['latitude']),
                deg2rad($filter_array['center']['longitude']),
                $query) === FALSE) or
        (($result = $stmt->get_result()) === FALSE)){
            throw new \PDOException($stmt->error,$stmt->errno,null);
        };

        $di->mysql->closeConnection($link);*/

        /*$conn = new \mysqli('127.0.0.1','root','',null,9306);

        $resource = $conn->query("SELECT * FROM events_with_filter_index WHERE MATCH('') LIMIT 0,10");



        if($resource->fetch_assoc()===true)	while ($row = $resource->fetch_assoc()){
            var_dump($row);
        }

        $resource->free_result();*/

        $di = DI::getDefault();
        $di->getMysql()->openConnection();

        $query = new CustomQuery([
            'from'=>'events_with_filters_index',
            'where'=>'MATCH(:query)',
            'bind'=>['query'=>$query],
            'order'=>'WEIGHT() desc',
            'limit'=>$offset.','.($offset+$page_size),
        ]);

        if (!empty($filter_array['center']) && is_array($filter_array['center'])
        && isset($filter_array['center']['longitude']) && isset($filter_array['center']['latitude'])) {
            $query->setColumns('*, GEODIST(`latitude`, `longitude`, '.deg2rad($filter_array['center']['latitude']).', 
            '.deg2rad($filter_array['center']['longitude']).') as g, IF(`radius`>g,1,0) as `geomatch`');
            $query->addWhere('`geomatch` = 1');
            $query->setOrder('g asc, WEIGHT() desc');
        }

        $result = $di->getMysql()->executeQuery($query);

        $result['data'] = self::handleEventsFromSearch($result['data']);
        return $result;
    }
}
