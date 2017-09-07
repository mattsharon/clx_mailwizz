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

class Sms_templatesController extends Controller
{
    public function init()
    {
        parent::init();
    }

    public function actionIndex()
    {
        $request = Yii::app()->request;
        $template = new SmsTemplate('search');

        $template->unsetAttributes();
        $template->attributes = (array)$request->getQuery($template->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_templates', 'View SMS Templates'),
            'pageHeading'     => Yii::t('sms_templates', 'View SMS Templates'),
            'pageBreadcrumbs' => array(
                Yii::t('sms_templates', 'SMS Templates')   => $this->createUrl('sms_templates/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('index', compact('template'));
    }

    /**
     * Create a new message
     */
    public function actionCreate()
    {
        $template = new SmsTemplate();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($template->modelName, array()))) {
            $template->attributes = $attributes;
            $template->content    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$template->modelName]['content']);

            if (!$template->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $template,
            )));

            if ($collection->success) {
                $this->redirect(array('sms_templates/index'));
            }
        }

        $template->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_templates', 'Create new sms template'),
            'pageHeading'     => Yii::t('sms_templates', 'Create new template'),
            'pageBreadcrumbs' => array(
                Yii::t('sms_templates', 'SMS Templates') => $this->createUrl('sms_templates/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('template'));
    }

    /**
     * Update existing message
     */
    public function actionUpdate($id)
    {
        $template = SmsTemplate::model()->findByPk((int)$id);

        if (empty($template)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($template->modelName, array()))) {
            $template->attributes = $attributes;
            $template->content    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$template->modelName]['content']);

            if (!$template->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $template,
            )));

            if ($collection->success) {
                $this->redirect(array('sms_templates/index'));
            }
        }

        $template->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_templates', 'Update content'),
            'pageHeading'     => Yii::t('sms_templates', 'Update content'),
            'pageBreadcrumbs' => array(
                Yii::t('sms_templates', 'SMS Templates')   => $this->createUrl('sms_templates/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('template'));
    }

    /**
     * View message
     */
    public function actionView($id)
    {
        $template = SmsTemplate::model()->findByPk((int)$id);

        if (empty($template)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('sms_templates', 'View template'),
            'pageHeading'     => Yii::t('sms_templates', 'View template'),
            'pageBreadcrumbs' => array(
                Yii::t('sms_templates', 'SMS Templates')   => $this->createUrl('sms_templates/index'),
                Yii::t('app', 'View'),
            )
        ));

        $this->render('view', compact('template'));
    }

    /**
     * Delete existing customer message
     */
    public function actionDelete($id)
    {
        $template = SmsTemplate::model()->findByPk((int)$id);

        if (empty($template)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $template->delete();

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $redirect = $request->getPost('returnUrl', array('sms_templates/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $template,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
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
