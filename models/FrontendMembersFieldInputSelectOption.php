<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-7-16
 * Time: 9:17
 */
class FrontendMembersFieldInputSelectOption
{
    public $id;
    public $index;
    public $parent_id;
    public $value;

    protected function __construct($id, $index, $parent_id, $value = "")
    {
        $this->id = $id;
        $this->index = $index;
        $this->parent_id = $parent_id;
        $this->value = $value;
    }

    public function create($index, $parent_id, $value = "")
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $max_in_database = $wpdb->get_var('SELECT MAX(id) FROM ' . $table . ';');
        if ($max_in_database == null) {
            $id = 0;
        } else {
            $id = $max_in_database + 1;
        }
        $wpdb->insert(
            $table,
            array(
                'id'          => $id,
                'field_index' => $index,
                'field_type'  => 'group_option',
                'field_title' => ''
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s'
            )
        );
    }

    public function save($remove = false) { }

    public function getHTML($value) { }

    protected function replace()
    {
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $wpdb->replace(
            $table,
            array(
                'id'          => $this->id,
                'field_index' => $this->index,
                'field_type'  => 'group_option',
                'field_title' => ''
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s'
            )
        );
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "parent_id", "meta_value" => $this->parent_id),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "value", "meta_value" => $this->value),
            array('%d', '%s', '%s')
        );
    }
}