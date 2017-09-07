<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBlacklist
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "email_blacklist".
 *
 * The followings are the available columns in table 'email_blacklist':
 * @property integer $email_id
 * @property integer $subscriber_id
 * @property string $email
 * @property string $reason
 * @property string $date_added
 * @property string $last_updated
 */
class PhoneBlacklist extends ActiveRecord
{
    const CHECK_ZONE_LIST_IMPORT = 'list import';

    const CHECK_ZONE_LIST_EXPORT = 'list export';

    public $file;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_phone_blacklist}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $mimes   = null;
        $options = Yii::app()->options;
        if ($options->get('system.importer.check_mime_type', 'yes') == 'yes' && CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('csv')->toArray();
        }

        $rules = array(
            array('phone', 'required', 'on' => 'insert, update'),
            array('phone', 'length', 'min' => 8, 'max' => 20),
            array('phone', 'unique'),

            array('reason', 'safe'),
            array('phone', 'safe', 'on' => 'search'),

            array('phone, reason', 'unsafe', 'on' => 'import'),
            array('file', 'required', 'on' => 'import'),
            array('file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => 512000000, 'allowEmpty' => true),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array();
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'phone_id'      => Yii::t('phone_blacklist', 'Phone ID'),
            'phone'         => Yii::t('phone_blacklist', 'Phone'),
            'reason'        => Yii::t('phone_blacklist', 'Reason'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;
        $criteria->compare('phone', $this->phone, true);
        $criteria->compare('reason', $this->reason, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder'  => array(
                    'phone_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EmailBlacklist the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        if ($this->getIsNewRecord() && MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_NEW_BLACKLIST_RECORDS) {
            return false;
        }
        
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
    }

    public function delete()
    {
        return parent::delete();
    }

    public function findByPhone($phone)
    {
        return $this->findByAttributes(array('phone' => $phone));
    }

    public static function removeByPhone($phone)
    {
        if (!($model = self::model()->findByPhone($phone))) {
            return false;
        }
        return $model->delete();
    }
    
    public static function getCheckZones()
    {
        return array(
            self::CHECK_ZONE_LIST_IMPORT, 
            self::CHECK_ZONE_LIST_EXPORT,
        );
    }
}
