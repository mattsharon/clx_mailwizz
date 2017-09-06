<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SettingsController
 *
 * Handles the settings for the application
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class Sms_callbackController extends Controller
{
    public function init()
    {
        parent::init();
    }

    public function actionDelivery_Status()
    {
        $type = '123123';
        Yii::log($type, CLogger::LEVEL_ERROR);
    }

    public function actionInbounce_Messages()
    {
        $type = '123123';
        Yii::log($type, CLogger::LEVEL_ERROR);
    }
}
