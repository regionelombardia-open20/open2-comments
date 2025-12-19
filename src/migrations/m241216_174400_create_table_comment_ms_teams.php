<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\migrations
 * @category   CategoryName
 */


use open20\amos\core\migration\AmosMigrationTableCreation;

class m241216_174400_create_table_comment_ms_teams extends AmosMigrationTableCreation
{
    /**
     * @inheritdoc
     */
    protected function setTableName()
    {
        $this->tableName = '{{%comment_ms_teams}}';
    }

    /**
     * @inheritdoc
     */
    protected function setTableFields()
    {
        $this->tableFields = [
            'id' => $this->primaryKey(),
            'comment_id' => $this->integer()->notNull()->comment('Comment id'),
            'msgraph_channel_message_id' => $this->string(256)->unique()->comment('Commen channel teams id'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function beforeTableCreation()
    {
        parent::beforeTableCreation();
        $this->setAddCreatedUpdatedFields(true);
    }

    /**
     * @inheritdoc
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey('fk_comment_ms_teams_comment_id', $this->tableName, 'comment_id', '{{%comment}}', 'id');
    }
}
