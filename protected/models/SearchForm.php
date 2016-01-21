<?php
class SearchForm extends CFormModel
{
    public $city_id;
    public $region_id;
    public $word;

    public function getCities($region_id)
    {
        $cities = City::model()->findAll('region_id=:region_id', array(':region_id'=>$region_id));
        return CHtml::listData($cities, 'city_id', 'name');
    }
}