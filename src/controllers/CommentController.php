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
use open20\amos\admin\AmosAdmin;
use open20\amos\comments\utility\CommentsUtility;
use open20\amos\community\models\Community;
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
        return ArrayHelper::merge(parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => [
                                //'create-ajax',
                                'valid',
                                'suspend',
                                'update-chat-ajax'
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
        $post = Yii::$app->request->post();

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
    public function actionUpdateChatAjax($id)
    {
        $post = $data = Yii::$app->request->post();
        $context = $post['context'];
        $context_id = $post['context_id'];
        $timeCheck = $post['timeCheck'];
        $modelContext = new $post['model'];
        $model = $modelContext::find()->andWhere(['id' => $this->model->context_id])->one();

        if (!Yii::$app->request->isAjax) {
            throw new CommentsException(AmosComments::t('amoscomments', 'The request is not AJAX.'));
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $queryNew = Comment::find()
            ->andWhere(['>', 'id', $id])
            ->andWhere(['!=', 'created_by', \Yii::$app->user->id])
            ->andWhere(['context' => $context])
            ->andWhere(['context_id' => $context_id])
            ->all();
        $result = [[], [], []];
        foreach($queryNew as $comment){
            $deleteCAN = Yii::$app->getUser()->can('COMMENT_DELETE', ['model' => $model, 'user_id' => $comment->created_by]);
            $updateCAN = Yii::$app->getUser()->can('COMMENT_UPDATE', ['model' => $model, 'user_id' => $comment->created_by]);
            // If context is community, check if user is community manager
            if($context == 'open20\amos\community\models\Community'){
                $community = Community::findOne($context_id);
                if($community && $community->isCommunityManager()){
                    $deleteCAN = true;
                    $updateCAN = true;
                }
            }

            if(Yii::$app->getFormatter()->asDate($comment->created_at, 'short') === date('d/m/y')){
                $date = Yii::$app->getFormatter()->asTime($comment->created_at, 'short');
            }
            else{
                $date = Yii::$app->getFormatter()->asDateTime($comment->created_at, 'short');
            }

            $result[0][] = [
                'id' => $comment->id,
                'message' => $comment->comment_text,
                'date' => $date,
                'profileId' => '/' . AmosAdmin::getModuleName() . '/user-profile/view?id=' . $comment->createdUserProfile->id,
                'nomeCognome' => $comment->createdUserProfile->nomeCognome,
                'image' => $comment->createdUserProfile->getAvatarWebUrl('square_small'),
                'deleteCAN' => $deleteCAN,
                'updateCAN' => $updateCAN
            ];
        }
        $queryMod = Comment::find()
            ->andWhere(['>', 'updated_at', $timeCheck])
            ->andWhere(['<=', 'id', $id])
            ->andWhere(['!=', 'updated_by', \Yii::$app->user->id])
            ->andWhere(new \yii\db\Expression("comment.created_at != comment.updated_at"))
            ->andWhere(['context' => $context])
            ->andWhere(['context_id' => $context_id])
            ->all();
        foreach($queryMod as $comment){

            $result[1][] = [
                'id' => $comment->id,
                'message' => $comment->comment_text
            ];
        }

        $queryDel = Comment::find()
            ->where(['>', 'deleted_at', $timeCheck])
            ->andWhere(['<=', 'id', $id])
            ->andWhere(['!=', 'deleted_by', \Yii::$app->user->id])
            ->andWhere(['context' => $context])
            ->andWhere(['context_id' => $context_id])
            ->all();
        foreach($queryDel as $comment){

            $result[2][] = [
                'id' => $comment->id
            ];
        }
        return $result;
    }

    /**
     * @return array|Comment
     * @throws CommentsException
     */
    public function actionCreateAjax($chat=false)
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
                    'msg' => reset($this->model->getFirstErrors())//AmosComments::t('amoscomments', 'Validation errors! Check the data entered.')
                ],
            ];
        }


        // check if model values is change
        // new and old comment text
        $user_profile_ids = $this->extractTagginUserCommentText($this->model->comment_text, $this->model->getOldAttribute('comment_text'));

        // send notification to user user_profile_ids
        if ((null != $user_profile_ids) || (!empty($user_profile_ids))) {

            // // get model context
            $modelContext = new $this->model->context;
            $modelContext = $modelContext::find()->andWhere(['id' => $this->model->context_id])->one();

            $user_profiles = UserProfile::find()->andWhere(['id' => $user_profile_ids])->all();
            $this->model->sendEmailForUserProfiles($user_profiles, $modelContext, $this->model);
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
            if($chat){
                // invio mail a tutti quelli che hanno la campanellina attiva tranne me stesso
                $toNotifyList = CommentsUtility::getAllCommentNotificationUserEnabled($this->model->context, $this->model->context_id);
                if (($key = array_search(\Yii::$app->user->id, $toNotifyList)) !== false) {
                    unset($toNotifyList[$key]);
                }
                if ((!empty($toNotifyList)) && isset($post['sendMailNotification']) && ($post['sendMailNotification'])) {
                    $partecipantsnotify = $this->getParticipantsNotificationInstance();
                    $partecipantsnotify->partecipantChatAlert($toNotifyList, $this->model);
                }

                $result = [
                    'id' => $this->model->id,
                    'message' => $this->model->comment_text,
                    'date' => Yii::$app->getFormatter()->asTime($this->model->created_at, 'short'),
                    'profileId' => '/' . AmosAdmin::getModuleName() . '/user-profile/view?id=' . $this->model->createdUserProfile->id,
                    'nomeCognome' => $this->model->createdUserProfile->nomeCognome,
                    'image' => $this->model->createdUserProfile->getAvatarWebUrl('square_small')
                ];
                return $result;
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
     *
     * @param type $id
     * @param type $url
     * @return boolean
     */
    public function actionValid($id, $url = null)
    {

        $this->model = $this->findModel($id);
        if ($this->model->isModerator() && $this->model->public == 0) {

            $this->model->public = 1;
            $this->model->save();
        }
        if (!empty($url)) {
            return $this->redirect($url);
        }
        return $this->redirect($this->goBack());
    }

    /**
     *
     * @param type $id
     * @param type $url
     * @return boolean
     */
    public function actionSuspend($id, $url = null)
    {

        $this->model = $this->findModel($id);
        if ($this->model->isModerator() && $this->model->public == 1) {

            $this->model->public = 0;
            $this->model->save();
        }
        if (!empty($url)) {
            return $this->redirect($url);
        }
        return $this->redirect($this->goBack());
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
                if (Yii::$app->request->isAjax) return true;
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
        $content_subject = 'email' . DIRECTORY_SEPARATOR . 'content_subject';

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
    public function extractTagginUserCommentText($new_text = null, $old_text = null)
    {

        $new_tagging_user_id = [];

        // get all user_id from old text and all user_id from new text
        $user_id_new = (array)Record::getMentionUserIdFromText($new_text);
        $user_id_old = (array)Record::getMentionUserIdFromText($old_text);

        // extract only different id user between new user id and old user id
        $new_tagging_user_id = array_diff($user_id_new, $user_id_old);


        // filtro gli utenti per quegli che hanno abilitato le notifiche per la tagatura degli utenti nei commenti
        foreach ($new_tagging_user_id as $key => $user_profile_id) {

            $user_profile = \open20\amos\admin\models\UserProfile::find()->andWhere(['id' => $user_profile_id])->one();

            // remove all user_profile with notify_taggin_user_in_content
            if ($user_profile && ($user_profile->notify_tagging_user_in_content == 0)) {
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
            $ok = true;
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
        if (Yii::$app->request->isAjax) return true;
        return $this->redirect(Url::previous());
    }


    /**
     * Method to add the authenticated user to CommentDisabledNotificationUsers
     *
     * @return void | go back
     */
    public function actionCommentNotificationUser($context, $contextId, $enable)
    {

        $ret = CommentsUtility::setCommentNotificationUser($context, $contextId, Yii::$app->user->id, (bool)$enable);
        if (!$ret) {
            \Yii::$app->getSession()->addFlash('danger', \Yii::t('app', 'Errore! Non è stato possibile aggiornare le notifiche dei commenti.'));
            if (Yii::$app->request->isAjax) return true;
            else return $this->goBack(\Yii::$app->request->referrer);
        }
        \Yii::$app->getSession()->addFlash('success', \Yii::t('app', 'Le notifiche dei commenti sono state aggiornate con successo.'));
        if (Yii::$app->request->isAjax) return true;
        else return $this->goBack(\Yii::$app->request->referrer);

    }

    /**
     * Method to enable comment notification for auth user
     * Enable only if comment notification user not is set!
     *
     * @param string|null $model_context_classname
     * @param int|null $model_context_id
     * @return void
     */
    private function enableCommentNotification(string $model_context_classname, int $model_context_id)
    {
        if (!is_null($model_context_classname) && !is_null($model_context_id)) {

            $model = CommentsUtility::getCommentNotificationUser(
                $model_context_classname,
                $model_context_id,
                Yii::$app->user->id
            );
            if (empty($model)) {
                $model = new \open20\amos\comments\models\base\CommentNotificationUsers();
                $model->user_id = Yii::$app->user->id;
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
    public function actionAdjustBells($write = false)
    {
        CommentsUtility::scannAllModelsForAdjustBells($write);
    }

}
