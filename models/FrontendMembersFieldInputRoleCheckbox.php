<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:10
 */
class FrontendMembersFieldInputRoleCheckbox extends FrontendMembersFieldInput
{
    public $role;
    public $display;
    public $defaultValue;

    /**
     * FrontendMembersFieldInputRoleCheckbox constructor.
     *
     * @param FrontendMembersFieldInput $field        is the parent field.
     * @param string|\WP_Role           $role         is the name of the role or the role itself associated with this checkbox.
     * @param string                    $display      is the way the input field is displayed (readonly, disabled or normal) default is normal.
     * @param string                    $defaultValue is whether the checkbox is checked or not when filling in the form.
     */
    protected function __construct($field, $role, $display, $defaultValue)
    {
        parent::__construct($field, $field->input_type, $field->name);
        $this->role         = $role;
        $this->display      = $display;
        $this->defaultValue = $defaultValue;
        $this->name         = $this->role . '_role';
    }

    /**
     * A checkbox always has a value ('no' or 'yes')
     *
     * @param FrontendMember|null $frontend_member is the member to check if this member already has the required value.
     *
     * @return bool required
     */
    public function isValueRequiredForMember($frontend_member = null)
    {
        if (!$this->isEditable()) {
            return false;
        }
        if (FrontendMember::get_current_user() != null && FrontendMember::get_current_user()->isBoard()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * If the field is displayed normally than this field is editable.
     *
     * @return bool returns if the field is displayed normally.
     */
    public function isEditable()
    {
        if (FrontendMember::get_current_user() != null && FrontendMember::get_current_user()->isBoard()) {
            return true;
        }
        return $this->display == 'normal';
    }

    /**
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        echo ssv_get_td(ssv_get_role_select($this->id, "Role", $this->role));
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled"), array()));
        } else {
            echo ssv_get_hidden($this->id, "Display", $this->display);
        }
        if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_checkbox("Checked by Default", '\' + sender_id + \'', "no", array(), false, false));
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
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
        global $wp_roles;
        if (current_theme_supports('mui')) {
            ?>
            <input type="hidden" name="<?php echo $this->name; ?>" value="no"/>
            <div class="mui-checkbox">
                <label>
                    <input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>" value="yes" <?php if ($value == "yes" || ($value == null && $this->defaultValue == "yes")) : echo "checked"; endif; ?>>
                    <?php echo translate_user_role($wp_roles->roles[$this->role]['name']); ?>
                </label>
            </div>
            <?php
        } else {
            ?>
            <input type="hidden" name="<?php echo $this->name; ?>" value="no"/>
            <label>
                <input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>" value="yes" <?php if ($value == "yes" || ($value == null && $this->defaultValue == "yes")) : echo "checked"; endif; ?>>
                <?php echo translate_user_role($wp_roles->roles[$this->role]['name']); ?>
            </label>
            <br/>
            <?php
        }

        return ob_get_clean();
    }

    public function save($remove = false, $user = null)
    {
        parent::save($remove);
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "role", "meta_value" => $this->role),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "default_value", "meta_value" => $this->defaultValue),
            array('%d', '%s', '%s')
        );
    }
}