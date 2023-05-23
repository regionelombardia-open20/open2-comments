<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\widgets\views\comments-widget
 * @category   CategoryName
 */
use open20\amos\admin\widgets\UserCardWidget;
use open20\amos\attachments\components\AttachmentsInput;
use open20\amos\attachments\components\AttachmentsTable;
use open20\amos\comments\AmosComments;
use open20\amos\comments\assets\CommentsAsset;
use open20\amos\comments\models\CommentReply;
use open20\amos\core\forms\ContextMenuWidget;
use open20\amos\core\forms\TextEditorWidget;
use open20\amos\core\helpers\Html;
use open20\amos\core\utilities\ModalUtility;
use open20\amos\core\views\AmosLinkPager;
use yii\web\View;
use yii\widgets\Pjax;
use open20\amos\news\models\News;
use open20\amos\admin\AmosAdmin;
use yii\helpers\Url;

$asset = CommentsAsset::register($this);

/**
 * @var \open20\amos\comments\widgets\CommentsWidget $widget
 * @var \open20\amos\comments\models\Comment[] $comments
 * @var \open20\amos\comments\models\CommentReply[] $commentReplies
 * @var \yii\data\Pagination $pages
 * @var boolean|null $notificationUserStatus
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

$class = $widget->model->className();

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
$contextObject         = null;
if (isset($commentsModule->displayNotifyCheckbox)) {
    if (is_bool($commentsModule->displayNotifyCheckbox)) {
        $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
    }
}

$displayNotifyCheckBox = $displayNotifyCheckBox && $commentsModule->modelCanDoIt($class, 'displayNotifyCheckbox');
?>

<div id="comments-loader" class="text-center hidden">
    <?=
    Html::img($asset->baseUrl."/img/inf-circle-loader.gif", ['alt' => AmosComments::t('amoscomments', 'Loading')])
    ?>
</div>
<div id="comments_anchor">
    <div class="comment_content">


        <?php
        Pjax::begin([
            'id' => 'pjax-block-comments',
            'timeout' => 15000,
            'linkSelector' => false
        ]);
        ?>
        <?php if (!empty($comments)) { ?>
        <div class="comment-content-true">
            <div class="subtitle-comments">
                <?=
                (!empty($comments)) ? Html::tag('h3', $widget->options['lastCommentsTitle'], ['class' => 'subtitle-comments']) : ''
                ?>


                    <?php
                    if (in_array($widget->model->className(), AmosComments::instance()->bellNotificationEnabledClasses)):
                        ?>
                        <div class="subtitle-text-container">
                        <?php
                        if ($notificationUserStatus):
                            $callToAction = Url::toRoute([
                                '/comments/comment/comment-notification-user',
                                'context' => $widget->model->className(),
                                'contextId' => $widget->model->id,
                                'enable' => 0,
                            ]);
                            ?>
                            <a class="icon-link-black" href="<?= $callToAction ?>" title="Notifiche abilitate, clicca qui per disabilitarle">
                                <span class="am am-notifications-add m-r-5" style="font-size: 24px;"></span>
                            </a>

                            <span class="m-r-5"> <?= AmosComments::t('amoscomments', '#able-notify') ?></span>
                            <a href="<?= $callToAction ?>" title="Notifiche abilitate, clicca qui per disabilitarle"> <?= AmosComments::t('amoscomments', '#disable-notify-link') ?></a>
                        <?php
                        else:
                            $callToAction = Url::toRoute([
                                '/comments/comment/comment-notification-user',
                                'context' => $widget->model->className(),
                                'contextId' => $widget->model->id,
                                'enable' => 1,
                            ]);
                            ?>
                            <a class="icon-link-black" href="<?= $callToAction ?>" title="Notifiche disabilitate, clicca qui per abilitarle">
                                <svg class="m-r-5" style="width:24px;height:24px" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M17.5 13A4.5 4.5 0 0 0 13 17.5A4.5 4.5 0 0 0 17.5 22A4.5 4.5 0 0 0 22 17.5A4.5 4.5 0 0 0 17.5 13M17.5 14.5A3 3 0 0 1 20.5 17.5A3 3 0 0 1 20.08 19L16 14.92A3 3 0 0 1 17.5 14.5M14.92 16L19 20.08A3 3 0 0 1 17.5 20.5A3 3 0 0 1 14.5 17.5A3 3 0 0 1 14.92 16M12 2C10.9 2 10 2.9 10 4C10 4.1 10 4.19 10 4.29C7.12 5.14 5 7.82 5 11V17L3 19V20H11.5A6.5 6.5 0 0 1 11 17.5A6.5 6.5 0 0 1 17.5 11A6.5 6.5 0 0 1 19 11.18V11C19 7.82 16.88 5.14 14 4.29C14 4.19 14 4.1 14 4C14 2.9 13.11 2 12 2M10 21C10 22.11 10.9 23 12 23C12.5 23 12.97 22.81 13.33 22.5A6.5 6.5 0 0 1 12.03 21Z" />
                                </svg>
                            </a>

                            <span class="m-r-5"> <?= AmosComments::t('amoscomments', '#disable-notify') ?></span>
                            <a href="<?=$callToAction ?>" title="Notifiche disabilitate, clicca qui per abilitarle"> <?= AmosComments::t('amoscomments', '#able-notify-link') ?></a>

                        <?php
                        endif;
                        ?>
                        </div>
                        <?php
                    endif;
                    ?>

            </div>
        <?php
        $url                   = \Yii::$app->params['platform']['backendUrl'].'/img/img_default.jpg';

        $baseUrl    = ((basename(Yii::getAlias('@app')) == 'backend') ? (!empty(\Yii::$app->params['platform']['backendUrl'])
                ? \Yii::$app->params['platform']['backendUrl'] : "") : (!empty(\Yii::$app->params['platform']['frontendUrl'])
                ? \Yii::$app->params['platform']['frontendUrl'] : ""));
        $currentUrl = str_replace('view?', 'public?', \yii\helpers\Url::current());
        foreach ($comments as $comment):
            if (empty($contextObject)) {
                $classContext  = $comment->context;
                $contextObject = $classContext::findOne($comment->context_id);

                $image = open20\amos\comments\models\Comment::getImageContext($contextObject);
                if (!is_null($image)) {
                    $url = \Yii::$app->params['platform']['backendUrl'].$image->getWebUrl('square_large', false, true);
                }
            }
            ?>
            <?php
            $createdUserProfile = $comment->createdUserProfile;
            /** @var \open20\amos\comments\models\Comment $comment */
            ?>
            <div class="answer media">
                <?php if (!empty($createdUserProfile)) { ?>
                    <div class="media-left">
                        <?= UserCardWidget::widget(['model' => $createdUserProfile, 'enableLink' => true]) ?>
                    </div>
                <?php } ?>
                <div class="answer-details media-body">
                    <div class="col-xs-10 nop">
                        <div>
                            <strong>
                                <?php if (!empty($createdUserProfile)): ?>
                                    <?php if (isset(\Yii::$app->params['disableLinkContentCreator']) && (\Yii::$app->params['disableLinkContentCreator'] === true)): ?>
                                        <?= $createdUserProfile; ?>
                                    <?php else: ?>
                                        <?php
