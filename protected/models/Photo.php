<?php

use PreviewGenerator\Adapter\WideImageAdapter;
use PreviewGenerator\PreviewGenerator;

/**
 * This is the model class for table "photo".
 *
 * The followings are the available columns in table 'photo':
 * @property string $id
 * @property string $name
 * @property string $upload_time
 * @property string $ad_id
 *
 * The followings are the available model relations:
 * @property Ad $ad
 */
class Photo extends CActiveRecord
{
	public $image;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'photo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('image', 'file', 'types'=>'jpg, gif, png'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, upload_time, ad_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'ad' => array(self::BELONGS_TO, 'Ad', 'ad_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Имя файла',
			'upload_time' => 'Загружен',
			'ad_id' => 'Номер объявления',
			'image' => 'Фото'
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('upload_time',$this->upload_time,true);
		$criteria->compare('ad_id',$this->ad_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Photo the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function afterSave()
	{
		if ($this->isNewRecord) {
			$name = "{$this->id}_{$this->name}";
			$path = Yii::getPathOfAlias('webroot').'/upload/'.$name;
            $logo = Yii::getPathOfAlias('webroot').'/images/logo.png';
            $thumbDir = Yii::getPathOfAlias('webroot').'/images/thumb/';
            $thumbPath = $thumbDir . 'small_' . $name;
			$this->image->saveAs($path);
            $adapter = new WideImageAdapter;
            $previewGenerator = new PreviewGenerator($adapter);
            $previewGenerator->load($path);
            $previewGenerator->putWatermark($logo)
                ->create(160, 120, 245, 245, 245)
                ->save($thumbPath);
		}
		parent::afterSave();
	}

	public function afterConstruct()
	{
		Yii::setPathOfAlias('WideImage',Yii::getPathOfAlias(
			'application.vendor.smottt.wideimage.lib.WideImage'
		));
	}

	public static function validateMultiple(array $images, $model_id)
	{
		foreach ($images as $image) {
			$photo = new self;
			$photo->image = $image;
			$photo->name = $photo->image->getName();
			$photo->ad_id = $model_id;
			$photos[] = $photo;
			if (!$photo->validate()) return $photo;
		}
		return null;
	}
}
