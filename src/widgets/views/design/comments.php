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

use yii\widgets\Pjax;
use open20\amos\comments\AmosComments;
use open20\amos\comments\assets\CommentsDesignAsset;
use open20\amos\admin\AmosAdmin;

$currentAsset = CommentsDesignAsset::register($this);

Pjax::begin([
    'id' => 'pjax-block-comments',
    'timeout' => 15000,
    'linkSelector' => false
]);
?>


<?php
$prev[-1] = 'bk-contribute';
foreach ($comments as $k => $comment):
    ?>
    <?php
    /** @var \open20\amos\comments\models\Comment $comment */
    if ($comment->public == 1 || $comment->created_by == \Yii::$app->user->id || $widget->moderator) {
        $prev[$k] = 'comment_id'.$comment->id;
        ?>
        <div class="media border-bottom mb-4 <?= ($comment->public == 1 ? '' : 'warning') ?>">
            <div class="avatar size-sm mr-2">
                <img src="<?= $comment->createdUserProfile->getAvatarUrl('square_small') ?>" alt="<?= $comment->createdUserProfile->nomeCognome ?>">
            </div>
            <div id="comment_id<?= $comment->id ?>" class="media-body">
                <p class="mt-0 mb-2">
                    <small><a href="/<?=AmosAdmin::getModuleName()?>/user-profile/view?id=<?= $comment->createdUserProfile->id ?>"><?= $comment->createdUserProfile->nomeCognome ?></a> <span class="text-muted"><?= \Yii::$app->formatter->asDatetime($comment->created_at) ?></span></small>
                </p>
                <?= \Yii::$app->formatter->asHtml($comment->comment_text) ?>
                <p class="mt-2">
                    <small>
                        <?php if (\Yii::$app->user->can('COMMENT_UPDATE', ['model' => $comment])) { ?>
                            <a href="<?= "/".AmosComments::getModuleName()."/comment/update?id=".$comment->id."&noAttach=$no_attach&url=".\yii\helpers\Url::current()."#comment_id".$comment->id ?>" class="mr-3">
                                <?=
                                AmosComments::t('amoscomments', 'Modifica')
                                ?>
                            </a>
                        <?php } ?>
                        <?php if (\Yii::$app->user->can('COMMENT_DELETE', ['model' => $comment])) { ?>
                            <a class="text-danger" href="<?=
                            "/".AmosComments::getModuleName()."/comment/delete?id=".$comment->id."&url=".\yii\helpers\Url::current()."#".$prev[$k
                            - 1]
                            ?>"><?=
                                   AmosComments::t('amoscomments', 'Elimina')
                                   ?>
                            </a>
                        <?php } ?>
                        <?php if ($comment->getModerator() && $comment->public == 0) { ?>
                            <a class="text-danger" href="<?=
                            \yii\helpers\Url::to(["/".AmosComments::getModuleName()."/comment/valid",
                                'id' => $comment->id, 'url' => \yii\helpers\Url::current().'#'.$prev[$k]])
                            ?>"><?=
                                   AmosComments::t('amoscomments', 'Valida')
                                   ?>
                            </a>
                        <?php } ?>
                        <?php if ($comment->getModerator() && $comment->public == 1) { ?>
                            <a class="text-danger" href="<?=
                               \yii\helpers\Url::to(["/".AmosComments::getModuleName()."/comment/suspend",
                                   'id' => $comment->id, 'url' => \yii\helpers\Url::current().'#'.$prev[$k]])
                               ?>"><?=
                                   AmosComments::t('amoscomments', 'Sospendi')
                                   ?>
                            </a>
                        <?php } ?>
                    </small>
                </p>
            </div>
        </div>
        <?php
    }
endforeach;
?>
<?php Pjax::end(); ?>