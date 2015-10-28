<?php

class SphinxService
{
    public static function saveAdToRt($adID)
    {
        $ad = Ad::model()->findByPk($adID);
        $connection = new CDbConnection(
            Yii::app()->params['sphinx']['dsn'],
            Yii::app()->params['sphinx']['user'],
            Yii::app()->params['sphinx']['pass']
        );
        $connection->active=true;

        $sphinxIndexes = Yii::app()->params['sphinx']['indexes'];
        $rt = $sphinxIndexes['rt'][0];
        $sql = "INSERT INTO $rt (id, title, description, added)
                VALUES (:id, :title, :description, :added)";
        $command = $connection->createCommand($sql);
        $command->execute(
            array(
                ':id' => $ad->id,
                ':title' => $ad->title,
                ':description' => $ad->description,
                ':added' => time(),
        ));
    }

    public static function implodeIndexes()
    {
        $sphinxIndexes = Yii::app()->params['sphinx']['indexes'];
        return implode(',', $sphinxIndexes['rt']) . ','
        . implode(',', $sphinxIndexes['disc']);
    }
}