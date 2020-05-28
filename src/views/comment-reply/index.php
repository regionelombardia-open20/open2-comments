<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\views\comment-reply
 * @category   CategoryName
 */

use lispa\amos\comments\AmosComments;
use lispa\amos\core\views\DataProviderView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var lispa\amos\comments\models\search\CommentReplySearch $model
 * @var string $currentView
 */

$this->title = AmosComments::t('amoscomments', 'Comments Replies');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-reply-index">
    <?php echo $this->render('_search', ['model' => $model]); ?>

    <p>
        <?php /* echo         Html::a('New Event Type'        , ['create'], ['class' => 'btn btn-amministration-primary'])*/ ?>
    </p>
    
    <?php echo DataProviderView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $model,
        'currentView' => $currentView,
        'gridView' => [
            'columns' => [
                'comment_text',
                [
                    'class' => 'lispa\amos\core\views\grid\ActionColumn',
                ]
            ]
        ]
    ]); ?>
</div>
