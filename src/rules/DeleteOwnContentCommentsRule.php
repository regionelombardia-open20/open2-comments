<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\rules
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

namespace open20\amos\comments\rules;

use open20\amos\comments\models\Comment;
use open20\amos\comments\models\CommentReply;
use open20\amos\core\rules\DefaultOwnContentRule;

/**
 * Class DeleteOwnContentCommentsRule
 * @package open20\amos\comments\rules
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
            /** @var \open20\amos\core\record\Record $model */
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
                    /** @var \open20\amos\core\record\Record $contextModelClassName */
                    $contextModelClassName = $comment->context;
                    /** @var \open20\amos\core\record\Record $contextModel */
                    $contextModel = $contextModelClassName::findOne($comment->context_id);
                    return ($contextModel->created_by == $user);
                }
            } elseif ($model instanceof Comment) {
                if($model->isNewRecord){
                    return true;
                }else {
                    /** @var Comment $model */
                    /** @var \open20\amos\core\record\Record $contextModelClassName */
                    $contextModelClassName = $model->context;
                    /** @var \open20\amos\core\record\Record $contextModel */
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
