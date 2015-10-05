<?php

/**
 * This is the model class for table "ad".
 *
 * The followings are the available columns in table 'ad':
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string $added
 * @property string $author_id
 * @property string $city_id
 * @property string $category_id
 * @property string $visit_counter
 * @property string $status
 * @property string $importance
 * @property string $eav_set_id
 *
 * The followings are the available model relations:
 * @property User $author
 * @property City $city
 * @property Category $category
 * @property EavSet $eavSet
 * @property Photo[] $photos
 */
class Ad extends EavActiveRecord
{
	private $eavAttributeInstances;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ad';
	}

	/*public function __call($name, array $params = null)
    {
        return '';
    }*/

/*	protected $price;
	public function getPrice()
	{
		return $this->price;
	}*/

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, description, category_id, city_id', 'required'),
			array('title', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title, description, added, author_id, city_id, category_id, visit_counter, status, importance, eav_set_id', 'safe', 'on'=>'search'),
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
			'author' => array(self::BELONGS_TO, 'User', 'author_id'),
			'city' => array(self::BELONGS_TO, 'City', 'city_id'),
			'category' => array(self::BELONGS_TO, 'Category', 'category_id'),
			'eavSet' => array(self::BELONGS_TO, 'EavSet', 'eav_set_id'),
			'photos' => array(self::HAS_MANY, 'Photo', 'ad_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Заголовок',
			'description' => 'Текст объявления',
			'added' => 'Added',
			'author_id' => 'Author',
			'city_id' => 'Город',
			'category_id' => 'Подкатегория',
			'visit_counter' => 'Visit Counter',
			'status' => 'Status',
			'importance' => 'Importance',
			'eav_set_id' => 'Eav Set',
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
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('added',$this->added,true);
		$criteria->compare('author_id',$this->author_id,true);
		$criteria->compare('city_id',$this->city_id,true);
		$criteria->compare('category_id',$this->category_id,true);
		$criteria->compare('visit_counter',$this->visit_counter,true);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('importance',$this->importance,true);
		$criteria->compare('eav_set_id',$this->eav_set_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Ad the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public static function getEavList()
	{
		$models = EavAttribute::model()->findAll();
		foreach ($models as $attr) {
			$attributes[] = $attr->name;
		}
		return $attributes;
	}

	public function getAttributeUnit($attribute)
	{
		$this->setAttributeInstances();
		return $this->eavAttributeInstances[$attribute]->unit;
	}

	protected function setAttributeInstances()
	{
		$set = EavSet::model()->findByPk($this->eav_set_id);
		$attributes = $set->getEavAttributes();
		foreach ($attributes as $attr) {
			$this->eavAttributeInstances[$attr->name] = $attr;
		}
	}

	public function getEavVariants($attrName)
	{
		$attr = EavAttribute::model()->findByAttributes(array('name'=>$attrName));
		return $attr->getPossibleValues();
	}

	/**
	 * There is an issue/bug in twig-renderer extension for yii:
	 * it throws an exception when property value equals to NULL, see
	 * https://github.com/twigphp/Twig/issues/1557
	 * so need a solution to get around with the problem.
	 */
	public function isEavAttributeEmpty($attrName)
	{
		if (!$this->hasEavAttribute($attrName) or !$this->getEavAttribute($attrName)) {
			return true;
		} else {
			return false;
		}
	}

	public function getCategoryList($id)
	{
		return Category::getChildren($id);
	}
}
