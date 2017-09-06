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
        $input = file_get_contents("php://input");
        Yii::log($input, CLogger::LEVEL_ERROR);
    }

    public function actionInbounce_Messages()
    {
        $str = "inbounce callback";
        print_r($str); exit;
    }
}
