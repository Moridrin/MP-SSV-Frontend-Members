<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 1-5-2016
 * Time: 13:56
 */
require_once "FrontendMembersFieldInputSelectOption.php";

class FrontendMembersFieldInputSelectRoleOption extends FrontendMembersFieldInputSelectOption
{
    public function __construct($id, $index, $parent_id, $value = "")
    {
        parent::__construct($id, $index, $parent_id, $value);
    }

    public function getHTML($selected_value)
    {
        ob_start();
        global $wp_roles;
        ?>
        <option value="<?php echo esc_html($this->value); ?>" <?php if ($this->value == $selected_value) : echo "selected"; endif; ?>><?php echo esc_html(translate_user_role($wp_roles->roles[$this->value]['name'])); ?></option>
        <?php
        return ob_get_clean();
    }

    public function save($remove = false)
    {
        global $wpdb;
        if (strlen($this->value) <= 0) {
            $remove = true;
        }
        if ($remove) {
            $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
            $wpdb->delete(
                $table,
                array('id' => $this->id,),
                array('%d',)
            );
            $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
            $wpdb->delete(
                $table,
                array('field_id' => $this->id,),
                array('%d',)
            );
        } else {
            $this->replace();
        }
    }
}