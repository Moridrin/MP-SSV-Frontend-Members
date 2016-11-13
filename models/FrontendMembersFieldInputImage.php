<?php
if (!defined('ABSPATH')) {
    exit;
}

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
        $this->preview  = $preview;
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
        }
        if ($frontend_member == null) {
            return $this->required == "yes";
        } else {
            $location = $frontend_member->getMeta($this->name);
            return $this->required == "yes" && $location == "";
        }
    }

    /**
     * This field is always editable.
     *
     * @return bool returns true.
     */
    public function isEditable()
    {
        return true;
    }

    /**
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        echo ssv_get_td(ssv_get_text_input("Name", $this->id, $this->name, 'text', array('required')));
        echo ssv_get_td(ssv_get_checkbox("Required", $this->id, $this->required));
        if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
            echo ssv_get_td(ssv_get_checkbox("Preview", $this->id, $this->preview));
        } else {
            echo ssv_get_hidden($this->id, "Display", $this->preview);
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

    /**
     * @param FrontendMember $frontend_member
     * @param int            $size
     *
     * @return string the HTML element
     */
    public
    function getHTML(
        $frontend_member = null,
        $size = 150
    ) {
        ob_start();
        if ($frontend_member == null) {
            $location      = "";
            $this->preview = "no";
        } else {
            $location = $frontend_member->getMeta($this->name);
        }
        if (current_theme_supports('mui')) {
            echo '<div class="mui-textfield">';
        }
        echo '<label>' . $this->title . '</label>';
        if ($this->required == "yes" && $location == "") {
            echo '<input type="file" id="' . $this->id . '" name="' . $this->name . '" class="' . $this->class . '" style="' . $this->style . '" required/>';
        } else {
            echo '<input type="file" id="' . $this->id . '" name="' . $this->name . '" class="' . $this->class . '" style="' . $this->style . '" />';
        }
        if ($this->preview == "yes" && $location != '') {
            echo '<img id="' . $this->id . '_preview" src="' . esc_url($location) . '" style="padding-top: 10px;" height="' . $size . '" width="' . $size . '">';
        }
        if ($this->required == 'no' && $location != "") {
            ?>
            <br/>
            <button class="mui-btn mui-btn--accent button-accent" type="button" id="<?php echo $this->id; ?>_remove" name="<?php echo $this->id; ?>_remove">Remove</button>
            <script>
                var removeImageClickHandler = function (e) {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo wp_nonce_url('/profile', 'ssv_remove_image_from_profile'); ?>",
                        data: {
                            remove_image: <?php echo $this->id; ?>,
                            user_id: <?php echo $frontend_member->ID; ?>
                        },
                        success: function (data) {
                            if (data.indexOf("image successfully removed success") >= 0) {
                                $("#<?php echo $this->id; ?>_remove").remove();
                                <?php if ($this->preview == 'yes') { ?>
                                $("#<?php echo $this->id; ?>_preview").remove();
                                <?php } ?>
                            }
                        },
                        error: function (data) {
                            alert(data.responseText);
                        }
                    });
                    e.stopImmediatePropagation();
                    return false;
                };
                $('#<?php echo $this->id; ?>_remove').one('click', removeImageClickHandler);
            </script>
            <?php
        }
        if (current_theme_supports('mui')) {
            echo '</div>';
        } else {
            echo '<br/>';
        }

        return ob_get_clean();
    }

    public
    function save(
        $remove = false
    ) {
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