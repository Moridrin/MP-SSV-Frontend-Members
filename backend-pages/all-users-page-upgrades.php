<?php
if (!defined('ABSPATH')) {
    exit;
}

function ssv_custom_user_column_values($val, $column_name, $user_id)
{
    $frontendMember = FrontendMember::get_by_id($user_id);
    if ($column_name == 'ssv_member') {
        $username_block = '';
        $username_block .= '<img style="float: left; margin-right: 10px; margin-top: 1px;" class="avatar avatar-32 photo" src="' . esc_url($frontendMember->getMeta('profile_picture')) . '" height="32" width="32"/>';
        $username_block .= '<strong>' . $frontendMember->getProfileLink('_blank') . '</strong><br/>';
        $directDebitPDF  = $frontendMember->getProfileURL() . '&view=directDebitPDF';
        $editURL         = 'user-edit.php?user_id=' . $frontendMember->ID . '&wp_http_referer=%2Fwp-admin%2Fusers.php';
        $capebilitiesURL = 'users.php?page=users-user-role-editor.php&object=user&user_id=' . $frontendMember->ID;
        $username_block .= '<div class="row-actions"><span class="direct_debit_pdf"><a href="' . esc_url($directDebitPDF) . '" target="_blank">PDF</a> | </span><span class="edit"><a href="' . esc_url($editURL) . '">Edit</a> | </span><span class="capabilities"><a href="' . esc_url($capebilitiesURL) . '">Capabilities</a></span></div>';
        return $username_block;
    } elseif (ssv_starts_with($column_name, 'ssv_')) {
        return $frontendMember->getMeta(str_replace('ssv_', '', $column_name));
    }
    return $val;
}

add_filter('manage_users_custom_column', 'ssv_custom_user_column_values', 10, 3);

function ssv_custom_user_columns($column_headers)
{
    unset($column_headers);
    $column_headers['cb'] = '<input type="checkbox" />';
    global $wpdb;
    if (get_option('ssv_frontend_members_main_column') == 'wordpress_default') {
        $column_headers['username'] = 'Username';
    } else {
        $url = $_SERVER['REQUEST_URI'];
        if (empty($_GET)) {
            $url .= '?orderby=name';
        } elseif (!isset($_GET['orderby'])) {
            $url .= '&orderby=name';
        } elseif (!isset($_GET['order'])) {
            $url .= '&order=DESC';
        } elseif ($_GET['order'] == 'DESC') {
            $url .= '&order=ASC';
        } else {
            $url .= '&order=DESC';
        }
        $column_headers['ssv_member'] = '<a href="' . $url . '">Member</a>';
    }
    $selected_columns = json_decode(get_option('ssv_frontend_members_user_columns'));
    $selected_columns = $selected_columns ?: array();
    foreach ($selected_columns as $column) {
        $sql   = 'SELECT field_id FROM ' . FRONTEND_MEMBERS_FIELD_META_TABLE_NAME . ' WHERE meta_key = "name" AND meta_value = "' . $column . '"';
        $sql   = 'SELECT field_title FROM ' . FRONTEND_MEMBERS_FIELDS_TABLE_NAME . ' WHERE id = (' . $sql . ')';
        $title = $wpdb->get_var($sql);
        if (ssv_starts_with($column, 'wp_')) {
            $column                              = str_replace('wp_', '', $column);
            $column_headers[strtolower($column)] = $column;
        } else {
            $column_headers['ssv_' . $column] = $title;
        }
    }
    return $column_headers;
}

add_action('manage_users_columns', 'ssv_custom_user_columns');

