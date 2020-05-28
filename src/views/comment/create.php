<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\views\comment
 * @category   CategoryName
 */

use lispa\amos\comments\AmosComments;

/**
 * @var yii\web\View $this
 * @var lispa\amos\comments\models\Comment $model
 */

$this->title = AmosComments::t('amoscomments', 'Create');
$this->params['breadcrumbs'][] = ['label' => AmosComments::t('amoscomments', 'Comments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-create">
    <?= $this->render('_form', [
        'model' => $model,
        'fid' => NULL,
        'dataField' => NULL,
        'dataEntity' => NULL,
    ]) ?>
</div>
