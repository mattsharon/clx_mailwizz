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
        $rawbody = Yii::app()->request->getRawBody();
        $request = json_decode($rawbody, true);
        $message_uid = $request['batch_id'];
        $arr_status = $request['statuses'];
        $status = $arr_status[0]['status'];
        $recipient = $arr_status[0]['recipients'][0];

        $message = CustomerSMSMessage::model()->find('sms_message_uid=:sms_message_uid', array(':sms_message_uid'=>$message_uid));
        if (empty($message)) {
            Yii::log("Callback -- Cannot Find sms_message_uid", CLogger::LEVEL_ERROR);
        }
        else{
            $message->status = $status;
            $message->update();
            Yii::log("sms_message_uid: ".$message_uid."  status: ".$status, CLogger::LEVEL_INFO);
        }
        
    }

    public function actionInbounce_Messages()
    {
        $request = Yii::app()->request->getRawBody();
        Yii::log($request, CLogger::LEVEL_INFO);
    }
}
