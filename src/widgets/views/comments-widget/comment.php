<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\widgets\views\comments-widget
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

use open20\amos\attachments\components\AttachmentsInput;
use open20\amos\comments\AmosComments;
use open20\amos\comments\assets\CommentsAsset;
use open20\amos\comments\models\Comment;
use open20\amos\core\forms\TextEditorWidget;
use open20\amos\core\helpers\Html;
use open20\amos\core\utilities\ModalUtility;
use yii\web\View;
use yii\helpers\Url;
use open20\amos\admin\AmosAdmin;



if (
    method_exists($widget->model, 'getValidatedStatus')
    &&  $widget->model->status != $widget->model->getValidatedStatus()
) {
    return '';
}

CommentsAsset::register($this);

/**
 * @var \open20\amos\comments\widgets\CommentsWidget $widget
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

$class = $widget->model->className();

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

ModalUtility::createAlertModal([
    'id' => 'ajax-error-comment-modal-id',
    'modalHeader' => AmosComments::t('amoscomments', 'Attenzione'),
    'headerText' => AmosComments::t('amoscomments', 'Attenzione'),

    'modalDescriptionText' => AmosComments::t('amoscomments', '#AJAX_ERROR_COMMENT')
]);
ModalUtility::createAlertModal([
    'id' => 'empty-comment-modal-id',
    'modalHeader' => AmosComments::t('amoscomments', 'Attenzione'),
    'headerText' => AmosComments::t('amoscomments', 'Attenzione'),

    'modalDescriptionText' => AmosComments::t('amoscomments', '#EMPTY_COMMENT')
]);
ModalUtility::createAlertModal([
    'id' => 'scan-comment-modal-id',
    'modalHeader' => AmosComments::t('amoscomments', 'Attenzione'),
    'headerText' => AmosComments::t('amoscomments', 'Attenzione'),

    'modalDescriptionText' => AmosComments::t('amoscomments', 'Il commento contiene termini non consentiti')
]);
?>

<?php if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $widget->model])) : ?>
    <?php
    $displayNotifyCheckBox = true;

    if (isset($commentsModule->displayNotifyCheckbox)) {
        if (is_bool($commentsModule->displayNotifyCheckbox)) {
            $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
        }
    }

    $displayNotifyCheckBox = $displayNotifyCheckBox && $commentsModule->modelCanDoIt(
        $class,
        'enableUserSendMailCheckbox'
    );

    $openAccordion = false;

    if (isset($commentsModule->accordionOpenedByDefault)) {
        if (is_bool($commentsModule->accordionOpenedByDefault)) {
            if ($commentsModule->accordionOpenedByDefault) {
                $openAccordion = 0;
            }
        }
    }
    
    $notifyCheckbox = $this->render('_send_notify_checkbox', [
        'widget' => $widget,
        'enableUserSendMailCheckbox' => $commentsModule->enableUserSendMailCheckbox,
        'displayNotifyCheckBox' => $displayNotifyCheckBox,
        'checkboxName' => 'send_notify_mail',
        'viewTypePosition' => Comment::VIEW_TYPE_POSITION
    ] );

    $scope = \open20\amos\cwh\AmosCwh::getInstance()->getCwhScope();

    $canComment = true;
    if (isset($scope['community'])) {
            $communityModule = \Yii::$app->getModule('community');
            $CommunityId = $scope['community'];
            $community = \open20\amos\community\models\Community::findOne($CommunityId);
            if (!\Yii::$app->user->isGuest && $community) {
                $count = \open20\amos\community\models\CommunityUserMm::find()
                    ->andWhere(['community_id' => $community->id, 'user_id' => \Yii::$app->user->id])
                    ->andWhere(['status' => \open20\amos\community\models\CommunityUserMm::STATUS_ACTIVE])
                    ->count();
                if ($count == 0) {
                    $canComment = false;
                }
        }

    }
    $tags = [];
    ?>
    <div id='bk-contribute'>
        <?php $redactorComment = Html::tag(
            'div',
            Html::tag(
                'div',
                Html::label(
                    $widget->options['commentTitle'],
                    'contribute-area', ['class' => 'sr-only']
                )
                . TextEditorWidget::widget([
                    'name' => 'contribute-area',
                    'value' => null,
                    'language' => substr(Yii::$app->language, 0, 2),
                    'options' => [
                        'id' => 'contribute-area',
                        'class' => 'form-control'
                    ],
                    'clientOptions' => [
                        'placeholder' => $widget->options['commentPlaceholder'],
                        'plugins' => $widget->plugins,
                        'toolbar' => $widget->toolbar,
                        'mobile' => $widget->rteMobile,
                        'mentions' => [
                            'url' => Url::to([
                                '/' . AmosAdmin::getModuleName() . '/user-profile/find-name-user-by-cwh',
                                'className' => $class,
                                'model_id' => $widget->model->id,
                            ])
                        ]
                    ],
                ]),
                ['class' => '']
            ),
            [
                'id' => '',
                'class' => 'contribute-container',
            ]
        );

        if (
            $commentsModule->modelCanDoIt($class, 'enableUserSendAttachment')
            && $commentsModule->enableAttachmentInComment
        ) {
            $attachmComment = Html::tag(
                'div',
                AttachmentsInput::widget(
                    [
                        'id' => 'commentAttachments',
                        'name' => 'commentAttachments',
                        'model' => $widget->model,
                        'options' => [ // Options of the Kartik's FileInput widget
                            'multiple' => true, // If you want to allow multiple upload, default to false
                        ],
                        'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
                            'maxFileCount' => $commentsModule->maxCommentAttachments, // Client max files
                            'showPreview' => false,
                            'allowedFileExtensions' => $commentsModule->hasProperty('whiteListFilesExtensions') ? $commentsModule->whiteListFilesExtensions : '',
                        ]
                    ]
                ),
                ['class' => '']
            );
        } else {
            $attachmComment = '';
        }

        $btnComment = Html::tag(
            'div',
            Html::button(
                AmosComments::t('amoscomments', '#COMMENT_BUTTON'),
                [
                    'id' => 'contribute-btn',
                    'class' => 'btn btn-primary',
                    'title' => AmosComments::t('amoscomments', 'Comment content')
                ]
            ),
            ['class' => 'text-right']
        );
        ?>
        <?php if($canComment) { ?>
        <div id="comments_contribute" class="contribute m-t-35">
            <div class="row">
                <div class="col-xs-12">
                    <strong class="text-uppercase">
                        <?= $widget->options['commentTitle'] ?>
                    </strong>
                </div>
                <div class="col-xs-12 m-b-15 m-t-15">
                    <?= $redactorComment ?>
                </div>
                <div class="col-xs-6 box-upload-file">
                    <?= $attachmComment ?>
                </div>
                <div class="col-xs-12 cta-comment">
                    <div class="row flexbox">
                        <div class="col-sm-8 col-xs-6"><?= $notifyCheckbox ?></div>
                        <div class="col-sm-4 col-xs-6">
                            <?= $btnComment ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>

    <?php
    $urlRedirect = \Yii::$app->request->get('urlRedirect');
    if (
        $urlRedirect
        && (
            strpos($urlRedirect, \Yii::$app->params['platform']['frontendUrl']) !== false
            || strpos($urlRedirect, \Yii::$app->params['platform']['backendUrl']) !== false)
        ) {
        
        echo Html::hiddenInput(
            'urlRedirect',
            $urlRedirect,
            ['id' => 'url-redirect']
        );
    }
    ?>

<?php endif; ?>