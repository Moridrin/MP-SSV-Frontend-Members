<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 16:01
 */
class FrontendMembersFieldHeader extends FrontendMembersField
{

    /**
     * FrontendMembersFieldHeader constructor.
     *
     * @param FrontendMembersField $field is the parent field.
     */
    protected function __construct($field)
    {
        parent::__construct($field->id, $field->index, $field->type, $field->title, $field->registrationPage, $field->class, $field->style);
    }

    /**
     * @param mixed $var is needed for some subclass implementations of this function.
     *
     * @return string row that can be added to the profile page options table.
     */
    public function getOptionRow($var = null)
    {
        ob_start();
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        if (get_option('ssv_frontend_members_view_display_preview_column', true)) {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        if (get_option('ssv_frontend_members_view_default_column', true)) {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        if (get_option('ssv_frontend_members_view_placeholder_column', true)) {
            echo ssv_get_td('<div class="' . $this->id . '_empty"></div>');
        }
        $content = ob_get_clean();

        return parent::getOptionRowField($content);
    }

    public function getHTML(
        /** @noinspection PhpUnusedParameterInspection */
        $var = null
    ) {
        ob_start();
        ?>
        <div class="col s12">
            <h1 id="<?php echo $this->id; ?>" class="<?php echo $this->class; ?>" style="<?php echo $this->style; ?>"><?php echo $this->title; ?></h1>
        </div>
        <?php

        return trim(preg_replace('/\s\s+/', ' ', ob_get_clean()));
    }

    public function save($remove = false)
    {
        parent::save($remove);
    }
}