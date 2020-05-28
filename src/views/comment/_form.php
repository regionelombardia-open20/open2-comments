<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\views\comment
 * @category   CategoryName
 */

use lispa\amos\comments\AmosComments;
use lispa\amos\comments\assets\CommentsAsset;
use lispa\amos\core\forms\ActiveForm;
use lispa\amos\core\forms\AttachmentsWidget;
use lispa\amos\core\forms\CloseSaveButtonWidget;
use lispa\amos\core\forms\CreatedUpdatedWidget;
use lispa\amos\core\forms\TextEditorWidget;
use lispa\amos\core\helpers\Html;

CommentsAsset::register($this);

/**
 * @var yii\web\View $this
 * @var lispa\amos\comments\models\Comment $model
 * @var yii\widgets\ActiveForm $form
 * @var string $fid
 */

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

?>

<div class="comment-form col-xs-12 nop">
    <?php $form = ActiveForm::begin([
        'options' => [
            'id' => 'comment_' . ((isset($fid)) ? $fid : 0),
            'data-fid' => (isset($fid)) ? $fid : 0,
            'data-field' => ((isset($dataField)) ? $dataField : ''),
            'data-entity' => ((isset($dataEntity)) ? $dataEntity : ''),
            'class' => ((isset($class)) ? $class : ''),
            'enctype' => 'multipart/form-data'
        ]
    ]);
    ?>

    <div class="row">
        <div class="col-lg-8 col-xs-12">
            <?= $form->field($model, 'comment_text')->widget(TextEditorWidget::className(), [
                'clientOptions' => [
                    'lang' => substr(Yii::$app->language, 0, 2)
                ]
            ]) ?>
        </div>
        <div class="col-lg-4 col-xs-12">
            <?= AttachmentsWidget::widget([
                'form' => $form,
                'model' => $model,
                'modelField' => 'commentAttachments',
                'attachInputOptions' => [
                    'multiple' => true
                ],
                'attachInputPluginOptions' => [
                    'maxFileCount' => $commentsModule->maxCommentAttachments,
                    'showPreview' => false
                ],
            ]) ?>
        </div>
    </div>

    <?php if ($commentsModule->enableUserSendMailCheckbox && (Yii::$app->controller->action->id == 'create')): ?>
        <div class="row">
            <div class="col-xs-12">
                <?= Html::checkbox('send-notify-mail', true, ['label' => ' ' . AmosComments::t('amoscomments', '#checkbox_send_notify')]) ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="clearfix"></div>

    <?= CreatedUpdatedWidget::widget(['model' => $model]) ?>
    <?= CloseSaveButtonWidget::widget(['model' => $model]); ?>
    <?php ActiveForm::end(); ?>
</div>
