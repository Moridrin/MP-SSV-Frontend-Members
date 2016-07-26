<?php

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:10
 */
class FrontendMembersFieldInputTextCheckbox extends FrontendMembersFieldInput
{
	public $required;
	public $display;

	/**
	 * FrontendMembersFieldInputRoleCheckbox constructor.
	 *
	 * @param FrontendMembersFieldInput $field    is the parent field.
	 * @param string                    $required is the name of the role or the role itself associated with this checkbox.
	 * @param string                    $display  is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 */
	protected function __construct($field, $required, $display)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->required = $required;
		$this->display = $display;
	}

	/**
	 * @param int    $index     is an index that specifies the display (/tab) order for the field.
	 * @param string $title     is the title of this component.
	 * @param string $name      is the name of the input field.
	 * @param string $help_text is the name of the role or the role itself associated with this checkbox.
	 * @param string $display   is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 *
     * @return FrontendMembersFieldInputTextCheckbox
	 */
	public static function create($index, $title, $name, $help_text = "", $display = "normal")
	{
		return new FrontendMembersFieldInputTextCheckbox(parent::createInput($index, $title, 'role_checkbox', $name), $help_text, $display);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name, "text", array("required")));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", $this->id, $this->required));
		echo mp_ssv_get_td(mp_ssv_get_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
		echo mp_ssv_get_td('<div class="' . $this->id . '_empty"></div>');
		$content = ob_get_clean();

		return parent::getOptionRowInput($content);
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
			<input type="hidden" name="<?php echo $this->name; ?>" value="no"/>
			<div class="mui-checkbox">
				<label>
					<input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" value="yes" <?php if ($value == "yes") : echo "checked"; endif; ?>>
					<?php echo $this->title; ?>
				</label>
			</div>
			<?php
		} else {
			?>
			<input type="hidden" name="<?php echo $this->name; ?>" value="no"/>
			<label>
				<input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->name; ?>" value="yes" <?php if ($value == "yes") : echo "checked"; endif; ?>>
				<?php echo $this->title; ?>
			</label>
			<br/>
			<?php
		}

		return ob_get_clean();
	}

	public function save($remove = false)
	{
		parent::save($remove);
		global $wpdb;
		$table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "help_text", "meta_value" => $this->required),
			array('%d', '%s', '%s')
		);
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "display", "meta_value" => $this->display),
			array('%d', '%s', '%s')
		);
	}
}