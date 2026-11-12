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
use open20\amos\comments\models\Comment;
use open20\amos\comments\models\CommentBlacklist;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\views\DataProviderView;
use kartik\grid\GridView;
use yii\helpers\Html;

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
            'comment_text' => [
                'attribute' => 'comment_text',
                'label' => AmosComments::t('amoscomments', 'Commento'),
                'format' => 'html',
                'value' => function ($model) {
                    /** @var Comment $model */
                    $commentText = $model->comment_text;
                    if (strlen($commentText) > 50) {
                        $commentText = substr($commentText, 0, 47) . '...';
                    }
                    return $commentText;
                }
            ],
//                'created_at:datetime',
//                'created_by' => [
//                    'attribute' => 'created_by',
//                    'value' => function ($model) {
//                        /** @var Comment $model */
//                        return $model->createdUserProfile->nomeCognome;
//                    }
//                ],
            [
                'class' => 'kartik\grid\ExpandRowColumn',
                'value' => function ($model, $key, $index, $column) {
                    return GridView::ROW_COLLAPSED;
                },
                'detailUrl' => \yii\helpers\Url::to(['/comments/comment-dashboard/to-validate-comment-replies']),
            ],
        ],
    ],
]); ?>