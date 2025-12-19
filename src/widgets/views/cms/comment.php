<?php

use open20\amos\core\forms\TextEditorWidget;
use amos\planner\components\bootstrapitalia\Html;
use open20\amos\comments\models\Comment;
use open20\amos\comments\AmosComments;
use yii\web\View;
use open20\amos\comments\assets\CommentsBootstrapitaliaExtAsset;
use open20\amos\core\utilities\CurrentUser;

CommentsBootstrapitaliaExtAsset::register($this);
$js = "
$('#contribute-btn').on('click', function (event) {
    if (typeof tinymce != 'undefined') {
        tinymce.triggerSave();
    }  
    var type = $('[id^=\"type_\"]:checked').val();
    if(type == 'undefined'){
        type = 1;
    }
    Comments.saveComment(" . $widget->model->id . ", '" . addslashes($widget->model->className()) . "', '" . \Yii::$app->request->csrfParam . "', '" . \Yii::$app->request->csrfToken . "', type)
}); 
";
$this->registerJs($js, View::POS_READY);

$class = $widget->model->className();

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());
$types = Comment::getTypeCategory();

if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $widget->model])) {
    $userProfile = CurrentUser::getUserProfile();
    $userImage = $userProfile->getAvatarUrl('square_small');
    $userNomeCognome = $userProfile->getNomeCognome();
    $displayNotifyCheckBox = true;

    if (isset($commentsModule->displayNotifyCheckbox)) {
        if (is_bool($commentsModule->displayNotifyCheckbox)) {
            $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
        }
    }

    $displayNotifyCheckBox = $displayNotifyCheckBox && $commentsModule->modelCanDoIt($class,
                    'enableUserSendMailCheckbox');

    $openAccordion = false;

    if (isset($commentsModule->accordionOpenedByDefault)) {
        if (is_bool($commentsModule->accordionOpenedByDefault)) {
            if ($commentsModule->accordionOpenedByDefault) {
                $openAccordion = 0;
            }
        }
    }

    $redactorComment = /* Html::tag(
              'div',
              Html::tag('div',
              Html::label($widget->options['commentTitle'], 'contribute-area', ['class' => 'sr-only']). */
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
                    'plugins' => [
                        "advlist autolink lists link charmap print preview anchor",
                        "searchreplace visualblocks code fullscreen code",
                        "insertdatetime media table contextmenu paste textcolor image insertdatetime",
                        "placeholder",
                        "contextmenu paste",
                        "mention"
                    ],
                ],
    ]);
    ?>
    <div class="media mt-5" id="bk-contribute">
        <div class="avatar size-sm mr-2">
            <img src="<?= $userImage ?>" alt="<?= $userNomeCognome ?>">
        </div>
        <div class="media-body">
            <div class="form-group mb-2">
                <?=
                $redactorComment
                ?>
            </div>
            <div class="d-flex form-group mb-2">
                <div class="link-box img-attachment">

                    <?php if ($commentsModule->modelCanDoIt($class, 'enableUserSendAttachment') && $commentsModule->enableAttachmentInComment && !$widget->noAttach) : ?>
                        <form class="md-form" action="#"> 
                            <div class="file-field">
                                <div class="float-left">
                                    <input type="file"  name="commentAttachments" class="btn btn-outline-secondary btn-xs custom-file-input" id="commentAttachments" multiple>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex">
                <div>
                    <?php
                    if ($widget->enableTypeComment && !empty($types)) {
                        $idx = 0;
                        foreach ($types as $k => $type) {
                            ?>
                              <input type="radio" id="type_<?= $k ?>" name="type" value="<?= $k ?>" <?= ($idx == 0)? 'checked' : '' ?>>
                              <label for="type_<?= $k ?>"><?= $type ?></label>
                            <?php
                            $idx++;
                        }
                    }
                    ?>
                    <button id="contribute-btn" type="button" class="btn btn-outline-primary btn-xs">
                        <?= AmosComments::t('amoscomments', 'Aggiungi commento') ?>
                    </button>
                </div>
                <?php if ($widget->viewSendNotification) { ?>
                    <div class="form-check form-check-inline ml-auto">
                        <input id="custom_key" type="text" value="<?= $widget->customKey ?>">
                        <input id="send_notify_mail-1" type="checkbox" name="send_notify_mail" checked="checked" value="1">
                        <label for="send_notify_mail-1"><small> <?= AmosComments::t('amoscomments', 'Invia notifica') ?></small></label>
                    </div>
                <?php } ?>
            </div>

        </div>

    </div>
<?php } ?>