<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\models
 * @category   CategoryName
 */

namespace lispa\amos\comments\models;

/**
 * Interface CommentInterface
 * @package lispa\amos\comments\models
 */
interface CommentInterface
{
    /**
     * In this method must be defined the conditions that say if the model is commentable and then return true or false.
     * @return bool
     */
    public function isCommentable();
}
