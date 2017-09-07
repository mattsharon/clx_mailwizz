<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Email_blacklistController
 *
 * Handles the actions for blacklisted emails related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class Phone_blacklistController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        set_time_limit(0);
        
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('phone-blacklist.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, delete_all',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all blacklisted emails.
     * Delivery to blacklisted emails is denied
     */
    public function actionIndex()
    {
        $notify    = Yii::app()->notify;
        $request   = Yii::app()->request;
        $blacklist = new PhoneBlacklist('search');

        $blacklist->unsetAttributes();
        $blacklist->attributes = (array)$request->getQuery($blacklist->modelName, array());


        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('phone_blacklist', 'Blacklisted phone numbers'),
            'pageHeading'       => Yii::t('phone_blacklist', 'Blacklisted phone numbers'),
            'pageBreadcrumbs'   => array(
                Yii::t('phone_blacklist', 'Blacklisted phone numbers') => $this->createUrl('phone_blacklist/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('blacklist'));
    }

    /**
     * Add a new email in the blacklist
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $blacklist  = new PhoneBlacklist();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($blacklist->modelName, array()))) {
            $blacklist->attributes = $attributes;
            if (!$blacklist->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'blacklist' => $blacklist,
            )));

            if ($collection->success) {
                $this->redirect(array('phone_blacklist/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('phone_blacklist', 'Blacklisted phone numbers'),
            'pageHeading'       => Yii::t('phone_blacklist', 'Add a new phone number to blacklist.'),
            'pageBreadcrumbs'   => array(
                Yii::t('phone_blacklist', 'Blacklisted phone numbers') => $this->createUrl('phone_blacklist/index'),
                Yii::t('app', 'Add new'),
            )
        ));

        $this->render('form', compact('blacklist'));
    }

    /**
     * Update an existing email from the blacklist
     */
    public function actionUpdate($id)
    {
        $blacklist = PhoneBlacklist::model()->findByPk((int)$id);

        if (empty($blacklist)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($blacklist->modelName, array()))) {
            $blacklist->attributes = $attributes;
            if (!$blacklist->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'blacklist' => $blacklist,
            )));

            if ($collection->success) {
                $this->redirect(array('phone_blacklist/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('phone_blacklist', 'Blacklisted phone numbers'),
            'pageHeading'       => Yii::t('phone_blacklist', 'Update blacklisted phone number.'),
            'pageBreadcrumbs'   => array(
                Yii::t('phone_blacklist', 'Blacklisted phone numbers') => $this->createUrl('phone_blacklist/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('blacklist'));
    }

    /**
     * Delete an email from the blacklist.
     * Once removed from the blacklist, the delivery servers will be able to deliver the email to the removed address
     */
    public function actionDelete($id)
    {
        $blacklist = PhoneBlacklist::model()->findByPk((int)$id);

        if (empty($blacklist)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $blacklist->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('phone_blacklist/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $blacklist,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Run a bulk action against the email blacklist
     */
    public function actionBulk_action()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $action = $request->getPost('bulk_action');
        $items  = array_unique(array_map('intval', (array)$request->getPost('phone_id', array())));

        if ($action == PhoneBlacklist::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                $phone = PhoneBlacklist::model()->findByPk((int)$item);
                if (empty($phone)) {
                    continue;
                }

                $phone->delete();
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        }

        $defaultReturn = $request->getServer('HTTP_REFERER', array('phone_blacklist/index'));
        $this->redirect($request->getPost('returnUrl', $defaultReturn));
    }

    /**
     * Delete all the emails from the blacklist
     */
    public function actionDelete_all()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'phone_id, phone';
        $criteria->limit  = 500;

        $models = PhoneBlacklist::model()->findAll($criteria);

        while (!empty($models)) {
            foreach ($models as $model) {
                $model->delete();
            }
            $models = PhoneBlacklist::model()->findAll($criteria);
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'Your items have been successfully deleted!'));
            $this->redirect($request->getPost('returnUrl', array('phone_blacklist/index')));
        }
    }

    /**
     * Export blacklisted emails
     */
    public function actionExport()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $redirect   = array('phone_blacklist/index');

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('phone_blacklist', 'Cannot open export temporary file!'));
            $this->redirect($redirect);
        }

        $fileName = 'phone-blacklist-' . date('Y-m-d-h-i-s') . '.csv';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header('Content-type: application/csv');
        header("Content-Transfer-Encoding: Binary");
        header('Content-Disposition: attachment; filename="'.$fileName.'"');

        // columns
        $columns = array(
            Yii::t('phone_blacklist', 'Phone'),
            Yii::t('phone_blacklist', 'Reason'),
            Yii::t('phone_blacklist', 'Date added')
        );
        fputcsv($fp, $columns, ',', '"');

        // rows
        $limit  = 500;
        $offset = 0;
        $models = $this->getBlacklistedModels($limit, $offset);
        while (!empty($models)) {
            foreach ($models as $model) {
                $row = array($model->phone, $model->reason, $model->dateAdded);
                fputcsv($fp, $row, ',', '"');
            }
            if (connection_status() != 0) {
                @fclose($fp);
                exit;
            }
            $offset = $offset + $limit;
            $models = $this->getBlacklistedModels($limit, $offset);
        }

        @fclose($fp);
        exit;
    }

    protected function getBlacklistedModels($limit = 100, $offset = 0)
    {
        $criteria = new CDbCriteria;
        $criteria->select = 't.phone, t.reason, t.date_added';
        $criteria->limit    = (int)$limit;
        $criteria->offset   = (int)$offset;
        return PhoneBlacklist::model()->findAll($criteria);
    }

    /**
     * Import blacklisted emails
     */
    public function actionImport()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $redirect   = array('phone_blacklist/index');

        if (!$request->isPostRequest) {
            $this->redirect($redirect);
        }

        ini_set('auto_detect_line_endings', true);

        $import = new PhoneBlacklist('import');
        $import->file = CUploadedFile::getInstance($import, 'file');

        if (!$import->validate()) {
            $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            $notify->addError($import->shortErrors->getAllAsString());
            $this->redirect($redirect);
        }

        $delimiter = StringHelper::detectCsvDelimiter($import->file->tempName);
        $file = new SplFileObject($import->file->tempName);
        $file->setCsvControl($delimiter);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
        $columns = $file->current(); // the header

        if (!empty($columns)) {
            $columns = array_map('strtolower', $columns);
            if (array_search('phone', $columns) === false) {
                $columns = null;
            }
        }

        if (empty($columns)) {
            $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            $notify->addError(Yii::t('phone_blacklist', 'Your file does not contain the header with the fields title!'));
            $this->redirect($redirect);
        }

        $ioFilter     = Yii::app()->ioFilter;
        $columnCount  = count($columns);
        $totalRecords = 0;
        $totalImport  = 0;

        while (!$file->eof()) {

            ++$totalRecords;

            $row = $file->fgetcsv();
            if (empty($row)) {
                continue;
            }

            $row = $ioFilter->stripPurify($row);
            $rowCount = count($row);

            if ($rowCount == 0) {
                continue;
            }

            $isEmpty = true;
            foreach ($row as $value) {
                if (!empty($value)) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                continue;
            }

            if ($columnCount > $rowCount) {
                $fill = array_fill($rowCount, $columnCount - $rowCount, '');
                $row = array_merge($row, $fill);
            } elseif ($rowCount > $columnCount) {
                $row = array_slice($row, 0, $columnCount);
            }

            $model = new PhoneBlacklist();
            $data  = new CMap(array_combine($columns, $row));
            $model->phone = $data->itemAt('phone');
            $model->reason = $data->itemAt('reason');
            if ($model->save()) {
                $totalImport++;
            }
            unset($model, $data);
        }

        $notify->addSuccess(Yii::t('phone_blacklist', 'Your file has been successfuly imported, from {count} records, {total} were imported!', array(
            '{count}'   => ($totalRecords - 1),
            '{total}'   => $totalImport,
        )));

        $this->redirect($redirect);
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('index', 'create', 'update'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
