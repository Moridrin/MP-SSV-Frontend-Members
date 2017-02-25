<?php
/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 21-1-17
 * Time: 7:38
 */
if (!defined('ABSPATH')) {
    exit;
}

?>
<form method="post" action="#" enctype="multipart/form-data">
    <table class="form-table">
        <tr>
            <th scope="row">Columns to Export</th>
            <td>
                <?php
                $selected   = json_decode(get_option(SSV_Users::OPTION_USER_EXPORT_COLUMNS));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getInputFieldNames();
                echo SSV_General::getListSelect('field_names', $fieldNames, $selected);
                ?>
            </td>
        </tr>
        <tr>
            <th scope="row">Filters</th>
            <td>
                <table>
                    <?php $fields = SSV_Users::getInputFields(); ?>
                    <?php foreach ($fields as $field): ?>
                        <tr>
                            <?php echo $field->getFilterRow(); ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <script>mp_ssv_auto_enable_filter();</script>
            </td>
        </tr>
    </table>
    <?= SSV_General::getFormSecurityFields(SSV_Users::ADMIN_REFERER_EXPORT, false, false); ?>
    <input type="submit" name="save_export" id="save_export" class="button button-primary" value="Export">
</form>
