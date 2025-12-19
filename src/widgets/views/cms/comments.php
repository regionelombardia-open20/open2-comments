<?php

use yii\widgets\Pjax;
use open20\amos\core\views\AmosLinkPager;
use open20\amos\comments\AmosComments;
use open20\amos\attachments\components\AttachmentsTable;
use open20\amos\attachments\FileModule;
use open20\amos\admin\AmosAdmin;
use open20\amos\comments\models\Comment;

$types = Comment::getTypeCategory();
$currentAsset = $asset::register($this);
\open20\amos\comments\widgets\SelectCommentEvidence::registerJsWidget($currentAsset);
Pjax::begin([
    'id' => 'pjax-block-comments',
    'timeout' => 15000,
    'linkSelector' => false
]);
?>


<?php
$prev[-1] = 'bk-contribute';
$types = Comment::getTypeCategory();

if (!empty($widget->queryFilter)) {
    $commentsSearch = $widget->queryFilter
            ->select(new \yii\db\Expression("distinct(DATE_FORMAT(created_at, '%Y-%m')) as date"))
            ->orderBy(new \yii\db\Expression("DATE_FORMAT(created_at, '%Y-%m') DESC"))
            ->asArray()
            ->all();

    if (!empty($commentsSearch)) {
        ?>
        <div class="select-wrapper col-xs-6">
            <label for="year-month-comments"><?= \Yii::t('amoscomments', 'Filtra per mese') ?></label>
            <select id="year-month-comments">
                <option selected="" value=""><?= \Yii::t('amoscomments', 'Seleziona ...') ?></option>
                <?php
                $filterSelected = '';
                if (!empty(filter_input(INPUT_GET, 'month'))) {
                    $filterSelected = filter_input(INPUT_GET, 'month');
                }
                foreach ($commentsSearch as $selFilter) {
                    ?>
                    <option value="<?= $selFilter['date'] ?>" <?= ($selFilter['date'] == $filterSelected ? 'selected' : '') ?>><?= ucfirst(\Yii::$app->formatter->asDate($selFilter['date'], 'php:F Y')) ?></option> 
                    <?php
                }
                ?>
            </select>
        </div>
        <?php if ($widget->enableTypeComment && !empty($types)) { ?>
            <div class="select-wrapper col-xs-6">
                <label for="type-comments"><?= \Yii::t('amoscomments', 'Filtra per Tipologia') ?></label>
                <select id="type-comments">
                    <option selected="" value=""><?= \Yii::t('amoscomments', 'Seleziona ...') ?></option>
                    <?php
                    $filterSelected2 = '';
                    if (!empty(filter_input(INPUT_GET, 'type'))) {
                        $filterSelected2 = filter_input(INPUT_GET, 'type');
                    }
                    foreach ($types as $k => $type) {
                        ?>
                        <option value="<?= $k ?>" <?= ($k == $filterSelected2 ? 'selected' : '') ?>><?= $type ?></option> 
                        <?php
                    }
                    ?>
                </select>
            </div>
            <?php
        }
    }
}

if (empty($comments)) {
    echo \Yii::t('amoscomments', 'Non sono presenti commenti.');
}


