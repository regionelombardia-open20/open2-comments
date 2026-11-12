<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\views\comment-reply
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
use open20\amos\comments\assets\CommentsAsset;
use open20\amos\core\forms\ActiveForm;
use open20\amos\core\forms\AttachmentsWidget;
use open20\amos\core\forms\CloseSaveButtonWidget;
use open20\amos\core\forms\CreatedUpdatedWidget;
use open20\amos\core\forms\TextEditorWidget;
use open20\amos\core\helpers\Html;

CommentsAsset::register($this);

/**
 * @var \yii\web\View $this
 * @var \open20\amos\comments\models\CommentReply $model
 * @var \yii\widgets\ActiveForm $form
 * @var string $fid
 */

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

?>

<div class="comment-reply-form col-xs-12 nop">
    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'comment_reply_' . ((isset($fid)) ? $fid : 0),
            'data-fid' => (isset($fid)) ? $fid : 0,
            'data-field' => ((isset($dataField)) ? $dataField : ''),
            'data-entity' => ((isset($dataEntity)) ? $dataEntity : ''),
            'class' => ((isset($class)) ? $class : ''),
            'enctype' => 'multipart/form-data'
        ]
    ]);
    ?>
    <?php // $form->errorSummary($model, ['class' => 'alert-danger alert fade in']); ?>

    <div class="row">
        <div class="col-lg-8 col-xs-12">
            <?= $form->field($model, 'comment_reply_text')->widget(TextEditorWidget::className(), [
                'clientOptions' => [
                    'lang' => substr(Yii::$app->language, 0, 2),
                    'plugins' => [
                        "paste link",
                    ],
                    'toolbar' => "undo redo | link",
                ]
            ]) ?>
        </div>

        <?php if ($commentsModule->modelCanDoIt($class, 'enableUserSendAttachment')) : ?>
        <div class="col-lg-4 col-xs-12">
            <?= AttachmentsWidget::widget([
                'form' => $form,
                'model' => $model,
                'modelField' => 'commentReplyAttachments',
                'attachInputOptions' => [
                    'multiple' => true
                ],
                'attachInputPluginOptions' => [
                    'maxFileCount' => $commentsModule->maxCommentAttachments,
                    'showPreview' => false,
                    'allowedFileExtensions' => $commentsModule->hasProperty('whiteListFilesExtensions') ? $commentsModule->whiteListFilesExtensions : '',
                ],
            ]) ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($commentsModule->enableUserSendMailCheckbox && $commentsModule->modelCanDoIt($class, 'enableUserSendMailCheckbox') && (Yii::$app->controller->action->id == 'create')): ?>
        <div class="row">
            <div class="col-xs-12">
                <?= Html::checkbox('send-reply-notify-mail', true, ['label' => ' ' . AmosComments::t('amoscomments', '#checkbox_send_notify')]) ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="clearfix"></div>

    <?= CreatedUpdatedWidget::widget(['model' => $model]) ?>
    <?= CloseSaveButtonWidget::widget(['model' => $model]); ?>
    <?php ActiveForm::end(); ?>
</div>
