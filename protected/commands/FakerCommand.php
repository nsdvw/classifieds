<?php

class FakerCommand extends CConsoleCommand
{
    const PER_INSERT = 100; // how many rows should be inserted in one query
    const PER_TRANSACTION = 100; // how many queries should contain one transaction

    private $fakerData = array();
    private $builder;

    public function init()
    {
        $this->fakerData = new FakerData(self::PER_INSERT);
        $this->builder = Yii::app()->db->getSchema()->getCommandBuilder();
    }

    public function actionUser($cnt = 1000)
    {
        $table = 'user'; 
        $insertCount = intval(floor($cnt/self::PER_INSERT));
        $txn = Yii::app()->db->beginTransaction();
        for ($i=0; $i < $insertCount; $i++) {
            $this->progressMsg($i * self::PER_INSERT, $cnt);
            $data = $this->collectUserData();
            $this->multipleInsert($table, $data);
            if ($i % self::PER_TRANSACTION == 0 and $i != 0) {
                $txn->commit();
                $txn = Yii::app()->db->beginTransaction();
            }
        }
        $remainder = $cnt % self::PER_INSERT;
        if ($remainder) {
            $data = $this->collectUserData($remainder);
            $this->multipleInsert($table, $data);
        }
        $txn->commit();
    }

    public function actionAd($cnt=1000, $eav=false, $photos=false)
    {
        echo date('H:i:s') . "\n";
        $insertCount = intval(floor($cnt/self::PER_INSERT));
        $txn = Yii::app()->db->beginTransaction();
        for ($i=0; $i < $insertCount; $i++) {
            $this->progressMsg($i * self::PER_INSERT, $cnt);
            $this->insertAd($eav, $photos);
            if ($i % self::PER_TRANSACTION == 0 and $i != 0) {
                $txn->commit();
                $txn = Yii::app()->db->beginTransaction();
            }
        }
        $remainder = $cnt % self::PER_INSERT;
        if ($remainder) {
            $this->insertAd($eav, $photos, $remainder);
        }
        $txn->commit();
        echo date('H:i:s') . "\n";
    }

    private function multipleInsert($table, $data)
    {
        $command = $this->builder->createMultipleInsertCommand($table, $data);
        $command->execute();
    }

    private function eavMultipleInsert($table, $data)
    {
        $sql = "INSERT INTO {$table} (eav_attribute_id, entity_id, entity, value) VALUES ";
        $i = 0;
        foreach ($data as $row) {
            if ($i == 0) {
                $sql .= "(:a{$i},:e{$i},'ad',:v{$i})";
            } else {
                $sql .= ",(:a{$i},:e{$i},'ad',:v{$i})";
            }
            $params[":e{$i}"] = $row['entity_id'];
            $params[":a{$i}"] = $row['eav_attribute_id'];
            $params[":v{$i}"] = $row['value'];
            $i++;
        }
        Yii::app()->db->createCommand($sql)->execute($params);
    }

    private function insertAd($eav, $photos, $cnt = self::PER_INSERT)
    {
        $table = 'ad';
        $data = $this->collectAdData($cnt);
        $this->multipleInsert($table, $data);
        $this->attachEav($eav, $cnt);
        $this->attachPhoto($photos, $cnt);
    }

    private function collectUserData($cnt = self::PER_INSERT)
    {
        $data = array();
        for ($i=0; $i < $cnt; $i++) {
            $data[] = array(
                'email' => $this->fakerData->getEmail(),
                // use 'demo' to login
                'password' => '$2y$13$gT2xqTJiIdQjHXUvVIwePOgGINJQmX6m7wdAZefcw8lQasxtGOple',
                'name' => $this->fakerData->getUserName(),
                'phone' => $this->fakerData->getPhoneNumber(),
            );
        }
        return $data;
    }

    private function collectAdData($cnt = self::PER_INSERT)
    {
        $data = array();
        for ($i=0; $i < $cnt; $i++) {
            $category = $this->fakerData->getCategory();
            $data[] = array(
                'title' => $this->fakerData->getTitle(),
                'description' => $this->fakerData->getDescription(),
                'author_id' => $this->fakerData->getAuthor(),
                'city_id' => $this->fakerData->getCity(),
                'category_id' => $category['id'],
                'eav_set_id' => $category['set_id'],
            );
        }
        return $data;
    }

    private function attachEav($eav, $cnt = self::PER_INSERT)
    {
        if (!$eav) return;
        $data = $this->collectEavData($cnt);
        if (!empty($data['int'])) {
            $this->eavMultipleInsert('eav_attribute_int', $data['int']);
        }
        if (!empty($data['varchar'])) {
            $this->eavMultipleInsert('eav_attribute_varchar', $data['varchar']);
        }
    }

    private function collectEavData($cnt)
    {
        $ads = $this->getAdsCommand($cnt)->queryAll();
        foreach ($ads as $ad) {
            $set = $this->fakerData->getSet($ad['eav_set_id']);
            foreach ($set as $attr) {
                if ($attr['data_type'] == 'IntDataType') {
                    $value = $this->getEavIntValue($attr);
                    $data['int'][] = array(
                        'eav_attribute_id' => $attr['eav_attribute_id'],
                        'entity_id' => $ad['id'],
                        'entity' => 'ad',
                        'value' => $value,
                    );
                } elseif ($attr['data_type'] == 'VarcharDataType') {
                    $value = $this->getEavVarcharValue($attr);
                    $data['varchar'][] = array(
                        'eav_attribute_id' => $attr['eav_attribute_id'],
                        'entity_id' => $ad['id'],
                        'entity' => 'ad',
                        'value' => $value,
                    );
                }
            }
        }
        return $data;
    }

    private function attachPhoto($photos, $cnt = self::PER_INSERT)
    {
        if (!$photos) return;
        $data = $this->collectPhotoData($cnt);
        $this->multipleInsert('photo', $data);
    }

    private function collectPhotoData($cnt)
    {
        $data = array();
        $ads = $this->getAdsCommand($cnt)->queryAll();
        foreach ($ads as $ad) {
            $photoCount = rand(0, 5);
            for ($i=0; $i < $photoCount; $i++) {
                $data[] = array(
                    'name' => $this->fakerData->getPhotoName(),
                    'ad_id' => $ad['id'],
                );
            }
        }
        return $data;
    }

    private function getEavIntValue($attr)
    {
        $rawData = unserialize($attr['data']);
        $min = (isset($rawData['rules']['numerical']['min']))
                ? intval($rawData['rules']['numerical']['min'])
                : 0;
        $max = (isset($rawData['rules']['numerical']['max']))
                ? intval($rawData['rules']['numerical']['max'])
                : 100000000;
        if ($attr['name'] == 'price') {
            $value = rand($min, intval($max/100));
        } elseif ($attr['name'] == 'modelYear') {
            $max = intval(date('Y'));
            $value = rand($min, $max);
        } else {
            $value = rand($min, $max);
        }
        return $value;
    }

    private function getEavVarcharValue($attr)
    {
        $rawData = unserialize($attr['data']);
        return $rawData['values'][array_rand($rawData['values'])];
    }

    private function getAdsCommand($cnt)
    {
        $sql = "SELECT id, eav_set_id FROM ad ORDER BY id DESC LIMIT $cnt";
        return Yii::app()->db->createCommand($sql);
    }

    private function progressMsg($cur, $cnt)
    {
        echo round($cur * 100 / $cnt, 2) . "%\n";
    }
}
