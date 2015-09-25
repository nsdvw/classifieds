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

	protected function setDependentCascadeDropDown(
		$id_region = 'id_region',
		$id_city = 'id_city',
		$action = 'site/citydata')
	{
		ECascadeDropDown::master($id_region)->setDependent(
			$id_city,
			array('dependentLoadingLabel'=>'Loading cities ...'),
			$action);
	}

	public function actionCitydata()
	{
		//check if isAjaxRequest and the needed GET params are set 
		ECascadeDropDown::checkValidRequest();

   		//load the cities for the current province id (=ECascadeDropDown::submittedKeyValue())
		$data = City::model()->findAll(
			'region_id=:region_id',
			array(':region_id'=>ECascadeDropDown::submittedKeyValue())
		);

	   //Convert the data by using 
	   //CHtml::listData, prepare the JSON-Response and Yii::app()->end 
		ECascadeDropDown::renderListData($data,'city_id', 'name');
	}
}