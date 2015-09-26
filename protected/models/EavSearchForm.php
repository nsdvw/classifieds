<?php

class EavSearchForm extends SearchForm
{
    public $eav;
    public $model;

    public function __construct()
    {
        $model = new Ad;
        $this->model = $model;
    }

    public function fill($getParam = 'search')
    {
        $attributes = Ad::getEavList();
        foreach ($_GET[$getParam] as $key=>$value) {
            if (!in_array($key, $attributes)) continue;
            $this->eav[$key] = $value;
        }
        $this->region_id = Yii::app()->request->getQuery('region_id');
        $this->city_id = Yii::app()->request->getQuery('city_id');
        $this->word = Yii::app()->request->getQuery('word');
    }
}
