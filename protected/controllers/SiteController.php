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
            /*'captcha'=>array(
                'class'=>'CCaptchaAction',
                'backColor'=>0xFFFFFF,
            ),*/
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            /*'page'=>array(
                'class'=>'CViewAction',
            ),*/
        );
    }

    public function filters()
    {
        return array('accessControl');
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index','view','search', 'error', 'contact',
                    'login', 'logout', 'cityData', 'getcities'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array('logout'),
                'users'=>array('@'),
            ),
            array('deny',
                'actions'=>array('admin'),
                'users'=>array('*'),
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
        $form = new SearchForm;
        $criteria = new CDbCriteria(array(
            'condition' => 'status="published"',
            'order' => 'added DESC',
            'with' => array('author', 'category', 'city', 'photos'),
            'limit' => 20,
        ));
        $models = Ad::model()->withEavAttributes()->findAll($criteria);
        $dp = new CActiveDataProvider('Ad', array(
            'data' => $models,
            'pagination' => false
        ));
        $regionList = Region::model()->getRegionList();

        $this->render(
            'index',
            array(
                'categories' => $categories,
                'form' => $form,
                'dataProvider' => $dp,
                'regionList' => $regionList,
            )
        );
    }

    /**
     * Action to search ads by key words
     */
    public function actionSearch($id=null,$word=null,$city_id=null,$page=null)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = "status='published'";
        $criteria->order = 'added DESC';

        $form = new EavSearchForm();
        if ($id) {
            $category = Category::model()->findByPk($id);
            $childrenIds = ($category) ? $category->getDescendantIds() : null;
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
        if ($word) {
            try {
                $ids = $this->sphinxSearch($word);
                $criteria->addInCondition('t.id', $ids);
            } catch(Exception $e) {
                $criteria->addCondition('title LIKE :word1 OR description LIKE :word2');
                $criteria->params[':word1'] = "%{$word}%";
                $criteria->params[':word2'] = "%{$word}%";
            }
        }
        if ($city_id) {
            $criteria->addCondition('city_id=:city_id');
            $criteria->params[':city_id'] = intval($city_id);
        }

        $dp = new EavActiveDataProvider('Ad', array(
            'criteria'=>$criteria,
            'countCriteria'=>array(
                'condition'=>$criteria->condition,
                'params'=>$criteria->params),
            'pagination'=>array('pageSize'=>10),
            ));

        $this->render(
            'search',
            array(
                'dataProvider'=>$dp,
                'form'=>$form,
            )
        );
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

    public function actionGetcities()
    {
        if (!isset($_POST['id']) or empty($_POST['id'])) {
            echo json_encode(false);
            Yii::app()->end();
        }
        $regionId = intval($_POST['id']);
        $cities = City::model()->findAllByAttributes(array('region_id'=>$regionId));
        foreach ($cities as $city) {
            $res[$city->city_id] = $city->name;
        }
        echo json_encode($res);
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
