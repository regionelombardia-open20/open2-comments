<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments
 * @category   CategoryName
 */

namespace lispa\amos\comments;

use lispa\amos\comments\components\CommentComponent;
use lispa\amos\comments\models\Comment;
use lispa\amos\comments\models\CommentReply;
use lispa\amos\core\components\AmosView;
use lispa\amos\core\module\AmosModule;
use lispa\amos\core\module\ModuleInterface;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * Class AmosComments
 * @package lispa\amos\comments
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
    public $controllerNamespace = 'lispa\amos\comments\controllers';
    
    public $newFileMode = 0666;
    
    public $name = 'Comments';
    
    /**
     * @var array $modelsEnabled
     */
    public $modelsEnabled = [];
    
    public $maxCommentAttachments = 5;

    public $enableMailsNotification = true;

    /**
     * @var bool $enableUserSendMailCheckbox If true enable the checkbox in the comments forms with the user can select if send or not the notify mail.
     */
    public $enableUserSendMailCheckbox = true;

    /**
     * This is the html used to render the subject of the e-mail. In the view is available the variable $profile
     * that is an instance of 'lispa\amos\admin\models\UserProfile'
     * @var string
     */
    public $htmlMailContentTitle = '@vendor/lispa/amos-comments/src/views/comment/email/content_title';

    /**
     * This is the html used to render the message of the e-mail. In the view is available the variable $profile
     * that is an instance of 'lispa\amos\admin\models\UserProfile'
     * @var string|array
     */
    public $htmlMailContent = '@vendor/lispa/amos-comments/src/views/comment/email/content';
//    public $htmlMailContent = [
//        'lispa\amos\news\models\News' => '@vendor/lispa/amos-comments/src/views/comment/email/content_news',
//        'lispa\amos\discussioni\models\DiscussioniTopic' => '@vendor/lispa/amos-comments/src/views/comment/email/content_discussioni',
//        'lispa\amos\documenti\models\Documenti' => '@vendor/lispa/amos-comments/src/views/comment/email/content_documenti'
//    ];

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
        \Yii::setAlias('@lispa/amos/' . static::getModuleName() . '/controllers', __DIR__ . '/controllers/');
        // custom initialization code goes here
        \Yii::configure($this, require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php'));
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
        Event::on(AmosView::className(), AmosView::AFTER_RENDER_CONTENT, [new CommentComponent(), 'showComments']);
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
        
        /** @var \lispa\amos\comments\models\Comment $lastComment */
        $countComment = $query->count();
        $query = Comment::find()
            ->joinWith('commentReplies', true, 'LEFT JOIN')
            ->andWhere(['context' => $model->className(), 'context_id' => $model->id])
            ->andWhere(['is not', CommentReply::tableName() . '.id', null]);
        $countComment += $query->count();
        return $countComment;
    }
}
