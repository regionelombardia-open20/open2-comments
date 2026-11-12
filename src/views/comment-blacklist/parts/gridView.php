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
use open20\amos\comments\models\CommentBlacklist;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\views\DataProviderView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var string $currentView
 */
?>

<?= DataProviderView::widget([
    'dataProvider' => $dataProvider,
    'currentView' => $currentView,
    'gridView' => [
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'word',
//                'created_at:datetime',
//                'created_by',
//                'updated_at:datetime',
//                'updated_by',
            [
                'class' => 'open20\amos\core\views\grid\ActionColumn',
                'template' => '{update}{delete}',
                'buttons' => [
                    'update' => function ($url, $model) {
                        /** @var CommentBlacklist $model */
                        return \yii\helpers\Html::a(
                            AmosIcons::show('edit'),
                            ['/comments/comment-blacklist/update', 'id' => $model->id],
                            [
                                'title' => \Yii::t('amoscore', 'Update'),
                                'class' => 'btn btn-tool-secondary'
                            ]
                        );
                    },
                    'delete' => function ($url, $model) {
                        /** @var CommentBlacklist $model */
                        return \yii\helpers\Html::a(
                            AmosIcons::show('delete'),
                            ['/comments/comment-blacklist/delete', 'id' => $model->id],
                            [
                                'title' => AmosComments::t('amoscomments', 'Cancella'),
                                'class' => 'btn btn-danger-inverse',
                                'data-confirm' => AmosComments::t('amoscomments', 'Sei sicuro di voler cancellare questo termine?'),
                                'data-method' => 'post'
                            ]
                        );
                    }
                ]
            ],
        ],
    ],
]); ?>