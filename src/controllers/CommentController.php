<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\controllers
 * @category   CategoryName
 */

namespace lispa\amos\comments\controllers;

use lispa\amos\comments\AmosComments;
use lispa\amos\comments\base\PartecipantsNotification;
use lispa\amos\comments\exceptions\CommentsException;
use lispa\amos\comments\models\Comment;
use lispa\amos\comments\models\search\CommentSearch;
use lispa\amos\core\controllers\CrudController;
use lispa\amos\core\helpers\BreadcrumbHelper;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class CommentController
 *
 * @property \lispa\amos\comments\models\Comment $model
 *
 * @package lispa\amos\comments\controllers
 */
class CommentController extends CrudController
{
    /**
     * @var string $layout
     */
    public $layout = 'list';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setModelObj(new Comment());
        $this->setModelSearch(new CommentSearch());

        $this->setAvailableViews([
            'grid' => [
                'name' => 'grid',
                'label' => AmosIcons::show('view-list-alt') . Html::tag('p', AmosComments::t('amoscomments', 'Table')),
                'url' => '?currentView=grid'
            ]
        ]);

        parent::init();
        $this->setUpLayout();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'create-ajax'
                        ],
                        'roles' => ['COMMENTS_ADMINISTRATOR', 'COMMENTS_CONTRIBUTOR']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post', 'get']
                ]
            ]
        ]);
    }

    /**
     * This method returns a new instance of the PartecipantsNotification object.
     * @return PartecipantsNotification
     */
    protected function getParticipantsNotificationInstance()
    {
        return new PartecipantsNotification();
    }

    /**
     * @param string $layout
     * @return string
     */
    public function actionIndex($layout = NULL)
    {
        Url::remember();
        $this->setDataProvider($this->getModelSearch()->search(Yii::$app->request->getQueryParams()));
        return parent::actionIndex();
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionView($id)
    {
        $this->model = $this->findModel($id);

        if ($this->model->load(Yii::$app->request->post()) && $this->model->save()) {
            return $this->redirect(['view', 'id' => $this->model->id]);
        } else {
            return $this->render('view', ['model' => $this->model]);
        }
    }

    /**
     * @return Comment|string|\yii\web\Response
     */
    public function actionCreate()
    {
        $this->setUpLayout('form');
        $this->model = new Comment();
        $post = Yii::$app->request->post();

        /** @var AmosComments $commentsModule */
        $commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

        if ($this->model->load($post) && $this->model->save()) {
            if (!$commentsModule->enableUserSendMailCheckbox || ($commentsModule->enableUserSendMailCheckbox && isset($post['send_notify_mail']) && $post['send_notify_mail'])) {
                $partecipantsnotify = $this->getParticipantsNotificationInstance();
                $partecipantsnotify->partecipantAlert($this->model);
            }
            if (Yii::$app->request->isAjax) {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $this->model;
            }

            return $this->redirect(Url::previous());
        } else {
            return $this->render('create', [
                'model' => $this->model,
            ]);
        }
    }

    /**
     * @return array|Comment
     * @throws CommentsException
     */
    public function actionCreateAjax()
    {
        $this->setUpLayout('form');
        $this->model = new Comment();

        if (!Yii::$app->request->isAjax) {
            throw new CommentsException(AmosComments::t('amoscomments', 'The request is not AJAX.'));
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        if (!$this->model->load($post)) {
            return [
                'error' => [
                    'msg' => AmosComments::t('amoscomments', 'Error loading parameters in the model.')
                ],
            ];
        }

        if (!$this->model->validate()) {
            return [
                'error' => [
                    'msg' => AmosComments::t('amoscomments', 'Validation errors! Check the data entered.')
                ],
            ];
        }

        if ($this->model->save()) {
            /** @var AmosComments $commentsModule */
            $commentsModule = Yii::$app->getModule(AmosComments::getModuleName());
            if (!$commentsModule->enableUserSendMailCheckbox || ($commentsModule->enableUserSendMailCheckbox && isset($post['send_notify_mail']) && $post['send_notify_mail'])) {
                $partecipantsnotify = $this->getParticipantsNotificationInstance();
                $partecipantsnotify->partecipantAlert($this->model);
            }
            return $this->model;
        } else {
            return [
                'error' => [
                    'msg' => AmosComments::t('amoscomments', 'Error during save comment.')
                ],
            ];
        }
    }

    /**
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $this->setUpLayout('form');
        $this->model = $this->findModel($id);
        if ($this->model->load(Yii::$app->request->post()) && $this->model->validate()) {
            if ($this->model->save()) {
                Yii::$app->getSession()->addFlash('success', AmosComments::t('amoscomments', 'Comment successfully updated.'));
                return $this->redirect(BreadcrumbHelper::lastCrumbUrl());
            } else {
                Yii::$app->getSession()->addFlash('danger', AmosComments::t('amoscomments', 'Comment not updated, check the data entered.'));
                return $this->render('update', [
                    'model' => $this->model,
                    'fid' => NULL,
                    'dataField' => NULL,
                    'dataEntity' => NULL,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $this->model,
                'fid' => NULL,
                'dataField' => NULL,
                'dataEntity' => NULL,
            ]);
        }
    }

    /**
     * The delete action deletes all the comment replies, if present, and then the comment.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->model = $this->findModel($id);
        if ($this->model) {
            $ok = true;
            $commentReplies = $this->model->commentReplies;

            if (!empty($commentReplies)) {
                foreach ($commentReplies as $commentReply) {
                    $commentReply->delete();
                    if ($commentReply->getErrors()) {
                        Yii::$app->getSession()->addFlash('danger', AmosComments::t('amoscomments', 'Errors while deleting a comment reply.'));
                        $ok = false;
                        break;
                    }
                }
            }

            if ($ok) {
                $this->model->delete();
                if (!$this->model->getErrors()) {
                    Yii::$app->getSession()->addFlash('success', AmosComments::t('amoscomments', 'Comment successfully deleted.'));
                } else {
                    Yii::$app->getSession()->addFlash('danger', AmosComments::t('amoscomments', 'Errors while deleting comment.'));
                }
            }
        } else {
            Yii::$app->getSession()->addFlash('danger', AmosComments::t('amoscomments', 'Comment not found.'));
        }
        return $this->redirect(Url::previous());
    }
}