//                                          OBSOLETO
//                                        echo Html::a(
//                                            $createdUserProfile,
//                                            ['/admin/user-profile/view', 'id' => $createdUserProfile->id]
//                                        );
                                        ?>
                                        <?=
                                        (!empty($createdUserProfile) ?
                                            Html::a($createdUserProfile,
                                                [(\Yii::$app->user->isGuest ? '#' : '/'.AmosAdmin::getModuleName().'/user-profile/view'),
                                                    'id' => $createdUserProfile->id]) : '#### ####')
                                        ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= '#### ####'; ?>
                                <?php endif; ?>


                        </strong>
                        </div>
                        <small> <?= Yii::$app->getFormatter()->asDatetime($comment->created_at) ?></small>
                    </div>
                    <?php if ($widget->model->hasMethod('getCloseCommentThread') && !$widget->model->getCloseCommentThread()) : ?>
                        <?=
                        ContextMenuWidget::widget([
                            'model' => $comment,
                            'actionModify' => "/".AmosComments::getModuleName()."/comment/update?id=".$comment->id . "&url=".\yii\helpers\Url::current(),
                            'actionDelete' => "/".AmosComments::getModuleName()."/comment/delete?id=".$comment->id . "&url=".\yii\helpers\Url::current(),
                            'mainDivClasses' => 'nop col-sm-1 col-xs-2 pull-right'
                        ])
                        ?>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                    <p class="answer_text"><?= Yii::$app->getFormatter()->asRaw($comment->comment_text) ?></p>

                    <?=
                    \open20\amos\core\forms\editors\socialShareWidget\SocialShareWidget::widget([
                        'mode' => \open20\amos\core\forms\editors\socialShareWidget\SocialShareWidget::MODE_NORMAL,
                        'configuratorId' => 'socialShare',
                        'model' => $comment,
                        'url' => \yii\helpers\Url::to($baseUrl.$currentUrl, true),
                        'description' => '"'.$comment->comment_text.'"',
                        'imageUrl' => $url,
                        'isComment' => true,
                    ]);
                    ?>
                    <?php $commentAttachments = $comment->getCommentAttachmentsForItemView(); ?>
                    <?php
                    if (count($commentAttachments) > 0):if ($widget->model->hasMethod('getCloseCommentThread') && $widget->model->getCloseCommentThread()) :
                            ?>
                            <?=
                            AttachmentsTable::widget([
                                'model' => $comment,
                                'attribute' => 'commentAttachments',
                                'viewDeleteBtn' => false,
                            ])
                            ?>
                        <?php else: ?>

                            <?=
                            AttachmentsTable::widget([
                                'model' => $comment,
                                'attribute' => 'commentAttachments',
                            ])
                            ?>
                        <?php
                        endif;
                    endif;
                    ?>
                    <div class="answer-action">
                        <?php
                        $module       = \Yii::$app->getModule('comments');
                        $replyComment = true;
                        if (!empty($module->enableCommentOnlyWithScope) && $module->enableCommentOnlyWithScope == true) {
                            $moduleCwh = \Yii::$app->getModule('cwh');
                            if (!is_null($moduleCwh)) {
                                $scope = $moduleCwh->getCwhScope();
                                if (!isset($scope['community'])) {
                                    $replyComment = false;
                                }
                            }
                        }
                        if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $comment]) && $replyComment) {
                            if (!isset(Yii::$app->params['isPoi']) || !($widget->model->className() == News::className()
                                && $widget->model->id == 3126)) {
                                ?>
                                <?php
                                if ($widget->model->hasMethod('getCloseCommentThread') && !$widget->model->getCloseCommentThread()) :
                                    ?>
                                    <?=
                                    Html::a(
                                        AmosComments::t('amoscomments', 'Reply'), 'javascript:void(0);',
                                        [
                                        'class' => 'btn btn-secondary btn-xs reply-to-comment',
                                        'title' => AmosComments::t('amoscomments', 'Reply to comment'),
                                        'data-comment_id' => $comment->id
                                    ])
                                    ?>
                                    <?php
                                endif;
                            }
                        }
                        ?>
                    </div>
                    <?php if ($replyComment) { ?>
                        <div id="bk-comment-reply-<?= $comment->id ?>" class="comment-reply-container hidden">
                            <?php
                            if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $comment])) {
                                ?>
                                <?php
                                if ($widget->model->hasMethod('getCloseCommentThread') && !$widget->model->getCloseCommentThread()) :
                                    ?>
                                    <?=
                                    Html::label(AmosComments::t('amoscomments', 'Reply'),
                                        'comment-reply-area-'.$comment->id, ['class' => 'sr-only'])
                                    ?>
                                <?php endif; ?>
                            <?php } ?>
                            <?=
                            TextEditorWidget::widget([
                                'name' => 'comment-reply-area',
                                'value' => null,
                                'language' => substr(Yii::$app->language, 0, 2),
                                'options' => [
                                    'id' => 'comment-reply-area-'.$comment->id,
                                    'class' => 'form-control'
                                ],
                                'clientOptions' => [
                                    'placeholder' => $widget->options['commentReplyPlaceholder'],
                                    'plugins' => $widget->plugins,
                                    'toolbar' => $widget->toolbar,
                                    'mobile' => $widget->rteMobile,
                                    'buttonsHide' => [
                                        'image',
                                        'file'
                                    ],
                                ],
                            ])
                            ?>
                            <?php
                            if ($commentsModule->modelCanDoIt($class, 'enableUserSendAttachment') && $commentsModule->enableAttachmentInComment) :
                                ?>
                                <?=
                                AttachmentsInput::widget([
                                    'id' => 'commentReplyAttachments'.$comment->id,
                                    'name' => 'commentReplyAttachments',
                                    'model' => $widget->model,
                                    'options' => [// Options of the Kartik's FileInput widget
                                        'multiple' => true, // If you want to allow multiple upload, default to false
                                    ],
                                    'pluginOptions' => [// Plugin options of the Kartik's FileInput widget
                                        'maxFileCount' => $commentsModule->maxCommentAttachments, // Client max files
                                        'showPreview' => true
                                    ]
                                ])
                                ?>
                            <?php endif; ?>
                            <?=
                            $this->render('_send_notify_checkbox',
                                [
                                'widget' => $widget,
                                'enableUserSendMailCheckbox' => $commentsModule->enableUserSendMailCheckbox,
                                'displayNotifyCheckBox' => $displayNotifyCheckBox,
                                'checkboxName' => 'send_reply_notify_mail',
                                'viewTypePosition' => CommentReply::VIEW_TYPE_POSITION
                            ])
                            ?>
                            <div class="clearfix"></div>
                            <div class="clear"></div>
                            <div class="bk-elementActions pull-right">
                                <?php
                                if ($widget->model->hasMethod('getCloseCommentThread') && !$widget->model->getCloseCommentThread()) :
                                    ?>
                                    <?=
                                    Html::button(AmosComments::t('amoscomments', 'Reply'),
                                        [
                                        'id' => 'comment-reply-btn-'.$comment->id,
                                        'class' => 'btn btn-primary comment-reply-btn-class',
                                        'title' => AmosComments::t('amoscomments', 'Reply'),
                                        'data-comment_id' => $comment->id
                                    ])
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <?php
                $commentReplies = $comment->getCommentReplies()->orderBy(['created_at' => SORT_ASC])->all();
                ?>
                <?php foreach ($commentReplies as $commentReply): ?>
                    <?php /** @var \open20\amos\comments\models\CommentReply $commentReply */
                    ?>
                    <div class="media col-xs-11 col-xs-offset-1 nop">
                        <div class="media-left">
                            <?=
                            UserCardWidget::widget(['model' => $commentReply->createdUserProfile,
                                'enableLink' => true])
                            ?>
                        </div>
                        <div class="media-body">
                            <div class="col-xs-10 nop">
                                <div><strong><?=
                                        Html::a($commentReply->createdUserProfile,
                                            ['/' . AmosAdmin::getModuleName() . '/user-profile/view', 'id' => $commentReply->createdUserProfile->id])
                                        ?></strong></div>
                                <small> <?= Yii::$app->getFormatter()->asDatetime($commentReply->created_at) ?></small>
                            </div>
                            <?php if ($widget->model->hasMethod('getCloseCommentThread') && !$widget->model->getCloseCommentThread()) : ?>
                                <?=
                                ContextMenuWidget::widget([
                                    'model' => $commentReply,
                                    'actionModify' => "/".AmosComments::getModuleName()."/comment-reply/update?id=".$commentReply->id . "&url=".\yii\helpers\Url::current(),
                                    'actionDelete' => "/".AmosComments::getModuleName()."/comment-reply/delete?id=".$commentReply->id . "&url=".\yii\helpers\Url::current(),
                                    'mainDivClasses' => 'col-sm-1 col-xs-2 nop pull-right'
                                ])
                                ?>
                            <?php endif; ?>
                            <div class="clearfix"></div>
                            <p><?= Yii::$app->getFormatter()->asRaw($commentReply->comment_reply_text) ?></p>
                            <?php $commentReplyAttachments = $commentReply->getCommentReplyAttachmentsForItemView(); ?>
                            <?=
                            \open20\amos\core\forms\editors\socialShareWidget\SocialShareWidget::widget([
                                'mode' => \open20\amos\core\forms\editors\socialShareWidget\SocialShareWidget::MODE_NORMAL,
                                'configuratorId' => 'socialShare',
                                'model' => $commentReply,
                                'url' => \yii\helpers\Url::to($baseUrl.$currentUrl, true),
                                'description' => '"'.$commentReply->comment_reply_text.'"',
                                'imageUrl' => $url,
                                'isComment' => true,
                            ]);
                            ?>
                            <?php
                            if (count($commentReplyAttachments) > 0):
                                if ($widget->model->hasMethod('getCloseCommentThread') && $widget->model->getCloseCommentThread()) :
                                    ?>
                                    <?=
                                    AttachmentsTable::widget([
                                        'model' => $commentReply,
                                        'attribute' => 'commentReplyAttachments',
                                        'viewDeleteBtn' => false,
                                    ])
                                    ?>
                                <?php else: ?>

                                    <?=
                                    AttachmentsTable::widget([
                                        'model' => $commentReply,
                                        'attribute' => 'commentReplyAttachments',
                                    ])
                                    ?>
                                <?php
                                endif;
                            endif;
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php if (!empty($pages)) { ?>
            <?=
            AmosLinkPager::widget([
                'pagination' => $pages,
                'showSummary' => true,
            ]);
            ?>
        <?php } ?>
        </div>
        <?php } ?>
        <?php Pjax::end(); ?>
    </div>
</div>
