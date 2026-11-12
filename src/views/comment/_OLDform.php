<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\views\comment
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
use open20\amos\attachments\components\AttachmentsTableWithPreview;
use open20\amos\comments\AmosComments;
use open20\amos\core\forms\ActiveForm;
use open20\amos\core\forms\CloseSaveButtonWidget;
use open20\amos\core\forms\CreatedUpdatedWidget;
use open20\amos\core\forms\Tabs;
use yii\redactor\widgets\Redactor;

/**
 * @var yii\web\View $this
 * @var open20\amos\comments\models\Comment $model
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
    <?php // $form->errorSummary($model, ['class' => 'alert-danger alert fade in']); ?>
    
    <?php $this->beginBlock('general'); ?>
    <div class="row">
        <div class="col-lg-12 col-sm-12">
            <?= $form->field($model, 'comment_text')->widget(Redactor::className(), [
                'clientOptions' => [
                    'lang' => substr(Yii::$app->language, 0, 2)
                ]
            ]) ?>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php $this->endBlock(); ?>
    <?php
    $itemsTab[] = [
        'label' => AmosComments::tHtml('amoscomments', 'General'),
        'content' => $this->blocks['general'],
    ];
    ?>
    
    <?php $this->beginBlock('attachments'); ?>
    <?= $form->field($model, 'commentAttachments')->widget(AttachmentsInput::classname(), [
        'options' => [ // Options of the Kartik's FileInput widget
            'multiple' => true, // If you want to allow multiple upload, default to false
        ],
        'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
            'maxFileCount' => $commentsModule->maxCommentAttachments, // Client max files
            'showPreview' => false
        ]
    ])->label(AmosComments::t('amoscomments', '#ATTACHMENTS')) ?>
    <?= AttachmentsTableWithPreview::widget([
        'model' => $model,
        'attribute' => 'commentAttachments'
    ]) ?>
    <div class="clearfix"></div>
    <?php $this->endBlock(); ?>
    <?php
    $itemsTab[] = [
        'label' => AmosComments::t('amoscomments', 'Attachments'),
        'content' => $this->blocks['attachments']
    ];
    ?>
    
    <?= Tabs::widget([
        'encodeLabels' => false,
        'items' => $itemsTab
    ]);
    ?>
    
    <?= CreatedUpdatedWidget::widget(['model' => $model]) ?>
    <?= CloseSaveButtonWidget::widget(['model' => $model]); ?>
    <?php ActiveForm::end(); ?>
</div>
