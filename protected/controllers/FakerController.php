<?php

#######################################
### Delete this file on production! ###
#######################################

class FakerController extends Controller
{
    public function actionIndex()
    {
        set_time_limit(3600 * 2);

        echo date('H:i:s') . ' Start! Begin to create users.<br>';
        /*$faker = Faker\Factory::create('ru_RU');
        for ($i=0; $i < 100; $i++) {
            $this->createLegion($faker);
        }*/
        echo date('H:i:s') . ' End creating users.<br>';



        /*$sql = "INSERT INTO user (email, password, name, phone) VALUES ";
        Yii::app()->db->createCommand($sql);*/
        /*$sets = EavSet::model()->with('eavAttribute')->findAll();

        $sql = "SELECT city_id FROM city WHERE country_id=3159";
        $cities = Yii::app()->db->createCommand($sql)->queryColumn();

        $sql = "SELECT id FROM user";
        $users = Yii::app()->db->createCommand($sql)->queryColumn();*/


        echo date('H:i:s') . ' Finish!<br>';
    }

    private function createLegion(Faker\Generator $faker)
    {
        $sql = "INSERT INTO user (email, password, name, phone) VALUES ";
        $alphabet = range('a', 'z');
        for ($i=0; $i < 1000; $i++) {
            // to avoid duplicates on unique field email use random prefix
            $user['email'] = $alphabet[rand(0, 25)] . rand(1000, 9999) . $faker->freeEmail;
            // problem with escape symbols when using ::password
            $user['password'] = $faker->word . $faker->word; 
            $user['name'] = $faker->name;
            $user['phone'] = $faker->phoneNumber;
            if ($i == 0) {
                $sql .= "('{$user['email']}','{$user['password']}','{$user['name']}','{$user['phone']}')";
            } else {
                $sql .= ",('{$user['email']}','{$user['password']}','{$user['name']}','{$user['phone']}')";
            }
        }

        Yii::app()->db->createCommand($sql)->execute();
    }
}