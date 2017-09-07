<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerMessage
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright 2013-2016 MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

/**
 * This is the model class for table "customer_sms_message".
 *
 * The followings are the available columns in table 'customer_sms_message':
 * @property integer $sms_message_id
 * @property string $sms_message_uid
 * @property integer $customer_id
 * @property integer $customer_phone
 * @property string $sms_message
 * @property string $message_translation_params
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class SmsTemplate extends ActiveRecord
{

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_sms_template}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('type, content', 'required'),
            array('type', 'length', 'min' => 1, 'max' => 24),
            array('type', 'unique'),
			array('content', 'length', 'min' => 1, 'max' => 1600),

			// The following rule is used by search().
			array('type, content', 'safe', 'on'=>'search'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'sms_template_id'  => Yii::t('sms_templates', 'SMS Template id'),
			'type' => Yii::t('sms_templates', 'SMS Type'),
            'content' => Yii::t('sms_templates', 'SMS Message Content')
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
		$criteria->compare('t.type', $this->type, true);
		$criteria->compare('t.content', $this->content, true);

		$criteria->order = 't.sms_template_id DESC';

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.sms_template_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerMessage the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @inheritdoc
     */
	protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        if (!empty($this->content_translation_params)) {
            $this->content_translation_params = serialize($this->content_translation_params);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        parent::afterFind();

        if (!empty($this->content_translation_params)) {
            $this->content_translation_params = @unserialize($this->content_translation_params);
        }
    }


    /**
     * @return string
     */
    public function getTranslatedContent()
    {
        if (!empty($this->content_translation_params) && is_array($this->content_translation_params)) {
            return Yii::t('sms_templates', $this->content, $this->content_translation_params);
        }

        return $this->content;
    }

    /**
     * @param $message_uid
     * @return static
     */
	public function findByType($type)
    {
        return $this->findByAttributes(array(
            'type' => $this->type,
        ));
    }

    /**
     * @param int $length
     * @return string
     */
	public function getShortContent($length = 45)
	{
		return StringHelper::truncateLength($this->getTranslatedContent(), $length);
	}

    public function getType($length = 30)
    {
        return $this->type;
    }

}