foreach ($comments as $k => $comment) :
    /** @var \open20\amos\comments\models\Comment $comment */
    $prev[$k] = 'comment_id' . $comment->id;

    $classAdded = '';
    if ($module->enableCommentInEvidence && $comment->in_evidence) {
        $classAdded = 'added-to-evidence';
    }
    $arrBadge = $module->typeCategoryBadge;
    ?>
    <div <?= $lastComment->id == $comment->id ? 'id="last-comment"' : '' ?> class="media border-bottom mb-4 <?= $classAdded ?>">
        <div class="avatar size-sm mr-2">
            <img src="<?= $comment->createdUserProfile->getAvatarUrl('square_small') ?>"
                 alt="<?= $comment->createdUserProfile->nomeCognome ?>">
        </div>
        <div id="comment_id<?= $comment->id ?>" class="media-body">
            <p class="mt-0 mb-2">
                <small><a href="/<?= AmosAdmin::getModuleName() ?>/user-profile/view?id=<?= $comment->createdUserProfile->id ?>"><?= $comment->createdUserProfile->nomeCognome ?></a>
                    <span class="text-muted"><?= \Yii::$app->formatter->asDatetime($comment->created_at) ?></span>
                </small>
                <?php if ($widget->enableTypeComment && !empty($types) && array_key_exists($comment->type, $types)) { ?>
                    <span class="badge rounded-pill bg-<?= (array_key_exists($comment->type, $arrBadge) ? $arrBadge[$comment->type] : 'primary') ?>"><?= $types[$comment->type] ?></span>
                <?php } ?>
                <?=
                \open20\amos\comments\widgets\SelectCommentEvidence::widget([
                    'model' => $comment,
                    'module' => $module,
                    'asset' => $currentAsset,
                ])
                ?>

            </p>
            <?=
            \Yii::$app->formatter->asHtml($comment->comment_text, [
                'HTML.SafeIframe' => true,
                'Attr.AllowedFrameTargets' => '_blank',
            ]);
            ?>
            <?php $commentAttachments = $comment->getCommentAttachmentsForItemView(); ?>
            <?php if (count($commentAttachments) > 0) { ?>
                <div class="p-3 lightgrey-bg-c1  rounded">
                    <p class="h6 text-uppercase">
                        <svg class="icon icon-xs">
                        <use xlink:href="<?= $currentAsset->baseUrl ?>/sprite/material-sprite.svg#ic_attach_file"></use>
                        </svg>
                        Allegati
                    </p>
                    <?php
                    foreach ($commentAttachments as $k => $v) {
                        $urlDelete = \yii\helpers\Url::to([
                                    '/' . FileModule::getModuleName() . '/file/delete',
                                    'id' => $v->id,
                                    'item_id' => $comment->id,
                                    'model' => get_class($comment),
                                    'attribute' => 'commentAttachments'
                        ]);
                        ?>
                        <span class="badge badge-pill badge-light px-3">
                            <a href="<?= $v->getWebUrl() ?>" data-toggle="tooltip" title="Scarica <?= $v->name ?>">
                                <?= $v->name ?>
                            </a>
                            <?php if (\Yii::$app->user->can('COMMENT_UPDATE', ['model' => $comment])) { ?>
                                <a href="<?= $urlDelete ?>" data-toggle="tooltip"
                                   title="<?= AmosComments::t('amoscomments', 'Cancella') ?>"
                                   data-confirm="<?= AmosComments::t('amoscomments', 'Sei sicuro di voler cancellare l\'allegato?') ?>">
                                    <span class="ml-1">
                                        <svg class="icon icon-xs">
                                        <use xlink:href="<?= $currentAsset->baseUrl ?>/sprite/material-sprite.svg#ic_close"></use>
                                        </svg>
                                    </span>
                                    <span class="sr-only"><?= AmosComments::t('amoscomments', 'Cancella allegato') ?></span>
                                </a>
                            <?php } ?>
                        </span>
                    <?php } ?>
                </div>
            <?php } ?>

            <p class="mt-2">
                <small>
                    <?php if (\Yii::$app->user->can('COMMENT_UPDATE', ['model' => $comment])) { ?>
                        <a href="<?= "/" . AmosComments::getModuleName() . "/comment/update?id=" . $comment->id . "&noAttach=$no_attach&url=" . \yii\helpers\Url::current() . "#comment_id" . $comment->id ?>"
                           class="mr-3">
                               <?= AmosComments::t('amoscomments', 'Modifica') ?>
                        </a>
                    <?php } ?>
                    <?php if (\Yii::$app->user->can('COMMENT_DELETE', ['model' => $comment])) { ?>
                        <a class="text-danger"
                           href="<?= "/" . AmosComments::getModuleName() . "/comment/delete?id=" . $comment->id . "&url=" . \yii\helpers\Url::current() . "#" . $prev[$k - 1] ?>"><?= AmosComments::t('amoscomments', 'Elimina') ?>
                        </a>
                    <?php } ?>
                </small>
            </p>
        </div>
    </div>
<?php endforeach; ?>
<?php Pjax::end(); ?>