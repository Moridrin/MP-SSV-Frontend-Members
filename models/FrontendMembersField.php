<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 13:57
 */

require_once "FrontendMembersFieldTab.php";
require_once "FrontendMembersFieldHeader.php";
require_once "FrontendMembersFieldInput.php";

class FrontendMembersField
{
    public $id;
    public $type;
    public $title;
    public $registration_page;
    public $class;
    public $style;
    protected $index;

    /**
     * FrontendMembersField constructor.
     *
     * @param int    $id                identifies the field in the database.
     * @param int    $index             identifies the order in which it is displayed.
     * @param string $type              is the type of FrontendMembersField.
     * @param string $title             is the title of this FrontendMembersField.
     * @param string $registration_page is true if this field should be displayed on the registration page.
     * @param string $class             is a string of classes added to the field.
     * @param string $style             is a string of styles added to the field.
     */
    protected function __construct($id, $index, $type, $title, $registration_page, $class, $style)
    {
        $this->id = $id;
        $this->index = $index;
        $this->type = $type;
        $this->title = $title;
        $this->registration_page = $registration_page;
        $this->class = $class;
        $this->style = $style;
    }

    /**
     * This function returns all tabs.
     *
     * @return array
     */
    public static function getTabs()
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $tabs = array();
        $database_rows = json_decode(json_encode($wpdb->get_results("SELECT * FROM $table WHERE field_type = 'tab' ORDER BY field_index ASC;")), true);
        foreach ($database_rows as $database_row) {
            $tabs[] = FrontendMembersFieldTab::fromDatabaseFields($database_row);
        }

