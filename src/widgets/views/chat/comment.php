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
use open20\amos\comments\assets\CommentsChatAsset;
use open20\amos\comments\models\Comment;
use open20\amos\core\forms\AccordionWidget;
use open20\amos\core\forms\TextEditorWidget;
use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\utilities\ModalUtility;
use yii\web\View;
use open20\amos\news\models\News;
use yii\helpers\Url;
use open20\amos\admin\AmosAdmin;

CommentsChatAsset::register($this);
/**
 * @var \open20\amos\comments\widgets\CommentsWidget $widget
 */

$moduleCommunity = \Yii::$app->getModule('community');
$isEnabledTeams = false;
if ($moduleCommunity && property_exists($moduleCommunity, 'enableMicrosoftTeams') && $moduleCommunity->enableMicrosoftTeams) {
    if (get_class($widget->model) == 'open20\amos\community\models\Community') {
        $community = \open20\amos\community\models\Community::findOne($widget->model->id);
        if ($community) {
            if (empty($community->parent_id)) {
                $isEnabledTeams = $community->enable_msgraph_teams;
            } else {
                $parent = \open20\amos\community\models\Community::getCommunityRoot($community);
                if ($parent) {
                    $isEnabledTeams = $parent->enable_msgraph_teams;
                }
            }
        }
    }
}

$template = '<div class="content-message-chat" id="" tabindex="0">' .
    '<div class="media">' .
    '<div class="media-left">' .
    '<div class="container-round-img-xs">' .
    '<img class="square-img" src="" alt="" style="margin-left: 0%; margin-top: 0%;">' .
    '</div>' .
    '</div>' .
    '<div class="media-body">' .
    '<div class="header-message-chat">' .
    '<div class="name-author">' .
    '<small>' .
    '<a href=""></a>' .
    '</small>' .
    '</div>' .
    '<div class="date-message-chat">' .
    '<small class="date-message"></small>' .
    '<span style="display:none" class="mdi mdi-microsoft-teams icon-ms-teams color-ms-teams mdi-18px"></span>'.
    '</div>' .
    '<div class="content-action-date">' .
    '<div class="action-message-chat">';

if (!$isEnabledTeams) {
    $template .=
        '<button id="" class="btn btn-xs btn-danger deleteComment bi bi-bucket" style="margin-right:3px">' .
        '<span class="am am-delete"></span>' .
        '</button>' .
        '<button id="" class="btn btn-xs btn-secondary updateComment bi bi-gea">' .
        '<span class="am am-edit"></span>' .
        '</button>';
}

$template .= '</div>' .
    '</div>' .
    '</div>' .
    '<p class="answer_text"></p>' .
    '</div>' .
    '</div>' .
    '</div>';


