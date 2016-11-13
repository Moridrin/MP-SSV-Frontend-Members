<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:01
 */

require_once "FrontendMembersFieldInputCustom.php";
require_once "FrontendMembersFieldInputImage.php";
require_once "FrontendMembersFieldInputRoleCheckbox.php";
require_once "FrontendMembersFieldInputSelect.php";
require_once "FrontendMembersFieldInputSelectRole.php";
require_once "FrontendMembersFieldInputSelectText.php";
require_once "FrontendMembersFieldInputText.php";
require_once "FrontendMembersFieldInputTextCheckbox.php";

class FrontendMembersFieldInput extends FrontendMembersField
{
    public $name;
    protected $input_type;

    /**
     * FrontendMembersFieldInput constructor.
     *
     * @param FrontendMembersField $field      is the parent field.
     * @param int                  $input_type is the type of input field.
     * @param string               $name       is the name of the input field.
     */
    protected function __construct($field, $input_type, $name)
    {
        parent::__construct($field->id, $field->index, $field->type, $field->title, $field->registration_page, $field->class, $field->style);
        $this->input_type = $input_type;
        $this->name       = $name;
    }

    /**
     * This is a function that is handled in the sub classes.
     *
     * @param FrontendMember|null $frontend_member is the member to check if this member already has the required value.
     */
    public function isValueRequiredForMember(
        /** @noinspection PhpUnusedParameterInspection */
        $frontend_member = null
    ) {
        throw new BadMethodCallException();
    }

    /**
     * This is a function that is handled in the sub classes.
     */
    public function isEditable()
    {
        throw new BadMethodCallException();
    }

    /**
     * @param string $content is a string of all input columns.
     * @param string $input_type_custom
     *
     * @return string row that can be added to the profile page options table.
     */
    protected function getOptionRowInput($content, $input_type_custom = "")
    {
        ob_start();
        echo ssv_get_td(ssv_get_select("Input Type", $this->id, $this->input_type, array("Text", "Text Select", "Role Select", "Text Checkbox", "Role Checkbox", "Image"), array('onchange="ssv_input_type_changed(\'' . $this->id . '\')"'), true, $input_type_custom));
        echo $content;
        $content = ob_get_clean();

        return parent::getOptionRowField($content);
    }

    protected function save($remove = false)
    {
        $remove = parent::save($remove);
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        if ($remove) {
            $wpdb->delete(
                $table,
                array("field_id" => $this->id),
                array('%d')
            );
        } else {
            $wpdb->replace(
                $table,
                array("field_id" => $this->id, "meta_key" => "input_type", "meta_value" => $this->input_type),
                array('%d', '%s', '%s')
            );
            $wpdb->replace(
                $table,
                array("field_id" => $this->id, "meta_key" => "name", "meta_value" => $this->name),
                array('%d', '%s', '%s')
            );
        }
        return $remove;
    }
}