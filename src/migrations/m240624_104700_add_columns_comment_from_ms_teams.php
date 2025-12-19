<?php



use open20\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

/**
 * Class m230712_155300_add_widget_icon_bookmarks_permissions */
class m240624_104700_add_columns_comment_from_ms_teams extends \yii\db\Migration
{
    const COMMENT = 'comment';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            self::COMMENT,
            'from_ms_teams',
            $this->integer(1)->defaultValue(0)->after('msgraph_channel_message_weburl')->comment('Ms channel message web url')
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
            'from_ms_teams'
        );

        return true;
    }
}