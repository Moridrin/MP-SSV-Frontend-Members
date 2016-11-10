<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:10
 */
class FrontendMembersFieldInputTextCheckbox extends FrontendMembersFieldInput
{
    public $required;
    public $display;
    public $defaultValue;

    /**
     * FrontendMembersFieldInputRoleCheckbox constructor.
     *
     * @param FrontendMembersFieldInput $field        is the parent field.
     * @param string                    $required     is the name of the role or the role itself associated with this checkbox.
     * @param string                    $display      is the way the input field is displayed (readonly, disabled or normal) default is normal.
     * @param string                    $defaultValue is whether the checkbox is checked or not when filling in the form.
     */
    protected function __construct($field, $required, $display, $defaultValue)
    {
        parent::__construct($field, $field->input_type, $field->name);
        $this->required     = $required;
        $this->display      = $display;
        $this->defaultValue = $defaultValue ?: '';
    }

    /**
     * @param int    $index        is an index that specifies the display (/tab) order for the field.
     * @param string $title        is the title of this component.
     * @param string $name         is the name of the input field.
     * @param string $help_text    is the name of the role or the role itself associated with this checkbox.
     * @param string $display      is the way the input field is displayed (readonly, disabled or normal) default is normal.
     * @param string $defaultValue is whether the checkbox is checked or not when filling in the form.
     *
     * @return FrontendMembersFieldInputTextCheckbox
     */
    public static function create($index, $title, $name, $help_text = "", $display = "normal", $defaultValue = "")
    {
        return new FrontendMembersFieldInputTextCheckbox(parent::createInput($index, $title, 'role_checkbox', $name), $help_text, $display, $defaultValue);
    }

    /**
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        echo ssv_get_td(ssv_get_text_input("Name", $this->id, $this->name, "text", array("required")));
        echo ssv_get_td(ssv_get_checkbox("Required", $this->id, $this->required));
        echo ssv_get_td(ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
        echo ssv_get_td(ssv_get_checkbox("Checked by Default", $this->id, $this->defaultValue));
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        $content = ob_get_clean();

        return parent::getOptionRowInput($content);
    }

    /**
     * @param FrontendMember $frontend_member
     *
     * @return string the HTML element
     */
    public function getHTML($frontend_member = null)
    {
        ob_start();
        if ($frontend_member == null) {
            $value         = null;
            $this->display = 'normal';
        } else {
            $value = $frontend_member->getMeta($this->name);
        }
        if (current_theme_supports('mui')) {
            ?>
            <input type="hidden" name="<?php echo $this->name; ?>" value="no"/>
            <div class="mui-checkbox">
                <label>
                    <input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>" value="yes" <?php if ($value == "yes" || ($value == null && $this->defaultValue == "yes")) : echo "checked"; endif; ?> <?php echo $this->required == 'yes' ? 'required' : ''; ?>>
                    <?php echo $this->title; ?>
                </label>
            </div>
            <?php
        } else {
            ?>
            <input type="hidden" name="<?php echo $this->name; ?>" value="no"/>
            <label>
                <input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>" value="yes" <?php if ($value == "yes" || ($value == null && $this->defaultValue == "yes")) : echo "checked"; endif; ?> <?php echo $this->required ? 'required' : ''; ?>>
                <?php echo $this->title; ?>
            </label>
            <br/>
            <?php
        }

        return ob_get_clean();
    }

    public function save($remove = false)
    {
        parent::save($remove);
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "required", "meta_value" => $this->required),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "default_value", "meta_value" => $this->defaultValue),
            array('%d', '%s', '%s')
        );
    }
}