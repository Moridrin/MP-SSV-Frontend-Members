<?php

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:10
 */
class FrontendMembersFieldInputImage extends FrontendMembersFieldInput
{
	public $required;
	public $preview;

	/**
	 * FrontendMembersFieldInputImage constructor.
	 *
	 * @param FrontendMembersFieldInput $field    is the parent field.
	 * @param bool                      $required is true if this is a required input field.
	 * @param bool                      $preview  is true if the already set image should be displayed as preview.
	 */
	protected function __construct($field, $required, $preview)
	{
		parent::__construct($field, $field->input_type, $field->name);
		$this->required = $required;
		$this->preview = $preview;
	}

	/**
	 * @param int    $index    is an index that specifies the display (/tab) order for the field.
	 * @param string $title    is the title of this component.
	 * @param string $name     is the name of the input field.
	 * @param bool   $required is true if this is a required input field.
	 * @param bool   $preview  is true if the already set image should be displayed as preview.
	 *
	 * @return FrontendMembersFieldInputImage
	 */
	public static function create($index, $title, $name, $required = false, $preview = true)
	{
		return new FrontendMembersFieldInputImage(parent::createInput($index, $title, 'image', $name), $required, $preview);
	}

	/**
	 * @return string row that can be added to the profile page options table.
	 */
	public function getOptionRow()
	{
		ob_start();
		echo mp_ssv_get_td(mp_ssv_get_text_input("Name", $this->id, $this->name));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Required", $this->id, $this->required));
		echo mp_ssv_get_td(mp_ssv_get_checkbox("Preview", $this->id, $this->preview));
        if (get_option('mp_ssv_view_advanced_profile_page', false)) {
            echo mp_ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
		$content = ob_get_clean();

		return parent::getOptionRowInput($content);
	}

    /**
     * @param FrontendMember $frontend_member
     * @param int            $size
     *
     * @return string the HTML element
     */
	public function getHTML($frontend_member = null, $size = 150)
	{
		ob_start();
		if ($frontend_member == null) {
			$location = "";
			$this->preview = "no";
		} else {
			$location = $frontend_member->getMeta($this->name);
		}
		if (current_theme_supports('mui')) {
			echo '<div class="mui-textfield">';
		}
		echo '<label>'.$this->title.'</label>';
		if ($this->required == "yes" && $location == "") {
            echo '<input type="file" id="' . $this->id . '" name="' . $this->name . '" class="' . $this->class . '" style="' . $this->style . '" required/>';
		} else {
            echo '<input type="file" id="' . $this->id . '" name="' . $this->name . '" class="' . $this->class . '" style="' . $this->style . '" />';
		}
		if ($this->preview == "yes") {
			echo '<img src="' . $location . '" style="padding-top: 10px;" height="' . $size . '" width="' . $size . '">';
		}
        if ($this->required == 'no' && $location != "") {
            echo '<br/><button class="mui-btn mui-btn--accent button-accent" type="button" id="' . $this->id . '_remove" name="' . $this->name . '_remove">Remove</button>';
        }
		if (current_theme_supports('mui')) {
			echo '</div>';
		} else {
			echo '<br/>';
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
			array("field_id" => $this->id, "meta_key" => "required", "meta_value" => $this->required),
			array('%d', '%s', '%s')
		);
		$wpdb->replace(
			$table,
			array("field_id" => $this->id, "meta_key" => "preview", "meta_value" => $this->preview),
			array('%d', '%s', '%s')
		);
	}
}