<?php
use mp_ssv_general\custom_fields\Field;
use mp_ssv_general\SSV_General;
use mp_ssv_general\User;
use mp_ssv_users\SSV_Users;

if (!defined('ABSPATH')) {
    exit;
}

#region Columns
/**
 * @param      $url
 * @param User $user
 *
 * @return mixed
 */
function mp_ssv_users_custom_profile_url($url, $user)
{
    /** @var WP_Post[] $pages */
    $pages       = SSV_Users::getPagesWithTag(SSV_Users::TAG_PROFILE_FIELDS);
    $wildcardURL = null;
    foreach ($pages as $page) {
        $pageRole = get_post_meta($page->ID, SSV_Users::PAGE_ROLE_META, true);
        if ($pageRole == $user->roles[0]) {
            return get_permalink($page) . '?member=' . $user->ID;
        } elseif ($pageRole == -1) {
            $wildcardURL = get_permalink($page) . '?member=' . $user->ID;
        }
    }
    return $wildcardURL ?: $url;
}

add_filter(SSV_General::HOOK_USER_PROFILE_URL, 'mp_ssv_users_custom_profile_url', 10, 2);

function mp_ssv_users_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    if (get_option(SSV_Users::OPTION_USERS_PAGE_MAIN_COLUMN) == 'wordpress_default') {
        $column_headers['username'] = 'Username';
    } else {
        $column_headers['ssv_display_name'] = 'Member';
    }
    $selected_columns = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        if (mp_ssv_starts_with($column, 'wp_')) {
            $column                              = str_replace('wp_', '', $column);
            $column_headers[strtolower($column)] = $column;
        } else {
            $column_headers['ssv_' . $column] = Field::titleFromDatabase($column);
        }
    }
    return $column_headers;
}

add_action('manage_users_columns', 'mp_ssv_users_custom_user_columns');

function mp_ssv_users_custom_user_column_values($val, $column_name, $user_id)
{
    $user = User::getByID($user_id);
    if ($column_name == 'ssv_display_name') {
        $actions   = array();
        $edit_link = esc_url(add_query_arg('wp_http_referer', urlencode(wp_unslash($_SERVER['REQUEST_URI'])), get_edit_user_link($user->ID)));
        if (current_user_can('edit_user', $user->ID)) {
            $actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit') . '</a>';
        }
        if (!is_multisite() && get_current_user_id() != $user->ID && current_user_can('delete_user', $user->ID)) {
            $actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url("users.php?action=delete&amp;user=$user->ID", 'bulk-users') . "'>" . __('Delete') . "</a>";
        }
        if (is_multisite() && get_current_user_id() != $user->ID && current_user_can('remove_user', $user->ID)) {
            $actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url("users.php?action=remove&amp;user=$user->ID", 'bulk-users') . "'>" . __('Remove') . "</a>";
        }
        $actions = apply_filters('user_row_actions', $actions, $user);
        ob_start();
        ?>
        <?= get_avatar($user->ID, 32, '', '', array('extra_attr' => 'style="float: left; margin-right: 5px; margin-top: 1px;"')); ?>
        <strong><?= $user->getProfileLink('_blank') ?></strong><br/>
        <div class="row-actions">
            <?php foreach ($actions as $key => $action): ?>
                <span class="<?= $key ?>"><?= $action ?></span>
                <?= $action !== end($actions) ? ' | ' : '' ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    } elseif (mp_ssv_starts_with($column_name, 'ssv_')) {
        return $user->getMeta(str_replace('ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'mp_ssv_users_custom_user_column_values', 10, 3);

function mp_ssv_users_custom_sortable_user_columns($columns)
{
    $selected_columns   = json_decode(get_option(SSV_Users::OPTION_USER_COLUMNS));
    $selected_columns[] = 'display_name';
    $selected_columns   = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        if (mp_ssv_starts_with($column, 'wp_')) {
            $column                       = str_replace('wp_', '', $column);
            $columns[strtolower($column)] = $column;
        } else {
            $columns['ssv_' . $column] = $column;
        }
    }
    return $columns;
}

add_filter('manage_users_sortable_columns', 'mp_ssv_users_custom_sortable_user_columns');

/**
 * @param WP_User_Query $query
 *
 * @return WP_User_Query
 */
function mp_ssv_users_sort_request($query)
{
    if (!isset($_GET['orderby'])) {
        return $query;
    }
    if ($_GET['orderby'] == 'email'
        || $_GET['orderby'] == 'display_name'
        || $_GET['orderby'] == 'first_name'
        || $_GET['orderby'] == 'last_name'
        || $_GET['orderby'] == 'user_login'
    ) {
        return $query;
    }

    $query->query_from .= ' INNER JOIN wp_usermeta usermeta_order ON (wp_users.ID = usermeta_order.user_id AND (usermeta_order.meta_key = \'' . $_GET['orderby'] . '\'))';
    if (isset($_GET['order'])) {
        $query->query_orderby = 'ORDER BY usermeta_order.meta_value ' . $_GET['order'];
    } else {
        $query->query_orderby = 'ORDER BY usermeta_order.meta_value ' . 'ASC';
    }
    return $query;
}

add_filter('pre_user_query', 'mp_ssv_users_sort_request');
#endregion

#region Filters
#endregion

#region Export
function mp_ssv_users_bulk_action_export($actions)
{
    $actions['csv_export'] = 'Export';
    return $actions;
}

add_filter('bulk_actions-users', 'mp_ssv_users_bulk_action_export');

function mp_ssv_users_exporter($redirect_to, $doaction, $user_ids)
{
    if ($doaction !== 'csv_export') {
        return $redirect_to;
    }

    $fields = json_decode(get_option(SSV_Users::OPTION_USER_EXPORT_COLUMNS));
    if (empty($fields)) {
        $fields = SSV_Users::getInputFieldNames();
    }

    $users = array();
    foreach ($user_ids as $user_id) {
        $users[] = User::getByID($user_id);
    }
    if (!$users) {
        return $redirect_to;
    }

    SSV_Users::export($users, $fields);

    return $redirect_to;
}

add_filter('handle_bulk_actions-users', 'mp_ssv_users_exporter', 10, 3);
#endregion
