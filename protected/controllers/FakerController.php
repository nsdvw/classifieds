<?php

#######################################
### Delete this file on production! ###
#######################################

class FakerController extends Controller
{
    public function actionIndex()
    {
        set_time_limit(3600 * 3);
        $faker = Faker\Factory::create('ru_RU');

        echo date('H:i:s') . ' Start!<br>';
        // Uncomment to create users
        // $this->createUsers($faker, 100); // number of users in thousands

        // Uncomment to create ads
        // $this->createAds($faker);

        // Uncomment to attach eav attributes to all ads that already exist in db
        //$this->attachEavAttributes();

        // Uncomment to attach photos to ads
        $this->attachPhotos($faker);
        echo date('H:i:s') . ' Finish!<br>';
    }

    private function createLegion(Faker\Generator $faker, $num = 1000)
    {
        $sql = "INSERT INTO user (email, password, name, phone) VALUES ";
        $alphabet = range('a', 'z');
        for ($i=0; $i < $num; $i++) {
            // to avoid duplicates on unique field email use random prefix
            $user['email'] = $alphabet[rand(0, 25)] . rand(1000, 9999) . $faker->freeEmail;
            // problem with escape symbols when using faker password
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

    private function createAdsPerCategory(
        Faker\Generator $faker,
        array $category,
        array $cities,
        array $authors,
        $num = 1000)
    {
        $sql = "INSERT INTO ad
        (title, description, eav_set_id, category_id, city_id, author_id) VALUES ";
        for ($i = 0; $i < $num; $i++) {
            $ad = array();
            $ad['title'] = $faker->sentence;
            /*
            ошибка при использовании realText:
            CDbCommand не удалось исполнить SQL-запрос: SQLSTATE[HY093]: Invalid parameter number: no parameters were bound.
            */
            $ad['description'] = $faker->text;
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

    private function attachEavAttributes()
    {
        echo date('H:i:s') . ' Begin attaching eav-attributes to ads.<br>';
        $sets = EavSet::model()->with('eavAttribute')->findAll();
        foreach ($sets as $s) {
            $setsAssoc[$s->id] = $s;
        }
        $sql = "SELECT count(*) FROM ad";
        $totalCount = intval(Yii::app()->db->createCommand($sql)->queryScalar());
        $perQuery = 10000;
        $offset = 0;
        while ($offset < $totalCount) {
            $transaction = Yii::app()->db->beginTransaction();
            $sql = "SELECT id, eav_set_id FROM ad LIMIT {$offset}, {$perQuery}";
            $ads = Yii::app()->db->createCommand($sql)->queryAll();
            foreach ($ads as $ad) {
            $set = $setsAssoc[$ad['eav_set_id']];
            foreach ($set->eavAttribute as $a) {
                $rawData = unserialize($a->data);
                if ($a->data_type == 'IntDataType') {
                    $tbl = 'eav_attribute_int';
                    $min = (isset($rawData['rules']['numerical']['min']))
                            ? intval($rawData['rules']['numerical']['min'])
                            : 0;
                    $max = (isset($rawData['rules']['numerical']['max']))
                            ? intval($rawData['rules']['numerical']['max'])
                            : 100000000;
                    if ($a->name == 'price') {
                        $value = rand($min, intval($max/100));
                    } elseif ($a->name == 'modelYear') {
                        $max = intval(date('Y'));
                        $value = rand($min, $max);
                    } else {
                        $value = rand($min, $max);
                    }
                } elseif ($a->data_type == 'VarcharDataType') {
                    $tbl = 'eav_attribute_varchar';
                    $value = array_rand($rawData['values']);
                }
                $sql = 
                    "INSERT INTO {$tbl} (eav_attribute_id, entity_id, entity, value)
                    VALUES ('{$a->id}', '{$ad['id']}', 'ad', '$value')";
                Yii::app()->db->createCommand($sql)->execute();
            }
            }
            $offset += $perQuery;
            $transaction->commit();
        }
        echo date('H:i:s') . ' End attaching eav-attributes to ads.<br>';
    }

    private function createUsers(Faker\Generator $faker, $thousand = 100)
    {
        echo date('H:i:s') . ' Begin creating users.<br>';
        for ($i=0; $i < $thousand; $i++) {
            $this->createLegion($faker, 1000);
        }
        echo date('H:i:s') . ' End creating users.<br>';
    }

    private function createAds(Faker\Generator $faker)
    {
        echo date('H:i:s') . ' Begin creating ads.<br>';

        $sql = "SELECT city_id FROM city WHERE country_id=3159";
        $cities = Yii::app()->db->createCommand($sql)->queryColumn();
        $sql = "SELECT id FROM user";
        $users = Yii::app()->db->createCommand($sql)->queryColumn();
        $categories = $this->getCategories();

        foreach ($categories as $c) {
            if ($c['lft'] + 1 != $c['rgt']) continue; // search leaves
            for ($i=0; $i < 1; $i++) {
                $this->createAdsPerCategory($faker, $c, $cities, $users, 50);
            }
        }
        echo date('H:i:s') . ' End creating ads.<br>';
    }

    private function attachPhotos(Faker\Generator $faker)
    {
        echo date('H:i:s') . ' Begin attaching photos to ad.<br>';
        $sql = "SELECT count(*) FROM ad";
        $totalCount = intval(Yii::app()->db->createCommand($sql)->queryScalar());
        $perQuery = 10000;
        $offset = 0;
        while ($offset < $totalCount) {
            $sql = "SELECT id FROM ad LIMIT {$offset}, {$perQuery}";
            $ads = Yii::app()->db->createCommand($sql)->queryAll();
            $sql = "INSERT INTO photo (name, ad_id) VALUES ";
            $j=0;
            foreach ($ads as $ad) {
                $count = rand(0,5);
                if ($j == 0 and $count == 0) {
                    $count++;
                }
                for ($i=0; $i < $count; $i++) {
                    $name = $faker->word;
                    if ($i == 0 and $j == 0) {
                        $sql .= "('{$name}',{$ad['id']})";
                    } else {
                        $sql .= ",('{$name}',{$ad['id']})";
                    }
                }
                $j++;
            }
            $offset += $perQuery;
            Yii::app()->db->createCommand($sql)->execute();
        }
        echo date('H:i:s') . ' End attaching photos to ad.<br>';
    }
}
