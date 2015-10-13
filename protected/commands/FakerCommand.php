<?php

$composerPath = Yii::getPathOfAlias('application.vendor');
require $composerPath . DIRECTORY_SEPARATOR . 'autoload.php';

class FakerCommand extends CConsoleCommand
{
    const PER_INSERT = 100;
    const PER_TRANSACTION = 100;

    private $faker;

    public function init()
    {
        $this->faker = Faker\Factory::create('ru_RU');
    }

    public function actionUser($cnt = 1000)
    {
        $table = 'user';
        $data = array();
        $insertCount = intval(floor($cnt/self::PER_INSERT));
        $builder = Yii::app()->db->getSchema()->getCommandBuilder();
        $txn = Yii::app()->db->beginTransaction();
        for ($i=0; $i < $insertCount; $i++) {
            $data = $this->collectUserData();
            $this->multipleInsert($builder, $table, $data);
            if ($i % self::PER_TRANSACTION == 0) {
                $txn->commit();
                $txn = Yii::app()->db->beginTransaction();
            }
        }
        $remainder = $cnt % self::PER_INSERT;
        if ($remainder) {
            $data = $this->collectUserData($remainder);
            $this->multipleInsert($builder, $table, $data);
            $txn->commit();
        }
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
                'email' => $this->faker->freeEmail,
                'password' => $this->faker->password,
                'name' => $this->faker->name,
                'phone' => $this->faker->phoneNumber,
            );
        }
        return $data;
    }
}
