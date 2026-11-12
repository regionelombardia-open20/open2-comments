<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\core\rules
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
use open20\amos\community\models\Community;
use open20\amos\community\utilities\CommunityUtil;
use open20\amos\core\record\Record;
use open20\amos\core\rules\DefaultOwnContentRule;
use open20\amos\cwh\query\CwhActiveQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CommunityUpdateContentRule
 * @package open20\amos\comments\rules
 */
class CommunityUpdateContentRule extends DefaultOwnContentRule
{
    /**
     * @inheritdoc
     */
    public $name = 'communityUpdateContent';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            /** @var Record $model */
            $model          = $params['model'];
            $modelClassName = $model->className();
            if (!strcmp($modelClassName, Comment::className()) || !strcmp($modelClassName, CommentReply::className())) {
                $cwhModule = Yii::$app->getModule('cwh');
                $data      = ArrayHelper::merge(
                        \Yii::$app->getRequest()->post(), \Yii::$app->getRequest()->get()
                );

                if (isset($data['id'])) {
                    $model = $this->instanceModel($model, $data['id']);
                }
                if ($model->id) {
                    if ($model instanceof CommentReply) {

                        /** @var Comment $comment */
                        $comment = $model->comment;
                        /** @var Record $contextModelClassName */
                        $contextModelClassName = $comment->context;
                        /** @var Record $contextModel */
                        $contextModel = $contextModelClassName::findOne($comment->context_id);
                    } elseif ($model instanceof Comment) {

                        /** @var Comment $model */
                        /** @var Record $contextModelClassName */
                        $contextModelClassName = $model->context;
                        /** @var Record $contextModel */
                        $contextModel = $contextModelClassName::findOne($model->context_id);
                    }
                    return $this->validatorContentUpdatePermission($model, $contextModel);
                }
            }
        }

        return false;
    }

    /**
     * @param Comment $model
     * @param Record $contextModel
     * @return bool
     */
    private function validatorContentUpdatePermission($model, $contextModel)
    {
        $cwhModule  = \Yii::$app->getModule('cwh');
        $cwhEnabled = (isset($cwhModule) && !empty($contextModel) && is_object($contextModel) && in_array(get_class($contextModel), $cwhModule->modelsEnabled) && $cwhModule->behaviors);
        if (empty($contextModel)) {
            return false;
        } else {
            if ($cwhEnabled) {
                $scope = $cwhModule->getCwhScope();
                if (isset($cwhModule) && !empty($scope)) {
                    $scope = $cwhModule->getCwhScope();

                    $communityModule = \Yii::$app->getModule('community');
                    if (isset($scope['community']) && $communityModule) {

                        $community = Community::findOne($scope['community']);

                        if (isset($communityModule->forceWorkflowSingleCommunity) && $communityModule->forceWorkflowSingleCommunity) {
                            if (CommunityUtil::hasRole($community) || !$community->force_workflow) {
                                return true;
                            }
                        } else {
                            if (CommunityUtil::hasRole($community)) {
                                return true;
                            }
                        }
                    }
                }
                if (empty($scope) && \Yii::$app->user->can($contextModel->getFacilitatorRole())) {
                    return true;
                }

                $validatorRole = $contextModel->getValidatorRole();
                if (\Yii::$app->user->can('VALIDATOR') || \Yii::$app->user->can($validatorRole)) {
                    return true;
                }
                $cwhActiveQuery     = new CwhActiveQuery(
                    $contextModel->className(),
                    [
                    'queryBase' => $contextModel::find()->distinct()
                ]);
                $queryToValidateIds = $cwhActiveQuery->getQueryCwhToValidate(false)->select($contextModel::tableName().'.id')->column();
            } else {
                // Condizione per avere il permesso
                $isOwner = ($model->created_by == Yii::$app->user->id);

                // Condizione extra come ad esempio essere Community Manager nel plugin Community
                $extraCondition = false;
                if($contextModel->className() == Community::className()){
                    $extraCondition = CommunityUtil::loggedUserIsCommunityManager($contextModel->id);
                }

                return ($isOwner || $extraCondition);
            }

            return (in_array($contextModel->id, $queryToValidateIds));
        }
    }
}