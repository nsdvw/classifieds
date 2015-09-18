<?php

class AdController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('update','new','getcategories','create'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Displays the page with category selection. When user choose category, redirect
	 * to the 'create' page.
	 */
	public function actionNew()
	{
		$criteria = new CDbCriteria;
		$criteria->condition = 'level=:level';
		$criteria->params = array('level'=>1);
		//$dp = new CActiveDataProvider('Category', array('criteria'=>$criteria));
		$models = Category::model()->findAll($criteria);
		$this->render('new', array('models'=>$models));
	}

	/*
	 * Responds to ajax request from ad/new page
	 */
	public function actionGetcategories()
	{
		if (!isset($_POST['id'])) {
			echo json_encode(array());
			Yii::app()->end();
		}
		$id = intval($_POST['id']);
		$parent_cat = Category::model()->findByPk($id);
		$children = $parent_cat->children()->findAll();
		if (!$children) {
			echo json_encode(array());
			Yii::app()->end();
		}
		foreach ($children as $child) {
			$res[$child->id] = $child->title;
		}
		echo json_encode($res);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate($id)
	{
		$model = new Ad;
		$category = Category::model()->findByPk($id);
		$children = $category->children()->findAll();
		$children = CHtml::listData($children, 'id', 'title');
		$hasChildren = ($children) ? true : false; 
		$model->attachEavSet($category->set_id);
		$model->category_id = $id;

		$lists = array('category' => $children);
		foreach ($model->eavAttributes as $key => $value) {
			$attribute = EavAttribute::model()->findByAttributes(array('name'=>$key));
			$variants =	AttrVariant::model()->findAllByAttributes(
				array('attr_id'=>$attribute->id)
			);
			if (!$variants) continue;
			$lists[$key] = CHtml::listData($variants, 'title', 'title');
		}

		if (isset($_POST['Ad'])) {
			$model->attributes = $_POST['Ad'];
			$model->author_id = Yii::app()->user->id;
			$model->city_id = 4400; // ???
			$model->saveWithEavAttributes();				
		}

		$photo = new Photo;
		if (isset($_POST['Photo'])) {
			$photo->image = CUploadedFile::getInstance($photo, 'image');
			$photo->name = $photo->image->getName();
			$photo->ad_id = $model->id;
			if ($photo->save()) {
				$path = Yii::getPathOfAlias('webroot').'/upload/'.
					$photo->id.'_'.$photo->image->getName().'.txt';
				$photo->image->saveAs($path);
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		$this->render('create', array(
			'model'=>$model, 'hasChildren'=>$hasChildren, 'lists'=>$lists, 'photo'=>$photo,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Ad']))
		{
			$model->attributes=$_POST['Ad'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Ad');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Ad('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Ad']))
			$model->attributes=$_GET['Ad'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Ad the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Ad::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Ad $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='ad-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
