<?php

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */
require_once 'FrontendMembersFieldInputSelectTextOption.php';

class FrontendMembersFieldInputSelectText extends FrontendMembersFieldInputSelect
{
	/**
	 * FrontendMembersFieldInputTextSelect constructor.
	 *
	 * @param FrontendMembersFieldInput $field   is the parent field.
	 * @param string                    $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 */
	protected function __construct($field, $display)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->display = $display;
	}

	/**
	 * @param int    $index   is an index that specifies the display (/tab) order for the field.
	 * @param string $title   is the title of this component.
	 * @param string $name    is the name of the input field.
	 * @param array  $options is an array with all the options for the select field.
	 * @param string $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 *
     * @return FrontendMembersFieldInputSelectText
	 */
	public static function create($index, $title, $name, $options = array(), $display = "normal")
	{
        return new FrontendMembersFieldInputSelectText(parent::createInput($index, $title, 'role_select', $name), $options, $display);
	}

	public function getOptionsFromPOST($variables)
	{
		$options = array();
		$index = 0;
		foreach ($variables as $name => $value) {
			if (strpos($name, "_option") !== false) {
				$id = str_replace("option", "", str_replace("_", "", $name));
                $options[] = new FrontendMembersFieldInputSelectTextOption($id, $index, $this->id, $value);
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
		echo mp_ssv_get_td(mp_ssv_get_options($this->id, self::getOptionsAsArray(), "text"));
        echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
        if (get_option('mp_ssv_view_advanced_profile_page', false)) {
            echo mp_ssv_get_td('<div class="' . $this->id . '_empty"></div>');
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
					$array[] = array('id' => $option->id, 'type' => 'text', 'value' => $option->value);
				}
			}
		}

		return $array;
	}
}