<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\models
 * @category   CategoryName
 */

namespace lispa\amos\comments\models;

use lispa\amos\attachments\behaviors\FileBehavior;
use lispa\amos\attachments\models\File;
use lispa\amos\comments\AmosComments;
use yii\helpers\ArrayHelper;
use lispa\amos\notificationmanager\behaviors\NotifyBehavior;

/**
 * Class Comment
 * This is the model class for table "comment".
 *
 * @method \yii\db\ActiveQuery hasOneFile($attribute = 'file', $sort = 'id')
 * @method \yii\db\ActiveQuery hasMultipleFiles($attribute = 'file', $sort = 'id')
 *
 * @package lispa\amos\comments\models
 */
class Comment extends \lispa\amos\comments\models\base\Comment
{
    const VIEW_TYPE_POSITION = 'comment';

    public $publicatedByLabel;

    /**
     * @var File[] $commentAttachments
     */
    private $commentAttachments;
    
    /**
     * @var File[] $commentAttachmentsForItemView
     */
    public $commentAttachmentsForItemView;


    /**
     */
    public function init()
    {
        parent::init();
        $this->publicatedByLabel = AmosComments::t('amoscomments', 'Pubblicato da');


    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'fileBehavior' => [
                'class' => FileBehavior::className()
            ],
            'NotifyBehavior' => [
                'class' => NotifyBehavior::className(),
                'conditions' => [],
            ],
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $maxCommentAttachments = 0;

        /** @var AmosComments $commentsModule */
        $commentsModule = \Yii::$app->getModule(AmosComments::getModuleName());
        if(isset($commentsModule)) {
            $maxCommentAttachments = $commentsModule->maxCommentAttachments;
        }
        return ArrayHelper::merge(parent::rules(), [
            [['commentAttachments'], 'file', 'maxFiles' => $maxCommentAttachments],
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'commentAttachments' => AmosComments::t('amoscomments', '#COMMENT_ATTACHMENTS'),
        ]);
    }
    
    /**
     * Getter for $this->attachments;
     *
     */
    public function getCommentAttachments()
    {
        if(empty($this->commentAttachments)){
            $this->commentAttachments = $this->hasMultipleFiles('commentAttachments')->one();
        }
        return $this->commentAttachments;
    }

    /**
     * @param $attachments
     */
    public function setCommentAttachments($attachments){
        $this->commentAttachments = $attachments;
    }

    /**
     * @return array|File[]|\yii\db\ActiveRecord[]
     */
    public function getCommentAttachmentsForItemView(){
        if(empty($this->commentAttachmentsForItemView)){
            $this->commentAttachmentsForItemView = $this->hasMultipleFiles('commentAttachments')->all();
        }
        return $this->commentAttachmentsForItemView;
    }
    
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

    }
}
