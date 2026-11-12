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
use Yii;

/**
 * This is the base-model class for table "comment_blacklist".
 *
 * @property integer $id
 * @property string $word
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 */
class  CommentBlacklist extends \open20\amos\core\record\Record
{
    /**
     * @var bool $isSearch
     */
    public $isSearch = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment_blacklist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['word'], 'required'],
            [['word'], 'string', 'max' => 255],
            [['word'], 'unique'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['created_by', 'updated_by', 'deleted_by'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'word' => AmosComments::t('amoscomments', 'Termine'),
            'created_at' => AmosComments::t('amoscomments', 'Data di creazione'),
            'updated_at' => AmosComments::t('amoscomments', 'Ultima modifica'),
            'deleted_at' => AmosComments::t('amoscomments', 'Data di eliminazione'),
            'created_by' => AmosComments::t('amoscomments', 'Creato da'),
            'updated_by' => AmosComments::t('amoscomments', 'Modificato da'),
            'deleted_by' => AmosComments::t('amoscomments', 'Eliminato da'),
        ];
    }
}
