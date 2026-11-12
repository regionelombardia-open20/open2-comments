<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\models
 * @author    Aria S.p.A. <protocollo@pec.ariaspa.it>
 * @license   GNU General Public License v3.0 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @link      https://www.ariaspa.it/
 *
 * This file is part of OPEN 2.0.
 *
 * OPEN 2.0 is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OPEN 2.0 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OPEN 2.0. If not, see <https://www.gnu.org/licenses/>.
 */

namespace open20\amos\comments\models;

use open20\amos\attachments\behaviors\FileBehavior;
use open20\amos\attachments\models\File;
use open20\amos\comments\AmosComments;
use yii\helpers\ArrayHelper;
use open20\amos\notificationmanager\behaviors\NotifyBehavior;

/**
 * Class CommentReply
 * This is the model class for table "comment_reply".
 *
 * @method \yii\db\ActiveQuery hasOneFile($attribute = 'file', $sort = 'id')
 * @method \yii\db\ActiveQuery hasMultipleFiles($attribute = 'file', $sort = 'id')
 *
 * @package open20\amos\comments\models
 */
class CommentReply extends \open20\amos\comments\models\base\CommentReply
{
    const VIEW_TYPE_POSITION = 'comment_reply';

    /**
     * @var File[] $commentReplyAttachments
     */
    private $commentReplyAttachments;
    
    /**
     * @var File[] $commentReplyAttachmentsForItemView
     */
    private $commentReplyAttachmentsForItemView;
    
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
        $mimeTypes = '';

        /** @var AmosComments $commentsModule */
        $commentsModule = \Yii::$app->getModule(AmosComments::getModuleName());
        if(isset($commentsModule)) {
            $maxCommentAttachments = $commentsModule->maxCommentAttachments;
            if($commentsModule->hasProperty('mimeTypes'))
                $mimeTypes = $commentsModule->mimeTypes;
        }
        return ArrayHelper::merge(parent::rules(), [
            [['commentReplyAttachments'], 'file', 'maxFiles' => $maxCommentAttachments, 'mimeTypes'=>$mimeTypes],
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'commentReplyAttachments' => AmosComments::t('amoscomments', '#COMMENT_REPLY_ATTACHMENTS'),
        ]);
    }
    
    /**
     * Getter for $this->attachments;
     *
     */
    public function getCommentReplyAttachments()
    {
        if(empty($this->commentReplyAttachments)){
            $this->commentReplyAttachments = $this->hasMultipleFiles('commentReplyAttachments')->one();
        }
        return $this->commentReplyAttachments;
    }


    /**
     * @param $attachments
     */
    public function setCommentReplyAttachments($attachments){
        $this->commentReplyAttachments = $attachments;
    }

    /**
     * @return array|File[]|\yii\db\ActiveRecord[]
     */
    public function getCommentReplyAttachmentsForItemView()
    {
        if(empty($this->commentReplyAttachmentsForItemView)){
            $this->commentReplyAttachmentsForItemView = $this->hasMultipleFiles('commentReplyAttachments')->all();
        }
        return $this->commentReplyAttachmentsForItemView;
    }

    /**
     * @param $attachments
     */
    public function setCommentReplyAttachmentsForItemView($attachments){
        $this->commentReplyAttachmentsForItemView = $attachments;
    }
    
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
    }
}
