<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\rules
 * @category   CategoryName
 */

namespace lispa\amos\comments\rules;

use lispa\amos\comments\models\Comment;
use lispa\amos\comments\models\CommentReply;
use lispa\amos\core\rules\DefaultOwnContentRule;

/**
 * Class DeleteOwnContentCommentsRule
 * @package lispa\amos\comments\rules
 */
class DeleteOwnContentCommentsRule extends DefaultOwnContentRule
{
    public $name = 'deleteOwnContentComments';
    
    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            /** @var \lispa\amos\core\record\Record $model */
            $model = $params['model'];
            if (!$model->id) {
                $post = \Yii::$app->getRequest()->post();
                $get = \Yii::$app->getRequest()->get();
                if (isset($get['id'])) {
                    $model = $this->instanceModel($model, $get['id']);
                } elseif (isset($post['id'])) {
                    $model = $this->instanceModel($model, $post['id']);
                }
            }
            
            if ($model instanceof CommentReply) {
                if($model->isNewRecord){
                    return true;
                }else {
                    /** @var Comment $comment */
                    $comment = $model->comment;
                    /** @var \lispa\amos\core\record\Record $contextModelClassName */
                    $contextModelClassName = $comment->context;
                    /** @var \lispa\amos\core\record\Record $contextModel */
                    $contextModel = $contextModelClassName::findOne($comment->context_id);
                    return ($contextModel->created_by == $user);
                }
            } elseif ($model instanceof Comment) {
                if($model->isNewRecord){
                    return true;
                }else {
                    /** @var Comment $model */
                    /** @var \lispa\amos\core\record\Record $contextModelClassName */
                    $contextModelClassName = $model->context;
                    /** @var \lispa\amos\core\record\Record $contextModel */
                    $contextModel = $contextModelClassName::findOne($model->context_id);
                    return ($contextModel->created_by == $user);
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
