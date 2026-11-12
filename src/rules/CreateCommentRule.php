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

use open20\amos\core\helpers\StringHelper;
use open20\amos\core\record\Record;
use open20\amos\core\user\User;
use open20\amos\core\rules\ReadContextRule;
use yii\rbac\Rule;

/**
 * Class CreateCommentRule
 * @package open20\amos\comments\rules
 */
class CreateCommentRule extends ReadContextRule
{
    public $name = 'createCommentFromContext';
    public $contextClass = 'context';
    public $contextId = 'context_id';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            $model = $params['model'];
            $modelNameForRule = StringHelper::baseName($model->className());
            if ($modelNameForRule == 'Comment' || $modelNameForRule == 'CommentReply')
                return parent::execute($user, $item, $params);
            /** @var Record $model */
            \Yii::debug(strtoupper($modelNameForRule).'_READ', 'comment');
            return \Yii::$app->user->can(strtoupper($modelNameForRule).'_READ', $params);
        } else {
            return false;
        }
    }
}
