<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 26-7-16
 * Time: 9:13
 */
class FrontendMembersFieldInputSelect extends FrontendMembersFieldInput
{
    public $options;
    public $display;

    /**
     * This function returns all the group options for this field.
     *
     * @return array|null with all options linked to this FrontendMembersField or null if this is not a group field.
     */
    public function getOptions()
    {
        global $wpdb;

        //Get Option Field ID's
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $option_ids = $wpdb->get_results(
            "SELECT field_id
			FROM $table
			WHERE meta_key = 'parent_id'
			AND meta_value = '$this->id';"
        );
        $option_ids = json_decode(json_encode($option_ids), true);

        //Get Option Fields
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        $sql = "SELECT * FROM $table WHERE field_type = 'group_option' AND (";
        for ($i = 0; $i < count($option_ids); $i++) {
            if ($i != 0) {
                $sql .= " OR ";
            }
            $sql .= "id = " . $option_ids[$i]["field_id"];
        }
        $sql .= ") ORDER BY id ASC;";
        $option_fields = $wpdb->get_results($sql);

        //Create Options and Get Value
        $options = array();
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        foreach ($option_fields as $option_field) {
            $option_field = json_decode(json_encode($option_field), true);
            if ($this instanceof FrontendMembersFieldInputSelectText) {
                $option = new FrontendMembersFieldInputSelectTextOption($option_field['id'], $option_field['field_index'], $this->id);
            } else {
                $option = new FrontendMembersFieldInputSelectRoleOption($option_field['id'], $option_field['field_index'], $this->id);
            }
            $value = $wpdb->get_var(
                "SELECT meta_value
			FROM $table
			WHERE field_id = '$option->id'
			AND meta_key = 'value';"
            );
            $option->value = stripslashes($value);
            $options[] = $option;
        }

        return $options;
    }

    public function save($remove = false, $user = null)
    {
        $remove = parent::save($remove);
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
            array('%d', '%s', '%s')
        );
        foreach ($this->options as $option) {
            /* @var $option FrontendMembersFieldInputSelectOption */
            $option->save($remove);
        }

        return $remove;
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
            $value = "";
            $this->display = 'normal';
        } else {
            $value = $frontend_member->getMeta($this->name);
        }
        if (current_theme_supports('mui')) {
            ?>
            <div class="mui-select mui-textfield">
                <label for="<?php echo esc_html($this->id); ?>"><?php echo esc_html($this->title); ?></label>
                <select id="<?php echo esc_html($this->id); ?>" name="<?php echo esc_html($this->name); ?>" class="<?php echo esc_html($this->class); ?>" style="<?php echo $this->style; ?>">
                    <?php foreach ($this->options as $option) {
                        /* @var $option FrontendMembersFieldInputSelectRoleOption|FrontendMembersFieldInputSelectTextOption */
                        echo $option->getHTML($value);
                    }
                    ?>
                </select>
            </div>
            <?php
        } else {
            ?>
            <label for="<?php echo esc_html($this->id); ?>"><?php echo esc_html($this->title); ?></label>
            <select id="<?php echo esc_html($this->id); ?>" name="<?php echo esc_html($this->name); ?>" class="<?php echo esc_html($this->class); ?>" style="<?php echo $this->style; ?>">
                <?php foreach ($this->options as $option) {
                    /* @var $option FrontendMembersFieldInputSelectRoleOption|FrontendMembersFieldInputSelectTextOption */
                    echo $option->getHTML($value);
                }
                ?>
            </select>
            <br/>
            <?php
        }

        return ob_get_clean();
    }

    public function getOptionRow()
    {
        ob_start();
        echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name, "text", array("required")));
        return ob_get_clean();
    }
}