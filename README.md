# Classifieds

Simple bulletin board on yii.

How to install:
- git clone
- cd to protected folder
- composer install

To create database schema you need:
- mysql -uuser -ppassword classifieds < protected/data/mysql.schema.sql

To export current database dump you need:
- mysql -uuser -ppassword classifieds < protected/data/classifieds.sql

If there are some migrations, you need execute
"protected/yiic migrate new"
to see available migrations and
"ptotected/yiic migrate"
to apply them.

To change dbname or other connection settings, modify the protected/config/database.php as usual in Yii 1.1.

That's all. No need to configurate extensions (nested-set-behavior, eavactiverecord, twig-renderer etc.) because i've already done that.
