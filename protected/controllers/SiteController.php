<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$criteria = new CDbCriteria;
		$criteria->addInCondition('level', array(1,2));
		$criteria->order = 'root, lft';
		$categories = Category::model()->findAll($criteria);
		$model = new SearchForm;
		$dp = new CActiveDataProvider('Ad', array(
			'criteria'=>array(
				'condition'=>'status="unpublished"',
				'order'=>'added DESC',
				'with'=>array('author', 'category', 'city', 'photos'),
				'limit'=>20,
			),
			'pagination'=>false
		));

		ECascadeDropDown::master('id_region')->setDependent(
			'id_city',
			array('dependentLoadingLabel'=>'Loading...'),
			'site/citydata'
		); 

		$this->render('index',
			array('categories'=>$categories, 'model'=>$model, 'dataProvider'=>$dp)
		);
	}

	/**
	 * Action to search ads by category_id
	 */
	public function actionCat($id)
	{

	}

	/**
	 * Action to search ads by key word(s?)
	 */
	public function actionSearch($word=null,$city=null)
	{

	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
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