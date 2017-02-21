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

if (SSV_General::isValidPOST(SSV_Users::ADMIN_REFERER_EXPORT)) {
    if (ini_get('safe_mode') == false) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
    }
    $data   = array();
    $fields = SSV_General::sanitize($_POST['user_columns']);
    $users  = get_users();
    if (!$users) {
        return; //TODO Add Message;
    }

    $filename = get_bloginfo('name') . ' users ' . date('Y-m-d H:i:s');
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');
    header('Content-Type: text/csv; charset=' . get_option('blog_charset'), true);


    // build the document headers ##
    $headers = array();
    foreach ($fields as $key => $field) {
        $headers[] = '"' . $field . '"';
    }
    ob_end_flush();

    // get the value in bytes allocated for Memory via php.ini ##
    // @link http://wordpress.org/support/topic/how-to-exporting-a-lot-of-data-out-of-memory-issue
    $memory_limit = ini_get('memory_limit');
    $memory_limit = trim( $memory_limit );
    $last = strtolower($memory_limit[strlen($memory_limit)-1]);
    switch( $last ) {
        case 'g':
            $memory_limit *= 1024;
            break;
        case 'm':
            $memory_limit *= 1024;
            break;
        case 'k':
            $memory_limit *= 1024;
            break;
    }
    $memory_limit = $memory_limit * .75;

    // we need to disable caching while exporting because we export so much data that it could blow the memory cache
    // if we can't override the cache here, we'll have to clear it later...
    if (function_exists('override_function')) {
        override_function('wp_cache_add', '$key, $data, $group="", $expire=0', '');
        override_function('wp_cache_set', '$key, $data, $group="", $expire=0', '');
        override_function('wp_cache_replace', '$key, $data, $group="", $expire=0', '');
        override_function('wp_cache_add_non_persistent_groups', '$key, $data, $group="", $expire=0', '');
    } elseif (function_exists('runkit_function_redefine')) {
        runkit_function_redefine('wp_cache_add', '$key, $data, $group="", $expire=0', '');
        runkit_function_redefine('wp_cache_set', '$key, $data, $group="", $expire=0', '');
        runkit_function_redefine('wp_cache_replace', '$key, $data, $group="", $expire=0', '');
        runkit_function_redefine('wp_cache_add_non_persistent_groups', '$key, $data, $group="", $expire=0', '');
    }
    echo implode(',', $headers) . "\n";
    foreach ($users as $user) {
        // check if we're hitting any Memory limits, if so flush them out ##
        // per http://wordpress.org/support/topic/how-to-exporting-a-lot-of-data-out-of-memory-issue?replies=2
        if (memory_get_usage(true) > $memory_limit) {
            wp_cache_flush();
        }

        $data     = array();
        $userMeta = (array)get_user_meta($user->ID);
        foreach ($fields as $field) {
            if (isset($userMeta[$field])) {
                $value = $userMeta[$field][0];
            } else {
                $value = isset($user->{$field}) ? $user->{$field} : null;
            }
            if (is_array($value)) {
                $value = implode(';', $value);
            }
            $value  = SSV_General::sanitize($value);
            $data[] = '"' . str_replace('"', '""', $value) . '"';
        }
        echo implode(',', $data);
    }
    exit;
}
?>
<form method="post" action="#" enctype="multipart/form-data">
    <table class="form-table">
        <tr>
            <th scope="row">Columns to Export</th>
            <td>
                <?php
                $selected   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getInputFieldNames();
                $fieldCount = count($fieldNames) + 3;
                ?>
                <select size="<?= $fieldCount > 25 ? 25 : $fieldCount ?>" name="user_columns[]" multiple title="Columns to Export">
                    <?php foreach ($fieldNames as $fieldName): ?>
                        <option value="<?= $fieldName ?>"><?= $fieldName ?></option>
                    <?php endforeach; ?>
                    <option value="blank" disabled>--- WP Defaults ---</option>
                    <option value="wp_Role">Role</option>
                    <option value="wp_Posts">Posts</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">Filter</th>
            <td>
                <?php
                $selected   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
                $selected   = $selected ?: array();
                $fieldNames = SSV_Users::getInputFieldNames();
                $fieldCount = count($fieldNames) + 3;
                ?>
                <select size="<?= $fieldCount > 25 ? 25 : $fieldCount ?>" name="user_columns[]" multiple title="Columns to Display" disabled>
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
    <?= SSV_General::getFormSecurityFields(SSV_Users::ADMIN_REFERER_EXPORT, false, false); ?>
    <input type="submit" name="save_export" id="save_export" class="button button-primary" value="Export">
</form>