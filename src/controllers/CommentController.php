<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\controllers
 * @category   CategoryName
 */

namespace open20\amos\comments\controllers;

use DeepCopyTest\Matcher\Y;
use open20\amos\comments\utility\CommentsUtility;
use Yii;
use Exception;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use open20\amos\core\helpers\Html;
use open20\amos\core\record\Record;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\utilities\Email;
use open20\amos\comments\AmosComments;
use open20\amos\comments\models\Comment;
use open20\amos\comments\models\CommentNotification;
use open20\amos\core\helpers\BreadcrumbHelper;
use open20\amos\core\controllers\CrudController;
use open20\amos\comments\models\search\CommentSearch;
use open20\amos\comments\exceptions\CommentsException;
use open20\amos\comments\base\PartecipantsNotification;

use open20\amos\notificationmanager\utility\NotifyUtility;
use open20\amos\comments\models\CommentNotificationUsers;
use open20\amos\admin\models\UserProfile;

/**
 * Class CommentController
 *
 * @property \open20\amos\comments\models\Comment $model
 *
 * @package open20\amos\comments\controllers
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
                'label' => AmosIcons::show('view-list-alt').Html::tag('p', AmosComments::t('amoscomments', 'Table')),
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
        return ArrayHelper::merge(parent::behaviors(),
                [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => [
                                'create-ajax',
                            ],
                            'roles' => ['COMMENTS_ADMINISTRATOR', 'COMMENTS_CONTRIBUTOR']
                        ],
                        [
                            'allow' => true,
                            'actions' => [
                                'comment-notification-user',
                            ],
                            'roles' => ['@']
                        ],
                        [
                            'allow' => true,
                            'actions' => [
                                'adjust-bells',
                            ],
                            'roles' => ['ADMIN']
                        ],

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
        $post        = Yii::$app->request->post();

        /** @var AmosComments $commentsModule */
        $commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

        if ($this->model->load($post) && $this->model->save()) {
            if (!$commentsModule->enableUserSendMailCheckbox || ($commentsModule->enableUserSendMailCheckbox && isset($post['send_notify_mail'])
                && $post['send_notify_mail'])) {
                $partecipantsnotify = $this->getParticipantsNotificationInstance();
                $partecipantsnotify->partecipantAlert($this->model);
            }
            if (Yii::$app->request->isAjax) {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $this->model;
            }

            $this->enableCommentNotification($this->model->context, $this->model->context_id);

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
        $post                       = Yii::$app->request->post();

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



        // check if model values is change
        // new and old comment text
        $user_profile_ids = $this->extractTagginUserCommentText($this->model->comment_text, $this->model->getOldAttribute('comment_text'));

        // send notification to user user_profile_ids
        if( (null != $user_profile_ids) || (!empty($user_profile_ids)) ){

            // // get model context
            $modelContext = new $this->model->context;
            $modelContext = $modelContext::find()->andWhere(['id' => $this->model->context_id])->one();

            $user_profiles = UserProfile::find()->andWhere(['id' => $user_profile_ids])->all();
            Record::sendEmailForUserProfiles($user_profiles, $modelContext, $this->model);
        }


        if ($this->model->save()) {
            //            NotifyUtility::getNetworkNotificationConf()
            /** @var AmosComments $commentsModule */
            $commentsModule = Yii::$app->getModule(AmosComments::getModuleName());
            if (!$commentsModule->enableUserSendMailCheckbox || ($commentsModule->enableUserSendMailCheckbox && isset($post['send_notify_mail'])
                && $post['send_notify_mail'])) {
                $partecipantsnotify = $this->getParticipantsNotificationInstance();
                $partecipantsnotify->partecipantAlert($this->model);
            }

            $this->enableCommentNotification($this->model->context, $this->model->context_id);

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
    public function actionUpdate($id, $noAttach = null, $url = null)
    {
        $this->setUpLayout('form');
        $this->model = $this->findModel($id);
        if ($this->model->load(Yii::$app->request->post()) && $this->model->validate()) {

            if ($this->model->save()) {
                Yii::$app->getSession()->addFlash('success',
                    AmosComments::t('amoscomments', 'Comment successfully updated.'));
                if (!empty($url)) {
                    return $this->redirect($url);
                }

                // enable notification for comments
                $this->enableCommentNotification($this->model->context, $this->model->context_id);

                return $this->redirect(BreadcrumbHelper::lastCrumbUrl());
            } else {
                Yii::$app->getSession()->addFlash('danger',
                    AmosComments::t('amoscomments', 'Comment not updated, check the data entered.'));
                return $this->render('update',
                        [
                        'no_attach' => $noAttach,
                        'url' => $url,
                        'model' => $this->model,
                        'fid' => NULL,
                        'dataField' => NULL,
                        'dataEntity' => NULL,
                ]);
            }
        } else {
            return $this->render('update',
                    [
                    'no_attach' => $noAttach,
                    'model' => $this->model,
                    'url' => $url,
                    'fid' => NULL,
                    'dataField' => NULL,
                    'dataEntity' => NULL,
            ]);
        }
    }



    /**
     * @param Record $contextModel
     * @return string
     */
    private function getSubject(Record $contextModel)
    {
        $content_subject = 'email'.DIRECTORY_SEPARATOR.'content_subject';

        if ($this->commentsModule) {
            if (is_array($this->commentsModule->htmlMailContentSubject)) {
                $contextModelClassName = $contextModel->className();
                if (!empty($this->commentsModule->htmlMailContentSubject[$contextModelClassName])) {
                    $content_subject = $this->commentsModule->htmlMailContentSubject[$contextModelClassName];
                } else {
                    $content_subject = $this->commentsModule->htmlMailContentSubjectDefault;
                }
            } else {
                $content_subject = $this->commentsModule->htmlMailContentSubject;
            }
        }

        $subject = $this->appController->renderMailPartial($content_subject, ['contextModel' => $contextModel]);

        return $subject;
    }


    /**
     * Method to extract taggin user from comment_text (user_profile id)
     *
     * @param string | $new_text
     *
     * @return array | $new_tagging_user_id
     */
    public function extractTagginUserCommentText($new_text = null, $old_text = null){

        $new_tagging_user_id = [];

        // get all user_id from old text and all user_id from new text
        $user_id_new = (array) Record::getMentionUserIdFromText($new_text);
        $user_id_old = (array) Record::getMentionUserIdFromText($old_text);

        // extract only different id user between new user id and old user id
        $new_tagging_user_id = array_diff($user_id_new, $user_id_old);


        // filtro gli utenti per quegli che hanno abilitato le notifiche per la tagatura degli utenti nei commenti
        foreach ($new_tagging_user_id as $key => $user_profile_id) {

            $user_profile = \open20\amos\admin\models\UserProfile::find()->andWhere(['id' => $user_profile_id])->one();

            // remove all user_profile with notify_taggin_user_in_content
            if( $user_profile && ($user_profile->notify_tagging_user_in_content == 0) ){
                unset($new_tagging_user_id[$key]);
            }
        }

        return $new_tagging_user_id;
    }



    /**
     * The delete action deletes all the comment replies, if present, and then the comment.
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id, $url = null)
    {
        $this->model = $this->findModel($id);
        if ($this->model) {
            $ok             = true;
            $commentReplies = $this->model->commentReplies;

            if (!empty($commentReplies)) {
                foreach ($commentReplies as $commentReply) {
                    $commentReply->delete();
                    if ($commentReply->getErrors()) {
                        Yii::$app->getSession()->addFlash('danger',
                            AmosComments::t('amoscomments', 'Errors while deleting a comment reply.'));
                        $ok = false;
                        break;
                    }
                }
            }

            if ($ok) {
                $this->model->delete();
                if (!$this->model->getErrors()) {
                    Yii::$app->getSession()->addFlash('success',
                        AmosComments::t('amoscomments', 'Comment successfully deleted.'));
                } else {
                    Yii::$app->getSession()->addFlash('danger',
                        AmosComments::t('amoscomments', 'Errors while deleting comment.'));
                }
            }
        } else {
            Yii::$app->getSession()->addFlash('danger', AmosComments::t('amoscomments', 'Comment not found.'));
        }
        if (!empty($url)) {
            return $this->redirect($url);
        }
        return $this->redirect(Url::previous());
    }


    /**
     * Method to add the authenticated user to CommentDisabledNotificationUsers
     *
     * @return void | go back
     */
    public function actionCommentNotificationUser($context, $contextId, $enable){

        $ret = CommentsUtility::setCommentNotificationUser($context,$contextId, Yii::$app->user->id, (bool)$enable);
        if( !$ret ){
            \Yii::$app->getSession()->addFlash('danger', \Yii::t('app', 'Errore! Non è stato possibile aggiornare le notifiche dei commenti.'));
            return $this->goBack(\Yii::$app->request->referrer);
        }
        \Yii::$app->getSession()->addFlash('success', \Yii::t('app', 'Le notifiche dei commenti sono state aggiornate con successo.'));
        return $this->goBack(\Yii::$app->request->referrer);

    }

    /**
     * Method to enable comment notification for auth user
     * Enable only if comment notification user not is set!
     *
     * @param string|null $model_context_classname
     * @param int|null $model_context_id
     * @return void
     */
    private function enableCommentNotification(string $model_context_classname, int $model_context_id) {
        if (!is_null($model_context_classname) && !is_null($model_context_id)) {

            $model = CommentsUtility::getCommentNotificationUser(
                $model_context_classname,
                $model_context_id,
                Yii::$app->user->id
            );
            if (empty($model)) {
                $model = new \open20\amos\comments\models\base\CommentNotificationUsers();
                $model->user_id =  Yii::$app->user->id;
                $model->context_model_class_name = $model_context_classname;
                $model->context_model_id = $model_context_id;
                $model->enable = true;
                $model->save(false);
            }
        }
    }

    /**
     * @param false $write
     */
    public function actionAdjustBells($write = false){
        CommentsUtility::scannAllModelsForAdjustBells($write);
    }

}
