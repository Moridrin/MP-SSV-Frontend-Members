<?php
if (!defined('ABSPATH')) {
    exit;
}
session_start();

/**
 * This function redirects the user to the login page if he/she is not signed in.
 */
function ssv_profile_page_login_redirect()
{
    global $post;
    if ($post == null) {
        return;
    }
    $post_name_correct = $post->post_name == 'profile';
    if (!is_user_logged_in() && $post_name_correct) {
        wp_redirect("/login");
        exit;
    }
}

add_action('wp_head', 'ssv_profile_page_login_redirect', 9);

/**
 * This function sets up the profile page.
 *
 * @param string $content is the post content.
 *
 * @return string the edited post content.
 */
function ssv_profile_page_setup($content)
{
    if (strpos($content, '[ssv-frontend-members-profile]') === false) { //Not the Profile Page Tag
        return $content;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_image']) && check_admin_referer('ssv_remove_image_from_profile')) {
        global $wpdb;
        $field_id       = $_POST['remove_image'];
        $table          = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $image_name     = $wpdb->get_var("SELECT meta_value FROM $table WHERE field_id = $field_id AND meta_key = 'name'");
        $frontendMember = FrontendMember::get_by_id($_POST['user_id']);
        unlink($frontendMember->getMeta($image_name . '_path'));
        $frontendMember->updateMeta($image_name, '');
        $frontendMember->updateMeta($image_name . '_path', '');
        echo 'image successfully removed success';
        return '';
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_save_frontend_member_profile')) {
        ssv_save_members_profile();
    }

    $currentUserIsBoardMember = FrontendMember::get_current_user()->isBoard();
    if (!isset($_GET['user_id']) || $currentUserIsBoardMember) {
        $content = ssv_profile_page_content();
    } else {
        $content = new Message('You have no access to view this profile', Message::ERROR_MESSAGE);
    }

    return $content;
}

/**
 * @return string the content of the Profile Page.
 */
function ssv_profile_page_content()
{
    if (isset($_GET['user_id'])) {
        $member     = get_user_by('id', $_GET['user_id']);
        $action_url = '/profile/?user_id=' . $member->ID;
    } else {
        $member     = wp_get_current_user();
        $action_url = '/profile/';
    }
    $can_edit = ($member == wp_get_current_user() || current_user_can('edit_user'));

    $member = new FrontendMember($member);

    $_SESSION["ABSPATH"]         = ABSPATH;
    $_SESSION["first_name"]      = $member->first_name;
    $_SESSION["initials"]        = $member->getMeta('initials');
    $_SESSION["last_name"]       = $member->last_name;
    $_SESSION["gender"]          = $member->getMeta('gender');
    $_SESSION["iban"]            = $member->getMeta('iban');
    $_SESSION["date_of_birth"]   = $member->getMeta('date_of_birth');
    $_SESSION["street"]          = $member->getMeta('street');
    $_SESSION["email"]           = $member->getMeta('email');
    $_SESSION["postal_code"]     = $member->getMeta('postal_code');
    $_SESSION["city"]            = $member->getMeta('city');
    $_SESSION["phone_number"]    = $member->getMeta('phone_number');
    $_SESSION["emergency_phone"] = $member->getMeta('emergency_phone');

    if (current_theme_supports('mui')) {
        $tabs = FrontendMembersField::getTabs();
        if (count($tabs) > 0) {
            $content = '<div class="mui--hidden-xs">';
            $content .= ssv_profile_page_content_tabs($member, $can_edit, $action_url);
            $content .= '</div>';
            $content .= '<div class="mui--visible-xs-block">';
            $content .= ssv_profile_page_content_single_page($member, $can_edit);
            $content .= '</div>';
        } else {
            $content = ssv_profile_page_content_single_page($member, $can_edit);
        }
    } else {
        $content = ssv_profile_page_content_single_page($member, $can_edit);
    }

    return $content;
}

/**
 * @param FrontendMember $member
 * @param string         $action_url
 * @param bool           $can_edit
 *
 * @return string
 */
