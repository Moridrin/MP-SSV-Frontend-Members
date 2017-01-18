<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */
class FrontendMembersFieldInputDate extends FrontendMembersFieldInput
{

    public $dateTimeType;
    public $required;
    public $display;
    public $placeholder;
    public $defaultValue;
    public $now;

    /**
     * FrontendMembersFieldInputCustom constructor.
     *
     * @param FrontendMembersFieldInput $field        is the parent field.
     * @param string                    $dateTimeType
     * @param bool                      $required     is true if this is a required input field.
     * @param string                    $display      is the way the input field is displayed (readonly, disabled or normal) default is normal.
     * @param string                    $placeholder  is the placeholder text that gives an example of what to enter.
     * @param string                    $defaultValue is the default input_type_custom that is already entered when you fill in the form.
     * @param string                    $now          is true if the default value should be the current datetime.
     */
    protected function __construct($field, $dateTimeType, $required, $display, $placeholder, $defaultValue, $now)
    {
        parent::__construct($field, $field->input_type, $field->name);
        $this->dateTimeType = $dateTimeType;
        $this->required     = $required;
        $this->display      = $display;
        $this->placeholder  = $placeholder;
        $this->defaultValue = $defaultValue ?: '';
        $this->now          = $now;
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
        if (get_option('ssv_frontend_members_view_display_preview_column', true)) {
            echo ssv_get_td(ssv_get_select("Display", $this->id, $this->display, array("Normal", "Disabled"), array()));
        } else {
            echo ssv_get_hidden($this->id, "Display", $this->display);
        }
        if (get_option('ssv_frontend_members_view_default_column', true)) {
            echo ssv_get_td(
                ssv_get_text_input("Default Value", $this->id, $this->defaultValue, 'text', array('class="' . $this->dateTimeType . 'picker"')) .
                ssv_get_checkbox("Now", $this->id, $this->now)
            );
        } else {
            echo ssv_get_hidden($this->id, "Default Value", $this->defaultValue);
            echo ssv_get_hidden($this->id, "Now", $this->now);
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', true)) {
            echo ssv_get_td(ssv_get_text_input("Placeholder", $this->id, $this->placeholder));
        } else {
            echo ssv_get_hidden($this->id, "Placeholder", $this->placeholder);
        }
        $content = ob_get_clean();

        return parent::getOptionRowInput($content, $this->dateTimeType);
    }

    /**
     * This function creates an input field for the filter.
     *
     * @return string div with a filter field.
     */
    public function getFilter()
    {
        ob_start();
        ?>
        <input type="text" id="<?php echo esc_html($this->id); ?>" name="filter_<?php echo esc_html($this->name); ?>" placeholder="<?php echo esc_html($this->title); ?>" value="<?= isset($_SESSION['filter_' . $this->name]) ? esc_html($_SESSION['filter_' . $this->name]) : '' ?>">
        <?php
        return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
    }

    /**
     * @param FrontendMember $frontend_member
     *
     * @return string
     * @throws Exception if te theme does not support MUI (will be removed later).
     */
    public function getHTML($frontend_member = null)
    {
        if ($frontend_member == null) {
            if ($this->now == 'yes') {
                switch ($this->dateTimeType) {
                    case 'datetime':
                        $this->defaultValue = date('Y-m-d H:i');;
                        break;
                    case 'date':
                        $this->defaultValue = date('Y-m-d');;
                        break;
                    case 'time':
                        $this->defaultValue = date('H:i');;
                        break;
                }
            }
            $value              = isset($_POST[$this->name]) ? $_POST[$this->name] : $this->defaultValue;
            $this->display      = 'normal';
        } else {
            $value = $frontend_member->getMeta($this->name);
        }
        $isBoard = (is_user_logged_in() && FrontendMember::get_current_user()->isBoard());

        if ($this->dateTimeType == 'date') {
            $class       = !empty($this->class) ? 'class="' . $this->class . '"' : '';
            $placeholder = '';
        } else {
            $class       = !empty($this->class) ? 'class="validate ' . $this->class . '"' : 'class="validate"';
            $placeholder = !empty($this->placeholder) ? 'placeholder="' . $this->placeholder . '"' : '';
        }
        $id       = !empty($this->id) ? 'id="' . $this->id . '"' : '';
        $type     = !empty($this->dateTimeType) ? 'type="' . $this->dateTimeType . '"' : '';
        $name     = !empty($this->name) ? 'name="' . $this->name . '"' : '';
        $style    = !empty($this->style) ? 'style="' . $this->style . '"' : '';
        $value    = !empty($value) ? 'value="' . $value . '"' : '';
        $display  = !$isBoard ? $this->display : '';
        $required = $this->required == "yes" && !$isBoard ? 'required' : '';

        ob_start();
        if (current_theme_supports('materialize')) {
            ?>
            <div class="input-field col s12">
                <label><?= $this->title ?><?= $this->required == "yes" ? '*' : '' ?></label><br/>
                <input <?= $type ?> <?= $id ?> <?= $name ?> <?= $value ?> <?= $placeholder ?> <?= $display ?> <?= $required ?> <?= $class ?> <?= $style ?> title="<?= $this->title ?>"/>
            </div>
            <?php
        } else {
            throw new Exception('Themes without "materialize" support are currently not supported by this plugin.');
        }

        return trim(preg_replace('/\s\s+/', ' ', ob_get_clean()));
    }

    public function save($remove = false)
    {
        parent::save($remove);
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "input_type_custom", "meta_value" => $this->dateTimeType),
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
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "now", "meta_value" => $this->now),
            array('%d', '%s', '%s')
        );
    }
}