<?php
/**
 * This file is part of cBackup, network equipment configuration backup tool
 * Copyright (C) 2017, Oļegs Čapligins, Imants Černovs, Dmitrijs Galočkins
 *
 * cBackup is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\models\search;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use app\models\OutCustom;
use app\models\Node;
use app\models\Task;


/** @noinspection UndetectableTableInspection
 *  OutCustomSearch represents the model behind the search form about `app\models\OutCustom`.
 *  @package app\models\search
 */
class OutCustomSearch extends OutCustom
{

    /**
     * @var string
     */
    public $out_table = '';

    /**
     * @var array
     */
    public $table_fields = [];

    /**
     * @var array
     */
    public $custom_fields = [];

    /**
     * @var string
     */
    public $destination = '';

    /**
     * @var string
     */
    public $task_name = '';

    /**
     * @var OutCustom
     */
    public $model;

    /**
     * OutCustomSearch constructor.
     *
     * @param string $table
     * @param array $config
     */
    public function __construct(string $table, array $config = [])
    {
        parent::__construct($config);

        $this->out_table     = $table;
        $this->task_name     = str_replace('out_', '', $table);
        $out_table_fields    = Task::getTaskOutTableFields($this->task_name);
        $this->table_fields  = array_merge($out_table_fields['default_fields'], $out_table_fields['custom_fields']); // Table fields object array
        $this->custom_fields = $out_table_fields['custom_fields']; // Only custom table fields
        $this->destination   = Task::find()->select('put')->where(['name' => $this->task_name])->scalar(); // Get task destination
        $this->model         = new OutCustom(['table' => $this->out_table]); // Init out table
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        /** Table fields array */
        $fields = array_merge(
            array_map(function ($field) { return $field->name; }, $this->table_fields),
            ['node_search', 'date_from', 'date_to', 'page_size']
        );
        return [
            [$fields, 'filter', 'filter' => 'trim'],
            [$fields, 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    public function search($params)
    {

        $query = $this->model::find();

        /** Load model params */
        $this->load($params);

        /** Validate input data */
        $this->validate();

        /** Permanent fields filter */
        $query->andFilterWhere(['like', 'node_id', $this->node_id])
            ->andFilterWhere(['like', 'hash', $this->hash]);

        /** Time interval filter  */
        if (!empty($this->date_from) && !empty($this->date_to)) {
            $query->andFilterWhere(['between', 'CAST(time AS DATE)', $this->date_from, $this->date_to]);
        }
        elseif (!empty($this->date_from)) {
            $query->andFilterWhere(['>=', 'CAST(time AS DATE)', $this->date_from]);
        }
        elseif (!empty($this->date_to)) {
            $query->andFilterWhere(['<=', 'CAST(time AS DATE)', $this->date_to]);
        }

        /** Dynamic fields filters if task destination is set to db */
        if ($this->destination === 'db') {
            foreach ($this->custom_fields as $field) {
                $field_name = $field->name;
                $query->andFilterWhere(['like', $field_name, $this->$field_name]);
            }
        }

        $data = $query->orderBy('id')->asArray()->all();

        /** Get node info */
        foreach ($data as $key => $entry) {
            $node        = Node::find()->select(['hostname', 'ip'])->where(['id' => $entry['node_id']])->one();
            $data[$key] += (!empty($node)) ? ['hostname' => $node->hostname, 'ip' => $node->ip] : ['hostname' => null, 'ip' => null] ;
        }

        /** Search in array */
        if (!empty($this->node_search)) {
            $node_input = preg_quote($this->node_search, '~');
            $data       = array_filter($data, function($node) use($node_input) { return preg_grep("~{$node_input}~i", $node); });
        }

        /** Populate custom table fields from file if task destination is set to file */
        if ($this->destination === 'file') {

            /** Populate custom table fields with data from file */
            $data = $this->getDataFromFile($data);

            /** Search in custom table fields */
            foreach ($this->custom_fields as $field) {
                $field_name = $field->name;
                if (!empty($this->$field_name)) {
                    $input = preg_quote($this->$field_name, '~');
                    $data  = array_filter($data, function ($node) use ($input, $field_name) {
                        return preg_grep("~{$input}~i", [$node[$field_name]]);
                    });
                }
            }

        }

        /** Create dataprovider */
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
            'sort' => [
                'attributes' => [
                    'time', 'node_id', 'hash',
                    'node_search' => [
                        'asc'  => ['hostname' => SORT_ASC, 'LENGTH(ip)' => SORT_ASC, 'ip' => SORT_ASC],
                        'desc' => ['hostname' => SORT_DESC, 'LENGTH(ip)' => SORT_DESC, 'ip' => SORT_DESC],
                    ],
                ]
            ],
        ]);

        /** Set page size dynamically */
        $dataProvider->pagination->pageSize = $this->page_size;

        return $dataProvider;

    }

    /**
     * Get data from file if task destination is set to file
     *
     * @param  array $data
     * @return mixed
     */
    private function getDataFromFile($data)
    {
        $dir_path = \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $this->task_name . DIRECTORY_SEPARATOR;

        foreach ($data as $key => $item)
        {
            $file = $dir_path . "{$item['node_id']}.txt";
            if (file_exists($file)) {
                $file_data = file_get_contents($file);
                $data[$key]['raw_data'] = $file_data;
                foreach ($this->custom_fields as $field) {
                    if (preg_match("/(?<=##{$field->name}##).*?(?=##ENDOF_{$field->name}##)/is", $file_data, $matches)) {
                        $data[$key][$field->name] = $matches[0];
                    }
                }
            }
        }

        return $data;
    }

}
