<?php



use open20\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

/**
 * Class m230712_155300_add_widget_icon_bookmarks_permissions */
class m240613_103700_add_columns_comment_ms_message extends \yii\db\Migration
{
    const COMMENT = 'comment';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            self::COMMENT,
            'msgraph_channel_message_weburl',
            $this->text()->defaultValue(null)->after('type')->comment('Ms channel message web url')
        );
        $this->addColumn(
            self::COMMENT,
            'msgraph_channel_message_id',
            $this->text()->defaultValue(null)->after('type')->comment('Ms channel message  id')
        );


        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(
            self::COMMENT,
            'msgraph_channel_message_weburl'
        );

        $this->dropColumn(
            self::COMMENT,
            'msgraph_channel_message_id'
        );
        return true;
    }
}