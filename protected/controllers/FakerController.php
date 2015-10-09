<?php

#######################################
### Delete this file on production! ###
#######################################

class FakerController extends Controller
{
    public function actionIndex()
    {
        set_time_limit(3600 * 2);
        $faker = Faker\Factory::create('ru_RU');

        echo date('H:i:s') . ' Start!<br>';
        echo date('H:i:s') . ' Begin to create users.<br>';
        /*for ($i=0; $i < 100; $i++) {
            // more than 1000 is not safe; you may increase max_allowed_packet
            $this->createLegion($faker, 1000);
        }*/
        echo date('H:i:s') . ' End creating users.<br>';

        $sets = EavSet::model()->with('eavAttribute')->findAll();

        $sql = "SELECT city_id FROM city WHERE country_id=3159";
        $cities = Yii::app()->db->createCommand($sql)->queryColumn();

        $sql = "SELECT id FROM user";
        $users = Yii::app()->db->createCommand($sql)->queryColumn();

        $categories = $this->getCategories();
        echo date('H:i:s') . ' Begin to create ads.<br>';
        /*foreach ($categories as $c) {
            if ($c['lft'] + 1 != $c['rgt']) continue; // search leaves
            for ($i=0; $i < 33; $i++) {
                // more than 100 at once is not safe; you may increase max_allowed_packet
                $this->createAds($faker, $c, $cities, $users, 100);
            }
        }*/
        echo date('H:i:s') . ' End creating ads.<br>';

        echo date('H:i:s') . ' Finish!<br>';
    }

    private function createLegion(Faker\Generator $faker, $num = 1000)
    {
        $sql = "INSERT INTO user (email, password, name, phone) VALUES ";
        $alphabet = range('a', 'z');
        for ($i=0; $i < $num; $i++) {
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

    private function getCategories()
    {
        $sql = "SELECT id, set_id, lft, rgt FROM category";
        return Yii::app()->db->createCommand($sql)->queryAll();
    }

    private function createAds(
        Faker\Generator $faker,
        array $category,
        array $cities,
        array $authors,
        $num = 100)
    {
        $sql = "INSERT INTO ad
        (title, description, eav_set_id, category_id, city_id, author_id) VALUES ";
        for ($i = 0; $i < $num; $i++) {
            $ad = array();
            $ad['title'] = $faker->sentence;
            $ad['description'] = $faker->realText;
            $ad['eav_set_id'] = $category['set_id'];
            $ad['category_id'] = $category['id'];
            $ad['city_id'] = $cities[array_rand($cities)];
            $ad['author_id'] = $authors[array_rand($authors)];
            if ($i == 0) {
                $sql .= "('{$ad['title']}','{$ad['description']}','{$ad['eav_set_id']}','{$ad['category_id']}','{$ad['city_id']}','{$ad['author_id']}')";
            } else {
                $sql .= ",('{$ad['title']}','{$ad['description']}','{$ad['eav_set_id']}','{$ad['category_id']}','{$ad['city_id']}','{$ad['author_id']}')";
            }
        }
        Yii::app()->db->createCommand($sql)->execute();
    }
}
