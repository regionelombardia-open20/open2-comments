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
                'class' => 'open20\amos\core\views\grid\ActionColumn',
                'template' => '{view}{validate}{reject}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        /** @var Comment $model */
                        $contextModel = $model->contextModel;
                        $viewUrl = $contextModel->getFullViewUrl();
                        $anchor = 'comment-' . $model->id;
                        /** @var AmosComments $moduleComments */
                        $moduleComments = \Yii::$app->getModule(AmosComments::getModuleName());
                        if (!$moduleComments->disablePagination) {
                            // Get page and page size
                            $pageSize = $moduleComments->paginationPageSize;
                            $commentsCount = Comment::find()
                                ->andWhere(['context' => $model->context])
                                ->andWhere(['context_id' => $model->context_id])
                                ->count();
                            $commentPosition = Comment::find()
                                ->andWhere(['context' => $model->context])
                                ->andWhere(['context_id' => $model->context_id])
                                ->andWhere(['<=', 'created_at', $model->created_at])
                                ->count();
                            $page = ceil(($commentsCount - $commentPosition + 1) / $pageSize);
                            if ($page > 1) {
                                $viewUrl .= '&page=' . $page . '&per-page=' . $pageSize;
                            }
                        }

                        return Html::a(
                            '<span class="mdi mdi-eye"></span>',
                            [$viewUrl, '#' => $anchor],
                            [
                                'title' => AmosComments::t('amoscomments', 'Visualizza commento in una nuova scheda'),
                                'class' => 'btn btn-tool-secondary',
                                'target' => '_blank',
                            ]
                        );
                    },
                    'validate' => function ($url, $model) {
                        /** @var Comment $model */
                        return Html::a(
                            '<span class="mdi mdi-check-circle"></span>',
                            ['/comments/comment-dashboard/validate-comment',
                                'id' => $model->id,
                                'redirectUrl' => '/comments/comment-dashboard/moderation'
                            ],
                            [
                                'title' => AmosComments::t('amoscomments', 'Valida commento'),
                                'class' => 'btn btn-success',
//                                'data-confirm' => AmosComments::t('amoscore', 'Sei sicuro di voler cancellare questo elemento?'),
                            ]
                        );
                    },
                    'reject' => function ($url, $model) {
                        /** @var Comment $model */
                        return Html::a(
                            '<span class="mdi mdi-close-circle"></span>',
                            ['/comments/comment-dashboard/reject-comment',
                                'id' => $model->id,
                                'redirectUrl' => '/comments/comment-dashboard/moderation'
                            ],
                            [
                                'title' => AmosComments::t('amoscomments', 'Rifiuta commento'),
                                'class' => 'btn btn-danger',
                                'data-confirm' => AmosComments::t('amoscomments', 'Sei sicuro di voler rifiutare questo commento?'),
                            ]
                        );
                    },
                ]
            ],
        ],
    ],
]); ?>