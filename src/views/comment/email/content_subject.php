<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\views\comment\email
 * @category   CategoryName
 */

use open20\amos\comments\AmosComments;
use open20\amos\core\interfaces\BaseContentModelInterface;
use open20\amos\core\interfaces\ModelLabelsInterface;

/**
 * @var \open20\amos\core\record\Record $contextModel
 */

$title = $contextModel->__toString();
if (($contextModel instanceof BaseContentModelInterface) || $contextModel->hasMethod('getTitle')) {
    $prefix = '';
    if ($contextModel->hasMethod('getSiglaPiattaforma')) {
        $prefix = $contextModel->getSiglaPiattaforma()." | ";
    }
    $title = $prefix . $contextModel->getTitle();
}

$label = '-';

if (($contextModel instanceof ModelLabelsInterface) || $contextModel->hasMethod('getGrammar')) {
    $label = $contextModel->getGrammar()->getModelSingularLabel();
}

?>

<?= AmosComments::t('amoscomments', '#notification_email_subject', [$label, $title]); ?>
