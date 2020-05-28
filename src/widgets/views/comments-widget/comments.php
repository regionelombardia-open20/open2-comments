<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\widgets\views\comments-widget
 * @category   CategoryName
 */

use lispa\amos\admin\widgets\UserCardWidget;
use lispa\amos\attachments\components\AttachmentsInput;
use lispa\amos\attachments\components\AttachmentsTable;
use lispa\amos\comments\AmosComments;
use lispa\amos\comments\assets\CommentsAsset;
use lispa\amos\comments\models\CommentReply;
use lispa\amos\core\forms\ContextMenuWidget;
use lispa\amos\core\forms\TextEditorWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\utilities\ModalUtility;
use lispa\amos\core\views\AmosLinkPager;
use yii\web\View;
use yii\widgets\Pjax;


$asset = CommentsAsset::register($this);

/**
 * @var \lispa\amos\comments\widgets\CommentsWidget $widget
 * @var \lispa\amos\comments\models\Comment[] $comments
 * @var \lispa\amos\comments\models\CommentReply[] $commentReplies
 * @var \yii\data\Pagination $pages
 */

$js = <<<JS
    $('#comments_anchor').on('click', '.reply-to-comment', function (event) {
        event.preventDefault();
        Comments.reply($(this).data('comment_id'));
    }).on('click', '.comment-reply-btn-class', function (event) {
        event.preventDefault();
        if (typeof tinymce != 'undefined') {
            tinymce.triggerSave();
        }
        Comments.saveCommentReply($(this).data('comment_id'));
    });
JS;
$this->registerJs($js, View::POS_READY);

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

ModalUtility::createAlertModal([
    'id' => 'ajax-error-comment-reply-modal-id',
    'modalDescriptionText' => AmosComments::t('amoscomments', '#AJAX_ERROR_COMMENT_REPLY')
]);
ModalUtility::createAlertModal([
    'id' => 'empty-comment-reply-modal-id',
    'modalDescriptionText' => AmosComments::t('amoscomments', '#EMPTY_COMMENT_REPLY')
]);

$displayNotifyCheckBox = true;

if (isset($commentsModule->displayNotifyCheckbox)) {
    if (is_bool($commentsModule->displayNotifyCheckbox)) {
        $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
    }
}

?>

<div id="comments-loader" class="text-center hidden">
    <?= Html::img($asset->baseUrl . "/img/inf-circle-loader.gif", ['alt' => AmosComments::t('amoscomments', 'Loading')]) ?>
