<?php
namespace mp_ssv_users\options;
use mp_ssv_general\SSV_General;
use mp_ssv_users\SSV_Users;

if (!defined('ABSPATH')) {
    exit;
}

if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_OPTIONS)) {
    if (isset($_POST['reset'])) {
        SSV_Users::resetOptions();
    } else {
        update_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN, SSV_General::sanitize($_POST['users_page_main_column']));
        update_option(SSV_Users::OPTION_USER_COLUMNS, json_encode(isset($_POST['user_columns']) ? $_POST['user_columns'] : ''));
    }
}
?>
<form method="post" action="#">
    <table class="form-table">
        <tr>
            <th scope="row">Main Column</th>
            <td>
                <select name="users_page_main_column" title="Main Column">
                    <option value="plugin_default" <?= get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'plugin_default' ? 'selected' : '' ?>>Plugin Default</option>
                    <option value="wordpress_default"<?= get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'wordpress_default' ? 'selected' : '' ?>>WordPress Default</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Columns to Display</th>
            <td>
                <?php
                $selected   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getInputFieldNames();
                $fieldCount = count($fieldNames) + 3;
                ?>
                <select size="<?= $fieldCount > 25 ? 25 : $fieldCount ?>" name="user_columns[]" multiple title="Columns to Display">
                    <?php foreach ($fieldNames as $fieldName): ?>
                        <option value="<?= $fieldName ?>" <?= in_array($fieldName, $selected) ? 'selected' : '' ?>><?= $fieldName ?></option>
                    <?php endforeach; ?>
                    <option value="blank" disabled>--- WP Defaults ---</option>
                    <option value="wp_Role" <?= in_array('wp_Role', $selected) ? 'selected' : '' ?>>Role</option>
                    <option value="wp_Posts" <?= in_array('wp_Posts', $selected) ? 'selected' : '' ?>>Posts</option>
                </select>
            </td>
        </tr>
    </table>
    <?= SSV_General::getFormSecurityFields(SSV_Users::ADMIN_REFERER_OPTIONS); ?>
</form>
