<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments
 * @category   CategoryName
 */

namespace open20\amos\comments;

use open20\amos\comments\components\CommentComponent;
use open20\amos\comments\models\Comment;
use open20\amos\comments\models\CommentReply;
use open20\amos\core\components\AmosView;
use open20\amos\core\module\AmosModule;
use open20\amos\core\module\ModuleInterface;
use open20\amos\core\record\Record;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

/**
 * Class AmosComments
 * @package open20\amos\comments
 */
class AmosComments extends AmosModule implements ModuleInterface, BootstrapInterface
{
    public static $CONFIG_FOLDER = 'config';

    /**
     * @var string|boolean the layout that should be applied for views within this module. This refers to a view name
     * relative to [[layoutPath]]. If this is not set, it means the layout value of the [[module|parent module]]
     * will be taken. If this is false, layout will be disabled within this module.
     */
    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'open20\amos\comments\controllers';
    public $newFileMode         = 0666;
    public $name                = 'Comments';
    public $layoutInverted      = false;

    /**
     * @var array $modelsEnabled
     */
    public $modelsEnabled           = [];
    public $modelsConfiguration     = [];
    public $maxCommentAttachments   = 5;
    public $enableMailsNotification = true;

    /**
     * @var bool $enableUserSendMailCheckbox If true enable the checkbox in the comments forms with the user can select if send or not the notify mail.
     */
    public $enableUserSendMailCheckbox = true;

    /**
     * This is the html used to render the subject of the e-mail. In the view is available the variable $profile
     * that is an instance of 'open20\amos\admin\models\UserProfile'
     * @var string
     */
    public $htmlMailContentSubject = '@vendor/open20/amos-comments/src/views/comment/email/content_subject';
    //    public $htmlMailContentTitle = [
//        'open20\amos\news\models\News' => '@vendor/open20/amos-comments/src/views/comment/email/content_subject_news',
//        'open20\amos\discussioni\models\DiscussioniTopic' => '@vendor/open20/amos-comments/src/views/comment/email/content_subject_discussioni',
//        'open20\amos\documenti\models\Documenti' => '@vendor/open20/amos-comments/src/views/comment/email/content_subjcet_documenti'
//    ];

    public $htmlMailContentSubjectDefault = '@vendor/open20/amos-comments/src/views/comment/email/content_subject';

    /**
     * This is the html used to render the subject of the e-mail. In the view is available the variable $profile
     * that is an instance of 'open20\amos\admin\models\UserProfile'
     * @var string
     */
    public $htmlMailContentTitle = '@vendor/open20/amos-comments/src/views/comment/email/content_title';
    //    public $htmlMailContentTitle = [
//        'open20\amos\news\models\News' => '@vendor/open20/amos-comments/src/views/comment/email/content_title_news',
//        'open20\amos\discussioni\models\DiscussioniTopic' => '@vendor/open20/amos-comments/src/views/comment/email/content_title_discussioni',
//        'open20\amos\documenti\models\Documenti' => '@vendor/open20/amos-comments/src/views/comment/email/content_title_documenti'
//    ];

    /*
     * 
     */
    public $htmlMailContentTitleDefault = '@vendor/open20/amos-comments/src/views/comment/email/content_title';

    /**
     * This is the html used to render the message of the e-mail. In the view is available the variable $profile
     * that is an instance of 'open20\amos\admin\models\UserProfile'
     * @var string|array
     */
    public $htmlMailContent = '@vendor/open20/amos-comments/src/views/comment/email/content';
//    public $htmlMailContent = [
//        'open20\amos\news\models\News' => '@vendor/open20/amos-comments/src/views/comment/email/content_news',
//        'open20\amos\discussioni\models\DiscussioniTopic' => '@vendor/open20/amos-comments/src/views/comment/email/content_discussioni',
//        'open20\amos\documenti\models\Documenti' => '@vendor/open20/amos-comments/src/views/comment/email/content_documenti'
//    ];

    /*
     * 
     */
    public $htmlMailContentDefault = '@vendor/open20/amos-comments/src/views/comment/email/content';

    /**
     * Sets if the notify checkbox must be visible into the comments accordion
     * @var bool
     */
    public $displayNotifyCheckbox = true;

