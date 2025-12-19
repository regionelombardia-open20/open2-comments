<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    svilupposostenibile\enti
 * @category   CategoryName
 */

use open20\amos\comments\models\Comment;
use yii\db\Migration;


class m240320_154700_add_column_comment_category extends Migration
{
    const TABLE = '{{%comment}}';

    public function safeUp()
    {
        $this->addColumn(self::TABLE, 'type',
            $this->integer()->defaultValue(1)->after('in_evidence')
        );
    }

    public function safeDown()
    {
        $this->dropColumn(self::TABLE, 'type');
    }

}