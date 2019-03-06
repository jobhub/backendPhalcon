<?php

namespace App\Services;

use App\Libs\PHPMailerApp;

/**
 * Class AbstractService
 *
 * @property \Phalcon\Db\Adapter\Pdo\Postgresql $db
 * @property \Phalcon\Config $config
 */
abstract class AbstractService extends \Phalcon\DI\Injectable
{
    const TYPE_USER = 'user';
    const TYPE_NEWS = 'news';
    const TYPE_REVIEW = 'review';
    const TYPE_SERVICE = 'service';
    const TYPE_COMPANY = 'company';
    const TYPE_TEMP = 'temp';
    const TYPE_RASTRENIYA = 'rastreniya';
    const TYPE_PRODUCT = 'product';
    const TYPE_EVENT = 'event';

    /**
     * Invalid parameters anywhere
     */
    const ERROR_INVALID_PARAMETERS = 100001;

    /**
     * Record already exists
     */
    const ERROR_ALREADY_EXISTS = 100002;

    const ERROR_UNABLE_SEND_TO_MAIL = 100003;
    const ERROR_UNABLE_SEND_TO_SMS = 100004;


    const ERROR_INVALID_OBJECT_TYPE = 100005;

    public function sendMail($action, $view, $data, $title)
    {
        $mailer = new PHPMailerApp($this->config['mail']);
        $newTo = 'titow.german@yandex.ru';//$this->config['mail']['from']['email'];
        $res = $mailer->createMessageFromView($view, $action, $data)
            ->to($newTo/*$data['email']*/)
            ->subject($title)
            ->send();

        if ($res === true) {
            return ['status' => STATUS_OK];
        } else {
            throw new ServiceExtendedException('Unable to send email', self::ERROR_UNABLE_SEND_TO_MAIL, null, null, ['sending_error' => $res]);
        }
    }

    public function sendSms($phone, $message){
        try {
            $sms = $this->di->get('SMS');
            $response = $sms->call('SmsAero')->setRecipient('NUMBER')->send('MESSAGE');

            return $response;
        }catch(\Exception $e){
            throw new ServiceException($e->getMessage(),self::ERROR_UNABLE_SEND_TO_SMS,$e);
        }
    }

    public function log($message)
    {
        $this->logger->log($message);
    }
}