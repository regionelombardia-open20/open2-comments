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

/**
 * @var yii\web\View $this
 * @var lispa\amos\comments\models\CommentReply $model
 */

$this->title = AmosComments::t('amoscomments', 'Update');
$this->params['breadcrumbs'][] = ['label' => AmosComments::t('amoscomments', 'Comments Replies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-reply-update">
    <?= $this->render('_form', [
        'model' => $model,
        'fid' => NULL,
        'dataField' => NULL,
        'dataEntity' => NULL,
    ]) ?>
</div>
