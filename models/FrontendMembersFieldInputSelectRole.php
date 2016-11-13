<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */

require_once 'FrontendMembersFieldInputSelectRoleOption.php';

class FrontendMembersFieldInputSelectRole extends FrontendMembersFieldInputSelect
{
    /**
     * FrontendMembersFieldInputRoleSelect constructor.
     *
     * @param FrontendMembersFieldInput $field   is the parent field.
     * @param string                    $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
     */
    protected function __construct($field, $display)
    {
        parent::__construct($field, $field->input_type, $field->name);
        $this->options = $this->getOptions();
        $this->display = $display;
        if (strpos($field->name, "_role_select") === false) {
            $this->name = $field->name . '_role_select';
        }
    }

    public function getOptionsFromPOST($variables)
    {
        $options = array();
        $index   = 0;
        foreach ($variables as $name => $value) {
            if (strpos($name, "_option") !== false) {
                $id        = str_replace("option", "", str_replace("_", "", $name));
                $options[] = new FrontendMembersFieldInputSelectRoleOption($id, $index, $this->id, $value);
                $index++;
            }
        }

        return $options;
    }

    /**
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        echo parent::getOptionRow();
        echo ssv_get_td(ssv_get_options($this->id, self::getOptionsAsArray(), "role"));
        if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled"), array()));
        } else {
            echo ssv_get_hidden($this->id, "Display", $this->display);
        }
        if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        $content = ob_get_clean();

        return parent::getOptionRowInput($content);
    }

    private function getOptionsAsArray($names_only = false)
    {
        $array = array();
        if (count($this->options) > 0) {
            foreach ($this->options as $option) {
                if ($names_only) {
                    $array[] = $option->value;
                } else {
                    $array[] = array('id' => $option->id, 'type' => 'role', 'value' => $option->value);
                }
            }
        }

        return $array;
    }
}