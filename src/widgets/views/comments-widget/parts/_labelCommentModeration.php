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

use open20\amos\comments\AmosComments;
use open20\amos\comments\models\Comment;
use open20\amos\comments\models\CommentReply;
use open20\amos\comments\utility\CommentsUtility;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var Comment|CommentReply $comment
 * @var bool $isReply
 * @var bool $userIsCommentsValidator
 * @var bool $isCommentReply
 * @var string $status
 */

if ($status == Comment::STATUS_REJECTED) { ?>
    <div class="comment-deleted">
        <div class="label-comment-deleted">
            <span class="mdi mdi-close-circle-outline mdi-24px m-r-5"></span>
            <strong class="text-uppercase"><?= AmosComments::t('amoscomments', 'Commento cancellato'); ?></strong>
        </div>
        <?php if ($userIsCommentsValidator) { ?>
            <div class="data-comment-deleted">
                <span class="text-muted"><?= Yii::$app->getFormatter()->asDatetime($comment->created_at) ?></span>
            </div>
        <?php } ?>
    </div>
<?php } else if ($status == Comment::STATUS_TOVALIDATE) {
    if (isset($isReply) && $isReply) {
        $isCommentReply = true;
        /** @var CommentReply $comment */
        $redirectUrl = $comment->comment->contextModel->getFullViewUrl();
    } else {
        $isCommentReply = false;
        /** @var Comment $comment */
        $redirectUrl = $comment->contextModel->getFullViewUrl();
    } ?>
    <div class="header-comment-waiting">
        <div class="label-comment-waiting text-muted">
            <span class="mdi mdi-message-processing-outline mdi-24px m-r-5"></span>
            <strong class="text-uppercase"><?= AmosComments::t('amoscomments', 'Commento in attesa di validazione'); ?></strong>
        </div>

        <?php if ($userIsCommentsValidator) { ?>
            <div class="actions-comment-waiting">
                <?= Html::a('<span class="mdi mdi-check-circle"></span>', ['/comments/comment-dashboard/validate-comment',
                    'id' => $comment->id,
                    'isCommentReply' => $isCommentReply,
                    'redirectUrl' => $redirectUrl
                ], [
                    'class' => 'btn btn-success',
                    'title' => AmosComments::t('amoscomments', 'Valida commento'),
                ]) ?>
                <?= Html::a('<span class="mdi mdi-close-circle"></span>', ['/comments/comment-dashboard/reject-comment',
                    'id' => $comment->id,
                    'isCommentReply' => $isCommentReply,
                    'redirectUrl' => $redirectUrl
                ], [
                    'class' => 'btn btn-danger',
                    'title' => AmosComments::t('amoscomments', 'Rifiuta commento'),
                    'data-confirm' => AmosComments::t('amoscomments', 'Sei sicuro di voler rifiutare questo commento?'),
                ]) ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>