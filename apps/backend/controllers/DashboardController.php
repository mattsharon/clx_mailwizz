<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DashboardController
 *
 * Handles the actions for dashboard related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

class DashboardController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->mergeWith(array(
            array('src' => AssetsUrl::js('dashboard.js'))
        ));
        parent::init();
    }

    /**
     * Display dashboard informations
     */
    public function actionIndex()
    {
        $options = Yii::app()->options;
        $notify  = Yii::app()->notify;
        
        if (file_exists(Yii::getPathOfAlias('root.install')) && is_dir($dir = Yii::getPathOfAlias('root.install'))) {
            $notify->addWarning(Yii::t('app', 'Please remove the install directory({dir}) from your application!', array(
                '{dir}' => $dir,
            )));
        }

        // since 1.3.6.3
        if ($options->get('system.installer.freshinstallextensionscheck', 0) == 0) {
            $options->set('system.installer.freshinstallextensionscheck', 1);
            
            $notify->clearAll()->addInfo(Yii::t('extensions', 'Conducting extensions checks for the fresh install...'));
            
            $manager    = Yii::app()->extensionsManager;
            $extensions = $manager->getCoreExtensions();
            $errors     = array();
            foreach ($extensions as $id => $instance) {
                if ($manager->extensionMustUpdate($id) && !$manager->updateExtension($id)) {
                    $errors[] = Yii::t('extensions', 'The extension "{name}" has failed to update!', array(
                        '{name}' => CHtml::encode($instance->name),
                    ));
                    $errors = CMap::mergeArray($errors, (array)$manager->getErrors());
                    $manager->resetErrors();
                }
            }
            
            if (!empty($errors)) {
                $notify->addError($errors);
            } else {
                $notify->addSuccess(Yii::t('extensions', 'All extension checks were conducted successfully.'));
            }
            
            // enable the tour extension which has been added in 1.3.6.3
            if (Yii::app()->extensionsManager->enableExtension('tour')) {
                Yii::app()->extensionsManager->getExtensionInstance('tour')->setOption('enabled', 'yes');
            }
            
            $this->redirect(array('dashboard/index'));
        }
        //
        $checkVersionUpdate = Yii::app()->options->get('system.common.check_version_update', 'yes') == 'yes';
        
        // stats
        $timelineItems = $this->getTimelineItems();
        $glanceStats   = $this->getGlanceStats();
//        print_r("<pre>"); print_r($glanceStats); print_r("</pre>"); exit;
        //
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('dashboard', 'Dashboard'),
            'pageHeading'       => Yii::t('dashboard', 'Dashboard'),
            'pageBreadcrumbs'   => array(
                Yii::t('dashboard', 'Dashboard'),
            ),
        ));
        
        $this->render('index', compact('checkVersionUpdate', 'glanceStats', 'timelineItems'));
    }

    /**
     * Check for updates
     */
    public function actionCheck_update()
    {
        ignore_user_abort(true);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $options = Yii::app()->options;
        if ($options->get('system.common.enable_version_update_check', 'yes') == 'no') {
            Yii::app()->end();
        }

        $now        = time();
        $lastCheck  = (int)$options->get('system.common.version_update.last_check', 0);
        $interval   = 60 * 60 * 24; // once at 24 hours should be enough

        if ($lastCheck + $interval > $now) {
            Yii::app()->end();
        }

        $options->set('system.common.version_update.last_check', $now);

        $response = AppInitHelper::simpleCurlGet('http://www.mailwizz.com/api/site/version');
        if (empty($response) || $response['status'] == 'error') {
            Yii::app()->end();
        }

        $json = CJSON::decode($response['message']);
        if (empty($json['current_version'])) {
            Yii::app()->end();
        }

        $dbVersion = $options->get('system.common.version', '1.0');
        if (version_compare($json['current_version'], $dbVersion, '>')) {
            $options->set('system.common.version_update.current_version', $json['current_version']);
        }

        Yii::app()->end();
    }

    /**
     * @return array
     */
    protected function getGlanceStats()
    {
        $cacheKey = md5('backend.dashboard.glanceStats');
        $cache    = Yii::app()->cache;

        if (($items = $cache->get($cacheKey))) {
            return $items;
        }
        
        $items = array(
            array(
                'count'     => Yii::app()->format->formatNumber(Customer::model()->count()),
                'heading'   => Yii::t('dashboard', 'Customers'),
                'icon'      => IconHelper::make('ion-person-add'),
                'url'       => $this->createUrl('customers/index'),
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(Campaign::model()->count()),
                'heading'   => Yii::t('dashboard', 'Campaigns'),
                'icon'      => IconHelper::make('ion-ios-email-outline'),
                'url'       => $this->createUrl('campaigns/index'),
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(Lists::model()->count()),
                'heading'   => Yii::t('dashboard', 'Lists'),
                'icon'      => IconHelper::make('ion ion-clipboard'),
                'url'       => 'javascript:;',
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(ListSubscriber::model()->count()),
                'heading'   => Yii::t('dashboard', 'Subscribers'),
                'icon'      => IconHelper::make('ion-ios-people'),
                'url'       => 'javascript:;',
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(ListSegment::model()->count()),
                'heading'   => Yii::t('dashboard', 'Segments'),
                'icon'      => IconHelper::make('ion-gear-b'),
                'url'       => 'javascript:;',
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(DeliveryServer::model()->count()),
                'heading'   => Yii::t('dashboard', 'Delivery servers'),
                'icon'      => IconHelper::make('ion-paper-airplane'),
                'url'       => $this->createUrl('delivery_servers/index'),
            ),
        );

        $cache->set($cacheKey, $items, 300);
        
        return $items;
    }

    /**
     * @return array
     */
    protected function getTimelineItems()
    {
        $cacheKey = md5('backend.dashboard.timelineItems');
        $cache    = Yii::app()->cache;
        
        if (($items = $cache->get($cacheKey))) {
            return $items;
        }
        
        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) as date_added';
        $criteria->condition = 'DATE(t.date_added) >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        $criteria->group     = 'DATE(t.date_added)';
        $criteria->order     = 't.date_added DESC';
        $criteria->limit     = 3;
        $models = CustomerActionLog::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $_item = array(
                'date'  => $model->dateTimeFormatter->formatLocalizedDate($model->date_added),
                'items' => array(),
            );
            $criteria = new CDbCriteria();
            $criteria->select    = 't.log_id, t.customer_id, t.message, t.date_added';
            $criteria->condition = 'DATE(t.date_added) = :date';
            $criteria->params    = array(':date' => $model->date_added);
            $criteria->limit     = 5;
            $criteria->order     = 't.date_added DESC';
            $criteria->with      = array(
                'customer' => array(
                    'select'   => 'customer.customer_id, customer.first_name, customer.last_name',
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                ),
            );
            $records = CustomerActionLog::model()->findAll($criteria);
            foreach ($records as $record) {
                $customer = $record->customer;
                $time     = $record->dateTimeFormatter->formatLocalizedTime($record->date_added);
                $_item['items'][] = array(
                    'time'         => $time,
                    'customerName' => $customer->getFullName(),
                    'customerUrl'  => $this->createUrl('customers/update', array('id' => $customer->customer_id)),
                    'message'      => strip_tags($record->message),
                );
            }
            $items[] = $_item;
        }
        
        $cache->set($cacheKey, $items, 300);
        
        return $items;
    }
}
