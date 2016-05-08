<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:08
 */

require_once 'FrontendMembersFieldInputRoleSelectOption.php';

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
		$this->name = $this->name . '_role_select';
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
			$option = new FrontendMembersFieldInputRoleSelectOption($option_field['id'], $option_field['field_index'], $this->id);
			$value = $wpdb->get_var(
				"SELECT meta_value
			FROM $table
			WHERE field_id = '$option->id'
			AND meta_key = 'value';"
			);
			$option->value = $value;
			$options[] = $option;
		}

		return $options;
	}

	public function getOptionsFromPOST($variables)
	{
		$options = array();
		$index = 0;
		foreach ($variables as $name => $value) {
			if (strpos($name, "_option") !== false) {
				$id = str_replace("option", "", str_replace("_", "", $name));
				$options[] = new FrontendMembersFieldInputRoleSelectOption($id, $index, $this->id, $value);
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
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name, "text", array("required")));
		echo mp_ssv_get_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
		echo mp_ssv_get_td(mp_ssv_get_options($this->id, self::getOptionsAsArray(), "role"));
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
				<label for="<?php echo $this->id; ?>"><?php echo $this->title; ?></label>
				<select id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>">
					<?php foreach ($this->options as $option) {
						echo $option->getHTML($value);
					}
					?>
				</select>
			</div>
			<?php
		} else {
			?>
			<label for="<?php echo $this->id; ?>"><?php echo $this->title; ?></label>
			<select id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>">
				<?php foreach ($this->options as $option) {
					echo $option->getHTML($value);
				}
				?>
			</select>
			<br/>
			<?php
		}

		return ob_get_clean();
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
			$option->save($remove);
		}

		return $remove;
	}
}