</div>
<div id="comments_anchor" class="comment_content col-xs-12">

    <?php Pjax::begin([
        'id' => 'pjax-block-comments',
        'timeout' => 15000,
        'linkSelector' => false
    ]); ?>

    <?= (!empty($comments)) ? Html::tag('h2', $widget->options['lastCommentsTitle'], ['class' => 'subtitle-comments']) : '' ?>
    <?php foreach ($comments as $comment): ?>
        <?php /** @var \lispa\amos\comments\models\Comment $comment */ ?>
        <div class="answer col-xs-12 nop media">
            <div class="media-left">
                <?= UserCardWidget::widget(['model' => $comment->createdUserProfile, 'enableLink' => true]) ?>
            </div>
            <div class="answer-details media-body">
                <div class="col-xs-10 nop">
                    <h4><?= Html::a($comment->createdUserProfile, ['/admin/user-profile/view', 'id' => $comment->createdUserProfile->id]) ?></h4>
                    <p> <?= Yii::$app->getFormatter()->asDatetime($comment->created_at) ?></p>
                </div>
                <?= ContextMenuWidget::widget([
                    'model' => $comment,
                    'actionModify' => "/" . AmosComments::getModuleName() . "/comment/update?id=" . $comment->id,
                    'actionDelete' => "/" . AmosComments::getModuleName() . "/comment/delete?id=" . $comment->id,
                    'mainDivClasses' => 'nop col-sm-1 col-xs-2 pull-right'
                ]) ?>
                <div class="clearfix"></div>
                <p class="answer_text"><?= Yii::$app->getFormatter()->asRaw($comment->comment_text) ?></p>
                <?php $commentAttachments = $comment->getCommentAttachmentsForItemView(); ?>
                <?php if (count($commentAttachments) > 0): ?>
                    <?= AttachmentsTable::widget([
                        'model' => $comment,
                        'attribute' => 'commentAttachments'
                    ]) ?>
                <?php endif; ?>
                <div class="answer-action">
                    <?php if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $comment])) { ?>
                        <?= Html::a(
                            AmosComments::t('amoscomments', 'Reply'), 'javascript:void(0);', [
                            'class' => 'underline bold reply-to-comment',
                            'title' => AmosComments::t('amoscomments', 'Reply to comment'),
                            'data-comment_id' => $comment->id
                        ]) ?>
                    <?php } ?>
                </div>
                <div id="bk-comment-reply-<?= $comment->id ?>" class="comment-reply-container hidden">
                    <?php if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $comment])) { ?>
                        <?= Html::label(AmosComments::t('amoscomments', 'Reply'), 'comment-reply-area-' . $comment->id, ['class' => 'sr-only']) ?>
                    <?php } ?>
                    <?= TextEditorWidget::widget([
                        'name' => 'comment-reply-area',
                        'value' => '',
                        'options' => [
                            'id' => 'comment-reply-area-' . $comment->id,
                            'class' => 'form-control'
                        ],
                        'clientOptions' => [
                            'placeholder' => $widget->options['commentReplyPlaceholder'],
                            'lang' => substr(Yii::$app->language, 0, 2),
                            'buttonsHide' => [
                                'image',
                                'file'
                            ]
                        ]
                    ]) ?>
                    <?= AttachmentsInput::widget([
                        'id' => 'commentReplyAttachments' . $comment->id,
                        'name' => 'commentReplyAttachments',
                        'model' => $widget->model,
                        'options' => [ // Options of the Kartik's FileInput widget
                            'multiple' => true, // If you want to allow multiple upload, default to false
                        ],
                        'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
                            'maxFileCount' => $commentsModule->maxCommentAttachments, // Client max files
                            'showPreview' => true
                        ]
                    ]) ?>
                    <?= $this->render('_send_notify_checkbox', [
                        'widget' => $widget,
                        'enableUserSendMailCheckbox' => $commentsModule->enableUserSendMailCheckbox,
                        'displayNotifyCheckBox' => $displayNotifyCheckBox,
                        'checkboxName' => 'send_reply_notify_mail',
                        'viewTypePosition' => CommentReply::VIEW_TYPE_POSITION
                    ]) ?>
                    <div class="clearfix"></div>
                    <div class="clear"></div>
                    <div class="bk-elementActions pull-right">
                        <?= Html::button(AmosComments::t('amoscomments', 'Reply'), [
                            'id' => 'comment-reply-btn-' . $comment->id,
                            'class' => 'btn btn-navigation-primary comment-reply-btn-class',
                            'title' => AmosComments::t('amoscomments', 'Reply'),
                            'data-comment_id' => $comment->id
                        ]) ?>
                    </div>
                </div>
            </div>
            <?php
            $commentReplies = $comment->getCommentReplies()->orderBy(['created_at' => SORT_ASC])->all();
            ?>
            <?php foreach ($commentReplies as $commentReply): ?>
                <?php /** @var \lispa\amos\comments\models\CommentReply $commentReply */
                ?>
                <div class="media col-xs-11 col-xs-offset-1 nop">
                    <div class="media-left">
                        <?= UserCardWidget::widget(['model' => $commentReply->createdUserProfile, 'enableLink' => true]) ?>
                    </div>
                    <div class="media-body">
                        <div class="col-xs-10 nop">
                            <h4><?= Html::a($commentReply->createdUserProfile, ['/admin/user-profile/view', 'id' => $commentReply->createdUserProfile->id]) ?></h4>
                            <div class="data"> <?= Yii::$app->getFormatter()->asDatetime($commentReply->created_at) ?></div>
                        </div>
                        <?= ContextMenuWidget::widget([
                            'model' => $commentReply,
                            'actionModify' => "/" . AmosComments::getModuleName() . "/comment-reply/update?id=" . $commentReply->id,
                            'actionDelete' => "/" . AmosComments::getModuleName() . "/comment-reply/delete?id=" . $commentReply->id,
                            'mainDivClasses' => 'col-sm-1 col-xs-2 nop pull-right'
                        ]) ?>
                        <div class="clearfix"></div>
                        <p><?= Yii::$app->getFormatter()->asRaw($commentReply->comment_reply_text) ?></p>
                        <?php $commentReplyAttachments = $commentReply->getCommentReplyAttachmentsForItemView(); ?>
                        <?php if (count($commentReplyAttachments) > 0): ?>
                            <?= AttachmentsTable::widget([
                                'model' => $commentReply,
                                'attribute' => 'commentReplyAttachments'
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <?= AmosLinkPager::widget([
        'pagination' => $pages,
        'showSummary' => true,
    ]); ?>
    <?php Pjax::end(); ?>
</div>
