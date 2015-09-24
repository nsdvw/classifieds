<?php

class m150924_202359_drop_table_attr_variant extends CDbMigration
{
	public function up()
	{
		$this->dropTable('attr_variant');
	}

	public function down()
	{
		$this->createTable('attr_variant', array(
			'id' => 'pk',
			'attr_id' => 'INT(10) UNSIGNED NOT NULL',
			'title' => 'VARCHAR(255) NOT NULL',
			));
		$this->addForeignKey(
			'ix_attr_id', 'attr_variant', 'attr_id',
			'eav_attribute', 'id', 'CASCADE', 'CASCADE');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}