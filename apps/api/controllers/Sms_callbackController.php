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
    public function accessRules()
    {
        return array(
            // allow all users on all actions
            array('allow'),
        );
    }

    public function actionDelivery_Status()
    {
        $request = Yii::app()->request;
        $arr_status = (array)$request->getQuery();
        $output = implode(', ', array_map( function ($v, $k) { return sprintf("%s='%s'", $k, $v); }, $arr_status, array_keys($arr_status)));
        Yii::log($output, CLogger::LEVEL_ERROR);
    }

    public function actionInbounce_Messages()
    {
        $request = Yii::app()->request;
        $arr_status = (array)$request->getQuery();
        $output = implode(', ', array_map( function ($v, $k) { return sprintf("%s='%s'", $k, $v); }, $arr_status, array_keys($arr_status)));
        Yii::log($output, CLogger::LEVEL_ERROR);
    }
}
