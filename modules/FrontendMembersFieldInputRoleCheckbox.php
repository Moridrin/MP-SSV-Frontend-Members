<?php
/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:10
 */

class FrontendMembersFieldInputRoleCheckbox extends FrontendMembersFieldInput
{
	public $role;
	public $display;

	/**
	 * FrontendMembersFieldInputRoleCheckbox constructor.
	 *
	 * @param FrontendMembersFieldInput $field   is the parent field.
	 * @param string|\WP_Role           $role    is the name of the role or the role itself associated with this checkbox.
	 * @param string                    $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 */
	protected function __construct($field, $role, $display)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->role = $role;
		$this->display = $display;
	}

	/**
	 * @param int             $index   is an index that specifies the display (/tab) order for the field.
	 * @param string          $title   is the title of this component.
	 * @param string          $name    is the name of the input field.
	 * @param string|\WP_Role $role    is the name of the role or the role itself associated with this checkbox.
	 * @param string          $display is the way the input field is displayed (readonly, disabled or normal) default is normal.
	 *
	 * @return FrontendMembersFieldInputRoleCheckbox
	 */
	public static function create($index, $title, $name, $role = "", $display = "normal")
	{
		return new FrontendMembersFieldInputRoleCheckbox(parent::createInput($index, $title, 'role_checkbox', $name), $role, $display);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_td('<div class="' . $this->id . '_empty"></div>');
		echo mp_ssv_td(mp_ssv_select("Display", $this->id, $this->display, array("Normal", "ReadOnly", "Disabled")));
		echo mp_ssv_td(mp_ssv_role_select($this->id, "Role", $this->role));
		$content = ob_get_clean();
		return parent::getOptionRowInput($content);
	}
}