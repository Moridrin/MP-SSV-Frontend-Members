<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */
class FrontendMembersFieldInputCustom extends FrontendMembersFieldInput
{

    public $input_type_custom;
    public $required;
    public $display;
    public $placeholder;
    public $defaultValue;

    /**
     * FrontendMembersFieldInputCustom constructor.
     *
     * @param FrontendMembersFieldInput $field        is the parent field.
     * @param string                    $input_type_custom
     * @param bool                      $required     is true if this is a required input field.
     * @param string                    $display      is the way the input field is displayed (readonly, disabled or normal) default is normal.
     * @param string                    $placeholder  is the placeholder text that gives an example of what to enter.
     * @param string                    $defaultValue is the default input_type_custom that is already entered when you fill in the form.
     */
    protected function __construct($field, $input_type_custom, $required, $display, $placeholder, $defaultValue)
    {
        parent::__construct($field, $field->input_type, $field->name);
        $this->input_type_custom = $input_type_custom;
        $this->required          = $required;
        $this->display           = $display;
        $this->placeholder       = $placeholder;
        $this->defaultValue      = $defaultValue ?: '';
    }

    /**
     * If the field is required than this field does need a value.
     *
     * @param FrontendMember|null $frontend_member is the member to check if this member already has the required value.
     *
     * @return bool returns if the field is required.
     */
    public function isValueRequiredForMember($frontend_member = null)
    {
        if (!$this->isEditable()) {
            return false;
        }
        if (FrontendMember::get_current_user() != null && FrontendMember::get_current_user()->isBoard()) {
            return false;
        } else {
            return $this->required == 'yes';
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
        echo ssv_get_td(ssv_get_text_input("Name", $this->id, $this->name, 'text', array('required')));
        echo ssv_get_td(ssv_get_checkbox("Required", $this->id, $this->required));
        if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled"), array()));
        } else {
            echo ssv_get_hidden($this->id, "Display", $this->display);
        }
        if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_text_input("Default Value", $this->id, $this->defaultValue, $this->input_type_custom));
        } else {
            echo ssv_get_hidden($this->id, "Default Value", $this->defaultValue);
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_text_input("Placeholder", $this->id, $this->placeholder));
        } else {
            echo ssv_get_hidden($this->id, "Placeholder", $this->placeholder);
        }
        $content = ob_get_clean();

        return parent::getOptionRowInput($content, $this->input_type_custom);
    }

    /**
     * @param FrontendMember $frontend_member
     *
     * @return string
     */
    public function getHTML($frontend_member = null)
    {
        ob_start();
        if ($frontend_member == null) {
            $value         = $this->defaultValue;
            $this->display = 'normal';
        } else {
            $value = $frontend_member->getMeta($this->name);
        }
        if (current_theme_supports('mui')) {
            ?>
            <div class="mui-textfield">
                <input type="<?php echo $this->input_type_custom; ?>" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" value="<?php echo $value; ?>" <?php if (!is_user_logged_in() || !FrontendMember::get_current_user()->isBoard()) {
                    echo $this->display;
                } ?>
                       placeholder="<?php echo $this->placeholder; ?>" <?php if ($this->required == "yes") {
                    echo "required";
                } ?> class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>"/>
                <label><?php echo $this->title; ?></label>
            </div>
            <?php
        } else {
            ?>
            <label><?php echo $this->title; ?></label>
            <input type="<?php echo $this->input_type_custom; ?>" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" value="<?php echo $value; ?>" <?php if (!is_user_logged_in() || !FrontendMember::get_current_user()->isBoard()) {
                echo $this->display;
            } ?>
                   placeholder="<?php echo $this->placeholder; ?>" <?php if ($this->required == "yes") {
                echo "required";
            } ?> class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>"/>
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
            array("field_id" => $this->id, "meta_key" => "input_type_custom", "meta_value" => $this->input_type_custom),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "required", "meta_value" => $this->required),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "placeholder", "meta_value" => $this->placeholder),
            array('%d', '%s', '%s')
        );
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "default_value", "meta_value" => $this->defaultValue),
            array('%d', '%s', '%s')
        );
    }
}