function ssv_include_custom_user_filter_fields()
{
    if (strpos($_SERVER['REQUEST_URI'], 'users.php') === false || get_option('ssv_frontend_members_custom_users_filters', 'under') == 'hide') {
        return;
    }
    $fields = FrontendMembersField::getAll(array('field_type' => 'input'));
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach ($fields as $field) {
            /** @var FrontendMembersFieldInput $field */
            if (isset($_POST['clear_filters']) || !isset($_POST['filter_' . $field->name]) || empty($_POST['filter_' . $field->name])) {
                unset($_SESSION['filter_' . $field->name]);
            } else {
                $_SESSION['filter_' . $field->name] = $_POST['filter_' . $field->name];
            }
        }
        if (isset($_GET['paged']) && $_GET['paged'] > 1) {
            $uri = $_SERVER['REQUEST_URI'];
            $uri = str_replace('paged=' . $_GET['paged'], 'paged=1', $uri);
            ssv_redirect($uri);
        }
    }
    $filters     = '';
    $selected    = json_decode(get_option('ssv_frontend_members_user_filters'));
    $selected    = $selected ?: array();
    $addedFields = array();
    foreach ($fields as $field) {
        /** @var FrontendMembersFieldInput $field */
        if (in_array($field->name, $selected) && !in_array($field->name, $addedFields)) {
            $filters .= '<div style="display: inline-block; margin-right: 6px;">';
            $filters .= $field->getFilter();
            $filters .= '</div>';
            $addedFields[] = $field->name;
        }
    }
    $filters .= '<br/><button type="submit" value="submit" class="button" style="margin-right: 6px;">Filter</button>';
    $filters .= '<button type="submit" name="clear_filters" value="clear_filters" class="button">Clear Filters</button>';
    ?>
    <script>
        window.onload = function () {
            jQuery(document).ready(function ($) {
                var old_filter_area = $('.subsubsub');
                old_filter_area.before('<h2 style="margin-bottom: 0;">Filters</h2>');
                old_filter_area.after('<form name="filter_form" method="post"><div id="filter_area"></div></form>');
                <?php if (get_option('ssv_frontend_members_custom_users_filters', 'under') == 'replace'): ?>
                old_filter_area.remove();
                <?php endif; ?>
                var filter_area = $('#filter_area');
                filter_area.html('<?php echo $filters; ?>');
            });
        };
    </script>
    <?php
}

add_action('admin_init', 'ssv_include_custom_user_filter_fields');

function ssv_custom_user_filters($query)
{
    if (strpos($_SERVER['REQUEST_URI'], 'users.php') === false) {
        return $query;
    }
    global $wpdb;
    $filtered = array();
    $fields   = FrontendMembersField::getAll(array('field_type' => 'input'));
    foreach ($fields as $field) {
        /** @var FrontendMembersFieldInput $field */
        if (isset($_SESSION['filter_' . $field->name]) && !in_array($field->name, $filtered)) {
            $value = $_SESSION['filter_' . $field->name];
            switch (get_class($field)) {
                case FrontendMembersFieldInputCustom::class:
                case FrontendMembersFieldInputText::class:
                    $table_alias = $field->name . 'meta';
                    $query->query_from .= " JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$field->name}'";
                    if (strpos($value, '<') !== false) {
                        $value = str_replace('<', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value < '{$value}'";
                    } elseif (strpos($value, '>') !== false) {
                        $value = str_replace('>', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value > '{$value}'";
                    } elseif (strpos($value, '!') !== false && (strpos($value, "'") !== false || strpos($value, '"') !== false)) {
                        $value = str_replace('!', '', $value);
                        $value = str_replace("'", '', $value);
                        $value = str_replace('"', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value != '{$value}'";
                    } elseif (strpos($value, '!') !== false) {
                        $value = str_replace('!', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value NOT LIKE '%{$value}%'";
                    } elseif (strpos($value, "\\'") !== false || strpos($value, '\\"') !== false) {
                        $value = str_replace("\\'", '', $value);
                        $value = str_replace('\\"', '', $value);
                        $query->query_where .= " AND {$table_alias}.meta_value = '{$value}'";
                    } else {
                        $query->query_where .= " AND {$table_alias}.meta_value LIKE '%{$value}%'";
                    }
                    break;
                case FrontendMembersFieldInputImage::class:
                    $table_alias = $field->name . 'meta';
                    $query->query_from .= " LEFT OUTER JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$field->name}'";
                    if ($value == 'no') {
                        $query->query_where .= " AND profile_picturemeta.meta_key IS NULL";
                    } else {
                        $query->query_where .= " AND profile_picturemeta.meta_key = '" . $field->name . "'";
                    }
                    break;
                case FrontendMembersFieldInputSelect::class:
                case FrontendMembersFieldInputRoleCheckbox::class:
                case FrontendMembersFieldInputTextCheckbox::class:
                default:
                    $table_alias = $field->name . 'meta';
                    $query->query_from .= " JOIN {$wpdb->usermeta} {$table_alias} ON {$table_alias}.user_id = {$wpdb->users}.ID AND {$table_alias}.meta_key = '{$field->name}'";
                    $query->query_where .= " AND {$table_alias}.meta_value LIKE '{$value}'";
                    break;
            }
            $filtered[] = $field->name;
        }
    }
    return $query;
}

add_filter('pre_user_query', 'ssv_custom_user_filters');