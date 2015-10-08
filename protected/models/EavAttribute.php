<?php

/**
 * This is the model class for table "eav_attribute".
 *
 * The followings are the available columns in table 'eav_attribute':
 * @property string $id
 * @property integer $type
 * @property string $data_type
 * @property string $name
 * @property string $label
 * @property string $data
 * @property string $unit
 *
 * The followings are the available model relations:
 * @property EavAttributeDate[] $eavAttributeDates
 * @property EavAttributeInt[] $eavAttributeInts
 * @property EavAttributeMoney[] $eavAttributeMoneys
 * @property EavAttributeNumeric[] $eavAttributeNumerics
 * @property EavSet[] $eavSets
 * @property EavAttributeText[] $eavAttributeTexts
 * @property EavAttributeVarchar[] $eavAttributeVarchars
 */
class EavAttribute extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'eav_attribute';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, data_type, name', 'required'),
			array('type', 'numerical', 'integerOnly'=>true),
			array('data_type, name, label', 'length', 'max'=>255),
			array('unit', 'length', 'max'=>20),
			array('data', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, type, data_type, name, label, data, unit', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'eavAttributeDates' => array(self::HAS_MANY, 'EavAttributeDate', 'eav_attribute_id'),
			'eavAttributeInts' => array(self::HAS_MANY, 'EavAttributeInt', 'eav_attribute_id'),
			'eavAttributeMoneys' => array(self::HAS_MANY, 'EavAttributeMoney', 'eav_attribute_id'),
			'eavAttributeNumerics' => array(self::HAS_MANY, 'EavAttributeNumeric', 'eav_attribute_id'),
			'eavSets' => array(self::MANY_MANY, 'EavSet', 'eav_attribute_set(eav_attribute_id, eav_set_id)'),
			'eavAttributeTexts' => array(self::HAS_MANY, 'EavAttributeText', 'eav_attribute_id'),
			'eavAttributeVarchars' => array(self::HAS_MANY, 'EavAttributeVarchar', 'eav_attribute_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type' => 'Type',
			'data_type' => 'Data Type',
			'name' => 'Name',
			'label' => 'Label',
			'data' => 'Data',
			'unit' => 'Unit',
		);
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
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('data_type',$this->data_type,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('label',$this->label,true);
		$criteria->compare('data',$this->data,true);
		$criteria->compare('unit',$this->unit,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return EavAttribute the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
