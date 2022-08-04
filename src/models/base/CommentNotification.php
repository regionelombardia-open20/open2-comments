<?php

namespace open20\amos\comments\models\base;

use open20\amos\comments\AmosComments;
use open20\amos\core\user\User;
use Yii;

/**
 * This is the base-model class for table "comment_notifications".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $context_model_class_name
 * @property integer $context_model_id
 * @property boolean $read
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property User;
 */
class CommentNotification extends \open20\amos\core\record\Record
{
    public $isSearch = false;

	/**
	 * @inheritdoc
	 */
    public static function tableName()
    {
        return 'comment_notifications';
    }

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        return [
            [['user_id', 'model_id', 'context_model_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['model_class_name', 'context_model_class_name'], 'string'],
            [['read'], 'boolean'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        return [
            'id' => AmosComments::t('amoscomments', 'ID'),
            'user_id' => AmosComments::t('amoscomments', 'User ID'),
            'model_id' => AmosComments::t('amoscomments', 'Model ID'),
            'model_class_name' => AmosComments::t('amoscomments', 'Model Class Name'),
            'context_model_id' => AmosComments::t('amoscomments', 'Content Model ID'),
            'context_model_class_name' => AmosComments::t('amoscomments', 'Context Model Class Name'),
            'read' => AmosComments::t('amoscomments', 'Read'),
            'created_at' => AmosComments::t('amoscomments', 'Created at'),
            'updated_at' => AmosComments::t('amoscomments', 'Updated at'),
            'deleted_at' => AmosComments::t('amoscomments', 'Deleted at'),
            'created_by' => AmosComments::t('amoscomments', 'Created by'),
            'updated_by' => AmosComments::t('amoscomments', 'Updated by'),
            'deleted_by' => AmosComments::t('amoscomments', 'Deleted by'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
