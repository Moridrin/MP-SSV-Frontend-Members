<?php
#region Authentication
if (!defined('ABSPATH')) {
    exit;
}
session_start();

/**
 * This function redirects the user to the login page if he/she is not signed in.
 */
function mp_ssv_profile_page_login_redirect()
{
    global $post;
    if ($post == null) {
        return;
    }
    $postNameCorrect = $post->post_name == 'profile';
    if (!is_user_logged_in() && $postNameCorrect) {
        wp_redirect("/login");
        exit;
    }
}

add_action('wp_head', 'mp_ssv_profile_page_login_redirect', 9);
#endregion

#region Direct Debit PDF Preperation
function mp_ssv_direct_debit_setup($content)
{
    if (strpos($content, '[ssv-frontend-members-profile]') === false) { //Not the Profile Page Tag
        return $content;
    }

    if (isset($_GET['view']) && $_GET['view'] == 'directDebitPDF') {
        if (isset($_GET['user_id'])) {
            $member = FrontendMember::get_by_id($_GET['user_id']);
        } else {
            $member = FrontendMember::get_current_user();
        }
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
        ssv_redirect(get_site_url() . '/wp-content/plugins/ssv-frontend-members/frontend-pages/direct-debit-pdf.php');
    }
    return $content;
}

add_filter('the_content', 'mp_ssv_direct_debit_setup');
#endregion

/**
 * This function sets up the profile page.
 *
 * @param string $content is the post content.
 *
 * @return string the edited post content.
 */
function mp_ssv_profile_page_setup($content)
{
    if (strpos($content, '[ssv-frontend-members-profile]') === false) {
        return $content;
    } elseif (!current_theme_supports('materialize')) {
        return (new Message('This functionality currently requires the theme to support "materialize".', Message::ERROR_MESSAGE))->getHTML();
    } elseif (isset($_GET['user_id']) && !FrontendMember::get_current_user()->isBoard()) {
        return (new Message('You have no access to view this profile', Message::ERROR_MESSAGE))->getHTML();
    }

    $_SESSION['field_errors'] = array();
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_image']) && check_admin_referer('ssv_remove_image_from_profile')) {
        #region Remove Image
        global $wpdb;
        $fieldID        = $_POST['remove_image'];
        $table          = FRONTEND_MEMBERS_FIELD_META_TABLE_NAME;
        $imageName      = $wpdb->get_var("SELECT meta_value FROM $table WHERE field_id = $fieldID AND meta_key = 'name'");
        $frontendMember = FrontendMember::get_by_id($_POST['user_id']);
        unlink($frontendMember->getMeta($imageName . '_path'));
        $frontendMember->updateMeta($imageName, '');
        $frontendMember->updateMeta($imageName . '_path', '');
        echo 'image successfully removed success';
        return '';
        #endregion
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && check_admin_referer('ssv_save_frontend_member_profile')) {
        mp_ssv_save_members_profile();
    }

    return mp_ssv_profile_page_content();
}

/**
 * @return string the content of the Profile Page.
 */
