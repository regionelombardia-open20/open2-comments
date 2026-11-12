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

use open20\amos\admin\widgets\UserCardWidget;
use open20\amos\comments\AmosComments;
use open20\amos\comments\assets\CommentsChatAsset;
use open20\amos\community\AmosCommunity;
use open20\amos\core\helpers\Html;
use open20\amos\core\utilities\ModalUtility;
use open20\amos\core\views\AmosLinkPager;
use yii\web\View;
use open20\amos\admin\AmosAdmin;
use yii\helpers\Url;

$asset = CommentsChatAsset::register($this);

$moduleCommunity = \Yii::$app->getModule('community');
$moduleCwh = \Yii::$app->getModule('cwh');
$scopeCommunityId = null;
if (!is_null($moduleCwh)) {
    $scope = $moduleCwh->getCwhScope();
    if (isset($scope['community'])) {
        $scopeCommunityId = $scope['community'];
        $modelCommunity = \open20\amos\community\models\Community::findOne($scopeCommunityId);
        if($modelCommunity->msgraph_channel_id){
            $urlUpdateMessage = '/community/ms-teams/update-channel-messages?communityId=' . $scopeCommunityId;
        }
    }
}

function object_to_array($data)
{
    if (is_array($data) || is_object($data)) {
        $result = array();

        foreach ($data as $key => $value) {
            $result[$key] = object_to_array($value);
        }

        return $result;
    }

    return $data;
}

/**
 * @var \open20\amos\comments\widgets\CommentsWidget $widget
 * @var \open20\amos\comments\models\Comment[] $comments
 * @var \open20\amos\comments\models\CommentReply[] $commentReplies
 * @var \yii\data\Pagination $pages
 * @var boolean|null $notificationUserStatus
 */

// Variabili da dare a Javascript
$notificationChanger = Url::toRoute([
    '/comments/comment/comment-notification-user',
    'context' => $widget->model->className(),
    'contextId' => $widget->model->id,
    'enable' => ''
]);
$contextMessage = json_encode($widget->model->className());

$theModel = json_encode($widget->model->className());

$js = <<<JS
    $(document).ready(function(){
        // Attributi di Comments
        Comments.context = {$contextMessage};
        Comments.context_id = {$widget->model->id};
        Comments.model = {$theModel};
        
        // Abbasso la barra laterale
        Comments.updateScroll();
        
        // Numero di messaggi presenti
        Comments.totalComments = $('.content-message-chat').length;
        // Salvo l'ultimo messaggio che vedo in chat
        Comments.lastMessageId = (Comments.totalComments > 0) ? $('.content-message-chat').last().prop('id').split('-')[1] : 0;
        
        // Controllo tag e link nei messaggi arrivati non ajax
        $('.content-message-chat').each(function(){
            let message = $(this).find('.answer_text');
            let messageText = Comments.at2Strong(message.text());
            messageText = Comments.search4Links(messageText);
            message.html(messageText);
        });
    });    
    
    // Aggiornamento automatico
    var refreshChat = window.setInterval(function(){
        Comments.autoupdate();
    }, 5*1000); // Comments.autoupdateInterval per test
    
    // Pulsante notifiche messaggi
    $(document).on('click', '#toggleNotification', function(event){
        Comments.toggleNotification("$notificationChanger");
    });
    
    // Pulsantino cancellazione messaggio
    $(document).on('click', '.deleteComment', function(event){
        Comments.idDeleteComment = $(this).attr('id'); // Id del messaggio contenente il codice univoco
        $('#ajax-delete-comment-confirm-id').modal('show');      
    });
    
    // Pulsante modale
    $(document).on('click', '#confirmDeleteComment', function(event){
        Comments.deleteComment();  
        $('#ajax-delete-comment-confirm-id').modal('hide');
    });
    
    // Pulsantino modifica messaggio
    $(document).on('click', '.updateComment', function(event){
        // Reset evidenziazione grafica
        Comments.resetSelectedComment();
        // Id del commento
        let id = $(this).attr('id').split("-")[1];
        Comments.idUpdateComment = id;
        Comments.selectedComment();
        // Inserisci nel text-area il commento da modificare e prendi il focus
        let textComment = $('#comment-'+id+' [class*="answer_text"]').text();
        $('#contribute-area').val(textComment).focus();
        
        // Modifica il pulsante di submit
        Comments.updateButton();
        Comments.enableSendButton();
    });
