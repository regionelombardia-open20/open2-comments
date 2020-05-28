amos-comments
----------------

Extension for comment a content like news, events, etc...

Installation
------------

1 The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require open20/amos-comments
```

or add this row

```
"open20/amos-comments": "dev-master"
```

to the require section of your `composer.json` file.

2 Add module to your main config in backend:
	
```php
<?php
'modules' => [
    'comments' => [
        'class' => 'open20\amos\comments\AmosComments',
        'modelsEnabled' => [
            /**
             * Add here the classnames of the models where you want the comments
             * (i.e. 'open20\amos\events\models\Event')
             */
        ],
        // the following are mandatory fields
        'displayNotifyCheckbox' => true, // if the notify checkbox in the accordion must be shown (if hidden, the notify checkbox is selected)
        'accordionOpenedByDefault' => false, // if the accordion must be opened by default
    ],
],
```

Also, add these lines to your bootstrap:
	
```php
<?php
'bootstrap' => [
    'comments',
],
```

3 Add the view component to your main config in common:
	
```php
<?php
'components' => [
    'view' => [
        'class' => 'open20\amos\core\components\AmosView',
    ],
],
```

4 Apply migrations

```bash
php yii migrate/up --migrationPath=@vendor/open20/amos-comments/src/migrations
```

or add this row to your migrations config in console:

```php
<?php
return [
    '@vendor/open20/amos-comments/src/migrations',
];
```

5 Implement the CommentInterface in your model
	
```php
<?php
use open20\amos\comments\models\CommentInterface;

/**
 * Implement the CommentInterface
 */
class MyClass implements CommentInterface

/**
 * Add the required method that must return boolean
 */
public function isCommentable()
{
    return true;
}
```

6 Add your model to the modulesEnables in the module config in backend/config/main.php

```php
<?php
'modules' => [
    'comments' => [
        'class' => 'open20\amos\comments\AmosComments',
        'modelsEnabled' => [
            'class_namespace\MyClass'
        ]
    ],
],
```


7 disable mail notifications

```php
<?php
'modules' => [
    'comments' => [
        'class' => 'open20\amos\comments\AmosComments',
        'enableMailsNotification' => false,
        'modelsEnabled' => [
            'class_namespace\MyClass'
        ]
    ],
],
```

***htmlMailContent** - string/array  
change the content of the mail of notification when you insert a comment
you can insert an array
```php
  'comments' => [
       'htmlMailContent' => [
            'open20\amos\news\models\News' => '@backend/mail/comment/content_news',
            'open20\amos\discussioni\models\DiscussioniTopic' => '@backend/mail/comment/content_discussioni',
            'open20\amos\documenti\models\Documenti' => '@backend/mail/comment/content_documenti'
        ],
```
or a string if the conente is valid for all contents(news/discussioni/docuemnts/ecc..)
```php
  'comments' => [
    'htmlMailContent' => '@backend/mail/comment/content_news'
    ]
```

enableCommentOnlyWithScope
***enableCommentOnlyWithScope** - boolean default false  
If true it enable the comments olny with the scope (in the community)
```php
  'comments' => [
       'enableCommentOnlyWithScope' => true,
  ]
```