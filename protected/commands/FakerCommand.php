<?php

class FakerCommand extends CConsoleCommand
{
    const PER_INSERT = 100; // how many rows should be inserted in one query
    const PER_TRANSACTION = 100; // how many queries should contain one transaction

    private $faker;
    private $fakerData;
    public function init()
    {
        $this->faker = Faker\Factory::create('ru_RU');
        for ($i = 0; $i < 1000; $i++) {
            $fakerData['names'][] = $this->faker->name;
            $fakerData['phoneNumbers'][] = $this->faker->phoneNumber;
            $fakerData['emails'][] = $this->faker->safeEmail;
        }
        $this->fakerData = $fakerData;
    }

    public function actionUser($cnt = 1000)
    {
        echo date('H:i:s') . "\n";
        $table = 'user';
        $insertCount = intval(floor($cnt/self::PER_INSERT));
        $builder = Yii::app()->db->getSchema()->getCommandBuilder();
        $txn = Yii::app()->db->beginTransaction();
        for ($i=0; $i < $insertCount; $i++) {
            $this->progressMsg($i * self::PER_INSERT, $cnt);
            $data = $this->collectUserData();
            $this->multipleInsert($builder, $table, $data);
            if ($i % self::PER_TRANSACTION == 0 and $i != 0) {
                $txn->commit();
                $txn = Yii::app()->db->beginTransaction();
            }
        }
        $remainder = $cnt % self::PER_INSERT;
        if ($remainder) {
            $data = $this->collectUserData($remainder);
            $this->multipleInsert($builder, $table, $data);
        }
        $txn->commit();
        echo date('H:i:s') . "\n";
    }

    private function multipleInsert($builder, $table, $data)
    {
        $command = $builder->createMultipleInsertCommand($table, $data);
        $command->execute();
    }

    private function collectUserData($cnt = self::PER_INSERT)
    {
        $data = array();
        for ($i=0; $i < $cnt; $i++) {
            $data[] = array(
                'email' => $this->getEmail($i),
                // use 'demo' to login
                'password' => '$2y$13$gT2xqTJiIdQjHXUvVIwePOgGINJQmX6m7wdAZefcw8lQasxtGOple',
                'name' => $this->fakerData['names'][$i],
                'phone' => $this->fakerData['phoneNumbers'][$i],
            );
        }
        return $data;
    }

    private function progressMsg($cur, $cnt)
    {
        echo round($cur * 100 / $cnt, 2) . "%\n";
    }

    private function getEmail($seed)
    {
        return 'u' . microtime(true) . $this->fakerData['emails'][$seed]; 
    }
}
