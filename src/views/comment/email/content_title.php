<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\views\comment\email
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

use open20\amos\comments\AmosComments;

/**
 * @var \open20\amos\core\interfaces\ModelLabelsInterface $model
 * @var \open20\amos\comments\models\Comment $modelComment
 */

?>

<div style="box-sizing:border-box;color:#000000;">
    <div style="padding:5px 10px;background-color: #F2F2F2;text-align:center;">
        <h1 style="color:#297A38;font-size:1.5em;margin:0;">
            <?= AmosComments::t('amoscomments', '#user_published_comment', $model_reply ? [$model_reply->getCreatedUserProfile()->one()->getSurnameName()] : [$modelComment->getCreatedUserProfile()->one()->getSurnameName()]) ?>
        </h1>
        <p style="font-size:1em;margin:0;margin-top:5px;">
            <?= AmosComments::t('amoscomments', '#there_have_content_interest', [$model->getGrammar()->getIndefiniteArticle(), $model->getGrammar()->getModelSingularLabel()]) ?>
        </p>
    </div>
</div>
