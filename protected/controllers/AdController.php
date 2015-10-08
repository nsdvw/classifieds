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
		$regions = Region::getRegionList();
		$model = new Ad;
		$model->attachEavSet(Category::model()->findByPk($id)->set_id);
		$model->category_id = $id;

		$photo = new Photo;
		if (isset($_POST['Ad'])) {
			$model->attributes = $_POST['Ad'];
			$model->author_id = Yii::app()->user->id;
			$transaction = Yii::app()->db->beginTransaction();
			if ($model->saveWithEavAttributes()) {
				$images = CUploadedFile::getInstancesByName('images');
				if ($images) {
					$wrongImage = Photo::validateMultiple($images, $model->id);
					if (!$wrongImage) {
						foreach ($images as $image) {
							$photo = new Photo;
							$photo->image = $image;
							$photo->name = $photo->image->getName();
							$photo->ad_id = $model->id;
							$photo->save(false);
						}
						$transaction->commit();
						$this->redirect(array('view','id'=>$model->id));
					} else {
						$photo = $wrongImage;
						$transaction->rollback();
					}
				} else {
					$transaction->commit();
					$this->redirect(array('view','id'=>$model->id));
				}
			}
		}

		$this->render('create', array(
			'model'=>$model,
			'photo'=>$photo,
			'regions'=>$regions,
		));
	}



	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	/*public function actionUpdate($id)
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
	}*/

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	/*public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}*/

	/**
	 * Lists all models.
	 */
	/*public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Ad');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}*/

	/**
	 * Manages all models.
	 */
	/*public function actionAdmin()
	{
		$model=new Ad('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Ad']))
			$model->attributes=$_GET['Ad'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}*/

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Ad the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = Ad::model()->withEavAttributes()->with(
				'author', 'category', 'city', 'photos'
			)->findByPk($id);
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
