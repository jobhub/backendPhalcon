<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use App\Libs\SphinxClient;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

use App\Libs\SupportClass;
use PHPMailer\PHPMailer\Exception;

class Services extends AccountWithNotDeletedWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $service_id;

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
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $price_min;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $price_max;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $region_id;


    /**
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $latitude;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $number_of_display;

    protected $rating;

    const publicColumns = ['service_id', 'description', 'date_publication', 'price_min', 'price_max',
        'region_id', 'name', 'rating'];

    const publicColumnsInStr = 'service_id, description, date_publication, price_min, price_max,
        region_id, name, rating';

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $service_id
     * @return $this
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;

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
     * Method to set the value of field datePublication
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
     * Method to set the value of field priceMin
     *
     * @param integer $price_min
     * @return $this
     */
    public function setPriceMin($price_min)
    {
        $this->price_min = $price_min;

        return $this;
    }

    /**
     * Method to set the value of field priceMax
     *
     * @param integer $price_max
     * @return $this
     */
    public function setPriceMax($price_max)
    {
        $this->price_max = $price_max;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $region_id
     * @return $this
     */
    public function setRegionId($region_id)
    {
        $this->region_id = $region_id;

        return $this;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Method to set the value of field longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Method to set the value of field latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Method to set the value of field numberOfDisplay
     *
     * @param integer $numberOfDisplay
     * @return $this
     */
    public function setNumberOfDisplay($numberOfDisplay)
    {
        $this->number_of_display = $numberOfDisplay;
        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->region_id;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->service_id;
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
     * Returns the value of field datePublication
     *
     * @return string
     */
    public function getDatePublication()
    {
        return $this->date_publication;
    }

    /**
     * Returns the value of field priceMin
     *
     * @return integer
     */
    public function getPriceMin()
    {
        return $this->price_min;
    }

    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Returns the value of field priceMax
     *
     * @return integer
     */
    public function getPriceMax()
    {
        return $this->price_max;
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
     * Returns the value of field longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the value of field latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns the value of field numberOfDisplay
     *
     * @return integer
     */
    public function getNumberOfDisplay()
    {
        return $this->number_of_display;
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
            'price_min',
            new Callback(
                [
                    "message" => "Минимальная цена должна быть меньше (или равна) максимальной",
                    "callback" => function ($service) {
                        if ($service->getPriceMin() == null || $service->getPriceMax() == null)
                            return true;
                        if (!SupportClass::checkPositiveInteger($service->getPriceMin())
                            || !SupportClass::checkPositiveInteger($service->getPriceMax()))
                            return false;
                        if ($service->getPriceMin() <= $service->getPriceMax())
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'region_id',
            new Callback(
                [
                    "message" => "Для услуги должен быть указан регион",
                    "callback" => function ($service) {
                        $region = Regions::findFirstByRegionId($service->getRegionId());
                        if ($region)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            "date_publication",
            new PresenceOf(
                [
                    "message" => "Не указана дата опубликования услуги",
                ]
            )
        );

        if ($this->getLongitude() != null) {
            $validator->add(
                'latitude',
                new Callback(
                    [
                        "message" => "Не указана широта для услуги",
                        "callback" => function ($service) {
                            if ($service->getLatitude() != null && SupportClass::checkDouble($service->getLatitude()))
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        if ($this->getLatitude() != null) {
            $validator->add(
                'longitude',
                new Callback(
                    [
                        "message" => "Не указана долгота для услуги",
                        "callback" => function ($service) {
                            if ($service->getLongitude() != null && SupportClass::checkDouble($service->getLongitude()))
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        return $this->validate($validator) && parent::validation();
    }

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        if ($delete) {
            $images = ImagesServices::findByServiceId($this->getServiceId());
            foreach ($images as $image) {
                if (!$image->delete()) {
                    return false;
                };
            }
        }
        $result = parent::delete($delete, $deletedCascade, $data, $whiteList);

        return $result;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        parent::initialize();
        $this->setSchema("public");
        $this->setSource("services");
        $this->hasMany('service_id', 'App\Models\ServicesPoints', 'service_id', ['alias' => 'ServicesPoints']);
        $this->belongsTo('region_id', 'App\Models\Regions', 'region_id', ['alias' => 'Regions']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'services';
    }

    public static function getServices($categoriesId = null, $serviceid = null, $companyid = null)
    {
        $db = DI::getDefault()->getDb();
        if ($serviceid == null) {
            if ($companyid == null) {
                $query = $db->prepare("SELECT * FROM (SELECT row_to_json(serv) as \"service\",
                row_to_json(comp) as \"company\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.\"companiesCategories\" compcat ON (compcat.categoryid = cat.categoryid)
                                       WHERE comp.companyid = compcat.companyid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
               array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\"                        
              FROM public.companies as comp
              INNER JOIN public.services as serv ON (serv.subjectid = comp.companyid AND serv.subjecttype = 1
              AND serv.deleted = false AND comp.deleted = false))) foo
              ");

                $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.userscategories uc ON(uc.categoryid = cat.categoryid)
                                       WHERE uc.userid = us.userid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
              array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\" 
              FROM public.userinfo as us
              INNER JOIN public.services as serv ON (serv.subjectid = us.userid AND serv.subjecttype = 0
              AND serv.deleted = false) 
              INNER JOIN public.users ON (us.userid = public.users.userid))
              ) foo LIMIT 100");
                $query->execute();
                $query2->execute();
            } else {
                $query = $db->prepare("SELECT * FROM (SELECT row_to_json(serv) as \"service\",
                row_to_json(comp) as \"company\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.\"companiesCategories\" compcat ON (compcat.categoryid = cat.categoryid)
                                       WHERE comp.companyid = compcat.companyid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
               array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\"                        
              FROM public.companies as comp
              INNER JOIN public.services as serv ON (serv.subjectid = comp.companyid AND serv.subjecttype = 1
              AND serv.deleted = false AND comp.deleted = false)
              WHERE comp.companyid = :companyId 
              ) foo");

                $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.userscategories uc ON(uc.categoryid = cat.categoryid)
                                       WHERE uc.userid = us.userid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
              array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\" 
              FROM public.userinfo as us
              INNER JOIN public.services as serv ON (serv.subjectid = us.userid AND serv.subjecttype = 0
              AND serv.deleted = false) 
              INNER JOIN public.users ON (us.userid = public.users.userid) 
              WHERE false) 
              ) foo");
                $query->execute(['companyId' => $companyid]);
                $query2->execute(/*['companyId'=> $companyid]*/);
            }
        } else {
            $query = $db->prepare("SELECT * FROM (SELECT row_to_json(serv) as \"service\",
                row_to_json(comp) as \"company\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.\"companiesCategories\" compcat ON (compcat.categoryid = cat.categoryid)
                                       WHERE comp.companyid = compcat.companyid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
               array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\"                        
              FROM public.companies as comp
              INNER JOIN public.services as serv ON (serv.subjectid = comp.companyid AND serv.subjecttype = 1
              AND serv.deleted = false AND comp.deleted = false)
              WHERE serv.serviceid = :serviceId 
              ) foo");

            $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.userscategories uc ON(uc.categoryid = cat.categoryid)
                                       WHERE uc.userid = us.userid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
              array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\" 
              FROM public.userinfo as us
              INNER JOIN public.services as serv ON (serv.subjectid = us.userid AND serv.subjecttype = 0
              AND serv.deleted = false) 
              INNER JOIN public.users ON (us.userid = public.users.userid) 
              WHERE serv.serviceid = :serviceId) 
              ) foo");
            $query->execute(['serviceId' => $serviceid]);
            $query2->execute(['serviceId' => $serviceid]);
        }


        $services = $query->fetchAll(\PDO::FETCH_ASSOC);
        $servicesusers = $query2->fetchAll(\PDO::FETCH_ASSOC);
        $reviews2 = [];
        foreach ($services as $review) {
            $review2 = [];
            $review2['service'] = json_decode($review['service']);

            $review2['company'] = json_decode($review['company']);


            $review['categories'][0] = '[';
            $review['categories'][strlen($review['categories']) - 1] = ']';

            $review['categories'] = str_replace('"{', '{', $review['categories']);
            $review['categories'] = str_replace('}"', '}', $review['categories']);
            $review['categories'] = stripslashes($review['categories']);
            $review2['categories'] = json_decode($review['categories']);

            $review2['images'] = json_decode($review['images']);
            $review['images'][0] = '[';
            $review['images'][strlen($review['images']) - 1] = ']';

            $review['images'] = str_replace('"{', '{', $review['images']);
            $review['images'] = str_replace('}"', '}', $review['images']);
            $review['images'] = stripslashes($review['images']);
            $review2['images'] = json_decode($review['images']);

            $review['points'][0] = '[';
            $review['points'][strlen($review['points']) - 1] = ']';

            $review['points'] = str_replace('"{', '{', $review['points']);
            $review['points'] = str_replace('}"', '}', $review['points']);
            $review['points'] = stripslashes($review['points']);
            $review2['ratingcount'] = 45;

            $review2['points'] = json_decode($review['points'], true);

            for ($i = 0; $i < count($review2['points']); $i++) {
                $review2['points'][$i]['phones'] = [];
                $pps = PhonesPoints::findByPointid($review2['points'][$i]['pointid']);
                foreach ($pps as $pp)
                    $review2['points'][$i]['phones'][] = $pp->phones->getPhone();
            }

            //$review2['points'] = json_decode($review2['points']);

            if ($categoriesId != null) {
                $flag = false;
                foreach ($categoriesId as $categoryId) {
                    foreach ($review2['categories'] as $category) {
                        if ($category->categoryid == $categoryId) {
                            $flag = true;
                            break;
                        }
                    }
                    if ($flag) {
                        $reviews2[] = $review2;
                        break;
                    }
                }
            } else {
                $reviews2[] = $review2;
            }
        }

        foreach ($servicesusers as $review) {
            $review2 = [];
            $review2['service'] = json_decode($review['service']);
            $review2['Userinfo'] = json_decode($review['userinfo']);

            $review['categories'][0] = '[';
            $review['categories'][strlen($review['categories']) - 1] = ']';

            $review['categories'] = str_replace('"{', '{', $review['categories']);
            $review['categories'] = str_replace('}"', '}', $review['categories']);
            $review['categories'] = stripslashes($review['categories']);
            $review2['categories'] = json_decode($review['categories']);

            $review['images'][0] = '[';
            $review['images'][strlen($review['images']) - 1] = ']';

            $review['images'] = str_replace('"{', '{', $review['images']);
            $review['images'] = str_replace('}"', '}', $review['images']);
            $review['images'] = stripslashes($review['images']);
            $review2['images'] = json_decode($review['images']);
            $review2['ratingcount'] = 45;


            $review['points'][0] = '[';
            $review['points'][strlen($review['points']) - 1] = ']';

            $review['points'] = str_replace('"{', '{', $review['points']);
            $review['points'] = str_replace('}"', '}', $review['points']);
            $review['points'] = stripslashes($review['points']);
            $review2['points'] = json_decode($review['points'], true);

            for ($i = 0; $i < count($review2['points']); $i++) {
                $review2['points'][$i]['phones'] = [];
                $pps = PhonesPoints::findByPointid($review2['points'][$i]['pointid']);
                foreach ($pps as $pp)
                    $review2['points'][$i]['phones'][] = $pp->phones->getPhone();
            }

            if ($categoriesId != null) {
                $flag = false;
                foreach ($categoriesId as $categoryId) {
                    foreach ($review2['categories'] as $category) {
                        if ($category->categoryid == $categoryId) {
                            $flag = true;
                            break;
                        }
                    }
                    if ($flag) {
                        $reviews2[] = $review2;
                        break;
                    }
                }
            } else {
                $reviews2[] = $review2;
            }
        }

        return $reviews2;
    }

    private function sortFunction($a, $b)
    {
        return ($a['weight'] < $b['weight']) ? -1 : 1;
    }

    function cmp($a, $b)
    {
        if ($a['weight'] == $b['weight']) {
            return 0;
        }
        return ($a['weight'] < $b['weight']) ? -1 : 1;
    }

    /**
     * @param $query
     * @param $center
     * @param $diagonal
     * @param null $regions
     * @return array
     */
    public static function getServicesByQuery($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        //$cl->SetMatchMode(SPH_MATCH_ANY);
        if (trim($query) == '')
            $cl->SetMatchMode(SPH_MATCH_ALL);
        else
            $cl->SetMatchMode(SPH_MATCH_ANY);

        $cl->SetLimits(0, 10000, 50);
        $cl->SetFieldWeights(['name' => 100, 'description' => 10]);
        $cl->SetRankingMode(SPH_RANK_SPH04);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
            $cl->AddQuery($query, 'bro4you_small_index');
            $cl->ResetFilters();
        }
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'bro4you_small_index');

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allmatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        return self::handleServiceFromArrayForSearch($allmatches);
    }

    public static function getAutocompleteByQuery($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_ANY);
        $cl->SetRankingMode(SPH_RANK_SPH04);
        $cl->SetLimits(0, 10000, 40);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);
        $cl->SetFieldWeights(['name2' => 100, 'description2' => 10]);

        //Сначала поиск по компаниям
        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
        }

        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        //$cl->SetGeoAnchor('latitude', 'longitude', deg2rad(39.023), deg2rad(54.032));
        //$cl->SetFilterFloatRange("@geodist", 0, 50000000, false);

        $cl->AddQuery($query, 'companies_min_index');
        //$cl->ResetFilters();
        $cl->AddQuery($query, 'services_min_index');

        $cl->ResetFilters();
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }
        $cl->AddQuery('@*' . $query . '*'/*$query*/, 'categories_min_index');

        $results = $cl->RunQueries();

        /*var_dump($results);
        die;*/

        $allMatches = [];

        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allMatches = array_merge($allMatches, $result['matches']);
            }
        }

        $res = usort($allMatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        $output = [];

        for ($i = 0; $i < 10 && $i < count($allMatches); $i++) {
            $result = $allMatches[$i];
            $output[] = ['id' => $result['attrs']['element_id'], 'name' => $result['attrs']['name'],
                'type' => $result['attrs']['type'],
            ];
        }

        return $output;
    }

    public static function getServicesByElement($type, $elementIds, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        $cl->SetLimits(0, 10000, 50);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            if ($type == 'service') {
                $cl->setFilter('regionid', $regions, false);
                $cl->setFilter('servid', $elementIds, false);
                $cl->AddQuery('', 'bro4you_small_index');
                $cl->ResetFilters();
            } elseif ($type == 'company') {
                $cl->setFilter('regionid', $regions, false);
                $cl->setFilter('companyid', $elementIds, false);
                $cl->AddQuery('', 'services_with_company_index');
                $cl->ResetFilters();
            } elseif ($type == 'category') {
                $cl->setFilter('regionid', $regions, false);
                $cl->setFilter('categoryid', $elementIds, false);
                $cl->AddQuery('', 'services_with_category_index');
                $cl->ResetFilters();
            }
        }

        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        if ($type == 'service') {
            $cl->setFilter('servid', $elementIds, false);
            $cl->AddQuery('', 'bro4you_small_index');
        } elseif ($type == 'company') {
            $cl->setFilter('companyid', $elementIds, false);
            $cl->AddQuery('', 'services_with_company_index');
        } elseif ($type == 'category') {
            $cl->setFilter('categoryid', $elementIds, false);
            $cl->AddQuery('', 'services_with_category_index');
        }

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allMatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        return self::handleServiceFromArrayForSearch($allmatches);
    }

    public static function getServicesWithFilters($query, $center, $diagonal, $regions = null,
                                                  $categories = null, $priceMin = null, $priceMax = null, $ratingMin = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        $cl->SetLimits(0, 10000, 50);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null)
            $cl->setFilter('regionid', $regions, false);
        if ($categories != null)
            $cl->setFilter('categoryid', $categories, false);

        if ($priceMin != null)
            $cl->setFilterFloatRange('pricemin', $priceMin, 9223372036854775807, false);

        if ($priceMax != null)
            $cl->setFilterFloatRange('pricemax', 0, $priceMax, false);

        if ($ratingMin != null)
            $cl->setFilterFloatRange('rating', $ratingMin, 100.0, false);

        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));
            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);
            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'services_with_filters_index');
        $results = $cl->RunQueries();

        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allMatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        return self::handleServiceFromArrayForSearch($allmatches);
    }

    public static function getServicesByQuery2($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        $cl->SetLimits(0, 10000, 50);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
            $cl->AddQuery($query, 'bro4you_index');
            $cl->ResetFilters();
        }
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'bro4you_index');

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allmatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        foreach ($allmatches as $match) {
            $service['service'] = json_decode($match['attrs']['service'], true);
            $subject = json_decode($match['attrs']['subject'], true);
            if ($service['service']['subjecttype'] == 1)
                $service['company'] = $subject;
            else {
                $service['userinfo'] = $subject;
            }

            $service['categories'] = SupportClass::translateInPhpArrFromPostgreArr($match['attrs']['categories']);

            $service['images'] = SupportClass::translateInPhpArrFromPostgreArr($match['attrs']['images']);
            $points = SupportClass::translateInPhpArrFromPostgreArr($match['attrs']['points']);

            foreach ($points as $point) {
                $f = false;
                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($point['pointid'] == $pointid) {
                        $f = true;
                        break;
                    }
                }
                if ($f)
                    $service['points'][] = $point;
            }

            $services[] = $service;
        }

        return $services;
    }

    public static function getServicesByQueryByTags($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        //$cl->SetMatchMode(SPH_MATCH_ANY);
        if (trim($query) == '')
            $cl->SetMatchMode(SPH_MATCH_ALL);
        else
            $cl->SetMatchMode(SPH_MATCH_ANY);

        $cl->SetLimits(0, 10000, 50);
        $cl->SetRankingMode(SPH_RANK_SPH04);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
            $cl->AddQuery($query, 'bro4you_small_tags_index');
            $cl->ResetFilters();
        }
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'bro4you_small_tags_index');

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        if($results!=null)
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allmatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        return self::handleServiceFromArrayForSearch($allmatches);
    }

    /**
     * @param $subjectId
     * @param $subjectType
     * @return Возвращает массив услуг в виде:
     *      [{serviceid, description, datepublication, pricemin, pricemax,
     * regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
     * [Userinfo или Company]}]
     */
    public static function getServicesForSubject($subjectId, $subjectType)
    {
        $db = DI::getDefault()->getDb();

        $services = Services::findBySubject($subjectId, $subjectType, 'datepublication desc', Services::publicColumnsInStr);

        $servicesArr = json_encode($services);
        $servicesArr = json_decode($servicesArr, true);
        $servicesAll = [];

        if ($subjectType == 0) {
            $categories = UsersCategories::getCategoriesByUser($subjectId);
        } else {
            $categories = CompaniesCategories::getCategoriesByCompany($subjectId);
        }

        foreach ($servicesArr as $service) {
            $serviceAll = $service;
            $serviceAll['categories'] = $categories;
            $images = ImagesServices::findByServiceid($service['serviceid']);
            $serviceAll['images'] = [];
            foreach ($images as $image) {
                $serviceAll['images'][] = $image->getImagePath();
            }
            $points = Services::getPointsForService($service['serviceid']);
            $serviceAll['point'] = count($points) > 0 ?
                $points[0] : [];

            $tags = Services::getTagsForService($service['serviceid']);
            $serviceAll['tags'] = count($tags) > 0 ?
                $tags : [];

            if ($subjectType == 0) {
                $user = Userinfo::findFirst(
                    ['conditions' => 'userid = :subjectid:',
                        'columns' => Userinfo::publicColumnsInStr,
                        'bind' => ['subjectid' => $subjectId]]);

                $user = json_encode($user);
                $user = json_decode($user, true);
                $serviceAll['publisherUser'] = $user;
                $phones = PhonesUserinfo::getUserPhones($subjectId);
                //$newsWithAllElement['publisherUser']->setPhones($phones);
                $serviceAll['publisherUser']['phones'] = $phones;
            } else {
                $company = Companies::findFirst(
                    ['conditions' => 'companyid = :subjectid:',
                        'columns' => Companies::publicColumnsInStr,
                        'bind' => ['subjectid' => $subjectId]]);

                $company = json_encode($company);
                $company = json_decode($company, true);

                $serviceAll['publisherCompany'] = $company;
                $phones = PhonesCompanies::getCompanyPhones($serviceAll['publisherCompany']['companyid']);
                $serviceAll['publisherCompany']['phones'] = $phones;
            }

            $servicesAll[] = $serviceAll;
        }

        return $servicesAll;
    }

    public static function getTasksForService($serviceId)
    {
        $db = DI::getDefault()->getDb();
        return [];
    }

    public static function getPointsForService($serviceId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        $columns = [];
        foreach (TradePoints::publicColumns as $publicColumn) {
            $columns[] = 'p.' . $publicColumn;
        }
        /*try {*/
            $result = $modelsManager->createBuilder()
                ->columns($columns)
                ->from(["p" => "App\Models\TradePoints"])
                ->join('App\Models\ServicesPoints', 'p.point_id = sp.point_id', 'sp')
                ->join('App\Models\Services', 'sp.service_id = s.service_id', 's')
                ->where('s.service_id = :serviceId:', ['serviceId' => $serviceId])
                ->getQuery()
                ->execute();
        /*}catch(\Exception $e){
            echo $e;
        }*/

        return $result;
    }

    public static function getTagsForService($serviceId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');

        $result = $modelsManager->createBuilder()
            ->from(["t" => "App\Models\Tags"])
            ->join('App\Models\ServicesTags', 't.tag_id = st.tag_id', 'st')
            ->join('App\Models\Services', 'st.service_id = s.service_id', 's')
            ->where('s.service_id = :serviceId:', ['serviceId' => $serviceId])
            ->getQuery()
            ->execute();

        return $result;
    }

    public function clipToPublic()
    {
        $service = $this;
        $service = json_encode($service);
        $service = json_decode($service, true);
        unset($service['deleted']);
        unset($service['deletedcascade']);
        unset($service['numberofdisplay']);
        return $service;
    }

    public static function findServicesByUserId($userId){
        $modelsManager = DI::getDefault()->get('modelsManager');

        $result = $modelsManager->createBuilder()
            ->columns(self::publicColumns)
            ->from(["s" => "App\Models\Services"])
            ->join('App\Models\Accounts', 'a.id = s.account_id and a.company_id is null', 'a')
            ->where('a.user_id = :userId: and s.deleted = false', ['userId' => $userId])
            ->getQuery()
            ->execute();

        return self::handleServiceFromArray($result->toArray());
    }

    public static function findServicesByCompanyId($companyId){
        $modelsManager = DI::getDefault()->get('modelsManager');

        $result = $modelsManager->createBuilder()
            ->columns(self::publicColumns)
            ->from(["s" => "App\Models\Services"])
            ->join('App\Models\Accounts', 'a.id = s.account_id', 'a')
            ->where('a.company_id = :companyId: and s.deleted = false', ['companyId' => $companyId])
            ->getQuery()
            ->execute();

        return self::handleServiceFromArray($result->toArray());
    }

    public static function handleServiceFromArray(array $services){
        $servicesAll = [];
        foreach ($services as $service) {
            $serviceAll = $service;

            $account = Accounts::findFirstById($service['account_id']);

            if($account){
                if($account->getCompanyId()!=null){
                    $categories = CompaniesCategories::getCategoriesByCompany($account->getCompanyId());
                    $publisher = Companies::findFirst(
                        ['conditions' => 'company_id = :companyId:',
                            'columns' => Companies::publicColumnsInStr,
                            'bind' => ['companyId' => $account->getCompanyId()]])->toArray();


                    $phones = PhonesCompanies::getCompanyPhones($account->getCompanyId());
                    $publisher['phones'] = $phones;
                    $serviceAll['publisher_company'] = $publisher;
                } else{
                    $categories = UsersCategories::getCategoriesByUser($account->getUserId());
                    $publisher = Userinfo::findFirst(
                        ['conditions' => 'user_id = :userId:',
                            'columns' => Userinfo::publicColumnsInStr,
                            'bind' => ['userId' => $account->getUserId()]])->toArray();
                    $phones = PhonesUsers::getUserPhones($account->getUserId());
                    $publisher['phones'] = $phones;
                    $serviceAll['publisher_user'] = $publisher;
                }

                $serviceAll['categories'] = $categories;
            }

            $images = ImagesServices::findByServiceId($service['service_id']);
            $serviceAll['images'] = [];
            foreach ($images as $image) {
                $serviceAll['images'][] = $image->getImagePath();
            }

            $points = Services::getPointsForService($service['service_id']);
            $serviceAll['point'] = count($points) > 0 ?
                $points[0] : [];

            $tags = Services::getTagsForService($service['service_id']);
            $serviceAll['tags'] = count($tags) > 0 ?
                $tags : [];
           // $serviceAll['rating_count'] = count(Reviews::getReviewsForService($service['service']['service_id']));


            $servicesAll[] = $serviceAll;
        }
        return $servicesAll;
    }

    public static function handleServiceFromArrayForSearch(array $matches){
        $servicesAll = [];
        foreach ($matches as $match) {
            $service = json_decode($match['attrs']['service'],true);
            $serviceAll['service'] = $service;

            $account = Accounts::findFirstById($service['account_id']);

            if($account){
                if($account->getCompanyId()!=null){
                    $categories = CompaniesCategories::getCategoriesByCompany($account->getCompanyId());
                    $publisher = Companies::findFirst(
                        ['conditions' => 'company_id = :companyId:',
                            'columns' => Companies::publicColumnsInStr,
                            'bind' => ['companyId' => $account->getCompanyId()]])->toArray();


                    $phones = PhonesCompanies::getCompanyPhones($account->getCompanyId());
                    $publisher['phones'] = $phones;
                    $serviceAll['publisher_company'] = $publisher;
                } else{
                    $categories = UsersCategories::getCategoriesByUser($account->getUserId());
                    $publisher = Userinfo::findFirst(
                        ['conditions' => 'user_id = :userId:',
                            'columns' => Userinfo::publicColumnsInStr,
                            'bind' => ['userId' => $account->getUserId()]])->toArray();
                    $phones = PhonesUsers::getUserPhones($account->getUserId());
                    $publisher['phones'] = $phones;
                    $serviceAll['publisher_user'] = $publisher;
                }

                $serviceAll['categories'] = $categories;
            }

            $images = ImagesServices::findByServiceId($service['service_id']);
            $serviceAll['images'] = [];
            foreach ($images as $image) {
                $serviceAll['images'][] = $image->getImagePath();
            }

            if (count($serviceAll['images']) == 0) {
                $image = new ImagesServices();
                $image->setImagePath('/images/no_image.jpg');
                $image->setServiceId($serviceAll['service']['service_id']);
                $serviceAll['images'] = [$image];
            }

            if (count($match['attrs']['pointid']) > 0) {
                $str = '';
                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($str == '')
                        $str .= 'pointid IN (' . $pointid;
                    else {
                        $str .= ', ' . $pointid;
                    }
                }
                $str .= ')';

                $points = TradePoints::find([$str, 'columns' => TradePoints::publicColumns]);

                $service['points'] = $points;
            }

            $tags = Services::getTagsForService($service['service_id']);
            $serviceAll['tags'] = count($tags) > 0 ?
                $tags : [];
            //$serviceAll['rating_count'] = count(Reviews::getReviewsForService($service['service']['service_id']));


            $servicesAll[] = $serviceAll;
        }
        return $servicesAll;
    }

    public static function findServiceById($serviceId){
        return self::findFirst(['columns'=>self::publicColumns,'condition'=>'service_id = :serviceId:',
            'bind'=>['serviceId'=>$serviceId]]);
    }
}