$js = "
    // Premuto pulsante per salvare il messaggio
    $('.sendSaveComment').on('click', function (event) {
        if (typeof tinymce != 'undefined') {
            tinymce.triggerSave();
        }
        Comments.saveComment(" . $widget->model->id . ", '" . addslashes($widget->model->className()) . "');
    });
    
    // Premuto pulsante per modificare il messaggio
    $('.sendUpdateComment').on('click', function (event) {
        Comments.resetSelectedComment();
        Comments.updateComment();
    });
    
    // Premuto annulla, nessuna modifica al messaggio
    $('.sendCancelUpdateComment').on('click', function (event) {
        Comments.resetSelectedComment();
        $('#contribute-area').val('');
        Comments.saveButton();
        Comments.disableSendButton();
    });
    
    // Invio messaggio quando si preme ENTER
    $(document).on('keydown', '#contribute-area', function(event){
        if(event.which === 13 && !event.shiftKey) {
            event.preventDefault();
            if(!$('#contribute-btn').is(':hidden') && !$('#contribute-btn').is(':disabled')){
                Comments.saveComment(" . $widget->model->id . ", '" . addslashes($widget->model->className()) . "');
            }
            else if(!$('#contributeUpdate-btn').is(':hidden') && !$('#contributeUpdate-btn').is(':disabled')){
                Comments.resetSelectedComment();
                Comments.updateComment();
            } 
        }
    });
    
    // Abilitazione pulsante invio solo quando c'è del testo + limite max
    if($('#contribute-area').val().length < 1) Comments.disableSendButton();
    
    $(document).on('change keyup paste', '#contribute-area', function(event){  
        let messageLength = $('#contribute-area').val().length;
        if(messageLength < 1) Comments.disableSendButton();
        else {
            if(messageLength > 220){
                let newText = $('#contribute-area').val().slice(0, 220);
                $('#contribute-area').val(newText);
            }
            Comments.enableSendButton();
        }
    });
    
    Comments.template = '" . $template . "';
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
    $notifyCheckbox = $this->render(
        '_send_notify_checkbox',
        [
            'widget' => $widget,
            'enableUserSendMailCheckbox' => $commentsModule->enableUserSendMailCheckbox,
            'displayNotifyCheckBox' => $displayNotifyCheckBox,
            'checkboxName' => 'send_notify_mail',
            'viewTypePosition' => Comment::VIEW_TYPE_POSITION
        ]
    );

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
        <div class="write-comment-chat">

            <div>
                <div>
                    <?php 
                    $labelRedactorComment = Html::label(
                        AmosComments::t('amoscomments', 'Scrivi messaggio'),
                        'contribute-area',
                        ['class' => 'hidden']
                    );
                    $redactorComment = Html::textArea(
                        'contribute-area',
                        '',
                        [
                            'id' => 'contribute-area',
                            'placeholder' => AmosComments::t('amoscomments', 'Scrivi messaggio (max 220 caratteri)'),
                            'rows' => '1'
                        ]
                    );

                    $btnComment =
                        Html::button(
                            AmosComments::t('amoscomments', 'Invia'),
                            [
                                'id' => 'contribute-btn',
                                'class' => 'btn btn-primary sendSaveComment',
                                'title' => AmosComments::t('amoscomments', 'Invia chat'),
                                'aria-label' => AmosComments::t('amoscomments', 'Invia chat')
                            ]
                        );

                    ?>

                    <?php
                    $btnCancelUpdateComment =
                        Html::button(
                            AmosComments::t('amoscomments', 'Annulla'),
                            [
                                'id' => 'contributeCancelUpdate-btn',
                                'class' => 'btn btn-outline-secondary sendCancelUpdateComment',
                                'style' => 'display:none',
                                'title' => AmosComments::t('amoscomments', 'Annulla')
                            ]
                        );
                    $btnUpdateComment =
                        Html::button(
                            AmosComments::t('amoscomments', 'Modifica'),
                            [
                                'id' => 'contributeUpdate-btn',
                                'class' => 'btn btn-warning sendUpdateComment',
                                'style' => 'display:none',
                                'title' => AmosComments::t('amoscomments', 'Comment content')
                            ]
                        );
                    ?>

                    <div class="redactor">
                        <div class="text-area">
                            <?= $canComment ? $labelRedactorComment . $redactorComment : '' ?>
                        </div>
                    </div>
                    <div class="action-chat-footer">

                        <div class="notify">
                            <?= $notifyCheckbox ?>

                        </div>
                        <div class="actions-buttons-chat">
                            <div class="button-send-comment">
                                <?= $canComment ? $btnComment : '' ?>
                                <?= $btnCancelUpdateComment ?>
                                <?= $btnUpdateComment ?>
                            </div>
                        </div>
                    </div>


                    <!--</div>-->
                </div>
            </div>
        </div>
    </div>
    <?php
    if (\Yii::$app->request->get('urlRedirect') && (strpos(
                \Yii::$app->request->get('urlRedirect'),
                \Yii::$app->params['platform']['frontendUrl']
            ) !== false || strpos(
                \Yii::$app->request->get('urlRedirect'),
                \Yii::$app->params['platform']['backendUrl']
            ) !== false)) {
        echo Html::hiddenInput('urlRedirect', \Yii::$app->request->get('urlRedirect'), ['id' => 'url-redirect']);
    }
    ?>
<?php
endif ?>