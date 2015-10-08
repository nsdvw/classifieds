<?php

#######################################
### Delete this file on production! ###
#######################################

class FakerController extends Controller
{
    public function actionIndex()
    {
        $sets = EavSet::model()->with('eavAttribute')->findAll();
        $sql = "SELECT city_id FROM city WHERE country_id=3159";
        $cities = Yii::app()->db->createCommand($sql)->queryColumn();
        $sql = "SELECT id FROM user";
        $users = Yii::app()->db->createCommand($sql)->queryColumn();
        
    }
}