JS;
$this->registerJs($js, View::POS_READY);

$class = $widget->model->className();

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

ModalUtility::createConfirmModal([
    'id' => 'ajax-delete-comment-confirm-id',
    'modalHeader' => AmosComments::t('amoscomments', 'Attenzione'),
    'headerText' => AmosComments::t('amoscomments', 'Attenzione'),
    'modalDescriptionText' => AmosComments::t('amoscomments', 'Vuoi veramente cancellare il messaggio?'),
    'confirmBtnOptions' => [
        'class' => 'btn btn-navigation-primary',
        'id' => 'confirmDeleteComment'
    ]
]);

ModalUtility::createAlertModal([
    'id' => 'scan-comment-modal-id',
    'modalHeader' => AmosComments::t('amoscomments', 'Attenzione'),
    'headerText' => AmosComments::t('amoscomments', 'Attenzione'),
    'modalDescriptionText' => AmosComments::t('amoscomments', 'Il commento contiene termini non consentiti')
]);

$displayNotifyCheckBox = true;
$contextObject = null;
if (isset($commentsModule->displayNotifyCheckbox)) {
    if (is_bool($commentsModule->displayNotifyCheckbox)) {
        $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
    }
}

$displayNotifyCheckBox = $displayNotifyCheckBox && $commentsModule->modelCanDoIt($class, 'displayNotifyCheckbox');
?>

<?php
/** js inserito come fix temporaneo in quanto il widget della chat è stato creato come widget-icon e non widget-graphic.
 * se inserito non in prima posizione, come per DUH, la grafica non regge.
 * serve un refactor in modo che prenda la struttura html corretta dei widget grafici
 */
$jsPezza = <<<JS
    $(document).ready(function(){
        $('#comments_anchor').parent('#comments-container').parent('.widgets-icon').css('margin-bottom','50px');
        $('#comments_anchor').parent('#comments-container').parent('.widgets-icon').addClass('container');
    });
JS;
$this->registerJs($jsPezza, View::POS_READY);
?>

