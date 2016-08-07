<?php
/**
 * User: moridrin
 * Date: 7-8-16
 * Time: 9:50
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //TODO Save the data from this page.
    global $options;
    update_option('mp_ssv_frontend_members_register_page', $_POST['mp_ssv_frontend_members_register_page']);
    if (isset($_POST['mp_ssv_frontend_members_show_admin_bar_front'])) {
        update_option('mp_ssv_frontend_members_show_admin_bar_front', 'true');
    } else {
        update_option('mp_ssv_frontend_members_show_admin_bar_front', 'false');
    }
    update_option('mp_ssv_recaptcha_site_key', $_POST['mp_ssv_recaptcha_site_key']);
    update_option('mp_ssv_recaptcha_secret_key', $_POST['mp_ssv_recaptcha_secret_key']);
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Main Column</th>
            <td>
                <select name="mp_ssv_frontend_members_main_column" title="Main Column">
                    <option value="plugin_default" <?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_main_column'))) == 'plugin_default') {
                        echo "selected";
                    } ?>>Plugin Default
                    </option>
                    <option value="wordpress_default"<?php if (esc_attr(stripslashes(get_option('mp_ssv_frontend_members_main_column'))) == 'wordpress_default') {
                        echo "selected";
                    } ?>>Wordpress Default
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Columns to Display</th>
            <td>
                <?php
                $selected = json_decode(get_option('mp_ssv_frontend_members_user_columns'));
                $selected = $selected ?: array();
                $fieldNames = FrontendMembersField::getAllFieldNames();
                ?>
                <select size="<?= count($fieldNames) ?>" name="mp_ssv_frontend_members_user_columns" multiple>
                    <?php
                    foreach ($fieldNames as $fieldName) {
                        echo '<option value="' . $fieldName . '" ';
                        if (in_array($fieldName, $selected)) {
                            echo 'selected';
                        }
                        echo '>' . $fieldName . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>