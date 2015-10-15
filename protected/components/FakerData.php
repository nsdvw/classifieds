<?php

class FakerData
{
    private $faker;

    private $userNames;
    private $userPhones;
    private $userEmails;

    private $adTitles;
    private $adDescriptions;
    private $adAuthors;
    private $adCities;
    private $adCategories;
    private $adSets;

    private $photoNames;

    public function __construct($cnt)
    {
        $this->faker = Faker\Factory::create('ru_RU');

        for ($i=0; $i < $cnt; $i++) {
            $this->userNames[] = $this->faker->name;
            $this->userPhones[] = $this->faker->phoneNumber;
            $this->userEmails[] = $this->faker->safeEmail;
            $this->adTitles[] = $this->faker->sentence;
            $this->adDescriptions[] = $this->faker->paragraph;
            $this->photoNames[] = $this->faker->word;
        }

        $sql = "SELECT id FROM user LIMIT 100";
        $command = Yii::app()->db->createCommand($sql);
        $this->adAuthors = $command->queryColumn();
        $sql = "SELECT city_id FROM city WHERE country_id = 3159";
        $command = Yii::app()->db->createCommand($sql);
        $this->adCities = $command->queryColumn();
        $sql = "SELECT id, set_id FROM category WHERE lft = rgt - 1";
        $command = Yii::app()->db->createCommand($sql);
        $this->adCategories = $command->queryAll();
        $sets = EavSet::model()->with('eavAttribute')->findAll();
        foreach ($sets as $s) {
            $this->adSets[$s->id] = $s;
        }
    }

    public function getSet($id)
    {
        return $this->adSets[$id];
    }

    public function getTitle()
    {
        return $this->adTitles[array_rand($this->adTitles)];
    }

    public function getDescription()
    {
        return $this->adDescriptions[array_rand($this->adDescriptions)];
    }

    public function getAuthor()
    {
        return $this->adAuthors[array_rand($this->adAuthors)];
    }

    public function getCity()
    {
        return $this->adCities[array_rand($this->adCities)];
    }

    public function getCategory()
    {
        return $this->adCategories[array_rand($this->adCategories)];
    }

    public function getUserName()
    {
        return $this->userNames[array_rand($this->userNames)];
    }

    public function getPhoneNumber()
    {
        return $this->userPhones[array_rand($this->userPhones)];
    }

    public function getEmail()
    {
        return 'u' . microtime(true) . rand(1000, 9999) . $this->userEmails[array_rand($this->userEmails)];
    }
}
