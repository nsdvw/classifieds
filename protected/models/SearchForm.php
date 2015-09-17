<?php
class SearchForm extends CFormModel
{
    public $city_id;
    public $region_id;
    public $word;

    public function getRegions($country_id = 3159) // default to Russia
    {
        $regions = Region::model()->findAllByAttributes(array('country_id'=>$country_id));
        return CHtml::listData($regions, 'region_id', 'name');
    }

    public function getCities($region_id)
    {
        $cities = City::model()->findAll('region_id=:region_id', array(':region_id'=>$region_id));
        return CHtml::listData($cities, 'city_id', 'name');
    }
}