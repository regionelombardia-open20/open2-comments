<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\utility
 * @category   utility
 */

namespace open20\amos\comments\utility;

use open20\amos\comments\AmosComments;
use open20\amos\comments\models\base\CommentNotificationUsers;
use open20\amos\comments\models\CommentNotification;
use open20\amos\comments\models\Comment;
use open20\amos\comments\models\CommentReply;
use open20\amos\core\record\Record;
use open20\amos\core\utilities\ArrayUtility;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 *
 */
class CommentsUtility
{

    public static function setCommentNotificationUser($contextClassName, $contextId, $userId, $enable)
    {
        $ret = false;
        try {
            $model = self::getCommentNotificationUser($contextClassName, $contextId, $userId);
            if (empty($model)) {
                $model = new CommentNotificationUsers();
                $model->user_id = $userId;
                $model->context_model_class_name = $contextClassName;
                $model->context_model_id = $contextId;
            }
            $model->enable = $enable;

            $ret = $model->save(false);
        } catch (\Exception $e) {
            return false;
        }
        return $ret;
    }

    /**
     * @param $contextClassName
     * @param $contextId
     * @param $userId
     * @return CommentNotificationUsers|false
     */
    public static function getCommentNotificationUser($contextClassName, $contextId, $userId)
    {
        try {
            $model = CommentNotificationUsers::findOne(
                [
                    'user_id' => $userId,
                    'context_model_class_name' => $contextClassName,
                    'context_model_id' => $contextId,
                ]
            );
            if (!empty($model)) {
                return $model;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    public static function getCommentNotificationUserStatus($contextClassName, $contextId, $userId)
    {
        $model = self::getCommentNotificationUser($contextClassName, $contextId, $userId);

        if (!empty($model)) {
            return $model->enable;
        }

        return null;
    }

    public static function getAllCommentNotificationUserEnabled($contextClassName, $contextId)
    {
        $cnuList = CommentNotificationUsers::find()
            ->andWhere(['context_model_class_name' => $contextClassName])
            ->andWhere(['context_model_id' => $contextId])
            ->andWhere(['enable' => true])
            ->all();

        return ArrayHelper::map($cnuList,'id', 'user_id');
    }


    public static function scannAllModelsForAdjustBells($write = false)
    {
        /** @var AmosComments $commentsModule */
        $commentsModule = AmosComments::instance();

        if (!empty($commentsModule->bellNotificationEnabledClasses) && is_array($commentsModule->bellNotificationEnabledClasses)){
            foreach ($commentsModule->bellNotificationEnabledClasses as $modelClassName){
                echo "<br>";
                echo "<hr>";
                echo "<h1>$modelClassName</h1>";

                $idList = [];
                $cont = 0;

                $contextModels = $modelClassName::find()->all();
                foreach($contextModels as $contextModel) {
                // potrebbe essere stato eliminato il contesto...
                    $idList[] = $contextModel->id;
                    // se essite il contensto perchè non eliminato allora lo tratto!
                    $model = self::getCommentNotificationUser(
                        $contextModel::className(),
                        $contextModel->id,
                        $contextModel->created_by
                    );
                    if (empty($model)) {
                        $model = new \open20\amos\comments\models\base\CommentNotificationUsers();
                        $model->user_id = $contextModel->created_by;
                        $model->context_model_class_name =  $contextModel::className();
                        $model->context_model_id = $contextModel->id;
                        $model->enable = true;
                        if ($write) {
                            $model->save(false);
                        }
                        $cont++;
                    }
                }

                /** @var ActiveQuery $query */
                $query = Comment::find()
                    ->select(new Expression('id, context, context_id, created_by'))
                    ->andWhere(['context' => $modelClassName])
                    ->groupBy(['context', 'context_id', 'created_by']);

                /** @var Record $model */
                foreach ($query->asArray()->all() as $element) {
//                    VarDumper::dump($element, 3, true);

                    // potrebbe essere stato eliminato il contesto...
                    if (in_array($element['context_id'], $idList)) {

                        // poi i creatori i creatori dei commenti controllo le campanelle
                        $model = self::getCommentNotificationUser(
                            $element['context'],
                            $element['context_id'],
                            $element['created_by']
                        );
                        if (empty($model)) {
                            $model = new \open20\amos\comments\models\base\CommentNotificationUsers();
                            $model->user_id = $element['created_by'];
                            $model->context_model_class_name = $element['context'];
                            $model->context_model_id = $element['context_id'];
                            $model->enable = true;
                            if ($write) {
                                $model->save(false);
                            }
                            $cont++;
                        }

                        // tratto le risposte a questo commento
                        $queryReply = CommentReply::find()
                            ->select(new Expression('created_by'))
                            ->andWhere(['comment_id' => $element['id']])
                            ->groupBy(['created_by']);

                        foreach ($queryReply->asArray()->all() as $arrayRisp) {
                            // poi i creatori i creatori dei commenti controllo le campanelle
                            $modelRisp = self::getCommentNotificationUser(
                                $element['context'],
                                $element['context_id'],
                                $arrayRisp['created_by']
                            );
                            if (empty($modelRisp)) {
                                $modelRisp = new \open20\amos\comments\models\base\CommentNotificationUsers();
                                $modelRisp->user_id = $arrayRisp['created_by'];
                                $modelRisp->context_model_class_name = $element['context'];
                                $modelRisp->context_model_id = $element['context_id'];
                                $modelRisp->enable = true;
                                if ($write) {
                                    $modelRisp->save(false);
                                }
                                $cont++;
                            }
                        }

                    }

                }
                echo 'aggiunte: ' . $cont . ' campanelline';
            }
        }

    }

    public static function setCommentNotificationsAsRead($contextClassName, $contextId, $userId, $read = true)
    {
        if (empty($contextClassName) || empty($contextId) || empty($userId)) return false;
        try {
            CommentNotification::updateAll(['read' => true], ['and', ['context_model_class_name' => $contextClassName], ['context_model_id' => $contextId], ['user_id' => $userId]]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}
