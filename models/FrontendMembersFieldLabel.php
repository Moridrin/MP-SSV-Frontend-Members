<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 11-11-2016
 * Time: 06:45
 */
class FrontendMembersFieldLabel extends FrontendMembersField
{

    public $text;

    /**
     * FrontendMembersFieldLabel constructor.
     *
     * @param FrontendMembersField $field is the parent field.
     * @param int                  $text is the main text in the label.
     */
    protected function __construct($field, $text)
    {
        parent::__construct($field->id, $field->index, $field->type, $field->title, $field->registration_page, $field->class, $field->style);
        $this->text = $text;
    }

    /**
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow()
    {
        ob_start();
        $colspan = 3;
        if (get_option('ssv_frontend_members_view_display__preview_column', 'true') == 'true') {
            $colspan++;
        }
        if (get_option('ssv_frontend_members_view_default_column', 'true') == 'true') {
            $colspan++;
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', 'true') == 'true') {
            $colspan++;
        }
        echo ssv_get_td(ssv_get_text_area("Text", $this->id, $this->text, "text", array("required"), false), $colspan);
        $content = ob_get_clean();

        return parent::getOptionRowField($content);
    }

    public function getHTML()
    {
        ob_start();
        ?><div class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>"><?php echo $this->text; ?></div><?php
        return ob_get_clean();
    }

    public function save($remove = false)
    {
        parent::save($remove);
        global $wpdb;
        $table = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $wpdb->replace(
            $table,
            array("field_id" => $this->id, "meta_key" => "text", "meta_value" => $this->text),
            array('%d', '%s', '%s')
        );
    }
}