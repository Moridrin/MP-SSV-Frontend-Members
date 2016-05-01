<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */

class FrontendMembersFieldInputRoleSelect extends FrontendMembersFieldInput
{
	public $options;
	public $display;

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
		return new FrontendMembersFieldInputRoleSelect(parent::createInput($index, $title, 'role_select', $name), $options, $display);
	}

	/**
	 * This function returns all the group options for this field.
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

		//Get Option Fields
		$table = FRONTEND_MEMBERS_FIELDS_TABLE_NAME;
		$sql = "SELECT * FROM $table WHERE field_type = 'group_option' AND (";
		for ($i = 0; $i < count($option_ids); $i++) {
			if ($i != 0) {
				$sql .= " OR ";
			}
			$sql .= "id = " . $option_ids[$i]["id"];
		}
		$sql .= ") ORDER BY id ASC;";
		$option_fields = $wpdb->get_results($sql);

		//Create Options and Get Value
		$options = array();
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		foreach ($option_fields as $group_field) {
			$option = new FrontendMembersFieldInputTextSelectOption($group_field['id'], $group_field['field_index']);
			$option->value = $wpdb->get_var(
				"SELECT id
			FROM $table
			WHERE meta_key = 'field_id'
			AND meta_value = '$option->id';"
			);
			$options[] = $option;
		}
		mp_ssv_print($options);

		return $options;
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name, "text", array("required")));
		echo mp_ssv_get_td('<div class="'.$this->id.'_empty"></div>');
		echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
		echo mp_ssv_get_td(mp_ssv_get_options($this->id, $this->options, "role"));
		$content = ob_get_clean();
		return parent::getOptionRowInput($content);
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
		//TODO Save options
	}
}