<?php

class ItemController extends Controller
{
    public $defaultAction = 'admin';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array('rights');
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->render('view', array('model' => $this->loadModel($id)));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model = new Item;
        // $this->performAjaxValidation($model);
        if ($mid = (int)Yii::app()->request->getQuery('mid')) {
            $model->menu_id = $mid;
        }
        if (isset($_POST['Item'])) {
            $model->attributes = $_POST['Item'];
            if ($model->save()) {
                $this->redirect(array('view', 'id' => $model->id));
            }
        }
        $criteria         = new CDbCriteria;
        $criteria->select = new CDbExpression('MAX(sort_order) as sort_order');
        $max              = $model->find($criteria);
        $model->sort_order= $max->sort_order + 1;

        $this->render('create', array('model' => $model));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        // $this->performAjaxValidation($model);
        if (isset($_POST['Item'])) {
            $currentSort       = $model->attributes['sort_order'];
            $model->attributes = $_POST['Item'];
            if ($model->attributes['sort_order'] > $currentSort) {
                $model->updateCounters(
                    array('sort_order' => -1),
                    'sort_order<=:sort_order AND sort_order>=:current_sort',
                    array('sort_order' => $model->attributes['sort_order'], 'current_sort' => $currentSort)
                );
            }
            if ($model->attributes['sort_order'] < $currentSort) {
                $model->updateCounters(
                    array('sort_order' => +1),
                    'sort_order>=:sort_order AND sort_order<=:current_sort',
                    array('sort_order' => $model->attributes['sort_order'], 'current_sort' => $currentSort)
                );
            }
            if ($model->save()) {
                $this->redirect(array('admin'));
            }
        }
        $this->render('update', array('model' => $model));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     * @throws CHttpException
     * @return void
     */
    public function actionDelete($id)
    {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if (!isset($_GET['ajax'])) {
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
            }
        } else {
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        }
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('Item');
        $this->render('index', array('dataProvider' => $dataProvider));
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model = new Item('search');
        $model->unsetAttributes(); // clear any default values
        if (isset($_GET['Item'])) {
            $model->attributes = $_GET['Item'];
        }
        $this->render('admin', array('model' => $model));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param $id
     * @throws CHttpException
     * @internal param \the $integer ID of the model to be loaded
     * @return Item
     */
    public function loadModel($id)
    {
        $model = Item::model()->findByPk($id);
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel $model the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'item-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