function ssv_profile_page_content_tabs($member, $can_edit = false, $action_url = '/profile/')
{
    ob_start();
    echo ssv_get_profile_page_tab_select($member);
    $tabs = FrontendMembersField::getTabs();
    foreach ($tabs as $tab) {
        if ($tabs[0] == $tab) {
            $active_class = "mui--is-active";
        } else {
            $active_class = "";
        }
        ?>
        <div class="mui-tabs__pane <?php echo esc_html($active_class); ?>" id="pane-<?php echo esc_html($tab->id); ?>">
            <form name="members_<?php echo esc_html($tab->title); ?>_form" id="member_<?php echo esc_html($tab->title); ?>_form" action="<?php echo esc_html($action_url) ?>" method="post" enctype="multipart/form-data">
                <?php
                $items_in_tab = FrontendMembersField::getItemsInTab($tab);
                foreach ($items_in_tab as $item) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    echo $item->getHTML($member);
                }
                ?>
                <?php
                if ($can_edit) {
                    wp_nonce_field('ssv_save_frontend_member_profile');
                    ?>
                    <button class="mui-btn mui-btn--primary button-primary" type="submit" name="submit" id="submit">Save</button>
                    <?php
                }
                ?>
            </form>
        </div>
        <?php
    }
    ?>
    <a href="<?php echo get_site_url() . '/wp-content/plugins/ssv-frontend-members/frontend-pages/direct-debit-pdf.php'; ?>" target="_blank" class="mui-btn mui-btn--primary button-primary">Direct Debit PDF</a>
    <?php

    return ob_get_clean();
}

/**
 * @param FrontendMember $member
 * @param bool           $can_edit
 *
 * @return string
 */
function ssv_profile_page_content_single_page($member, $can_edit = false)
{
    ob_start();
    $items = FrontendMembersField::getAll();
    ?>
    <!--suppress HtmlUnknownTarget -->
    <form name="members_form" id="members_form" action="/profile" method="post" enctype="multipart/form-data">
        <?php
        foreach ($items as $item) {
            if (!$item instanceof FrontendMembersFieldTab) {
                /** @noinspection PhpUndefinedMethodInspection */
                echo $item->getHTML($member);
            }
        }
        if ($can_edit) {
            wp_nonce_field('ssv_save_frontend_member_profile');
            echo '<button class="mui-btn mui-btn--primary button-primary" type="submit" name="submit" id="submit">Save</button>';
        }
        ?>
    </form>
    <a href="<?php echo get_site_url() . '/wp-content/plugins/ssv-frontend-members/frontend-pages/direct-debit-pdf.php'; ?>" target="_blank" class="mui-btn mui-btn--primary button-primary">Direct Debit PDF</a><br/>
    <?php
    if ($member->isCurrentUser()) {
        $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
        echo '<button type="button" class="mui-btn mui-btn--flat mui-btn--danger" href="' . wp_logout_url($url) . '" >Logout</button>';
    }

    return ob_get_clean();
}

/**
 * @param FrontendMember $member is to define if the logout button should be displayed.
 *
 * @return string containing a mui-tabs__bar.
 */
function ssv_get_profile_page_tab_select($member)
{
    ob_start();
    $tabs = FrontendMembersField::getTabs();
    echo '<ul id="profile-menu" class="mui-tabs__bar mui-tabs__bar--justified">';
    for ($i = 0; $i < count($tabs); $i++) {
        $tab = $tabs[$i];
        if ($tab instanceof FrontendMembersFieldTab) {
            if ($i == 0) {
                echo $tab->getTabButton(true);
            } else {
                echo $tab->getTabButton();
            }
        }
    }
    if ($member->isCurrentUser()) {
        $url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?logout=success';
        echo '<li><a class="mui-btn mui-btn--flat mui-btn--danger" href="' . wp_logout_url($url) . '">Logout</a></li>';
    }
    echo '</ul>';

    return ob_get_clean();
}

function ssv_save_members_profile()
{
    if (isset($_GET['user_id'])) {
        $user = get_user_by('id', $_GET['user_id']);
    } else {
        $user = wp_get_current_user();
    }
    $user = new FrontendMember($user);
    foreach ($_POST as $name => $val) {
        if (strpos($name, "_reset") !== false) {
            $name = str_replace("_reset", "", $name);
        }
        $update_response = $user->updateMeta($name, $val);
        if ($update_response !== true) {
            $update_response->htmlPrint();
        }
    }
    foreach ($_FILES as $name => $file) {
        if ($file['size'] > 0) {
            if (!function_exists('wp_handle_upload')) {
                /** @noinspection PhpIncludeInspection */
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            $file_location = wp_handle_upload($file, array('test_form' => false));
            if ($file_location && !isset($file_location['error'])) {
                if ($user->getMeta($name) != '' && $user->getMeta($name) != $file_location['url']) {
                    unlink($user->getMeta($name . '_path'));
                    $user->updateMeta($name, $file_location["url"]);
                    $user->updateMeta($name . '_path', $file_location["file"]);
                } elseif ($user->getMeta($name) != $file_location['url']) {
                    $user->updateMeta($name, $file_location["url"]);
                    $user->updateMeta($name . '_path', $file_location["file"]);
                }
            }
        }
    }
    /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (is_plugin_active('ssv-mailchimp/ssv-mailchimp.php')) {
        ssv_update_mailchimp_member($user);
    }
    unset($_POST);
}

add_filter('the_content', 'ssv_profile_page_setup');

?>