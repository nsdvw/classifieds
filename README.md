Classifieds
===========

Simple bulletin board on yii.
Features:
- implements EAV pattern to store various attrs, which depends on category
- hierarchical category structure of 4 and more levels
- preview and thumbnail creation for uploaded images

Installation
------------
1. Clone repository
   ``` sh
$ git clone https://github.com/nsdvw/classifieds.git
```

1. cd to "protected" folder

1. composer install

1. To create database schema you need:

   ``` sh
$ mysql -uuser -ppassword classifieds < protected/data/mysql.schema.sql
```

1. To export current database dump for testing (not necessary, up to you):
   ``` sh
$ mysql -uuser -ppassword classifieds < protected/data/classifieds.sql
```

1. if there are some migrations, apply them by

   ``` sh
$ protected/yiic migrate
```

1. To change dbname or other connection settings, modify the protected/config/database.php as usual in Yii 1.1.

1. Sphinx configuration is under protected/config/main.php in "params" section.
If you do not use sphinx, comment the configuration, or leave it as is (exception will be caught and use "like" query instead of sphinx).

Frameworks, extensions and libraries, used in project
-----------------------------------------------------
1. Yii framework
https://github.com/yiisoft/yii
1. EavActiveRecord extention for yii
https://github.com/iAchilles/eavactiverecord
1. Nested set behavior for yii
https://github.com/yiiext/nested-set-behavior
1. Twig template engine
http://twig.sensiolabs.org/
1. Twig view renderer extention for yii
https://github.com/yiiext/twig-renderer
1. WideImage library
http://wideimage.sourceforge.net/
