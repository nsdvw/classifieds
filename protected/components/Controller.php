<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout=false;
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

	/**
	 * Search via sphinx (mysql client)
	 */
	protected function sphinxSearch($phrase)
	{
		$connection = new CDbConnection(
			Yii::app()->params['sphinx']['dsn'],
			Yii::app()->params['sphinx']['user'],
			Yii::app()->params['sphinx']['pass']
		);
		$connection->active=true;
		$words = mb_split('[^\w]+', $phrase);
		$words = array_filter($words); // unset empty elements
		$search = implode('|', $words);
		$sphinxIndexes = SphinxService::implodeIndexes();
		$sql = "SELECT * FROM $sphinxIndexes WHERE MATCH('$search') LIMIT 10000";
		$command = $connection->createCommand($sql);
		return $command->queryColumn();
	}
}