function mp_ssv_profile_page_content()
{
    #region set variables
    if (isset($_GET['user_id'])) {
        $member    = FrontendMember::get_by_id($_GET['user_id']);
        $actionURL = '/profile/?user_id=' . $member->ID;
    } else {
        $member    = FrontendMember::get_current_user();
        $actionURL = '/profile/';
    }
    $canEdit = ($member->ID == wp_get_current_user()->ID || current_user_can('edit_user'));
    $member  = new FrontendMember($member);
    $tabs    = FrontendMembersField::getTabs();
    if (count($tabs) > 0) {
        if (isset($_POST['tab'])) {
            $activeTabID = $_POST['tab'];
        } else {
            $activeTabID = $tabs[0];
        }
    }
    #endregion

    ob_start();
    ?>
    <div class="row">
        <?php
        if (count($tabs) > 0): ?>
            <?php #region tabs ?>
            <div class="col s12 m10">
                <ul id="profile-menu" class="tabs">
                    <?php
                    foreach ($tabs as $tab) {
                        /** @var FrontendMembersFieldTab $tab */
                        echo $tab->getHTML($tab->id == $activeTabID);
                    }
                    ?>
                </ul>
            </div>
            <div class="col s12 m2">
                <?php if ($member->isCurrentUser()): ?>
                    <?php $url = mp_ssv_get_current_base_url() . '?logout=success'; ?>
                    <a class="btn waves-effect waves-light red right" href="<?= wp_logout_url($url) ?>">Logout</a>
                <?php endif; ?>
            </div>
            <?php #endregion ?>

            <?php foreach ($tabs as $tab): ?>
                <div id="tab<?= esc_html($tab->id) ?>" class="col s12">
                    <form name="members_<?= esc_html($tab->title) ?>_form" id="member_<?= esc_html($tab->title) ?>_form" action="<?= esc_html($actionURL) ?>" method="post" enctype="multipart/form-data">
                        <div class="row" style="padding: 10px;">
                            <?php
                            echo ssv_get_hidden(null, 'tab', $tab->id);
                            $itemsInTab = FrontendMembersField::getItemsInTab($tab);
                            foreach ($itemsInTab as $item) {
                                /** @var FrontendMembersFieldInput $item */
                                echo $item->getHTML($member);
                                if (isset($item->name) && isset($_SESSION['field_errors'][$item->name])) {
                                    /** @var Message $error */
                                    $error = $_SESSION['field_errors'][$item->name];
                                    echo $error->getHTML();
                                }
                            }
                            ?>
                            <?php
                            if ($canEdit) {
                                wp_nonce_field('ssv_save_frontend_member_profile');
                                ?>
                                <div class="col s12">
                                    <button class="btn waves-effect waves-light btn waves-effect waves-light--primary button-primary" type="submit" name="submit" id="submit">Save</button>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col s12 m2 offset-m10">
                <?php if ($member->isCurrentUser()): ?>
                    <?php $url = mp_ssv_get_current_base_url() . '?logout=success'; ?>
                    <a class="btn waves-effect waves-light red right" href="<?= wp_logout_url($url) ?>">Logout</a>
                <?php endif; ?>
            </div>
            <div id="all" class="col s12">
                <form name="members_form" id="member_form" action="<?= esc_html($actionURL) ?>" method="post" enctype="multipart/form-data">
                    <div class="row" style="padding: 10px;">
                        <?php
                        $items = FrontendMembersField::getAll(array('registration_page' => 'no'));
                        foreach ($items as $item) {
                            /** @var FrontendMembersFieldInput $item */
                            if (isset($item->name) && isset($_SESSION['field_errors'][$item->name])) {
                                /** @var Message $error */
                                $error = $_SESSION['field_errors'][$item->name];
                                echo $error->getHTML();
                            }
                            echo $item->getHTML($member);
                        }
                        ?>
                        <?php
                        if ($canEdit) {
                            wp_nonce_field('ssv_save_frontend_member_profile');
                            ?>
                            <div class="col s12">
                                <button class="btn waves-effect waves-light btn waves-effect waves-light--primary button-primary" type="submit" name="submit" id="submit">Save</button>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}

function mp_ssv_save_members_profile()
{
    if (isset($_GET['user_id'])) {
        $member = FrontendMember::get_by_id($_GET['user_id']);
    } else {
        $member = FrontendMember::get_current_user();
    }
    $filters = array('field_type' => 'input');
    $items   = FrontendMembersField::getItemsInTab($_POST['tab'], $filters);
    if (!current_theme_supports('materialize')) {
        echo (new Message('Saving this profile requires a theme with "materialize" support.'))->getHTML();
    }
    foreach ($items as $item) {
        /** @var FrontendMembersFieldInput $item */
        $value = null;
        if (isset($_POST[$item->name]) || isset($_POST[$item->name . '_reset'])) {
            $value = isset($_POST[$item->name]) ? $_POST[$item->name] : $_POST[$item->name . '_reset'];
        }
        if ($item->isValueRequiredForMember($member) && $value == null) {
            $error                                 = new Message($item->title . ' is required but there was no value given.', Message::ERROR_MESSAGE);
            $_SESSION['field_errors'][$item->name] = $error;
        } elseif (!$item->isEditable() && $value != null && $member->getMeta($item->name) != $value) {
            $error                                 = new Message('You are not allowed to edit ' . $item->title . '.', Message::ERROR_MESSAGE);
            $_SESSION['field_errors'][$item->name] = $error;
        } elseif ($member->getMeta($item->name) != $value && $item->isEditable()) {
            if (!($item instanceof FrontendMembersFieldInputImage && $item->required && $value == null)) {
                $update_response = $member->updateMeta($item->name, sanitize_text_field($value));
                if ($update_response !== true) {
                    echo $update_response->getHTML();
                }
            }
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
                if ($member->getMeta($name) != '' && $member->getMeta($name) != $file_location['url']) {
                    unlink($member->getMeta($name . '_path'));
                    $member->updateMeta($name, $file_location["url"]);
                    $member->updateMeta($name . '_path', $file_location["file"]);
                } elseif ($member->getMeta($name) != $file_location['url']) {
                    $member->updateMeta($name, $file_location["url"]);
                    $member->updateMeta($name . '_path', $file_location["file"]);
                }
            }
        }
    }
    do_action('mp_ssv_frontend_member_saved', $member);
}

add_filter('the_content', 'mp_ssv_profile_page_setup');
