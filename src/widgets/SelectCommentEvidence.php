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

namespace open20\amos\comments\widgets;

use yii\base\Widget;

class SelectCommentEvidence extends Widget
{
    public $asset;
    public $model;
    public $module;

    public function run()
    {
       return $this->render('select_comment_evidence',[
           'comment' => $this->model,
           'currentAsset' => $this->asset,
           'module' => $this->module,
       ]);
    }

    /**
     * @param $currentAsset
     * @return void
     */
    public static function registerJsWidget($currentAsset){
        \Yii::$app->controller->view->registerJsVar('labelAdd',"Metti in evidenza");
        \Yii::$app->controller->view->registerJsVar('labelRemove',"Rimuovi da evidenza");
        $pinIconUrl = $currentAsset->baseUrl ."/sprite/material-sprite.svg#ic_pin-outline";
        $pinOffIconUrl = $currentAsset->baseUrl ."/sprite/material-sprite.svg#ic_pin";
        $js = <<<JS
    $(document).on('click','.add-remove-pin',function(e){
        e.preventDefault();
        var commentId = $(this).attr('data-key');
        $.ajax({
               url: '/comments/comment/add-remove-evidence',
               type: 'get',
               data: {
                         id: commentId
                     },
               success: function (data) {
                   var linkComment = $('a[data-key="'+commentId+'"]');
                    if(data['action'] == 'selected'){
                            $(linkComment).attr('title', labelRemove);
                            $(linkComment).attr('data-original-title', labelRemove).tooltip('hide').tooltip('show');
                            $(linkComment).parents('.media').addClass('added-to-evidence');
                            $(linkComment).find('svg use').attr('xlink:href', '$pinOffIconUrl');
                            
                    }else{
                            $(linkComment).attr('title', labelAdd);
                            $(linkComment).attr('data-original-title', labelAdd).tooltip('hide').tooltip('show');
                            $(linkComment).parents('.media').removeClass('added-to-evidence');
                            $(linkComment).find('svg use').attr('xlink:href', '$pinIconUrl');
                    }               
               }
        });
    })
JS;
        \Yii::$app->controller->view->registerJs($js);
        \Yii::$app->controller->view->registerCss(".added-to-evidence {
background-color: #eefaf6; padding-left:8px; padding-top:8px
}");
    }

}