        return $tabs;
    }

    /**
     * This function returns all the items in the given Tab.
     *
     * @param FrontendMembersFieldTab $tab is the tab where you want the fields from.
     *
     * @return array
     */
    public static function getItemsInTab($tab)
    {
        $all_fields = self::getAll();
        $is_in_tab = false;
        $fields_in_tab = array();
        foreach ($all_fields as $field) {
            if ($field instanceof FrontendMembersFieldTab) {
                if ($field == $tab) {
                    $is_in_tab = true;
                } else {
                    $is_in_tab = false;
                }
            } else {
                if ($is_in_tab) {
                    $fields_in_tab[] = $field;
                }
            }
        }

        return $fields_in_tab;
    }

    /**
     * @param array $filters are applied to the SQL query.
     *
     * @return array of all the FrontendMembersFields.
     */
    public static function getAll($filters = array("field_type" => '!group_option'))
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $sql = "SELECT id FROM $table";
        foreach ($filters as $filter => $value) {
            if (substr($value, 0, 1) == "!") {
                $sql .= " WHERE " . $filter . " != '" . str_replace("!", "", $value) . "'";
            } else {
                $sql .= " WHERE " . $filter . " = '" . $value . "'";
            }
        }
        $sql .= " ORDER BY field_index ASC;";

        $database_fields = json_decode(json_encode($wpdb->get_results($sql)), true);
        $fields = array();
        foreach ($database_fields as $database_field) {
            $field = self::fromID($database_field['id']);
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * @param int $id is the id to find the field in the database.
     *
     * @return FrontendMembersField|FrontendMembersFieldHeader|FrontendMembersFieldInputText|FrontendMembersFieldInputTextCheckbox|FrontendMembersFieldInputSelectText|FrontendMembersFieldTab
     */
    protected static function fromID($id)
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $database_fields = json_decode(
            json_encode(
                $wpdb->get_row(
                    "SELECT *
					FROM $table
					WHERE id = $id;"
                )
            ), true
        );
        $field = self::fromDatabaseFields($database_fields);
        switch ($field->type) {
            case "tab":
                $field = new FrontendMembersFieldTab($field);
                break;
            case "header":
                $field = new FrontendMembersFieldHeader($field);
                break;
            case "input":
                $input_type = $field->getMeta("input_type");
                $name = $field->getMeta("name");
                $field = new FrontendMembersFieldInput($field, $input_type, $name);
                switch ($input_type) {
                    case "custom":
                        $field = new FrontendMembersFieldInputCustom($field, $field->getMeta('input_type_custom'), $field->getMeta('required'), $field->getMeta('display'), $field->getMeta('placeholder'));
                        break;
                    case "image":
                        $field = new FrontendMembersFieldInputImage($field, $field->getMeta('required'), $field->getMeta('preview'));
                        break;
                    case "role_checkbox":
                        $field = new FrontendMembersFieldInputRoleCheckbox($field, $field->getMeta('role'), $field->getMeta('display'));
                        break;
                    case "role_select":
                        $field = new FrontendMembersFieldInputSelectRole($field, $field->getMeta('display'));
                        break;
                    case "text":
                        $field = new FrontendMembersFieldInputText($field, $field->getMeta('required'), $field->getMeta('display'), $field->getMeta('placeholder'));
                        break;
                    case "text_checkbox":
                        $field = new FrontendMembersFieldInputTextCheckbox($field, $field->getMeta('help_text'), $field->getMeta('display'));
                        break;
                    case "text_select":
                        $field = new FrontendMembersFieldInputSelectText($field, $field->getMeta('display'));
                        $field->options = $field->getOptions();
                        break;
                }
                break;
        }

        return $field;
    }

    /**
     * @param array $database_fields the array returned by wpdb.
     *
     * @return FrontendMembersField
     */
    protected static function fromDatabaseFields($database_fields)
    {
        return new FrontendMembersField(
            $database_fields['id'],
            $database_fields['field_index'],
            $database_fields['field_type'],
            $database_fields['field_title'],
            $database_fields['registration_page'],
            $database_fields['field_class'],
            $database_fields['field_style']
        );
    }

    /**
     * This function gets the field metadata specified by the key.
     *
     * @param string $key is the key defining what metadata should be returned.
     *
     * @return string the meta value linked to the given key.
     */
    public function getMeta($key)
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $value = $wpdb->get_var(
            "SELECT meta_value
			FROM $table
			WHERE field_id = '$this->id'
			AND meta_key = '$key';"
        );

        return stripslashes($value);
    }

    /**
     *
     * @return array of all the FrontendMembersFields.
     */
    public static function getAllFieldNames()
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $sql = "SELECT id FROM $table WHERE field_type = 'input' ORDER BY field_index ASC;";
        $ids = json_decode(json_encode($wpdb->get_results($sql)), true);

        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $names = array();
        foreach ($ids as $id) {
            $sql = "SELECT meta_value FROM $table WHERE meta_key = 'name' AND field_id = " . $id['id'];
            $names[] = json_decode(json_encode($wpdb->get_var($sql)), true);
        }

        return $names;
    }

    public static function saveAllFromPost()
    {
        $id = 0;
        foreach ($_POST as $name => $val) {
            if (strpos($name, "_field_title") !== false) {
                $id++;
                $_POST[str_replace("_field_title", "", $name) . "_field_index"] = $id; //Set field_index
                $field = self::fromPOST(str_replace("_field_title", "", $name));
                $field->save();
            }
        }
    }

    /**
     * This method returns a FrontendMembersField created from the POST values from a form.
     *
     * @param int $id is the id of the FrontendMembersField that should be created.
     *
     * @return FrontendMembersField|FrontendMembersFieldHeader|FrontendMembersFieldInputText|FrontendMembersFieldInputTextCheckbox|FrontendMembersFieldInputSelectText|FrontendMembersFieldTab
     */
    public static function fromPOST($id)
    {
        $variables = array();
        foreach ($_POST as $name => $value) {
            if (in_array($id, explode("_", $name))) {
                $variables[str_replace($id . "_", "", $name)] = $value;
            }
        }
        $field = new FrontendMembersField(
            $id,
            sanitize_text_field($variables['field_index']),
            sanitize_text_field($variables["field_type"]),
            sanitize_text_field($variables["field_title"]),
            sanitize_text_field($variables["registration_page"]),
            sanitize_text_field($variables['field_class']),
            sanitize_text_field($variables['field_style'])
        );
        unset($variables["id"]);
        unset($variables["field_type"]);
        unset($variables["field_title"]);
        unset($variables["registration_page"]);
        unset($variables["field_class"]);
        unset($variables["field_style"]);
        switch ($field->type) {
            case "tab":
                $field = new FrontendMembersFieldTab($field);
                break;
            case "header":
                $field = new FrontendMembersFieldHeader($field);
                break;
            case "input":
                $input_type = $field->getMetaFromPOST("input_type");
                $name = $field->getMetaFromPOST("name");
                $field = new FrontendMembersFieldInput($field, $input_type, $name);
                switch ($input_type) {
                    case "custom":
                        $field = new FrontendMembersFieldInputCustom($field, $field->getMetaFromPOST('input_type_custom'), $field->getMetaFromPOST('required'), $field->getMetaFromPOST('display'), $field->getMetaFromPOST('placeholder'));
                        break;
                    case "image":
                        $field = new FrontendMembersFieldInputImage($field, $field->getMetaFromPOST('required'), $field->getMetaFromPOST('preview'));
                        break;
                    case "role_checkbox":
                        $field = new FrontendMembersFieldInputRoleCheckbox($field, $field->getMetaFromPOST('role'), $field->getMetaFromPOST('display'));
                        break;
                    case "role_select":
                        $field = new FrontendMembersFieldInputSelectRole($field, $field->getMetaFromPOST('display'));
                        $field->options = $field->getOptionsFromPOST($variables);
                        break;
                    case "text":
                        $field = new FrontendMembersFieldInputText($field, $field->getMetaFromPOST('required'), $field->getMetaFromPOST('display'), $field->getMetaFromPOST('placeholder'));
                        break;
                    case "text_checkbox":
                        $field = new FrontendMembersFieldInputTextCheckbox($field, $field->getMetaFromPOST('help_text'), $field->getMetaFromPOST('display'));
                        break;
                    case "text_select":
                        $field = new FrontendMembersFieldInputSelectText($field, $field->getMetaFromPOST('display'));
                        $field->options = $field->getOptionsFromPOST($variables);
                        break;
                }
                break;
        }

        return $field;
    }

    /**
     * This function gets the field metadata specified by the key.
     *
     * @param string $key is the key defining what metadata should be returned.
     *
     * @return string the meta value linked to the given key.
     */
    public function getMetaFromPOST($key)
    {
        if (!isset($_POST[$this->id . "_" . $key])) {
            return "no";
        }

        return sanitize_text_field($_POST[$this->id . "_" . $key]);
    }

    /**
     * This function creates a new FrontendMembersField and adds it to the database.
     *
     * @param int    $index             is an id that specifies the display (/tab) order for the field.
     * @param string $title             is the title of this component.
     * @param string $type              specifies the type of field. Either "tab", "header", "input" or "group_option".
     * @param string $registration_page is set to false if the field should not be displayed on the registration page.
     * @param string $class             is a string that is added to the class field.
     * @param string $style             is a string that is added to the style field.
     *
     * @return FrontendMembersField the just created instance.
     */
    protected static function createField($index, $title, $type, $registration_page = 'true', $class = '', $style = '')
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
                'id'                => $id,
                'field_index'       => $index,
                'field_type'        => $type,
                'field_title'       => $title,
                'registration_page' => $registration_page,
                'field_class'       => $class,
                'field_style'       => $style
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s'
            )
        );

        return new FrontendMembersField($id, $index, $type, $title, $registration_page, $class, $style);
    }

    /**
     * This function adds a property to this FrontendMembersField.
     *
     * @param string $key   is the key value that defines the property of the field.
     * @param string $value is the value of the property.
     */
    public function setMeta($key, $value)
    {
        global $wpdb;
        /** @noinspection PhpIncludeInspection */
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->insert(
            $table,
            array(
                'id'         => $this->id,
                'meta_key'   => $key,
                'meta_value' => $value
            ),
            array(
                '%d',
                '%s',
                '%s'
            )
        );
    }

    /**
     * This function is implemented in all subclasses.
     *
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        throw new BadMethodCallException();
    }

    /**
     * @param string $content is the extra content that it gets from it's child.
     * @param bool   $visible defines if this option row should be displayed (used to hide tab rows for themes that do not support mui).
     *
     * @return string a row that can be added to the profile page options table.
     */
    protected function getOptionRowField($content, $visible = true)
    {
        ob_start();
        echo ssv_get_td(ssv_get_draggable_icon());
        echo ssv_get_td(ssv_get_text_input("Field Title", $this->id, $this->title));
        if (get_theme_support('mui')) {
            echo ssv_get_td(ssv_get_select("Field Type", $this->id, $this->type, array("Tab", "Header", "Input"), array('onchange="ssv_type_changed(\'' . $this->id . '\')"')));
        } else {
            echo ssv_get_td(ssv_get_select("Field Type", $this->id, $this->type, array("Header", "Input"), array('onchange="ssv_type_changed(\'' . $this->id . '\')"')));
        }
        echo $content;
        if (get_option('ssv_frontend_members_register_page', 'same_as_profile_page') == 'custom') {
            echo ssv_get_td(ssv_get_checkbox('Registration Page', $this->id, $this->registration_page, array(), true));
        }
        if (get_option('ssv_view_advanced_profile_page', 'false') == 'true') {
            echo ssv_get_td(ssv_get_text_input('Field Class', $this->id, $this->class));
            echo ssv_get_td(ssv_get_text_input('Field Style', $this->id, $this->style));
        } else {
            echo ssv_get_hidden($this->id, 'Field Class', $this->class);
            echo ssv_get_hidden($this->id, 'Field Style', $this->style);
        }

        return ssv_get_tr($this->id, ob_get_clean(), $visible);
    }

    protected function save($remove = false)
    {
        global $wpdb;
        if (strlen($this->title) <= 0) {
            $remove = true;
        }
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $update = $wpdb->get_results(
            "SELECT id
					FROM $table
					WHERE id = $this->id;"
        );
        if ($remove) {
            $wpdb->delete(
                $table,
                array("id" => $this->id),
                array('%d')
            );
        } else {
            if (count($update) > 0) {
                $wpdb->update(
                    $table,
                    array(
                        "field_index"       => $this->index,
                        "field_type"        => $this->type,
                        "field_title"       => $this->title,
                        "registration_page" => $this->registration_page,
                        "field_class"       => $this->class,
                        "field_style"       => $this->style,
                    ),
                    array("id" => $this->id),
                    array('%d', '%s', '%s', '%s', '%s', '%s'),
                    array('%d')
                );
            } else {
                $wpdb->insert(
                    $table,
                    array(
                        "id"                => $this->id,
                        "field_index"       => $this->index,
                        "field_type"        => $this->type,
                        "field_title"       => $this->title,
                        "registration_page" => $this->registration_page,
                        "field_class"       => $this->class,
                        "field_style"       => $this->style,
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
                );
            }
        }

        return $remove;
    }
}