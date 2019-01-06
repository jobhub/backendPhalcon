<?php

namespace App\Models;

use Phalcon\DI\FactoryDefault as DI;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;



class TradePoints extends AccountWithNotDeletedWithCascade
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $point_id;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
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
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    protected $fax;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=true)
     */
    protected $time;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=true)
     */
    protected $email;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $user_manager;

    /**
     *
     * @var string
     * @Column(type="string", length=90, nullable=true)
     */
    protected $website;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $address;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $position_variable;

    const publicColumns = ['point_id','name', 'longitude', 'latitude', 'time',
        'email', 'user_manager', 'website', 'address', 'position_variable'];

    /**
     * Method to set the value of field pointId
     *
     * @param integer $pointid
     * @return $this
     */
    public function setPointId($pointid)
    {
        $this->point_id = $pointid;

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
     * Method to set the value of field fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Method to set the value of field time
     *
     * @param string $time
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Method to set the value of field email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Method to set the value of field userManager
     *
     * @param integer $usermanager
     * @return $this
     */
    public function setUserManager($usermanager)
    {
        $this->user_manager = $usermanager;

        return $this;
    }

    /**
     * Method to set the value of field webSite
     *
     * @param string $website
     * @return $this
     */
    public function setWebSite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Method to set the value of field webSite
     *
     * @param string $positionvariable
     * @return $this
     */
    public function setPositionVariable($positionvariable)
    {
        $this->position_variable = $positionvariable;

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
     * Returns the value of field pointId
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->point_id;
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
     * Returns the value of field fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Returns the value of field time
     *
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Returns the value of field email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the value of field email
     *
     * @return string
     */
    public function getPositionVariable()
    {
        return $this->position_variable;
    }

    /**
     * Returns the value of field userManager
     *
     * @return integer
     */
    public function getUserManager()
    {
        return $this->user_manager;
    }

    /**
     * Returns the value of field webSite
     *
     * @return string
     */
    public function getWebSite()
    {
        return $this->website;
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
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if ($this->getEmail() != null)
            $validator->add(
                'email',
                new EmailValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, корректный email',
                    ]
                )
            );
        if ($this->getWebSite() != null)
            $validator->add(
                'website',
                new UrlValidator(
                    [
                        'model' => $this,
                        'message' => 'Введите, пожалуйста, корректный URL',
                    ]
                )
            );

        if ($this->getUserManager() != null) {
            $validator->add(
                'user_manager',
                new Callback(
                    [
                        "message" => "Такого пользователя не существует",
                        "callback" => function ($company) {
                            $user = Users::findFirstByUserId($company->getUserManager());
                            if ($user)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        /*if($this->getSubjectType()==0 && $this->getPointId() == null){
            $validator->add(
                'subjectid',
                new Callback(
                    [
                        "message" => "Нельзя добавить больше одной точки оказания услуг для пользователя",
                        "callback" => function ($tradePoint) {
                            $tradePoint = TradePoints::findBySubject($tradePoint->getSubjectId(), $tradePoint->getSubjectType());
                            if (count($tradePoint)==0)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }*/

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("tradePoints");
        $this->hasMany('point_id', 'App\Models\PhonesPoints', 'point_id', ['alias' => 'PhonesPoints']);
        $this->belongsTo('user_manager', 'App\Models\Users', 'user_id', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tradePoints';
    }

    public function getSequenceName()
    {
        return "tradepoints_pointid_seq";
    }

    public static function findServicesForPoint($pointId)
    {
        $modelsManager = Phalcon\DI::getDefault()->get('modelsManager');
        $result = $modelsManager->createBuilder()
            ->columns(self::publicColumns)
            ->from(["s" => "App\Models\Services"])
            ->join('App\Models\ServicesPoints','s.service_id = sp.service_id','sp')
            ->join('App\Models\TradePoints', 'sp.point_id = p.point_id', 'p')
            ->where('p.point_id = :pointId:',['pointId'=>$pointId])
            ->getQuery()
            ->execute();

        return $result;
    }

    public static function findPointsByCompany($companyId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        $columns = [];
        foreach (self::publicColumns as $publicColumn) {
            $columns[] = 'tp.' . $publicColumn;
        }
        try {
            $result = $modelsManager->createBuilder()
                ->columns($columns)
                ->from(["tp" => "App\Models\TradePoints"])
                ->join('App\Models\Accounts', 'tp.account_id = a.id', 'a')
                ->where('a.company_id = :companyId: and tp.deleted = false', ['companyId' => $companyId])
                ->getQuery()
                ->execute();
        }catch(\Exception $e){
            echo $e;
        }

        return $result;
    }

    public static function findPointsByUser($userId)
    {
        $modelsManager = DI::getDefault()->get('modelsManager');
        $result = $modelsManager->createBuilder()
            ->columns(self::publicColumns)
            ->from(["tp" => "App\Models\TradePoints"])
            ->join('App\Models\Accounts', 'tp.account_id = a.id and a.company_id is null', 'a')
            ->where('a.user_id = :userId: and n.deleted = false', ['userId' => $userId])
            ->getQuery()
            ->execute();

        return $result;
    }
}
