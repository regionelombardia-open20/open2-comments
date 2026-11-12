<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos-comments
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

use open20\amos\core\forms\TextEditorWidget;
use amos\planner\components\bootstrapitalia\Html;
use open20\amos\comments\models\Comment;
use open20\amos\comments\AmosComments;
use yii\web\View;
use open20\amos\core\utilities\CurrentUser;

$js = "
$('#contribute-btn').on('click', function (event) {
    if (typeof tinymce != 'undefined') {
        tinymce.triggerSave();
    }  
    Comments.saveComment(".$widget->model->id.", '".addslashes($widget->model->className())."', '".\Yii::$app->request->csrfParam."', '".\Yii::$app->request->csrfToken."')
});
";
$this->registerJs($js, View::POS_READY);

$class= $widget->model->className();

/** @var AmosComments $commentsModule */
$commentsModule = Yii::$app->getModule(AmosComments::getModuleName());

if (Yii::$app->getUser()->can('COMMENT_CREATE', ['model' => $widget->model])) {
    $userProfile           = CurrentUser::getUserProfile();
    $userImage             = $userProfile->getAvatarUrl('square_small');
    $userNomeCognome       = $userProfile->getNomeCognome();
    $displayNotifyCheckBox = true;

    if (isset($commentsModule->displayNotifyCheckbox)) {
        if (is_bool($commentsModule->displayNotifyCheckbox)) {
            $displayNotifyCheckBox = $commentsModule->displayNotifyCheckbox;
        }
    }

    $displayNotifyCheckBox = $displayNotifyCheckBox&& $commentsModule->modelCanDoIt($class, 'enableUserSendMailCheckbox');

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
        \yii\helpers\Html::textArea('contribute-area', "", ['id' => 'contribute-area']);
    ?>


    <div class="media mt-5" id="bk-contribute">
        <!--        <div class="avatar size-sm mr-2">
                    <img src="< ?= $userImage ?>" alt="< ?= $userNomeCognome ?>">
                </div>-->
        <div class="media-body">
            <div class="form-group mb-2">
                <?=
                $redactorComment
                ?>
            </div>

            <div class="d-flex">
                <button id="contribute-btn" type="button" class="btn btn-outline-secondary btn-xs">
                    <?= AmosComments::t('amoscomments', 'Aggiungi commento') ?>
                </button>
            </div>

        </div>

    </div>
<?php } else {
    ?>
    <div class="media mt-5" id="bk-contribute">
        <!--        <div class="avatar size-sm mr-2">
                    <img src="< ?= $userImage ?>" alt="< ?= $userNomeCognome ?>">
                </div>-->
        <div class="media-body">

            <div class="d-flex">
                <?php if (!empty($widget->urlRegistrazione)) { ?>
                    <a href="<?= $widget->urlRegistrazione ?>" id="register-contribute-btn" type="button" class="btn btn-outline-secondary btn-xs">
                        <?= AmosComments::t('amoscomments', 'Registrati e commenta') ?>
                    </a>                
                <?php } else { ?>
                    <button id="register-contribute-btn" type="button" class="btn btn-outline-secondary btn-xs">
                        <?= AmosComments::t('amoscomments', 'Registrati e commenta') ?>
                    </button>
                <?php } ?>
            </div>

        </div>

    </div>
    <?php
}
?>