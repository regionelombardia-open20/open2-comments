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

use open20\amos\comments\AmosComments;
use yii\web\View;
use open20\amos\comments\assets\CommentsDesignAsset;
use open20\design\components\bootstrapitalia\Input; 

CommentsDesignAsset::register($this);

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
    
    $textArea = Input::widget([
            'name' => 'contribute-area',
            'type' => 'textarea',
            'value' => null,
            'options' => [
                'id' => 'contribute-area',
                'class' => 'form-control'
            ],
    ]);
    ?>
    <div class="media mt-5" id="bk-contribute">
        <div class="media-body">
            <div class="form-group mb-2">
                <?=
                $textArea
                ?>
            </div>
            <div class="d-flex">
                <button id="contribute-btn" type="button" class="btn btn-outline-secondary btn-xs">
                    <?= AmosComments::t('amoscomments', 'Aggiungi commento') ?>
                </button>
            </div>

        </div>

    </div>
<?php } ?>