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

class Customer_sms_messagesController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('customer-sms-messages.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }

    public function actionIndex()
    {
        $request = Yii::app()->request;
        $message = new CustomerSMSMessage('search');

        $message->unsetAttributes();
        $message->attributes = (array)$request->getQuery($message->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_messages', 'View SMS Messages'),
            'pageHeading'     => Yii::t('sms_messages', 'View SMS Messages'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customer_sms_messages/index'),
                Yii::t('sms_messages', 'SMS Messages')   => $this->createUrl('customer_sms_messages/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('index', compact('message'));
    }

    /**
     * Create a new message
     */
    public function actionCreate()
    {
        $message = new CustomerSMSMessage();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($message->modelName, array()))) {
            $message->attributes = $attributes;
            $message->sms_message    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$message->modelName]['sms_message']);

            if (!$message->validate()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $message->customer_phone = $string = str_replace(' ', '', $message->customer_phone);
                $arr_phone=explode(",",$message->customer_phone);
                try {
                    $client = new Clx\Xms\Client('saberlinkl1', '5841eb6a74e049d29b9b609ba97cca54');
                    $batchParams = new \Clx\Xms\Api\MtBatchTextSmsCreate();
                    $batchParams->setSender('25720');
                    $batchParams->setRecipients($arr_phone);
                    $batchParams->setBody($message->sms_message);
                    $batchParams->setDeliveryReport(Clx\Xms\DeliveryReportType::FULL);
                    $batchParams->setCallbackUrl(MW_SMS_DELIBERY_CALLBACK_PATH);
                    $msg = $client->createTextBatch($batchParams);
                    
                    for($i=0; $i<count($arr_phone); $i++){
                        $message = new CustomerSMSMessage();
                        $message->attributes = $attributes;
                        $message->sms_message    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$message->modelName]['sms_message']);
                        $message->status = 'Unknown';
                        $message->sms_message_uid = $msg->getBatchId();
                        $message->customer_phone = $arr_phone[$i];
                        $message->save();
                    }
                    $batch = $client->fetchBatch($msg->getBatchId());
                    $notify->addSuccess(Yii::t('app', "Message Sent!"));
                    // $result = $client->fetchDeliveryReport($msg->getBatchId(), Clx\Xms\DeliveryReportType::SUMMARY);
                    // $status = $result->getStatuses()[0]->getStatus();

                    // while($status == 'Dispatched' && $status == 'Queued'){
                    //     $result = $client->fetchDeliveryReport($msg->getBatchId(), Clx\Xms\DeliveryReportType::SUMMARY);
                    //     $status = $result->getStatuses()[0]->getStatus();
                    // }
                    // if($status == 'Delivered')
                    //     $notify->addSuccess(Yii::t('app', "Message ".$status."!"));
                    // else
                    //     $notify->addError(Yii::t('app', "Message ".$status."."));
                }
                catch (Exception $ex) {
                    $notify->addError(Yii::t('app', $ex->getMessage()));
                }
            }
            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $message,
            )));

            if ($collection->success) {
                $this->redirect(array('customer_sms_messages/index'));
            }
        }

        $message->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_messages', 'Create new sms message'),
            'pageHeading'     => Yii::t('sms_messages', 'Create new message'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('sms_messages', 'SMS Messages') => $this->createUrl('customer_sms_messages/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('message'));
    }

    /**
     * Update existing message
     */
    public function actionUpdate($id)
    {
        $message = CustomerSMSMessage::model()->findByPk((int)$id);

        if (empty($message)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($message->modelName, array()))) {
            $message->attributes = $attributes;
            $message->sms_message    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$message->modelName]['sms_message']);

            if (!$message->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $message,
            )));

            if ($collection->success) {
                $this->redirect(array('customer_sms_messages/index'));
            }
        }

        $message->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_messages', 'Update message'),
            'pageHeading'     => Yii::t('sms_messages', 'Update message'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('sms_messages', 'SMS Messages')   => $this->createUrl('customer_sms_messages/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('message'));
    }

    /**
     * View message
     */
    public function actionView($id)
    {
        $message = CustomerSMSMessage::model()->findByPk((int)$id);

        if (empty($message)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_messages', 'View message'),
            'pageHeading'     => Yii::t('sms_messages', 'View message'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('sms_messages', 'SMS Messages')   => $this->createUrl('customer_sms_messages/index'),
                Yii::t('app', 'View'),
            )
        ));

        $this->render('view', compact('message'));
    }
 
    /**
     * Delete existing customer message
     */
    public function actionDelete($id)
    {
        $message = CustomerSMSMessage::model()->findByPk((int)$id);

        if (empty($message)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $message->delete();

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $redirect = $request->getPost('returnUrl', array('customer_sms_messages/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $message,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    public function actionDelete_all()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'sms_message_id';
        $criteria->limit  = 500;

        $models = CustomerSMSMessage::model()->findAll($criteria);

        while (!empty($models)) {
            foreach ($models as $model) {
                $model->delete();
            }
            $models = CustomerSMSMessage::model()->findAll($criteria);
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'Your items have been successfully deleted!'));
            $this->redirect($request->getPost('returnUrl', array('customer_sms_messages/index')));
        }
    }
        
    /**
     * Callback method to set the editor options for email footer in campaigns
     */
    public function _setEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('message'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id'] = CHtml::activeId($event->sender->owner, $event->params['attribute']);

        if ($event->params['attribute'] == 'notification_message') {
            $options['height'] = 100;
        }

        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('create', 'update'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
