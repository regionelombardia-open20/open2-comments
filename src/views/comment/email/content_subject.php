<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\views\comment\email
 * @category   CategoryName
 */

use lispa\amos\comments\AmosComments;
use lispa\amos\core\interfaces\BaseContentModelInterface;
use lispa\amos\core\interfaces\ModelLabelsInterface;

/**
 * @var \lispa\amos\core\record\Record $contextModel
 */

$title = $contextModel->__toString();
if (($contextModel instanceof BaseContentModelInterface) || $contextModel->hasMethod('getTitle')) {
    $title = $contextModel->getTitle();
}

$label = '-';
if (($contextModel instanceof ModelLabelsInterface) || $contextModel->hasMethod('getGrammar')) {
    $label = $contextModel->getGrammar()->getModelSingularLabel();
}

?>

<?= AmosComments::t('amoscomments', '#notification_email_subject', [$label, $title]); ?>
