<?php

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

	/**
	 * FrontendMembersFieldInputCustom constructor.
	 *
	 * @param FrontendMembersFieldInput $field       is the parent field.
	 * @param string                    $input_type_custom
	 * @param bool                      $required    is true if this is a required input field.
	 * @param string                    $display     is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 * @param string                    $placeholder is the placeholder text that gives an example of what to enter.
	 */
	protected function __construct($field, $input_type_custom, $required, $display, $placeholder)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->input_type_custom = $input_type_custom;
		$this->required = $required;
		$this->display = $display;
		$this->placeholder = $placeholder;
	}

	/**
	 * @param int    $index       is an index that specifies the display (/tab) order for the field.
	 * @param string $title       is the title of this component.
	 * @param string $input_type  is the input type of the input field.
	 * @param string $name        is the name of the input field.
	 * @param bool   $required    is true if this is a required input field.
	 * @param string $display     is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 * @param string $placeholder is the placeholder text that gives an example of what to enter.
	 *
	 * @return FrontendMembersFieldInputCustom
	 */
	public static function create($index, $title, $input_type, $input_type_custom, $name, $required = false, $display = "normal", $placeholder = "")
	{
		return new FrontendMembersFieldInputCustom(parent::createInput($index, $title, $input_type, $name), $input_type_custom, $required, $display, $placeholder);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", $this->id, $this->required));
		echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled"), array()));
		echo mp_ssv_get_td(mp_ssv_get_text_input("Placeholder", $this->id, $this->placeholder));
		$content = ob_get_clean();

		return parent::getOptionRowInput($content, $this->input_type_custom);
	}

	/**
	 * @param FrontendMember $frontend_member
	 *
	 * @return string
	 */
	public function getHTML($frontend_member)
	{
		ob_start();
		$value = $frontend_member->getMeta($this->name);
		?>
		<div class="mui-textfield">
			<input type="<?php echo $this->input_type_custom; ?>" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" value="<?php echo $value; ?>" <?php echo $this->display; ?>
			       placeholder="<?php echo $this->placeholder; ?>" <?php if ($this->required == "yes") echo "required"; ?>/>
			<label><?php echo $this->title; ?></label>
		</div>
		<?php
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
	}
}