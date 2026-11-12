<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos\comments\models\search
 * @author    Aria S.p.A. <protocollo@pec.ariaspa.it>
 * @license   GNU General Public License v3.0 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @link      https://www.ariaspa.it/
 *
 * This file is part of OPEN 2.0.
 *
 * OPEN 2.0 is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OPEN 2.0 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OPEN 2.0. If not, see <https://www.gnu.org/licenses/>.
 */

namespace open20\amos\comments\models\search;

use open20\amos\comments\models\Comment;
use open20\amos\comments\utility\CommentsUtility;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class CommentSearch
 * @package open20\amos\comments\models\search
 */
class CommentSearch extends Comment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'context_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['comment_text', 'context', 'created_at', 'updated_at', 'deleted_at', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Comment::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'context_id' => $this->context_id,
            'context' => $this->context,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
        ]);

        $query->andFilterWhere(['like', 'comment_text', $this->comment_text]);

        return $dataProvider;
    }

    /**
     * Search comments to validate
     * @param $params
     * @return ActiveDataProvider
     * @throws InvalidConfigException
     */
    public function searchToValidate($params): ActiveDataProvider
    {
        // Checks if the user is a validator of the context model
        $commentsToValidate = Comment::find()->andWhere(['status' => Comment::STATUS_TOVALIDATE])->all();
        $commentIdsToShow = [];
        /** @var Comment $commentToValidate */
        foreach ($commentsToValidate as $commentToValidate) {
            $contextModel = $commentToValidate->contextModel;
            if (CommentsUtility::userIsCommentsValidator($contextModel)) {
                $commentIdsToShow[] = $commentToValidate->id;
            }
        }
        if (!empty($commentIdsToShow)) {
            $dataProvider = $this->search($params);
            $dataProvider->query->andWhere(['status' => Comment::STATUS_TOVALIDATE]);
            $dataProvider->query->andWhere(['in', 'id', $commentIdsToShow]);
        } else {
            // If the user is not a validator, the dataProvider is empty
            $dataProvider = new ActiveDataProvider([
                'query' => Comment::find()->andWhere(['id' => 0]),
            ]);
        }

        return $dataProvider;
    }
}
