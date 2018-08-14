<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class Services extends SubjectsWithNotDeleted
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceid;

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
    protected $datepublication;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pricemin;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pricemax;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $regionid;


    /**
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->serviceid = $serviceid;

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
     * @param string $datepublication
     * @return $this
     */
    public function setDatePublication($datepublication)
    {
        $this->datepublication = $datepublication;

        return $this;
    }

    /**
     * Method to set the value of field priceMin
     *
     * @param integer $pricemin
     * @return $this
     */
    public function setPriceMin($pricemin)
    {
        $this->pricemin = $pricemin;

        return $this;
    }

    /**
     * Method to set the value of field priceMax
     *
     * @param integer $pricemax
     * @return $this
     */
    public function setPriceMax($pricemax)
    {
        $this->pricemax = $pricemax;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionid
     * @return $this
     */
    public function setRegionId($regionid)
    {
        $this->regionid = $regionid;

        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionid;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceid;
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
        return $this->datepublication;
    }

    /**
     * Returns the value of field priceMin
     *
     * @return integer
     */
    public function getPriceMin()
    {
        return $this->pricemin;
    }

    /**
     * Returns the value of field priceMax
     *
     * @return integer
     */
    public function getPriceMax()
    {
        return $this->pricemax;
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
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'pricemin',
            new Callback(
                [
                    "message" => "Минимальная цена должна быть меньше (или равна) максимальной",
                    "callback" => function ($service) {
                        if(!SupportClass::checkPositiveInteger($service->getPriceMin())
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
            'regionid',
            new Callback(
                [
                    "message" => "Для услуги должен быть указан регион",
                    "callback" => function ($service) {
                        $region = Regions::findFirstByRegionid($service->getRegionId());

                        if ($region)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            "datepublication",
            new PresenceOf(
                [
                    "message" => "Не указана дата опубликования услуги",
                ]
            )
        );

        return $this->validate($validator) && parent::validation();
    }

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        $result = parent::delete($delete, $deletedCascade, $data, $whiteList);

        return $result;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("services");
        $this->hasMany('serviceid', 'ServicesPoints', 'serviceid', ['alias' => 'ServicesPoints']);
        $this->belongsTo('regionid', '\Regions', 'regionid', ['alias' => 'Regions']);
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

    public function getServices($categoryId = null)
    {
        $db = $this->getDI()->getDb();
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
              AND serv.deleted = false AND comp.deleted = false)) foo");

        $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat
                                       WHERE cat.categoryid = 202034) as \"categories\",
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
              ) foo");


        $query->execute();
        $query2->execute();
        $services = $query->fetchAll(\PDO::FETCH_ASSOC);
        $servicesusers = $query2->fetchAll(\PDO::FETCH_ASSOC);
        $reviews2 = [];
        foreach ($services as $review) {
            $review2 = [];
            $review2['service'] = json_decode($review['service']);

            $review2['company'] = json_decode($review['company']);
            $review2['images'] = json_decode($review['images']);

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

            //$review2['points'] = json_decode($review2['points']);

            if ($categoryId != null) {
                $flag = false;
                foreach ($review2['categories'] as $category) {
                    if ($category == $categoryId) {
                        $flag = true;
                        break;
                    }
                }
                if ($flag)
                    $reviews2[] = $review2;
            } else {
                $reviews2[] = $review2;
            }
        }

        foreach ($servicesusers as $review) {
            $review2 = [];
            $review2['service'] = json_decode($review['service']);
            $review2['userinfo'] = json_decode($review['userinfo']);

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
            $reviews2[] = $review2;
        }

        return $reviews2;
    }

}
