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
class CustomerSMSMessage extends ActiveRecord
{
	const STATUS_DELIVERED = 'delivered';

    const STATUS_ABORTED = 'aborted';

    const STATUS_REJECTED = 'rejected';

    const STATUS_FAILED = 'failed';

    const STATUS_EXPIRED = 'expired';

    const STATUS_UNKNOWN = 'unknown';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_sms_message}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('customer_id, sms_message, customer_phone', 'required'),
			array('customer_id', 'exist', 'className' => 'Customer'),
            array('customer_phone', 'length', 'min' => 8),
            array('customer_phone', 'numerical', 'integerOnly' => true),
			array('sms_message', 'length', 'min' => 1, 'max' => 1600),
			array('status', 'in', 'range' => array_keys($this->getStatusesList())),

			// The following rule is used by search().
			array('customer_id, customer_phone, sms_message, status', 'safe', 'on'=>'search'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'sms_message_id'  => Yii::t('sms_messages', 'SMS Message id'),
			'sms_message_uid' => Yii::t('sms_messages', 'SMS Message uid'),
			'customer_id' => Yii::t('sms_messages', 'Customer'),
			'customer_phone' => Yii::t('sms_messages', 'Customer Phone'),
			'sms_message' 	  => Yii::t('sms_messages', 'SMS Message'),
            'date_added'     => Yii::t('sms_messages', 'Created At')
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

		if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with['customer'] = array(
                    'condition' => 'customer.email LIKE :name OR customer.first_name LIKE :name OR customer.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->customer_id . '%')
                );
            }
        }

		$criteria->compare('t.customer_phone', $this->customer_phone, true);
		$criteria->compare('t.sms_message', $this->sms_message, true);
		$criteria->compare('t.status', $this->status);

		$criteria->order = 't.sms_message_id DESC';

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.sms_message_id' => CSort::SORT_DESC,
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

        if (!empty($this->message_translation_params)) {
            $this->message_translation_params = serialize($this->message_translation_params);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        parent::afterFind();

        if (!empty($this->message_translation_params)) {
            $this->message_translation_params = @unserialize($this->message_translation_params);
        }
    }


    /**
     * @return string
     */
    public function getTranslatedMessage()
    {
        if (!empty($this->message_translation_params) && is_array($this->message_translation_params)) {
            return Yii::t('sms_messages', $this->sms_message, $this->message_translation_params);
        }

        return $this->sms_message;
    }

    /**
     * @param $message_uid
     * @return static
     */
	public function findByUid($message_uid)
    {
        return $this->findByAttributes(array(
            'sms_message_uid' => $this->sms_message_uid,
        ));
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->sms_message_uid;
    }

    /**
     * @return array
     */
	public function getStatusesList()
    {
        return array(
            self::STATUS_DELIVERED => Yii::t('sms_messages', 'Delivered'),
            self::STATUS_ABORTED => Yii::t('sms_messages', 'Aborted'),
            self::STATUS_REJECTED => Yii::t('sms_messages', 'Rejected'),
            self::STATUS_EXPIRED => Yii::t('sms_messages', 'Expired'),
            self::STATUS_FAILED   => Yii::t('sms_messages', 'Failed'),
            self::STATUS_UNKNOWN   => Yii::t('sms_messages', 'Unknown')
        );
    }

    /**
     * @param int $length
     * @return string
     */
	public function getShortMessage($length = 45)
	{
		return StringHelper::truncateLength($this->getTranslatedMessage(), $length);
	}

    public function getPhoneNumber($customerID, $length = 30)
    {
        return $this->customer_phone;
    }

    /**
     * @return bool
     */
	public function getIsDelivered()
	{
		return $this->status == self::STATUS_DELIVERED;
	}

    /**
     * @return bool
     */
    public function getIsAborted()
    {
        return $this->status == self::STATUS_ABORTED;
    }

    /**
     * @return bool
     */
    public function getIsRejected()
    {
        return $this->status == self::STATUS_REJECTED;
    }

    /**
     * @return bool
     */
    public function getIsExpired()
    {
        return $this->status == self::STATUS_EXPIRED;
    }

    /**
     * @return bool
     */
	public function getIsFailed()
	{
		return $this->status == self::STATUS_FAILED;
	}

    /**
     * @return bool
     */
    public function getIsUnknown()
    {
        return $this->status == self::STATUS_UNKNOWN;
    }

    /**
     * @param null $status
     * @return bool|int
     */
	public function saveStatus($status = null)
    {
        if (empty($this->sms_message_id)) {
            return false;
        }

        if ($status) {
            $this->status = $status;
        }

		$attributes = array('status' => $this->status);
		return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'sms_message_id = :id', array(':id' => (int)$this->sms_message_id));
    }

    /**
     * @param $customerId
     * @return int
     */
	public static function markAllAsCompletedForCustomer($customerId)
	{
		$attributes = array('status' => self::STATUS_DELIVERED);
		$instance   = new self();
		return Yii::app()->getDb()->createCommand()->update($instance->tableName(), $attributes, 'customer_id = :id', array(':id' => (int)$customerId));
	}

    /**
     * @return $this
     */
    public function broadcast()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'customer_id';
        $criteria->compare('status', User::STATUS_ACTIVE);
        $customers = Customer::model()->findAll($criteria);

        foreach ($customers as $customer) {
            $message = clone $this;
            $message->user_id = $customer->customer_id;
            $message->save();
        }

        return $this;
    }
}
