<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\models\base
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
use yii\helpers\ArrayHelper;

/**
 * Class CommentContextAttribute
 * This is the base-model class for table "comment_context_attribute".
 *
 * @property integer $id
 * @property integer $commentable_context
 * @property string $context
 * @property integer $context_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @package open20\amos\comments\models\base
 */
class CommentContextAttribute extends \open20\amos\core\record\Record
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment_context_attribute';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['commentable_context', 'context', 'context_id'], 'required'],
            [['commentable_context'], 'boolean'],
            [['context'], 'string', 'max' => 255],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['context_id', 'created_by', 'updated_by', 'deleted_by'], 'integer']
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => AmosComments::t('amoscomments', 'ID'),
            'commentable_context' => AmosComments::t('amoscomments', 'Commentable Context'),
            'context' => AmosComments::t('amoscomments', 'Context'),
            'context_id' => AmosComments::t('amoscomments', 'Context ID'),
            'created_at' => AmosComments::t('amoscomments', 'Created At'),
            'updated_at' => AmosComments::t('amoscomments', 'Updated At'),
            'deleted_at' => AmosComments::t('amoscomments', 'Deleted At'),
            'created_by' => AmosComments::t('amoscomments', 'Created By'),
            'updated_by' => AmosComments::t('amoscomments', 'Updated By'),
            'deleted_by' => AmosComments::t('amoscomments', 'Deleted By')
        ]);
    }
}
