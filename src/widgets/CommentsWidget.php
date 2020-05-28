<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\comments\widgets
 * @category   CategoryName
 */

namespace lispa\amos\comments\widgets;

use lispa\amos\comments\AmosComments;
use lispa\amos\comments\models\Comment;
use yii\base\Widget;
use yii\data\Pagination;

/**
 * Class CommentsWidget
 *
 * Widget to show the comments for a content.
 *
 * @package lispa\amos\comments\widgets
 */
class CommentsWidget extends Widget
{
    public $layout = '<div id="comments-container">{commentSection}{commentsSection}</div>';
    
    /**
     * @var \lispa\amos\core\record\Record $model
     */
    public $model;
    
    /**
     * @var array $options Options array for the widget (ie. html options)
     */
    public $options = [];
    
    /**
     *
     * Set of the permissionSave
     */
    public function init()
    {
        $this->initDefaultOptions();
        
        parent::init();
    }
    
    /**
     * Set default options values.
     */
    private function initDefaultOptions()
    {
        $this->options['commentPlaceholder'] = AmosComments::t('amoscomments', 'Write a comment') . '...';
        $this->options['commentReplyPlaceholder'] = AmosComments::t('amoscomments', 'Write a reply') . '...';
        $this->options['commentTitle'] = AmosComments::t('amoscomments', '#COMMENT_TITLE');
        $this->options['lastCommentsTitle'] = AmosComments::t('amoscomments', 'Last comments');
    }
    
    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    public function run()
    {
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            
            return $content === false ? $matches[0] : $content;
        }, $this->layout);
        
        return $content;
    }
    
    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     * @return string|boolean the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{commentSection}':
                return $this->commentSection();
            case '{commentsSection}':
                return $this->commentsSection();
            default:
                return false;
        }
    }
    
    /**
     * Method that render the section of the comment container.
     * @return string
     */
    public function commentSection()
    {
        return $this->render('comments-widget/comment', [
            'widget' => $this
        ]);
    }
    
    /**
     * Method that render the comments section where there are all the comments and comments replies.
     * @return string
     */
    public function commentsSection()
    {
        /** @var \yii\db\ActiveQuery $query */
        $query = Comment::find()->andWhere(['context' => $this->model->className(), 'context_id' => $this->model->id])->orderBy(['created_at' => SORT_DESC]);
        
        /** @var \lispa\amos\comments\models\Comment $lastComment */
        $lastComment = $query->one();
        
        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->setPageSize(5);
        $comments = $query->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('comments-widget/comments', [
            'widget' => $this,
            'pages' => $pages,
            'comments' => $comments,
            'lastComment' => $lastComment,
        ]);
    }
}
