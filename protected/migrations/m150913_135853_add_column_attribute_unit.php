<?php

class m150913_135853_add_column_attribute_unit extends CDbMigration
{
	public function up()
	{
		$this->addColumn('eav_attribute', 'unit', 'varchar(20)');
	}

	public function down()
	{
		$this->dropColumn('eav_attribute', 'unit');
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