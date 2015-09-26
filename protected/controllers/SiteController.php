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

	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view','search', 'error', 'contact',
					'login', 'logout', 'cityData'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('logout'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
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

		$this->setDependentCascadeDropDown();

		$this->render('index',
			array('categories'=>$categories, 'model'=>$model, 'dataProvider'=>$dp)
		);
	}

	/**
	 * Action to search ads by key word(s?)
	 */
	public function actionSearch($id=null,$word=null,$city=null,$page=null)
	{
		$criteria = new CDbCriteria;
		$criteria->condition = "status='unpublished'";
		$criteria->order = 'added DESC';

		$form = new EavSearchForm();
		if ($id) {
			$childrenIds = Category::getDescendantIds($id);
			if ($childrenIds) {
				$criteria->addInCondition('category_id', $childrenIds);
			} else {
				$criteria->addCondition('category_id=:category_id');
				$criteria->params[':category_id'] = intval($id);
			}
			$form->model->attachEavSet(Category::model()->findByPk($id)->set_id);
			$form->eav = $form->model->eavAttributes;
			if (isset($_GET['search'])) {
				$form->fill();
				$this->buildEavCriteria($criteria);
			}
		}

		$dp = new EavActiveDataProvider('Ad', array(
			'criteria'=>$criteria,
			'countCriteria'=>array(
				'condition'=>$criteria->condition,
				'params'=>$criteria->params),
		    'pagination'=>array('pageSize'=>2),
			));

		$this->setDependentCascadeDropDown();

		$this->render(
			'search',
			array(
				'dataProvider'=>$dp,
				'form'=>$form,
				));
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

	protected function buildEavCriteria(CDbCriteria $criteria, $getParam = 'search')
	{
		$attributes = Ad::getEavList();
		foreach ($_GET[$getParam] as $key=>$value) {
			if (!in_array($key, $attributes)) continue;
			if (is_array($value)) {
				if (isset($value['min']) and !empty($value['min'])) {
					$criteria->addCondition("::{$key} >= :min_{$key}");
					$criteria->params[":min_{$key}"] = $value['min'];
				}
				if (isset($value['max']) and !empty($value['max'])) {
					$criteria->addCondition("::{$key} <= :max_{$key}");
					$criteria->params[":max_{$key}"] = $value['max'];
				}
			} else {
				if (!$value) continue;
				$criteria->addCondition("::{$key} = :{$key}");
				$criteria->params[":{$key}"] = $value;
			}
		}
	}
}