    /**
     * Sets if the comments accordion must be opened by default
     * @var bool
     */
    public $accordionOpenedByDefault = true;

    /**
     * If true it enable the comments olny with the scope (in the community)
     * @var boolean $enableCommentOnlyWithScope
     */
    public $enableCommentOnlyWithScope = false;
    public $disableAutoDisplay         = ['amos\planner\models\PlanWork'];

    /**
     * If a true notify the context model (DiscussioniTopic) if is created a comment
     * @var bool $enableNotifyCommentForDiscussions
     */
    public $enableNotifyCommentForDiscussions = true;

    /**
     *
     * @var type 3 by SORT_DESC, 4 by SORT_ASC
     */
    public $orderDisplayComments = 3;

    /**
     *
     * @var type
     */
    public $disablePagination     = false;

    /**
     *
     * @var bool $enableModerator
     */
    public $enableModerator     = false;

    /**
     * @var bool
     */
    public $enableAttachmentInComment = true;


    private static $registerEvent = false;

    /**
     *
     * @var type
     */
    public $enableCanDoIt = true;

    /**
     *  Example:
     * [
     *  'open20\amos\documenti\models\Documenti',
     *  'open20\amos\news\models\News',
     *  'open20\amos\discussioni\models\Discussioni',
     * ]
     *
     * @var boolean
     */
    public $bellNotificationEnabledClasses = [
        'open20\amos\community\models\Community'
    ];
    
    /**
     * @var string List of the allowed extensions for the upload of files.
     */
    public $whiteListFilesExtensions = ['csv', 'doc', 'docx', 'pdf', 'rtf', 'txt', 'xls', 'xlsx', 'odt'];

    /**
     * @var string List of the allowed mime types.
     */
    public $mimeTypes = '';

    /**
     * Option to disable the render of 'banner-cta' box for guest in CommentsWidget.
     * Enabled by default.
     * @var bool
     */
    public $disableBannerCtaForGuest = false;

    /**
     * @return string
     */
    public static function getModuleName()
    {
        return 'comments';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        \Yii::setAlias('@open20/amos/'.static::getModuleName().'/controllers', __DIR__.'/controllers/');
        // custom initialization code goes here
        \Yii::configure($this, require(__DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'));
    }

    /**
     * @inheritdoc
     */
    public function getWidgetGraphics()
    {
        return NULL;
    }

    /**
     * @inheritdoc
     */
    public function getWidgetIcons()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultModels()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        if (self::$registerEvent == false) {
            self::$registerEvent = true;
            Event::on(AmosView::className(), AmosView::AFTER_RENDER_CONTENT, [new CommentComponent(), 'showComments']);
        }

        if (!empty($this->bellNotificationEnabledClasses) && is_array($this->bellNotificationEnabledClasses)){
            foreach ($this->bellNotificationEnabledClasses as $modelClassName){
                Event::on($modelClassName, Record::EVENT_AFTER_INSERT, [new CommentComponent(), 'addCreatorEnableNotificationUser']);
            }
        }

    }

    /**
     * @param $model
     */
    public function countComments($model)
    {
        $query = Comment::find()
            ->joinWith('commentReplies', true, 'LEFT JOIN')
            ->andWhere(['context' => $model->className(), 'context_id' => $model->id])
            ->groupBy('comment.id');

        /** @var \open20\amos\comments\models\Comment $lastComment */
        $countComment = $query->count();
        $query        = Comment::find()
            ->joinWith('commentReplies', true, 'LEFT JOIN')
            ->andWhere(['context' => $model->className(), 'context_id' => $model->id])
            ->andWhere(['is not', CommentReply::tableName().'.id', null]);
        $countComment += $query->count();
        return $countComment;
    }

    /**
     * Configurazione per-modulo di varie opzioni
     * @param $classname
     * @param $action
     * @return bool|mixed
     */
    public function modelCanDoIt($classname, $action) {
        if(empty($classname) || empty($action)) {
            return false;
        }

        if(array_key_exists($classname, $this->modelsConfiguration)) {
            $config = $this->modelsConfiguration[$classname];

            if(array_key_exists($action, $config)) {
                return $config[$action];
            }
        }

        return true;
    }
}