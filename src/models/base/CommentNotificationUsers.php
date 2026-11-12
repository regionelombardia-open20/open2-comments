<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos-comments
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

namespace open20\amos\comments\models\base;

use open20\amos\comments\AmosComments;
use open20\amos\core\user\User;
use Yii;

/**
 * This is the base-model class for table "comment_disabled_notification_users".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $context_model_class_name
 * @property integer $context_model_id
 * @property boolean $enable
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property User;
 */
class CommentNotificationUsers extends \open20\amos\core\record\Record
{
    public $isSearch = false;

	/**
	 * @inheritdoc
	 */
    public static function tableName()
    {
        return 'comment_notification_users';
    }

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        return [
            [['user_id', 'context_model_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['context_model_class_name'], 'string'],
            [['enable'], 'boolean'],
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
            'context_model_id' => AmosComments::t('amoscomments', 'Content Model ID'),
            'context_model_class_name' => AmosComments::t('amoscomments', 'Context Model Class Name'),
            'enable' => AmosComments::t('amoscomments', 'Enable'),
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
