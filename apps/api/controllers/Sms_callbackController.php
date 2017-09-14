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
        Yii::log("report: ".$rawbody, CLogger::LEVEL_ERROR);    
        $request = json_decode($rawbody, true);
        if(isset($request['type']) && ($request['type'] == "delivery_report_sms")){
            $count = $request['total_message_count'];
            $message_uid = $request['batch_id'];
            $arr_status = $request['statuses'];
            for($i = 0; $i < $count; $i++ ){
                $status = $arr_status[$i]['status'];
                $recipient = $arr_status[$i]['recipients'][0];
                $message = CustomerSMSMessage::model()->find('sms_message_uid=:sms_message_uid and customer_phone=:customer_phone', array(':sms_message_uid'=>$message_uid, ':customer_phone'=>$recipient));
                if (empty($message)) {
                    Yii::log("Callback -- Cannot Find sms_message_uid", CLogger::LEVEL_ERROR);
                }
                else{
                    $message->status = $status;
                    $message->update();
                    Yii::log("phone: ".$recipient."  status: ".$status, CLogger::LEVEL_ERROR);
                }
            }  
        }
        else{
            Yii::log("report: unknown", CLogger::LEVEL_ERROR); 
        }
        
    }

    public function actionInbound_Messages()
    {
        $rawbody = Yii::app()->request->getRawBody();
        $request = json_decode($rawbody, true);
        $client = new Clx\Xms\Client('saberlinkl1', '5841eb6a74e049d29b9b609ba97cca54');
        if(isset($request['type']) && ($request['type'] == "mo_text" || $request['type'] == "mo_binary")){
            $body = strtolower($request['body']);
            if($body == 'help'){
                Yii::log("inbound:".$body, CLogger::LEVEL_ERROR);
                $template = SmsTemplate::model()->find('type=:type', array(':type'=>$body));
                if (empty($template)) {
                    Yii::log("inbound: Cannot find help sms template", CLogger::LEVEL_ERROR);
                }
                else{
                    try{
                        $batchParams = new \Clx\Xms\Api\MtBatchTextSmsCreate();
                        $batchParams->setSender('25720');
                        $batchParams->setRecipients([$request['from']]);
                        $batchParams->setBody($template->content);
                        $batchParams->setDeliveryReport(Clx\Xms\DeliveryReportType::FULL);
                        $msg = $client->createTextBatch($batchParams);
                        $batch = $client->fetchBatch($msg->getBatchId());
                        Yii::log("inbound: message id is ".$msg->getBatchId(), CLogger::LEVEL_ERROR);
                    }catch(Exception $ex){
                        Yii::log("inbound: error->".$ex->getMessage(), CLogger::LEVEL_ERROR);
                    }
                }
            }
            else if($body == 'stop'){
                $suppressionlist  = new Phonesuppressionlist();
                $suppressionlist->phone = $request['from'];
                $suppressionlist->reason = $body;
                if (!$suppressionlist->save()) {
                    Yii::log("suppressionlist: error save ".$suppressionlist->phone, CLogger::LEVEL_ERROR);
                } else {
                    Yii::log("suppressionlist: success save ".$suppressionlist->phone, CLogger::LEVEL_ERROR);
                }
            }
            else{
                $template = SmsTemplate::model()->find('type=:type', array(':type'=>'unknown'));
                if (empty($template)) {
                    Yii::log("inbound: Cannot find unknown sms template", CLogger::LEVEL_ERROR);
                }
                else{
                    try{
                        $batchParams = new \Clx\Xms\Api\MtBatchTextSmsCreate();
                        $batchParams->setSender('25720');
                        $batchParams->setRecipients([$request['from']]);
                        $batchParams->setBody($template->content);
                        $batchParams->setDeliveryReport(Clx\Xms\DeliveryReportType::FULL);
                        $msg = $client->createTextBatch($batchParams);
                        $batch = $client->fetchBatch($msg->getBatchId());
                        Yii::log("inbound: message id is ".$msg->getBatchId(), CLogger::LEVEL_ERROR);
                    }catch(Exception $ex){
                        Yii::log("inbound: error->".$ex->getMessage(), CLogger::LEVEL_ERROR);
                    }
                }
            }
        }
        else{
            Yii::log("inbound: unknown".$rawbody, CLogger::LEVEL_ERROR);
        }
        
    }
}