<div id="comments_anchor">
    <div class="widget-graphic-cms-bi-less widget-chat">
        <div class="box-new-widget">
            <div class="page-header header-new-widget">
                <div class="title-new-widget">
                    <?= AmosComments::t('amoscomments', 'Chat') ?>
                    <?php if ($urlUpdateMessage) { ?>
                        <a title="Aggiorna chat" href="<?= $urlUpdateMessage ?>"><span class="mdi mdi-refresh"></span></a>
                    <?php } ?>
                </div>

                <div class="form-check form-switch" data-placement="left" data-toggle="tooltip"
                     title="Ricevi notifiche email dei messaggi in arrivo da questa chat">
                    <input class="form-check-input" type="checkbox" role="switch"
                           aria-label="<?= AmosComments::t('amoscomments', 'Ricevi notifiche email dei messaggi in arrivo da questa chat') ?>"
                           id="toggleNotification" <?= $checked ?>>
                    <!-- < ?php
                    if (in_array($widget->model->className(), AmosComments::instance()->bellNotificationEnabledClasses)) :
                    ?>
                        <div class="subtitle-text-container">
                            < ?php
                            if ($notificationUserStatus) :
                                $checked = 'checked';
                                $icon = '<span class="am am-notifications-add m-r-5" style="font-size: 24px;"></span>';
                            else :
                                $checked = '';
                                $icon = '<svg class="m-r-5" style="width:24px;height:24px" viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                          d="M17.5 13A4.5 4.5 0 0 0 13 17.5A4.5 4.5 0 0 0 17.5 22A4.5 4.5 0 0 0 22 17.5A4.5 4.5 0 0 0 17.5 13M17.5 14.5A3 3 0 0 1 20.5 17.5A3 3 0 0 1 20.08 19L16 14.92A3 3 0 0 1 17.5 14.5M14.92 16L19 20.08A3 3 0 0 1 17.5 20.5A3 3 0 0 1 14.5 17.5A3 3 0 0 1 14.92 16M12 2C10.9 2 10 2.9 10 4C10 4.1 10 4.19 10 4.29C7.12 5.14 5 7.82 5 11V17L3 19V20H11.5A6.5 6.5 0 0 1 11 17.5A6.5 6.5 0 0 1 17.5 11A6.5 6.5 0 0 1 19 11.18V11C19 7.82 16.88 5.14 14 4.29C14 4.19 14 4.1 14 4C14 2.9 13.11 2 12 2M10 21C10 22.11 10.9 23 12 23C12.5 23 12.97 22.81 13.33 22.5A6.5 6.5 0 0 1 12.03 21Z"/>
                                </svg>';
                            endif;
                            ? >

                        </div>
                    < ?php
                    endif;
                    ? > -->
                    <div id="iconNotification"><?= $icon ?></div>
                    <label class="form-check-label"
                           for="toggleNotification"><?= AmosComments::t('amoscomments', 'Ricevi notifiche chat') ?></label>
                </div>
            </div>
        </div>
    </div>

    <div class="comment-chat-content">
        <?php if (!empty($comments)) { ?>
            <?php
            foreach (array_reverse($comments) as $comment) :
                /*echo ContextMenuWidget::widget([
                    'model' => $comment,
                    'actionModify' => "/comments/comment/update?id=" . $comment->id,
                    'actionDelete' => "/comments/comment/delete?id=" . $comment->id,
                    'labelDeleteConfirm' => \open20\amos\core\module\BaseAmosModule::t('community', 'Sei sicuro di voler cancellare questo link?')
                ]);*/
                if (empty($contextObject)) {
                    $classContext = $comment->context;
                    $contextObject = $classContext::findOne($comment->context_id);
                }
                ?>
                <?php
                $createdUserProfile = $comment->createdUserProfile;
                /** @var \open20\amos\comments\models\Comment $comment */
                ?>
                <div class="content-message-chat" id="comment-<?= $comment->id ?>" tabindex="0">
                    <div class="media">

                        <?php if (!empty($createdUserProfile)) { ?>
                            <div class="media-left">
                                <!-- Immagine -->
                                <div>
                                    <?= UserCardWidget::widget(['model' => $createdUserProfile, 'enableLink' => false, 'avatarXS' => true]) ?>
                                </div>

                            </div>
                        <?php } ?>

                        <div class="media-body">
                            <div class="header-message-chat">
                                <div class="name-author">
                                    <small>
                                        <?php if (!empty($createdUserProfile)) : ?>
                                            <?php if (isset(\Yii::$app->params['disableLinkContentCreator']) && (\Yii::$app->params['disableLinkContentCreator'] === true)) : ?>
                                                <?= $createdUserProfile; ?>
                                            <?php else : ?>
                                                <?=
                                                (!empty($createdUserProfile) ?
                                                    Html::a(
                                                        $createdUserProfile,
                                                        [
                                                            (\Yii::$app->user->isGuest ? '#' : '/' . AmosAdmin::getModuleName() . '/user-profile/view'),
                                                            'id' => $createdUserProfile->id
                                                        ]
                                                    ) : '#### ####')
                                                ?>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <?= '#### ####'; ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="date-message-chat">
                                    <?php if (Yii::$app->getFormatter()->asDate($comment->created_at, 'short') === date('d/m/y')) { ?>
                                        <small class="date-message"> <?= Yii::$app->getFormatter()->asTime($comment->created_at, 'short') ?></small>
                                    <?php } else { ?>
                                        <small class="date-message"> <?= Yii::$app->getFormatter()->asDateTime($comment->created_at, 'short') ?></small>
                                    <?php } ?>
                                    <?php if ($comment->msgraph_channel_message_id) { ?>
                                        <span class="mdi mdi-microsoft-teams icon-ms-teams color-ms-teams mdi-18px"></span>
                                    <?php } ?>
                                </div>
                                <div class="content-action-date">
                                    <div class="action-message-chat">
                                        <?php
                                        $isEnabledTeams = false;
                                        if ($moduleCommunity && property_exists($moduleCommunity, 'enableMicrosoftTeams') && $moduleCommunity->enableMicrosoftTeams) {
                                            if ($comment->context == 'open20\amos\community\models\Community') {
                                                $community = \open20\amos\community\models\Community::findOne($comment->context_id);
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
                                        if (!$isEnabledTeams) {
                                            if (Yii::$app->getUser()->can('COMMENT_DELETE', ['model' => $comment, 'user_id' => $comment->created_by])) { ?>
                                                <button id="deleteComment-<?= $comment->id ?>"
                                                        class="btn btn-xs btn-danger deleteComment bi bi-bucket">
                                                    <span class="am am-delete"></span>
                                                </button>
                                            <?php }
                                            if (Yii::$app->getUser()->can('COMMENT_UPDATE', ['model' => $comment, 'user_id' => $comment->created_by])) { ?>
                                                <button id="updateComment-<?= $comment->id ?>"
                                                        class="btn btn-xs btn-secondary updateComment bi bi-gea">
                                                    <span class="am am-edit"></span>
                                                </button>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>

                                </div>
                            </div>
                            <?php

                            $actionTeams = '';
                            $classAnswer = 'answer_text';
                            $isTeamMember = false;
                            $isAccountTeamsConnected = false;
                            if ($isEnabledTeams) {
                                $tokenTeams = \open20\amos\community\models\CommunityMsgraphTokens::find()->andWhere(['user_id' => \Yii::$app->user->id])->one();
                                $member = \open20\amos\community\models\CommunityUserMm::find()->andWhere(['user_id' => \Yii::$app->user->id, 'community_id' => $comment->context_id])->one();
                                if ($tokenTeams->msgraph_user_id) {
                                    $isAccountTeamsConnected = true;
                                }
                                if ($member && $member->msgraph_membership_id) {
                                    $isTeamMember = true;
                                }
                                if ($comment->msgraph_channel_message_id) {
                                    if ($comment->from_ms_teams) {
                                        if (!$isAccountTeamsConnected) {
                                            $actionTeams = Html::a(AmosComments::t('amoscomments', 'Collega il tuo account Microsoft Teams per visualizzarlo') . '<span class="mdi mdi-arrow-right-circle-outline"></span>', ['/community/ms-teams/choose-mail-microsoft', 'urlRedirect' => Url::current()], ['title' => AmosComments::t('amoscomments', 'Collega il tuo account Microsoft Teams per visualizzarlo'), 'class' => 'link-readmore']);
                                        } else if ($isTeamMember == false) {
                                            $actionTeams = Html::a(AmosComments::t('amoscomments', 'Iscriviti al team') . '<span class="mdi mdi-arrow-right-circle-outline"></span>', ['/community/ms-teams/subscribe-to-team', 'community_id' => $comment->context_id, 'urlRedirect' => Url::current()], ['title' => AmosComments::t('amoscomments', 'Iscriviti al team'), 'class' => 'link-readmore']);
                                        } else {
                                            $actionTeams = Html::a(AmosComments::t('amoscomments', 'APRI') . '<span class="mdi mdi-arrow-right-circle-outline"></span>', $comment->msgraph_channel_message_weburl, ['title' => AmosComments::t('amoscomments', 'Apri'), 'target' => '_blank', 'class' => 'link-readmore']);
                                        }
                                    }
                                    $classAnswer = 'answer_teams';
                                }
                            }
                            ?>
                            <div class="<?= $classAnswer ?>"><?= Yii::$app->getFormatter()->asRaw($comment->comment_text) ?><?= $actionTeams ?></div>

                        </div>
                    </div>


                </div>
            <?php endforeach; ?>
        <?php } ?>
    </div>
    <div id="comments-loader" class="text-center hidden">
        <?=
        Html::img($asset->baseUrl . "/img/inf-circle-loader.gif", ['alt' => AmosComments::t('amoscomments', 'Loading')])
        ?>
    </div>
</div>