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

class FrontendMembersFieldInputSelectTextOption extends FrontendMembersFieldInputSelectOption
{
    public function __construct($id, $index, $parent_id, $value = "")
    {
        parent::__construct($id, $index, $parent_id, $value);
    }

    public function getHTML($selected_value)
    {
        ob_start();
        ?>
        <option value="<?php echo esc_html($this->value); ?>" <?php if ($this->value == $selected_value) : echo "selected"; endif; ?>><?php echo esc_html($this->value); ?></option>
        <?php
        return ob_get_clean();
    }

    public function save($remove = false)
    {
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
        if (strlen($this->value) <= 0) {
            $remove = true;
        }
        if ($remove) {
            $wpdb->delete(
                $table,
                array('id' => $this->id,),
                array('%d',)
            );
        } else {
            $this->replace();
        }
    }
}