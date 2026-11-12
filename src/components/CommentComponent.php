<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\components
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

namespace open20\amos\comments\components;

use open20\amos\comments\AmosComments;
use open20\amos\comments\models\CommentInterface;
use open20\amos\comments\utility\CommentsUtility;
use open20\amos\comments\widgets\CommentsWidget;
use open20\amos\core\record\Record;
use Yii;
use yii\base\Component;
use yii\base\Event;
use yii\helpers\VarDumper;

/**
 * Class CommentComponent
 * @package open20\amos\events\components
 */
class CommentComponent extends Component implements CommentComponentInterface
{

    /**
     * Method that enable the comments on a specific model.
     * @param \yii\base\Event $event
     */
    public function showComments(Event $event)
    {
        if (isset(Yii::$app->controller->model)) {
            /** @var \open20\amos\core\record\Record $controllerModel */
            $controllerModel = Yii::$app->controller->model;           
            if ($this->checkDisableAutoDisplay($controllerModel) && $this->checkCommentsModuleEnabled($controllerModel) && $this->checkCommentsEnabledOnModel($controllerModel)) {
                echo CommentsWidget::widget([
                    'model' => Yii::$app->controller->model
                ]);
            }
        }
    }

    /**
     * The method checks if the comment module is present and the actual controller
     * model class name is present in the comments module configurations.
     * @param \open20\amos\core\record\Record $controllerModel
     * @return bool
     */
    protected function checkCommentsModuleEnabled($controllerModel)
    {
        /** @var AmosComments $commentsModule */
        $commentsModule = Yii::$app->getModule(AmosComments::getModuleName());
        return (
            !is_null($commentsModule) &&
            isset($commentsModule->modelsEnabled) &&
            in_array($controllerModel::className(), $commentsModule->modelsEnabled)
            );
    }

    /**
     * Method that checks if the controller model is an instance of CommentInterface
     * and then if the model is commentable.
     * @param \open20\amos\core\record\Record $controllerModel
     * @return bool
     */
    protected function checkCommentsEnabledOnModel($controllerModel)
    {
        if ($controllerModel instanceof CommentInterface) {
            /** @var CommentInterface $controllerModel */
            return $controllerModel->isCommentable();
        }
        return false;
    }

    protected function checkDisableAutoDisplay($controllerModel)
    {
        $module = \Yii::$app->getModule('comments');       
        if (!in_array(get_class($controllerModel), $module->disableAutoDisplay)) {
            return true;
        }
        return false;
    }

    /**
     * @param \yii\base\Event $event
     */
    public function addCreatorEnableNotificationUser(Event $event)
    {
        $model = $event->sender;
        if (!empty($model) && ($model instanceof Record) && isset($model->attributes['created_by']) && isset($model->attributes['id'])) {
            CommentsUtility::setCommentNotificationUser($model::className(), $model->id, $model->created_by, true);
        }
    }

}