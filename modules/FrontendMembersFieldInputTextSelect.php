<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */

class FrontendMembersFieldInputTextSelect extends FrontendMembersFieldInput
{
	public $options;
	public $display;

	/**
	 * FrontendMembersFieldInputTextSelect constructor.
	 *
	 * @param FrontendMembersFieldInput $field   is the parent field.
	 * @param array                     $options is an array with all the options for the select field.
	 * @param string                    $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 */
	protected function __construct($field, $options, $display)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->options = $options;
		$this->display = $display;
	}

	/**
	 * @param int    $index   is an index that specifies the display (/tab) order for the field.
	 * @param string $title   is the title of this component.
	 * @param string $name    is the name of the input field.
	 * @param array  $options is an array with all the options for the select field.
	 * @param string $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 *
	 * @return FrontendMembersFieldInputRoleCheckbox
	 */
	public static function create($index, $title, $name, $options = array(), $display = "normal")
	{
		return new FrontendMembersFieldInputTextSelect(parent::createInput($index, $title, 'role_select', $name), $options, $display);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name));
		echo mp_ssv_get_td('<div class="'.$this->id.'_empty"></div>');
		echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
		echo mp_ssv_get_td(mp_ssv_get_options($this->id, $this->options, "text"));
		$content = ob_get_clean();
		return parent::getOptionRowInput($content);
	}

	public function save()
	{
		parent::save();
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
			array('%d', '%s', '%s')
		);
		$this->saveOptions();
	}
}