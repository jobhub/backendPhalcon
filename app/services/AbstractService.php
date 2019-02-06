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
    /**
     * Invalid parameters anywhere
     */
    const ERROR_INVALID_PARAMETERS = 10001;

    /**
     * Record already exists
     */
    const ERROR_ALREADY_EXISTS = 10002;

    const ERROR_UNABLE_SEND_TO_MAIL = 100003;

    public function sendMail($action, $view, $data, $title)
    {
        $mailer = new PHPMailerApp($this->config['mail']);
        //$newTo = 'titow.german@yandex.ru';//$this->config['mail']['from']['email'];
        $res = $mailer->createMessageFromView($view, $action, $data)
            ->to(/*$newTo*/$data['email'])
            ->subject($title)
            ->send();

        if ($res === true) {
            return ['status' => STATUS_OK];
        } else {
            throw new ServiceExtendedException('Unable to send email', self::ERROR_UNABLE_SEND_TO_MAIL, null, null, ['sending_error' => $res]);
        }
    }

    public function log($message)
    {
        $this->logger->log($message);
    }
}