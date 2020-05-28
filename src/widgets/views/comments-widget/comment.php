<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\widgets\views\comments-widget
 * @category   CategoryName
 */

use lispa\amos\attachments\components\AttachmentsInput;
use lispa\amos\comments\AmosComments;
use lispa\amos\comments\assets\CommentsAsset;
use lispa\amos\comments\models\Comment;
use lispa\amos\core\forms\AccordionWidget;
use lispa\amos\core\forms\TextEditorWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\utilities\ModalUtility;
use yii\web\View;

CommentsAsset::register($this);

/**
 * @var \lispa\amos\comments\widgets\CommentsWidget $widget
 */

$js = "
$('#contribute-btn').on('click', function (event) {
    if (typeof tinymce != 'undefined') {
        tinymce.triggerSave();
    }
    Comments.saveComment(" . $widget->model->id . ", '" . addslashes($widget->model->className()) . "')
});
";
$this->registerJs($js, View::POS_READY);

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

ModalUtility::createAlertModal([
    'id' => 'ajax-error-comment-modal-id',
    'modalDescriptionText' => AmosComments::t('amoscomments', '#AJAX_ERROR_COMMENT')
]);
ModalUtility::createAlertModal([
    'id' => 'empty-comment-modal-id',
    'modalDescriptionText' => AmosComments::t('amoscomments', '#EMPTY_COMMENT')
]);

?>

<div id="comments_contribute" class="contribute col-xs-12 nop">
    <?php if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $widget->model])) { ?>

        <?php

        $displayNotifyCheckBox = true;

        if (isset($commentsModule->displayNotifyCheckbox)) {
            if (is_bool($commentsModule->displayNotifyCheckbox)) {
                $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
            }
        }

        $openAccordion = false;

        if (isset($commentsModule->accordionOpenedByDefault)) {
            if (is_bool($commentsModule->accordionOpenedByDefault)) {
                if ($commentsModule->accordionOpenedByDefault) {
                    $openAccordion = 0;
                }
            }
        }

        $redactorComment = Html::tag(
            'div',
            Html::tag('div',
                Html::label($widget->options['commentTitle'], 'contribute-area', ['class' => 'sr-only']) .
                TextEditorWidget::widget([
                    'name' => 'contribute-area',
                    'value' => null,
                    'language' => substr(Yii::$app->language, 0, 2),
                    'options' => [
                        'id' => 'contribute-area',
                        'class' => 'form-control'
                    ],
                    'clientOptions' => [
                        'placeholder' => $widget->options['commentPlaceholder'],
                    ],
                ]) .
                $this->render('_send_notify_checkbox', [
                    'widget' => $widget,
                    'enableUserSendMailCheckbox' => $commentsModule->enableUserSendMailCheckbox,
                    'displayNotifyCheckBox' => $displayNotifyCheckBox,
                    'checkboxName' => 'send_notify_mail',
                    'viewTypePosition' => Comment::VIEW_TYPE_POSITION
                ]),
                ['class' => '']),
            [
                'id' => 'bk-contribute',
                'class' => 'contribute-container col-md-8 col-xs-12 nop'
            ]);

        $attachmComment = Html::tag(
            'div',
            AttachmentsInput::widget([
                'id' => 'commentAttachments',
                'name' => 'commentAttachments',
                'model' => $widget->model,
                'options' => [ // Options of the Kartik's FileInput widget
                    'multiple' => true, // If you want to allow multiple upload, default to false
                ],
                'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
                    'maxFileCount' => $commentsModule->maxCommentAttachments, // Client max files
                    'showPreview' => false
                ]
            ]),
            ['class' => 'col-md-4 col-xs-12 nop']
        );

        $btnComment = Html::tag(
            'div',
            Html::button(AmosComments::t('amoscomments', '#COMMENT_BUTTON'), ['id' => 'contribute-btn',
                'class' => 'btn btn-navigation-primary',
                'title' => AmosComments::t('amoscomments', 'Comment content')]),
            ['class' => 'col-xs-12 text-right m-t-15 nop']
        );
        ?>

        <?= AccordionWidget::widget([
            'items' => [
                [
                    'header' => AmosIcons::show('comments') . $widget->options['commentTitle'],
                    'content' => $redactorComment . $attachmComment . $btnComment
                ]
            ],
            'headerOptions' => ['tag' => 'h2'],
            'clientOptions' => [
                'collapsible' => true,
                'active' => $openAccordion, // set integer 0 for active on load view
                'icons' => [
                    'header' => 'ui-icon-amos am am-plus-square',
                    'activeHeader' => 'ui-icon-amos am am-minus-square',
                ]
            ],
            'options' => [
                'class' => 'first-accordion'
            ]
        ]); ?>

    <?php } ?>
</div>
